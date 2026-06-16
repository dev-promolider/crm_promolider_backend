<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use App\Traits\ResponseFormat;

class CategoryController extends Controller
{
    use ResponseFormat;

    /**
     * Show categories list
     * @return \Illuminate\Http\Response
     */
    public function list()
    {
        $data = Category::select('id', 'name', 'icon')->get();
        $status_preference = User::findOrFail(auth()->user()->id);
        return $this->responseOk('', $data, $status_preference);
    }
}
