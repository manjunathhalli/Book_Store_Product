<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Cart;
use App\Models\User;
use App\Exceptions\BookStoreException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class CartController extends Controller
{
    /**
     *  @OA\Post(
     *   path="/api/addBookToCartByBookId",
     *   summary="addBook to cart using book Id ",
     *   description="add book to cart from user  only",
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
     *   @OA\Response(response=201, description="Book Added to Cart Sucessfully"),
     *   @OA\Response(response=404, description="NOT AN USER"),
     *   @OA\Response(response=401, description="Book already added to the cart"),
     *   security={
     *       {"Bearer": {}}
     *     }
     * )
     * 
     * Function adding a new book to the CART and mysql database and user bearer token
     * must be passed because only USER can add or remove books .
     */

    public function addBookToCartByBookId(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'book_id' => 'required|integer',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->tojson(), 400);
        }

        try {
            $currentUser = JWTAuth::parseToken()->authenticate();
            $cart = new Cart();
            $book = new Book();
            $user = new User();

            $userId = $user->userVerification($currentUser->id);

            if (count($userId) == 0) {
                return response()->json(['message' => 'NOT AN USER'], 404);
            }

            if ($currentUser) {
                $book_id = $request->input('book_id');
                $book_existance = $book->findingbook($book_id);

                if (!$book_existance) {

                    return response()->json([
                        'message' => 'Book not found',
                        'status' => 404
                    ], 404);
                }

                $books = $book->findingBook($book_id);
                if ($books->quantity == 0) {
                    return response()->json([
                        'status' => 404,
                        'message' => ' OUT OF STOCK '
                    ], 404);
                }
                $book_cart = $cart->bookCart($book_id, $currentUser->id);

                if ($book_cart) {

                    return response()->json([
                        'status' => 'Book already added to the cart'
                    ], 401);
                }
                $cart->book_id = $request->get('book_id');

                if ($currentUser->carts()->save($cart)) {
                    Cache::remember('carts', 3600, function () {
                        return DB::table('carts')->get();
                    });

                    return response()->json([
                        'message' => 'Book Added to Cart Sucessfully'
                    ], 201);
                }
                return response()->json([
                    'message ' => 'Book Cannot Added to Cart '
                ], 405);
            } else {
                Log::error('Invalid User');
                throw new BookStoreException("Invalid Authorization Token", 404);
            }
        } catch (BookStoreException $exception) {
            return $exception->message();
        }
    }


    /**
     *  @OA\Post(
     *   path="/api/deleteBookByCartId",
     *   summary="Delete Book from cart using Cart Id ",
     *   description="Delete book from cart from user  only",
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
     *   @OA\Response(response=201, description="Book deleted succesfully from Cart"),
     *   @OA\Response(response=404, description="Invalid authorization token"),
     *   security={
     *       {"Bearer": {}}
     *     }
     * )
     * 
     * Function Deleting a book from the CART and mysql database and user bearer token
     * must be passed because only USER can delete books .
     */

    public function deleteBookByCartId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->tojson(), 400);
        }

        try {
            $id = $request->input('id');
            $currentUser = JWTAuth::parseToken()->authenticate();
            $user = new User();
            $userId = $user->userVerification($currentUser->id);
            if (count($userId) == 0) {
                return response()->json([
                    'status' => 404,
                    'message' => 'NOT AN USER'
                ], 404);
            }

            if (!$currentUser) {
                Log::error("Invalid User");
                throw new BookStoreException("Invalid authorization token", 404);
            }

            $book = $currentUser->carts()->find($id);
            if (!$book) {
                Log::error('Book not found', ['id' => $request->id]);
                return response()->json([
                    'message' => 'Book not found in CART'
                ], 404);
            }

            if ($book->delete()) {
                Log::info('book deleted', ['user_id' => $currentUser, 'book_id' => $request->id]);
                Cache::forget('carts');
                return response()->json([
                    'message' => 'Book deleted succesfully from Cart'
                ], 201);
            }
        } catch (BookStoreException $exception) {
            return $exception->message();
        }
    }


    /**
     *  @OA\get(
     *   path="/api/getAllBooksInCart",
     *   summary="Display All Books from cart user Id",
     *   description="Display books from cart from user  only",
     *   @OA\RequestBody(
     *      ),
     *   @OA\Response(response=201, description="All Books Present in Cart"),
     *   @OA\Response(response=404, description="Invalid Authorization token"),
     *   security={
     *       {"Bearer": {}}
     *     }
     * )
     * 
     * Function Display All books from the CART and mysql database and user bearer token
     * must be passed because only USER can display all books .
     */

    public function getAllBooksInCart()
    {
        try {
            $currentUser = JWTAuth::parseToken()->authenticate();
            $user = new User();
            $userId = $user->userVerification($currentUser->id);
            if (count($userId) == 0) {
                return response()->json([
                    'message' => 'NOT AN USER'
                ], 404);
            }
            if ($currentUser) {
                $books = Cart::leftJoin('books', 'carts.book_id', '=', 'books.id')
                    ->select('books.id', 'books.name', 'books.author', 'books.description', 'books.Price', 'carts.book_quantity')
                    ->where('carts.user_id', '=', $currentUser->id)
                    ->get();

                if ($books == []) {
                    Log::error('Book not found');
                    return response()->json([
                        'message' => 'Books not found'
                    ], 404);
                }

                Log::info('All Book Present in Cart are Fetched');
                return response()->json([
                    'message' => 'All Books Present in Cart',
                    'Cart' => $books
                ], 201);
            } else {
                Log::error('Invalid User');
                throw new BookStoreException("Invalid Authorization token", 404);
            }
        } catch (BookStoreException $exception) {
            return $exception->message();
        }
    }
}
