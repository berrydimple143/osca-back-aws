<?php

use App\Http\Controllers\Api\DataController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ChatsController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::controller(ChatsController::class)->group(function () {
    Route::post('getMessages', 'getMessages');
    Route::post('sendMessage', 'sendMessage');
});

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('register/admin', 'registerAdmin');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
});
Route::controller(DataController::class)->group(function () {
    Route::get('get-details', 'getDetails');
    Route::get('get-barangays/{mun}', 'getBarangays');
    Route::post('get-barangay-data', 'getBarangayData');
    Route::post('file-upload', 'fileUpload');
    Route::get('generate-and-get-id/{mun}/{dist}', 'generateAndGetID');
    Route::get('check-id-number/{idNumber}', 'checkId');
    Route::post('get-municipalities', 'getMunicipalities');
    Route::post('sendEmail', 'sendEmail');
});
Route::controller(AnalyticsController::class)->group(function () {
    Route::get('get-municipalities-members', 'getTotalMembersPerMunicipality');
});
Route::controller(MemberController::class)->group(function () {
    Route::post('getMembers', 'getMembers');
    Route::post('exportData', 'exportData');
    Route::post('updateMember', 'updateMember');
    Route::post('getMember', 'getMember');
    Route::post('deleteMember', 'deleteMember');
    Route::post('userSignature', 'userSignature');
    Route::post('vaccineIdCamera', 'vaccineIdCamera');
    Route::post('userCamera', 'userCamera');
    Route::post('getCanvasData', 'getCanvasData');
    Route::get('getSpecificMunicipalities/{mun}', 'getSpecificMunicipalities');
    Route::post('getDosageLevel', 'getDosageLevel');
    Route::post('saveVaccinationInfo', 'saveVaccinationInfo');
    Route::post('uploadImage', 'uploadImage');
    Route::post('saveMemberTransaction', 'saveMemberTransaction');
});
Route::controller(UserController::class)->group(function () {
    Route::post('getUsers', 'getUsers');
    Route::post('getUser', 'getUser');
    Route::post('saveUser', 'saveUser');
    Route::post('updateUser', 'updateUser');
    Route::post('deleteUser', 'deleteUser');
    Route::post('emailExist', 'emailExist');
    Route::post('checkPassword', 'checkPassword');
    Route::post('changePassword', 'changePassword');
    Route::post('activateAccount', 'activateAccount');
    Route::post('getUserStatus', 'getUserStatus');
});
Route::controller(RoleController::class)->group(function () {
    Route::post('getRoles', 'getRoles');
});
Route::controller(ReportController::class)->group(function () {
    Route::post('getMemberStatuses', 'getMemberStatuses');
    Route::post('getEmploymentStatuses', 'getEmploymentStatuses');
    Route::post('generateMemberReport', 'generateMemberReport');
    Route::post('exportReport', 'exportReport');
});
