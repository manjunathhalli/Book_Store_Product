<?php

namespace App\Http\Controllers;

use App\Exceptions\BookStoreException;
use Illuminate\Http\Request;
use App\Models\Book;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class BookController extends Controller
{

    /**
     *  @OA\Post(
     *   path="/api/addingBook",
     *   summary="addBook to s3 bucket ",
     *   description="addbook from Admin only",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *             required={"name","description","author","image","price", "quantity"},
     *               @OA\Property(property="name", type="string"),
     *               @OA\Property(property="description", type="string"),
     *               @OA\Property(property="author", type="string"),
     *               @OA\Property(property="image", type="file"),
     *               @OA\Property(property="price", type="integer"),
     *               @OA\Property(property="quantity", type="integer"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Book created successfully"),
     *   @OA\Response(response=404, description="Invalid authorization toke"),
     *   @OA\Response(response=401, description="Book is already exist in there"),
     *   security={
     *       {"Bearer": {}}
     *     }
     * )
     * Function adding a new book with proper name, description, author, image
     * image will be stored in aws S3 bucket and bucket will generate
     * an url and that urlwill be stored in mysql database and admin bearer token
     * must be passed because only ADMIN can add or remove books .
     */

    public function addingBook(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'description' => 'required|string|between:5,1000',
            'author' => 'required|string|between:5,300',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'Price' => 'required',
            'quantity' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        try {
            $currentUser = JWTAuth::parseToken()->authenticate();
            if ($currentUser) {
                $book = new Book();
                $adminId = $book->adminOrUserVerification($currentUser->id);
                // return $adminId;
                if (count($adminId) == 0) {
                    throw new BookStoreException("NOT AN ADMIN", 404);
                }

                $bookDetails = Book::where('name', $request->name)->first();
                if ($bookDetails) {
                    throw new BookStoreException("Book is already exist in there", 401);
                }
                //$imageName = time() . '.' . $request->image->extension();
                $path = Storage::disk('s3')->put('bookimage2', $request->image);
                $url = env('AWS_URL') . $path;
                $book->name = $request->input('name');
                $book->description = $request->input('description');
                $book->author = $request->input('author');
                $book->image = $url;
                $book->Price = $request->input('Price');
                $book->quantity = $request->input('quantity');
                $book->user_id = $currentUser->id;
                $book->save();
            } else {
                Log::error('Invalid User');
                throw new BookStoreException("Invalid authorization token", 404);
            }

            Cache::remember('books', 3600, function () {
                return DB::table('books')->get();
            });

            Log::info('book created', ['admin_id' => $book->user_id]);

            return response()->json([
                'message' => 'Book created successfully'
            ], 201);
        } catch (BookStoreException $exception) {
            return $exception->message();
        }
    }


    /**
     *  @OA\Post(
     *   path="/api/updateBookById",
     *   summary="updateBook in s3 bucket ",
     *   description="updateBook from Admin only",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *             required={"id","name","description","author","image","price"},
     *               @OA\Property(property="id", type="integer"),
     *               @OA\Property(property="name", type="string"),
     *               @OA\Property(property="description", type="string"),
     *               @OA\Property(property="author", type="string"),
     *               @OA\Property(property="image", type="file"),
     *               @OA\Property(property="price", type="decimal"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Book updated Sucessfully"),
     *   @OA\Response(response=404, description="Invalid authorization token"),
     *   security={
     *       {"Bearer": {}}
     *     }
     * )
     * Function Update by Id ,the existing book with  proper name, description, author, image
     * image will be stored in aws S3 bucket and bucket will generate
     * a url and that urlwill be stored in mysql database and admin bearer token
     * must be passed because only admin can add or remove books .
     */

    public function updateBookById(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'name' => 'required|string|between:2,100',
            'description' => 'required|string|between:5,1000',
            'author' => 'required|string|between:5,300',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'Price' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        try {

            $currentUser = JWTAuth::parseToken()->authenticate();
            if (!$currentUser) {
                Log::error('Invalid User');
                throw new BookStoreException("Invalid authorization token", 404);
            }
            $book = new Book();
            $adminId = $book->adminOrUserVerification($currentUser->id);
            if (count($adminId) == 0) {
                return response()->json(['message' => 'NOT AN ADMIN'], 404);
            }

            $bookDetails = $book->findingBook($request->id);
            if (!$book) {
                throw new BookStoreException("Book not Found", 404);
            }

            if ($request->image) {
                $path = str_replace(env('AWS_URL'), '', $bookDetails->image);

                if (Storage::disk('s3')->exists($path)) {
                    Storage::disk('s3')->delete($path);
                }
                $path = Storage::disk('s3')->put('bookimage2', $request->image);
                $pathurl = env('AWS_URL') . $path;
                $bookDetails->image = $pathurl;
            }

            $bookDetails->fill($request->except('image'));
            Cache::forget('books');

            if ($bookDetails->save()) {
                Log::info('book updated', ['admin_id' => $bookDetails->user_id]);
                return response()->json(['message' => 'Book updated Sucessfully'], 201);
            }
        } catch (BookStoreException $exception) {
            return $exception->message();
        }
    }


    /**
     *  @OA\Post(
     *   path="/api/addQuantityToExistBook",
     *   summary="add quantity to existing book ",
     *   description="add quantity to book",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *             required={"id","quantity"},
     *               @OA\Property(property="id", type="integer"),
     *               @OA\Property(property="quantity", type="integer"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Book Quantity updated Successfully"),
     *   @OA\Response(response=404, description="Couldnot found a book with that given id"),
     *   security={
     *       {"Bearer": {}}
     *     }
     * )
     * Function takes perticular Bookid and a Quantity value 
     * and then take input valid Authentication token as an input and fetch 
     * the book stock in the book store
     * and performs add quantity operation on that perticular Bookid.
     */

    public function addQuantityToExistBook(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'quantity' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        try {
            $currentUser = JWTAuth::parseToken()->authenticate();
            if (!$currentUser) {
                Log::error('Invalid User');
                throw new BookStoreException("Invalid authorization token", 404);
            }
            $book = new Book();
            $adminId = $book->adminOrUserVerification($currentUser->id);
            if (count($adminId) == 0) {
                return response()->json(['message' => 'NOT AN ADMIN'], 404);
            }

            $bookDetails = $book->findingBook($request->id);
            if (!$bookDetails) {
                throw new BookStoreException("Couldnot found a book with that given id", 404);
            }

            $bookDetails->quantity += $request->quantity; //quantity =quantity +$request_quantity
            $bookDetails->save();
            Cache::forget('books');

            return response()->json([
                'status' => 201,
                'message' => 'Book Quantity updated Successfully'
            ], 201);
        } catch (BookStoreException $exception) {
            return $exception->message();
        }
    }


    /**
     *  @OA\Post(
     *   path="/api/deleteBookById",
     *   summary="delete in s3 bucket ",
     *   description="deleted Book from s3 bucket and databse ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *             required={"id"},
     *               @OA\Property(property="id", type="integer"),
     *               
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Book Deleted Sucessfully"),
     *   @OA\Response(response=404, description="Invalid authorization token"),
     *    @OA\Response(response=401, description="NOT AN ADMIN"),                                
     *   security={
     *       {"Bearer": {}}
     *     }
     * )
     * Function takes perticular Bookid and a valid Authentication token as an input
     * and fetch the book in the bookstore database and performs delete operation on
     * on that perticular Bookid
     */

    public function deleteBookById(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        try {
            $currentUser = JWTAuth::parseToken()->authenticate();
            if (!$currentUser) {
                Log::error('Invalid User');
                throw new BookStoreException("Invalid authorization token", 404);
            }
            $book = new Book();
            $adminId = $book->adminOrUserVerification($currentUser->id);
            if (count($adminId) == 0) {
                return response()->json(['message' => 'NOT AN ADMIN'], 401);
            }

            $bookDetails = $book->findingBook($request->id);
            if (!$bookDetails) {
                return response()->json(['message' => 'Book not Found'], 404);
            }

            $path = str_replace(env('AWS_URL'), '', $bookDetails->image);
            if (Storage::disk('s3')->exists($path)) {
                Storage::disk('s3')->delete($path);
                if ($bookDetails->delete()) {
                    Log::info('book deleted', ['user_id' => $currentUser, 'book_id' => $request->id]);
                    Cache::forget('books');
                    return response()->json(['message' => 'Book Deleted Sucessfully'], 201);
                }
            }
            return response()->json(['message' => 'File image was not deleted'], 402);
        } catch (BookStoreException $exception) {
            return $exception->message();
        }
    }


    /**
     *  @OA\get(
     *   path="/api/displayAllBooks",
     *   summary="display all books from book store ",
     *   description="get all books from bookstore ",
     *   @OA\RequestBody(
     *        ),
     *   @OA\Response(response=201, description="Display All books are"),
     *   @OA\Response(response=404, description="Books are not there"),                               
     *   security={
     *       {"Bearer": {}}
     *     }
     * )
     * Function returns (display) all the added books in the store .
     */

    public function displayAllBooks()
    {
        try {
            $book = Book::paginate(2);

            if ($book == []) {
                throw new BookStoreException("Books are not there", 404);
            }
            return response()->json([
                'message' => 'Display All books are :',
                'books' => $book

            ], 201);
        } catch (BookStoreException $exception) {
            return $exception->message();
        }
    }


    /**
     *  @OA\get(
     *   path="/api/sortPriceLowToHigh",
     *   summary="Assending order Books based on Price  ",
     *   description="display all books in assending order based on price",
     *   @OA\RequestBody(
     *        ),
     *   @OA\Response(response=201, description="Sorting Books are Low to high"),
     *   @OA\Response(response=404, description="Books not found"),                               
     *   security={
     *       {"Bearer": {}}
     *     }
     * )
     * Function returns (display) all the books in the Assending order .
     */

    public function sortPriceLowToHigh()
    {
        $currentUser = JWTAuth::parseToken()->authenticate();
        $book = new Book();
        if ($currentUser) {
            $bookDetails = $book->ascendingOrder();
        }
        if ($bookDetails == []) {
            return response()->json(['message' => 'Books not found'], 404);
        }
        return response()->json([
            'message' => 'Sorting Books are Low to high',
            'books' => $bookDetails
        ], 201);
    }


    /**
     *  @OA\get(
     *   path="/api/sortPriceHighToLow",
     *   summary="Descending order Books based on Price  ",
     *   description="display all books in Descending order based on price",
     *   @OA\RequestBody(
     *        ),
     *   @OA\Response(response=201, description="Sorting Books are High to low"),
     *   @OA\Response(response=404, description="Books not found"),                               
     *   security={
     *       {"Bearer": {}}
     *     }
     * )
     * Function returns (display) all the books in the Descending order .
     */

    public function sortPriceHighToLow()
    {
        $currentUser = JWTAuth::parseToken()->authenticate();
        $book = new Book();
        if ($currentUser) {
            $bookDetails = $book->descendingOrder();
        }
        if ($bookDetails == []) {
            return response()->json(['message' => 'Books not found'], 404);
        }
        return response()->json([
            'message' => 'Sorting Books are High to Low',
            'books' => $bookDetails
        ], 201);
    }

    /**
     *  @OA\Post(
     *   path="/api/searchBookByKeyword",
     *   summary="search Book Enter Search Key ",
     *   description="Searching Book using Search key in books table ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *             required={"search"},
     *               @OA\Property(property="search", type="string"),
     *               
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Search done Successfully"),
     *   @OA\Response(response=404, description="No Book Found For This Search Key"),                             
     *   security={
     *       {"Bearer": {}}
     *     }
     * )
     * Function takes search key and a valid Authentication token as an input
     * and fetch the book in the bookstore database and performs shows books 
     * from the books list
     */

    public function searchBookByKeyword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        try {
            $searchKey = $request->input('search');
            $currentUser = JWTAuth::parseToken()->authenticate();

            if ($currentUser) {
                $userbooks = Book::leftJoin('carts', 'carts.book_id', '=', 'books.id')
                    ->select('books.id', 'books.name', 'books.description', 'books.author', 'books.image', 'books.Price', 'books.quantity')
                    ->Where('books.name', 'like', '%' . $searchKey . '%')
                    ->orWhere('books.author', 'like', '%' . $searchKey . '%')
                    ->get();

                if ($userbooks == '[]') {
                    Log::error('No Book Found');
                    throw new BookStoreException("No Book Found For This Search Key ", 404);
                }
                Log::info('Search is Successfull');
                return response()->json([
                    'message' => 'Search done Successfully',
                    'books' => $userbooks
                ], 201);
            }
        } catch (BookStoreException $exception) {
            return $exception->message();
        }
    }
}
