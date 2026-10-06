{{--
    Layout of the popup (#app-modal in main.blade.php). A page opened with <a href="..." data-modal> is loaded by ajax
    and renders with this layout instead of the full page:
    @extends(request()->ajax() ? 'layoutmodule::modal' : 'layoutmodule::account.main')
    Sections: title, actions (header buttons), content, modal-size (md | lg | xl, default md).
--}}
<div data-modal-size="@yield('modal-size', 'md')">
    <div class="flex items-center gap-3 px-5 py-3 border-b border-slate-200/60 dark:border-darkmode-400">
        <h2 class="mr-auto text-base font-medium">@yield('title')</h2>
        <div class="app-modal__actions flex flex-wrap items-center">
            @yield('actions')
        </div>
        <button type="button" data-tw-dismiss="modal" class="app-modal__close" title="Close"><i data-lucide="x"></i></button>
    </div>
    <div class="p-5">
        @yield('content')
    </div>
</div>
