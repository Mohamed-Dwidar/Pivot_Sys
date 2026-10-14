{{-- the <option>s of a list filter: $options = [id => ['name', 'active']] (active first, from a service filterOptions()),
     inactive ones greyed + "(Inactive)"; optional $prefix before the value (e.g. "category-") --}}
@foreach ($options as $id => $option)
    <option value="{{ ($prefix ?? '') . $id }}" @class(['option-inactive' => !$option['active']])>{{ Str::humanize($option['name']) }}{{ $option['active'] ? '' : ' (Inactive)' }}</option>
@endforeach
