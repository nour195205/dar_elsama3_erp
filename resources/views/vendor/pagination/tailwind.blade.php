@if ($paginator->hasPages())
    <nav role="navigation" aria-label="التنقل بين الصفحات" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">

        {{-- Results info --}}
        <div style="font-size: 0.85rem; color: var(--text-muted);">
            عرض
            @if ($paginator->firstItem())
                <span style="font-weight: 600; color: #fff;">{{ $paginator->firstItem() }}</span>
                إلى
                <span style="font-weight: 600; color: #fff;">{{ $paginator->lastItem() }}</span>
            @else
                {{ $paginator->count() }}
            @endif
            من أصل
            <span style="font-weight: 600; color: #fff;">{{ $paginator->total() }}</span>
            نتيجة
        </div>

        {{-- Page buttons --}}
        <div style="display: flex; align-items: center; gap: 0.35rem; direction: ltr;">

            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 10px; background: rgba(255,255,255,0.04); border: 1px solid var(--border-color); color: rgba(255,255,255,0.2); cursor: not-allowed;">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 10px; background: rgba(255,255,255,0.06); border: 1px solid var(--border-color); color: var(--text-muted); text-decoration: none; transition: all 0.2s;" onmouseenter="this.style.background='rgba(0,230,153,0.15)';this.style.color='var(--primary)';this.style.borderColor='rgba(0,230,153,0.3)'" onmouseleave="this.style.background='rgba(255,255,255,0.06)';this.style.color='var(--text-muted)';this.style.borderColor='var(--border-color)'">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                </a>
            @endif

            {{-- Page Numbers --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span style="display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 4px; font-size: 0.85rem; color: rgba(255,255,255,0.25); cursor: default;">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span style="display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 4px; border-radius: 10px; background: linear-gradient(135deg, var(--primary), #00b377); color: #000; font-size: 0.85rem; font-weight: 700; box-shadow: 0 4px 15px var(--primary-glow); cursor: default;">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" style="display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 4px; border-radius: 10px; background: rgba(255,255,255,0.04); border: 1px solid var(--border-color); color: var(--text-muted); font-size: 0.85rem; font-weight: 500; text-decoration: none; transition: all 0.2s;" onmouseenter="this.style.background='rgba(0,230,153,0.1)';this.style.color='var(--primary)';this.style.borderColor='rgba(0,230,153,0.25)'" onmouseleave="this.style.background='rgba(255,255,255,0.04)';this.style.color='var(--text-muted)';this.style.borderColor='var(--border-color)'">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 10px; background: rgba(255,255,255,0.06); border: 1px solid var(--border-color); color: var(--text-muted); text-decoration: none; transition: all 0.2s;" onmouseenter="this.style.background='rgba(0,230,153,0.15)';this.style.color='var(--primary)';this.style.borderColor='rgba(0,230,153,0.3)'" onmouseleave="this.style.background='rgba(255,255,255,0.06)';this.style.color='var(--text-muted)';this.style.borderColor='var(--border-color)'">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                </a>
            @else
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 10px; background: rgba(255,255,255,0.04); border: 1px solid var(--border-color); color: rgba(255,255,255,0.2); cursor: not-allowed;">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                </span>
            @endif
        </div>
    </nav>
@endif
