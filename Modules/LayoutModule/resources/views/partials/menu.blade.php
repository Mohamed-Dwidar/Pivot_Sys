{{--
    Renders sidebar items. Each item is one of:
      ['divider' => 'Group title']
      ['title' => '', 'icon' => 'lucide-name', 'url' => '', 'active' => bool, 'badge' => int|null]
      ['title' => '', 'icon' => 'lucide-name', 'children' => [items like above]]   (drop list)
    A drop list is opened and marked active when one of its children is active.
--}}
@foreach ($items as $item)
    @if (isset($item['divider']))
        <li class="side-menu__divider">
            {{ $item['divider'] }}
        </li>
    @elseif (isset($item['children']))
        @php($open = collect($item['children'])->contains('active', true))
        <li>
            <a href="javascript:;" class="side-menu__link {{ $open ? 'side-menu__link--active side-menu__link--active-dropdown' : '' }}">
                <i data-tw-merge="" data-lucide="{{ $item['icon'] }}" class="stroke-[1] w-5 h-5 side-menu__link__icon"></i>
                <div class="side-menu__link__title">{{ $item['title'] }}</div>
                @if (collect($item['children'])->sum('badge'))
                    <div class="side-menu__link__badge">{{ collect($item['children'])->sum('badge') }}</div>
                @endif
                <i data-tw-merge="" data-lucide="chevron-down" class="stroke-[1] w-5 h-5 side-menu__link__chevron {{ $open ? 'transform rotate-180' : '' }}"></i>
            </a>
            <ul class="{{ $open ? 'block' : 'hidden' }}">
                @include('layoutmodule::partials.menu', ['items' => $item['children']])
            </ul>
        </li>
    @else
        <li>
            <a href="{{ $item['url'] }}" class="side-menu__link {{ !empty($item['active']) ? 'side-menu__link--active' : '' }}">
                <i data-tw-merge="" data-lucide="{{ $item['icon'] }}" class="stroke-[1] w-5 h-5 side-menu__link__icon"></i>
                <div class="side-menu__link__title">{{ $item['title'] }}</div>
                @if (!empty($item['badge']))
                    <div class="side-menu__link__badge">{{ $item['badge'] }}</div>
                @endif
            </a>
        </li>
    @endif
@endforeach
