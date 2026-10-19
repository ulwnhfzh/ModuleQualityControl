@props([
    'name',
    'value' => '',
    'label' => null,
    'id' => null,
    'required' => false,
    'inputClass' => 'w-full rounded-lg border-slate-300 text-sm',
    'ariaLabel' => null,
])
@php
    $rawDateValue = (string) $value;
    $initialIsoDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawDateValue) ? $rawDateValue : '';
    $initialDisplayDate = $initialIsoDate
        ? \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $initialIsoDate)->format('d/m/Y')
        : $rawDateValue;
    $dateInputId = $id ?: 'date-entry-' . str_replace(['[', ']'], '-', $name) . '-' . \Illuminate\Support\Str::uuid();
@endphp
<div class="date-entry" data-date-entry>
    @if($label)
        <label for="{{ $dateInputId }}" class="mb-1 block">{{ $label }}</label>
    @endif
    <div class="flex min-w-0 items-stretch">
        <input type="hidden" name="{{ $name }}" value="{{ $initialIsoDate }}" data-date-value>
        <input
            id="{{ $dateInputId }}"
            type="text"
            value="{{ $initialDisplayDate }}"
            placeholder="DD/MM/YYYY, DDMMYYYY, atau DDMMYY"
            inputmode="text"
            autocomplete="off"
            data-date-text
            @if($required) required @endif
            @if($ariaLabel) aria-label="{{ $ariaLabel }}" @endif
            class="{{ $inputClass }} min-w-0 flex-1 rounded-r-none"
            {{ $attributes }}
        >
        <button type="button" data-date-open aria-label="Pilih tanggal dari kalender" title="Pilih tanggal dari kalender" class="inline-flex shrink-0 items-center justify-center rounded-r-lg border border-l-0 border-slate-300 bg-slate-50 px-3 text-slate-600 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <svg aria-hidden="true" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3.5" y="5" width="17" height="16" rx="2"/><path d="M7.5 3v4M16.5 3v4M3.5 9.5h17M8 13h.01M12 13h.01M16 13h.01M8 17h.01M12 17h.01" stroke-linecap="round"/></svg>
        </button>
        <input type="date" tabindex="-1" aria-hidden="true" data-date-picker class="pointer-events-none absolute h-px w-px opacity-0">
    </div>
    <p data-date-error class="mt-1 hidden text-xs text-rose-600">Tanggal tidak valid. Gunakan DD/MM/YYYY, DDMMYYYY, atau DDMMYY.</p>
</div>
