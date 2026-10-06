{{-- Employee photo or his first letter. 'small' => true in the list. --}}
@if ($employee->image_url)
    <img src="{{ $employee->image_url }}" alt="{{ $employee->name }}" class="employee-photo {{ !empty($small) ? 'employee-photo--sm' : '' }}" data-action="zoom">
@else
    <div class="employee-photo employee-photo--placeholder {{ !empty($small) ? 'employee-photo--sm' : '' }}">{{ mb_strtoupper(mb_substr($employee->name, 0, 1)) }}</div>
@endif
