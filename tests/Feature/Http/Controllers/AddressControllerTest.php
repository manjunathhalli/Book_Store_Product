<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AddressControllerTest extends TestCase
{
    /**
     * @test for
     * Address add to respective user successfull
     *
     */

    public function test_SuccessfulAddAddress()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNjUwMDMzMDkwLCJleHAiOjE2NTAwMzY2OTAsIm5iZiI6MTY1MDAzMzA5MCwianRpIjoiT2RSRkEybU9DbGVzTkpGQiIsInN1YiI6IjIiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.VVZe9Jvto9Y15k60AQwcIdsxesrakO_BbfvFRPRjHok'
        ])->json('POST', '/api/addAddress', [
            "address" => "sree",
            "city" => "bengaluru",
            "state" => "karantaka",
            "landmark" => "near market big bazaar",
            "pincode" => "69008",
            "address_type" => "home",
        ]);
        $response->assertStatus(201)->assertJson(['message' => 'Address Added Successfully']);
    }

    public function test_UnSuccessfulAddAddress()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNjUwMDMzMDkwLCJleHAiOjE2NTAwMzY2OTAsIm5iZiI6MTY1MDAzMzA5MCwianRpIjoiT2RSRkEybU9DbGVzTkpGQiIsInN1YiI6IjIiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.VVZe9Jvto9Y15k60AQwcIdsxesrakO_BbfvFRPRjHok'
        ])->json('POST', '/api/addAddress', [
            "address" => "sree",
            "city" => "bengaluru",
            "state" => "karantaka",
            "landmark" => "near market big bazaar",
            "pincode" => "69008",
            "address_type" => "home",
        ]);
        $response->assertStatus(201)->assertJson(['message' => 'Address alredy present for the user']);
    }

    /**
     * @test for
     * Address Update to respective user successfull
     *
     */
    public function test_SuccessfulUpdateAddress()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNjUwMDMzMDkwLCJleHAiOjE2NTAwMzY2OTAsIm5iZiI6MTY1MDAzMzA5MCwianRpIjoiT2RSRkEybU9DbGVzTkpGQiIsInN1YiI6IjIiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.VVZe9Jvto9Y15k60AQwcIdsxesrakO_BbfvFRPRjHok'
        ])->json('POST', '/api/updateAddress', [
            "id" => "2",
            "address" => "devi krupa",
            "city" => "hubli",
            "state" => "karanatak",
            "landmark" => "sujatha takies",
            "pincode" => "582345",
            "addresstype" => "work",
        ]);
        $response->assertStatus(201)->assertJson(['message' => 'Address Updated Successfully']);
    }

    /**
     * @test for
     * Address Update to respective user Unsuccessfull
     *
     */
    public function test_UnSuccessfulUpdateAddress()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNjUwMDMzMDkwLCJleHAiOjE2NTAwMzY2OTAsIm5iZiI6MTY1MDAzMzA5MCwianRpIjoiT2RSRkEybU9DbGVzTkpGQiIsInN1YiI6IjIiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.VVZe9Jvto9Y15k60AQwcIdsxesrakO_BbfvFRPRjHok'
        ])->json('POST', '/api/updateAddress', [
            "id" => "25",
            "address" => "devi krupa",
            "city" => "hubli",
            "state" => "karanatak",
            "landmark" => "sujatha takies",
            "pincode" => "582345",
            "addresstype" => "work",
        ]);
        $response->assertStatus(201)->assertJson(['message' => 'Address not present add address first']);
    }

    /**
     * @test
     * for delete address successfull
     */
    public function test_SuccessfullDeleteAddress()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNjUwMDMzMDkwLCJleHAiOjE2NTAwMzY2OTAsIm5iZiI6MTY1MDAzMzA5MCwianRpIjoiT2RSRkEybU9DbGVzTkpGQiIsInN1YiI6IjIiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.VVZe9Jvto9Y15k60AQwcIdsxesrakO_BbfvFRPRjHok'
        ])->json(
            'POST',
            '/api/deleteAddress',
            [
                "id" => "3",
            ]
        );
        $response->assertStatus(201)->assertJson(['message' => 'Address deleted Sucessfully']);
    }

    /**
     * @test for successfull display all Address
     * for respective user
     */
    public function test_SuccessfullDisplayAddress()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNjUwMDMzMDkwLCJleHAiOjE2NTAwMzY2OTAsIm5iZiI6MTY1MDAzMzA5MCwianRpIjoiT2RSRkEybU9DbGVzTkpGQiIsInN1YiI6IjIiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.VVZe9Jvto9Y15k60AQwcIdsxesrakO_BbfvFRPRjHok'
        ])->json(
            'GET',
            '/api/getAddress',
            []
        );
        $response->assertStatus(201)->assertJson(['message' => 'Fetched Address Successfully']);
    }

    /**
     * @test for Unsuccessfull display all Address
     * for respective user
     */
    public function test_UnSuccessfullDisplayAddress()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNjUwMDMzMDkwLCJleHAiOjE2NTAwMzY2OTAsIm5iZiI6MTY1MDAzMzA5MCwianRpIjoiT2RSRkEybU9DbGVzTkpGQiIsInN1YiI6IjIiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.VVZe9Jvto9Y15k60AQwcIdsxesrakO_BbfvFRPRjHok'
        ])->json(
            'GET',
            '/api/getAddress',
            []
        );
        $response->assertStatus(404)->assertJson(['message' => 'Address not found']);
    }
}
