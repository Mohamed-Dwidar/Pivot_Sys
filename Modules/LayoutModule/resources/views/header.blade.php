{{--
    Shared top bar. Variables (from the user-type layout):
    $homeUrl, $userName, $userSubtitle, $menuLinks [[label, icon, url]], $logoutUrl
--}}
<div class="box fixed inset-x-0 top-0 z-10 flex h-[65px] rounded-none border-x-0 border-t-0">
    <div class="side-menu__content brand-bar bg-white flex-none flex items-center z-10 px-5 h-full xl:w-[275px] overflow-hidden relative duration-300 group-[.side-menu--collapsed]:xl:w-[91px] group-[.side-menu--collapsed.side-menu--on-hover]:xl:w-[275px] group-[.side-menu--collapsed.side-menu--on-hover]:xl:shadow-[6px_0_12px_-4px_#0000001f] before:content-[''] before:hidden before:xl:block before:absolute before:right-0 before:border-r before:border-dashed before:border-slate-300/70 before:h-4/6 before:group-[.side-menu--collapsed.side-menu--on-hover]:xl:border-solid before:group-[.side-menu--collapsed.side-menu--on-hover]:xl:h-full">
        <a class="flex items-center" href="{{ $homeUrl }}">
            <img src="{{ asset('assets/images/logo.png') }}" alt="{{ config('app.name') }}" class="brand-logo">
            <img src="{{ asset('assets/images/favicon.jpg') }}" alt="{{ config('app.name') }}" class="brand-icon">
        </a>
        <a class="toggle-compact-menu ml-auto hidden h-[20px] w-[20px] items-center justify-center rounded-full border border-slate-600/40 transition-[opacity,transform] hover:bg-slate-600/5 group-[.side-menu--collapsed]:xl:rotate-180 xl:flex" href="javascript:;">
            <i data-tw-merge="" data-lucide="arrow-left" class="h-3.5 w-3.5 stroke-[1.3]"></i>
        </a>
        <div class="ml-auto flex items-center gap-1 xl:hidden">
            <a class="p-2 rounded-full open-mobile-menu hover:bg-slate-100" href="">
                <i data-tw-merge="" data-lucide="align-justify" class="stroke-[1] h-[18px] w-[18px]"></i>
            </a>
        </div>
    </div>
    <div class="absolute inset-x-0 h-full transition-[padding] duration-100 xl:pl-[275px] group-[.side-menu--collapsed]:xl:pl-[91px]">
        <div class="flex items-center w-full h-full px-5">
            <!-- BEGIN: Breadcrumb -->
            <nav aria-label="breadcrumb" class="flex flex-1 hidden xl:block">
                <ol class="flex items-center text-theme-1 dark:text-slate-300">
                    <li class="">
                        <a href="{{ $homeUrl }}">{{ config('app.name') }}</a>
                    </li>
                    <li class="relative ml-5 pl-0.5 before:content-[''] before:w-[14px] before:h-[14px] before:bg-chevron-black before:transform before:rotate-[-90deg] before:bg-[length:100%] before:-ml-[1.125rem] before:absolute before:my-auto before:inset-y-0 dark:before:bg-chevron-white text-slate-600 cursor-text dark:text-slate-400">
                        @yield('title')
                    </li>
                </ol>
            </nav>
            <!-- END: Breadcrumb -->
            <!-- BEGIN: User Menu -->
            <div class="flex items-center flex-1">
                <div class="flex items-center gap-1 ml-auto">
                    <a class="p-2 rounded-full request-full-screen hover:bg-slate-100" href="javascript:;">
                        <i data-tw-merge="" data-lucide="expand" class="stroke-[1] h-[18px] w-[18px]"></i>
                    </a>
                </div>
                <div data-tw-merge="" data-tw-placement="bottom-end" class="dropdown relative ml-5">
                    <button data-tw-toggle="dropdown" aria-expanded="false" class="cursor-pointer flex h-[36px] w-[36px] items-center justify-center overflow-hidden rounded-full border-[3px] border-slate-200/70 bg-primary font-medium text-white">
                        {{ mb_strtoupper(mb_substr($userName, 0, 1)) }}
                    </button>
                    <div data-transition="" data-selector=".show" data-enter="transition-all ease-linear duration-150" data-enter-from="absolute !mt-5 invisible opacity-0 translate-y-1" data-enter-to="!mt-1 visible opacity-100 translate-y-0" data-leave="transition-all ease-linear duration-150" data-leave-from="!mt-1 visible opacity-100 translate-y-0" data-leave-to="absolute !mt-5 invisible opacity-0 translate-y-1" class="dropdown-menu absolute z-[9999] hidden">
                        <div data-tw-merge="" class="dropdown-content rounded-md border-transparent bg-white p-2 shadow-[0px_3px_10px_#00000017] dark:border-transparent dark:bg-darkmode-600 w-56 mt-1">
                            <div class="p-2">
                                <div class="font-medium">{{ $userName }}</div>
                                <div class="mt-0.5 text-xs text-slate-500">{{ $userSubtitle }}</div>
                            </div>
                            <div class="h-px my-2 -mx-2 bg-slate-200/60 dark:bg-darkmode-400"></div>
                            @foreach ($menuLinks as $link)
                                <a href="{{ $link['url'] }}" class="cursor-pointer flex items-center p-2 transition duration-300 ease-in-out rounded-md hover:bg-slate-200/60 dark:bg-darkmode-600 dark:hover:bg-darkmode-400 dropdown-item">
                                    <i data-tw-merge="" data-lucide="{{ $link['icon'] }}" class="stroke-[1] w-4 h-4 mr-2"></i>
                                    {{ $link['label'] }}
                                </a>
                            @endforeach
                            <div class="h-px my-2 -mx-2 bg-slate-200/60 dark:bg-darkmode-400"></div>
                            <form method="POST" action="{{ $logoutUrl }}">
                                @csrf
                                <button type="submit" class="dropdown-item-button cursor-pointer flex items-center p-2 transition duration-300 ease-in-out rounded-md hover:bg-slate-200/60 dark:bg-darkmode-600 dark:hover:bg-darkmode-400 dropdown-item">
                                    <i data-tw-merge="" data-lucide="power" class="stroke-[1] w-4 h-4 mr-2"></i>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END: User Menu -->
        </div>
    </div>
</div>
