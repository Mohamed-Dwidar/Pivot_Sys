<?php

namespace Modules\AdminModule\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\AdminModule\app\Services\AdminService;
use Modules\UserModule\app\Services\UserService;
use Illuminate\Support\Facades\Validator;

class AdminModuleController extends Controller
{
    protected $adminService;
    protected $userService;

    function __construct(AdminService $adminService, UserService $userService)
    {
        $this->userService = $userService;
        $this->adminService = $adminService;
    }


    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('adminmodule::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('adminmodule::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('adminmodule::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('adminmodule::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    public function dashboard()
    {
        return view('layoutmodule::admin.dashboard');
    }


    public function changePassword(Request $request)
    {
        $admin = $this->adminService->findOne(auth()->guard('admin')->user()->id);
        return view('adminmodule::admin.change_password', compact('admin'));
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required|min:6',
        ]);
        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }
        $request['id'] = $request->id;
        $request['password'] = $request->new_password;
        $new_pass = $this->adminService->checkExistPass($request['id'], $request->old_password);
        $requests = $request->except('old_password', 'new_password');
        if ($new_pass) {
            $store = $this->adminService->update($requests);
            return redirect()->route('admin.dashboard')
                ->with('success', trans('messages.Updated_Successfully'));
        } else {
            return back()->withErrors(trans('messages.invalid_old_password'))->withInput();
        }
    }

    public function calendar()
    {
        return view('adminmodule::calendar');
    }
}
