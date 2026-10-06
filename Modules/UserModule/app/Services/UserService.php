<?php

namespace Modules\UserModule\app\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Modules\UserModule\app\Repositories\UserRepository;

class UserService
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function findOne($id)
    {
        return $this->userRepository->find($id);
    }

    // create the login of a userable model (Account, Employee, ...)
    public function createFor(Model $userable, array $data)
    {
        return $userable->user()->create([
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    // update email (when sent); the password is changed only when a new one is sent
    public function update($id, array $data)
    {
        $user_data = [];
        if (array_key_exists('email', $data)) {
            $user_data['email'] = $data['email'];
        }
        if (!empty($data['password'])) {
            $user_data['password'] = Hash::make($data['password']);
        }

        return $this->userRepository->update($user_data, $id);
    }
}
