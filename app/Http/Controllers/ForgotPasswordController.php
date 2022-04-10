<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Mailer\SendEmailRequest;
use App\Notifications\PasswordResetRequest;
use App\Exceptions\BookStoreException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class ForgotPasswordController extends Controller
{
   /**
     *  @OA\Post(
     *   path="/api/forgotPassword",
     *   summary="forgot password",
     *   description="forgot user password",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"email"},
     *               @OA\Property(property="email", type="string"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=200, description="password reset link to respective E-mail"),
     *   @OA\Response(response=404, description="we can not find a user with that email address"),
     *   security={
     *       {"Bearer": {}}
     *     }
     * )
     * This API Takes the request which is the email id and validates it and check where that email id
     * is present in DB or not if it is not,it returns failure with the appropriate response code and
     * checks for password reset model once the email is valid and by creating an object of the
     * sendEmail function which is there in App\Http\Requests\SendEmailRequest and calling the function
     * by passing args and successfully sending the password reset link to the specified email id.
     *
     * @return success reponse about reset link.
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        try {
            $userObject = new User();
            $user = $userObject->userEmailValidation($request->email);
            if (!$user) {
                Log::error('Email not found.', ['id' => $request->email]);
                throw new BookStoreException("we can not find a user with that email address", 404);
            }
            $token = Auth::fromUser($user);
            if ($user) {
                // $sendEmail = new SendEmailRequest();
                // $sendEmail->sendEmail($user->email, $token);
                $delay = now()->addSeconds(5);
                $user->notify((new PasswordResetRequest($user->email, $token))->delay($delay));
            }

            Log::info('Forgot PassWord Link is here : ' . 'Email Id :' . $request->email);
            return response()->json([
                'status' => 200,
                'message' => 'password reset link to respective E-mail'
            ], 200);
        } catch (BookStoreException $exception) {
            return $exception->message();
        }
    }

   /**
     *  @OA\Post(
     *   path="/api/resetPassword",
     *   summary="reset password",
     *   description="reset user password",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"email"},
     *               @OA\Property(property="new_password", type="string"),
     *               @OA\Property(property="confirm_password", type="string"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Password reset successfull!"),
     *   @OA\Response(response=404, description="Can not find a user with this email address"),
     *   security={
     *       {"Bearer": {}}
     *     }
     * )
     * This API Takes the request which has new password and confirm password and validates both of them
     * if validation fails returns failure resonse and if it passes it checks with DB whether the token
     * is there or not if not returns a failure response and checks the user email also if everything is
     * good resets the password successfully.
     */
    public function resetPassword(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'new_password' => 'min:6|required|',
            'confirm_password' => 'required|same:new_password'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => "Password doesn't match"
            ], 400);
        }
        try {
            $currentUser = Auth::user();
            $userObject = new User();
            $user = $userObject->userEmailValidation($currentUser->email);

            if (!$user) {
                Log::error('Email not found.', ['id' => $request->email]);
                throw new BookStoreException("Can not find a user with this email address", 404);
            } else {
                $user->password = bcrypt($request->new_password);
                $user->save();

                Log::info('Reset Password Successful : ' . 'Email Id :' . $request->email);
                return response()->json([
                    'status' => 201,
                    'message' => 'Password reset successfull!'
                ], 201);
            }
        } catch (BookStoreException $exception) {
            return $exception->message();
        }
    }
}
