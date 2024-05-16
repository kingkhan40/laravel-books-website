<?php

namespace App\Http\Controllers;
use App\Models\Book;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request){
        $books = Book::orderBy('created_at','DESC');

        if (!empty($request->keyword)) {

            $books->where('title', 'like', '%' . $request->keyword. '%');

        }

       $books = $books->where('status', 1)->paginate(8);


        return view('home',['books' => $books]);
    }

    public function detail($id){
        {
            $book = Book::findOrFail($id);

           

            $relatedBooks = Book::where('status', 1)->take(3)->where('id','!=',$id)->inRandomOrder()->get();


            return view('book-detail', [
                'book' => $book,
                'relatedBooks' => $relatedBooks,
            ]);
        }
    }
}
