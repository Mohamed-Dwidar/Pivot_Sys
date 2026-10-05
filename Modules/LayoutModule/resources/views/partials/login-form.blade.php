{{--
    Sign in form shared by the admin and the users login pages.
    @include('layoutmodule::partials.login-form', ['action' => route(...), 'subtitle' => '...', 'remember' => 'remember'])
--}}
<div class="text-2xl font-medium">Sign In</div>
<div class="mt-2.5 text-slate-600">{{ $subtitle }}</div>

@if ($errors->any())
    <div role="alert" class="alert relative border rounded-md px-5 py-4 bg-danger border-danger bg-opacity-20 border-opacity-5 text-danger dark:border-danger dark:border-opacity-20 mt-7 flex items-center">
        <i data-tw-merge="" data-lucide="alert-octagon" class="stroke-[1] w-6 h-6 mr-2"></i>
        {{ $errors->first() }}
    </div>
@endif
@if (session('success'))
    <div role="alert" class="alert relative border rounded-md px-5 py-4 bg-success border-success bg-opacity-20 border-opacity-5 text-success dark:border-success dark:border-opacity-20 mt-7 flex items-center">
        <i data-tw-merge="" data-lucide="check-circle" class="stroke-[1] w-6 h-6 mr-2"></i>
        {{ session('success') }}
    </div>
@endif

<form method="POST" action="{{ $action }}" class="mt-6">
    @csrf
    <label for="email" class="{{ config('layoutmodule.form.label') }}">Email*</label>
    <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="Email Address" required autofocus class="{{ config('layoutmodule.form.input_lg') }}">

    <label for="password" class="{{ config('layoutmodule.form.label') }} mt-4">Password*</label>
    <input type="password" name="password" id="password" placeholder="************" required class="{{ config('layoutmodule.form.input_lg') }}">

    <div class="flex mt-4 text-xs text-slate-500 sm:text-sm">
        <div class="flex items-center mr-auto">
            <input type="checkbox" name="{{ $remember }}" id="remember-me" value="1" class="{{ config('layoutmodule.form.checkbox') }} mr-2.5 border">
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
