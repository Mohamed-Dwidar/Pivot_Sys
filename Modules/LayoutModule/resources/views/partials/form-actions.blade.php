{{--
    Save + Cancel of the create / edit forms. In the popup Cancel closes it, on a full page it goes to $cancel.
    @include('layoutmodule::partials.form-actions', ['cancel' => route('...index')])
--}}
<div class="form-actions">
    <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Save</button>
    @if (request()->ajax())
        <button type="button" data-tw-dismiss="modal" class="btn btn-secondary">Cancel</button>
    @else
        <a href="{{ $cancel }}" class="btn btn-secondary">Cancel</a>
    @endif
</div>
