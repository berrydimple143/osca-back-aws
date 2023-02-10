<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use DB;
use Exception;
use Carbon\Carbon;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    public function getRoles(Request $request)
    {
        try {
            DB::beginTransaction();
                $roles = DB::table('roles')->select('id','name')->orderBy('name')->get();
            DB::commit();
            $msg = 'success';
        } catch (Exception $e) 
        {
            DB::rollBack();
            $msg = $e->getMessage();
        }
        return response()->json([
            'role_status' => $msg,
            'roles' => $roles
        ]);
    }
}
