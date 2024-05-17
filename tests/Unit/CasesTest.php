<?php

namespace Tests\Unit;
use Tests\TestCase;
//use PHPUnit\Framework\TestCase;

use App\Models\Admin;



class CasesTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_get_all_countries()
    {
        $response = $this->get('/api/admin/countries/getlist')
        ->assertJsonStructure([
            'data',
            'message',
        ]);
    }

    
    public function testSuccessfulLogin()
    {
        $response = $this->post('/api/admin/login', 
            ['email' => 'admin@gmail.com', 'password' => 'password1']
        );
        //$response->assertStatus(200);
        $this->assertEquals($response->status(),200);
    }
}

// php artisan make:test ContractTypeTest --unit 

// php artisan test