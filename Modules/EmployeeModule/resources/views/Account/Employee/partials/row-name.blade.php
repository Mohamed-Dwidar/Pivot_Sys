<div class="flex items-center gap-3">
    @include('employeemodule::Account.Employee.partials.photo', ['small' => true])
    <div>
        <a href="{{ route('account.employees.show', $employee->id) }}" data-modal class="font-medium">{{ $employee->name }}</a>
        <div class="mt-0.5 text-xs text-slate-500">{{ $employee->user?->email }}</div>
    </div>
</div>
