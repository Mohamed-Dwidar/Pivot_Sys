<?php

namespace Modules\ReservationModule\app\Services;

use Illuminate\Support\Facades\DB;
use Modules\ReservationModule\app\Repositories\ReservationStatusRepository;

/**
 * Every method takes the owner account id, so an account can only reach its own statuses.
 */
class ReservationStatusService
{
    private $statusRepository;

    public function __construct(ReservationStatusRepository $statusRepository)
    {
        $this->statusRepository = $statusRepository;
    }

    // list query (paged / ordered by DataTables)
    public function listQuery($accountId, array $filters = [])
    {
        return $this->statusRepository->forAccount($accountId)->filter($filters);
    }

    // [id => name] for the reservation form: the active ones + the reservation's current one
    public function options($accountId, $currentId = null): array
    {
        return $this->statusRepository->forAccount($accountId)
            ->where(fn ($query) => $query->where('is_active', true)->orWhere('id', (int) $currentId))
            ->ordered()->get()->pluck('name', 'id')->all();
    }

    // the status of a new reservation: the default one (else the first active one by sort order, 0 = none yet)
    public function defaultId($accountId): int
    {
        $statuses = $this->statusRepository->forAccount($accountId)->where('is_active', true);
        return (int) ((clone $statuses)->where('is_default', true)->value('id') ?: $statuses->ordered()->value('id'));
    }

    // the status menu of a reservation: the active statuses (+ its current one) with their badge classes
    public function menuOptions($accountId, $currentId = null)
    {
        return $this->statusRepository->forAccount($accountId)
            ->where(fn ($query) => $query->where('is_active', true)->orWhere('id', (int) $currentId))
            ->ordered()->get();
    }

    // all of them, for the list filter
    public function filterOptions($accountId): array
    {
        return $this->statusRepository->forAccount($accountId)->ordered()->get()->pluck('name', 'id')->all();
    }

    // 404 when it belongs to another account
    public function findOne($accountId, $id)
    {
        return $this->statusRepository->forAccount($accountId)->findOrFail($id);
    }

    public function create($accountId, array $data)
    {
        // no sort order: after the last one
        if (!isset($data['sort_order']) || $data['sort_order'] === '') {
            $data['sort_order'] = (int) $this->statusRepository->forAccount($accountId)->max('sort_order') + 1;
        }
        return DB::transaction(function () use ($accountId, $data) {
            // the first active status of the account is its default
            $isFirst = !$this->statusRepository->forAccount($accountId)->where('is_default', true)->exists();
            $status = $this->statusRepository->create($this->statusData($data) + ['account_id' => $accountId]);
            if ($isFirst && $status->is_active && !$status->is_default) {
                $status->update(['is_default' => true]);
            }
            $this->keepOneDefault($status);
            return $status;
        });
    }

    public function update($accountId, $id, array $data)
    {
        $status = $this->findOne($accountId, $id);

        return DB::transaction(function () use ($status, $data) {
            $status->update($this->statusData($data));
            $this->keepOneDefault($status);
            return $status;
        });
    }

    // the active switch of the list; false when it is the default status (it must stay active)
    public function setActive($accountId, $id, bool $isActive)
    {
        $status = $this->findOne($accountId, $id);
        if (!$isActive && $status->is_default) {
            return false;
        }
        $status->update(['is_active' => $isActive]);
        return $status;
    }

    // the default switch of the list: the reason when it is refused, null when done
    // (on: must be active, the others are not default any more; off: refused, another status is made the default instead)
    public function setDefault($accountId, $id, bool $isDefault): ?string
    {
        $status = $this->findOne($accountId, $id);
        if (!$isDefault) {
            return $status->is_default ? 'There must be a default status, make another status the default instead.' : null;
        }
        if (!$status->is_active) {
            return 'The default status must be active, activate it first.';
        }

        DB::transaction(function () use ($status) {
            $status->update(['is_default' => true]);
            $this->keepOneDefault($status);
        });
        return null;
    }

    // soft delete; the reason when it can not be deleted (used by reservations / the default status), null when deleted
    public function deleteOne($accountId, $id): ?string
    {
        $status = $this->findOne($accountId, $id);
        if ($status->is_default) {
            return 'This is the default status, choose another default status first.';
        }
        if ($status->reservations_count > 0) {
            return 'This status is used by reservations, change their status first.';
        }
        $status->delete();
        return null;
    }

    // one default status per account: the others are not default any more
    private function keepOneDefault($status): void
    {
        if ($status->is_default) {
            $status->newQuery()->where('account_id', $status->account_id)->whereKeyNot($status->id)->update(['is_default' => false]);
        }
    }

    private function statusData(array $data): array
    {
        return [
            'name_ar' => $data['name_ar'],
            'name_en' => $data['name_en'] ?? null,
            'is_active' => !empty($data['is_active']),
            'is_default' => !empty($data['is_default']),
            'color' => $data['color'],
            'is_counted' => !empty($data['is_counted']),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ];
    }
}
