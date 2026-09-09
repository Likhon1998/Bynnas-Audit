<?php

namespace App\Services;

use App\Models\AuditFinding;
use App\Models\AuditReport;
use App\Models\Shakha;
use App\Support\BangladeshGazetteer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AuditMapService
{
    public function __construct(private UserAccessService $access) {}

    /**
     * @return array<string, mixed>
     */
    public function live($user): array
    {
        return $this->buildLive($user, $this->access->accessibleShakhaIds($user));
    }

    /**
     * @param  array<int, int>|null  $ids
     * @return array<string, mixed>
     */
    private function buildLive($user, ?array $ids): array
    {
        $query = Shakha::query()
            ->with(['area:id,name,division,status', 'latestRiskAssessment'])
            ->withCount([
                'auditReports',
                'auditReports as completed_reports_count' => fn ($q) => $q->where('status', AuditReport::STATUS_COMPLETED),
                'auditFindings',
            ])
            ->orderBy('name');

        if ($ids !== null) {
            $query->whereIn('id', $ids ?: [0]);
        }

        $shakhas = $query->get([
            'id', 'area_id', 'name', 'code', 'status', 'focal_person_name', 'updated_at',
        ]);

        $issuesByShakha = $this->priorityIssuesByShakha($shakhas->pluck('id'));

        $markers = [];
        $riskCounts = [
            'significant' => 0,
            'high' => 0,
            'medium' => 0,
            'low' => 0,
            'unassessed' => 0,
        ];
        $divisionCounts = [];

        foreach ($shakhas as $shakha) {
            $area = $shakha->area;
            $point = BangladeshGazetteer::locate(
                (string) $shakha->name,
                $area?->name,
                $area?->division,
                (int) $shakha->id,
            );
            $risk = $this->riskKey($shakha->latestRiskAssessment?->risk_category);
            $riskCounts[$risk]++;
            $division = $point['division'] ?: ($area?->division ?: 'Unknown');
            $divisionCounts[$division] = ($divisionCounts[$division] ?? 0) + 1;

            $score = $shakha->latestRiskAssessment?->total_weighted_score;

            $markers[] = [
                'id' => $shakha->id,
                'name' => $shakha->name,
                'code' => $shakha->code,
                'status' => $shakha->status,
                'focal' => $shakha->focal_person_name,
                'auditor' => $shakha->focal_person_name ?: 'Unassigned',
                'area' => $area?->name,
                'area_color' => $this->areaColor($area?->name),
                'division' => $division,
                'district' => $point['district'],
                'upazila' => $point['upazila'],
                'pourashava' => $point['pourashava'],
                'lat' => $point['lat'],
                'lng' => $point['lng'],
                'risk' => $risk,
                'risk_label' => $shakha->latestRiskAssessment?->risk_category ?: 'Not assessed',
                'score' => $score,
                'compliance_score' => $this->complianceScore($score, $risk),
                'reports' => (int) $shakha->audit_reports_count,
                'completed' => (int) $shakha->completed_reports_count,
                'findings_count' => (int) $shakha->audit_findings_count,
                'issues' => $issuesByShakha->get($shakha->id, []),
                'url' => $this->shakhaUrl($user, $shakha->id),
            ];
        }

        ksort($divisionCounts);

        return [
            'updated_at' => Carbon::now('Asia/Dhaka')->toIso8601String(),
            'fingerprint' => md5(json_encode([
                $shakhas->count(),
                $shakhas->max('updated_at'),
                $riskCounts,
            ]) ?: ''),
            'stats' => [
                'shakhas' => $shakhas->count(),
                'active' => $shakhas->where('status', 'active')->count(),
                'risk' => $riskCounts,
                'divisions' => $divisionCounts,
            ],
            'places' => BangladeshGazetteer::catalog(),
            'markers' => $markers,
        ];
    }

    private function riskKey(?string $category): string
    {
        return match ($category) {
            'Significant Risk' => 'significant',
            'High Risk' => 'high',
            'Medium Risk' => 'medium',
            'Low Risk' => 'low',
            default => 'unassessed',
        };
    }

    private function areaColor(?string $name): string
    {
        $seed = strtolower(trim((string) $name));
        if ($seed === '') {
            return '#64748b';
        }

        $hash = crc32($seed);
        $hue = $hash % 360;
        $sat = 58 + ($hash % 18);
        $light = 44 + ((int) ($hash / 360) % 10);

        return "hsl({$hue}, {$sat}%, {$light}%)";
    }

    private function shakhaUrl($user, int $id): ?string
    {
        if ($user?->can('shakhas.manage')) {
            return route('shakhas.edit', $id);
        }

        if ($user?->can('risk.manage')) {
            return route('shakhas.risk.create', $id);
        }

        return null;
    }

    /**
     * Invert the weighted risk score onto a 0–100 compliance scale for the drawer.
     */
    private function complianceScore(mixed $weighted, string $risk): ?int
    {
        if ($weighted !== null && $weighted !== '') {
            return max(0, min(100, 100 - ((int) $weighted * 8)));
        }

        return match ($risk) {
            'low' => 88,
            'medium' => 70,
            'high' => 52,
            'significant' => 35,
            default => null,
        };
    }

    /**
     * @param  Collection<int, int|string>  $shakhaIds
     * @return Collection<int, array<int, array<string, mixed>>>
     */
    private function priorityIssuesByShakha(Collection $shakhaIds): Collection
    {
        if ($shakhaIds->isEmpty()) {
            return collect();
        }

        return AuditFinding::query()
            ->with('indicator:id,indicator_code,title,risk_rating')
            ->whereIn('shakha_id', $shakhaIds)
            ->orderByDesc('amount')
            ->orderByDesc('irregularity_count')
            ->get()
            ->groupBy('shakha_id')
            ->mapWithKeys(fn (Collection $rows, $id) => [(int) $id => $rows])
            ->map(function (Collection $rows): array {
                return $rows
                    ->filter(fn (AuditFinding $finding) => $finding->hasIrregularity())
                    ->take(6)
                    ->map(fn (AuditFinding $finding) => [
                        'title' => $finding->indicator?->title
                            ?: ($finding->indicator?->indicator_code ?: 'Finding'),
                        'severity' => $finding->indicator?->risk_rating ?: 'medium',
                        'amount' => $finding->amount !== null ? (float) $finding->amount : null,
                        'count' => (int) ($finding->irregularity_count ?? 0),
                        'detail' => $finding->observation,
                    ])
                    ->values()
                    ->all();
            });
    }
}
