<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class add_delete_task_comment_document_in_permission_table extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $comments = \DB::table('modules')->where('slug','like', '%task-comments%')->first();
        $documents = \DB::table('modules')->where('slug','like', '%task_documents%')->first();

        \DB::table('permissions')->where('module_id',$documents->id)->delete();

        $permissiondata = [
            ['name'=>'Delete', 'slug'=>'delete-task-comments', 'module_id'=>$comments->id],
            ['name'=>'View',   'slug'=>'view-task-documents', 'module_id'=>$documents->id],
            ['name'=>'Edit',   'slug'=>'edit-task-documents', 'module_id'=>$documents->id],
            ['name'=>'Create', 'slug'=>'create-task-documents', 'module_id'=>$documents->id],
            ['name'=>'Delete', 'slug'=>'delete-task-documents', 'module_id'=>$documents->id],
        ];
        \DB::table('permissions')->insert($permissiondata);
    }
}
