<?php

namespace Modules\JobModule\app\Models;

use App\Helpers\LocalizedHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AccountModule\app\Models\Account;

class Job extends Model
{
    use LocalizedHelper, SoftDeletes;

    // read in the active language: $model->name (see App\Helpers\LocalizedHelper)
    protected array $localizedFields = ['name'];

    public $table = 'job_positions';

    protected $fillable = ['account_id', 'name_ar', 'name_en'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function scopeFilter($query, array $filters = [])
    {
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($query) use ($search) {
                $query->where('name_ar', 'like', $search)->orWhere('name_en', 'like', $search);
            });
        }

        return $query;
    }

}
