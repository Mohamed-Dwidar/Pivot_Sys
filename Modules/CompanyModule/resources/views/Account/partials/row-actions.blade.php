@include('layoutmodule::partials.row-menu', [
    'links' => [
        ['label' => 'View', 'icon' => 'eye', 'url' => route('account.companies.show', $company->id)],
        ['label' => 'Edit', 'icon' => 'pencil', 'url' => route('account.companies.edit', $company->id)],
    ],
    'actions' => [['view' => 'companymodule::Account.partials.delete-form', 'data' => ['company' => $company]]],
])
