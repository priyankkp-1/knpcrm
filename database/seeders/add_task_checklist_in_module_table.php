<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class add_task_checklist_in_module_table extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $moduleid = \DB::table('modules')->insertGetId([
            'name' => 'Task Check List',
            'slug' => 'task-checklist',
        ]);

        $permissiondata = [
            ['name'=>'View', 'slug'=>'view-task-checklist', 'module_id'=>$moduleid],
            ['name'=>'Edit', 'slug'=>'edit-task-checklist', 'module_id'=>$moduleid],
            ['name'=>'Delete', 'slug'=>'delete-task-checklist', 'module_id'=>$moduleid],
            ['name'=>'Create', 'slug'=>'create-task-checklist', 'module_id'=>$moduleid],
        ];
        \DB::table('permissions')->insert($permissiondata);

    }
}
