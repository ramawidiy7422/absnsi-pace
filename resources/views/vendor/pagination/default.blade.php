@if ($paginator->hasPages())
<nav style="display:flex;gap:4px;align-items:center;flex-wrap:wrap;">
    {{-- Prev --}}
    @if ($paginator->onFirstPage())
        <span style="padding:5px 11px;border-radius:7px;border:1px solid var(--border,#e2e8f0);background:var(--bg-card,#fff);color:#cbd5e0;font-size:13px;cursor:not-allowed;">‹</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}"
           style="padding:5px 11px;border-radius:7px;border:1px solid var(--border,#e2e8f0);background:var(--bg-card,#fff);color:var(--text-sub,#4a5568);font-size:13px;text-decoration:none;transition:all .15s;"
           onmouseover="this.style.background='#4299e1';this.style.color='white';this.style.borderColor='#4299e1';"
           onmouseout="this.style.background='var(--bg-card,#fff)';this.style.color='var(--text-sub,#4a5568)';this.style.borderColor='var(--border,#e2e8f0)';">
            ‹
        </a>
    @endif

    {{-- Pages --}}
    @foreach ($elements as $element)
        @if (is_string($element))
            <span style="padding:5px 8px;color:#94a3b8;font-size:13px;">…</span>
        @endif
        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span style="padding:5px 11px;border-radius:7px;background:#4299e1;color:white;font-size:13px;font-weight:700;border:1px solid #4299e1;">{{ $page }}</span>
                @else
                    <a href="{{ $url }}"
                       style="padding:5px 11px;border-radius:7px;border:1px solid var(--border,#e2e8f0);background:var(--bg-card,#fff);color:var(--text-sub,#4a5568);font-size:13px;text-decoration:none;transition:all .15s;"
                       onmouseover="this.style.background='#4299e1';this.style.color='white';this.style.borderColor='#4299e1';"
                       onmouseout="this.style.background='var(--bg-card,#fff)';this.style.color='var(--text-sub,#4a5568)';this.style.borderColor='var(--border,#e2e8f0)';">
                        {{ $page }}
                    </a>
                @endif
            @endforeach
        @endif
    @endforeach

    {{-- Next --}}
    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}"
           style="padding:5px 11px;border-radius:7px;border:1px solid var(--border,#e2e8f0);background:var(--bg-card,#fff);color:var(--text-sub,#4a5568);font-size:13px;text-decoration:none;transition:all .15s;"
           onmouseover="this.style.background='#4299e1';this.style.color='white';this.style.borderColor='#4299e1';"
           onmouseout="this.style.background='var(--bg-card,#fff)';this.style.color='var(--text-sub,#4a5568)';this.style.borderColor='var(--border,#e2e8f0)';">
            ›
        </a>
    @else
        <span style="padding:5px 11px;border-radius:7px;border:1px solid var(--border,#e2e8f0);background:var(--bg-card,#fff);color:#cbd5e0;font-size:13px;cursor:not-allowed;">›</span>
    @endif
</nav>
@endif
