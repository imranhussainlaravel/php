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
        ->orderBy('title', 'asc')
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
                    $nav2[] = [
                        'id'    => $category->id,
                        'title' => $category->title,
                        'nav_id'  => $category->nav_id,
                    ];
                    break;
                case 3:
                    $nav3[] = [
                        'id'    => $category->id,
                        'title' => $category->title,
                        'nav_id'  => $category->nav_id,
                    ];
                    break;
                default:
                    break;
            }
        }

        $response = [
            'industry' => $nav1,
            'material' => $nav2,
            'style' => $nav3,
            'message' => 'Categories found.',
            'status' => 200,
        ];
        return response()->json($response);
    }
    public function get_all_category(){

        $categories = Categories::where('status', 'active')
        ->select('id', 'title', 'nav_id','main_img','alt_name')
        ->where('main_page','listed') // Replace $someValue with the value you want to exclude
        ->orderBy('sorting', 'asc') // Order by sorting in ascending order
        ->get();

        $response = [
            'categories' => $categories,
            'message' => 'working fine',
            'status' => 200,
        ];

        return response()->json($response);

    }
    public function get_category_by_id(Request $request)
    {
        // Validate the incoming request
        // $id = $request->input('id');
        $title = $request->input('title');
        $title = str_replace('-', ' ', $title);

        if (!$title) {
            return response()->json(['message' => 'Title is required'], 400);
        }

        // Fetch category based on ID from the JSON request
        $category = Categories::where('title', $title)
            ->where('status', 'active')
            ->select('id', 'title', 'description', 'main_img', 'alt_name' , 'header_img','nav_id','content')
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
            'status' => 200,
            'message' => 'Category found successfully'
        ]);
    }
    public function get_product_by_id(Request $request)
    {
        // $id = $request->input('id');
        $title = $request->input('title');
        $title = str_replace('-', ' ', $title);
        if (!$title) {
            return response()->json(['message' => 'Title is required'], 400);
        }

        $product = Product::where('title', $title)
        ->where('status', 'active')
        ->select('id', 'title', 'description', 'image_1','image_2','image_3','image_4', 'alt_name','content','title_2','description_2')
        ->first();

        return response()->json([
            'product' => $product,
            'status' => 200,
            'message' => 'Category found successfully'
        ]);
    }

}
