<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class add_notes_in_modules extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $moduleid = \DB::table('modules')->insertGetId([
            'name' => 'Notes',
            'slug' => 'notes',
        ]);

        $permissiondata = [
            ['name'=>'View', 'slug'=>'view-notes', 'module_id'=>$moduleid],
            ['name'=>'Edit', 'slug'=>'edit-notes', 'module_id'=>$moduleid],
            ['name'=>'Delete', 'slug'=>'delete-notes', 'module_id'=>$moduleid],
            ['name'=>'Create', 'slug'=>'create-notes', 'module_id'=>$moduleid],
        ];
        \DB::table('permissions')->insert($permissiondata);
    }
}
