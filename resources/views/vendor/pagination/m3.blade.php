@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi Halaman" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; font-size: 13px; color: var(--md-sys-color-on-surface-variant);">
        
        <!-- Summary Info -->
        <div style="font-size: 12.5px; color: var(--md-sys-color-outline);">
            Menampilkan 
            <b style="color: var(--md-sys-color-on-surface);">{{ $paginator->firstItem() ?? 0 }}</b> 
            sampai 
            <b style="color: var(--md-sys-color-on-surface);">{{ $paginator->lastItem() ?? 0 }}</b> 
            dari 
            <b style="color: var(--md-sys-color-primary);">{{ $paginator->total() }}</b> 
            data
        </div>

        <!-- Page Numbers List -->
        <div style="display: flex; align-items: center; gap: 4px;">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 8px; border: 1px solid var(--md-sys-color-outline-variant); color: var(--md-sys-color-outline); opacity: 0.5; cursor: not-allowed;" aria-disabled="true">
                    <span class="material-symbols-rounded" style="font-size: 18px;">chevron_left</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 8px; border: 1px solid var(--md-sys-color-outline-variant); background-color: var(--md-sys-color-surface-container-lowest); color: var(--md-sys-color-on-surface); text-decoration: none; transition: all 0.2s ease;" title="Halaman Sebelumnya">
                    <span class="material-symbols-rounded" style="font-size: 18px;">chevron_left</span>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; color: var(--md-sys-color-outline);">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 8px; background-color: var(--md-sys-color-primary); color: var(--md-sys-color-on-primary); font-weight: 700; font-size: 13px; box-shadow: 0 1px 3px rgba(0,0,0,0.15);" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 8px; border: 1px solid var(--md-sys-color-outline-variant); background-color: var(--md-sys-color-surface-container-lowest); color: var(--md-sys-color-on-surface); text-decoration: none; font-size: 13px; font-weight: 500; transition: all 0.2s ease;" onmouseover="this.style.backgroundColor='var(--md-sys-color-primary-container)'; this.style.borderColor='var(--md-sys-color-primary)';" onmouseout="this.style.backgroundColor='var(--md-sys-color-surface-container-lowest)'; this.style.borderColor='var(--md-sys-color-outline-variant)';">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 8px; border: 1px solid var(--md-sys-color-outline-variant); background-color: var(--md-sys-color-surface-container-lowest); color: var(--md-sys-color-on-surface); text-decoration: none; transition: all 0.2s ease;" title="Halaman Selanjutnya">
                    <span class="material-symbols-rounded" style="font-size: 18px;">chevron_right</span>
                </a>
            @else
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 8px; border: 1px solid var(--md-sys-color-outline-variant); color: var(--md-sys-color-outline); opacity: 0.5; cursor: not-allowed;" aria-disabled="true">
                    <span class="material-symbols-rounded" style="font-size: 18px;">chevron_right</span>
                </span>
            @endif
        </div>

    </nav>
@endif
