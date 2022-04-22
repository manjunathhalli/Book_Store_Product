<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $table = "feedbacks";
    protected $fillable = [
        'book_id',
        'feedback',
        'rating',

    ];

    public function saveingFeedback($request, $currentUser)
    {
        $feedback = new Feedback();
        $feedback->book_id = $request->get('book_id');
        $feedback->feedback = $request->input('feedback');
        $feedback->rating = $request->input('rating');
        $feedback->user_id = $currentUser->id;

        return $feedback;
    }
    public function avgRating($book_id)
    {
        return Feedback::where('feedbacks.book_id', $book_id)
            ->pluck('rating')->avg();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
