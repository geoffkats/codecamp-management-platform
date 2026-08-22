<?php

namespace App\Services;

use App\Models\CodeClub;
use App\Models\CodeClubMembership;
use App\Models\Role;
use App\Models\School;
use App\Models\StudentProfile;
use App\Models\User;
use App\Support\SimpleXlsxReader;
use App\Support\StudentPassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CodeClubStudentImportService
{
    private const MAX_ROWS = 500;

    /** @var array<int, string> */
    public const IMPORT_FILE_RULES = [
        'required',
        'file',
        'max:5120',
        'mimes:csv,txt,xlsx',
        'mimetypes:text/plain,text/csv,application/csv,application/vnd.ms-excel,text/x-csv,text/comma-separated-values,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/zip,application/octet-stream',
    ];

    /** @var array<int, string> */
    private const ALLOWED_HEADERS = [
        'full_name',
        'name',
        'student_id',
        'email',
        'school_id',
        'school_name',
        'club_id',
        'club_name',
        'parent_name',
        'parent_guardian_name',
        'parent_phone',
        'parent_guardian_contact',
        'parent_email',
        'class_grade',
        'gender',
        'age',
        'date_of_birth',
        'nationality',
        'address',
        'scratch_account',
        'scratch_password',
        'github_account',
    ];

    /**
     * @return array{imported: int, skipped: int, errors: array<int, string>}
     */
    public function importFromPath(
        string $path,
        User $importer,
        ?int $fixedClubId = null,
        ?string $originalFilename = null,
        ?string $defaultClassGrade = null,
        bool $applyClassToAll = false,
    ): array
    {
        $result = [
            'imported' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        if (! is_readable($path)) {
            $result['errors'][] = 'Could not read the CSV file.';

            return $result;
        }

        $parsed = $this->parseImportFile($path, $originalFilename);

        if ($parsed['error'] !== null) {
            $result['errors'][] = $parsed['error'];

            return $result;
        }

        $rows = $parsed['rows'];
        $delimiter = $parsed['delimiter'];

        $headerRow = array_shift($rows);
        $headers = $this->normalizeHeaders($headerRow);
        $headerError = $this->validateHeaders($headers);

        if ($headerError !== null) {
            $result['errors'][] = $headerError;
            if ($delimiter !== ',') {
                $result['errors'][] = "Detected delimiter: \"{$delimiter}\". If columns look wrong, re-save the file as CSV UTF-8 (Comma delimited) from Excel.";
            }

            return $result;
        }

        $allowedClubIds = $this->allowedClubIds($importer);
        $normalizedHeaders = array_values(array_filter($headers));
        $hasClubColumn = in_array('club_id', $normalizedHeaders, true)
            || in_array('club_name', $normalizedHeaders, true);
        $fixedClubId = $this->resolveFixedClubId($fixedClubId, $allowedClubIds, $headers);

        if ($fixedClubId === null && ! $hasClubColumn) {
            $result['errors'][] = 'No club selected for import. Choose a club above, open Admin → Code Clubs → your club and import there, or add a club_id column to your CSV.';

            return $result;
        }

        if ($rows === []) {
            $result['errors'][] = 'No student rows found after the header. Save Excel as CSV UTF-8 (Comma delimited), not .xlsx. Each row needs at least a full_name.';

            return $result;
        }

        $rowNum = 1;
        $dataRows = 0;

        $defaultClassGrade = trim((string) $defaultClassGrade);

        foreach ($rows as $row) {
            $rowNum++;

            if (count(array_filter($row, fn ($cell) => trim((string) $cell) !== '')) === 0) {
                continue;
            }

            $dataRows++;

            if ($dataRows > self::MAX_ROWS) {
                $result['errors'][] = 'CSV exceeds the maximum of ' . self::MAX_ROWS . ' data rows.';
                break;
            }

            $data = $this->mapRow($headers, $row);

            try {
                $outcome = $this->importRow($data, $importer, $fixedClubId, $allowedClubIds, $defaultClassGrade, $applyClassToAll);

                if ($outcome === 'imported') {
                    $result['imported']++;
                } else {
                    $result['skipped']++;
                    $result['errors'][] = "Row {$rowNum}: {$outcome}";
                }
            } catch (\Throwable $e) {
                $result['skipped']++;
                $result['errors'][] = "Row {$rowNum}: {$e->getMessage()}";
            }
        }

        if ($dataRows === 0) {
            $result['errors'][] = 'All rows appeared empty. Re-save from Excel as CSV UTF-8 (Comma delimited). Detected delimiter: "' . $delimiter . '".';
        }

        return $result;
    }

    /**
     * @return array{rows: array<int, array<int, string>>, delimiter: string, error: ?string}
     */
    private function parseImportFile(string $path, ?string $originalFilename = null): array
    {
        if ($this->isXlsxFile($path, $originalFilename)) {
            $result = $this->parseXlsxFile($path, $originalFilename);

            if ($result['error'] !== null && $result['rows'] === []) {
                $csvFallback = $this->parseCsvFile($path);

                if ($csvFallback['rows'] !== []) {
                    return $csvFallback;
                }
            }

            return $result;
        }

        return $this->parseCsvFile($path);
    }

    private function isXlsxFile(string $path, ?string $originalFilename = null): bool
    {
        if ($originalFilename && str_ends_with(strtolower($originalFilename), '.xlsx')) {
            return true;
        }

        if (str_ends_with(strtolower($path), '.xlsx')) {
            return true;
        }

        $head = @file_get_contents($path, false, null, 0, 4);

        return $head === "PK\x03\x04";
    }

    /**
     * @return array{rows: array<int, array<int, string>>, delimiter: string, error: ?string}
     */
    private function parseXlsxFile(string $path, ?string $originalFilename = null): array
    {
        try {
            $sheets = SimpleXlsxReader::readAllSheets($path);
        } catch (\Throwable $e) {
            return ['rows' => [], 'delimiter' => ',', 'error' => $e->getMessage()];
        }

        if ($sheets === []) {
            return ['rows' => [], 'delimiter' => ',', 'error' => 'Excel file is empty or has no readable rows.'];
        }

        $preferredSheet = $this->inferPreferredSheetName($originalFilename ?? $path);
        $orderedSheets = $this->orderSheetsForImport($sheets, $path, $preferredSheet);
        $headerErrors = [];

        foreach ($orderedSheets as $sheetName => $rows) {
            if ($rows === []) {
                continue;
            }

            $headers = $this->normalizeHeaders($rows[0]);
            $headerError = $this->validateHeaders($headers);

            if ($headerError === null) {
                return ['rows' => $rows, 'delimiter' => ',', 'error' => null];
            }

            $headerErrors[] = "Sheet \"{$sheetName}\": {$headerError}";
        }

        $fallbackRows = SimpleXlsxReader::readRows($path, $preferredSheet);

        if ($fallbackRows !== []) {
            $headers = $this->normalizeHeaders($fallbackRows[0]);
            $headerError = $this->validateHeaders($headers);

            if ($headerError === null) {
                return ['rows' => $fallbackRows, 'delimiter' => ',', 'error' => null];
            }

            $headerErrors[] = $headerError;
        }

        return [
            'rows' => [],
            'delimiter' => ',',
            'error' => $headerErrors[0] ?? 'Excel file is empty or has no readable rows.',
        ];
    }

    /**
     * @param  array<string, array<int, array<int, string>>>  $sheets
     * @return array<string, array<int, array<int, string>>>
     */
    private function orderSheetsForImport(array $sheets, string $path, ?string $preferredSheet): array
    {
        $ordered = [];

        if ($preferredSheet !== null) {
            foreach ($sheets as $name => $rows) {
                if ($this->sheetNamesMatch($name, $preferredSheet)) {
                    $ordered[$name] = $rows;
                }
            }
        }

        $activeIndex = SimpleXlsxReader::activeTabIndex($path);
        $sheetNames = array_keys($sheets);

        if (isset($sheetNames[$activeIndex]) && ! isset($ordered[$sheetNames[$activeIndex]])) {
            $ordered[$sheetNames[$activeIndex]] = $sheets[$sheetNames[$activeIndex]];
        }

        foreach ($sheets as $name => $rows) {
            if (! isset($ordered[$name])) {
                $ordered[$name] = $rows;
            }
        }

        return $ordered;
    }

    private function inferPreferredSheetName(string $source): ?string
    {
        $name = basename($source);

        if (preg_match('/\bP\.?\s*(\d+)\b/i', $name, $matches)) {
            return 'P.' . $matches[1];
        }

        if (preg_match('/\bS\.?\s*(\d+)\b/i', $name, $matches)) {
            return 'S.' . $matches[1];
        }

        return null;
    }

    private function sheetNamesMatch(string $sheetName, string $preferredName): bool
    {
        $normalize = static function (string $value): string {
            $value = strtolower(trim($value));
            $value = preg_replace('/\s+/', '', $value) ?? $value;

            if (preg_match('/^p(\d+)$/', $value, $matches)) {
                return 'p.' . $matches[1];
            }

            if (preg_match('/^p\.(\d+)$/', $value, $matches)) {
                return 'p.' . $matches[1];
            }

            if (preg_match('/^s(\d+)$/', $value, $matches)) {
                return 's.' . $matches[1];
            }

            return $value;
        };

        return $normalize($sheetName) === $normalize($preferredName);
    }

    /**
     * @return array{rows: array<int, array<int, string>>, delimiter: string, error: ?string}
     */
    private function parseCsvFile(string $path): array
    {
        $content = file_get_contents($path);

        if ($content === false || trim($content) === '') {
            return ['rows' => [], 'delimiter' => ',', 'error' => 'Could not read the CSV file or file is empty.'];
        }

        if (str_starts_with($content, "PK\x03\x04")) {
            return ['rows' => [], 'delimiter' => ',', 'error' => 'This looks like an Excel .xlsx file but could not be read. Try uploading the .xlsx directly or save as CSV UTF-8.'];
        }

        $content = $this->normalizeFileEncoding($content);

        $stream = fopen('php://memory', 'r+');

        if ($stream === false) {
            return ['rows' => [], 'delimiter' => ',', 'error' => 'Could not parse the CSV file.'];
        }

        fwrite($stream, $content);
        rewind($stream);

        $firstLine = fgets($stream);

        if ($firstLine === false || trim($firstLine) === '') {
            fclose($stream);

            return ['rows' => [], 'delimiter' => ',', 'error' => 'CSV file is empty.'];
        }

        $delimiter = $this->detectDelimiter($firstLine);
        rewind($stream);

        $rows = [];

        while (($row = fgetcsv($stream, 0, $delimiter, '"', '\\')) !== false) {
            if (count(array_filter($row, fn ($cell) => trim((string) $cell) !== '')) === 0) {
                continue;
            }

            $rows[] = $row;
        }

        fclose($stream);

        return ['rows' => $rows, 'delimiter' => $delimiter, 'error' => null];
    }

    private function normalizeFileEncoding(string $content): string
    {
        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        } elseif (str_starts_with($content, "\xFF\xFE")) {
            $converted = @mb_convert_encoding(substr($content, 2), 'UTF-8', 'UTF-16LE');
            $content = $converted !== false ? $converted : $content;
        } elseif (str_starts_with($content, "\xFE\xFF")) {
            $converted = @mb_convert_encoding(substr($content, 2), 'UTF-8', 'UTF-16BE');
            $content = $converted !== false ? $converted : $content;
        }

        return str_replace("\r\n", "\n", str_replace("\r", "\n", $content));
    }

    private function detectDelimiter(string $headerLine): string
    {
        $candidates = [
            ',' => substr_count($headerLine, ','),
            ';' => substr_count($headerLine, ';'),
            "\t" => substr_count($headerLine, "\t"),
        ];

        arsort($candidates);

        foreach ($candidates as $delimiter => $count) {
            if ($count > 0) {
                return $delimiter;
            }
        }

        return ',';
    }

    /**
     * @return array<int, string>|null
     */
    private function allowedClubIds(User $importer): ?array
    {
        if ($importer->isAdmin() || $importer->isSupervisor() || $importer->isOperationsManager()) {
            return null;
        }

        return $importer->activeClubIds();
    }

    /**
     * @param  array<int, string>  $headers
     */
    private function validateHeaders(array $headers): ?string
    {
        $normalized = array_values(array_filter($headers));

        if ($normalized === []) {
            return 'CSV header row is missing or invalid.';
        }

        if (! in_array('full_name', $normalized, true) && ! in_array('name', $normalized, true)) {
            return 'CSV must include a full_name (or name) column.';
        }

        foreach ($normalized as $header) {
            if ($header !== '' && ! in_array($header, self::ALLOWED_HEADERS, true)) {
                return "Unknown CSV column: {$header}.";
            }
        }

        return null;
    }

    /**
     * @param  array<int, string>  $headers
     * @return array<string, string>
     */
    private function mapRow(array $headers, array $row): array
    {
        $mapped = [];

        foreach ($headers as $index => $key) {
            if ($key === '') {
                continue;
            }

            $mapped[$key] = $this->sanitizeCell((string) ($row[$index] ?? ''));
        }

        return $mapped;
    }

    private function sanitizeCell(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        while ($value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            $value = ltrim($value, "=+-\t\r@");
            $value = trim($value);
        }

        return $value;
    }

    /**
     * @param  array<string, string>  $data
     * @param  array<int, string>|null  $allowedClubIds
     */
    private function importRow(array $data, User $importer, ?int $fixedClubId, ?array $allowedClubIds, string $defaultClassGrade = '', bool $applyClassToAll = false): string
    {
        $fullName = $data['full_name'] ?? $data['name'] ?? '';

        if ($fullName === '' || strlen($fullName) > 255) {
            throw new InvalidArgumentException('full_name is required and must be 255 characters or fewer.');
        }

        $clubId = $fixedClubId ?: $this->resolveClubId($data, $allowedClubIds);
        $club = CodeClub::find($clubId);

        if (! $club) {
            throw new InvalidArgumentException('Code club not found.');
        }

        if ($allowedClubIds !== null && ! in_array((int) $club->id, $allowedClubIds, true)) {
            throw new InvalidArgumentException('You are not allowed to import into this club.');
        }

        $schoolId = $this->resolveSchoolId($data, $club);

        if ((int) $club->school_id !== (int) $schoolId) {
            throw new InvalidArgumentException('Club does not belong to the resolved school.');
        }

        $studentId = $data['student_id'] ?? '';

        if ($studentId !== '') {
            if (! preg_match('/^[A-Za-z0-9._-]{1,50}$/', $studentId)) {
                throw new InvalidArgumentException('student_id contains invalid characters.');
            }

            if (StudentProfile::where('student_id', $studentId)->exists()) {
                return "Skipped duplicate student_id {$studentId}.";
            }
        }

        $email = $data['email'] ?? '';

        if ($email !== '') {
            if (! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
                throw new InvalidArgumentException('Invalid email format.');
            }

            if (User::where('email', $email)->exists()) {
                return "Skipped duplicate email {$email}.";
            }
        }

        $parentName = $data['parent_name'] ?? $data['parent_guardian_name'] ?? '';
        $parentPhone = $data['parent_phone'] ?? $data['parent_guardian_contact'] ?? '';

        if ($parentName !== '' && strlen($parentName) > 255) {
            throw new InvalidArgumentException('parent_name is too long.');
        }

        if ($parentPhone !== '' && strlen($parentPhone) > 50) {
            throw new InvalidArgumentException('parent_phone is too long.');
        }

        $generatedStudentId = $studentId !== '' ? $studentId : StudentProfile::generateStudentId('codeclub');
        $resolvedEmail = $email !== '' ? $email : null;
        $password = StudentPassword::generateKidFriendly();
        $classGrade = $this->resolveClassGrade($data, $defaultClassGrade, $applyClassToAll);

        DB::transaction(function () use ($fullName, $generatedStudentId, $resolvedEmail, $password, $schoolId, $parentName, $parentPhone, $data, $clubId, $classGrade) {
            $user = User::create([
                'name' => $fullName,
                'email' => $resolvedEmail,
                'student_type' => 'codeclub',
                'student_id' => $generatedStudentId,
                'password' => Hash::make($password),
                'initial_password' => $password,
            ]);

            $role = Role::where('name', 'student')->first();

            if ($role) {
                $user->roles()->attach($role->id);
            }

            $parentEmail = $data['parent_email'] ?? null;
            $hasParent = $parentName !== '' || $parentPhone !== '';

            $parentData = null;
            if ($hasParent) {
                $parentData = [
                    'parent1' => [
                        'name' => $parentName,
                        'relationship' => 'guardian',
                        'phone' => $parentPhone,
                        'email' => $parentEmail && filter_var($parentEmail, FILTER_VALIDATE_EMAIL) ? $parentEmail : null,
                    ],
                ];
            }

            StudentProfile::create([
                'user_id' => $user->id,
                'school_id' => $schoolId,
                'program_type' => 'codeclub',
                'student_category' => 'school_club',
                'student_id' => $generatedStudentId,
                'exam_readiness_status' => 'not_ready',
                'is_active' => true,
                'full_name' => $fullName,
                'date_of_birth' => ($data['date_of_birth'] ?? '') ?: null,
                'age' => $this->parseAge($data['age'] ?? null),
                'gender' => in_array($data['gender'] ?? '', ['male', 'female'], true) ? ($data['gender'] ?? null) : null,
                'nationality' => isset($data['nationality']) ? Str::limit($data['nationality'], 100, '') : null,
                'parent_guardian_name' => $parentName !== '' ? $parentName : '',
                'parent_guardian_contact' => $parentPhone !== '' ? $parentPhone : '',
                'parent_data' => $parentData,
                'class_grade' => $classGrade !== null ? Str::limit($classGrade, 50, '') : null,
                'address' => isset($data['address']) ? Str::limit($data['address'], 500, '') : null,
                'scratch_account' => isset($data['scratch_account']) ? Str::limit($data['scratch_account'], 255, '') : null,
                'scratch_password' => isset($data['scratch_password']) ? Str::limit($data['scratch_password'], 255, '') : null,
                'github_account' => isset($data['github_account']) ? Str::limit($data['github_account'], 255, '') : null,
            ]);

            CodeClubMembership::updateOrCreate(
                [
                    'code_club_id' => $clubId,
                    'student_id' => $user->id,
                ],
                [
                    'status' => 'active',
                    'enrolled_at' => now(),
                    'dropped_at' => null,
                ]
            );
        });

        return 'imported';
    }

    /**
     * @param  array<string, string>  $data
     * @param  array<int, string>|null  $allowedClubIds
     */
    private function resolveClubId(array $data, ?array $allowedClubIds): int
    {
        if (! empty($data['club_id'])) {
            if (! ctype_digit((string) $data['club_id'])) {
                throw new InvalidArgumentException('club_id must be a positive integer.');
            }

            return (int) $data['club_id'];
        }

        $clubName = $data['club_name'] ?? '';

        if ($clubName === '') {
            throw new InvalidArgumentException('club_id or club_name is required.');
        }

        $query = CodeClub::query()->where('name', $clubName);

        if ($allowedClubIds !== null) {
            $query->whereIn('id', $allowedClubIds);
        }

        $matches = $query->get();

        if ($matches->isEmpty()) {
            throw new InvalidArgumentException("Club \"{$clubName}\" not found.");
        }

        if ($matches->count() > 1) {
            throw new InvalidArgumentException("Club name \"{$clubName}\" is ambiguous; use club_id instead.");
        }

        return (int) $matches->first()->id;
    }

    /**
     * @param  array<string, string>  $data
     */
    private function resolveSchoolId(array $data, CodeClub $club): int
    {
        if (! empty($data['school_id'])) {
            if (! ctype_digit((string) $data['school_id'])) {
                throw new InvalidArgumentException('school_id must be a positive integer.');
            }

            return (int) $data['school_id'];
        }

        $schoolName = $data['school_name'] ?? '';

        if ($schoolName !== '') {
            $matches = School::where('name', $schoolName)->get();

            if ($matches->isEmpty()) {
                throw new InvalidArgumentException("School \"{$schoolName}\" not found.");
            }

            if ($matches->count() > 1) {
                throw new InvalidArgumentException("School name \"{$schoolName}\" is ambiguous; use school_id instead.");
            }

            return (int) $matches->first()->id;
        }

        if ($club->school_id) {
            return (int) $club->school_id;
        }

        throw new InvalidArgumentException('school_name, school_id, or a club with a school is required.');
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, string>|null  $allowedClubIds
     */
    private function resolveFixedClubId(?int $fixedClubId, ?array $allowedClubIds, array $headers): ?int
    {
        if ($fixedClubId !== null) {
            return $fixedClubId;
        }

        $normalized = array_values(array_filter($headers));

        if (in_array('club_id', $normalized, true) || in_array('club_name', $normalized, true)) {
            return null;
        }

        if ($allowedClubIds !== null && count($allowedClubIds) === 1) {
            return (int) $allowedClubIds[0];
        }

        return null;
    }

    /**
     * @param  array<int, string>  $headers
     * @return array<int, string>
     */
    private function normalizeHeaders(array $headers): array
    {
        $aliases = [
            'scratch_username' => 'scratch_account',
            'scratch_user' => 'scratch_account',
            'student_name' => 'full_name',
            'class' => 'class_grade',
            'grade' => 'class_grade',
            'stream' => 'class_grade',
        ];

        return array_map(function ($header) use ($aliases) {
            $key = preg_replace('/^\xEF\xBB\xBF/u', '', (string) $header);
            $key = strtolower(trim($key));
            $key = str_replace([' ', '-'], '_', $key);

            return $aliases[$key] ?? $key;
        }, $headers);
    }

    private function resolveClassGrade(array $data, string $defaultClassGrade = '', bool $applyToAll = false): ?string
    {
        $defaultClassGrade = trim($defaultClassGrade);
        $rowClass = trim((string) ($data['class_grade'] ?? ''));

        if ($applyToAll && $defaultClassGrade !== '') {
            return $defaultClassGrade;
        }

        if ($rowClass === '' || strcasecmp($rowClass, 'N/A') === 0) {
            $rowClass = $defaultClassGrade;
        }

        return $rowClass !== '' ? $rowClass : null;
    }

    public function suggestDefaultClassGrade(?string $filename): string
    {
        if ($filename === null || trim($filename) === '') {
            return '';
        }

        return $this->inferClassGradeFromFilename($filename);
    }

    private function inferClassGradeFromFilename(string $path): string
    {
        $name = basename($path);

        if (preg_match('/\bP\.?\s*(\d+)\b/i', $name, $matches)) {
            return 'P.' . $matches[1];
        }

        if (preg_match('/\bS\.?\s*(\d+)\b/i', $name, $matches)) {
            return 'S.' . $matches[1];
        }

        if (preg_match('/\b(?:grade|class)\s*(\d+)\b/i', $name, $matches)) {
            return 'Grade ' . $matches[1];
        }

        return '';
    }

    private function parseAge(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $age = (int) $value;

        if ($age < 4 || $age > 25) {
            throw new InvalidArgumentException('age must be between 4 and 25.');
        }

        return $age;
    }

}
