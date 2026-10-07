{{--
    On / off switch in a list row (template "Switch"), saved by ajax as soon as it is clicked (custom.js [data-toggle-form]):
    the switch is locked with a loading spinner until the answer, then the row is updated (or switched back on error).
    @include('layoutmodule::partials.row-toggle', [
        'url' => route('...active', $model->id), 'row' => $model->id,
        'name' => 'is_active', 'checked' => $model->is_active,
        'on' => 1, 'off' => 0, (values sent, default 1 / 0)    'method' => 'PATCH' (default),
        'onLabel' => 'Active', 'offLabel' => 'Inactive', 'title' => 'Click to ...' (default: Click to activate / deactivate),
    ])
--}}
<form method="POST" action="{{ $url }}" data-ajax data-toggle-form data-row="{{ $row }}" class="row-toggle">
    @csrf
    @method($method ?? 'PATCH')
    <input type="hidden" name="{{ $name }}" value="{{ $off ?? 0 }}">
    <label class="row-toggle__label" title="{{ $title ?? ($checked ? 'Click to deactivate' : 'Click to activate') }}">
        <input type="checkbox" name="{{ $name }}" value="{{ $on ?? 1 }}" class="{{ config('layoutmodule.form.switch') }}" @checked($checked)>
        <span class="row-toggle__text">{{ $checked ? $onLabel ?? 'Active' : $offLabel ?? 'Inactive' }}</span>
        <span class="row-toggle__spinner" aria-hidden="true"></span>
    </label>
</form>
