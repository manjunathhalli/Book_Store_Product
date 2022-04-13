<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\BookStoreException;
use App\Models\Book;
use App\Models\User;
use App\Models\WishList;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class WishlistController extends Controller
{

    /**
     *  @OA\Post(
     *   path="/api/addBookToWishlistByBookId",
     *   summary="Add the book to wishlist",
     *   description=" Add book to wishlist",
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
     *   @OA\Response(response=201, description="Book added to wishlist Success"),
     *   @OA\Response(response=404, description="Invalid authorization token"),
     *   security={
     *       {"Bearer": {}}
     *     }
     * )
     * 
     * This Function will take book id as input and 
     * it will ad that book to wishlist
     * as per user's requirement
     */

    public function addBookToWishlistByBookId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'book_id' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        try {
            $currentUser = JWTAuth::parseToken()->authenticate();
            $wishlist = new WishList();
            $user = new User();
            $book = new Book();
            $userId = $user->userVerification($currentUser->id);

            if (count($userId) == 0) {
                return response()->json(['message' => 'NOT AN USER'], 404);
            }
            if ($currentUser) {
                $book_id = $request->input('book_id');
                $book_existance = $book->findingBook($book_id);

                if (!$book_existance) {
                    return response()->json(['message' => 'Book not Found in The BOOKSTORE'], 404);
                }
                $books = Book::find($book_id);
                if ($books->quantity == 0) {
                    return response()->json(['message' => 'OUT OF STOCK FROM THE BOOKSTORE '], 404);
                }
                $book_wishlist = $wishlist->wishlistBook($book_id, $currentUser->id);

                if ($book_wishlist) {
                    return response()->json(['message' => 'Book already added to the Wishlist'], 404);
                }

                $wishlist->book_id = $request->get('book_id');

                if ($currentUser->wishlists()->save($wishlist)) {
                    return response()->json(['message' => 'Book added to wishlist Success'], 201);
                }
                Cache::remember('wishlists', 3600, function () {
                    return DB::table('wishlists')->get();
                });
            } else {
                Log::error('Invalid User');
                throw new BookStoreException("Invalid authorization token", 404);
            }
        } catch (BookStoreException $exception) {
            return $exception->message();
        }
    }


    /**
     *  @OA\Post(
     *   path="/api/deleteBookByWishlistId",
     *   summary="Delete the book to wishlist",
     *   description=" Delete book to wishlist",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *             required={"id"},
     *               @OA\Property(property="id", type="integer"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Book deleted Sucessfully from wishlist"),
     *   @OA\Response(response=404, description="Invalid authorization token"),
     *   security={
     *       {"Bearer": {}}
     *     }
     * )
     * 
     * This Function will take wishlist Id as input and
     *  will perform the delete operation
     * for the perticular wishlist which the user want to delete from wishlist
     */

    public function deleteBookByWishlistId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        try {
            $id = $request->input('id');
            $currentUser = JWTAuth::parseToken()->authenticate();
            $user = new User();
            $userId = $user->userVerification($currentUser->id);
            if (count($userId) == 0) {
                return response()->json(['message' => 'NOT AN USER'], 404);
            }

            if (!$currentUser) {
                Log::error('Invalid User');
                throw new BookStoreException("Invalid authorization token", 404);
            }

            $book = $currentUser->wishlists()->find($id);
            if (!$book) {
                Log::error('Book Not Found', ['id' => $request->id]);
                return response()->json(['message' => 'Book not Found in The wishlist'], 404);
            }

            if ($book->delete()) {
                Log::info('book deleted', ['user_id' => $currentUser, 'book_id' => $request->id]);
                Cache::remember('wishlists', 3600, function () {
                    return DB::table('wishlists')->get();
                });
                return response()->json(['message' => 'Book deleted Sucessfully from wishlist'], 201);
            }
        } catch (BookStoreException $exception) {
            return $exception->message();
        }
    }


    /**
     *  @OA\get(
     *   path="/api/getAllBooksInWishlist",
     *   summary="Get All Books Present in wishlist",
     *   description="DisplayAll Books Present in wishlist",
     *   @OA\RequestBody(
     *      ),
     *   @OA\Response(response=201, description="'Books Present in wishlist :"),
     *   @OA\Response(response=404, description="Invalid Authorization token"),
     *   security={
     *       {"Bearer": {}}
     *     }
     * )
     * 
     * This method will execute and return for the current user which books are added
     * in the wishlist and return all data
     */

    public function getAllBooksInWishlist()
    {
        try {
            $currentUser = JWTAuth::parseToken()->authenticate();
            $wishlist = new WishList();
            $user = new User();
            $userId = $user->userVerification($currentUser->id);
            if (count($userId) == 0) {
                return response()->json(['message' => 'NOT AN USER'], 404);
            }
            if ($currentUser) {
                $books = $wishlist->getAllWishlistBooks($currentUser->id);

                if ($books == []) {
                    Log::error('Book Not Found');
                    return response()->json(['message' => 'Books not found'], 404);
                }
                Log::info('All book Presnet in wishlist are fetched');
                return response()->json([
                    'message' => 'Books Present in wishlist :',
                    'wishlist' => $books,
                ], 201);
            } else {
                Log::error('Invalid User');
                throw new BookStoreException("Invalid authorization token", 404);
            }
        } catch (BookStoreException $exception) {
            return $exception->message();
        }
    }
}
