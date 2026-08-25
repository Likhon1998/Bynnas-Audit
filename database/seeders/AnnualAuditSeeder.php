<?php

namespace Database\Seeders;

use App\Models\AuditPlan;
use App\Models\AuditPolicy;
use App\Models\HqDepartment;
use App\Models\PlanSchedule;
use App\Models\Project;
use App\Models\ProjectLocation;
use App\Models\StrategicPlanItem;
use App\Models\User;
use App\Services\AnnualPlanGenerator;
use Illuminate\Database\Seeder;

class AnnualAuditSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedProjects();
        $this->seedHqDepartments();
        $this->seedStrategicPlan();

        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->first();
        $generator = app(AnnualPlanGenerator::class);
        $plan = $generator->ensureFy2026Plan($admin?->id);

        // Align Project Audit policy with Excel (quarterly Jul/Oct/Jan/Apr)
        AuditPolicy::query()->updateOrCreate(
            ['audit_plan_id' => $plan->id, 'category' => AuditPolicy::CATEGORY_PROJECT_AUDIT],
            [
                'frequency_per_year' => 4,
                'interval_months' => 3,
                'pattern' => 'quarterly',
                'custom_month_indexes' => [0, 3, 6, 9],
                'notes' => 'Default quarterly (Jul/Oct/Jan/Apr) matching Excel Project Audit work plan.',
            ]
        );

        AuditPolicy::query()->updateOrCreate(
            ['audit_plan_id' => $plan->id, 'category' => AuditPolicy::CATEGORY_PKSF],
            [
                'frequency_per_year' => 4,
                'interval_months' => 3,
                'pattern' => 'quarterly',
                'custom_month_indexes' => [0, 3, 6, 9],
                'notes' => 'Excel PKSF & Maternity: 4 visits/year, staggered within each quarter.',
            ]
        );

        AuditPolicy::query()->updateOrCreate(
            ['audit_plan_id' => $plan->id, 'category' => AuditPolicy::CATEGORY_HQ],
            [
                'frequency_per_year' => 2,
                'interval_months' => 6,
                'pattern' => 'interval',
                'custom_month_indexes' => [5, 11],
                'notes' => 'Default twice yearly. Excel HQ uses staggered months per department.',
            ]
        );

        $generator->generate($plan->fresh('policies'));
        $this->applyExcelHqSchedules($plan->fresh());
        $this->applyExcelPksfSchedules($plan->fresh());
    }

    protected function seedProjects(): void
    {
        $projects = [
            [
                'name' => 'DSK-WASH Water Aid Project',
                'donor' => 'Water Aid',
                'has_project_audit' => true,
                'has_project_monitoring' => true,
                'locations' => [
                    ['name' => 'Dhaka', 'division' => 'Dhaka'],
                    ['name' => 'Chattogram', 'division' => 'Chattogram'],
                ],
            ],
            [
                'name' => 'DSK Public Toilet project',
                'donor' => 'DSK',
                'has_project_audit' => true,
                'has_project_monitoring' => true,
                'locations' => [
                    ['name' => 'Dhaka', 'division' => 'Dhaka'],
                    ['name' => 'Chattogram', 'division' => 'Chattogram'],
                ],
            ],
            [
                'name' => 'DSK-Billing Project',
                'donor' => 'DSK',
                'has_project_audit' => true,
                'has_project_monitoring' => true,
                'locations' => [
                    ['name' => 'Dhaka', 'division' => 'Dhaka'],
                ],
            ],
            [
                'name' => 'DSK-Vacutug Project',
                'donor' => 'DSK',
                'has_project_audit' => true,
                'has_project_monitoring' => true,
                'locations' => [
                    ['name' => 'Mirpur-10, Dhaka', 'division' => 'Dhaka'],
                ],
            ],
            [
                'name' => 'DSK-Water 1st International',
                'donor' => 'International Donor',
                'has_project_audit' => true,
                'has_project_monitoring' => true,
                'locations' => [
                    ['name' => 'Savar (Unit Office)', 'division' => 'Dhaka'],
                    ['name' => 'Keranigonj (Unit Office)', 'division' => 'Dhaka'],
                    ['name' => 'Tarabo (Unit Office)', 'division' => 'Dhaka'],
                    ['name' => 'Chattogram (Unit Office)', 'division' => 'Chattogram'],
                    ['name' => 'Dhaka (PMO)', 'division' => 'Dhaka'],
                    ['name' => 'Khulna', 'division' => 'Khulna'],
                ],
            ],
            [
                'name' => 'DSK-Oxfam-SUNITI Project',
                'donor' => 'Oxfam',
                'has_project_audit' => true,
                'has_project_monitoring' => true,
                'locations' => [
                    ['name' => 'Mohammadpur', 'division' => 'Dhaka'],
                    ['name' => 'Khilgaon', 'division' => 'Dhaka'],
                    ['name' => 'Badda', 'division' => 'Dhaka'],
                ],
            ],
            [
                'name' => 'DWASA ADF Project',
                'donor' => 'DWASA',
                'has_project_audit' => true,
                'has_project_monitoring' => true,
                'locations' => [
                    ['name' => 'Dhaka', 'division' => 'Dhaka'],
                ],
            ],
            [
                'name' => 'DSK-DPHE Project',
                'donor' => 'DPHE',
                'has_project_audit' => true,
                'has_project_monitoring' => true,
                'locations' => [
                    ['name' => 'Tarabo', 'division' => 'Dhaka'],
                    ['name' => 'Debidar', 'division' => 'Chattogram'],
                    ['name' => 'Homna', 'division' => 'Chattogram'],
                ],
            ],
            [
                'name' => 'WOP-3 Project',
                'donor' => 'WOP',
                'has_project_audit' => true,
                'has_project_monitoring' => true,
                'locations' => [
                    ['name' => 'Dhaka', 'division' => 'Dhaka'],
                ],
            ],
            [
                'name' => 'UNICEF Pipe Networking Project Camp-16',
                'donor' => 'UNICEF',
                'has_project_audit' => true,
                'has_project_monitoring' => true,
                'locations' => [
                    ['name' => 'Ukhia', 'division' => 'Chattogram'],
                ],
            ],
            [
                'name' => 'DSK-UNICEF Urban WASH CXB',
                'donor' => 'UNICEF',
                'has_project_audit' => true,
                'has_project_monitoring' => true,
                'locations' => [
                    ['name' => 'Cox\'s Bazar', 'division' => 'Chattogram'],
                ],
            ],
            [
                'name' => 'UNICEF WASH Emergency camp 22',
                'donor' => 'UNICEF',
                'has_project_audit' => true,
                'has_project_monitoring' => true,
                'locations' => [
                    ['name' => 'Teknaf', 'division' => 'Chattogram'],
                ],
            ],
            [
                'name' => 'UNICEF WASH Emergency camp 16',
                'donor' => 'UNICEF',
                'has_project_audit' => true,
                'has_project_monitoring' => true,
                'locations' => [
                    ['name' => 'Ukhiya', 'division' => 'Chattogram'],
                ],
            ],
            [
                'name' => 'ADB Project',
                'donor' => 'ADB',
                'has_project_audit' => true,
                'has_project_monitoring' => true,
                'locations' => [
                    ['name' => 'Ukhiya', 'division' => 'Chattogram'],
                ],
            ],
            [
                'name' => 'KNH-BMZ Project',
                'donor' => 'KNH-BMZ',
                'has_project_audit' => true,
                'has_project_monitoring' => true,
                'locations' => [
                    ['name' => 'Ukhiya', 'division' => 'Chattogram'],
                ],
            ],
            [
                'name' => 'RMTP',
                'donor' => 'PKSF',
                'is_pksf' => true,
                'has_project_audit' => false,
                'has_project_monitoring' => false,
                'locations' => [
                    ['name' => 'Bharhatta, Netrokona', 'division' => 'Mymensingh'],
                ],
            ],
            [
                'name' => 'RAISE',
                'donor' => 'PKSF',
                'is_pksf' => true,
                'has_project_audit' => false,
                'has_project_monitoring' => false,
                'locations' => [
                    ['name' => 'Khilkhet, Dhaka', 'division' => 'Dhaka'],
                ],
            ],
            [
                'name' => 'PPEPP',
                'donor' => 'PKSF',
                'is_pksf' => true,
                'has_project_audit' => false,
                'has_project_monitoring' => false,
                'locations' => [
                    ['name' => 'Kishorgonj', 'division' => 'Dhaka'],
                ],
            ],
            [
                'name' => 'DSK NFPE',
                'donor' => 'PKSF',
                'is_pksf' => true,
                'has_project_audit' => false,
                'has_project_monitoring' => false,
                'locations' => [
                    ['name' => 'Durgapur, Netrokona', 'division' => 'Mymensingh'],
                ],
            ],
            [
                'name' => 'ENRICH',
                'donor' => 'PKSF',
                'is_pksf' => true,
                'has_project_audit' => false,
                'has_project_monitoring' => false,
                'locations' => [
                    ['name' => 'Durgapur, Netrokona', 'division' => 'Mymensingh'],
                ],
            ],
            [
                'name' => 'Adolescent Program',
                'donor' => 'PKSF',
                'is_pksf' => true,
                'has_project_audit' => false,
                'has_project_monitoring' => false,
                'locations' => [
                    ['name' => 'Durgapur & Karimganj', 'division' => 'Mymensingh'],
                ],
            ],
            [
                'name' => 'DSK-Hospital Dhaka',
                'donor' => 'DSK',
                'is_maternity' => true,
                'has_project_audit' => false,
                'has_project_monitoring' => false,
                'locations' => [
                    ['name' => 'Shyamoli, Dhaka', 'division' => 'Dhaka'],
                ],
            ],
            [
                'name' => 'DSK Maternity Hospital',
                'donor' => 'DSK',
                'is_maternity' => true,
                'has_project_audit' => false,
                'has_project_monitoring' => false,
                'locations' => [
                    ['name' => 'Durgapur, Netrokona', 'division' => 'Mymensingh'],
                ],
            ],
            [
                'name' => 'DSK Gajaria Matri Sadan',
                'donor' => 'DSK',
                'is_maternity' => true,
                'has_project_audit' => false,
                'has_project_monitoring' => false,
                'locations' => [
                    ['name' => 'Gajaria, Gazipur', 'division' => 'Dhaka'],
                ],
            ],
        ];

        $excelPksfNames = [
            'RMTP',
            'RAISE',
            'PPEPP',
            'DSK NFPE',
            'ENRICH',
            'Adolescent Program',
            'DSK-Hospital Dhaka',
            'DSK Maternity Hospital',
            'DSK Gajaria Matri Sadan',
        ];

        // Hide older PKSF/maternity samples not in Excel sheet
        Project::query()
            ->where(fn ($q) => $q->where('is_pksf', true)->orWhere('is_maternity', true))
            ->whereNotIn('name', $excelPksfNames)
            ->update(['status' => 'inactive']);

        foreach ($projects as $data) {
            $locations = $data['locations'];
            unset($data['locations']);

            $project = Project::query()->updateOrCreate(
                ['name' => $data['name']],
                $data + [
                    'status' => 'active',
                    'is_pksf' => $data['is_pksf'] ?? false,
                    'is_maternity' => $data['is_maternity'] ?? false,
                    'has_project_audit' => $data['has_project_audit'] ?? false,
                    'has_project_monitoring' => $data['has_project_monitoring'] ?? false,
                ]
            );

            foreach ($locations as $location) {
                ProjectLocation::query()->updateOrCreate(
                    ['project_id' => $project->id, 'name' => $location['name']],
                    ['division' => $location['division'], 'status' => 'active']
                );
            }
        }
    }

    protected function seedHqDepartments(): void
    {
        // Match Excel HQ sheet departments
        $excelDepartments = [
            'HR and Admin Department',
            'Finance and Procurement Department including projects and PF and Gratuity',
            'DSK - Mart',
            'Training Department',
        ];

        // Soft-hide older sample departments so Excel list stays clean
        HqDepartment::query()
            ->whereNotIn('name', $excelDepartments)
            ->update(['status' => 'inactive']);

        foreach ($excelDepartments as $index => $name) {
            HqDepartment::query()->updateOrCreate(
                ['name' => $name],
                ['status' => 'active', 'sort_order' => $index + 1]
            );
        }
    }

    /**
     * Apply Excel-style staggered HQ months (2 visits/year per department).
     */
    protected function applyExcelHqSchedules(AuditPlan $plan): void
    {
        $fy = \App\Support\FinancialYear::fromLabel($plan->fy_label);

        $patterns = [
            'HR and Admin Department' => [5, 11], // Dec, Jun
            'Finance and Procurement Department including projects and PF and Gratuity' => [5, 11],
            'DSK - Mart' => [2, 7], // Sep, Feb
            'Training Department' => [4, 9], // Nov, Apr
        ];

        foreach ($patterns as $name => $monthIndexes) {
            $department = HqDepartment::query()->where('name', $name)->where('status', 'active')->first();
            if (! $department) {
                continue;
            }

            PlanSchedule::query()
                ->where('audit_plan_id', $plan->id)
                ->where('category', AuditPolicy::CATEGORY_HQ)
                ->where('schedulable_type', HqDepartment::class)
                ->where('schedulable_id', $department->id)
                ->delete();

            foreach ($monthIndexes as $occurrence => $monthIndex) {
                PlanSchedule::query()->create([
                    'audit_plan_id' => $plan->id,
                    'category' => AuditPolicy::CATEGORY_HQ,
                    'schedulable_type' => HqDepartment::class,
                    'schedulable_id' => $department->id,
                    'month_index' => $monthIndex,
                    'planned_date' => $fy->dateForMonthIndex($monthIndex)->toDateString(),
                    'occurrence' => $occurrence + 1,
                    'status' => 'planned',
                    'is_manual' => true,
                    'remarks' => 'Excel HQ pattern',
                ]);
            }
        }
    }

    /**
     * Excel PKSF & Maternity: 4 visits/year, staggered by row group.
     * Rows 1–3: Jul/Oct/Jan/Apr | 4–6: Aug/Nov/Feb/May | 7–9: Sep/Dec/Mar/Jun
     */
    protected function applyExcelPksfSchedules(AuditPlan $plan): void
    {
        $fy = \App\Support\FinancialYear::fromLabel($plan->fy_label);

        $patterns = [
            'RMTP' => [0, 3, 6, 9],
            'RAISE' => [0, 3, 6, 9],
            'PPEPP' => [0, 3, 6, 9],
            'DSK NFPE' => [1, 4, 7, 10],
            'ENRICH' => [1, 4, 7, 10],
            'Adolescent Program' => [1, 4, 7, 10],
            'DSK-Hospital Dhaka' => [2, 5, 8, 11],
            'DSK Maternity Hospital' => [2, 5, 8, 11],
            'DSK Gajaria Matri Sadan' => [2, 5, 8, 11],
        ];

        foreach ($patterns as $name => $monthIndexes) {
            $project = Project::query()->where('name', $name)->where('status', 'active')->first();
            if (! $project) {
                continue;
            }

            foreach ($project->locations as $location) {
                PlanSchedule::query()
                    ->where('audit_plan_id', $plan->id)
                    ->where('category', AuditPolicy::CATEGORY_PKSF)
                    ->where('schedulable_type', ProjectLocation::class)
                    ->where('schedulable_id', $location->id)
                    ->delete();

                foreach ($monthIndexes as $occurrence => $monthIndex) {
                    PlanSchedule::query()->create([
                        'audit_plan_id' => $plan->id,
                        'category' => AuditPolicy::CATEGORY_PKSF,
                        'schedulable_type' => ProjectLocation::class,
                        'schedulable_id' => $location->id,
                        'month_index' => $monthIndex,
                        'planned_date' => $fy->dateForMonthIndex($monthIndex)->toDateString(),
                        'occurrence' => $occurrence + 1,
                        'status' => 'planned',
                        'is_manual' => true,
                        'remarks' => 'Excel PKSF & Maternity pattern',
                    ]);
                }
            }
        }
    }

    protected function seedStrategicPlan(): void
    {
        $items = [
            ['sl_no' => 1, 'targeted_development' => 'Strengthen branch audit coverage', 'year_1' => 'Plan', 'year_2' => 'Rollout', 'year_3' => 'Scale', 'year_4' => 'Review', 'year_5' => 'Sustain'],
            ['sl_no' => 2, 'targeted_development' => 'Digitize audit execution tracking', 'year_1' => 'Spec', 'year_2' => 'Build', 'year_3' => 'Pilot', 'year_4' => 'Adopt', 'year_5' => 'Optimize'],
            ['sl_no' => 3, 'targeted_development' => 'Improve Area Office quarterly reviews', 'year_1' => 'Design', 'year_2' => 'Train', 'year_3' => 'Monitor', 'year_4' => 'Improve', 'year_5' => 'Embed'],
            ['sl_no' => 4, 'targeted_development' => 'Integrate project audit & monitoring calendars', 'year_1' => 'Map', 'year_2' => 'Align', 'year_3' => 'Unify', 'year_4' => 'Report', 'year_5' => 'Govern'],
            ['sl_no' => 5, 'targeted_development' => 'Build auditor capacity & rotation policy', 'year_1' => 'Assess', 'year_2' => 'Curriculum', 'year_3' => 'Deliver', 'year_4' => 'Certify', 'year_5' => 'Refresh'],
        ];

        foreach ($items as $item) {
            StrategicPlanItem::query()->updateOrCreate(
                ['sl_no' => $item['sl_no']],
                $item + ['status' => 'planned']
            );
        }
    }
}
