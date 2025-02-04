<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categories;

class CategoryController extends Controller
{
    public function get_industry(){

        $navbarData = Categories::where('status', 'active')
        ->get();

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
                    // Handle other nav_ids or skip if not needed
                    break;
            }
        }

        // Share the navbar data with the header view
        $view->with([
            'nav1' => $nav1,
            'nav2' => $nav2,
            'nav3' => $nav3
        ]);
        $response = ['message' => 'working fine'];
        return response()->json($response);
    }
}
