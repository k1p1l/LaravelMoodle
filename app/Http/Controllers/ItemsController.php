<?php

namespace App\Http\Controllers;

use App\Category;
use App\Item;
use App\User;

//use Auth;
use File;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use FileSystem;
use Illuminate\Http\Response;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use function Sodium\compare;


class ItemsController extends Controller
{
    const PAGINATE = 5;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $strSearch = $request->input('search');
        if (empty($strSearch)) {
            $itemData = Item::paginate(self::PAGINATE);
            return view('welcome', compact('itemData'));
        }
        $categoryInfo = Category::where('title', '=', $strSearch)
            ->first();
        if ($categoryInfo != null) {
            $idCategory = $categoryInfo->id;
            $itemData = Item::where('category_id', '=', $idCategory)
                ->paginate(self::PAGINATE);
            return view('welcome', compact('itemData'));
        }
        return redirect('welcome')->with('exception', "No found for $strSearch");

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'category_id' => 'required',
            'image' => 'required|image|max:2048',
            'description' => 'required'
        ]);

        $image = $request->file('image');
        $newName = rand() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images'), $newName);

        $formData = array(
            'title' => $request->input('title'),
            'category_id' => $request->category_id,
            'image' => $newName,
            'description' => $request->description,
            'user_id' => Auth::user()->id
        );

        Category::findOrFail($formData['category_id']);
        Item::create($formData);
        return redirect('welcome')->with('success', 'Data Added successfully.');

        return redirect('', '406')->with('exception', 'Data not saved because you tried to change field data')->route('welcome');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $item = Item::findOrFail($id);
        $userId = $item->user_id;
        $user = User::findOrFail($userId);
        return view('view', compact('item', 'user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $itemEdit = Item::findOrFail($id);
        return view('edit', compact('itemEdit'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $imageName = $request->hidden_image;
        $image = $request->file('image');

        if ($image != null) {
            unlink(public_path('images/') . $imageName);
            $request->validate([
                'title' => 'required',
                'category_id' => 'required',
                'image' => 'required|image|max:2048',
                'description' => 'required'
            ]);
            $imageName = rand() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
        } else {
            $request->validate([
                'title' => 'required',
                'category_id' => 'required',
                'description' => 'required'
            ]);
        }
        $formData = [
            'title' => $request->title,
            'category_id' => $request->category_id,
            'image' => $imageName,
            'description' => $request->description
        ];

        Item::findOrFail($id)->update($formData);
        return redirect('welcome')->with('success', 'Data is successfully update');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $itemDelete = Item::findOrFail($id);
        $itemDelete->delete();
        return redirect('welcome')->with('success', 'Data is successfully delete');
    }
}
