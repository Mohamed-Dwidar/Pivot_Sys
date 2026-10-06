@if ($employee->hasLeft())
    <span class="badge badge-inactive" title="Left at {{ $employee->leave_date->format('Y-m-d') }}, he can not log in">Left</span>
@else
    <span class="badge badge-active">Working</span>
@endif
