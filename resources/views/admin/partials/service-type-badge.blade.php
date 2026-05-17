@php
    $type = $serviceType ?? 'regular';
    $classes = match ($type) {
        'non_stop' => 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-400',
        'express'  => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        'regular'  => 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400',
        default    => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
    };
    $label = match ($type) {
        'non_stop' => 'Non-Stop',
        'express'  => 'Express',
        'regular'  => 'Regular',
        default    => ucfirst($type),
    };
@endphp
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $classes }}">
    {{ $label }}
</span>
