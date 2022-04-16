<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\BookStoreException;
use App\Models\Address;
use App\Models\Book;
use App\Models\Order;
use App\Models\User;
use App\Notifications\SendOrderDetails;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class OrderController extends Controller
{

    /**
     *  @OA\Post(
     *   path="/api/placeOrder",
     *   summary="place Order",
     *   description="place order to address",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *             required={"address_id","name","quantity"},
     *               @OA\Property(property="address_id", type="integer"),
     *               @OA\Property(property="name", type="string"),
     *               @OA\Property(property="quantity", type="integer"),   
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description=" Order Successfully Placed and Mail also sent to the user with all details"),
     *   @OA\Response(response=401, description="We Do not have this book in the store"),
     *   security={
     *       {"Bearer": {}}
     *     }
     * )
     * This method will take input address_id,name,quantity from user
     * and order placed,details sent to the respective user
     */

    public function placeOrder(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'address_id' => 'required',
            'name' => 'required',
            'quantity' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if ($user) {
                $book = new Book();
                $address = new Address();
                $bookDetail = $book->getBookDetails($request->input('name'));
                if ($bookDetail == '') {
                    Log::error('Book is not available');
                    throw new BookStoreException("We Do not have this book in the store", 401);
                }

                if ($bookDetail['quantity'] < $request->input('quantity')) {
                    Log::error('Book stock is not available');
                    throw new BookStoreException("This much stock is unavailable for the book", 401);
                }

                //getting addressID
                $getAddress = $address->addressExist($request->input('address_id'));
                if (!$getAddress) {
                    throw new BookStoreException("This address id not available", 401);
                }

                //calculate total price
                $total_price = $request->input('quantity') * $bookDetail['Price'];

                $order = Order::create([
                    'user_id' => $user->id,
                    'book_id' => $bookDetail['id'],
                    'address_id' => $getAddress['id'],
                    'order_id' => $this->UniqueOrderId(),
                ]);

                $userId = User::where('id', $user->id)->first();

                $delay = now()->addSeconds(5);
                $userId->notify((new SendOrderDetails($order->order_id, $bookDetail['name'], $bookDetail['author'], $request->input('quantity'), $total_price))->delay($delay));

                $bookDetail['quantity'] -= $request->quantity;
                $bookDetail->save();
                return response()->json([
                    'message1' => 'Order Successfully Placed',
                    'OrderId' => $order->order_id,
                    'Quantity' => $request->input('quantity'),
                    'Total_Price' => $total_price,
                    'message2' => 'Mail also sent to the user with all details',
                ], 201);
                Cache::remember('orders', 3600, function () {
                    return DB::table('orders')->get();
                });
            }
        } catch (BookStoreException $exception) {
            return $exception->message();
        }
    }

    public function UniqueOrderId()
    {
        do {
            $orderid = random_int(1000000000, 9999999999);
        } while (Order::where("order_id", "=", $orderid)->first());

        return $orderid;
    }
}
