@include('layoutmodule::partials.row-menu', [
    'links' => [
        ['label' => 'View', 'icon' => 'eye', 'url' => route('account.jobs.show', $job->id)],
        ['label' => 'Edit', 'icon' => 'pencil', 'url' => route('account.jobs.edit', $job->id)],
    ],
    'actions' => [['view' => 'jobmodule::Account.partials.delete-form', 'data' => ['job' => $job]]],
])
