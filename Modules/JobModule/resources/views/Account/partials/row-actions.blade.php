@include('layoutmodule::partials.row-menu', [
    'links' => [
        ['label' => 'View', 'icon' => 'eye', 'url' => route('account.jobs.show', $job->id), 'modal' => true],
        ['label' => 'Edit', 'icon' => 'pencil', 'url' => route('account.jobs.edit', $job->id), 'modal' => true],
    ],
    'actions' => [['view' => 'jobmodule::Account.partials.delete-form', 'data' => ['job' => $job]]],
])
