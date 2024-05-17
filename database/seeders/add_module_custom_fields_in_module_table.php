<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class add_module_custom_fields_in_module_table extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $moduleid = \DB::table('modules')->insertGetId([
            'name' => 'Custom Fields',
            'slug' => 'custom-fields',
        ]);

        $permissiondata = [
            ['name'=>'View', 'slug'=>'view-custom-fields', 'module_id'=>$moduleid],
            ['name'=>'Edit', 'slug'=>'edit-custom-fields', 'module_id'=>$moduleid],
            ['name'=>'Delete', 'slug'=>'delete-custom-fields', 'module_id'=>$moduleid],
            ['name'=>'Create', 'slug'=>'create-custom-fields', 'module_id'=>$moduleid],
        ];
        \DB::table('permissions')->insert($permissiondata);
    }
}
