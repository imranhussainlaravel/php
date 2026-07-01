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
        ->whereNull('deleted_at')
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
        ->whereNull('deleted_at')
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
        $slug = trim($title);
        $title = str_replace('-', ' ', $slug);

        // Fetch category based on ID from the JSON request
        $category = Categories::where(function ($query) use ($slug, $title) {
                $query->where('title', $slug)
                    ->orWhere('title', $title);
            })
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->select('id', 'title', 'description', 'main_img', 'alt_name' , 'header_img','nav_id','content','meta_description','meta_title','faqs','status','deleted_at','updated_at')
            ->first();
        if (empty($category)) {
            return response()->json(['message' => 'Category gone','status' => 410], 410);
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
        $slug = trim($title);
        $title = str_replace('-', ' ', $slug);

        $product = Product::where(function ($query) use ($slug, $title) {
            $query->where('title', $slug)
                ->orWhere('title', $title);
        })
        ->where('status', 'active')
        ->whereNull('deleted_at')
        ->select('id', 'title', 'description', 'image_1','image_2','image_3','image_4','image_5', 'alt_name','content','title_2','description_2','meta_description','meta_title','faqs','status','deleted_at','updated_at')
        ->first();

        if (empty($product)) {
            return response()->json(['message' => 'Product gone', 'status' => 410], 410);
        }

        if (!empty($product->faqs)) {
            $decodedFaqs = json_decode($product->faqs, true);
            $product->faqs = is_array($decodedFaqs) ? $decodedFaqs : [];
        } else {
            $product->faqs = [];
        }
        $product->images = array_filter([
            $product->image_1,
            $product->image_2,
            $product->image_3,
            $product->image_4
        ]); // Removes null/empty values
    
        unset($product->image_1, $product->image_2, $product->image_3, $product->image_4);
    
        return response()->json($product, 200);
    }
    public function get_portfolio(){
       $portfolios = Portfolio::orderBy('id', 'desc')->get();
        return response()->json([
            'status' => 200,
            'portfolios' => $portfolios
        ]);
    }
    public function get_all_blogs(){
        $blogs = Blog::where('status', 'active') // Only active blogs
        ->whereNull('deleted_at')
        ->orderBy('sorting', 'asc') // Sorting
        ->get(); // Fetch only title and image

        return response()->json([
        'status' => 200,
        'blogs' => $blogs
        ]);
    }
    public function get_blog_detail_with_id(Request $request){
        $id = $request->json('id');
    
        $blog = Blog::where('id', $id)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->first();

        if (!$blog) {
            return response()->json(['message' => 'Blog gone','status' => 410], 410);
        }

        return response()->json([
            'status' => 200,
            'blog' => $blog // Returns title, image, content, etc.
        ]);
    }
    public function get_sliders_products(Request $request){

        $productModel = new Product();
        if ($request->type === 'beat-my-quote') {
           $ids = [54, 47, 48, 54, 49, 50];

            $product = $productModel->select('id', 'title', 'image_1', 'alt_name')
                ->whereIn('id', $ids)
                ->where('status', 'active')
                ->whereNull('deleted_at')
                ->orderByRaw('FIELD(id, ' . implode(',', $ids) . ')')
                ->get();
        } else {
            $product = $productModel->select('id', 'title', 'image_1', 'alt_name')
                ->where('status', 'active')
                ->whereNull('deleted_at')
                ->inRandomOrder() 
                ->limit(7)       
                ->get();   
        }
            return response()->json([
                'status' => 200,
                'product' => $product // Returns title, image, content, etc.
            ]);

    }
    public function search(Request $request)
    {
        $query = trim($request->json('search'));

        if (!$query) {
            return response()->json(['error' => 'Search query is required'], 400);
        }

        $commonWords = ['custom', 'boxes', 'box', 'packaging'];

        $words = explode(' ', $query);

        $filteredWords = array_diff($words, $commonWords);

        $searchWords = !empty($filteredWords) ? $filteredWords : $words;

        $exactMatches = Product::where('title', 'LIKE', "%{$query}%")
        ->select('id', 'title', 'image_1','alt_name') // Select specific columns
        ->whereNull('deleted_at')
        ->where('status', 'active')
        ->get();

        $reverseMatches = Product::where(function ($q) use ($searchWords) {
            foreach ($searchWords as $word) {
                $q->orWhere('title', 'LIKE', "%{$word}%");
            }
        })->select('id', 'title', 'image_1','alt_name') // Select specific columns
        ->whereNull('deleted_at')
        ->where('status', 'active')
        ->get();

        $partialMatches = Product::where(function ($q) use ($searchWords) {
            foreach ($searchWords as $word) {
                $q->orWhere('title', 'LIKE', "%{$word}%");
            }
        })->select('id', 'title', 'image_1','alt_name') // Select specific columns
        ->whereNull('deleted_at')
        ->where('status', 'active')
        ->get();

        $mergedResults = collect($exactMatches)
            ->merge($reverseMatches)
            ->merge($partialMatches)
            ->unique('id')
            ->values();

        if ($mergedResults->isEmpty()) {
            return response()->json([
                'message' => 'No products found for the given search query.',
                'data' => []
            ], 200);
        }
    
        return response()->json([
            'message' => 'Products found successfully.',
            'data' => $mergedResults
        ], 200);
    }
    public function getAllProducts()
    {
        $products = Product::where('status', 'active')
            ->whereNull('deleted_at')
            ->get(); 

        foreach ($products as $product) {
            // Decode `faqs` only if it's not empty
            $product->title = strtolower(str_replace(' ', '-', $product->title));
            if (!empty($product->faqs)) {
                $decodedFaqs = json_decode($product->faqs, true);
                $product->faqs = is_array($decodedFaqs) ? $decodedFaqs : [];
            } else {
                $product->faqs = [];
            }
    
            // Collect images and remove empty values
            $product->images = array_filter([
                $product->image_1,
                $product->image_2,
                $product->image_3,
                $product->image_4
            ]);
    
            // Remove individual image columns
            unset($product->image_1, $product->image_2, $product->image_3, $product->image_4);
        }

        return response()->json([
            'success' => true,
            'data' => $products
        ], 200);
    }
    public function get_all_category_for_seo()
    {
        $categories = Categories::where('status', 'active')
            ->whereNull('deleted_at')
            ->orderBy('sorting', 'asc')
            ->get();

        // Convert FAQs to JSON format if they are not empty
        $categories->transform(function ($category) {

            $category->title = strtolower(str_replace(' ', '-', $category->title));

            $category->faqs = !empty($category->faqs) ? json_decode($category->faqs, true) : [];
            return $category;
        });

        return response()->json([
            'categories' => $categories,
            'message' => 'categories found.',
            'status' => 200,
        ]);
    }


}
