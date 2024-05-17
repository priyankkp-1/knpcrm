<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class add_task_comments_in_module_table extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $moduleid = \DB::table('modules')->insertGetId([
            'name' => 'Task Comments',
            'slug' => 'task-comments',
        ]);

        $permissiondata = [
            ['name'=>'View', 'slug'=>'view-task-comments', 'module_id'=>$moduleid],
            ['name'=>'Edit', 'slug'=>'edit-task-comments', 'module_id'=>$moduleid],
            ['name'=>'Create', 'slug'=>'create-task-comments', 'module_id'=>$moduleid],
        ];
        \DB::table('permissions')->insert($permissiondata);


        $moduleid = \DB::table('modules')->insertGetId([
            'name' => 'Task Documents',
            'slug' => 'task_documents',
        ]);

        $permissiondata = [
            ['name'=>'View', 'slug'=>'view-task_documents', 'module_id'=>$moduleid],
            ['name'=>'Edit', 'slug'=>'edit-task_documents', 'module_id'=>$moduleid],
            ['name'=>'Create', 'slug'=>'create-task_documents', 'module_id'=>$moduleid],
        ];
        \DB::table('permissions')->insert($permissiondata);
    }
}
