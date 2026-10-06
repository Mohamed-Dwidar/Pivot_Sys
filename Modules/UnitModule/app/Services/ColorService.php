<?php

namespace Modules\UnitModule\app\Services;

use Modules\UnitModule\app\Repositories\ColorRepository;

class ColorService
{
    private $colorRepository;

    public function __construct(ColorRepository $colorRepository)
    {
        $this->colorRepository = $colorRepository;
    }

    // list query (paged / ordered by DataTables)
    public function listQuery(array $filters = [])
    {
        return $this->colorRepository->query()->filter($filters);
    }

    // [id => name], for the units form
    public function options(): array
    {
        return $this->colorRepository->query()->orderBy('name')->pluck('name', 'id')->all();
    }

    // [id => value] (hex), to show the swatches in the units form
    public function values(): array
    {
        return $this->colorRepository->query()->pluck('value', 'id')->all();
    }

    public function findOne($id)
    {
        return $this->colorRepository->query()->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->colorRepository->create($this->colorData($data));
    }

    public function update($id, array $data)
    {
        return $this->colorRepository->update($this->colorData($data), $id);
    }

    // false when units still use the color
    public function deleteOne($id): bool
    {
        $color = $this->findOne($id);
        if ($color->units_count > 0) {
            return false;
        }
        return $color->delete();
    }

    private function colorData(array $data): array
    {
        return [
            'name' => $data['name'],
            'value' => strtolower($data['value']),
        ];
    }
}
