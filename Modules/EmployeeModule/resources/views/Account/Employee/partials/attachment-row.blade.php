{{-- One new attachment row: label + file ($index, 'removable' for the added rows). --}}
<div class="attachment-row" data-repeat-row>
    <input type="text" name="attachments[{{ $index }}][label]" class="{{ config('layoutmodule.form.input') }}" placeholder="Label, e.g. National ID" aria-label="Attachment label">
    <input type="file" name="attachments[{{ $index }}][file]" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx" class="{{ config('layoutmodule.form.input') }}" aria-label="Attachment file">
    @if (!empty($removable))
        <button type="button" class="app-modal__close" data-repeat-remove title="Remove this row"><i data-lucide="x"></i></button>
    @endif
</div>
