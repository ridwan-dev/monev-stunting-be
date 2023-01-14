<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\UserResource;
use App\JsonResponse;
use App\Permission;
use App\Role;
use App\Models\Sys\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Validator;

/**
 * Class UserController
 *
 * @package App\Http\Controllers\Api
 */
class UserController extends BaseController
{
    const ITEM_PER_PAGE = 15;

    /**
     * Display a listing of the user resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|ResourceCollection
     */
    public function index(Request $request)
    {
        $searchParams = $request->all();
        $userQuery = User::query();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $role = Arr::get($searchParams, 'role', '');
        $keyword = Arr::get($searchParams, 'keyword', '');

        if (!empty($role)) {
            $userQuery->whereHas('roles', function($q) use ($role) { $q->where('name', $role); });
        }

        if (!empty($keyword)) {
            $userQuery->where('name', 'LIKE', '%' . $keyword . '%');
            $userQuery->where('email', 'LIKE', '%' . $keyword . '%');
        }

        return UserResource::collection($userQuery->paginate($limit));
    }

    public function getMe(Request $request){
        $user = request()->user();

        return $this->returnJsonSuccess("Profile fetched successfully", $user);
    }

    public function putMe(Request $request){
        $validator = Validator::make($request->all(),
        [
            'nama' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users'],
            'usergroup' => ['required', 'string'],
            'kl_id' => ['string', 'nullable'],
            'prov_id' => ['string', 'nullable'],
            'city_id' => ['string', 'nullable'],
            'nama_kantor' => ['string'],
            'jabatan_eselon_2' => ['string'],
            'pejabat_eselon_2' => ['string'],
            'alamat' => ['string'],
            'email_tambahan' => ['email', 'nullable'],
            'no_telp' => ['string'],
            'no_hp' => ['string'],
            'file' => ['file', 'max:3072', 'mimes:pdf,doc,docx'],
        ],
        [
            'nama.required' => "Nama wajib diisi",
            'email.required' => "Email wajib diisi",
            'email.unique' => "Email sudah terdaftar",
            'email.email' => "Silahkan isi email dengan benar",
            'usergroup.required' => "User group wajib diisi",
            'file.max' => 'Maksimal ukurang file yang dapat diupload 3MB',
            'file.mimes' => 'Hanya dapat upload file pdf, docx',
        ]);
        
        if($validator->fails()){
            return $this->returnJsonError($validator->messages(), 500);
        }
        
        $user = User::find(request()->user()->id);

        $user->name = $request->nama;
        if(!empty($request->email)){
            $user->email_baru = $request->email;
        }
        
        $user->usergroup = $request->usergroup;
        $user->kl_id = $request->kl_id;
        $user->prov_id = $request->prov_id;
        $user->city_id = $request->city_id;
        $user->nama_kantor = $request->nama_kantor;
        $user->jabatan_eselon_2 = $request->jabatan_eselon_2;
        $user->pejabat_eselon_2 = $request->pejabat_eselon_2;
        $user->alamat = $request->alamat;
        $user->email_tambahan = $request->email_tambahan;
        $user->no_telp = $request->no_telp;
        $user->no_hp = $request->no_hp;

        $file = $request->file('file');
        $folder = public_path() . '/uploads/regis/';

        if (!\Storage::exists($folder)) {
            \Storage::makeDirectory($folder, 0775, true, true);
        }

        if (!empty($file)) {
            $fileName = $file->getClientOriginalName();
            $_fileName = time().'_'.preg_replace('/\s+/', '_', $file->getClientOriginalName());
            \Storage::disk('public_uploads_regis')->put($_fileName, file_get_contents($file));

            $user->file_upload_orig = $fileName;
            $user->file_upload_path = $_fileName;
        }

        $user->save();

        return $this->returnJsonSuccess("User registered successfully", $user);

    }

    public function registrasi(Request $request){
        $validator = Validator::make($request->all(),
        [
            'nama' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'min:6'],
            'confirmPassword' => ['same:password'],
            'usergroup' => ['required', 'string'],
            'kl_id' => ['string', 'nullable'],
            'prov_id' => ['string', 'nullable'],
            'city_id' => ['string', 'nullable'],
            'nama_kantor' => ['string'],
            'jabatan_eselon_2' => ['string'],
            'pejabat_eselon_2' => ['string'],
            'alamat' => ['string'],
            'email_tambahan' => ['email', 'nullable'],
            'no_telp' => ['string'],
            'no_hp' => ['string'],
            'file' => ['required', 'file', 'max:3072', 'mimes:pdf,doc,docx'],
        ],
        [
            'nama.required' => "Nama wajib diisi",
            'email.required' => "Email wajib diisi",
            'email.unique' => "Email sudah terdaftar",
            'email.email' => "Silahkan isi email dengan benar",
            'password.required' => "Password wajib diisi",
            'password.min' => "Password minimal 6 karakter",
            'usergroup.required' => "User group wajib diisi",
            'file.required' => 'File wajib diupload',
            'file.max' => 'Maksimal ukurang file yang dapat diupload 3MB',
            'file.mimes' => 'Hanya dapat upload file pdf, docx',
        ]);
        
        if($validator->fails()){
            return $this->returnJsonError($validator->messages(), 500);
        }

        $user = new User();
        $user->name = $request->nama;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->usergroup = $request->usergroup;
        $user->kl_id = $request->kl_id;
        $user->prov_id = $request->prov_id;
        $user->city_id = $request->city_id;
        $user->nama_kantor = $request->nama_kantor;
        $user->jabatan_eselon_2 = $request->jabatan_eselon_2;
        $user->pejabat_eselon_2 = $request->pejabat_eselon_2;
        $user->alamat = $request->alamat;
        $user->email_tambahan = $request->email_tambahan;
        $user->no_telp = $request->no_telp;
        $user->no_hp = $request->no_hp;

        $file = $request->file('file');
        $folder = public_path() . '/uploads/regis/';

        if (!\Storage::exists($folder)) {
            \Storage::makeDirectory($folder, 0775, true, true);
        }

        if (!empty($file)) {
            $fileName = $file->getClientOriginalName();
            $_fileName = time().'_'.preg_replace('/\s+/', '_', $file->getClientOriginalName());
            \Storage::disk('public_uploads_regis')->put($_fileName, file_get_contents($file));
        }

        $user->file_upload_orig = $fileName;
        $user->file_upload_path = $_fileName;

        $user->save();

        $details = [
            'title' => 'Reset Password',
            'body' => 'Silahkan klik link dibawah untuk reset password.',
            'hash' => \UrlHash::encodeId('cirgobanggocir', $user->id, 150),
            'email' => $user->email
        ];
       
        \Mail::to($user->email)->send(new \App\Mail\ForgotPasswordMail($details));

        return $this->returnJsonSuccess("User registered successfully", $user);
        
    }

    public function listRegisteredUser(Request $request){
        $listUser = User::where('status_verifikasi', '0')
                        ->orWhere('status_aktif', '0')
                        ->orWhereIn('status_verifikasi_email', ['0', '2', '3']) 
                        ->get();

        return $this->returnJsonSuccess("User registration list fetched successfully", $listUser);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            array_merge(
                $this->getValidationRules(),
                [
                    'password' => ['required', 'min:6'],
                    'confirmPassword' => 'same:password',
                ]
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();
            $user = User::create([
                'name' => $params['name'],
                'email' => $params['email'],
                'password' => Hash::make($params['password']),
            ]);
            $role = Role::findByName($params['role']);
            $user->syncRoles($role);

            return new UserResource($user);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  User $user
     * @return UserResource|\Illuminate\Http\JsonResponse
     */
    public function show(User $user)
    {
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param User    $user
     * @return UserResource|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, User $user)
    {
        if ($user === null) {
            return response()->json(['error' => 'User not found'], 404);
        }
        if ($user->isAdmin()) {
            return response()->json(['error' => 'Admin can not be modified'], 403);
        }

        $currentUser = Auth::user();
        if (!$currentUser->isAdmin()
            && $currentUser->id !== $user->id
            && !$currentUser->hasPermission(\App\Laravue\Constants::PERMISSION_USER_MANAGE)
        ) {
            return response()->json(['error' => 'Permission denied'], 403);
        }

        $validator = Validator::make($request->all(), $this->getValidationRules(false));
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $email = $request->get('email');
            $found = User::where('email', $email)->first();
            if ($found && $found->id !== $user->id) {
                return response()->json(['error' => 'Email has been taken'], 403);
            }

            $user->name = $request->get('name');
            $user->email = $email;
            $user->save();
            return new UserResource($user);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param User    $user
     * @return UserResource|\Illuminate\Http\JsonResponse
     */
    public function updatePermissions(Request $request, User $user)
    {
        if ($user === null) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($user->isAdmin()) {
            return response()->json(['error' => 'Admin can not be modified'], 403);
        }

        $permissionIds = $request->get('permissions', []);
        $rolePermissionIds = array_map(
            function($permission) {
                return $permission['id'];
            },

            $user->getPermissionsViaRoles()->toArray()
        );

        $newPermissionIds = array_diff($permissionIds, $rolePermissionIds);
        $permissions = Permission::allowed()->whereIn('id', $newPermissionIds)->get();
        $user->syncPermissions($permissions);
        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        if ($user->isAdmin()) {
            return response()->json(['error' => 'Ehhh! Can not delete admin user'], 403);
        }

        try {
            $user->delete();
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 403);
        }

        return response()->json(null, 204);
    }

    /**
     * Get permissions from role
     *
     * @param User $user
     * @return array|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function permissions(User $user)
    {
        try {
            return new JsonResponse([
                'user' => PermissionResource::collection($user->getDirectPermissions()),
                'role' => PermissionResource::collection($user->getPermissionsViaRoles()),
            ]);
        } catch (\Exception $ex) {
            response()->json(['error' => $ex->getMessage()], 403);
        }
    }

    /**
     * @param bool $isNew
     * @return array
     */
    private function getValidationRules($isNew = true)
    {
        return [
            'name' => 'required',
            'email' => $isNew ? 'required|email|unique:users' : 'required|email',
            'roles' => [
                'required',
                'array'
            ],
        ];
    }
}
