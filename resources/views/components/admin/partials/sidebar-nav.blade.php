@props(['menu'])

<nav class="space-y-4">
    @foreach($menu as $section)
        <div>
            <div class="px-4 mb-2 text-xs font-semibold tracking-wider text-gray-400 uppercase">
                {{ $section['title'] }}
            </div>

            <div class="space-y-2">
                @foreach($section['items'] as $item)
                    <x-admin.sidebar-link
                        :href="$item['href']"
                        :icon="$item['icon']"
                        :label="$item['label']"
                        :active="$item['active']"
                        :badge="$item['badge'] ?? null"
                    />
                @endforeach
            </div>
        </div>
    @endforeach
</nav>
