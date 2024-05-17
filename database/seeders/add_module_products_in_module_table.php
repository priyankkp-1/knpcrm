<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class add_module_products_in_module_table extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $moduleid = \DB::table('modules')->insertGetId([
            'name' => 'Products',
            'slug' => 'products',
        ]);

        $permissiondata = [
            ['name'=>'View', 'slug'=>'view-products', 'module_id'=>$moduleid],
            ['name'=>'Edit', 'slug'=>'edit-products', 'module_id'=>$moduleid],
            ['name'=>'Delete', 'slug'=>'delete-products', 'module_id'=>$moduleid],
            ['name'=>'Create', 'slug'=>'create-products', 'module_id'=>$moduleid],
        ];
        \DB::table('permissions')->insert($permissiondata);
    }
}
