<?php

namespace Modules\AccountModule\app\Repositories;

use Modules\AccountModule\app\Models\Account;
use Prettus\Repository\Eloquent\BaseRepository;

class AccountRepository extends BaseRepository
{
    public function model()
    {
        return Account::class;
    }

    public function filter(array $filters = [])
    {
        return Account::with('user')->filter($filters)->latest();
    }

    // without order (DataTables orders the list)
    public function listQuery(array $filters = [])
    {
        return Account::with('user')->filter($filters);
    }

    public function countDeleted(): int
    {
        return Account::onlyTrashed()->count();
    }

    public function findDeleted($id)
    {
        return Account::onlyTrashed()->findOrFail($id);
    }

    // [status => count] (soft deleted accounts are not counted)
    public function countByStatus(): array
    {
        return Account::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status')->all();
    }
}
