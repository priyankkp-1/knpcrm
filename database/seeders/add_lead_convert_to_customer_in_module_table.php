<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class add_lead_convert_to_customer_in_module_table extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $customers = \DB::table('modules')->where('slug','like', '%customers%')->first();
        $leads = \DB::table('modules')->where('slug','like', '%leads%')->first();

        $permissiondata = [
            ['name'=>'Create', 'slug'=>'convert-to-customers', 'module_id'=>$leads->id],
            ['name'=>'View',   'slug'=>'view-customers', 'module_id'=>$customers->id],
            ['name'=>'Edit',   'slug'=>'edit-customers', 'module_id'=>$customers->id],
            ['name'=>'Delete', 'slug'=>'delete-customers', 'module_id'=>$customers->id],
            ['name'=>'Create', 'slug'=>'create-customers', 'module_id'=>$customers->id],
        ];
        \DB::table('permissions')->insert($permissiondata);
    }
}
