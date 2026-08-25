{{-- Variante « précédent / suivant » de la pagination ePharma. --}}
@if ($paginator->hasPages())
    <nav class="ep-pager" role="navigation" aria-label="Pagination">
        <ul class="ep-pager__list">
            @if ($paginator->onFirstPage())
                <li><span class="ep-pager__link is-disabled" aria-disabled="true">Précédent</span></li>
            @else
                <li><a class="ep-pager__link" href="{{ $paginator->previousPageUrl() }}" rel="prev">Précédent</a></li>
            @endif

            @if ($paginator->hasMorePages())
                <li><a class="ep-pager__link" href="{{ $paginator->nextPageUrl() }}" rel="next">Suivant</a></li>
            @else
                <li><span class="ep-pager__link is-disabled" aria-disabled="true">Suivant</span></li>
            @endif
        </ul>
    </nav>
@endif
