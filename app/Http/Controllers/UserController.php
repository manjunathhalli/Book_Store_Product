<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exceptions\BookStoreException;

class UserController extends Controller
{
    /**
     * @OA\Post(
     *   path="/api/register",
     *   summary="register",
     *   description="register the user for login",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"role","first_name","last_name","phone_no","email", "password", "confirm_password"},
     *               @OA\Property(property="role", type="string"),
     *               @OA\Property(property="first_name", type="string"),
     *               @OA\Property(property="last_name", type="string"),
     *               @OA\Property(property="phone_no", type="string"),
     *               @OA\Property(property="email", type="string"),
     *               @OA\Property(property="password", type="password"),
     *               @OA\Property(property="confirm_password", type="password")
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="User Successfully Registered"),
     *   @OA\Response(response=401, description="The email has already been taken")
     * )  
     */
    /**
     * Register a User.
     * path="api/register",
     * description="register the user for login",
     * required=("role","first_name","last_name","phone_no" ,"email", "password", "confirm_password")
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'role' => 'required|string|between:2,10',
                'first_name' => 'required|string|between:2,50',
                'last_name' => 'required|string|between:2,50',
                'phone_no' => 'required|string|min:10',
                'email' => 'required|string|email|max:100',
                'password' => 'required|string|min:6',
                'confirm_password' => 'required|same:password',
            ]);
            $userArray = array(
                'role' => $request->role,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone_no' => $request->phone_no,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            );

            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }

            $userObject = new User();
            $user = $userObject->userEmailValidation($request->email);
            if ($user) {
                throw new BookStoreException("The email has already been taken", 401);
            }

            $userObject->saveUserDetails($userArray);
            Log::info('Registered user Email : ' . 'Email Id :' . $request->email);
            Cache::remember('users', 3600, function () {
                return DB::table('users')->get();
            });
            return response()->json([
                'status' => 201,
                'message' => 'User Successfully Registered',
            ], 201);
        } catch (BookStoreException $exception) {
            Log::error('Invalid User');
            return $exception->message();
        }
    }

    /**
     * @OA\Post(
     *   path="/api/login",
     *   summary="user login",
     *   description="the user login to the application",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"email", "password", "confirm_password"},
     *               @OA\Property(property="email", type="string"),
     *               @OA\Property(property="password", type="password"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=200, description="Successfull Login"),
     *   @OA\Response(response=401, description="Can Not Find User with this email Id")
     * ) 
     * Takes the POST request and user credentials checks if it correct,
     * if so, returns JWT access token.
     * 
     * @return \Illuminate\Http\JsonResponse
     */ 
    
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            Cache::remember('users', 3600, function () {
                return User::all();
            });

            $userObject = new User();
            $user = $userObject->userEmailValidation($request->email);
            if (!$user) {
                Log::error('user failed to login.', ['id' => $request->email]);
                throw new BookStoreException("Can Not Find User with this email Id ", 401);
            }
            if (!$token = auth()->attempt($validator->validated())) {
                throw new BookStoreException("Invalid Credtials", 401);
            }

            Log::info('Successfully Login: ' . 'Email Id:' . $request->email);
            return response()->json([
                'status' => 200,
                'access_token' => $token,
                'message' => 'Successfull Login'
            ], 200);
        } catch (BookStoreException $exception) {
            Log::error('Invalid user');
            return $exception->message();
        }
    }

    /**
     * @OA\Post(
     *   path="/api/logout",
     *   summary="logout",
     *   description=" user logout from application ",
     *  @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"token",},
     *               @OA\Property(property="token", type="string"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=200, description="successfully Logged out"),
     * )
     */
    public function logout()
    {
        auth()->logout();

        return response()->json([
            'status' => 200,
            'message' => 'Successfully Logged Out'
        ], 200);
    }
}
