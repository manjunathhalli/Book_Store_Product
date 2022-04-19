<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $table = "orders";
    protected $fillable = [
        'user_id',
        'book_id',
        'address_id',
        'order_id'
    ];

    // public function orderCreation($order)
    // {
    //      $order = Order::create([
    //     'user_id' => $currentUser->id,
    //     'book_id' => $bookDetails['id'],
    //     'address_id' => $getAddress['id'],
       
    // ]);
    // return $order;

    // }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
