<?php

namespace Modules\ReservationModule\app\Models;

use App\Helpers\LocalizedHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AccountModule\app\Models\Account;

// reservation statuses list of an account (only the account manages it)
class ReservationStatus extends Model
{
    use LocalizedHelper, SoftDeletes;

    // read in the active language: $model->name (see App\Helpers\LocalizedHelper)
    protected array $localizedFields = ['name'];

    // the badge colors: the theme colors (custom.css .badge-status-{key}), no inline colors
    const COLORS = [
        'success' => 'Green',
        'primary' => 'Blue',
        'info' => 'Cyan',
        'warning' => 'Yellow',
        'pending' => 'Orange',
        'danger' => 'Red',
        'slate' => 'Gray',
        'dark' => 'Dark',
    ];

    protected $fillable = ['account_id', 'name_ar', 'name_en', 'is_active', 'is_default', 'color', 'is_counted', 'sort_order'];

    protected $casts = [
        'is_active' => 'boolean',
        // the default status of the account: given to the new reservations (one per account, always active)
        'is_default' => 'boolean',
        // its reservations are active / counted (package price, hours ...); off for cancelled like statuses
        'is_counted' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    // the badge class of its color
    public function getBadgeClassAttribute(): string
    {
        return 'badge badge-status-' . (array_key_exists($this->color, self::COLORS) ? $this->color : 'slate');
    }

    // the ids of the statuses whose reservations are not counted (deleted ones too), for the totals
    public static function notCountedIds(): array
    {
        return self::withTrashed()->where('is_counted', false)->pluck('id')->all();
    }

    // the statuses order (lists, drop menus, the default status of a new reservation)
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function scopeFilter($query, array $filters = [])
    {
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(fn ($query) => $query->where('name_ar', 'like', $search)->orWhere('name_en', 'like', $search));
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        return $query;
    }
}
