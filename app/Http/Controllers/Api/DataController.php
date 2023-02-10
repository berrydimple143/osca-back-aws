<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Mail\ContactMail;
use App\Mail\MailNotify;
use App\Models\Municipality;
use App\Models\Barangay;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class DataController extends Controller
{
    public function sendEmail(Request $request) 
    {
        try
        {
            $now = Carbon::now()->format('Y-m-d H:i:s');
            $details = [
                'name' => $request->name,
                'email' => $request->email,
                'subject' => $request->subject,
                'message' => $request->message,
                'created_at' => $now,
                'updated_at' => $now
            ];
            DB::beginTransaction();
                $ins = DB::table('contacts')->insert($details);
            DB::commit();
            Mail::to("support@rpcbulacan.org")->cc($request->email)->bcc("dimplevirgil@gmail.com")->send(new MailNotify($details));
            $msg = 'success';
        } catch (Exception $e) 
        {
            DB::rollBack();
            $msg = $e->getMessage();
        }
        return response()->json([
            'send_status' => $msg
        ]);
    }
    private function getCount() 
    {
        $arg_list = func_get_args();
        $to = str_replace("00:00:00", "11:59:59", $arg_list[1]);
        if($arg_list[4] == "municipality") {
            $data = DB::table('users AS u')
                    ->join('user_details AS d', 'u.id', '=', 'd.user_id')
                    ->join('user_address AS a', 'u.id', '=', 'a.user_id')
                    ->whereNull('u.deleted_at')
                    ->whereNotNull('u.id_number')
                    ->where('u.created_at', '>=', $arg_list[0])->where('u.created_at', '<=', $to)
                    ->where($arg_list[5], $arg_list[2])->where('a.municipality', $arg_list[3])
                    ->count();
        } else if($arg_list[4] == "barangay") {
            $data = DB::table('users AS u')
                    ->join('user_details AS d', 'u.id', '=', 'd.user_id')
                    ->join('user_address AS a', 'u.id', '=', 'a.user_id')
                    ->whereNull('u.deleted_at')
                    ->whereNotNull('u.id_number')
                    ->where('u.created_at', '>=', $arg_list[0])->where('u.created_at', '<=', $to)
                    ->where($arg_list[5], $arg_list[2])->where('a.barangay', $arg_list[3])
                    ->count();
        }
        return $data;
    }
    
    public function getBarangayData(Request $request)
    {
        $ctype = $request->mun;
        $dtf = $request->from. ' 00:00:00';
        $dtt = $request->to. ' 00:00:00';
        $passed = $hrisk = $arisk = $inc = $act = $listOfGeneralStatus = $employed = $unemployed = $self_employed = [];
        if($ctype == "all") 
        {
            $listOfGeneralStatus = Municipality::orderBy('municipality_name')->get();
            foreach($listOfGeneralStatus as $mun) {
                $passed[] = $this->getCount($dtf, $dtt, 'Passed Away', $mun->municipality_code_number, 'municipality', 'd.member_status');
                $hrisk[] = $this->getCount($dtf, $dtt, 'High Risk', $mun->municipality_code_number, 'municipality', 'd.member_status');
                $arisk[] = $this->getCount($dtf, $dtt, 'At Risk', $mun->municipality_code_number, 'municipality', 'd.member_status');
                $inc[] = $this->getCount($dtf, $dtt, 'Inactive', $mun->municipality_code_number, 'municipality', 'd.member_status');
                $act[] = $this->getCount($dtf, $dtt, 'Active', $mun->municipality_code_number, 'municipality', 'd.member_status');
            }
        } else 
        {
            $listOfGeneralStatus = Barangay::where('municipality_code_number', $ctype)->orderBy('barangay_name')->get();
            foreach($listOfGeneralStatus as $bar) {
                $passed[] = $this->getCount($dtf, $dtt, 'Passed Away', $bar->id, 'barangay', 'd.member_status');
                $hrisk[] = $this->getCount($dtf, $dtt, 'High Risk', $bar->id, 'barangay', 'd.member_status');
                $arisk[] = $this->getCount($dtf, $dtt, 'At Risk', $bar->id, 'barangay', 'd.member_status');
                $inc[] = $this->getCount($dtf, $dtt, 'Inactive', $bar->id, 'barangay', 'd.member_status');
                $act[] = $this->getCount($dtf, $dtt, 'Active', $bar->id, 'barangay', 'd.member_status');
            }
        }
        $graphArr = [];
        foreach($listOfGeneralStatus as $loc) {
            if($ctype == "all") {
                $graphArr[] = $loc->municipality_name;
            } else {
                $graphArr[] = $loc->barangay_name;
            }
        }
        $labels = join(",", $graphArr);
        $active = join(",", $act);
        $inactive = join(",", $inc);
        $highRisk = join(",", $hrisk);
        $atRisk = join(",", $arisk);
        $passedAway = join(",", $passed);
        return response()->json([
            'classtype' => $ctype,
            'labels' => $labels,
            'passedAwayData' => $passedAway,
            'highRiskData' => $highRisk,
            'atRiskData' => $atRisk,
            'inactiveData' => $inactive,
            'activeData' => $active,
            'listOfGeneralStatus' => $listOfGeneralStatus
        ]);
    }
    public function getAllMunicipalities() {
        return DB::table('municipalities')->select('municipality_code_number', 'municipality_name', 'district_no')->orderBy('municipality_name')->get();
    }
    public function getMunicipalities() {
        $mun = $this->getAllMunicipalities();
        return response()->json(['mun' => $mun]);
    }
    public function getReligions() {
        return DB::table('religions')->get();
    }
    public function getGenders() {
        return DB::table('genders')->get();
    }
    public function getCivilStatus() {
        return DB::table('civil_statuses')->get();
    }
    public function getBloodTypes() {
        return DB::table('blood_types')->get();
    }
    public function getEducation() {
        return DB::table('education')->get();
    }
    public function getEmploymentStatus() {
        return DB::table('employment_statuses')->get();
    }
    public function getClassifications() {
        return DB::table('classifications')->get();
    }
    public function getIllnesses() {
        return DB::table('illness')->get();
    }
    public function getMemberStatuses() {
        return DB::table('member_statuses')->get();
    }
    public function getDetails()
    {
        return response()->json([
            'status' => 'success',
            'mun' => $this->getAllMunicipalities(),
            'genders' => $this->getGenders(),
            'civil_statuses' => $this->getCivilStatus(),
            'member_statuses' => $this->getMemberStatuses(),
            'blood_types' => $this->getBloodTypes(),
            'religions' => $this->getReligions(),
            'education' => $this->getEducation(),
            'employment_statuses' => $this->getEmploymentStatus(),
            'classifications' => $this->getClassifications(),
            'illness' => $this->getIllnesses()
        ]);
    }
    public function getBarangays($mun)
    {
        $barangays = DB::table('barangays')->select('id', 'barangay_name')->where('municipality_code_number', $mun)->orderBy('barangay_name')->get();
        $municipality = DB::table('municipalities')->select('district_no')->where('municipality_code_number', $mun)->first();
        return response()->json([
            'status' => 'success',
            'barangays' => $barangays,
            'district_no' => $municipality->district_no
        ]);
    }
    public function checkBirth($birth)
    {
        $age = Carbon::parse($birth)->diff(Carbon::now())->y;
        $stat = "not qualified";
        if((int)$age >= 60)
        {
            $stat = "qualified";
        }
        return response()->json(['age_status' => $stat]);
    }
    public function fileUpload(Request $request)
    {
        
    }
    public function generateAndGetID($mun, $dist)
    {
        $municipality = DB::table('municipalities')->select('municipality_code_name')->where('municipality_code_number', $mun)->first();
        $cntr = DB::table('user_address')->where('municipality', $mun)->where('district_no', $dist)->count();
        $ID = "";
        $num = (int)$cntr + 1;
        $len = strlen((string)$num);
        $code_name = $municipality->municipality_code_name;
        $dist_no = "LD";
        if($dist != "LD") {
            $dist_no = "00".$dist;
        }
        if($len == 1) {
            $ID = $dist_no."-".$code_name."-"."00000".(string)$num;
        } else if($len == 2) {
            $ID = $dist_no."-".$code_name."-"."0000".(string)$num;
        } else if($len == 3) {
            $ID = $dist_no."-".$code_name."-"."000".(string)$num;
        } else if($len == 4) {
            $ID = $dist_no."-".$code_name."-"."00".(string)$num;
        } else if($len == 5) {
            $ID = $dist_no."-".$code_name."-"."0".(string)$num;
        } else if($len == 6) {
            $ID = $dist_no."-".$code_name."-".(string)$num;
        } else {
            $ID = "limit";
        }
        return response()->json(['senior_id' => $ID]);
    }
    public function checkId($idNumber)
    {
        $user_count = DB::table('users')->where('id_number', $idNumber)->count();
        $stat = "vacant";
        if($user_count > 0) {
            $stat = "available";
        }
        return response()->json(['stat' => $stat]);
    }
}
