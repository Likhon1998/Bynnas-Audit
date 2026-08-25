<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

class MonthlyAssignment extends Model
{
    protected $fillable = [
        'monthly_work_item_id',
        'employee_id',
        'visit_date',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'duration_days',
        'duration_mode',
        'count_off_days',
        'purpose',
        'remarks',
        'last_audit_upto',
        'last_audit_upto_override',
        'is_override_conflict',
        'original_start_date',
        'original_end_date',
        'reschedule_reason',
        'assigned_by',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'start_date' => 'date',
            'end_date' => 'date',
            'last_audit_upto' => 'date',
            'original_start_date' => 'date',
            'original_end_date' => 'date',
            'last_audit_upto_override' => 'boolean',
            'is_override_conflict' => 'boolean',
            'count_off_days' => 'boolean',
        ];
    }

    public function workItem(): BelongsTo
    {
        return $this->belongsTo(MonthlyWorkItem::class, 'monthly_work_item_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function visitors(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'monthly_assignment_visitors')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function execution(): HasOne
    {
        return $this->hasOne(VisitExecution::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(AssignmentStatusLog::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /** @return Collection<int, Employee> */
    public function visitorList(): Collection
    {
        $visitors = $this->relationLoaded('visitors') ? $this->visitors : $this->visitors()->get();

        if ($visitors->isNotEmpty()) {
            return $visitors;
        }

        return $this->employee ? collect([$this->employee]) : collect();
    }

    public function visitorNames(string $separator = "\n"): string
    {
        return $this->visitorList()->pluck('name')->filter()->implode($separator);
    }

    public function visitDateRangeLabel(): string
    {
        if (! $this->start_date || ! $this->end_date) {
            return '—';
        }

        $start = $this->start_date;
        $end = $this->end_date;

        if ($start->isSameDay($end)) {
            return $start->format('d F-Y');
        }

        if ($start->isSameMonth($end) && $start->isSameYear($end)) {
            return $start->format('d').' - '.$end->format('d F-Y');
        }

        return $start->format('d M').' - '.$end->format('d F-Y');
    }
}
