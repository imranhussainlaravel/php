<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;

use App\Models\User;
use App\Models\Categories;
use App\Models\Products;
use App\Models\Blog;
use App\Models\Portfolio;
use App\Models\Request as RequestModel; 




class AdminController extends Controller
{
    // Admin Dashboard
    // public function index()
    // {
    //     return response()->json([
    //         'message' => 'Welcome to the admin dashboard',
    //         'admin' => Auth::user(),
    //     ]);
    // }
    public function login_user(Request $request){
        $validator = validator::make($request->all() ,[
            'email' =>'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->input('email'))->first();


        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials',
                'token' => "",
                'admin_type' => ""
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'token' => $token,
            'admin_type' => $user->usertype
        ], 200);
    }
    public function admin_get_categories(){
        
        $categories = Categories::all();
        // ->select('id', 'title', 'icon', 'nav_id')
        // ->select('id', 'title', 'nav_id','main_img','alt_name','icon','header_img','content','description')
        

        $response = [
            'categories' => $categories,
            'message' => 'categories available',
            'status' => 200
        ];

        return response()->json($response);
    }
    public function create_category(Request $request){

        $validatedData = $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
            'nav_id' => 'required',
            'header_img' => 'nullable',  
            'main_img' => 'nullable',  
            'icon' => 'nullable', 
            'alt_name' => 'nullable',
            'content' => 'nullable',
            // 'faqs' => 'nullable',
            'id' => 'nullable',
            'status' => 'nullable',
            'main_page' => 'nullable',
        ]);

        if(!empty($validatedData['id'])){

            $category = Categories::find($validatedData['id']);
            $category->title = $validatedData['title'];
            $category->description = $validatedData['description'];
            $category->nav_id = $validatedData['nav_id'];
            $category->header_img = $validatedData['header_img'];
            $category->main_img = $validatedData['main_img'] ;
            $category->icon = $validatedData['icon'];
            $category->content = $validatedData['content'];
            // $category->faqs = $validatedData['faqs'];
            $category->alt_name = $validatedData['alt_name'];
            $category->status = $validatedData['status'];
            $category->main_page = $validatedData['main_page'];
            $category->sorting = 0;
            $category->save();

            $response = [
                'message' => 'categories Updated sucessfully.',
                'category' => $category,
                'status' => 200
            ];
    
            return response()->json($response);

        } else {
            $category = new Categories();
            $category->title = $validatedData['title'];
            $category->description = $validatedData['description'];
            $category->nav_id = $validatedData['nav_id'];
            $category->header_img = $validatedData['header_img'];
            $category->main_img = $validatedData['main_img'] ;
            $category->icon = $validatedData['icon'];
            $category->content = $validatedData['content'];
            // $category->faqs = $validatedData['faqs'];
            $category->alt_name = $validatedData['alt_name'];
            $category->status = $validatedData['status'];
            $category->main_page = $validatedData['main_page'];
            $category->sorting = 0;
            $category->save();

            $response = [
                'message' => 'categories added sucessfully.',
                'category' => $category,
                'status' => 200
            ];
    
            return response()->json($response);
        }
        

        
    }
    public function saved_image(Request $request) {
        $imageData = $request->input('image');
        
        if (!$imageData) {
            return response()->json([
                'image_url' => '',
                'message' => 'No image provided.',
                'status' => 200
            ], 400);
        }
    
        try {
            // Extract and process image data
            $imageData = preg_replace('#^data:image/\w+;base64,#i', '', $imageData);
            $decodedImage = base64_decode($imageData);
            
            if (!$decodedImage) {
                return response()->json([
                    'image_url' => '',
                    'message' => 'Invalid image data.',
                    'status' => 200
                ], 400);
            }
    
            // Create image resource
            $image = @imagecreatefromstring($decodedImage);
            if (!$image) {
                return response()->json([
                    'image_url' => '',
                    'message' => 'Unsupported image format.',
                    'status' => 200
                ], 400);
            }
    
            // Create storage directory
            $imageDirectory = public_path('images');
            if (!file_exists($imageDirectory)) {
                mkdir($imageDirectory, 0777, true);
            }
    
            // Generate filename
            $imageName = time() . '.webp';
            $imagePath = $imageDirectory . '/' . $imageName;
    
            // Save with WebP compression if supported
            if (function_exists('imagewebp')) {
                $success = imagewebp($image, $imagePath, 80);
            } else {
                // Fallback to original format
                $success = file_put_contents($imagePath, $decodedImage);
            }
    
            imagedestroy($image);
    
            if (!$success) {
                return response()->json([
                    'image_url' => '',
                    'message' => 'Failed to save image.',
                    'status' => 200
                ], 500);
            }
    
            return response()->json([
                'image_url' => asset('images/' . $imageName),
                'message' => 'Image uploaded successfully.',
                'status' => 200
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'image_url' => '',
                'message' => 'Image processing error: ' . $e->getMessage(),
                 'status' => 200
            ], 500);
        }
    }
    public function delete_category(Request $request) {
        $categoryId = $request->json('id');
    
        if (!$categoryId) {
            return response()->json([
                'message' => 'Category ID is required.',
                'status' => 200
            ], 400);
        }
    
        $category = Categories::find($categoryId);
    
        if (!$category) {
            return response()->json([
                'message' => 'Category not found.',
                'status' => 200
            ], 404);
        }
    
        $imageFields = ['header_img', 'main_img', 'icon'];
    
        foreach ($imageFields as $field) {
            if (!empty($category->$field)) {
                // $imagePath = public_path('images/' . $category->$field);
    
                if (file_exists($category->$field)) {
                    unlink($category->$field); 
                }
            }
        }
    
        $category->delete();
    
        return response()->json([
            'message' => 'Category and associated images deleted successfully.',
            'status' => 200
        ], 200);
    }

    public function toggleCategory(Request $request) 
    {  
        $request->validate([ 
            'id' => 'required',
            'type' => 'required',
            ]);
        $category = Categories::find($request->id); 
        // Toggle value in database
        if ($request->type === 'status') 
        { 
            $category->status = $category->status == "active" ? "inactive" : "active"; 
        } elseif ($request->type === 'main_page')
        { 
            $category->main_page = $category->main_page == "listed" ? "unlisted" : "listed"; // Toggle showing 
        } 
        $category->save(); 
        return response()->json([ 
            'message' => 'Category updated successfully', 
            'status' => 200
            // 'category_id' => $category->id, 
            // 'updated_type' => $request->type, 
        ]); 
    }

    public function create_product(Request $request) {
        $validatedData = $request->validate([
            'id'           => 'nullable|integer',
            'title'        => 'required|max:255',
            'title_2'      => 'nullable|max:255',
            'alt_name'     => 'nullable|max:255',
            'description'  => 'nullable',
            'description_2' => 'nullable',
            // 'faqs'         => 'nullable',
            'content'      => 'nullable',
            'industry_id'  => 'required|integer',
            'material_id'  => 'required|integer',
            'style_id'     => 'required|integer',
            // 'image_1'      => 'nullable',
            // 'image_2'      => 'nullable',
            // 'image_3'      => 'nullable',
            // 'image_4'      => 'nullable',
            'images'       => 'nullable|array',
            'image_5'      => 'nullable',
            'status'       => 'nullable',
        ]);
    
        $productData = [
            'title'        => $validatedData['title'],
            'title_2'      => $validatedData['title_2'] ?? "",
            'alt_name'     => $validatedData['alt_name'] ?? "",
            'description'  => $validatedData['description'],
            'description_2' => $validatedData['description_2'] ?? "",
            // 'faqs'         => $validatedData['faqs'] ?? "",
            'content'      => $validatedData['content'] ?? "",
            'industry_id'  => $validatedData['industry_id'],
            'material_id'  => $validatedData['material_id'],
            'style_id'     => $validatedData['style_id'],
            // 'image_1'      => $validatedData['image_1'] ?? "",
            // 'image_2'      => $validatedData['image_2'] ?? "",
            // 'image_3'      => $validatedData['image_3'] ?? "",
            // 'image_4'      => $validatedData['image_4'] ?? "",
            'image_5'      => $validatedData['image_5'] ?? "",
            'status'       => $validatedData['status'] ?? "active",
        ];

        if (!empty($validatedData['images'])) {
            foreach ($validatedData['images'] as $index => $imageUrl) {
                if ($index == 0) {
                    $productData['image_1'] = $imageUrl;
                } elseif ($index == 1) {
                    $productData['image_2'] = $imageUrl;
                } elseif ($index == 2) {
                    $productData['image_3'] = $imageUrl;
                } elseif ($index == 3) {
                    $productData['image_4'] = $imageUrl;
                }
            }
        }
    
        if (!empty($validatedData['id'])) {
            $product = Products::findOrFail($validatedData['id']);
            $product->update($productData);
            $message = 'Product updated successfully.';
        } else {
            $product = Products::create($productData);
            $message = 'Product created successfully.';
        }
        
        // $product->images = $validatedData['images'];
        // $responseProduct = $product->fresh()->toArray();
        // unset($responseProduct['image_1'], $responseProduct['image_2'], $responseProduct['image_3'], $responseProduct['image_4']);
        // $responseProduct = $validatedData['images'];
        return response()->json([
            'message' => $message,
            'product' => $product->fresh(),
            // 'product' => $responseProduct,
            'status' => 200
        ]);
    }
    public function delete_product(Request $request) {
        $productid = $request->json('id');
    
        if (!$productid) {
            return response()->json([
                'message' => 'Category ID is required.',
                'status' => 200
            ], 400);
        }
    
        $product = Products::find($productid);
    
        if (!$product) {
            return response()->json([
                'message' => 'Category not found.',
                'status' => 200
            ], 404);
        }
    
        $imageFields = ['image_1', 'image_2', 'image_3' , 'image_4', 'image_5'];
    
        foreach ($imageFields as $field) {
            if (!empty($product->$field)) {
                // $imagePath = public_path('images/' . $product->$field);
    
                if (file_exists($product->$field)) {
                    unlink($product->$field); 
                }
            }
        }
    
        $product->delete();
    
        return response()->json([
            'message' => 'Product and associated images deleted successfully.',
            'status' => 200
        ], 200);
    }
    public function admin_get_products() {
        $products = Products::all()->map(function ($product) {
            $product->images = array_filter([
                $product->image_1,
                $product->image_2,
                $product->image_3,
                $product->image_4
            ]); // Removes null/empty values
    
            unset($product->image_1, $product->image_2, $product->image_3, $product->image_4);
    
            return $product;
        });
    
        $response = [
            'products' => $products, // Use modified products
            'message'  => 'Products available',
            'status'   => 200
        ];
    
        return response()->json($response);
    }

    public function toggleproduct(Request $request) 
    {  
        $request->validate([ 
            'id' => 'required',
            ]);
        $Products = Products::find($request->id); 
        if (!$Products) {
            return response()->json([
                'message' => 'Product not found',
                'status' => 404
            ], 404);
        }
        // Toggle value in database
        
        $Products->status = $Products->status == "active" ? "inactive" : "active"; 
        $Products->save(); 
        return response()->json([ 
            'message' => 'Products updated successfully', 
            'status' => 200
        ]); 
    }
    public function get_all_forms(){

        $requests = RequestModel::orderBy('id', 'desc')->get();
        $ALL = [
            "quote" => [],
            "contact_us" => [],
            "subscribe" => [],
        ];
        foreach ($requests as $request) {
            if($request->type == 'quote'){
                $ALL['quote'][] = $request;
            }
            if($request->type == 'contact_us'){
                $ALL['contact_us'][] = $request;
            }
            if($request->type == 'subscribe'){
                $ALL['subscribe'][] = $request;
            }
        }

        return response()->json([ 
            // 'message' => 'Poducts updated successfully', 
            "all_forms" => $ALL,
            'status' => 200
        ]); 


    }
    public function create_update_blog(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'nullable',
            'title' => 'required',
            'image' => 'required',
            'content' => 'nullable|string',
            'sorting' => 'nullable|integer',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(),'status'=>200], 422);
        }

        $blog_data = [
            'title' => $request->title,
            'image' => $request->image,
            'content' => $request->content,
            'sorting' => $request->sorting ?? 0,
            'status' => $request->status ?? 'active',
        ];

        if (!empty($request->id)) {
            $Blog = Blog::findOrFail($request->id);
            $Blog->update($blog_data);
            $message = 'Blog updated successfully.';
        } else {
            $Blog = Blog::create($blog_data);
            $message = 'Blog created successfully.';
        } 

        return response()->json([
            'message' => $message,
            'Blog' => $Blog->fresh(),
            'status' => 200
        ]);

    }
    public function getadminBlogs()
    {
        $blogs = Blog::all(); 

        return response()->json([
            'status' => 200,
            'blogs' => $blogs
        ]);
    }

    public function getBlogs()
    {
        $blogs = Blog::where('status', 'active') // Only active blogs
                     ->orderBy('sorting', 'asc') // Sorting
                     ->get(['id', 'title', 'image']); // Fetch only title and image

        return response()->json([
            'status' => 200,
            'blogs' => $blogs
        ]);
    }
    public function getBlogById(Request $request)
    {
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

    public function deleteBlog(Request $request)
    {
        $id = $request->json('id');

        $blog = Blog::find($id);

        if (!$blog) {
            return response()->json(['message' => 'Blog not found','status' => 200], 404);
        }

        $blog->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Blog deleted successfully.'
        ]);
    }

    public function create_update_portfolio(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'nullable|integer',
            'image' => 'required|string', // Assuming image URL or filename
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(),'status'=>200], 422);
        }

        $portfolioData = [
            'image' => $request->image,
        ];

        if (!empty($request->id)) {
            $portfolio = Portfolio::find($request->id);
            if (!$portfolio) {
                return response()->json(['message' => 'Portfolio not found','status' => 200,], 404);
            }
            $portfolio->update($portfolioData);
            $message = 'Portfolio updated successfully.';
        } else {
            $portfolio = Portfolio::create($portfolioData);
            $message = 'Portfolio created successfully.';
        }

        return response()->json([
            'status' => 200,
            'message' => $message,
            'portfolio' => $portfolio
        ]);
    }

    public function getPortfolios()
    {
        $portfolios = Portfolio::all();
        return response()->json([
            'status' => 200,
            'portfolios' => $portfolios
        ]);
    }

    public function deletePortfolio(Request $request)
    {
        $id = $request->json('id');

        $portfolio = Portfolio::find($id);
        if (!$portfolio) {
            return response()->json(['message' => 'Portfolio not found','status' => 200], 404);
        }

        $portfolio->delete();
        return response()->json([
            'status' => 200,
            'message' => 'Portfolio deleted successfully.'
        ]);
    }
}

