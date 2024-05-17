<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class add_task_in_module_table extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $moduleid = \DB::table('modules')->insertGetId([
            'name' => 'Tasks',
            'slug' => 'tasks',
        ]);

        $permissiondata = [
            ['name'=>'View', 'slug'=>'view-tasks', 'module_id'=>$moduleid],
            ['name'=>'Edit', 'slug'=>'edit-tasks', 'module_id'=>$moduleid],
            ['name'=>'Delete', 'slug'=>'delete-tasks', 'module_id'=>$moduleid],
            ['name'=>'Create', 'slug'=>'create-tasks', 'module_id'=>$moduleid],
        ];
        \DB::table('permissions')->insert($permissiondata);
    }
}
