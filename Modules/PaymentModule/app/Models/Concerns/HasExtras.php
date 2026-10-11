<?php

namespace Modules\PaymentModule\app\Models\Concerns;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\PaymentModule\app\Models\Extra;

/**
 * The extras of a payable (a package now): extras / adjustments added to what it owes (due = net + extrasTotal()).
 */
trait HasExtras
{
    public function extras(): MorphMany
    {
        return $this->morphMany(Extra::class, 'extrable');
    }

    // the extras block of its view (oldest first)
    public function extrasList(): Collection
    {
        return $this->extras()->with('incomeCategory')->orderBy('date')->orderBy('id')->get();
    }

    // Σ extras (signed); refreshAll() loads it with the rows (withSum: extras_sum_amount)
    public function extrasTotal(): float
    {
        if (array_key_exists('extras_sum_amount', $this->attributes)) {
            return round((float) $this->extras_sum_amount, 2);
        }
        return $this->exists ? round((float) $this->extras()->sum('amount'), 2) : 0;
    }
}
