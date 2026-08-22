<?php

namespace App\Support;

use InvalidArgumentException;
use ZipArchive;

/**
 * Minimal XLSX reader for simple single-sheet imports (header + data rows).
 */
class SimpleXlsxReader
{
    private const MAIN_NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    private const REL_NS = 'http://schemas.openxmlformats.org/package/2006/relationships';

    private const OFFICE_REL_NS = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

    /**
     * @return array<int, array<int, string>>
     */
    public static function readRows(string $path, ?string $preferredSheetName = null): array
    {
        $sheets = self::readAllSheets($path);

        if ($sheets === []) {
            return [];
        }

        if ($preferredSheetName !== null) {
            foreach ($sheets as $name => $rows) {
                if (self::sheetNamesMatch($name, $preferredSheetName) && $rows !== []) {
                    return $rows;
                }
            }
        }

        $activeIndex = self::activeTabIndex($path);

        if (isset($sheets[$activeIndex])) {
            $name = array_keys($sheets)[$activeIndex] ?? null;
            $rows = $name !== null ? ($sheets[$name] ?? []) : [];

            if ($rows !== []) {
                return $rows;
            }
        }

        foreach ($sheets as $rows) {
            if ($rows !== []) {
                return $rows;
            }
        }

        return [];
    }

    /**
     * @return array<string, array<int, array<int, string>>>
     */
    public static function readAllSheets(string $path): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new InvalidArgumentException('XLSX import requires the PHP Zip extension.');
        }

        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new InvalidArgumentException('Could not open the Excel file.');
        }

        $sharedStrings = self::readSharedStrings($zip);
        $sheetPaths = self::resolveSheetPaths($zip);
        $sheets = [];

        foreach ($sheetPaths as $sheet) {
            $sheetXml = $zip->getFromName($sheet['path']);

            if ($sheetXml === false) {
                continue;
            }

            $sheets[$sheet['name']] = self::parseSheet($sheetXml, $sharedStrings);
        }

        $zip->close();

        return $sheets;
    }

    public static function activeTabIndex(string $path): int
    {
        if (! class_exists(ZipArchive::class)) {
            return 0;
        }

        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            return 0;
        }

        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $zip->close();

        if ($workbookXml === false) {
            return 0;
        }

        $document = @simplexml_load_string($workbookXml);

        if ($document === false) {
            return 0;
        }

        foreach ($document->children(self::MAIN_NS)->bookViews->children(self::MAIN_NS) as $view) {
            $attrs = $view->attributes();

            if (isset($attrs['activeTab'])) {
                return max(0, (int) $attrs['activeTab']);
            }
        }

        return 0;
    }

    /**
     * @return array<int, array{name: string, path: string}>
     */
    private static function resolveSheetPaths(ZipArchive $zip): array
    {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if ($workbookXml === false || $relsXml === false) {
            return [['name' => 'Sheet1', 'path' => 'xl/worksheets/sheet1.xml']];
        }

        $workbook = @simplexml_load_string($workbookXml);
        $rels = @simplexml_load_string($relsXml);

        if ($workbook === false || $rels === false) {
            return [['name' => 'Sheet1', 'path' => 'xl/worksheets/sheet1.xml']];
        }

        $targetsById = [];

        foreach ($rels->children(self::REL_NS) as $relationship) {
            $attrs = $relationship->attributes();
            $id = (string) ($attrs['Id'] ?? '');
            $type = (string) ($attrs['Type'] ?? '');
            $target = (string) ($attrs['Target'] ?? '');

            if ($id === '' || $target === '' || ! str_contains($type, '/worksheet')) {
                continue;
            }

            $path = ltrim(str_replace('../', '', $target), '/');
            $targetsById[$id] = str_starts_with($path, 'xl/') ? $path : 'xl/' . ltrim($path, '/');
        }

        $sheets = [];

        foreach ($workbook->children(self::MAIN_NS)->sheets->children(self::MAIN_NS) as $sheet) {
            $sheetAttrs = $sheet->attributes();
            $relAttrs = $sheet->attributes(self::OFFICE_REL_NS);
            $name = (string) ($sheetAttrs['name'] ?? 'Sheet');
            $relId = (string) ($relAttrs['id'] ?? '');

            if ($relId === '' || ! isset($targetsById[$relId])) {
                continue;
            }

            $sheets[] = [
                'name' => $name,
                'path' => $targetsById[$relId],
            ];
        }

        if ($sheets === []) {
            return [['name' => 'Sheet1', 'path' => 'xl/worksheets/sheet1.xml']];
        }

        return $sheets;
    }

    private static function sheetNamesMatch(string $sheetName, string $preferredName): bool
    {
        return self::normalizeSheetName($sheetName) === self::normalizeSheetName($preferredName);
    }

    private static function normalizeSheetName(string $name): string
    {
        $name = strtolower(trim($name));
        $name = preg_replace('/\s+/', '', $name) ?? $name;

        if (preg_match('/^p(\d+)$/', $name, $matches)) {
            return 'p.' . $matches[1];
        }

        if (preg_match('/^p\.(\d+)$/', $name, $matches)) {
            return 'p.' . $matches[1];
        }

        if (preg_match('/^s(\d+)$/', $name, $matches)) {
            return 's.' . $matches[1];
        }

        return $name;
    }

    /**
     * @return array<int, string>
     */
    private static function readSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if ($xml === false) {
            return [];
        }

        $document = @simplexml_load_string($xml);

        if ($document === false) {
            return [];
        }

        $strings = [];

        foreach ($document->children(self::MAIN_NS) as $si) {
            $strings[] = self::sharedStringValue($si);
        }

        return $strings;
    }

    private static function sharedStringValue(\SimpleXMLElement $si): string
    {
        $children = $si->children(self::MAIN_NS);

        if (isset($children->t)) {
            return trim((string) $children->t);
        }

        $text = '';

        foreach ($children->r as $run) {
            $runChildren = $run->children(self::MAIN_NS);
            $text .= (string) ($runChildren->t ?? '');
        }

        return trim($text);
    }

    /**
     * @param  array<int, string>  $sharedStrings
     * @return array<int, array<int, string>>
     */
    private static function parseSheet(string $xml, array $sharedStrings): array
    {
        $document = @simplexml_load_string($xml);

        if ($document === false) {
            throw new InvalidArgumentException('Could not parse worksheet data in the Excel file.');
        }

        $sheetData = $document->children(self::MAIN_NS)->sheetData ?? null;

        if ($sheetData === null) {
            return [];
        }

        $rows = [];

        foreach ($sheetData->children(self::MAIN_NS) as $row) {
            $cells = [];
            $maxIndex = -1;
            $sequentialIndex = 0;

            foreach ($row->children(self::MAIN_NS) as $cell) {
                $attrs = $cell->attributes();
                $ref = (string) ($attrs['r'] ?? '');

                if ($ref !== '' && preg_match('/^([A-Z]+)/', $ref, $matches)) {
                    $index = self::columnIndexFromLetters($matches[1]);
                } else {
                    $index = $sequentialIndex;
                    $sequentialIndex++;
                }

                $cells[$index] = self::cellValue($cell, $sharedStrings);
                $maxIndex = max($maxIndex, $index);
            }

            if ($maxIndex < 0) {
                continue;
            }

            $line = [];

            for ($i = 0; $i <= $maxIndex; $i++) {
                $line[] = $cells[$i] ?? '';
            }

            while ($line !== [] && trim((string) end($line)) === '') {
                array_pop($line);
            }

            if (count(array_filter($line, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            $rows[] = $line;
        }

        return $rows;
    }

    private static function columnIndexFromLetters(string $letters): int
    {
        $index = 0;

        for ($i = 0, $len = strlen($letters); $i < $len; $i++) {
            $index = ($index * 26) + (ord($letters[$i]) - ord('A') + 1);
        }

        return $index - 1;
    }

    /**
     * @param  array<int, string>  $sharedStrings
     */
    private static function cellValue(\SimpleXMLElement $cell, array $sharedStrings): string
    {
        $attrs = $cell->attributes();
        $type = (string) ($attrs['t'] ?? '');
        $children = $cell->children(self::MAIN_NS);

        if ($type === 's') {
            $idx = (int) ($children->v ?? 0);

            return $sharedStrings[$idx] ?? '';
        }

        if ($type === 'inlineStr') {
            $is = $children->is ?? null;

            if ($is === null) {
                return '';
            }

            $isChildren = $is->children(self::MAIN_NS);

            if (isset($isChildren->t)) {
                return trim((string) $isChildren->t);
            }

            $text = '';

            foreach ($isChildren->r as $run) {
                $text .= (string) ($run->children(self::MAIN_NS)->t ?? '');
            }

            return trim($text);
        }

        if ($type === 'b') {
            return ((string) ($children->v ?? '0')) === '1' ? 'TRUE' : 'FALSE';
        }

        return trim((string) ($children->v ?? ''));
    }
}
