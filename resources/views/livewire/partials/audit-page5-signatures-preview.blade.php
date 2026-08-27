<div class="a4-sheet">
    <div class="a4-inner official-preview text-black">
        <div class="a4-body">
            @include('livewire.partials.audit-signatures-block', [
                'sign_auditor_name' => $sign_auditor_name,
                'sign_auditor_designation' => $sign_auditor_designation,
                'sign_auditor_date' => $sign_auditor_date,
                'sign_bm_name' => $sign_bm_name,
                'sign_bm_date' => $sign_bm_date,
                'sign_abm_name' => $sign_abm_name,
                'sign_abm_date' => $sign_abm_date,
            ])
        </div>

        <p class="a4-page-num">5</p>
    </div>
</div>
