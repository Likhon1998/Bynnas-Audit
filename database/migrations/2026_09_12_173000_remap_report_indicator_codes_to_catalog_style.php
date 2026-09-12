<?php

use App\Models\AuditIndicator;
use App\Support\AuditIndicatorCodes;
use Illuminate\Database\Migrations\Migration;

/**
 * Remap legacy ad-hoc codes রিপোর্ট-{ymdHis}-{rand} → catalog-style ৯০০০-N.
 */
return new class extends Migration
{
    public function up(): void
    {
        $legacy = AuditIndicator::query()
            ->where('indicator_code', 'like', 'রিপোর্ট-%')
            ->orderBy('id')
            ->get();

        foreach ($legacy as $indicator) {
            $indicator->update([
                'indicator_code' => AuditIndicatorCodes::nextCustomReportCode(),
            ]);
        }
    }

    public function down(): void
    {
        // Irreversible remap — legacy random codes are not reconstructed.
    }
};
