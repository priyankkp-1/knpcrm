<?php

namespace App\Imports;

use App\Models\Leads;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ImportLead implements ToModel, WithHeadingRow, WithStartRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function startRow(): int
    {
        return 2;
    }

    public function headingRow(): int
    {
        return 0;
    }

    public function model(array $row)
    {
        if(!array_filter($row) || empty($row[3]) || (Leads::where('email', '=', $row[3])->exists())) {
            return null;
        }

        return new Leads([
            'hash_id'      => getHashid(),
            'first_name'   => isset($row[0])?$row[0]:NULL,
            'last_name'    => isset($row[1])?$row[1]:NULL,
            'company'      => isset($row[2])?$row[2]:NULL,
            'email'        => isset($row[3])?$row[3]:NULL,
            'country_code' => isset($row[4])?$row[4]:NULL,
            'phone'        => isset($row[5])?$row[5]:NULL,
            'city'         => isset($row[6])?$row[6]:NULL,
            'state'        => isset($row[7])?$row[7]:NULL,
            'zip'          => isset($row[8])?$row[8]:NULL,
            'address'      => isset($row[9])?$row[9]:NULL,
            'address_1'    => isset($row[10])?$row[10]:NULL,
            'address_2'    => isset($row[11])?$row[11]:NULL,
            'description'  => isset($row[12])?$row[12]:NULL,
            'is_public'    => isset($row[13])?$row[13]:NULL,
        ]);
    }
}
//-> lead import with excel and csv is done and also, duplicate lead or null lead removed