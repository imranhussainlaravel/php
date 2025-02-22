<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categories;
use App\Models\Product;


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
    public function get_category_by_id(Request $request)
    {
        // Validate the incoming request
        $id = $request->input('id');

        if (!$id) {
            return response()->json(['message' => 'ID is required'], 400);
        }

        // Fetch category based on ID from the JSON request
        $category = Categories::where('id', $request->id)
            ->where('status', 'active')
            ->select('id', 'title', 'description', 'main_img', 'alt_name' , 'header_img','nav_id')
            ->first();

        $productModel = new Product();
        $query = $productModel->select('id', 'title', 'image_1','alt_name') // Select specific columns
        ->where('status', 'active'); // Common condition

        if ($category->nav_id == '1') {
            $query->where('industry_id', $category->id);
        } elseif ($category->nav_id == '2') {
            $query->where('material_id', $category->id);
        } elseif ($category->nav_id == '3') {
            $query->where('style_id', $category->id);
        }
        
        $products = $query->get()->toArray();
        

        return response()->json([
            'category' => $category,
            'products' => $products,
            'message' => 'Category found successfully'
        ]);
    }
    public function get_product_by_id(Request $request)
    {
        $id = $request->input('id');

        if (!$id) {
            return response()->json(['message' => 'ID is required'], 400);
        }

        $product = Product::where('id', $request->id)
        ->where('status', 'active')
        ->select('id', 'title', 'description', 'image_1','image_2','image_3','image_4', 'alt_name','content')
        ->first();

        return response()->json([
            'product' => $product,
            'message' => 'Category found successfully'
        ]);
    }
}
