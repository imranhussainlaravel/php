<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;

use App\Models\User;
use App\Models\Categories;



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
        
        $navbarData = Categories::where('status', 'active')
        // ->select('id', 'title', 'icon', 'nav_id')
        ->select('id', 'title', 'nav_id','main_img','alt_name','icon','header_img','content','description')
        ->get();

        $response = [
            'categories' => $navbarData,
            'message' => 'categories available',
        ];

        return response()->json($response);
    }
    public function create_category(Request $request){

        $validatedData = $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
            'nav_id' => 'required|max:255',
            'header_img' => 'nullable',  
            'main_img' => 'nullable',  
            'icon' => 'nullable', 
            'alt_name' => 'nullable',
            'content' => 'nullable',
            'faqs' => 'nullable',
            'id' => 'nullable',
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
            $category->faqs = $validatedData['faqs'];
            $category->alt_name = $validatedData['alt_name'];
            $category->status = $validatedData['status'];
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
            $category->faqs = $validatedData['faqs'];
            $category->alt_name = $validatedData['alt_name'];
            $category->status = $validatedData['status'];
            $category->save();

            $response = [
                'message' => 'categories added sucessfully.',
            ];
    
            return response()->json($response);
        }
        

        
    }
    public function saved_image(Request $request) {
        $imageData = $request->input('image'); // Base64 string
    
        if ($imageData) {
            // Extract file type
            preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches);
            $imageType = isset($matches[1]) ? $matches[1] : 'png'; // Default to PNG if unknown
    
            // Remove Base64 prefix
            $imageData = preg_replace('#^data:image/\w+;base64,#i', '', $imageData);
    
            // Decode image
            $decodedImage = base64_decode($imageData);
    
            // Ensure valid image before saving
            if (!$decodedImage) {
                return response()->json([
                    'image_url' => '',
                    'message' => 'Invalid image data.',
                    'status' => false
                ], 400);
            }
    
            // Define the images directory
            $imageDirectory = public_path('images');
    
            // 🚨 Ensure the directory exists
            if (!file_exists($imageDirectory)) {
                mkdir($imageDirectory, 0777, true);
            }
    
            // Save file
            $imageName = time() . '.' . $imageType;
            $imagePath = $imageDirectory . '/' . $imageName;
            file_put_contents($imagePath, $decodedImage);
    
            return response()->json([
                'image_url' => asset('images/' . $imageName),
                'message' => 'Image uploaded successfully.',
                'status' => true,
            ]);
        }
    
        return response()->json([
            'image_url' => '',
            'message' => 'No image provided.',
            'status' => false
        ], 400);
    }
    
    
}

