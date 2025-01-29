<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function get_industry(){
        return response()->json(['message' => 'working fine']);
    }
}
