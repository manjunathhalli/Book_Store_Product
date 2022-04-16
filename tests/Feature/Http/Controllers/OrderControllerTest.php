<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    /**
     * @test
     * for Successfull Placing an Order
     */
    public function test_SuccessfullPlaceOrder()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNjUwMDMzOTAzLCJleHAiOjE2NTAwMzc1MDMsIm5iZiI6MTY1MDAzMzkwMywianRpIjoiU3ZHUzFsWk1OeUd1ZmV3NiIsInN1YiI6IjIiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.x8QNczV1fB-y-ghfZCW5PLtTQA_CrHEP8557iWoRqF8'
        ])->json(
            'POST',
            '/api/placeOrder',
            [
                "address_id" => 2,
                "name" => "maths",
                "quantity" => "5",
            ]
        );
        $response->assertStatus(201)->assertJson(['message' => 'Order Successfully Placed']);
    }

    /**
     * @test
     * for UnSuccessfull Placing an Order
     */
    public function test_UnSuccessfullPlaceOrder()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNjUwMDMzOTAzLCJleHAiOjE2NTAwMzc1MDMsIm5iZiI6MTY1MDAzMzkwMywianRpIjoiU3ZHUzFsWk1OeUd1ZmV3NiIsInN1YiI6IjIiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.x8QNczV1fB-y-ghfZCW5PLtTQA_CrHEP8557iWoRqF8'
        ])->json(
            'POST',
            '/api/placeOrder',
            [
                "address_id" => 2,
                "name" => "chemistry",
                "quantity" => "5",
            ]
        );
        $response->assertStatus(401)->assertJson(['message' => 'We Do not have this book in the store']);
    }
}
