<?php

namespace App\Http\Controllers;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\RegisterAdminRequest;
use App\Models\Address;
use App\Models\Benefit;
use App\Models\Classification;
use App\Models\Contact;
use App\Models\Detail;
use App\Models\Sickness;
use App\Models\User;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Image;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register','registerAdmin']]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $token = Auth::attempt($credentials, ['exp' => Carbon::now()->addDays(1)->timestamp]);
        if (!$token) {
            return response()->json([
                'login_status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user();
        $role = $user->roles->pluck('name')[0];
        return response()->json([
            'login_status' => 'success',
            'user_id' => $user->id,
            'user_first_name' => $user->first_name,
            'user_status' => $user->status,
            'role' => $role,
            'token' => $token
        ]);
    }

    public function registerAdmin(Request $request)
    {
        try
        {
            DB::beginTransaction();
                $user = User::create([
                    'first_name' => ucwords($request->first_name),
                    'last_name' => ucwords($request->last_name),
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);
            DB::commit();
            $role = Role::where('name','admin')->first();
            $user->assignRole($role);
            $token = Auth::login($user);
            return response()->json([
                'admin_status' => 'success',
                'message' => 'Admin user created successfully.',
                'user' => $user,
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]);
        } catch (Exception $e)
        {
            DB::rollBack();
            return response()->json([
                'admin_status' => $e->getMessage()
            ]);
        }
    }

    public function register(Request $request)
    {
        try
        {
            DB::beginTransaction();
                $user = User::create([
                    'id_number' => $request->id_number,
                    'first_name' => ucwords($request->first_name),
                    'last_name' => ucwords($request->last_name),
                    'middle_name' => ucwords($request->middle_name),
                    'email ' => $request->email,
                ]);
            DB::commit();
            $contact = Contact::create([
                'user_id' => $user->id,
                'phone' => $request->phone,
                'mobile' => $request->mobile,
                'contact_person' => $request->contact_person,
                'contact_person_number' => $request->contact_person_number,
            ]);
            $address = Address::create([
                'user_id' => $user->id,
                'province' => '044',
                'province_name' => $request->province,
                'municipality' => $request->municipality,
                'municipality_name' => $request->municipality_name,
                'barangay' => $request->barangay,
                'barangay_name' => $request->barangay_name,
                'address' => $request->address,
                'birth_place' => $request->birth_place,
                'district_no' => $request->district_no,
            ]);
            $benefit = Benefit::create([
                'user_id' => $user->id,
                'gsis' => $request->gsis,
                'sss' => $request->sss,
                'tin' => $request->tin,
                'philhealth' => $request->philhealth,
                'pension' => $request->pension,
            ]);

            $illness = Sickness::create([
                'user_id' => $user->id,
                'sickness' => $request->selected_illness,
            ]);
            $classification = Classification::create([
                'user_id' => $user->id,
                'classification' => $request->member_type,
            ]);

            $filename = null;
            if(!empty($request->data))
            {
                $now = Carbon::now()->format('Y-m-d-H-i-s');
                $filename = $request->id_number.'-vaccine-card-'.$now.'.png';
                $img = Image::make($request->data)->resize(250, 180);
                $fullPath = public_path('images/id_cards/'.$filename);
                $img->save($fullPath);
            }
            $mStatus = $request->member_status;
            if(empty($mStatus))
            {
               $mStatus = "Active";
            }
            $detail = Detail::create([
                'user_id' => $user->id,
                'birth_date' => $request->formatted_bday,
                'religion' => $request->religion,
                'blood_type' => $request->blood_type,
                'education' => $request->education,
                'employment_status' => $request->employment_status,
                'member_status' => $mStatus,
                'civil_status' => $request->civil_status,
                'gender' => $request->gender,
                'identification' => $filename,
            ]);
            return response()->json([
                'reg_status' => 'success',
                'message' => 'User registered successfully.'
            ]);
        } catch (Exception $e)
        {
            DB::rollBack();
            return response()->json([
                'reg_status' => 'failed',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

}
