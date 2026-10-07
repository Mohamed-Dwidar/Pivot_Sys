<?php

namespace Modules\MemberModule\app\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Modules\MemberModule\app\Repositories\MemberRepository;

/**
 * Every method takes the owner account id, so an account can only reach its own members.
 */
class MemberService
{
    private $memberRepository;

    public function __construct(MemberRepository $memberRepository)
    {
        $this->memberRepository = $memberRepository;
    }

    // members list query (paged / ordered by DataTables)
    public function listQuery($accountId, array $filters = [])
    {
        return $this->memberRepository->forAccount($accountId)->filter($filters);
    }

    // [id => "name mobile"] (searchable by both), for the packages / reservations forms
    public function options($accountId): array
    {
        return $this->memberRepository->forAccount($accountId)->orderBy('name')->get()
            ->mapWithKeys(fn ($member) => [$member->id => $member->name . ' ' . $member->phone])->all();
    }

    // 404 when the member belongs to another account
    public function findOne($accountId, $id)
    {
        return $this->memberRepository->forAccount($accountId)->findOrFail($id);
    }

    // $creator: the logged in user who adds the member
    public function create($accountId, array $data, Model $creator)
    {
        $member = $this->memberRepository->makeModel()->newInstance($this->memberData($data) + [
            'account_id' => $accountId,
            'created_by' => $creator->getKey(),
        ]);
        $member->creatable()->associate($creator);
        $member->save();

        return $member;
    }

    public function update($accountId, $id, array $data)
    {
        $member = $this->findOne($accountId, $id);
        $member->update($this->memberData($data));
        return $member;
    }

    public function deleteOne($accountId, $id)
    {
        return $this->findOne($accountId, $id)->delete();
    }

    private function memberData(array $data): array
    {
        return [
            // saved humanized: "ahmed_ali" / "ahmed ali" => "Ahmed Ali"
            'name' => Str::humanize($data['name']),
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'national_number' => $data['national_number'] ?? null,
            // the columns are not nullable: 0 = none
            'company_id' => $data['company_id'] ?? 0,
            'job_id' => $data['job_id'] ?? 0,
            'from_where' => $data['from_where'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];
    }
}
