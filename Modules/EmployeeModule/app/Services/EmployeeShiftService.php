<?php

namespace Modules\EmployeeModule\app\Services;

use Modules\EmployeeModule\app\Repositories\EmployeeShiftRepository;

/**
 * Every method takes the owner account id, so an account can only reach its own shifts.
 */
class EmployeeShiftService
{
    private $shiftRepository;

    public function __construct(EmployeeShiftRepository $shiftRepository)
    {
        $this->shiftRepository = $shiftRepository;
    }

    // list query (paged / ordered by DataTables)
    public function listQuery($accountId, array $filters = [])
    {
        return $this->shiftRepository->forAccount($accountId)->filter($filters);
    }

    // [id => name] for the employee form: the active ones + the employee's current one (even if inactive)
    public function options($accountId, $currentId = null): array
    {
        return $this->shiftRepository->forAccount($accountId)
            ->where(fn ($query) => $query->where('is_active', true)->orWhere('id', (int) $currentId))
            ->orderBy('name')->get()->mapWithKeys(fn ($shift) => [$shift->id => $this->optionLabel($shift)])->all();
    }

    // all of them, for the list filters
    public function filterOptions($accountId): array
    {
        return $this->shiftRepository->forAccount($accountId)->orderBy('name')->pluck('name', 'id')->all();
    }

    // 404 when it belongs to another account
    public function findOne($accountId, $id)
    {
        return $this->shiftRepository->forAccount($accountId)->findOrFail($id);
    }

    public function create($accountId, array $data)
    {
        return $this->shiftRepository->create($this->shiftData($data) + ['account_id' => $accountId]);
    }

    public function update($accountId, $id, array $data)
    {
        $shift = $this->findOne($accountId, $id);
        $shift->update($this->shiftData($data));
        return $shift;
    }

    // the active switch of the list
    public function setActive($accountId, $id, bool $isActive)
    {
        $shift = $this->findOne($accountId, $id);
        $shift->update(['is_active' => $isActive]);
        return $shift;
    }

    // soft delete, false when employees still use it
    public function deleteOne($accountId, $id)
    {
        $shift = $this->findOne($accountId, $id);
        if ($shift->employees_count > 0) {
            return false;
        }
        return $shift->delete();
    }

    // "Morning (09:00 - 17:00)"
    private function optionLabel($shift): string
    {
        return $shift->name . ' (' . $shift->time_range . ')';
    }

    private function shiftData(array $data): array
    {
        return [
            'name' => $data['name'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'is_active' => !empty($data['is_active']),
        ];
    }
}
