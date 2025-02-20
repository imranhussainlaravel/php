<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categories;

class CategoryController extends Controller
{
    public function get_industry(){

        $navbarData = Categories::where('status', 'active')
        ->select('id', 'title', 'icon', 'nav_id')
        ->get();

        $nav1 = [];
        $nav2 = [];
        $nav3 = [];

        foreach ($navbarData as $category) {
            switch ($category->nav_id) {
                case 1:
                    $nav1[] = $category;
                    break;
                case 2:
                    $nav2[] = $category;
                    break;
                case 3:
                    $nav3[] = $category;
                    break;
                default:
                    break;
            }
        }

        $response = [
            'industry' => $nav1,
            'material' => $nav2,
            'style' => $nav3,
            'message' => 'working fine'
        ];
        // $response = ['message' => 'working fine','categories' => $categories];
        return response()->json($response);
    }
}
