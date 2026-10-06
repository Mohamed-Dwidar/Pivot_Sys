{{--
    Plans checkboxes of the space form (all the account plans) and the unit form (only the space plans). Sends plans[].
    @include('planmodule::Account.partials.checklist', ['plans' => [id => name], 'selected' => [ids], 'empty' => 'html when no plans', 'hint' => '...'])
--}}
@php($selectedPlans = array_map('intval', old('plans', $selected ?? [])))
<div data-field="plans">
    @if ($plans)
        <div class="checkbox-list">
            @foreach ($plans as $planId => $planName)
                <div class="flex items-center">
                    <input type="checkbox" name="plans[]" id="plan-{{ $planId }}" value="{{ $planId }}" class="{{ config('layoutmodule.form.checkbox') }}"
                        @checked(in_array($planId, $selectedPlans))>
                    <label for="plan-{{ $planId }}" class="{{ config('layoutmodule.form.checkbox_label') }}">{{ $planName }}</label>
                </div>
            @endforeach
        </div>
        @if (!empty($hint))
            <div class="{{ config('layoutmodule.form.hint') }}">{{ $hint }}</div>
        @endif
    @else
        <div class="text-slate-500">{!! $empty !!}</div>
    @endif
    @foreach (['plans', 'plans.*'] as $errorKey)
        @foreach ($errors->get($errorKey) as $messages)
            @foreach ((array) $messages as $message)
                <div class="{{ config('layoutmodule.form.error') }}">{{ $message }}</div>
            @endforeach
        @endforeach
    @endforeach
</div>
