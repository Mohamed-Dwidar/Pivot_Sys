<?php

namespace Modules\AdminModule\app\Services;

use Illuminate\Support\Facades\Hash;
use Modules\AdminModule\app\Repositories\AdminRepository;

class AdminService
{
    private $adminRepository;

    public function __construct(AdminRepository $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    // get specific admin with id from database
    public function findOne($id)
    {
        return $this->adminRepository->find($id);
    }

    // update admin name / email; the password is changed only when a new one is sent
    public function update($id, array $data)
    {
        $admin_data = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];
        if (!empty($data['password'])) {
            $admin_data['password'] = Hash::make($data['password']);
        }

        return $this->adminRepository->update($admin_data, $id);
    }
}
