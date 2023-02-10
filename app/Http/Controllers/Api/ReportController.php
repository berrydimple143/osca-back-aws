<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Municipality;
use App\Models\Barangay;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Exports\ReportsExport;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    public function exportReport(Request $request)
    {
        $now = Carbon::now()->format('Y_m_d_H_i_s');
        $filename = "reports_".$now.'.xlsx';
        $page = $request->page;
        $classtype = strtolower($request->ctype);
        $headings = [];
        if($page == "members") {
            $headings = [ucfirst($classtype), 'Active', 'Inactive', 'At Risk', 'High Risk', 'Passed Away', 'Total No. of Senior Citizens'];
            $passedAway = $request->passedAway;
            $highRisk = $request->highRisk;
            $atRisk = $request->atRisk;
            $inactive = $request->inactive;
            $active = $request->active;
            $filename = "member_status_reports_per_municipality_".$now.'.xlsx';
            if($classtype == "barangay") {
                $filename = "member_status_reports_per_barangay_".$now.'.xlsx';
            }
        } else if($page == "civil_status") {
            $headings = [ucfirst($classtype), 'Single', 'Married', 'Widowed', 'Separated', 'Divorced', 'Total No. of Senior Citizens'];
            $single = $request->single;
            $married = $request->married;
            $widowed = $request->widowed;
            $divorced = $request->divorced;
            $separated = $request->separated;
            $filename = "civil_status_reports_per_municipality_".$now.'.xlsx';
            if($classtype == "barangay") {
                $filename = "civil_status_reports_per_barangay_".$now.'.xlsx';
            }
        } else if($page == "employments") {
            $headings = [ucfirst($classtype), 'Employed', 'Unemployed', 'Self-Employed', 'Total No. of Senior Citizens'];
            $employed = $request->employed;
            $unemployed = $request->unemployed;
            $selfEmployed = $request->selfEmployed;
            $filename = "employment_status_reports_per_municipality_".$now.'.xlsx';
            if($classtype == "barangay") {
                $filename = "employment_status_reports_per_barangay_".$now.'.xlsx';
            }
        } else if($page == "education") {
            $headings = [ucfirst($classtype), 'Elementary', 'High School', 'College', 'Vocational', "Master's Degree", 'Doctoral', 'Total No. of Senior Citizens'];
            $elementary = $request->elementary;
            $highSchool = $request->highSchool;
            $college = $request->college;
            $vocational = $request->vocational;
            $masterDegree = $request->masterDegree;
            $doctoral = $request->doctoral;
            $filename = "educational_attainment_reports_per_municipality_".$now.'.xlsx';
            if($classtype == "barangay") {
                $filename = "educational_attainment_reports_per_barangay_".$now.'.xlsx';
            }
        } else if($page == "genders") {
            $headings = [ucfirst($classtype), 'Male', 'Female', 'Total No. of Senior Citizens'];
            $male = $request->male;
            $female = $request->female;
            $filename = "gender_reports_per_municipality_".$now.'.xlsx';
            if($classtype == "barangay") {
                $filename = "gender_reports_per_barangay_".$now.'.xlsx';
            }
        } else if($page == "religions") {
            $headings = [ucfirst($classtype), 'Roman Catholic', 'Iglesia ni Cristo', 'Baptist Church', 'Adventist', "Jesus is Lord", 'Victory', 'Church of Christ', 'Islam', 'Buddhist', 'Methodist', 'Others', 'Total No. of Senior Citizens'];
            $catholic = $request->catholic;
            $iglesia = $request->iglesia;
            $baptist = $request->baptist;
            $adventist = $request->adventist;
            $jil = $request->jil;
            $victory = $request->victory;
            $coc = $request->coc;
            $islam = $request->islam;
            $buddhist = $request->buddhist;
            $methodist = $request->methodist;
            $others = $request->others;
            $filename = "religion_reports_per_municipality_".$now.'.xlsx';
            if($classtype == "barangay") {
                $filename = "religion_reports_per_barangay_".$now.'.xlsx';
            }
        } else if($page == "classifications") {
            $headings = [ucfirst($classtype), 'Indigent', 'Pensioner', 'Supported', 'PWD', 'Total No. of Senior Citizens'];
            $indigent = $request->indigent;
            $pensioner = $request->pensioner;
            $supported = $request->supported;
            $pwd = $request->pwd;
            $filename = "classification_reports_per_municipality_".$now.'.xlsx';
            if($classtype == "barangay") {
                $filename = "classification_reports_per_barangay_".$now.'.xlsx';
            }
        } else if($page == "illness") {
            $headings = [ucfirst($classtype), "Alzheimer's Disease", 'Arthritis', 'Cancer', 'Chronic Kidney Disease', "COPD", 'Diabetes', 'Heart Disease', 'High Cholesterol', 'Influenza and Pneumonia', 'Osteoporosis', 'Others', 'Total No. of Senior Citizens'];
            $alzheimer = $request->alzheimer;
            $arthritis = $request->arthritis;
            $cancer = $request->cancer;
            $kidney = $request->kidney;
            $pulmonary = $request->pulmonary;
            $diabetes = $request->diabetes;
            $heart = $request->heart;
            $cholesterol = $request->cholesterol;
            $pneumonia = $request->pneumonia;
            $osteoporosis = $request->osteoporosis;
            $otherIllness = $request->otherIllness;
            $filename = "common_illness_reports_per_municipality_".$now.'.xlsx';
            if($classtype == "barangay") {
                $filename = "common_illness_reports_per_barangay_".$now.'.xlsx';
            }
        }
        $newArray = [$headings];
        $allMale = $allFemale = 0;
        $allEmployed = $allUnemployed = $allSelfEmployed = 0;
        $allActive = $allInactive = $allAtRisk = $allHighRisk = $allPassedAway = 0;
        $allSingle = $allMarried = $allWidowed = $allSeparated = $allDivorced = $total = $j = 0;
        $allElementary = $allHighSchool = $allCollege = $allVocational = $allMasters = $allDoctoral = 0;
        $allcatholic = $alliglesia = $allbaptist = $alladventist = $alljil = $allvictory = $allcoc = $allislam = $allbuddhist = $allmethodist = $allothers  = 0;
        $allindigent = $allpensioner = $allsupported = $allpwd = 0;
        $allalzheimer = $allarthritis = $allcancer = $allkidney = $allpulmonary = $alldiabetes = $allheart = $allcholesterol = $allpneumonia = $allosteoporosis = $allotherIllness  = 0;
        foreach($request->listOfGeneralStatus as $mun) {
            if($classtype == "municipality") {
                $name = $mun['municipality_name'];
            } else if($classtype == "barangay") {
                $name = $mun['barangay_name'];
            }
            if($page == "members") {
                $pa = $passedAway[$j];
                $hr = $highRisk[$j];
                $ar = $atRisk[$j];
                $in = $inactive[$j];
                $ac = $active[$j];
                $total = $pa + $hr + $ar + $in + $ac;
                $allActive += $active[$j];
                $allInactive += $inactive[$j];
                $allAtRisk += $atRisk[$j];
                $allHighRisk += $highRisk[$j];
                $allPassedAway += $passedAway[$j];
                if($pa == 0 or $pa == '' or $pa == null) { $pa = '0'; }
                if($hr == 0 or $hr == '' or $hr == null) { $hr = '0'; }
                if($ar == 0 or $ar == '' or $ar == null) { $ar = '0'; }
                if($in == 0 or $in == '' or $in == null) { $in = '0'; }
                if($ac == 0 or $ac == '' or $ac == null) { $ac = '0'; }
                if($total == 0 or $total == '' or $total == null) { $total = '0'; }
                $newArray[] = [$name, $ac, $in, $ar, $hr, $pa, $total];
                if($allActive == 0 or $allActive == '' or $allActive == null) { $allActive = '0'; }
                if($allInactive == 0 or $allInactive == '' or $allInactive == null) { $allInactive = '0'; }
                if($allAtRisk == 0 or $allAtRisk == '' or $allAtRisk == null) { $allAtRisk = '0'; }
                if($allHighRisk == 0 or $allHighRisk == '' or $allHighRisk == null) { $allHighRisk = '0'; }
                if($allPassedAway == 0 or $allPassedAway == '' or $allPassedAway == null) { $allPassedAway = '0'; }
            } else if($page == "civil_status") {
                $sg = $single[$j];
                $mr = $married[$j];
                $wd = $widowed[$j];
                $dv = $divorced[$j];
                $sp = $separated[$j];
                $total = $sg + $mr + $wd + $dv + $sp;
                $allSingle += $single[$j];
                $allMarried += $married[$j];
                $allWidowed += $widowed[$j];
                $allSeparated += $separated[$j];
                $allDivorced += $divorced[$j];
                if($sg == 0 or $sg == '' or $sg == null) { $sg = '0'; }
                if($mr == 0 or $mr == '' or $mr == null) { $mr = '0'; }
                if($wd == 0 or $wd == '' or $wd == null) { $wd = '0'; }
                if($dv == 0 or $dv == '' or $dv == null) { $dv = '0'; }
                if($sp == 0 or $sp == '' or $sp == null) { $sp = '0'; }
                if($total == 0 or $total == '' or $total == null) { $total = '0'; }
                $newArray[] = [$name, $sg, $mr, $wd, $sp, $dv, $total];
                if($allSingle == 0 or $allSingle == '' or $allSingle == null) { $allSingle = '0'; }
                if($allMarried == 0 or $allMarried == '' or $allMarried == null) { $allMarried = '0'; }
                if($allWidowed == 0 or $allWidowed == '' or $allWidowed == null) { $allWidowed = '0'; }
                if($allSeparated == 0 or $allSeparated == '' or $allSeparated == null) { $allSeparated = '0'; }
                if($allDivorced == 0 or $allDivorced == '' or $allDivorced == null) { $allDivorced = '0'; }
            } else if($page == "employments") {
                $em = $employed[$j];
                $unm = $unemployed[$j];
                $sem = $selfEmployed[$j];
                $total = $em + $unm + $sem;
                $allEmployed += $employed[$j];
                $allUnemployed += $unemployed[$j];
                $allSelfEmployed += $selfEmployed[$j];
                if($em == 0 or $em == '' or $em == null) { $em = '0'; }
                if($unm == 0 or $unm == '' or $unm == null) { $unm = '0'; }
                if($sem == 0 or $sem == '' or $sem == null) { $sem = '0'; }
                if($total == 0 or $total == '' or $total == null) { $total = '0'; }
                $newArray[] = [$name, $em, $unm, $sem, $total];
                if($allEmployed == 0 or $allEmployed == '' or $allEmployed == null) { $allEmployed = '0'; }
                if($allUnemployed == 0 or $allUnemployed == '' or $allUnemployed == null) { $allUnemployed = '0'; }
                if($allSelfEmployed == 0 or $allSelfEmployed == '' or $allSelfEmployed == null) { $allSelfEmployed = '0'; }
            } else if($page == "education") {
                $elm = $elementary[$j];
                $hsc = $highSchool[$j];
                $col = $college[$j];
                $voc = $vocational[$j];
                $msd = $masterDegree[$j];
                $doc = $doctoral[$j];
                $total = $elm + $hsc + $col + $voc + $msd + $doc;
                $allElementary += $elementary[$j];
                $allHighSchool += $highSchool[$j];
                $allCollege += $college[$j];
                $allVocational += $vocational[$j];
                $allMasters += $masterDegree[$j];
                $allDoctoral += $doctoral[$j];
                if($elm == 0 or $elm == '' or $elm == null) { $elm = '0'; }
                if($hsc == 0 or $hsc == '' or $hsc == null) { $hsc = '0'; }
                if($col == 0 or $col == '' or $col == null) { $col = '0'; }
                if($voc == 0 or $voc == '' or $voc == null) { $voc = '0'; }
                if($msd == 0 or $msd == '' or $msd == null) { $msd = '0'; }
                if($doc == 0 or $doc == '' or $doc == null) { $doc = '0'; }
                if($total == 0 or $total == '' or $total == null) { $total = '0'; }
                $newArray[] = [$name, $elm, $hsc, $col, $voc, $msd, $doc, $total];
                if($allElementary == 0 or $allElementary == '' or $allElementary == null) { $allElementary = '0'; }
                if($allHighSchool == 0 or $allHighSchool == '' or $allHighSchool == null) { $allHighSchool = '0'; }
                if($allCollege == 0 or $allCollege == '' or $allCollege == null) { $allCollege = '0'; }
                if($allVocational == 0 or $allVocational == '' or $allVocational == null) { $allVocational = '0'; }
                if($allMasters == 0 or $allMasters == '' or $allMasters == null) { $allMasters = '0'; }
                if($allDoctoral == 0 or $allDoctoral == '' or $allDoctoral == null) { $allDoctoral = '0'; }
            } else if($page == "genders") {
                $ml = $male[$j];
                $fml = $female[$j];
                $total = $ml + $fml;
                $allMale += $male[$j];
                $allFemale += $female[$j];
                if($ml == 0 or $ml == '' or $ml == null) { $ml = '0'; }
                if($fml == 0 or $fml == '' or $fml == null) { $fml = '0'; }
                if($total == 0 or $total == '' or $total == null) { $total = '0'; }
                $newArray[] = [$name, $ml, $fml, $total];
                if($allMale == 0 or $allMale == '' or $allMale == null) { $allMale = '0'; }
                if($allFemale == 0 or $allFemale == '' or $allFemale == null) { $allFemale = '0'; }
            } else if($page == "religions") {
                $cat = $catholic[$j];
                $inc = $iglesia[$j];
                $bap = $baptist[$j];
                $adv = $adventist[$j];
                $jl = $jil[$j];
                $vic = $victory[$j];
                $ch = $coc[$j];
                $is = $islam[$j];
                $bud = $buddhist[$j];
                $met = $methodist[$j];
                $oth = $others[$j];
                $total = $cat + $inc + $bap + $adv + $jl + $vic + $ch + $is + $bud + $met + $oth;
                $allcatholic += $catholic[$j];
                $alliglesia += $iglesia[$j];
                $allbaptist += $baptist[$j];
                $alladventist += $adventist[$j];
                $alljil += $jil[$j];
                $allvictory += $victory[$j];
                $allcoc += $coc[$j];
                $allislam += $islam[$j];
                $allbuddhist += $buddhist[$j];
                $allmethodist += $methodist[$j];
                $allothers += $others[$j];
                if($cat == 0 or $cat == '' or $cat == null) { $cat = '0'; }
                if($inc == 0 or $inc == '' or $inc == null) { $inc = '0'; }
                if($bap == 0 or $bap == '' or $bap == null) { $bap = '0'; }
                if($adv == 0 or $adv == '' or $adv == null) { $adv = '0'; }
                if($jl == 0 or $jl == '' or $jl == null) { $jl = '0'; }
                if($vic == 0 or $vic == '' or $vic == null) { $vic = '0'; }
                if($ch == 0 or $ch == '' or $ch == null) { $ch = '0'; }
                if($is == 0 or $is == '' or $is == null) { $is = '0'; }
                if($bud == 0 or $bud == '' or $bud == null) { $bud = '0'; }
                if($met == 0 or $met == '' or $met == null) { $met = '0'; }
                if($oth == 0 or $oth == '' or $oth == null) { $oth = '0'; }
                if($total == 0 or $total == '' or $total == null) { $total = '0'; }
                $newArray[] = [$name, $cat, $inc, $bap, $adv, $jl, $vic, $ch, $is, $bud, $met, $oth, $total];
                if($allcatholic == 0 or $allcatholic == '' or $allcatholic == null) { $allcatholic = '0'; }
                if($alliglesia == 0 or $alliglesia == '' or $alliglesia == null) { $alliglesia = '0'; }
                if($allbaptist == 0 or $allbaptist == '' or $allbaptist == null) { $allbaptist = '0'; }
                if($alladventist == 0 or $alladventist == '' or $alladventist == null) { $alladventist = '0'; }
                if($alljil == 0 or $alljil == '' or $alljil == null) { $alljil = '0'; }
                if($allvictory == 0 or $allvictory == '' or $allvictory == null) { $allvictory = '0'; }
                if($allcoc == 0 or $allcoc == '' or $allcoc == null) { $allcoc = '0'; }
                if($allislam == 0 or $allislam == '' or $allislam == null) { $allislam = '0'; }
                if($allbuddhist == 0 or $allbuddhist == '' or $allbuddhist == null) { $allbuddhist = '0'; }
                if($allmethodist == 0 or $allmethodist == '' or $allmethodist == null) { $allmethodist = '0'; }
                if($allothers == 0 or $allothers == '' or $allothers == null) { $allothers = '0'; }
            } else if($page == "classifications") {
                $ind = $indigent[$j];
                $pen = $pensioner[$j];
                $sup = $supported[$j];
                $pd = $pwd[$j];
                $total = $ind + $pen + $sup + $pd;
                $allindigent += $indigent[$j];
                $allpensioner += $pensioner[$j];
                $allsupported += $supported[$j];
                $allpwd += $pwd[$j];
                if($ind == 0 or $ind == '' or $ind == null) { $ind = '0'; }
                if($pen == 0 or $pen == '' or $pen == null) { $pen = '0'; }
                if($sup == 0 or $sup == '' or $sup == null) { $sup = '0'; }
                if($pd == 0 or $pd == '' or $pd == null) { $pd = '0'; }
                if($total == 0 or $total == '' or $total == null) { $total = '0'; }
                $newArray[] = [$name, $ind, $pen, $sup, $pd, $total];
                if($allindigent == 0 or $allindigent == '' or $allindigent == null) { $allindigent = '0'; }
                if($allpensioner == 0 or $allpensioner == '' or $allpensioner == null) { $allpensioner = '0'; }
                if($allsupported == 0 or $allsupported == '' or $allsupported == null) { $allsupported = '0'; }
                if($allpwd == 0 or $allpwd == '' or $allpwd == null) { $allpwd = '0'; }
            } else if($page == "illness") {
                $alz = $alzheimer[$j];
                $art = $arthritis[$j];
                $can = $cancer[$j];
                $kid = $kidney[$j];
                $pul = $pulmonary[$j];
                $dia = $diabetes[$j];
                $hrt = $heart[$j];
                $cho = $cholesterol[$j];
                $pne = $pneumonia[$j];
                $ost = $osteoporosis[$j];
                $othi = $otherIllness[$j];
                $total = $alz + $art + $can + $kid + $pul + $dia + $hrt + $cho + $pne + $ost + $othi;
                $allalzheimer += $alzheimer[$j];
                $allarthritis += $arthritis[$j];
                $allcancer += $cancer[$j];
                $allkidney += $kidney[$j];
                $allpulmonary += $pulmonary[$j];
                $alldiabetes += $diabetes[$j];
                $allheart += $heart[$j];
                $allcholesterol += $cholesterol[$j];
                $allpneumonia += $pneumonia[$j];
                $allosteoporosis += $osteoporosis[$j];
                $allotherIllness += $otherIllness[$j];
                if($alz == 0 or $alz == '' or $alz == null) { $alz = '0'; }
                if($art == 0 or $art == '' or $art == null) { $art = '0'; }
                if($can == 0 or $can == '' or $can == null) { $can = '0'; }
                if($kid == 0 or $kid == '' or $kid == null) { $kid = '0'; }
                if($pul == 0 or $pul == '' or $pul == null) { $pul = '0'; }
                if($dia == 0 or $dia == '' or $dia == null) { $dia = '0'; }
                if($hrt == 0 or $hrt == '' or $hrt == null) { $hrt = '0'; }
                if($cho == 0 or $cho == '' or $cho == null) { $cho = '0'; }
                if($pne == 0 or $pne == '' or $pne == null) { $pne = '0'; }
                if($ost == 0 or $ost == '' or $ost == null) { $ost = '0'; }
                if($othi == 0 or $othi == '' or $othi == null) { $othi = '0'; }
                if($total == 0 or $total == '' or $total == null) { $total = '0'; }
                $newArray[] = [$name, $alz, $art, $can, $kid, $pul, $dia, $hrt, $cho, $pne, $ost, $othi, $total];
                if($allalzheimer == 0 or $allalzheimer == '' or $allalzheimer == null) { $allalzheimer = '0'; }
                if($allarthritis == 0 or $allarthritis == '' or $allarthritis == null) { $allarthritis = '0'; }
                if($allcancer == 0 or $allcancer == '' or $allcancer == null) { $allcancer = '0'; }
                if($allkidney == 0 or $allkidney == '' or $allkidney == null) { $allkidney = '0'; }
                if($allpulmonary == 0 or $allpulmonary == '' or $allpulmonary == null) { $allpulmonary = '0'; }
                if($alldiabetes == 0 or $alldiabetes == '' or $alldiabetes == null) { $alldiabetes = '0'; }
                if($allheart == 0 or $allheart == '' or $allheart == null) { $allheart = '0'; }
                if($allcholesterol == 0 or $allcholesterol == '' or $allcholesterol == null) { $allcholesterol = '0'; }
                if($allpneumonia == 0 or $allpneumonia == '' or $allpneumonia == null) { $allpneumonia = '0'; }
                if($allosteoporosis == 0 or $allosteoporosis == '' or $allosteoporosis == null) { $allosteoporosis = '0'; }
                if($allotherIllness == 0 or $allotherIllness == '' or $allotherIllness == null) { $allotherIllness = '0'; }
            }
            $j++;
        }
        if($page == "members") {
            $arr = [$allActive, $allInactive, $allAtRisk, $allHighRisk, $allPassedAway];
            $newArray[] = ['Total', $allActive, $allInactive, $allAtRisk, $allHighRisk, $allPassedAway, array_sum($arr)];
        } else if($page == "civil_status") {
            $arr = [$allSingle, $allMarried, $allWidowed, $allSeparated, $allDivorced];
            $newArray[] = ['Total', $allSingle, $allMarried, $allWidowed, $allSeparated, $allDivorced, array_sum($arr)];
        } else if($page == "employments") {
            $arr = [$allEmployed, $allUnemployed, $allSelfEmployed];
            $newArray[] = ['Total', $allEmployed, $allUnemployed, $allSelfEmployed, array_sum($arr)];
        } else if($page == "education") {
            $arr = [$allElementary, $allHighSchool, $allCollege, $allVocational, $allMasters, $allDoctoral];
            $newArray[] = ['Total', $allElementary, $allHighSchool, $allCollege, $allVocational, $allMasters, $allDoctoral, array_sum($arr)];
        } else if($page == "genders") {
            $arr = [$allMale, $allFemale];
            $newArray[] = ['Total', $allMale, $allFemale, array_sum($arr)];
        } else if($page == "religions") {
            $arr = [$allcatholic, $alliglesia, $allbaptist, $alladventist, $alljil, $allvictory, $allcoc, $allislam, $allbuddhist, $allmethodist, $allothers];
            $newArray[] = ['Total', $allcatholic, $alliglesia, $allbaptist, $alladventist, $alljil, $allvictory, $allcoc, $allislam, $allbuddhist, $allmethodist, $allothers, array_sum($arr)];
        } else if($page == "classifications") {
            $arr = [$allindigent, $allpensioner, $allsupported, $allpwd];
            $newArray[] = ['Total', $allindigent, $allpensioner, $allsupported, $allpwd, array_sum($arr)];
        } else if($page == "illness") {
            $arr = [$allalzheimer, $allarthritis, $allcancer, $allkidney, $allpulmonary, $alldiabetes, $allheart, $allcholesterol, $allpneumonia, $allosteoporosis, $allotherIllness];
            $newArray[] = ['Total', $allalzheimer, $allarthritis, $allcancer, $allkidney, $allpulmonary, $alldiabetes, $allheart, $allcholesterol, $allpneumonia, $allosteoporosis, $allotherIllness, array_sum($arr)];
        }
        Excel::store(new ReportsExport($newArray), $filename, 'local');
        return response()->json($filename, 200);
    }
    public function generateMemberReport(Request $request) 
    {
        $user = Auth::user();
        $role = $user->roles->pluck('name')[0];
        $page = $request->page;
        $ctype = strtolower($request->ctype);
        $dtf = $request->from. ' 00:00:00';
        $dtt = $request->to. ' 00:00:00';
        $municipality = $request->municipality;
        $passed = $hrisk = $arisk = $inc = $act = $listOfGeneralStatus = $employed = $unemployed = $self_employed = [];
        $elementary = $highSchool = $college = $vocational = $master = $doctoral = [];
        $single = $married = $widowed = $divorced = $separated = $male = $female = [];
        $catholic = $iglesia = $baptist = $adventist = $jil = $victory = $coc = $islam = $buddhist = $methodist = $others = [];
        $indigent = $pensioner = $supported = $pwd = [];
        $alzheimer = $arthritis = $cancer = $kidney = $pulmonary = $diabetes = $heart = $cholesterol = $pneumonia = $osteoporosis = $otherIllness = [];
        
        if($role == "team lead" or $role == "encoder") {
            $munplty = $user->municipality;
            if(Str::contains($munplty, ',')) {
                $munID = explode(',', $munplty);
            } else {
                $munID = [$munplty];
            }
            if($ctype == "municipality") {
                $listOfGeneralStatus = Municipality::whereIn('municipality_code_number', $munID)->orderBy('municipality_name')->get();
                foreach($listOfGeneralStatus as $mun) {
                    if($page == "members") {
                        $passed[] = $this->getCount($dtf, $dtt, 'Passed Away', $mun->municipality_code_number, 'municipality', 'd.member_status');
                        $hrisk[] = $this->getCount($dtf, $dtt, 'High Risk', $mun->municipality_code_number, 'municipality', 'd.member_status');
                        $arisk[] = $this->getCount($dtf, $dtt, 'At Risk', $mun->municipality_code_number, 'municipality', 'd.member_status');
                        $inc[] = $this->getCount($dtf, $dtt, 'Inactive', $mun->municipality_code_number, 'municipality', 'd.member_status');
                        $act[] = $this->getCount($dtf, $dtt, 'Active', $mun->municipality_code_number, 'municipality', 'd.member_status');
                    } else if($page == "employments") {
                        $employed[] = $this->getCount($dtf, $dtt, 'Employed', $mun->municipality_code_number, 'municipality', 'd.employment_status');
                        $unemployed[] = $this->getCount($dtf, $dtt, 'Unemployed', $mun->municipality_code_number, 'municipality', 'd.employment_status');
                        $self_employed[] = $this->getCount($dtf, $dtt, "Self-Employed", $mun->municipality_code_number, 'municipality', 'd.employment_status');
                    } else if($page == "education") {
                        $elementary[] = $this->getCount($dtf, $dtt, 'Elementary', $mun->municipality_code_number, 'municipality', 'd.education');
                        $highSchool[] = $this->getCount($dtf, $dtt, 'High School', $mun->municipality_code_number, 'municipality', 'd.education');
                        $college[] = $this->getCount($dtf, $dtt, 'College', $mun->municipality_code_number, 'municipality', 'd.education');
                        $vocational[] = $this->getCount($dtf, $dtt, 'Vocational', $mun->municipality_code_number, 'municipality', 'd.education');
                        $master[] = $this->getCount($dtf, $dtt, "Master's Degree", $mun->municipality_code_number, 'municipality', 'd.education');
                        $doctoral[] = $this->getCount($dtf, $dtt, 'Doctoral', $mun->municipality_code_number, 'municipality', 'd.education');
                    } else if($page == "civil_status") {
                        $single[] = $this->getCount($dtf, $dtt, 'single', $mun->municipality_code_number, 'municipality', 'd.civil_status');
                        $married[] = $this->getCount($dtf, $dtt, 'married', $mun->municipality_code_number, 'municipality', 'd.civil_status');
                        $widowed[] = $this->getCount($dtf, $dtt, 'widowed', $mun->municipality_code_number, 'municipality', 'd.civil_status');
                        $divorced[] = $this->getCount($dtf, $dtt, 'divorced', $mun->municipality_code_number, 'municipality', 'd.civil_status');
                        $separated[] = $this->getCount($dtf, $dtt, 'separated', $mun->municipality_code_number, 'municipality', 'd.civil_status');
                    } else if($page == "genders") {
                        $male[] = $this->getCount($dtf, $dtt, 'male', $mun->municipality_code_number, 'municipality', 'd.gender');
                        $female[] = $this->getCount($dtf, $dtt, 'female', $mun->municipality_code_number, 'municipality', 'd.gender');
                    } else if($page == "religions") {
                        $catholic[] = $this->getCount($dtf, $dtt, 'Roman Catholic', $mun->municipality_code_number, 'municipality', 'd.religion');
                        $iglesia[] = $this->getCount($dtf, $dtt, 'Iglesia ni Cristo', $mun->municipality_code_number, 'municipality', 'd.religion');
                        $baptist[] = $this->getCount($dtf, $dtt, 'Baptist Church', $mun->municipality_code_number, 'municipality', 'd.religion');
                        $adventist[] = $this->getCount($dtf, $dtt, 'Adventist', $mun->municipality_code_number, 'municipality', 'd.religion');
                        $jil[] = $this->getCount($dtf, $dtt, 'Jesus is Lord', $mun->municipality_code_number, 'municipality', 'd.religion');
                        $victory[] = $this->getCount($dtf, $dtt, 'Victory', $mun->municipality_code_number, 'municipality', 'd.religion');
                        $coc[] = $this->getCount($dtf, $dtt, 'Church of Christ', $mun->municipality_code_number, 'municipality', 'd.religion');
                        $islam[] = $this->getCount($dtf, $dtt, 'Islam', $mun->municipality_code_number, 'municipality', 'd.religion');
                        $buddhist[] = $this->getCount($dtf, $dtt, 'Buddhist', $mun->municipality_code_number, 'municipality', 'd.religion');
                        $methodist[] = $this->getCount($dtf, $dtt, 'Methodist', $mun->municipality_code_number, 'municipality', 'd.religion');
                        $others[] = $this->getCount($dtf, $dtt, 'Others', $mun->municipality_code_number, 'municipality', 'd.religion');
                    } else if($page == "classifications") {
                        $indigent[] = $this->classificationCount($dtf, $dtt, 'Indigent', $mun->municipality_code_number, 'municipality', 'c.classification');
                        $pensioner[] = $this->classificationCount($dtf, $dtt, 'Pensioner', $mun->municipality_code_number, 'municipality', 'c.classification');
                        $supported[] = $this->classificationCount($dtf, $dtt, 'Supported', $mun->municipality_code_number, 'municipality', 'c.classification');
                        $pwd[] = $this->classificationCount($dtf, $dtt, 'PWD', $mun->municipality_code_number, 'municipality', 'c.classification');
                    } else if($page == "illness") {
                        $alzheimer[] = $this->illnessCount($dtf, $dtt, "Alzheimer's Disease", $mun->municipality_code_number, 'municipality', 'i.sickness');
                        $arthritis[] = $this->illnessCount($dtf, $dtt, 'Arthritis', $mun->municipality_code_number, 'municipality', 'i.sickness');
                        $cancer[] = $this->illnessCount($dtf, $dtt, 'Cancer', $mun->municipality_code_number, 'municipality', 'i.sickness');
                        $kidney[] = $this->illnessCount($dtf, $dtt, 'Chronic Kidney Disease', $mun->municipality_code_number, 'municipality', 'i.sickness');
                        $pulmonary[] = $this->illnessCount($dtf, $dtt, 'Chronic Obstruction Pulmonary Disease', $mun->municipality_code_number, 'municipality', 'i.sickness');
                        $diabetes[] = $this->illnessCount($dtf, $dtt, 'Diabetes', $mun->municipality_code_number, 'municipality', 'i.sickness');
                        $heart[] = $this->illnessCount($dtf, $dtt, 'Heart Disease', $mun->municipality_code_number, 'municipality', 'i.sickness');
                        $cholesterol[] = $this->illnessCount($dtf, $dtt, 'High Cholesterol', $mun->municipality_code_number, 'municipality', 'i.sickness');
                        $pneumonia[] = $this->illnessCount($dtf, $dtt, 'Influenza and Pneumonia', $mun->municipality_code_number, 'municipality', 'i.sickness');
                        $osteoporosis[] = $this->illnessCount($dtf, $dtt, 'Osteoporosis', $mun->municipality_code_number, 'municipality', 'i.sickness');
                        $otherIllness[] = $this->illnessCount($dtf, $dtt, 'Others', $mun->municipality_code_number, 'municipality', 'i.sickness');
                    }
                }
            } else if($ctype == "barangay") {
                $listOfGeneralStatus = Barangay::where('municipality_code_number', $municipality)->orderBy('barangay_name')->get();
                foreach($listOfGeneralStatus as $bar) {
                    if($page == "members") {
                        $passed[] = $this->getCount($dtf, $dtt, 'Passed Away', $bar->id, 'barangay', 'd.member_status');
                        $hrisk[] = $this->getCount($dtf, $dtt, 'High Risk', $bar->id, 'barangay', 'd.member_status');
                        $arisk[] = $this->getCount($dtf, $dtt, 'At Risk', $bar->id, 'barangay', 'd.member_status');
                        $inc[] = $this->getCount($dtf, $dtt, 'Inactive', $bar->id, 'barangay', 'd.member_status');
                        $act[] = $this->getCount($dtf, $dtt, 'Active', $bar->id, 'barangay', 'd.member_status');
                    } else if($page == "employments") {
                        $employed[] = $this->getCount($dtf, $dtt, 'Employed', $bar->id, 'barangay', 'd.employment_status');
                        $unemployed[] = $this->getCount($dtf, $dtt, 'Unemployed', $bar->id, 'barangay', 'd.employment_status');
                        $self_employed[] = $this->getCount($dtf, $dtt, "Self-Employed", $bar->id, 'barangay', 'd.employment_status');
                    } else if($page == "education") {
                        $elementary[] = $this->getCount($dtf, $dtt, 'Elementary', $bar->id, 'barangay', 'd.education');
                        $highSchool[] = $this->getCount($dtf, $dtt, 'High School', $bar->id, 'barangay', 'd.education');
                        $college[] = $this->getCount($dtf, $dtt, 'College', $bar->id, 'barangay', 'd.education');
                        $vocational[] = $this->getCount($dtf, $dtt, 'Vocational', $bar->id, 'barangay', 'd.education');
                        $master[] = $this->getCount($dtf, $dtt, "Master's Degree", $bar->id, 'barangay', 'd.education');
                        $doctoral[] = $this->getCount($dtf, $dtt, 'Doctoral', $bar->id, 'barangay', 'd.education');
                    } else if($page == "civil_status") {
                        $single[] = $this->getCount($dtf, $dtt, 'single', $bar->id, 'barangay', 'd.civil_status');
                        $married[] = $this->getCount($dtf, $dtt, 'married', $bar->id, 'barangay', 'd.civil_status');
                        $widowed[] = $this->getCount($dtf, $dtt, 'widowed', $bar->id, 'barangay', 'd.civil_status');
                        $divorced[] = $this->getCount($dtf, $dtt, 'divorced', $bar->id, 'barangay', 'd.civil_status');
                        $separated[] = $this->getCount($dtf, $dtt, 'separated', $bar->id, 'barangay', 'd.civil_status');
                    } else if($page == "genders") {
                        $male[] = $this->getCount($dtf, $dtt, 'male', $bar->id, 'barangay', 'd.gender');
                        $female[] = $this->getCount($dtf, $dtt, 'female', $bar->id, 'barangay', 'd.gender');
                    } else if($page == "religions") {
                        $catholic[] = $this->getCount($dtf, $dtt, 'Roman Catholic', $bar->id, 'barangay', 'd.religion');
                        $iglesia[] = $this->getCount($dtf, $dtt, 'Iglesia ni Cristo', $bar->id, 'barangay', 'd.religion');
                        $baptist[] = $this->getCount($dtf, $dtt, 'Baptist Church', $bar->id, 'barangay', 'd.religion');
                        $adventist[] = $this->getCount($dtf, $dtt, 'Adventist', $bar->id, 'barangay', 'd.religion');
                        $jil[] = $this->getCount($dtf, $dtt, 'Jesus is Lord', $bar->id, 'barangay', 'd.religion');
                        $victory[] = $this->getCount($dtf, $dtt, 'Victory', $bar->id, 'barangay', 'd.religion');
                        $coc[] = $this->getCount($dtf, $dtt, 'Church of Christ', $bar->id, 'barangay', 'd.religion');
                        $islam[] = $this->getCount($dtf, $dtt, 'Islam', $bar->id, 'barangay', 'd.religion');
                        $buddhist[] = $this->getCount($dtf, $dtt, 'Buddhist', $bar->id, 'barangay', 'd.religion');
                        $methodist[] = $this->getCount($dtf, $dtt, 'Methodist', $bar->id, 'barangay', 'd.religion');
                        $others[] = $this->getCount($dtf, $dtt, 'Others', $bar->id, 'barangay', 'd.religion');
                    } else if($page == "classifications") {
                        $indigent[] = $this->classificationCount($dtf, $dtt, 'Indigent', $bar->id, 'barangay', 'c.classification');
                        $pensioner[] = $this->classificationCount($dtf, $dtt, 'Pensioner', $bar->id, 'barangay', 'c.classification');
                        $supported[] = $this->classificationCount($dtf, $dtt, 'Supported', $bar->id, 'barangay', 'c.classification');
                        $pwd[] = $this->classificationCount($dtf, $dtt, 'PWD', $bar->id, 'barangay', 'c.classification');
                    } else if($page == "illness") {
                        $alzheimer[] = $this->illnessCount($dtf, $dtt, "Alzheimer's Disease", $bar->id, 'barangay', 'i.sickness');
                        $arthritis[] = $this->illnessCount($dtf, $dtt, 'Arthritis', $bar->id, 'barangay', 'i.sickness');
                        $cancer[] = $this->illnessCount($dtf, $dtt, 'Cancer', $bar->id, 'barangay', 'i.sickness');
                        $kidney[] = $this->illnessCount($dtf, $dtt, 'Chronic Kidney Disease', $bar->id, 'barangay', 'i.sickness');
                        $pulmonary[] = $this->illnessCount($dtf, $dtt, 'Chronic Obstruction Pulmonary Disease', $bar->id, 'barangay', 'i.sickness');
                        $diabetes[] = $this->illnessCount($dtf, $dtt, 'Diabetes', $bar->id, 'barangay', 'i.sickness');
                        $heart[] = $this->illnessCount($dtf, $dtt, 'Heart Disease', $bar->id, 'barangay', 'i.sickness');
                        $cholesterol[] = $this->illnessCount($dtf, $dtt, 'High Cholesterol', $bar->id, 'barangay', 'i.sickness');
                        $pneumonia[] = $this->illnessCount($dtf, $dtt, 'Influenza and Pneumonia', $bar->id, 'barangay', 'i.sickness');
                        $osteoporosis[] = $this->illnessCount($dtf, $dtt, 'Osteoporosis', $bar->id, 'barangay', 'i.sickness');
                        $otherIllness[] = $this->illnessCount($dtf, $dtt, 'Others', $bar->id, 'barangay', 'i.sickness');
                    }
                }
            }
        } else {
            if($ctype == "municipality") {
                $listOfGeneralStatus = Municipality::orderBy('municipality_name')->get();
                foreach($listOfGeneralStatus as $mun) {
                    if($page == "members") {
                        $passed[] = $this->getCount($dtf, $dtt, 'Passed Away', $mun->municipality_code_number, 'municipality', 'd.member_status');
                        $hrisk[] = $this->getCount($dtf, $dtt, 'High Risk', $mun->municipality_code_number, 'municipality', 'd.member_status');
                        $arisk[] = $this->getCount($dtf, $dtt, 'At Risk', $mun->municipality_code_number, 'municipality', 'd.member_status');
                        $inc[] = $this->getCount($dtf, $dtt, 'Inactive', $mun->municipality_code_number, 'municipality', 'd.member_status');
                        $act[] = $this->getCount($dtf, $dtt, 'Active', $mun->municipality_code_number, 'municipality', 'd.member_status');
                    } else if($page == "employments") {
                        $employed[] = $this->getCount($dtf, $dtt, 'Employed', $mun->municipality_code_number, 'municipality', 'd.employment_status');
                        $unemployed[] = $this->getCount($dtf, $dtt, 'Unemployed', $mun->municipality_code_number, 'municipality', 'd.employment_status');
                        $self_employed[] = $this->getCount($dtf, $dtt, "Self-Employed", $mun->municipality_code_number, 'municipality', 'd.employment_status');
                    } else if($page == "education") {
                        $elementary[] = $this->getCount($dtf, $dtt, 'Elementary', $mun->municipality_code_number, 'municipality', 'd.education');
                        $highSchool[] = $this->getCount($dtf, $dtt, 'High School', $mun->municipality_code_number, 'municipality', 'd.education');
                        $college[] = $this->getCount($dtf, $dtt, 'College', $mun->municipality_code_number, 'municipality', 'd.education');
                        $vocational[] = $this->getCount($dtf, $dtt, 'Vocational', $mun->municipality_code_number, 'municipality', 'd.education');
                        $master[] = $this->getCount($dtf, $dtt, "Master's Degree", $mun->municipality_code_number, 'municipality', 'd.education');
                        $doctoral[] = $this->getCount($dtf, $dtt, 'Doctoral', $mun->municipality_code_number, 'municipality', 'd.education');
                    } else if($page == "civil_status") {
                        $single[] = $this->getCount($dtf, $dtt, 'single', $mun->municipality_code_number, 'municipality', 'd.civil_status');
                        $married[] = $this->getCount($dtf, $dtt, 'married', $mun->municipality_code_number, 'municipality', 'd.civil_status');
                        $widowed[] = $this->getCount($dtf, $dtt, 'widowed', $mun->municipality_code_number, 'municipality', 'd.civil_status');
                        $divorced[] = $this->getCount($dtf, $dtt, 'divorced', $mun->municipality_code_number, 'municipality', 'd.civil_status');
                        $separated[] = $this->getCount($dtf, $dtt, 'separated', $mun->municipality_code_number, 'municipality', 'd.civil_status');
                    } else if($page == "genders") {
                        $male[] = $this->getCount($dtf, $dtt, 'male', $mun->municipality_code_number, 'municipality', 'd.gender');
                        $female[] = $this->getCount($dtf, $dtt, 'female', $mun->municipality_code_number, 'municipality', 'd.gender');
                    } else if($page == "religions") {
                        $catholic[] = $this->getCount($dtf, $dtt, 'Roman Catholic', $mun->municipality_code_number, 'municipality', 'd.religion');
                        $iglesia[] = $this->getCount($dtf, $dtt, 'Iglesia ni Cristo', $mun->municipality_code_number, 'municipality', 'd.religion');
                        $baptist[] = $this->getCount($dtf, $dtt, 'Baptist Church', $mun->municipality_code_number, 'municipality', 'd.religion');
                        $adventist[] = $this->getCount($dtf, $dtt, 'Adventist', $mun->municipality_code_number, 'municipality', 'd.religion');
                        $jil[] = $this->getCount($dtf, $dtt, 'Jesus is Lord', $mun->municipality_code_number, 'municipality', 'd.religion');
                        $victory[] = $this->getCount($dtf, $dtt, 'Victory', $mun->municipality_code_number, 'municipality', 'd.religion');
                        $coc[] = $this->getCount($dtf, $dtt, 'Church of Christ', $mun->municipality_code_number, 'municipality', 'd.religion');
                        $islam[] = $this->getCount($dtf, $dtt, 'Islam', $mun->municipality_code_number, 'municipality', 'd.religion');
                        $buddhist[] = $this->getCount($dtf, $dtt, 'Buddhist', $mun->municipality_code_number, 'municipality', 'd.religion');
                        $methodist[] = $this->getCount($dtf, $dtt, 'Methodist', $mun->municipality_code_number, 'municipality', 'd.religion');
                        $others[] = $this->getCount($dtf, $dtt, 'Others', $mun->municipality_code_number, 'municipality', 'd.religion');
                    } else if($page == "classifications") {
                        $indigent[] = $this->classificationCount($dtf, $dtt, 'Indigent', $mun->municipality_code_number, 'municipality', 'c.classification');
                        $pensioner[] = $this->classificationCount($dtf, $dtt, 'Pensioner', $mun->municipality_code_number, 'municipality', 'c.classification');
                        $supported[] = $this->classificationCount($dtf, $dtt, 'Supported', $mun->municipality_code_number, 'municipality', 'c.classification');
                        $pwd[] = $this->classificationCount($dtf, $dtt, 'PWD', $mun->municipality_code_number, 'municipality', 'c.classification');
                    } else if($page == "illness") {
                        $alzheimer[] = $this->illnessCount($dtf, $dtt, "Alzheimer's Disease", $mun->municipality_code_number, 'municipality', 'i.sickness');
                        $arthritis[] = $this->illnessCount($dtf, $dtt, 'Arthritis', $mun->municipality_code_number, 'municipality', 'i.sickness');
                        $cancer[] = $this->illnessCount($dtf, $dtt, 'Cancer', $mun->municipality_code_number, 'municipality', 'i.sickness');
                        $kidney[] = $this->illnessCount($dtf, $dtt, 'Chronic Kidney Disease', $mun->municipality_code_number, 'municipality', 'i.sickness');
                        $pulmonary[] = $this->illnessCount($dtf, $dtt, 'Chronic Obstruction Pulmonary Disease', $mun->municipality_code_number, 'municipality', 'i.sickness');
                        $diabetes[] = $this->illnessCount($dtf, $dtt, 'Diabetes', $mun->municipality_code_number, 'municipality', 'i.sickness');
                        $heart[] = $this->illnessCount($dtf, $dtt, 'Heart Disease', $mun->municipality_code_number, 'municipality', 'i.sickness');
                        $cholesterol[] = $this->illnessCount($dtf, $dtt, 'High Cholesterol', $mun->municipality_code_number, 'municipality', 'i.sickness');
                        $pneumonia[] = $this->illnessCount($dtf, $dtt, 'Influenza and Pneumonia', $mun->municipality_code_number, 'municipality', 'i.sickness');
                        $osteoporosis[] = $this->illnessCount($dtf, $dtt, 'Osteoporosis', $mun->municipality_code_number, 'municipality', 'i.sickness');
                        $otherIllness[] = $this->illnessCount($dtf, $dtt, 'Others', $mun->municipality_code_number, 'municipality', 'i.sickness');
                    }
                }
            } else if($ctype == "barangay") {
                $listOfGeneralStatus = Barangay::where('municipality_code_number', $municipality)->orderBy('barangay_name')->get();
                foreach($listOfGeneralStatus as $bar) {
                    if($page == "members") {
                        $passed[] = $this->getCount($dtf, $dtt, 'Passed Away', $bar->id, 'barangay', 'd.member_status');
                        $hrisk[] = $this->getCount($dtf, $dtt, 'High Risk', $bar->id, 'barangay', 'd.member_status');
                        $arisk[] = $this->getCount($dtf, $dtt, 'At Risk', $bar->id, 'barangay', 'd.member_status');
                        $inc[] = $this->getCount($dtf, $dtt, 'Inactive', $bar->id, 'barangay', 'd.member_status');
                        $act[] = $this->getCount($dtf, $dtt, 'Active', $bar->id, 'barangay', 'd.member_status');
                    } else if($page == "employments") {
                        $employed[] = $this->getCount($dtf, $dtt, 'Employed', $bar->id, 'barangay', 'd.employment_status');
                        $unemployed[] = $this->getCount($dtf, $dtt, 'Unemployed', $bar->id, 'barangay', 'd.employment_status');
                        $self_employed[] = $this->getCount($dtf, $dtt, "Self-Employed", $bar->id, 'barangay', 'd.employment_status');
                    } else if($page == "education") {
                        $elementary[] = $this->getCount($dtf, $dtt, 'Elementary', $bar->id, 'barangay', 'd.education');
                        $highSchool[] = $this->getCount($dtf, $dtt, 'High School', $bar->id, 'barangay', 'd.education');
                        $college[] = $this->getCount($dtf, $dtt, 'College', $bar->id, 'barangay', 'd.education');
                        $vocational[] = $this->getCount($dtf, $dtt, 'Vocational', $bar->id, 'barangay', 'd.education');
                        $master[] = $this->getCount($dtf, $dtt, "Master's Degree", $bar->id, 'barangay', 'd.education');
                        $doctoral[] = $this->getCount($dtf, $dtt, 'Doctoral', $bar->id, 'barangay', 'd.education');
                    } else if($page == "civil_status") {
                        $single[] = $this->getCount($dtf, $dtt, 'single', $bar->id, 'barangay', 'd.civil_status');
                        $married[] = $this->getCount($dtf, $dtt, 'married', $bar->id, 'barangay', 'd.civil_status');
                        $widowed[] = $this->getCount($dtf, $dtt, 'widowed', $bar->id, 'barangay', 'd.civil_status');
                        $divorced[] = $this->getCount($dtf, $dtt, 'divorced', $bar->id, 'barangay', 'd.civil_status');
                        $separated[] = $this->getCount($dtf, $dtt, 'separated', $bar->id, 'barangay', 'd.civil_status');
                    } else if($page == "genders") {
                        $male[] = $this->getCount($dtf, $dtt, 'male', $bar->id, 'barangay', 'd.gender');
                        $female[] = $this->getCount($dtf, $dtt, 'female', $bar->id, 'barangay', 'd.gender');
                    } else if($page == "religions") {
                        $catholic[] = $this->getCount($dtf, $dtt, 'Roman Catholic', $bar->id, 'barangay', 'd.religion');
                        $iglesia[] = $this->getCount($dtf, $dtt, 'Iglesia ni Cristo', $bar->id, 'barangay', 'd.religion');
                        $baptist[] = $this->getCount($dtf, $dtt, 'Baptist Church', $bar->id, 'barangay', 'd.religion');
                        $adventist[] = $this->getCount($dtf, $dtt, 'Adventist', $bar->id, 'barangay', 'd.religion');
                        $jil[] = $this->getCount($dtf, $dtt, 'Jesus is Lord', $bar->id, 'barangay', 'd.religion');
                        $victory[] = $this->getCount($dtf, $dtt, 'Victory', $bar->id, 'barangay', 'd.religion');
                        $coc[] = $this->getCount($dtf, $dtt, 'Church of Christ', $bar->id, 'barangay', 'd.religion');
                        $islam[] = $this->getCount($dtf, $dtt, 'Islam', $bar->id, 'barangay', 'd.religion');
                        $buddhist[] = $this->getCount($dtf, $dtt, 'Buddhist', $bar->id, 'barangay', 'd.religion');
                        $methodist[] = $this->getCount($dtf, $dtt, 'Methodist', $bar->id, 'barangay', 'd.religion');
                        $others[] = $this->getCount($dtf, $dtt, 'Others', $bar->id, 'barangay', 'd.religion');
                    } else if($page == "classifications") {
                        $indigent[] = $this->classificationCount($dtf, $dtt, 'Indigent', $bar->id, 'barangay', 'c.classification');
                        $pensioner[] = $this->classificationCount($dtf, $dtt, 'Pensioner', $bar->id, 'barangay', 'c.classification');
                        $supported[] = $this->classificationCount($dtf, $dtt, 'Supported', $bar->id, 'barangay', 'c.classification');
                        $pwd[] = $this->classificationCount($dtf, $dtt, 'PWD', $bar->id, 'barangay', 'c.classification');
                    } else if($page == "illness") {
                        $alzheimer[] = $this->illnessCount($dtf, $dtt, "Alzheimer's Disease", $bar->id, 'barangay', 'i.sickness');
                        $arthritis[] = $this->illnessCount($dtf, $dtt, 'Arthritis', $bar->id, 'barangay', 'i.sickness');
                        $cancer[] = $this->illnessCount($dtf, $dtt, 'Cancer', $bar->id, 'barangay', 'i.sickness');
                        $kidney[] = $this->illnessCount($dtf, $dtt, 'Chronic Kidney Disease', $bar->id, 'barangay', 'i.sickness');
                        $pulmonary[] = $this->illnessCount($dtf, $dtt, 'Chronic Obstruction Pulmonary Disease', $bar->id, 'barangay', 'i.sickness');
                        $diabetes[] = $this->illnessCount($dtf, $dtt, 'Diabetes', $bar->id, 'barangay', 'i.sickness');
                        $heart[] = $this->illnessCount($dtf, $dtt, 'Heart Disease', $bar->id, 'barangay', 'i.sickness');
                        $cholesterol[] = $this->illnessCount($dtf, $dtt, 'High Cholesterol', $bar->id, 'barangay', 'i.sickness');
                        $pneumonia[] = $this->illnessCount($dtf, $dtt, 'Influenza and Pneumonia', $bar->id, 'barangay', 'i.sickness');
                        $osteoporosis[] = $this->illnessCount($dtf, $dtt, 'Osteoporosis', $bar->id, 'barangay', 'i.sickness');
                        $otherIllness[] = $this->illnessCount($dtf, $dtt, 'Others', $bar->id, 'barangay', 'i.sickness');
                    }
                }
            }
        }
        $graphArr = [];
        foreach($listOfGeneralStatus as $loc) {
            if($ctype == "municipality") {
                $graphArr[] = $loc->municipality_name;
            } else if($ctype == "barangay") {
                $graphArr[] = $loc->barangay_name;
            }
        }
        $labels = join(",", $graphArr);
        $active = join(",", $act);
        $inactive = join(",", $inc);
        $highRisk = join(",", $hrisk);
        $atRisk = join(",", $arisk);
        $passedAway = join(",", $passed);
        $employedData = join(",", $employed);
        $unemployedData = join(",", $unemployed);
        $selfEmployedData = join(",", $self_employed);
        $elementaryData = join(",", $elementary);
        $highSchoolData = join(",", $highSchool);
        $collegeData = join(",", $college);
        $vocationalData = join(",", $vocational);
        $masterData = join(",", $master);
        $doctoralData = join(",", $doctoral);
        $singleData = join(",", $single);
        $marriedData = join(",", $married);
        $widowedData = join(",", $widowed);
        $divorcedData = join(",", $divorced);
        $separatedData = join(",", $separated);
        $maleData = join(",", $male);
        $femaleData = join(",", $female);
        $catholicData = join(",", $catholic);
        $iglesiaData = join(",", $iglesia);
        $baptistData = join(",", $baptist);
        $adventistData = join(",", $adventist);
        $jilData = join(",", $jil);
        $victoryData = join(",", $victory);
        $cocData = join(",", $coc);
        $islamData = join(",", $islam);
        $buddhistData = join(",", $buddhist);
        $methodistData = join(",", $methodist);
        $othersData = join(",", $others);
        $indigentData = join(",", $indigent);
        $pensionerData = join(",", $pensioner);
        $supportedData = join(",", $supported);
        $pwdData = join(",", $pwd);
        
        $alzheimerData = join(",", $alzheimer);
        $arthritisData = join(",", $arthritis);
        $cancerData = join(",", $cancer);
        $kidneyData = join(",", $kidney);
        $pulmonaryData = join(",", $pulmonary);
        $diabetesData = join(",", $diabetes);
        $heartData = join(",", $heart);
        $cholesterolData = join(",", $cholesterol);
        $pneumoniaData = join(",", $pneumonia);
        $osteoporosisData = join(",", $osteoporosis);
        $otherIllnessData = join(",", $otherIllness);
        
        return response()->json([
            'classtype' => $ctype,
            'passed_away' => $passed,
            'high_risk' => $hrisk,
            'at_risk' => $arisk,
            'inactive' => $inc,
            'active' => $act,
            'labels' => $labels,
            'passedAwayData' => $passedAway,
            'highRiskData' => $highRisk,
            'atRiskData' => $atRisk,
            'inactiveData' => $inactive,
            'activeData' => $active,
            'employed' => $employed,
            'unemployed' => $unemployed,
            'self_employed' => $self_employed,
            'employedData' => $employedData,
            'unemployedData' => $unemployedData,
            'selfEmployedData' => $selfEmployedData,
            'elementary' => $elementary,
            'highSchool' => $highSchool,
            'college' => $college,
            'vocational' => $vocational,
            'master' => $master,
            'doctoral' => $doctoral,
            'elementaryData' => $elementaryData,
            'highSchoolData' => $highSchoolData,
            'collegeData' => $collegeData,
            'vocationalData' => $vocationalData,
            'masterData' => $masterData,
            'doctoralData' => $doctoralData,
            'single' => $single,
            'married' => $married,
            'widowed' => $widowed,
            'divorced' => $divorced,
            'separated' => $separated,
            'singleData' => $singleData,
            'marriedData' => $marriedData,
            'widowedData' => $widowedData,
            'divorcedData' => $divorcedData,
            'separatedData' => $separatedData,
            'male' => $male,
            'female' => $female,
            'maleData' => $maleData,
            'femaleData' => $femaleData,
            'catholicData' => $catholicData,
            'iglesiaData' => $iglesiaData,
            'baptistData' => $baptistData,
            'adventistData' => $adventistData,
            'jilData' => $jilData,
            'victoryData' => $victoryData,
            'cocData' => $cocData,
            'islamData' => $islamData,
            'buddhistData' => $buddhistData,
            'methodistData' => $methodistData,
            'othersData' => $othersData,
            'catholic' => $catholic,
            'iglesia' => $iglesia,
            'baptist' => $baptist,
            'adventist' => $adventist,
            'jil' => $jil,
            'victory' => $victory,
            'coc' => $coc,
            'islam' => $islam,
            'buddhist' => $buddhist,
            'methodist' => $methodist,
            'others' => $others,
            'indigent' => $indigent,
            'pensioner' => $pensioner,
            'supported' => $supported,
            'pwd' => $pwd,
            'indigentData' => $indigentData,
            'pensionerData' => $pensionerData,
            'supportedData' => $supportedData,
            'pwdData' => $pwdData,
            'alzheimer' => $alzheimer,
            'arthritis' => $arthritis,
            'cancer' => $cancer,
            'kidney' => $kidney,
            'pulmonary' => $pulmonary,
            'diabetes' => $diabetes,
            'heart' => $heart,
            'cholesterol' => $cholesterol,
            'pneumonia' => $pneumonia,
            'osteoporosis' => $osteoporosis,
            'otherIllness' => $otherIllness,
            'alzheimerData' => $alzheimerData,
            'arthritisData' => $arthritisData,
            'cancerData' => $cancerData,
            'kidneyData' => $kidneyData,
            'pulmonaryData' => $pulmonaryData,
            'diabetesData' => $diabetesData,
            'heartData' => $heartData,
            'cholesterolData' => $cholesterolData,
            'pneumoniaData' => $pneumoniaData,
            'osteoporosisData' => $osteoporosisData,
            'otherIllnessData' => $otherIllnessData,
            'listOfGeneralStatus' => $listOfGeneralStatus
        ]);
    }
    private function illnessCount() 
    {
        $arg_list = func_get_args();
        $to = str_replace("00:00:00", "11:59:59", $arg_list[1]);
        $search = '%'.$arg_list[2].'%';
        if($arg_list[4] == "municipality") {
            $data = DB::table('users AS u')
                    ->join('user_illness AS i', 'u.id', '=', 'i.user_id')
                    ->join('user_address AS a', 'u.id', '=', 'a.user_id')
                    ->whereNull('u.deleted_at')
                    ->whereNotNull('u.id_number')
                    ->where('u.created_at', '>=', $arg_list[0])->where('u.created_at', '<=', $to)
                    ->where($arg_list[5], 'LIKE', $search)->where('a.municipality', $arg_list[3])
                    ->count();
        } else if($arg_list[4] == "barangay") {
            $data = DB::table('users AS u')
                    ->join('user_illness AS i', 'u.id', '=', 'i.user_id')
                    ->join('user_address AS a', 'u.id', '=', 'a.user_id')
                    ->whereNull('u.deleted_at')
                    ->whereNotNull('u.id_number')
                    ->where('u.created_at', '>=', $arg_list[0])->where('u.created_at', '<=', $to)
                    ->where($arg_list[5], 'LIKE', $search)->where('a.barangay', $arg_list[3])
                    ->count();
        }
        return $data;
    }
    private function classificationCount() 
    {
        $arg_list = func_get_args();
        $to = str_replace("00:00:00", "11:59:59", $arg_list[1]);
        $search = '%'.$arg_list[2].'%';
        if($arg_list[4] == "municipality") {
            $data = DB::table('users AS u')
                    ->join('user_classification AS c', 'u.id', '=', 'c.user_id')
                    ->join('user_address AS a', 'u.id', '=', 'a.user_id')
                    ->whereNull('u.deleted_at')
                    ->whereNotNull('u.id_number')
                    ->where('u.created_at', '>=', $arg_list[0])->where('u.created_at', '<=', $to)
                    ->where($arg_list[5], 'LIKE', $search)->where('a.municipality', $arg_list[3])
                    ->count();
        } else if($arg_list[4] == "barangay") {
            $data = DB::table('users AS u')
                    ->join('user_classification AS c', 'u.id', '=', 'c.user_id')
                    ->join('user_address AS a', 'u.id', '=', 'a.user_id')
                    ->whereNull('u.deleted_at')
                    ->whereNotNull('u.id_number')
                    ->where('u.created_at', '>=', $arg_list[0])->where('u.created_at', '<=', $to)
                    ->where($arg_list[5], 'LIKE', $search)->where('a.barangay', $arg_list[3])
                    ->count();
        }
        return $data;
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
    public function getEmploymentStatuses(Request $request) 
    {
        try {
            DB::beginTransaction();
                $msg = DB::table('employment_statuses')->orderBy('id')->get();
            DB::commit();
        } catch (Exception $e) 
        {
            DB::rollBack();
            $msg = $e->getMessage();
        }
        return response()->json(['employment_statuses' => $msg]);
    }
    public function getMemberStatuses(Request $request) {
        try {
            DB::beginTransaction();
                $msg = DB::table('member_statuses')->orderBy('id')->get();
            DB::commit();
        } catch (Exception $e) 
        {
            DB::rollBack();
            $msg = $e->getMessage();
        }
        return response()->json(['member_statuses' => $msg]);
    }
    public function getReportTable(Request $request)
    {
        try {
            DB::beginTransaction();
                $municipalities = DB::table('municipalities AS mun')
                                ->leftJoin('user_address AS addr', 'mun.municipality_code_number', '=', 'addr.municipality')
                                ->join('users AS usr', 'addr.user_id', '=', 'usr.id')
                                ->join('user_details AS ud', 'usr.id', '=', 'ud.user_id')
                                ->select('mun.municipality_name AS municipality_name')
                                ->selectRaw("COUNT(addr.municipality) AS municipality, COUNT(ud.member_status) AS member_status")
                                ->whereNull('usr.deleted_at')->whereNotNull('usr.id_number')
                                ->where('usr.created_at', '>=', $request->from)->where('usr.created_at', '<=', $request->to)
                                ->orderBy('mun.municipality_name')
                                ->groupBy('ud.member_status')->get();
            DB::commit();
            $msg = 'success';
        } catch (Exception $e) 
        {
            DB::rollBack();
            $msg = $e->getMessage();
        }
        return response()->json([
            'report_status' => $msg,
            'municipalities' => $municipalities
        ]);
    }
}
