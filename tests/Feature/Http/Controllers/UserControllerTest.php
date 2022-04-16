<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    /**
     * @test for
     * user registration successfull
     *
     */

    public function test_SuccessfulRegistration()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])->json('POST', '/api/register', [
            "role" => "user",
            "firstname" => "Manjunath",
            "lastname" => "Halli",
            "email" => "manjunathHalli@gmail.com",
            "phone_no" => "8073227941",
            "password" => "223344",
            "confirm_password" => "223344"
        ]);
        $response->assertStatus(201)->assertJson(['message' => 'User Successfully Registered']);
    }

    /**
     * @test for
     * Unsuccessfull User Registration
     */
    public function test_UnSuccessfulRegistration()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])->json('POST', '/api/register', [
            "role" => "user",
            "firstname" => "Manju",
            "lastname" => "Halli",
            "email" => "manjunHalli@gmail.com",
            "phone_no" => "8073227941",
            "password" => "223344",
            "confirm_password" => "223344"
        ]);
        $response->assertStatus(401)->assertJson(['message' => 'The email has already been taken']);
    }

    /**
     * @test for
     * Successfull Login
     */
    public function test_SuccessfulLogin()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])->json(
            'POST',
            '/api/login',
            [
                "email" => "manjunathHalli629@gmail.com",
                "password" => "223344"
            ]
        );
        $response->assertStatus(200)->assertJson(['message' => 'Login successfull']);
    }

    /**
     * @test for
     * Unsuccessfull Login
     */
    public function test_UnSuccessfulLogin()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])->json(
            'POST',
            '/api/login',
            [
                "email" => "xyez@gmail.com",
                "password" => "123456"
            ]
        );
        $response->assertStatus(401)->assertJson(['message' => 'we can not find the user with that e-mail address You need to register first']);
    }

    /**
     * @test for
     * Successfull Logout
     */
    public function test_SuccessfulLogout()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNjUwMDMwODk3LCJleHAiOjE2NTAwMzQ0OTcsIm5iZiI6MTY1MDAzMDg5NywianRpIjoidUd4bm93Q3FyQTFCU0FyTSIsInN1YiI6IjIiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.ZGwsV7npZXWXbX-IBxDGJN0mphS8R42Gp0v4XRKs3sc'
        ])->json('POST', '/api/logout');
        $response->assertStatus(201)->assertJson(['message' => 'User successfully signed out']);
    }

    /**
     * @test for
     * Successfull forgotpassword
     */
    public function test_SuccessfulForgotPassword()
    { {
            $response = $this->withHeaders([
                'Content-Type' => 'Application/json',
            ])->json('POST', '/api/forgotpassword', [
                "email" => "manjunathhalli629@gmail.com"
            ]);

            $response->assertStatus(200)->assertJson(['message' => 'we have mailed your password reset link to respective E-mail']);
        }
    }

    /**
     * @test for
     * UnSuccessfull forgotpassword
     */
    public function test_UnSuccessfulForgotPassword()
    { {
            $response = $this->withHeaders([
                'Content-Type' => 'Application/json',
            ])->json('POST', '/api/forgotpassword', [
                "email" => "xyzz@gmail.com"
            ]);

            $response->assertStatus(404)->assertJson(['message' => 'we can not find a user with that email address']);
        }
    }

    /**
     * @test for
     * Successfull resetpassword
     */
    public function test_SuccessfulResetPassword()
    { {
            $response = $this->withHeaders([
                'Content-Type' => 'Application/json',
                'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNjUwMDMxMTIzLCJleHAiOjE2NTAwMzQ3MjMsIm5iZiI6MTY1MDAzMTEyMywianRpIjoieFVGclc1RDVqcFcyUUZSNCIsInN1YiI6IjIiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.sCq-hdGdst48xUyIe14aXKe03hLQxyMX6d_KUU8MWeI'
            ])->json('POST', '/api/resetpassword', [
                "new_password" => "445566",
                "confirm_password" => "445566"
            ]);

            $response->assertStatus(201)->assertJson(['message' => 'Password reset successfull!']);
        }
    }

    /**
     * @test for
     * UnSuccessfull resetpassword
     */
    public function test_UnSuccessfulResetPassword()
    { {
            $response = $this->withHeaders([
                'Content-Type' => 'Application/json',
                'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNjUwMDMxMTIzLCJleHAiOjE2NTAwMzQ3MjMsIm5iZiI6MTY1MDAzMTEyMywianRpIjoieFVGclc1RDVqcFcyUUZSNCIsInN1YiI6IjIiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.sCq-hdGdst48xUyIe14aXKe03hLQxyMX6d_KUU8MWeI'
            ])->json('POST', '/api/resetpassword', [
                "new_password" => "manju23",
                "confirm_password" => "manju23"
            ]);

            $response->assertStatus(400)->assertJson(['message' => 'we cannot find the user with that e-mail address']);
        }
    }
}
