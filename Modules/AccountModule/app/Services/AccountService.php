<?php

namespace Modules\AccountModule\app\Services;

use App\Helpers\UploaderHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Modules\AccountModule\app\Models\Account;
use Modules\AccountModule\app\Repositories\AccountRepository;
use Modules\UserModule\app\Services\UserService;

class AccountService
{
    use UploaderHelper;

    private $accountRepository;
    private $userService;

    public function __construct(AccountRepository $accountRepository, UserService $userService)
    {
        $this->accountRepository = $accountRepository;
        $this->userService = $userService;
    }

    public function paginate(array $filters = [], $perPage = 15)
    {
        return $this->accountRepository->filter($filters)->paginate($perPage)->withQueryString();
    }

    public function latestPending($limit = 5)
    {
        return $this->accountRepository->filter(['status' => Account::STATUS_PENDING])->limit($limit)->get();
    }

    // [status => count] for every status, including the empty ones
    public function countByStatus(): array
    {
        $counts = $this->accountRepository->countByStatus();
        return collect(Account::STATUSES)->map(fn ($label, $status) => $counts[$status] ?? 0)->all();
    }

    public function countDeleted(): int
    {
        return $this->accountRepository->countDeleted();
    }

    public function findOne($id)
    {
        return $this->accountRepository->with('user')->find($id);
    }

    // guest registration: basic data only, waits for the admin approval
    public function register(array $data)
    {
        return $this->createWithUser($data, Account::STATUS_PENDING);
    }

    // created by the admin
    public function create(array $data)
    {
        return $this->createWithUser($data, $data['status'] ?? Account::STATUS_ACTIVE);
    }

    // admin update: profile + status + login data
    public function update($id, array $data)
    {
        $account = $this->findOne($id);

        DB::transaction(function () use ($account, $data) {
            $this->setStatus($account, $data['status']);
            $account->update($this->accountData($data));
            $this->userService->update($account->user->id, $data);
        });

        $this->storeLogo($account, $data['logo'] ?? null);
        return $account;
    }

    // account owner: complete / edit his profile
    public function updateProfile($id, array $data)
    {
        $account = $this->findOne($id);
        $account->update($this->accountData($data));
        $this->storeLogo($account, $data['logo'] ?? null);
        return $account;
    }

    public function changeStatus($id, $status)
    {
        $account = $this->findOne($id);
        $this->setStatus($account, $status);
        $account->save();
        return $account;
    }

    // soft delete: the login and the logo are kept so the account can be restored,
    // the login is refused while the account is deleted (see EnsureUserIsActive / login)
    public function deleteOne($id)
    {
        return $this->findOne($id)->delete();
    }

    public function restore($id)
    {
        $account = $this->accountRepository->findDeleted($id);
        $account->restore();
        return $account;
    }

    private function createWithUser(array $data, $status)
    {
        $account = DB::transaction(function () use ($data, $status) {
            $account = new Account($this->accountData($data));
            $this->setStatus($account, $status);
            $account->save();

            $this->userService->createFor($account, $data);
            return $account;
        });

        $this->storeLogo($account, $data['logo'] ?? null);
        return $account;
    }

    // only the account columns that were sent (logo is handled by storeLogo)
    private function accountData(array $data): array
    {
        $fields = ['name_ar', 'name_en', 'phone', 'address', 'description_ar', 'description_en'];
        return array_intersect_key($data, array_flip($fields));
    }

    private function setStatus(Account $account, $status)
    {
        $account->status = $status;
        if ($status == Account::STATUS_ACTIVE && !$account->approved_at) {
            $account->approved_at = now();
        }
    }

    private function storeLogo(Account $account, $file)
    {
        if (!$file) {
            return;
        }

        $folder = 'accounts/' . $account->id;
        if ($account->logo) {
            File::delete(public_path('uploads/' . $folder . '/' . $account->logo));
        }

        $account->update(['logo' => $this->uploadImage($file, $folder, 'logo', 400, 400)]);
    }
}
