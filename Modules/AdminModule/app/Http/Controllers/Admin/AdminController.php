<?php

namespace Modules\AdminModule\app\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\AdminModule\app\Services\AdminService;
use Modules\UserModule\app\Services\UserService;
use Yajra\DataTables\Facades\DataTables;

class AdminController extends Controller
{
    private $adminService;
    private $userService;

    public function __construct(AdminService $adminService ,UserService $userService)
    {
        $this->adminService = $adminService;
        $this->userService = $userService;
    }

    //  this page will include datatable of admins
    public function listAdmins()
    {
        $admins = $this->adminService->findAll();
      
        return view('adminmodule::admin.index', compact('admins'));
    }

    //function which retrieve datatable data (admins' data)
    public function getIndexAdmins(Request $request)
    {
        if ($request->ajax()) {
            $admins = [];
            if (auth()->guard('admin')->user()) {  //check authorization 
                $admins = $this->adminService->findAll();  // return all admins from database
            }
            $table = DataTables::of($admins); // draw datatable with admins' data
            $table->addColumn('action', function ($admins) {
                $button = null;
                if ($admins->deleted_at == null) {
                    // add edit button in each row in datatable to edit this specific  row
                    $button = '<a class="btn-sm btn-warning" href="' . route('admin.admins.edit', $admins->id) . '" role="button">' . trans('messages.edit') . '</a>';
                    if ($admins->id != 2) {
                        $button .= '&nbsp;&nbsp;&nbsp;<a href="#" data-toggle="tooltip" data-id="' . $admins->id . '"  data-original-title="Delete" class="btn-sm btn-danger deleteAdmin">' . trans('messages.delete') . '</a>';
                    }
                }
                return $button;
            });

            $table->rawColumns(['action']);

            return $table->make(true);
        }
    }

    
    public function resetPassword($id)
    {
        $admin = $this->adminService->findOne($id);
        return view('adminmodule::admin.reset_password', ['admin' => $admin]);
    }

    public function resetPasswordPost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'new_password' => 'required'
        ], [
            'new_password.required' => 'يجب إدخال كلمة المرور الجديدة.'
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        if ($this->userService->updatePassword($request->new_password, $request->id)) {
            return redirect()->route('admin.admins.index')
                ->with('success', 'تم تغيير كلمة المرور بنجاح');
        }

        return redirect()->back()->withErrors(['error' => ' خطأ في كلمة المرور الحالية .'])->withInput();
    }

    //  view -> form to add new admin
    public function create()
    {
        return view('adminmodule::admin.create');
    }

    //   store data in database for new admin
    public function store(Request $request)
    {
        // check incoming data with validation rules
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'email' => 'required|email|unique:admins',
                'password' => 'required|min:6',
            ]
        );
        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        // set 'first_password' column value 
        $request['first_password'] = $request->password;

        // send requested data to servies
        $this->adminService->create($request);

        // if return true will redirect to the view which list all admins
        return redirect()->route('admin.admins.index')
            ->with('success', trans('messages.Added_Successfully'));
    }

    //    get the view of show admin data with (admin id)
    public function show($id)
    {
        return view('adminmodule::show');
    }

    // view to edit/Update admin data with (admin id)
    public function edit($id)
    {
        $admin = $this->adminService->findOne($id);
        return view('adminmodule::admin.edit', compact('admin'));
    }

    //  save updated data in database
    public function update(Request $request)
    {
        // check incoming data if it meet validation rules or not
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'email' => 'required|email|unique:admins,email,' . $request->id,
                'password' => 'nullable|min:6',
            ]
        );
        // return error with unvalidated data
        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }
        // set value of 'first_password' column in admin table  
        $request['first_password'] = null;
        $requests = $request->all();
        // send new admin's data to the service to be updated in database
        $this->adminService->update($requests);
        // if database updated it will return true then redirect to admins list view
        return redirect()->route('admin.admins.index')
            ->with('success', trans('messages.Updated_Successfully'));
    }

    //   function to delete an admin with (admin id)
    public function destroy($id)
    {
        $admin = $this->adminService->deleteOne($id);  //send id to the service to delete the row which has this id

        // if 'deleteOne' function delete the row it will return 'true' else it will return 'false'
        if ($admin) {
            // use json response for Ajax
            return response()->json('true');
        } else {
            return response()->json('false');
        }
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

    public function delete($id)
    {
        $data = $this->adminService->deleteOne($id);  //send id to the service to delete the row which has this id
        // if 'deleteOne' function delete the row it will return 'true' else it will return 'false'
        if ($data) {
            // use json response for Ajax
            return redirect()->route('admin.admins.index')->with('success', 'تم الحذف');
        } else {
            return redirect()->route('admin.admins.index')->with('error', 'تعذر الحذف');
        }
    }
}
