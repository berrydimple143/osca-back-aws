<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class UsersExport implements FromCollection, WithHeadings
{
    public $type;
    public $typeVal;
    public $munVal;
     
    public function __construct($type, $mun, $value) {
        $this->type = $type;
        $this->munVal = $mun;
        $this->typeVal = $value;
    }
    public function collection()
    {
        if($this->type == "all") {
            return DB::table('users')
                    ->join('user_address', 'users.id', '=', 'user_address.user_id')
                    ->join('user_details', 'users.id', '=', 'user_details.user_id')
                    ->join('contact_details', 'users.id', '=', 'contact_details.user_id')
                    ->select('users.first_name AS first_name', 'users.last_name AS last_name', 'users.id_number AS id_number', DB::raw('DATE_FORMAT(user_details.birth_date, "%b %d, %Y") as birth_date'), 'user_address.province_name AS province_name', 'user_address.municipality_name AS municipality_name', 'user_address.barangay_name AS barangay_name', 'user_address.district_no AS district_no', 'users.email AS email', 'contact_details.mobile AS mobile', 'users.created_at AS created_at', 'users.updated_at AS updated_at')
                    ->whereNotNull('users.id_number')->whereNull('users.deleted_at')->get();
        } else if($this->type == "municipality") {
            return DB::table('users')
                    ->join('user_address', 'users.id', '=', 'user_address.user_id')
                    ->join('user_details', 'users.id', '=', 'user_details.user_id')
                    ->join('contact_details', 'users.id', '=', 'contact_details.user_id')
                    ->select('users.first_name AS first_name', 'users.last_name AS last_name', 'users.id_number AS id_number', DB::raw('DATE_FORMAT(user_details.birth_date, "%b %d, %Y") as birth_date'), 'user_address.province_name AS province_name', 'user_address.municipality_name AS municipality_name', 'user_address.barangay_name AS barangay_name', 'user_address.district_no AS district_no', 'users.email AS email', 'contact_details.mobile AS mobile', 'users.created_at AS created_at', 'users.updated_at AS updated_at')
                    ->where('user_address.municipality', $this->typeVal)->whereNotNull('users.id_number')->whereNull('users.deleted_at')->get();
        } else {
            return DB::table('users')
                    ->join('user_address', 'users.id', '=', 'user_address.user_id')
                    ->join('user_details', 'users.id', '=', 'user_details.user_id')
                    ->join('contact_details', 'users.id', '=', 'contact_details.user_id')
                    ->select('users.first_name AS first_name', 'users.last_name AS last_name', 'users.id_number AS id_number', DB::raw('DATE_FORMAT(user_details.birth_date, "%b %d, %Y") as birth_date'), 'user_address.province_name AS province_name', 'user_address.municipality_name AS municipality_name', 'user_address.barangay_name AS barangay_name', 'user_address.district_no AS district_no', 'users.email AS email', 'contact_details.mobile AS mobile', 'users.created_at AS created_at', 'users.updated_at AS updated_at')
                    ->where('user_address.municipality', $this->munVal)->where('user_address.barangay', $this->typeVal)->whereNotNull('users.id_number')->whereNull('users.deleted_at')->get();
        }
    }
    public function headings(): array
    {
        return [
            'First Name',
            'Last Name',
            'ID Number',
            'Date of Birth',
            'Province',
            'Municipality',
            'Barangay',
            'District No.',
            'Email',
            'Mobile',
            'Date Created',
            'Date Updated',
        ];
    }
}
