@extends('layoutmodule::login')

@section('content')
    <div class="mt-10">
        <div class="text-2xl font-medium">Sign In</div>
        <div class="mt-2.5 text-slate-600">Admin Panel Login</div>

        @if ($errors->any())
            <div role="alert" class="alert relative border rounded-md px-5 py-4 bg-danger border-danger bg-opacity-20 border-opacity-5 text-danger dark:border-danger dark:border-opacity-20 mt-7 flex items-center">
                <i data-tw-merge="" data-lucide="alert-octagon" class="stroke-[1] w-6 h-6 mr-2"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.loginpost') }}" id="form-login" class="mt-6">
            @csrf
            <label data-tw-merge="" for="email" class="inline-block mb-2">
                Email*
            </label>
            <input data-tw-merge="" type="email" name="email" id="email" value="{{ old('email') }}" placeholder="Email Address" required autofocus class="disabled:bg-slate-100 disabled:cursor-not-allowed dark:disabled:bg-darkmode-800/50 dark:disabled:border-transparent [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 [&[readonly]]:dark:border-transparent transition duration-200 ease-in-out w-full text-sm shadow-sm placeholder:text-slate-400/90 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 dark:placeholder:text-slate-500/80 block rounded-[0.6rem] border-slate-300/80 px-4 py-3.5">

            <label data-tw-merge="" for="password" class="inline-block mb-2 mt-4">
                Password*
            </label>
            <input data-tw-merge="" type="password" name="password" id="password" placeholder="************" required class="disabled:bg-slate-100 disabled:cursor-not-allowed dark:disabled:bg-darkmode-800/50 dark:disabled:border-transparent [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 [&[readonly]]:dark:border-transparent transition duration-200 ease-in-out w-full text-sm shadow-sm placeholder:text-slate-400/90 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 dark:placeholder:text-slate-500/80 block rounded-[0.6rem] border-slate-300/80 px-4 py-3.5">

            <div class="flex mt-4 text-xs text-slate-500 sm:text-sm">
                <div class="flex items-center mr-auto">
                    <input data-tw-merge="" type="checkbox" name="rememberme" id="remember-me" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 mr-2.5 border">
                    <label class="cursor-pointer select-none" for="remember-me">
                        Remember me
                    </label>
                </div>
            </div>

            <div class="mt-5 text-center xl:mt-8 xl:text-left">
                <button data-tw-merge="" type="submit" class="transition duration-200 border shadow-sm inline-flex items-center justify-center px-3 font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 disabled:opacity-70 disabled:cursor-not-allowed bg-primary border-primary text-white dark:border-primary rounded-full w-full bg-gradient-to-r from-theme-1/70 to-theme-2/70 py-3.5 xl:mr-3">
                    Sign In
                </button>
            </div>
        </form>
    </div>
@endsection
