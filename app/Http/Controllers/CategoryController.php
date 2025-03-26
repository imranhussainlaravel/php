<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categories;
use App\Models\Product;
use App\Models\Blog;
use App\Models\Portfolio;
use App\Models\Request as RequestModel; 


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
        
        $data = $request->json()->all();

        // Extract the 'id' field
        $title = $data['id'] ?? null;

        if (!$title) {
            return response()->json(['message' => 'Title is required','status'=>'200'], 400);
        }
        $title = str_replace('-', ' ', $title);

        // Fetch category based on ID from the JSON request
        $category = Categories::where('title', $title)
            ->where('status', 'active')
            ->select('id', 'title', 'description', 'main_img', 'alt_name' , 'header_img','nav_id','content','meta_description','faqs')
            ->first();
        if (empty($category)) {
            return response()->json(['message' => 'Category not found','status'=>'200'], 400);
        }

        if (!empty($category->faqs)) {
            $decodedFaqs = json_decode($category->faqs, true);
            $category->faqs = is_array($decodedFaqs) ? $decodedFaqs : [];
        } else {
            $category->faqs = [];
        }

        $productModel = new Product();
        $query = $productModel->select('id', 'title', 'image_1','alt_name') // Select specific columns
        ->whereNull('deleted_at')
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
        $data = $request->json()->all();

        // Extract the 'id' field
        $title = $data['id'] ?? null;
        if (!$title) {
            return response()->json(['message' => 'Title is required'], 400);
        }
        $title = str_replace('-', ' ', $title);

        $product = Product::where('title', $title)
        ->where('status', 'active')
        ->select('id', 'title', 'description', 'image_1','image_2','image_3','image_4','image_5', 'alt_name','content','title_2','description_2','meta_description')
        ->first();

        $product->images = array_filter([
            $product->image_1,
            $product->image_2,
            $product->image_3,
            $product->image_4
        ]); // Removes null/empty values
    
        unset($product->image_1, $product->image_2, $product->image_3, $product->image_4);
    
        return $product;

        return response()->json([
            'product' => $product,
            'status' => 200,
            'message' => 'Category found successfully'
        ]);
    }
    public function get_portfolio(){
        $portfolios = Portfolio::all();
        return response()->json([
            'status' => 200,
            'portfolios' => $portfolios
        ]);
    }
    public function get_all_blogs(){
        $blogs = Blog::where('status', 'active') // Only active blogs
        ->orderBy('sorting', 'asc') // Sorting
        ->get(['id', 'title', 'image']); // Fetch only title and image

        return response()->json([
        'status' => 200,
        'blogs' => $blogs
        ]);
    }
    public function get_blog_detail_with_id(Request $request){
        $id = $request->json('id');
    
        $blog = Blog::find($id);

        if (!$blog) {
            return response()->json(['message' => 'Blog not found','status' => 200], 404);
        }

        return response()->json([
            'status' => 200,
            'blog' => $blog // Returns title, image, content, etc.
        ]);
    }
    public function get_sliders_products(Request $request){
        $productModel = new Product();
        $product = $productModel->select('id', 'title', 'image_1', 'alt_name')
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->limit(7) // Get only 7 products
            ->get(); // Execute the query

            return response()->json([
                'status' => 200,
                'product' => $product // Returns title, image, content, etc.
            ]);

    }

}
