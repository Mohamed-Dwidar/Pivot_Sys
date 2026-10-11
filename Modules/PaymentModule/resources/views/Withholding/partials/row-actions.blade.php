@include('layoutmodule::partials.row-menu', [
    'links' => [
        ['label' => 'View', 'icon' => 'eye', 'url' => route('account.withholding-certificates.show', $certificate->id), 'modal' => true],
        ['label' => 'Edit', 'icon' => 'pencil', 'url' => route('account.withholding-certificates.edit', $certificate->id), 'modal' => true],
    ],
    'actions' => [['view' => 'paymentmodule::Withholding.partials.delete-form', 'data' => ['certificate' => $certificate]]],
])
