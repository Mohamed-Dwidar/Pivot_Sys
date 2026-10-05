@extends('layoutmodule::login')

@section('content')
    <div class="mt-10">
        <div class="text-2xl font-medium">Create Account</div>
        <div class="mt-2.5 text-slate-600">Your account will be active after the admin approval.</div>

        <form method="POST" action="{{ route('account.registerPost') }}" class="mt-6 flex flex-col gap-4">
            @csrf
            @include('layoutmodule::partials.field', ['name' => 'name_ar', 'label' => 'Name (Arabic)', 'required' => true, 'size' => 'lg', 'attrs' => 'dir=rtl autofocus'])
            @include('layoutmodule::partials.field', ['name' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'required' => true, 'size' => 'lg', 'attrs' => 'inputmode="tel" pattern="\+?[0-9]{8,15}" placeholder="01012345678"'])
            @include('layoutmodule::partials.field', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'size' => 'lg'])
            @include('layoutmodule::partials.field', ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'required' => true, 'size' => 'lg', 'attrs' => 'autocomplete=new-password'])
            @include('layoutmodule::partials.field', ['name' => 'password_confirmation', 'label' => 'Confirm Password', 'type' => 'password', 'required' => true, 'size' => 'lg', 'attrs' => 'autocomplete=new-password'])

            <div class="mt-2">
                <button data-tw-merge="" type="submit" class="transition duration-200 border shadow-sm inline-flex items-center justify-center px-3 font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 disabled:opacity-70 disabled:cursor-not-allowed bg-primary border-primary text-white dark:border-primary rounded-full w-full bg-gradient-to-r from-theme-1/70 to-theme-2/70 py-3.5 xl:mr-3">
                    Register
                </button>
            </div>
        </form>

        <div class="auth-links">
            Already have an account? <a href="{{ route('login') }}">Sign in</a>
        </div>
    </div>
@endsection
