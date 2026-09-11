<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

class AuditReport extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_COMPLETED = 'completed';

    /** Max concurrent drafts a user may keep open at once. */
    public const MAX_CONCURRENT_DRAFTS = 3;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'audit_start_date' => 'date',
            'audit_end_date' => 'date',
            'draft_sent_date' => 'date',
            'comments_received_date' => 'date',
            'pages_data' => 'array',
            'working_days' => 'integer',
            'progress_pct' => 'integer',
            'last_saved_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function shakha(): BelongsTo
    {
        return $this->belongsTo(Shakha::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function collaborators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'audit_report_collaborators')
            ->withTimestamps()
            ->orderBy('users.name');
    }

    public function checklistFiles(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AuditReportChecklistFile::class, 'audit_report_id')
            ->orderByDesc('id');
    }

    public function checklistSubmissions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AuditChecklistSubmission::class, 'audit_report_id')
            ->orderByDesc('saved_at')
            ->orderByDesc('id');
    }

    public function checklistFormats(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            AuditChecklistFormat::class,
            'audit_report_checklist_format',
            'audit_report_id',
            'audit_checklist_format_id'
        )->withTimestamps()->orderBy('format_number');
    }

    public function sends(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AuditReportSend::class, 'audit_report_id')
            ->orderByDesc('sent_at')
            ->orderByDesc('id');
    }

    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeAccessibleBy(Builder $query, int $userId): Builder
    {
        return $query->where(function (Builder $inner) use ($userId) {
            $inner->where('user_id', $userId)
                ->orWhereHas('collaborators', fn (Builder $q) => $q->where('users.id', $userId));
        });
    }

    public function isAccessibleBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $userId = (int) $user->id;
        if ($userId < 1) {
            return false;
        }

        if ((int) $this->user_id === $userId) {
            return true;
        }

        if ($this->relationLoaded('collaborators')) {
            return $this->collaborators->contains(fn (User $u) => (int) $u->id === $userId);
        }

        return $this->collaborators()->where('users.id', $userId)->exists();
    }

    public function isOwnedBy(?User $user): bool
    {
        return $user !== null && (int) $this->user_id === (int) $user->id;
    }

    public function collaboratorNamesLabel(): string
    {
        $this->loadMissing(['user:id,name', 'collaborators:id,name']);

        $names = collect([$this->user])
            ->merge($this->collaborators)
            ->filter()
            ->pluck('name')
            ->map(fn ($n) => trim((string) $n))
            ->filter()
            ->unique()
            ->values();

        if ($names->count() <= 1) {
            return (string) $names->first();
        }

        return $names->slice(0, -1)->implode(', ').' ও '.$names->last();
    }

    public function scopeDrafts(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function periodLabel(): string
    {
        $month = (int) ($this->report_month ?: 0);
        $year = (int) ($this->report_year ?: 0);
        if ($month < 1 || $month > 12 || $year < 1) {
            return '—';
        }

        return Carbon::create($year, $month, 1)->format('F Y');
    }

    public function progressLabel(): string
    {
        return $this->progress_pct.'%';
    }

    public function statusBadge(): string
    {
        if ($this->isCompleted()) {
            return 'Completed';
        }

        return $this->progress_pct > 0 ? 'Ongoing' : 'Pending';
    }

    /**
     * Compute progress from wizard meta + cover fields.
     *
     * @param  array<string, mixed>  $pages
     */
    public static function computeProgress(array $pages, array $coverHints = []): int
    {
        $done = (array) data_get($pages, 'meta.tabs_done', []);
        $steps = ['cover', 'page2', 'page3', 'page4'];
        $completed = 0;

        foreach ($steps as $step) {
            if (! empty($done[$step])) {
                $completed++;
            }
        }

        $score = (int) round(($completed / count($steps)) * 100);

        // Cover may not be marked yet but already has key fields.
        if (empty($done['cover']) && ! empty($coverHints['memo_no']) && ! empty($coverHints['auditor_name'])) {
            $score = max($score, 8);
        }

        return min(100, $score);
    }

    public static function ratingColor(?string $rating): string
    {
        return match ($rating) {
            'Satisfactory' => '#22c55e',
            'Minor' => '#eab308',
            'Medium' => '#f97316',
            'Major' => '#ef4444',
            'Unsatisfactory' => '#b91c1c',
            default => '#f97316',
        };
    }
}
