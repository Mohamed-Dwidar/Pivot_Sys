<?php

namespace Modules\UserModule\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Support\Renderable;
use Modules\UserModule\app\Services\UserService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class UserModuleController extends Controller
{

    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function loginForm()
    {
        return view('usermodule::user.login');
    }

    public function loginUser(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make(
            $request->all(),
            [
                'password' => 'required',
                'username' => 'required',
            ],
            [
                'username.required' => 'إسم المستخدم مطلوب .',
                'password.required' => 'كلمه المرور مطلوبه.',
            ]

        );
        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $credentials = $request->only('username', 'password');

        if (Auth::attempt($credentials)) {
            return redirect()->intended('admin/dashboard');
        }
        $data = $this->userService->login($credentials);
        return redirect('/')->with('error', 'خطأ في إسم المستخدم او كلمه المرور');
    }

    public function changePassword()
    {
        return view('usermodule::user.change_password');
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required',
            'confirm_password' => 'required|same:new_password',
        ], [
            // Custom error messages in Arabic
            'current_password.required' => 'يجب إدخال كلمة المرور الحالية.',
            'new_password.required' => 'يجب إدخال كلمة المرور الجديدة.',
            'confirm_password.required' => 'يجب إدخال تأكيد كلمة المرور.',
            'confirm_password.same' => 'تأكيد كلمة المرور يجب أن يتطابق مع كلمة المرور الجديدة.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();
        if ($this->userService->checkExistPass($user->id, $request->current_password) == false) {
            return redirect()->back()->withErrors(['error' => ' خطأ في كلمة المرور الحالية .'])->withInput();
        } else {
            if ($this->userService->updatePassword($request->new_password, $user->id)) {
                return redirect()->route('login')->with('success', 'تم تغيير كلمة المرور بنجاح');
            }
        }
    }


    public function logout(Request $request)
    {
        Auth::logout();
        return redirect('/');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('usermodule::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('usermodule::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('usermodule::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
}
