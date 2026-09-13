<?php

namespace App\Livewire;

use App\Services\AuditReportReviewService;
use Livewire\Attributes\Url;
use Livewire\Component;

class AuditorsLogPanel extends Component
{
    public string $mode = 'pipeline';

    #[Url(as: 'month', except: '')]
    public string $month = '';

    #[Url(as: 'year', except: '')]
    public string $year = '';

    #[Url(as: 'auditor_id', except: '')]
    public string $auditorId = '';

    #[Url(as: 'reviewer_id', except: '')]
    public string $reviewerId = '';

    #[Url(as: 'position', except: '')]
    public string $position = '';

    #[Url(as: 'q', except: '')]
    public string $q = '';

    public function mount(string $mode = 'pipeline'): void
    {
        $this->mode = in_array($mode, ['pipeline', 'activity'], true) ? $mode : 'pipeline';
    }

    public function setPosition(string $position): void
    {
        $this->position = $this->position === $position ? '' : $position;
    }

    public function clearFilters(): void
    {
        $this->month = '';
        $this->year = '';
        $this->auditorId = '';
        $this->reviewerId = '';
        $this->position = '';
        $this->q = '';
    }

    public function render(AuditReportReviewService $reviews)
    {
        $log = $reviews->auditorLog(
            $this->month !== '' ? (int) $this->month : null,
            $this->year !== '' ? (int) $this->year : null,
            $this->auditorId !== '' ? (int) $this->auditorId : null,
            $this->reviewerId !== '' ? (int) $this->reviewerId : null,
            $this->position !== '' ? $this->position : null,
            $this->q !== '' ? $this->q : null,
        );

        return view('livewire.auditors-log-panel', [
            'log' => $log,
            'now' => bd_now(),
        ]);
    }
}
