<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class add_template_data extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $hash_id = getHashid();
        $data = [
            ['hash_id'=>$hash_id,'header_title'=>'Email Template','header_color'=>'#ffffff','header_front_size'=>'16px','header_background_color'=>'#269ed5','form_title'=>'Template','form_front_size'=>'11px','form_button_color'=>'#ffffff','form_button_background_color'=>'#269ed5','footer_title'=>'Footer','footer_color'=>'#ffffff','footer_front_size'=>'16px','footer_background_color'=>'#269ed5'],
        ];
        \DB::table('templates')->insert($data);
    }
}
