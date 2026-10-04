@if ($errors->any())
    <div role="alert" class="alert relative border rounded-md px-5 py-4 bg-danger border-danger bg-opacity-20 border-opacity-5 text-danger dark:border-danger dark:border-opacity-20 mt-3.5">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@if ($message = Session::get('success'))
    <div role="alert" class="alert relative border rounded-md px-5 py-4 bg-success border-success bg-opacity-20 border-opacity-5 text-success dark:border-success dark:border-opacity-20 mt-3.5">
        {{ $message }}
    </div>
@endif
