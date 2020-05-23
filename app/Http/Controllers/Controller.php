<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Request;
use Illuminate\Pagination\Paginator;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public function search(Request $request)
    {
//        $name = Request::get('search');
//        if ($name == 'FIRST'){
//            $name = 1;
//        } elseif ($name == 'SECOND'){
//            $name = 2;
//        } else ($name == 'THIRD'){
//            $name = 3
//        };
//        $data = DB::table('items')
//            ->select(DB::raw("*"))
//            ->where('category_id', '=', $name)
//            ->where('deleted_at','=', null)
//            ->paginate(5);
//        return view('welcome', compact('data'))
//            ->with('i', (request()->input('page', 1) - 1) * 5);
    }
}
