{{--
    Pagination du système de design ePharma.

    La vue Laravel par défaut est écrite pour Tailwind, absent de ce projet :
    ses classes utilitaires ne s'appliquaient pas et les chevrons SVG
    s'étiraient sur plusieurs centaines de pixels. Celle-ci n'utilise que les
    classes maison, et parle français.
--}}
@if ($paginator->hasPages())
    <nav class="ep-pager" role="navigation" aria-label="Pagination">
        <p class="ep-small ep-pager__count">
            {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} sur {{ $paginator->total() }}
            {{ $paginator->total() > 1 ? 'résultats' : 'résultat' }}
        </p>

        <ul class="ep-pager__list">
            @if ($paginator->onFirstPage())
                <li><span class="ep-pager__link is-disabled" aria-disabled="true">Précédent</span></li>
            @else
                <li><a class="ep-pager__link" href="{{ $paginator->previousPageUrl() }}" rel="prev">Précédent</a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="ep-pager__gap" aria-hidden="true">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li><span class="ep-pager__link is-current" aria-current="page">{{ $page }}</span></li>
                        @else
                            <li><a class="ep-pager__link" href="{{ $url }}" aria-label="Page {{ $page }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li><a class="ep-pager__link" href="{{ $paginator->nextPageUrl() }}" rel="next">Suivant</a></li>
            @else
                <li><span class="ep-pager__link is-disabled" aria-disabled="true">Suivant</span></li>
            @endif
        </ul>
    </nav>
@endif
