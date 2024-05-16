<?php

namespace App\Http\Controllers;
use App\Models\Book;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class BookController extends Controller
{
    public function index(Request $request){
        $books = Book::orderBy('created_at', 'DESC');

        if (!empty($request->keyword)) {
            $books->where('title', 'like', '%' . $request->keyword . '%');
        }

        $books = $books->paginate(10);
        return view('books.list', [
            'books' => $books
        ]);
    }

    public function create(){
        return view('books.create');


    }

    public function store(Request $request)
    {
        $rules = [
            'title' => 'required|min:5',
            'author' => 'required|min:3',
            'status' => 'required',
        ];

        if(!empty($request->image)){
             $rules['image'] ='image';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->route('books.create')->withInput()->withErrors($validator);
        }

        $book = new Book();
        $book->title = $request->title;
        $book->author = $request->author;
        $book->status = $request->status;
        $book->description = $request->description;
        $book->save();

        if(!empty($request->image)){
            $image = $request->image;
           $ext = $image->getClientOriginalExtension();
           $imageName = time(). '.'. $ext;
           $image->move(public_path('uploads/books'), $imageName);

           $book->image = $imageName;
           $book->save();

           $manager = new ImageManager(Driver::class);
           $img = $manager->read(public_path('uploads/books/'.$imageName));
           $img->resize(990);
           $img->save(public_path('uploads/books/thumb/'.$imageName));
        }





        return redirect()->route('books.index')->with('success', 'Book Added Successfully');
    }


    public function edit($id){
        $book = Book::findOrFail($id);
        return view('books.edit', ['book' => $book]);

    }

    public function update($id, Request $request){

        $book = Book::findOrFail($id);

        $rules = [
            'title' => 'required|min:5',
            'author' => 'required|min:3',
            'status' => 'required',
        ];

        if(!empty($request->image)){
             $rules['image'] ='image';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->route('books.edit')->withInput()->withErrors($validator);
        }

        $book->title = $request->title;
        $book->author = $request->author;
        $book->status = $request->status;
        $book->description = $request->description;
        $book->save();

        if(!empty($request->image)){

            File::delete(public_path('uploads/books/' . $book->image));
            File::delete(public_path('uploads/books/thumb/' . $book->image));

            $image = $request->image;
           $ext = $image->getClientOriginalExtension();
           $imageName = time(). '.'. $ext;
           $image->move(public_path('uploads/books'), $imageName);

           $book->image = $imageName;
           $book->save();

           $manager = new ImageManager(Driver::class);
           $img = $manager->read(public_path('uploads/books/'.$imageName));
           $img->resize(990);
           $img->save(public_path('uploads/books/thumb/'.$imageName));
        }
        return redirect()->route('books.index')->with('success', 'Book Updated Successfully');
    }

    public function destroy($id){
        $book = Book::find($id);

        if ($book == null) {
            session()->flash('error', 'Book Not Found');
            return response()->json([
                'status' => false,
                'message' => 'Book not found'
            ]);
        } else {
            File::delete(public_path('uploads/books/' . $book->image));
            File::delete(public_path('uploads/books/thumb/' . $book->image));
            $book->delete();
            session()->flash('success', 'Book Deleted Successfully');

            return response()->json([
                'status' => true,
                'message' => 'Book Deleted Successfully'
            ]);
        }

    }

}
