<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class add_reminder_modules_with_permission extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $moduleid = \DB::table('modules')->insertGetId([
            'name' => 'Reminders',
            'slug' => 'reminders',
        ]);

        $permissiondata = [
            ['name'=>'View', 'slug'=>'view-reminders', 'module_id'=>$moduleid],
            ['name'=>'Edit', 'slug'=>'edit-reminders', 'module_id'=>$moduleid],
            ['name'=>'Delete', 'slug'=>'delete-reminders', 'module_id'=>$moduleid],
            ['name'=>'Create', 'slug'=>'create-reminders', 'module_id'=>$moduleid],
        ];
        \DB::table('permissions')->insert($permissiondata);
    }
}
