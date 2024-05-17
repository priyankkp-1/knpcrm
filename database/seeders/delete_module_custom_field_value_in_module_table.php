<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class delete_module_custom_field_value_in_module_table extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $moduleid = \DB::table('modules')->where('slug','like', '%custom-field-value%')->first();
      
        \DB::table('permissions')->where('module_id',$moduleid->id)->delete();
        \DB::table('modules')->where('id',$moduleid->id)->delete();
    }
}
