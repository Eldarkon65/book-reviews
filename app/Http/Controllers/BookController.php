<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $title = $request->input('title');
        $filter = $request->input('filter','');

        $books = Book::when($title, function ($query, $title) {
            return $query->title($title);
        });

        $books = match ($filter) {
            'popular_last_month' => $books -> popularLastMonth(),
            'popular_last_6months' => $books -> popularLast6Months(),
            'highest_rating_last_month' => $books-> HighestRatingLastMonth(),
            'highest_rating_last_6months' => $books-> HighestRatingLast6Months(),
            default => $books->latest()->withAvgRatings()->withReviewsCount()
        };

        $cacheKey = 'book:' . $filter . $title;
        $books =
//            cache()->remember($cacheKey, 3600,
//            fn() =>
            $books->get();
//        );

//

        return view('books.index',['books'=>$books]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('books.create');

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|min:3',
            'author' => 'required|min:2'
         ]);

        $book = $this->create($data);

        return redirect()->route('books.index');

    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $cacheKey = 'book:' . $id;
        $book = cache()->remember($cacheKey, 3600,
            fn()=> Book::with(
            ['reviews' => fn($query) => $query->latest()])
                ->withAvgRatings()
                ->withReviewsCount()
                ->findOrFail($id));

        return view('books.show', ['book'=>$book]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}