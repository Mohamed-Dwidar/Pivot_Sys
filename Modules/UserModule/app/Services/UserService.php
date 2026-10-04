<?php

namespace Modules\UserModule\app\Services;

use Auth;
use App\Helpers\UploaderHelper;

use Illuminate\Support\Facades\Hash;
use Modules\UserModule\app\Repositories\UserRepository;

class UserService
{
    private $userRepository;
    use UploaderHelper;
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    // get all admins from database
    public function findAll()
    {
        return $this->userRepository->get();
    }

    public function findWhere($arr)
    {
        return $this->userRepository->findWhere($arr);
    }

    // get specific admin with id from database
    public function findOne($id)
    {
        return $this->userRepository->find($id);
    }

    // save new admin data to database
    public function create($request)
    {
        $data = [];
        if (key_exists('username', $request)) {
            $data['username'] = $request['username'];
        }
        
        if (key_exists('password', $request) ) {
            $data['password'] = bcrypt($request['password']);
        }
        $user =  $this->userRepository->create($data);
        // $user->syncRoles($request['roles']);

        return $user;
    }

    // update admin data to database
    public function update($request ,$id)
    {
        $data = [];
        if (key_exists('username', $request)) {
            $data['username'] = $request['username'];
        }
        
        if (key_exists('password', $request)) {
            $data['password'] = bcrypt($request['password']);
        }
        $user = $this->userRepository->update($data,$id);

        // $user->syncRoles($request['roles'] );
        return $user;

    }


    // function to check requested columns name
    public function validateUpdateData($data)
    {
        $admin_data = [];
        if (key_exists('name', $data)) {
            $admin_data['name'] = $data['name'];
        }
        if (key_exists('email', $data)) {
            $admin_data['email'] = $data['email'];
        }
        if (key_exists('first_password', $data)) {
            $admin_data['first_password'] = $data['first_password'];
        }
        if (key_exists('password', $data) && $data['password'] != null) {
            $admin_data['password'] = bcrypt($data['password']);
        }
        return $admin_data;
    }
    
    // delete admin from database
    public function deleteOne($id)
    {
        $admin = $this->userRepository->findWhere(['id' => $id])->first(); //determine the row which will be deleted
        return $admin->delete(); //delete action
    }
    
    public function checkExistPass($user_id, $old_pass)
    {
        $user_pass = $this->userRepository->getPassword($user_id);
        return Hash::check($old_pass, $user_pass);
    }

    public function login($data)
    {
        $user = $this->userRepository->findWhere(
            [
                'username' => $data['username']
            ]
        )->first(); 
        if (!$user)
            return null;
        
        if (!Hash::check($data['password'], $user->password)) {
            return 'false';
        }
        
        // return $user;
    }

    public function updatePassword($password, $user_id)
    {
        return $this->userRepository->update(['password' => Hash::make($password)], $user_id);
    }  

}
