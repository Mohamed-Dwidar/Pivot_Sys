{{--
    Employee attachments: the saved ones (download / remove) + new rows of [label, file] ("Add another file" adds a row, custom.js [data-repeat]).
    Sends attachments[i][label], attachments[i][file] and delete_attachments[].
--}}
<div data-field="attachments" data-repeat>
    @if ($employee && $employee->attachments->isNotEmpty())
        <div class="attachment-list mb-5">
            @foreach ($employee->attachments as $attachment)
                <div class="attachment-list__item">
                    <i data-lucide="paperclip"></i>
                    <a href="{{ route('account.employees.attachments.download', [$employee->id, $attachment->id]) }}" class="attachment-list__name">{{ $attachment->attach_label }}</a>
                    <label class="attachment-list__remove">
                        <input type="checkbox" name="delete_attachments[]" value="{{ $attachment->id }}" class="{{ config('layoutmodule.form.checkbox') }}">
                        Remove
                    </label>
                </div>
            @endforeach
        </div>
    @endif

    <div class="flex flex-col gap-3" data-repeat-list>
        @include('employeemodule::Account.Employee.partials.attachment-row', ['index' => 0])
    </div>
    <template data-repeat-template>
        @include('employeemodule::Account.Employee.partials.attachment-row', ['index' => '__INDEX__', 'removable' => true])
    </template>

    <div class="mt-3 flex items-center gap-3">
        <button type="button" class="btn btn-secondary btn-sm" data-repeat-add><i data-lucide="plus"></i> Add another file</button>
        <span class="{{ config('layoutmodule.form.hint') }} attachment-hint">PDF, image, Word or Excel, max 5 MB each. The label is the file name when it is empty.</span>
    </div>
</div>
