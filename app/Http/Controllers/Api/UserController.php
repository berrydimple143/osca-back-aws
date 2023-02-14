<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Address;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use DB;
use Exception;
use Carbon\Carbon;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    public function getUsers(Request $request)
    {
        try {
            DB::beginTransaction();
                $users = DB::table('users')
                    ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->leftJoin('role_has_permissions', 'roles.id', '=', 'role_has_permissions.role_id')
                    ->leftJoin('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
                    ->select('users.id AS id', 'users.id_number AS id_number', 'users.first_name AS first_name', 'users.last_name AS last_name', 'users.email AS email', 'users.status AS status', 'roles.name AS role', 'permissions.name AS permission')
                    ->whereNull('users.id_number')->whereNull('users.deleted_at')->orderBy('users.last_name')->get();
            DB::commit();
            $msg = 'success';
        } catch (Exception $e)
        {
            DB::rollBack();
            $msg = $e->getMessage();
        }
        return response()->json([
            'user_status' => $msg,
            'users' => $users
        ]);
    }
    public function saveUser(Request $request)
    {
        try
        {
            DB::beginTransaction();
                $user = User::create([
                    'first_name' => ucwords($request->first_name),
                    'middle_name' => ucwords($request->middle_name),
                    'last_name' => ucwords($request->last_name),
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);
            DB::commit();
            $role = $request->role;

            if($role == "team lead" || $role == "encoder")
            {
                $address = Address::create([
                    'user_id' => $user->id,
                    'province' => '044',
                    'province_name' => 'BULACAN',
                    'municipality' => $request->selected_municipalities,
                ]);
            }
            $rl = Role::firstOrCreate(
                ['name' => $role],
                ['guard_name' => 'web']
            );
            $user->assignRole($rl);
            $msg = 'success';
        } catch (Exception $e)
        {
            DB::rollBack();
            $msg = $e->getMessage();
        }
        return response()->json([
            'register_status' => $msg,
            'user' => $user
        ]);
    }
    public function updateUser(Request $request)
    {
        $id = $request->id;
        $values = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middle_name' => $request->middle_name,
            'email' => $request->email,
        ];
        try {
            $affected = DB::table('users')->where('id', $id)->update($values);
            $role = DB::table('roles')->where('name', $request->role)->first();
            if(!$role) {
                $role = Role::create(['name' => $request->role, 'guard_name' => 'web']);
            }
            $affectedRole = DB::table('model_has_roles')->where('model_id', $id)->update(['role_id' => $role->id]);
            if($role->name == "team lead" || $role->name == "encoder")
            {
                $affectedMun = DB::table('user_address')->where('user_id', $id)->update(['municipality' => $request->selected_municipalities]);
            } else
            {
                $affectedMun = DB::table('user_address')->where('user_id', $id)->update(['province' => null, 'province_name' => null, 'municipality' => null]);
            }
            $msg = 'success';
        } catch (Exception $e)
        {
            DB::rollBack();
            $msg = $e->getMessage();
        }
        return response()->json([
            'update_status' => $msg
        ]);
    }
    public function getUser(Request $request)
    {
        $msg = "";
        try {
            DB::beginTransaction();
            $user = DB::table('users')
                    ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->leftJoin('user_address', 'users.id', '=', 'user_address.user_id')
                    ->select('users.id AS id', 'users.first_name AS first_name', 'users.last_name AS last_name', 'users.middle_name AS middle_name', 'users.email AS email', 'roles.name AS role', 'user_address.municipality AS municipality')->where('users.id', $request->id)->first();
            DB::commit();
            $msg = 'success';
        } catch (Exception $e)
        {
            DB::rollBack();
            $msg = $e->getMessage();
        }
        return response()->json([
            'user_status' => $msg,
            'user' => $user
        ]);
    }
    public function deleteUser(Request $request)
    {
        try {
            DB::beginTransaction();
                $deleted = User::where('id', $request->id)->delete();
            DB::commit();
            $admin = Auth::user();
            $affected = DB::table('users')->where('id', $request->id)->update(['deleted_by' => $admin->id]);
            $msg = 'success';
        } catch (Exception $e)
        {
            DB::rollBack();
            $msg = $e->getMessage();
        }
        return response()->json([
            'delete_status' => $msg,
        ]);
    }
    public function emailExist(Request $request)
    {
        $oldEmail = '';
        try {
            $msg = 'not found';
            DB::beginTransaction();
                if($request->page == "add") {
                    if(DB::table('users')->where('email', $request->email)->exists()) {
                        $msg = 'found';
                    }
                } else if($request->page == "edit") {
                    $user = DB::table('users')->where('id', $request->id)->first();
                    $oldEmail = $user->email;
                    if(DB::table('users')->where('email', '!=', $oldEmail)->where('email', $request->email)->exists()) {
                        $msg = 'found';
                    }

                }
            DB::commit();
        } catch (Exception $e)
        {
            DB::rollBack();
            $msg = $e->getMessage();
        }
        return response()->json([
            'email_status' => $msg,
            'email' => $oldEmail
        ]);
    }
    public function changePassword(Request $request)
    {
        try
        {
            DB::beginTransaction();
                if($request->type == "admin") {
                    $admin = Auth::user();
                    $updated = DB::table('users')->where('id', $admin->id)->update(['password' => Hash::make($request->password)]);
                } else {
                    $updated = DB::table('users')->where('id', $request->id)->update(['password' => Hash::make($request->password)]);
                }
            DB::commit();
            $msg = 'success';
        } catch (Exception $e)
        {
            DB::rollBack();
            $msg = $e->getMessage();
        }
        return response()->json([
            'password_status' => $msg
        ]);
    }
    public function checkPassword(Request $request)
    {
        $admin = Auth::user();
        $msg = 'not match';
        try
        {
            DB::beginTransaction();
                $user = DB::table('users')->where('id', $admin->id)->first();
            DB::commit();
            if (Hash::check($request->password, $user->password))
            {
                $msg = 'match';
            }
        } catch (Exception $e)
        {
            DB::rollBack();
            $msg = $e->getMessage();
        }
        return response()->json([
            'password_status' => $msg
        ]);
    }
    public function getUserStatus(Request $request)
    {
        try
        {
            DB::beginTransaction();
                $user = DB::table('users')->select('status')->where('id', $request->id)->first();
            DB::commit();
            $msg = $user->status;
        } catch (Exception $e)
        {
            DB::rollBack();
            $msg = $e->getMessage();
        }
        return response()->json([
            'user_status' => $msg
        ]);
    }
    public function activateAccount(Request $request)
    {
        try
        {
            DB::beginTransaction();
                if($request->status == 0 || $request->status == '0' || $request->status == false)
                {
                    $updated = DB::table('users')->where('id', $request->id)->update(['status' => true]);
                } else
                {
                    $updated = DB::table('users')->where('id', $request->id)->update(['status' => false]);
                }
            DB::commit();
            $msg = 'success';
        } catch (Exception $e)
        {
            DB::rollBack();
            $msg = $e->getMessage();
        }
        return response()->json([
            'activation_status' => $msg
        ]);
    }
}
