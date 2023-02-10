<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function getTotalMembersPerMunicipality()
    {
        $user = Auth::user();
        $role = $user->roles->pluck('name')[0];
        $locations = DB::table('municipalities')->select('municipality_code_number', 'municipality_name')->orderBy('municipality_name')->get();
        $today = DB::table('users')->whereNull('deleted_at')->whereDate('created_at', Carbon::now())->whereNotNull('id_number')->count();
        $total = DB::table('users')->whereNull('deleted_at')->whereNotNull('id_number')->count();
        $arrDelete = [];
        $deleted = DB::table('users')->whereNotNull('deleted_at')->get();
        foreach($deleted as $del) {
            $arrDelete[] = $del->id;
        }
        if($role == "team lead" or $role == "encoder") {
            $municipality = DB::table('users')->join('user_address', 'users.id', '=', 'user_address.user_id')->select('user_address.municipality')->where('users.id', $user->id)->first();
            $mun = empty($municipality) ? '' : $municipality->municipality;
            $arr_mun = [];
            if(!empty($mun)) {
                if(Str::contains($mun, ',')) {
                    $arr_mun = explode(',', $mun);
                } else {
                    $arr_mun[] = $mun;
                }
            }
            $today = DB::table('users')->join('user_address', 'users.id', '=', 'user_address.user_id')->whereDate('users.created_at', Carbon::now())->whereNotNull('users.id_number')->whereNull('users.deleted_at')->whereIn('user_address.municipality', $arr_mun)->count();
            $total = DB::table('users')->join('user_address', 'users.id', '=', 'user_address.user_id')->whereNotNull('users.id_number')->whereNull('users.deleted_at')->whereIn('user_address.municipality', $arr_mun)->count();
            $locations = DB::table('municipalities')->select('municipality_code_number', 'municipality_name')->whereIn('municipality_code_number', $arr_mun)->orderBy('municipality_name')->get();
        }
        $graphArr = $counterArr = [];
        foreach($locations as $loc) {
            $graphArr[] = $loc->municipality_name;
            $counterArr[] = DB::table('user_address')->where('municipality', $loc->municipality_code_number)->whereNotIn('user_id', $arrDelete)->count();
        }
        $labels = join(",", $graphArr);
        $info = join(",", $counterArr);

        return response()->json([
            'status' => 'success',
            'labels' => $labels,
            'today' => $today,
            'total' => $total,
            'info' => $info
        ]);
    }
}
