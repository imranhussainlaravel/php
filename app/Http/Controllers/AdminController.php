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
                'status' => false
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
                    'status' => false
                ], 400);
            }
    
            // Create image resource
            $image = @imagecreatefromstring($decodedImage);
            if (!$image) {
                return response()->json([
                    'image_url' => '',
                    'message' => 'Unsupported image format.',
                    'status' => false
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
                    'status' => false
                ], 500);
            }
    
            return response()->json([
                'image_url' => asset('images/' . $imageName),
                'message' => 'Image uploaded successfully.',
                'status' => true,
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'image_url' => '',
                'message' => 'Image processing error: ' . $e->getMessage(),
                'status' => false
            ], 500);
        }
    }
    public function delete_category(Request $request) {
        $categoryId = $request->json('id');
    
        if (!$categoryId) {
            return response()->json([
                'message' => 'Category ID is required.',
                'status' => false
            ], 400);
        }
    
        $category = Categories::find($categoryId);
    
        if (!$category) {
            return response()->json([
                'message' => 'Category not found.',
                'status' => false
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
            'status' => true
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
            'image_1'      => 'nullable',
            'image_2'      => 'nullable',
            'image_3'      => 'nullable',
            'image_4'      => 'nullable',
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
            'image_1'      => $validatedData['image_1'] ?? "",
            'image_2'      => $validatedData['image_2'] ?? "",
            'image_3'      => $validatedData['image_3'] ?? "",
            'image_4'      => $validatedData['image_4'] ?? "",
            'image_5'      => $validatedData['image_5'] ?? "",
            'status'       => $validatedData['status'] ?? "active",
        ];
    
        if (!empty($validatedData['id'])) {
            $product = Products::findOrFail($validatedData['id']);
            $product->update($productData);
            $message = 'Product updated successfully.';
        } else {
            $product = Products::create($productData);
            $message = 'Product created successfully.';
        }
    
        return response()->json([
            'message' => $message,
            'product' => $product->fresh(),
        ]);
    }
    public function delete_product(Request $request) {
        $productid = $request->json('id');
    
        if (!$productid) {
            return response()->json([
                'message' => 'Category ID is required.',
                'status' => false
            ], 400);
        }
    
        $product = Products::find($productid);
    
        if (!$product) {
            return response()->json([
                'message' => 'Category not found.',
                'status' => false
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
            'status' => true
        ], 200);
    }
    
    
    
}

