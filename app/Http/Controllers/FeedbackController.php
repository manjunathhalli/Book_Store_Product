<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\BookStoreException;
use Illuminate\Support\Facades\Validator;
use App\Models\Feedback;
use App\Models\Book;
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
     *             required={"book_id","feedback","rating"},
     *               @OA\Property(property="book_id", type="integer"),
     *               @OA\Property(property="feedback", type="string"),
     *               @OA\Property(property="rating", type="integer"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Thanks for providing us with detailed feedback about our service."),
     *   @OA\Response(response=401, description="Invalid authorization token"),
     *   security={
     *       {"Bearer": {}}
     *     }
     * )
     * Function takes input as feedback rating and book id from user and 
     * store in the feedbacks database. 
     */

    public function feedback(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'book_id' => 'required|integer',
                'feedback' => 'required|string|between:4,1000',
                'rating' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $currentUser = JWTAuth::parseToken()->authenticate();
            $book = new Book();
            if (!$currentUser) {
                throw new BookStoreException("Invalid authorization token", 401);
            } else {
                $feedback = new Feedback();
                $book_id = $request->input('book_id');
                $book->findingbook($book_id);
                $feedback->saveingFeedback($request, $currentUser)->save();
            }

            Log::info('feedback created', ['user_id' => $feedback->user_id]);
            return response()->json([
                'status' => 201,
                'message' => 'Thanks for providing us with detailed feedback about our service.',
                'Average Rating ' => $feedback->avgRating($book_id)
            ], 201);
        } catch (BookStoreException $exception) {
            Log::error('Invalid User');
            return $exception->message();
        }
    }

    /**
     *  @OA\Post(
     *   path="/api/getAverageRatingByBookId",
     *   summary="average rating of book",
     *   description="average rating of book",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *             required={"book_id"},
     *               @OA\Property(property="book_id", type="integer"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Average rating of Book ."),
     *   @OA\Response(response=401, description="Invalid authorization token"),
     *   security={
     *       {"Bearer": {}}
     *     }
     * )
     * Function takes input as book id from user and 
     * return the avearage rating perticular book from feedbacks database. 
     */

    public function getAverageRatingByBookId(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'book_id' => 'required|integer'
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            $currentUser = JWTAuth::parseToken()->authenticate();
            $book = new Book();
            if (!$currentUser) {
                throw new BookStoreException("Invalid authorization token", 401);
            } else {
                $feedback = new Feedback();
                $book_id = $request->input('book_id');
                $book_existance = $book->findingBook($book_id);
            }
            if (!$book_existance) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Book Not Found'
                ], 404);
            }
            $bookDetails = $book->findingBook($book_id);
            return response()->json([
                'message' => 'Average rating of Book ' . $book_id .  ':',
                'Average Rating' => $feedback->avgRating($book_id),
                'book Detail' => $bookDetails
            ], 201);
        } catch (BookStoreException $exception) {
            Log::error('Invalid User');
            return $exception->message();
        }
    }
}
