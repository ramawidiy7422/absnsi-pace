@if ($paginator->hasPages())
<nav style="display:flex;gap:4px;align-items:center;">
    @if ($paginator->onFirstPage())
        <span style="padding:5px 11px;border-radius:7px;border:1px solid var(--border,#e2e8f0);background:var(--bg-card,#fff);color:#cbd5e0;font-size:13px;cursor:not-allowed;">‹ Sebelumnya</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}"
           style="padding:5px 11px;border-radius:7px;border:1px solid var(--border,#e2e8f0);background:var(--bg-card,#fff);color:var(--text-sub,#4a5568);font-size:13px;text-decoration:none;">
            ‹ Sebelumnya
        </a>
    @endif

    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}"
           style="padding:5px 11px;border-radius:7px;border:1px solid var(--border,#e2e8f0);background:var(--bg-card,#fff);color:var(--text-sub,#4a5568);font-size:13px;text-decoration:none;">
            Selanjutnya ›
        </a>
    @else
        <span style="padding:5px 11px;border-radius:7px;border:1px solid var(--border,#e2e8f0);background:var(--bg-card,#fff);color:#cbd5e0;font-size:13px;cursor:not-allowed;">Selanjutnya ›</span>
    @endif
</nav>
@endif
