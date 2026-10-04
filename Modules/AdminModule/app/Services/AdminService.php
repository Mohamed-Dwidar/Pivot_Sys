<?php

namespace Modules\AdminModule\app\Services;

use App\Helpers\UploaderHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Modules\AdminModule\app\Repositories\AdminRepository;

class AdminService
{
    private $adminRepository;
  
    private $permissionRepository;
    use UploaderHelper;
    public function __construct(AdminRepository $adminRepository)
    {
        $this->adminRepository = $adminRepository;
       
        $this->permissionRepository = $permissionRepository;
    }

    // get all admins from database
    public function findAll()
    {
        return $this->adminRepository->get();
    }

    // get specific admin with id from database
    public function findOne($id)
    {
        return $this->adminRepository->find($id);
    }

    // save new admin data to database
    public function create($requests)
    {
        //fields to add
        $admin_data = [
            'name' => $requests['name'],
            'email' => $requests['email'],
            'password' => bcrypt($requests['password']),

        ];
        // add action
        return $this->adminRepository->create($admin_data);
    }

    // update admin data to database
    public function update($data)
    {
        $admin_data = $this->validateUpdateData($data); // check columns' name to be updated
        return $this->adminRepository->update($admin_data, $data['id']); // update action
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
        $admin = $this->adminRepository->findWhere(['id' => $id])->first(); 
        return $admin->delete(); 
    }
    
    public function checkExistPass($store_id, $old_pass)
    {
        $store_pass = $this->adminRepository->getPass($store_id);

        if (!Hash::check($old_pass, $store_pass)) {
            return false;
        }
        return true;
    }
    public function login($data)
    {
        $admin = $this->adminRepository->findWhere(
            [
                'email' => $data['email']
            ]
        )->first();
        if (!$admin)
            return null;
        
        if (!Hash::check($data['password'], $admin->password)) {
            return 'false';
        }
    
        auth()->guard('admin')->setUser($admin);
        $admin->token = $admin->createToken('token')->accessToken;
        $role =  $this->roleRepository->findWhere(['name' => 'admin']);
        if( $role->count() > 0) {
            $role = $role->first();
            // $role->syncPermissions('full-access');

        } else {
            $role = $this->roleRepository->create([
                'name' => 'admin',
                'guard_name' => 'admin',
                'account_id' => $admin->account_id,
            ]);

        }

        // dd($role);

        $permission =  $this->permissionRepository->findWhere(['name' => 'Full_Access' ]);
            // dd($permission);
        if($permission->count() > 0) {
            // dd($permission);
        $role->syncPermissions('Full_Access');

        } 

        $admin->syncRoles('admin');

//  dd($admin);
        return $admin;
    }
}
