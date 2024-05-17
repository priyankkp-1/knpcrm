<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class add_module_custom_field_value_in_module_table extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $moduleid = \DB::table('modules')->insertGetId([
            'name' => 'Custom Field Value',
            'slug' => 'custom-field-value',
        ]);

        $permissiondata = [
            ['name'=>'View', 'slug'=>'view-custom-field-value', 'module_id'=>$moduleid],
            ['name'=>'Edit', 'slug'=>'edit-custom-field-value', 'module_id'=>$moduleid],
            ['name'=>'Delete', 'slug'=>'delete-custom-field-value', 'module_id'=>$moduleid],
            ['name'=>'Create', 'slug'=>'create-custom-field-value', 'module_id'=>$moduleid],
        ];
        \DB::table('permissions')->insert($permissiondata);
    }
}
