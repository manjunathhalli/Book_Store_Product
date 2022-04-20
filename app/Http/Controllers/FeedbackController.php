<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\BookStoreException;
use Illuminate\Support\Facades\Validator;
use App\Models\Feedback;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class FeedbackController extends Controller
{
    /**
     *  @OA\Post(
     *   path="/api/feedback",
     *   summary="user feedback",
     *   description="feedback from user",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *             required={"feedback"},
     *               @OA\Property(property="feedback", type="string"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="feedback submited successfully"),
     *   @OA\Response(response=401, description="Invalid authorization token"),
     *   security={
     *       {"Bearer": {}}
     *     }
     * )
     * Function takes input as feedback from user and 
     * store in the feedbacks database. 
     */

    public function feedback(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'feedback' => 'required|string|between:4,1000',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $currentUser = JWTAuth::parseToken()->authenticate();
            if (!$currentUser) {
                throw new BookStoreException("Invalid authorization token", 401);
            } else {
                $feedback = new Feedback();
                $feedback->feedback = $request->input('feedback');
                $feedback->user_id = $currentUser->id;
                $feedback->save();
            }

            Log::info('feedback created', ['user_id' => $feedback->user_id]);
            return response()->json([
                'status' => 201,
                'message' => 'feedback submited successfully'
            ], 201);
        } catch (BookStoreException $exception) {
            Log::error('Invalid User');
            return $exception->message();
        }
    }
}
