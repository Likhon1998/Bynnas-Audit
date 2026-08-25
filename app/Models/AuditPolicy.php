<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditPolicy extends Model
{
    public const CATEGORY_SHAKHA = 'shakha_audit';

    public const CATEGORY_AREA = 'area_office';

    public const CATEGORY_PKSF = 'pksf_maternity';

    public const CATEGORY_HQ = 'hq_concern';

    public const CATEGORY_PROJECT_AUDIT = 'project_audit';

    public const CATEGORY_PROJECT_MONITORING = 'project_monitoring';

    protected $fillable = [
        'audit_plan_id',
        'category',
        'frequency_per_year',
        'interval_months',
        'pattern',
        'custom_month_indexes',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'custom_month_indexes' => 'array',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(AuditPlan::class, 'audit_plan_id');
    }
}
