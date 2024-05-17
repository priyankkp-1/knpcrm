<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class add_web_lead_in_module_table extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $moduleid = \DB::table('modules')->insertGetId([
            'name' => 'Web To Lead',
            'slug' => 'web-to-lead',
        ]);

        $permissiondata = [
            ['name'=>'View', 'slug'=>'view-web-to-lead', 'module_id'=>$moduleid],
            ['name'=>'Edit', 'slug'=>'edit-web-to-lead', 'module_id'=>$moduleid],
            ['name'=>'Delete', 'slug'=>'delete-web-to-lead', 'module_id'=>$moduleid],
            ['name'=>'Create', 'slug'=>'create-web-to-lead', 'module_id'=>$moduleid],
        ];
        
        \DB::table('permissions')->insert($permissiondata);
    }
}
