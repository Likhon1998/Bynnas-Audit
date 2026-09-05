{{-- Routes to the correct format editor. Expects: $formatModel, $definition, $payload --}}
@php
    $layout = $definition['layout'] ?? 'society_lifecycle';
    $code = $definition['code'] ?? $formatModel?->code;
@endphp

@if ($layout === 'savings_refund' || $code === 'format-5')
    @include('livewire.partials.audit-checklist-format-5-editor', compact('formatModel', 'definition', 'payload'))
@elseif ($layout === 'savings_loan_collection' || $code === 'format-4')
    @include('livewire.partials.audit-checklist-format-4-editor', compact('formatModel', 'definition', 'payload'))
@elseif ($layout === 'society_management' || $code === 'format-3')
    @include('livewire.partials.audit-checklist-format-3-editor', compact('formatModel', 'definition', 'payload'))
@elseif ($layout === 'member_admission' || $code === 'format-2')
    @include('livewire.partials.audit-checklist-format-2-editor', compact('formatModel', 'definition', 'payload'))
@else
    @include('livewire.partials.audit-checklist-format-1-editor', compact('formatModel', 'definition', 'payload'))
@endif
