<?php

namespace Modules\UserModule\app\Http\Controllers\Admin;

use App\Helpers\ApiResponseHelper;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\AdminModule\app\Http\Requests\LoginAdminRequest;
use Modules\UserModule\app\Http\Requests\UserRequest;
use Modules\UserModule\app\Services\UserService;
use Modules\UserModule\Transformers\UserAuthResource;
use Modules\UserModule\Transformers\UserResource;

class UserModuleController extends Controller
{
    private $userService;
    use ApiResponseHelper;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function getAll(Request $request)
    {
        $users = $this->userService->findAll();
        // return $user->getAllPermissions()->pluck('name');

        if ($users) {
            $users = UserResource::collection($users);
            return $this->json(200, true, $users, trans('messages.Success'), trans('messages.Success'));
        } else {
            return $this->error(400, false, trans('messages.error'), trans('messages.error'));
        }
    }

    public function getPermissions($id)
    {
        $user = $this->userService->findOne($id);
        // $user = Auth::guard('api')->user();

        // dd($user);
        if ($user) {
            return $this->json(200, true, [

                'permissions' => $user->getAllPermissions()->pluck('name'),

            ], trans('messages.Success'), trans('messages.Success'));

        } else {
            return $this->error(400, false, trans('messages.error'), trans('messages.error'));
        }

    }

    public function getPermissionsUser()
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            $user = Auth::guard('api')->user();
        }

        if ($user->count() > 0) {
            if (count($user->getAllPermissions()) > 0) {

                return $this->json(200, true, [

                    'permissions' => $user->getAllPermissions()->pluck('name'),

                ], trans('messages.Success'), trans('messages.Success'));
            } else {
                return $this->json(200, true, [

                    'permissions' => [],

                ], trans('messages.Success'), trans('messages.Success'));

            }

        } else {
            return $this->error(400, false, trans('messages.id_not_found'), trans('messages.error'));
        }

    }

    
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('usermodule::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(UserRequest $request)
    {
        $users = $this->userService->create($request->all());
        if ($users) {
            $users = UserResource::make($users);

            return $this->json(200, true, $users, trans('messages.Success'), trans('messages.Success'));
        } else {
            return $this->error(400, false, trans('messages.error'), trans('messages.error'));
        }

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

    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $data = $this->userService->findWhere(['id' => $id]);
        if ($data->count() > 0) {
            return $this->json(200, true, UserResource::make($data->first()), trans('messages.Success'));

        } else {
            return $this->error(400, false, trans('messages.id_not_found'), trans('messages.error'));
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $data = $this->userService->findWhere(['id' => $id]);
        if ($data->count() > 0) {

            $data = $this->userService->update($request->all(), $id);

            return $this->json(200, true, UserResource::make($data), trans('messages.Success'));

        } else {
            return $this->error(400, false, trans('messages.id_not_found'), trans('messages.error'));
        }

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
