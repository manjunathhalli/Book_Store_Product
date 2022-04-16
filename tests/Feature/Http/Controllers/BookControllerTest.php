<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BookControllerTest extends TestCase
{
    /**
     * @test for
     * Admin Book Addition successfull
     */

    public function test_SuccessfullAddingBook()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])->json('POST', '/api/addingBook', [
            "name" => "PHP",
            "description" => "PHP Programming",
            "author" => "manjunath",
            "image" => "cat.jpg",
            "Price" => "1000",
            "quantity" => "10",
        ]);
        $response->assertStatus(201)->assertJson(['message' => 'Book created successfully']);
    }

    /**
     * @test for
     * Admin Book Quantity Addition successfull
     */

    public function test_SuccessfullAddQuantityToExistingBook()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNjUwMDMxODM4LCJleHAiOjE2NTAwMzU0MzgsIm5iZiI6MTY1MDAzMTgzOCwianRpIjoiYzhMV2hkMU9MTjRsaXREeCIsInN1YiI6IjIiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.j6WmGlYAb4g7IIRlI5PjLEPcx8dKjYlx4oIuqZhi_Jw'
        ])->json(
            'POST',
            '/api/addQuantityToExistBook',
            [
                "id" => "5",
                "quantity" => "7"
            ]
        );
        $response->assertStatus(201)->assertJson(['message' => 'Book Quantity updated Successfully']);
    }

    /**
     * @test for
     * Admin Book Quantity Addition Unsuccessfull
     */
    public function test_UnSuccessfullAddQuantityToExistingBook()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNjUwMDMxODM4LCJleHAiOjE2NTAwMzU0MzgsIm5iZiI6MTY1MDAzMTgzOCwianRpIjoiYzhMV2hkMU9MTjRsaXREeCIsInN1YiI6IjIiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.j6WmGlYAb4g7IIRlI5PjLEPcx8dKjYlx4oIuqZhi_Jw'
        ])->json(
            'POST',
            '/api/addQuantityToExistBook',
            [
                "id" => "30",
                "quantity" => "5"
            ]
        );
        $response->assertStatus(404)->assertJson(['message' => 'Couldnot found a book with that given id']);
    }


    /**
     * @test for
     * Admin Delete Book successfull
     */
    public function test_SuccessfullDeleteBook()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNjUwMDMxODM4LCJleHAiOjE2NTAwMzU0MzgsIm5iZiI6MTY1MDAzMTgzOCwianRpIjoiYzhMV2hkMU9MTjRsaXREeCIsInN1YiI6IjIiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.j6WmGlYAb4g7IIRlI5PjLEPcx8dKjYlx4oIuqZhi_Jw'
        ])->json(
            'POST',
            '/api/deleteBookById',
            [
                "id" => "4",
            ]
        );
        $response->assertStatus(201)->assertJson(['message' => 'Book deleted Sucessfully']);
    }

    /**
     * @test for
     * Admin Delete Book Unsuccessfull
     */
    public function test_UnSuccessfullDeleteBook()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNjUwMDMxODM4LCJleHAiOjE2NTAwMzU0MzgsIm5iZiI6MTY1MDAzMTgzOCwianRpIjoiYzhMV2hkMU9MTjRsaXREeCIsInN1YiI6IjIiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.j6WmGlYAb4g7IIRlI5PjLEPcx8dKjYlx4oIuqZhi_Jw'
        ])->json(
            'POST',
            '/api/deleteBookById',
            [
                "id" => "33",
            ]
        );
        $response->assertStatus(404)->assertJson(['message' => 'Book not Found']);
    }
}
