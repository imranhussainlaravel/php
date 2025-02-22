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
    public function get_all_category(){

        $categories = Categories::where('status', 'active')
        ->select('id', 'title', 'nav_id','main_img','alt_name')
        ->where('sorting', '!=', '0') // Replace $someValue with the value you want to exclude
        ->orderBy('sorting', 'asc') // Order by sorting in ascending order
        ->get();

        $response = [
            'categories' => $categories,
            'message' => 'working fine'
        ];

        return response()->json($response);


    }
}
