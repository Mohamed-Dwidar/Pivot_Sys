<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - @yield('title')</title>

    <link rel="icon" href="{{ asset('assets/images/favicon.jpg') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('assets/css/vendors/simplebar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/vendors/zoom-vanilla.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/themes/dagger.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    {{-- page vendor styles (e.g. DataTables) before custom.css, so custom.css can override them --}}
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
    @yield('head')
</head>

<body>
    <div class="dagger before:content-[''] before:bg-gradient-to-b before:from-slate-100 before:to-slate-50 before:fixed before:inset-0">
        <div class="h-screen relative loading-page loading-page--before-hide [&.loading-page--before-hide]:before:block [&.loading-page--hide]:before:opacity-0 before:content-[''] before:transition-opacity before:duration-300 before:hidden before:inset-0 before:h-screen before:w-screen before:fixed before:bg-gradient-to-b before:from-theme-1 before:to-theme-2 before:z-[60] [&.loading-page--before-hide]:after:block [&.loading-page--hide]:after:opacity-0 after:content-[''] after:transition-opacity after:duration-300 after:hidden after:h-16 after:w-16 after:animate-pulse after:fixed after:opacity-50 after:inset-0 after:m-auto after:bg-loading-puff after:bg-cover after:z-[61]">
            <div class="fixed top-0 left-0 z-50 h-screen side-menu group">
                @yield('header')
                @yield('sidebar')
            </div>

            <div class="content transition-[margin,width] duration-100 px-5 mt-[65px] pt-[31px] pb-16 relative z-10 xl:ml-[275px] [&.content--compact]:xl:ml-[91px]">
                <div class="grid grid-cols-12 gap-x-6 gap-y-10">
                    <div class="col-span-12">
                        <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                            <div class="text-base font-medium">
                                @yield('title')
                            </div>
                            <div class="flex flex-wrap gap-2 md:ml-auto">
                                @yield('actions')
                            </div>
                        </div>

                        @include('layoutmodule::flash')

                        <div class="box box--stacked mt-3.5 flex flex-col p-5">
                            @yield('content')
                        </div>

                        @include('layoutmodule::footer')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/vendors/dom.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/tailwind-merge.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/lucide.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/popper.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/dropdown.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/transition.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/simplebar.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/image-zoom.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/modal.js') }}"></script>
    <script src="{{ asset('assets/js/components/base/lucide.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    <script src="{{ asset('assets/js/themes/dagger.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('scripts')
</body>

</html>
