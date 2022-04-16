<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    /**
     * @test
     * for add book to cart successfull
     */
    public function test_SuccessfullAddToCart()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNjUwMDMyMjQyLCJleHAiOjE2NTAwMzU4NDIsIm5iZiI6MTY1MDAzMjI0MiwianRpIjoic2RIakdsUHdFS2dSMU02VSIsInN1YiI6IjIiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.6-tSbrouTVvC4cCETZ3plDJKtLO1Sysx2YP1eKNXoH4'
        ])->json(
            'POST',
            '/api/addBookToCartByBookId',
            [
                "book_id" => "8",
            ]
        );
        $response->assertStatus(201)->assertJson(['message' => 'Book added to Cart Sucessfully']);
    }

    /**
     * @test
     * for Unsuccessfull add book to cart
     */
    public function test_UnSuccessfullAddToCart()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNjUwMDMyMjQyLCJleHAiOjE2NTAwMzU4NDIsIm5iZiI6MTY1MDAzMjI0MiwianRpIjoic2RIakdsUHdFS2dSMU02VSIsInN1YiI6IjIiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.6-tSbrouTVvC4cCETZ3plDJKtLO1Sysx2YP1eKNXoH4'
        ])->json(
            'POST',
            '/api/addBookToCartByBookId',
            [
                "book_id" => "20",
            ]
        );
        $response->assertStatus(404)->assertJson(['message' => 'Book not Found']);
    }

    /**
     * @test
     * for delet book from cart successfull
     */
    public function test_SuccessfullAddDeleteFromCart()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNjUwMDMyMjQyLCJleHAiOjE2NTAwMzU4NDIsIm5iZiI6MTY1MDAzMjI0MiwianRpIjoic2RIakdsUHdFS2dSMU02VSIsInN1YiI6IjIiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.6-tSbrouTVvC4cCETZ3plDJKtLO1Sysx2YP1eKNXoH4'
        ])->json(
            'POST',
            '/api/deleteBookByCartId',
            [
                "id" => "8",
            ]
        );
        $response->assertStatus(201)->assertJson(['message' => 'Book deleted Sucessfully from cart']);
    }

    /**
     * @test
     * for delet book from cart Unsuccessfull
     */
    public function test_UnSuccessfullAddDeleteFromCart()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNjUwMDMyMjQyLCJleHAiOjE2NTAwMzU4NDIsIm5iZiI6MTY1MDAzMjI0MiwianRpIjoic2RIakdsUHdFS2dSMU02VSIsInN1YiI6IjIiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.6-tSbrouTVvC4cCETZ3plDJKtLO1Sysx2YP1eKNXoH4'
        ])->json(
            'POST',
            '/api/deleteBookByCartId',
            [
                "id" => "25",
            ]
        );
        $response->assertStatus(404)->assertJson(['message' => 'Book not Found in cart']);
    }

    /**
     * @test
     * for Successfull Cart update by adding quantity
     */
    public function test_SuccessfullUpdateCart()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNjUwMDMyMjQyLCJleHAiOjE2NTAwMzU4NDIsIm5iZiI6MTY1MDAzMjI0MiwianRpIjoic2RIakdsUHdFS2dSMU02VSIsInN1YiI6IjIiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.6-tSbrouTVvC4cCETZ3plDJKtLO1Sysx2YP1eKNXoH4'
        ])->json(
            'POST',
            '/api/increamentBookQuantityInCart',
            [
                "id" => "3",
                "book_quantity" => "5",
            ]
        );
        $response->assertStatus(201)->assertJson(['message' => 'Book Quantity updated Successfully']);
    }

    /**
     * @test
     * for Successfull Cart update by adding quantity
     */
    public function test_UnSuccessfullUpdateCart()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNjUwMDMyMjQyLCJleHAiOjE2NTAwMzU4NDIsIm5iZiI6MTY1MDAzMjI0MiwianRpIjoic2RIakdsUHdFS2dSMU02VSIsInN1YiI6IjIiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.6-tSbrouTVvC4cCETZ3plDJKtLO1Sysx2YP1eKNXoH4'
        ])->json(
            'POST',
            '/api/increamentBookQuantityInCart',
            [
                "id" => "25",
                "book_quantity" => "5",
            ]
        );
        $response->assertStatus(404)->assertJson(['message' => 'Item Not found with this id']);
    }

    /**
     * @test for successfull display all bokks
     * present in the cart
     */
    public function test_SuccessfullDisplayBooksFromCart()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNjUwMDMyMjQyLCJleHAiOjE2NTAwMzU4NDIsIm5iZiI6MTY1MDAzMjI0MiwianRpIjoic2RIakdsUHdFS2dSMU02VSIsInN1YiI6IjIiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.6-tSbrouTVvC4cCETZ3plDJKtLO1Sysx2YP1eKNXoH4'
        ])->json(
            'GET',
            '/api/getAllBooksInCart',
            []
        );
        $response->assertStatus(201)->assertJson(['message' => 'Books Present in Cart :']);
    }

    /**
     * @test for Unsuccessfull display all bokks
     * present in the cart
     */
    public function test_UnSuccessfullDisplayBooksFromCart()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNjUwMDMyMjQyLCJleHAiOjE2NTAwMzU4NDIsIm5iZiI6MTY1MDAzMjI0MiwianRpIjoic2RIakdsUHdFS2dSMU02VSIsInN1YiI6IjIiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.6-tSbrouTVvC4cCETZ3plDJKtLO1Sysx2YP1eKNXij5'
        ])->json(
            'GET',
            '/api/getAllBooksInCart',
            []
        );
        $response->assertStatus(404)->assertJson(['message' => 'Invalid authorization token']);
    }
}
