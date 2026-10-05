<?php

namespace Modules\AdminModule\app\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\AdminModule\app\Http\Requests\UpdateAdminProfileRequest;
use Modules\AdminModule\app\Services\AdminService;

class AdminController extends Controller
{
    private $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    // logged in admin: edit his own account
    public function editProfile()
    {
        $admin = Auth::guard('admin')->user();
        return view('adminmodule::profile', compact('admin'));
    }

    public function updateProfile(UpdateAdminProfileRequest $request)
    {
        $this->adminService->update(Auth::guard('admin')->id(), $request->validated());

        return redirect()->route('admin.profile.edit')->with('success', 'Your account has been updated successfully.');
    }
}
