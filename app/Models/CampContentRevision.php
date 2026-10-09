<?php

namespace App\Models;

use App\Contracts\Commentable;
use App\Models\Concerns\HasSubmissionComments;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class CampContentRevision extends Model implements Commentable
{
    use HasSubmissionComments;

    public const STATUSES = [
        'pending' => 'Waiting review',
        'approved' => 'Approved',
        'changes_requested' => 'Changes requested',
    ];

    /** Extensions trainers may upload (slides, docs, code and project files). */
    public const FILE_EXTENSIONS = 'pdf,doc,docx,ppt,pptx,xls,xlsx,csv,txt,md,zip,png,jpg,jpeg,gif,sb3,py,ipynb,html,css,js,json';

    protected $fillable = [
        'camp_id',
        'course_id',
        'trainer_id',
        'title',
        'notes',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('pending_camp_revisions'));
        static::deleted(fn () => Cache::forget('pending_camp_revisions'));
        static::deleting(fn (self $revision) => $revision->files->each->delete());
    }

    public function camp(): BelongsTo
    {
        return $this->belongsTo(CodeCamp::class, 'camp_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function files(): HasMany
    {
        return $this->hasMany(CampContentRevisionFile::class);
    }

    /**
     * @param  array<int, \Illuminate\Http\UploadedFile>  $uploads
     */
    public function storeUploads(array $uploads): void
    {
        foreach ($uploads as $upload) {
            $this->files()->create([
                'disk' => 'local',
                'path' => $upload->store('camp-revisions/'.$this->id, 'local'),
                'original_name' => mb_substr($upload->getClientOriginalName(), 0, 191),
                'mime_type' => $upload->getMimeType(),
                'size' => (int) $upload->getSize(),
            ]);
        }
    }

    public static function uploadRules(): array
    {
        return ['file', 'max:12288', 'extensions:'.self::FILE_EXTENSIONS];
    }

    public static function uploadMessages(string $field): array
    {
        return [
            "$field.*.extensions" => 'That file type is not supported.',
            "$field.*.max" => 'Each file must be 12 MB or smaller. Zip large folders or share a link in the notes.',
        ];
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public static function canReview(User $user): bool
    {
        return $user->isAdmin() || $user->isSupervisor();
    }

    public function approve(User $reviewer, ?string $note = null): void
    {
        $this->review($reviewer, 'approved', $note);

        app(NotificationService::class)->notify(
            $this->trainer,
            'Revised content approved',
            $reviewer->name.' approved your revised content "'.$this->title.'".',
            'info',
            ['action_url' => $this->commentUrl()]
        );
    }

    public function requestChanges(User $reviewer, string $note): void
    {
        $this->review($reviewer, 'changes_requested', $note);

        app(NotificationService::class)->notify(
            $this->trainer,
            'Changes requested on your revised content',
            $reviewer->name.' asked for changes to "'.$this->title.'".',
            'approval',
            ['action_url' => $this->commentUrl()]
        );
    }

    private function review(User $reviewer, string $status, ?string $note): void
    {
        abort_unless(self::canReview($reviewer), 403);

        $this->update([
            'status' => $status,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'review_notes' => $note ?: null,
        ]);

        if ($note) {
            $this->comments()->create(['user_id' => $reviewer->id, 'body' => $note]);
        }
    }

    public function notifyReviewers(string $title, string $message): void
    {
        $notifications = app(NotificationService::class);

        User::whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'supervisor']))
            ->where('id', '!=', $this->trainer_id)
            ->get()
            ->each(fn (User $user) => $notifications->notify($user, $title, $message, 'approval', ['action_url' => $this->commentUrl()]));
    }

    public function commentOwner(): ?User
    {
        return $this->trainer;
    }

    public function commentSubject(): string
    {
        return 'the revised content "'.$this->title.'"';
    }

    public function commentUrl(): string
    {
        return route('camp-revisions.show', $this);
    }

    public function canComment(User $user): bool
    {
        return self::canReview($user) || (int) $this->trainer_id === (int) $user->id;
    }
}
