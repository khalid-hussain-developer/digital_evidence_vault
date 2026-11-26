@props(['class' => ''])

<span {{ $attributes->merge(['class' => 'd-inline-flex align-items-center gap-2 fw-semibold '.$class]) }}>
    <i class="bi bi-shield-lock-fill text-primary fs-4"></i>
    <span>Digital Evidence Vault</span>
</span>
