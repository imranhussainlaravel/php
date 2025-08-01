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
        
        $categories = Categories::orderBy('sorting', 'asc') // Order by sorting in ascending order
        ->get();
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
            'meta_description' => 'nullable', 
            'alt_name' => 'nullable',
            'content' => 'nullable',
            // 'faqs' => 'nullable',
            'id' => 'nullable',
            'status' => 'nullable',
            'main_page' => 'nullable',
        ]);

        if(!empty($validatedData['id'])){

            $category = Categories::find($validatedData['id']);

            if ($category) {
                $imageFields = ['header_img', 'main_img', 'icon'];
            
                foreach ($imageFields as $field) {
                    $oldImage = $category->$field;
                    $newImage = $validatedData[$field] ?? null; // Avoid undefined index error
        
                    // Check if the old image exists and is being replaced with a new one
                    if (!empty($oldImage) && !empty($newImage) && $oldImage !== $newImage) {
                        $relativePath = str_replace(asset('/'), '', $oldImage);
                        $filePath = public_path($relativePath);
        
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }
        
                    // Assign the new image only if it's not empty
                    if (!empty($newImage)) {
                        $category->$field = $newImage;
                    }
                }
            
                $category->title = $validatedData['title'];
                $category->description = $validatedData['description'];
                $category->nav_id = $validatedData['nav_id'];
                // $category->header_img = $validatedData['header_img'];
                // $category->main_img = $validatedData['main_img'] ;
                // $category->icon = $validatedData['icon'];
                $category->content = $validatedData['content'] ?? '';
                $category->meta_description  = $validatedData['meta_description'] ?? '';
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
                // Return error if category is not found
                return response()->json([
                    'message' => 'Category not found.',
                    'status' => 404
                ]);
            }


            
        } else {
            $category = new Categories();
            $category->title = $validatedData['title'];
            $category->description = $validatedData['description'];
            $category->nav_id = $validatedData['nav_id'];
            $category->header_img = $validatedData['header_img'];
            $category->main_img = $validatedData['main_img'] ;
            $category->icon = $validatedData['icon'] ?? "";
            $category->content = $validatedData['content'] ?? '';
            $category->meta_description  = $validatedData['meta_description'] ?? '';
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
    
            $finfo = finfo_open();
            $mimeType = finfo_buffer($finfo, $decodedImage, FILEINFO_MIME_TYPE);
            finfo_close($finfo);
    
            // **Create an image resource based on type**
            // if ($mimeType === 'image/png') {
            //     $image = imagecreatefrompng($decodedImage);
            // } else {
                    $image = @imagecreatefromstring($decodedImage);
            // }
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


    public function saveDocument(Request $request)
    {
        $base64Data = $request->input('file');

        if (!$base64Data || !str_contains($base64Data, 'base64,')) {
            return response()->json([
                'file_url' => '',
                'message' => 'Invalid base64 format.',
                'status' => 400
            ]);
        }

        try {
            // Extract mime type from base64 string
            preg_match('/^data:(.*);base64,/', $base64Data, $matches);
            $mimeType = $matches[1] ?? null;
            $base64Str = explode('base64,', $base64Data)[1];

            if (!$mimeType) {
                return response()->json([
                    'file_url' => '',
                    'message' => 'MIME type not found.',
                    'status' => 400
                ]);
            }

            // Get extension from mime type
            $extension = explode('/', $mimeType)[1] ?? null;

            // Clean up extension for complex types like docx, xlsx
            $mimeMap = [
                'vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
                'msword' => 'doc',
                'vnd.ms-excel' => 'xls',
                'vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
                'plain' => 'txt',
                'pdf' => 'pdf',
                'png' => 'png',
                'jpeg' => 'jpg',
                'jpg' => 'jpg',
                'webp' => 'webp',
                'gif' => 'gif'
            ];

            if (isset($mimeMap[$extension])) {
                $extension = $mimeMap[$extension];
            }

            if (!$extension) {
                return response()->json([
                    'file_url' => '',
                    'message' => 'Unsupported or unknown file type: ' . $mimeType,
                    'status' => 400
                ]);
            }

            // Decode base64
            $decodedFile = base64_decode($base64Str);
            if (!$decodedFile) {
                return response()->json([
                    'file_url' => '',
                    'message' => 'Failed to decode file.',
                    'status' => 400
                ]);
            }

            // Ensure directory exists
            $directory = public_path('images/documents');
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }

            // Save file
            $fileName = time() . '_' . uniqid() . '.' . $extension;
            $filePath = $directory . '/' . $fileName;
            file_put_contents($filePath, $decodedFile);

            // Return the file URL
            return response()->json([
                'file_url' => asset('images/documents/' . $fileName),
                'message' => 'File uploaded successfully.',
                'status' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'file_url' => '',
                'message' => 'Server error: ' . $e->getMessage(),
                'status' => 500
            ]);
        }
    }


    public function delete_category(Request $request) {
        $categoryId = $request->json('id');
    
        if (!$categoryId) {
            return response()->json([
                'message' => 'Category ID is required.',
                'status' => 400
            ]);
        }
    
        $category = Categories::find($categoryId);
    
        if (!$category) {
            return response()->json([
                'message' => 'Category not found.',
                'status' => 404
            ]);
        }
    
        // Soft delete the category
        $category->delete();
    
        return response()->json([
            'message' => 'Category deleted successfully (soft delete).',
            'status' => 200
        ]);
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
            'meta_description' => 'nullable', 
            // 'faqs'         => 'nullable',
            'content'      => 'nullable',
            'industry_id'  => 'nullable|integer',
            'material_id'  => 'nullable|integer',
            'style_id'     => 'nullable|integer',
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
            'meta_description' => $validatedData['meta_description'] ?? "",
            // 'faqs'         => $validatedData['faqs'] ?? "",
            'content'      => $validatedData['content'] ?? "",
            'industry_id'  => $validatedData['industry_id']?? "0",
            'material_id'  => $validatedData['material_id']?? "0",
            'style_id'     => $validatedData['style_id']?? "0",
            // 'image_1'      => $validatedData['image_1'] ?? "",
            // 'image_2'      => $validatedData['image_2'] ?? "",
            // 'image_3'      => $validatedData['image_3'] ?? "",
            // 'image_4'      => $validatedData['image_4'] ?? "",
            // 'image_5'      => $validatedData['image_5'] ?? "",
            'status'       => $validatedData['status'] ?? "active",
        ];

        // if (!empty($validatedData['images'])) {
        //     foreach ($validatedData['images'] as $index => $imageUrl) {
        //         if ($index == 0) {
        //             $productData['image_1'] = $imageUrl;
        //         } elseif ($index == 1) {
        //             $productData['image_2'] = $imageUrl;
        //         } elseif ($index == 2) {
        //             $productData['image_3'] = $imageUrl;
        //         } elseif ($index == 3) {
        //             $productData['image_4'] = $imageUrl;
        //         }
        //     }
        // }
        if (!empty($validatedData['images'])) {
            if (!empty($validatedData['id'])) {
                $product = Products::findOrFail($validatedData['id']);
        
                // Existing product images
                $oldImages = [
                    'image_1' => $product->image_1,
                    'image_2' => $product->image_2,
                    'image_3' => $product->image_3,
                    'image_4' => $product->image_4,
                ];
        
                // Loop through images and assign them
                foreach ($validatedData['images'] as $index => $imageUrl) {
                    $imageKey = 'image_' . ($index + 1);
        
                    // Delete old image if it's not in the new images array
                    if (!empty($oldImages[$imageKey]) && !in_array($oldImages[$imageKey], $validatedData['images'])) {
                        $relativePath = str_replace(asset('/'), '', $oldImages[$imageKey]);
                        $filePath = public_path($relativePath);
        
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }
        
                    // Update the product data with new image
                    $productData[$imageKey] = $imageUrl;
                }

                if (!empty($product->image_5) && !empty($validatedData['image_5']) && $product->image_5 !== $validatedData['image_5']) {
                    $relativePath = str_replace(asset('/'), '', $product->image_5);
                    $filePath = public_path($relativePath);
    
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
                if (!empty($validatedData['image_5'])) {
                    $productData['image_5'] = $validatedData['image_5'];
                }
            } else {
                // If it's a new product, just assign images
                foreach ($validatedData['images'] as $index => $imageUrl) {
                    $productData['image_' . ($index + 1)] = $imageUrl;
                }
                $productData['image_5'] = $validatedData['image_5'];
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
        $responseProduct = $product->fresh()->toArray();
        unset($responseProduct['image_1'], $responseProduct['image_2'], $responseProduct['image_3'], $responseProduct['image_4']);
        $responseProduct['images'] = $validatedData['images'];
        
        return response()->json([
            'message' => $message,
            'product' => $responseProduct,
            'status' => 200
        ]);
    }
    public function delete_product(Request $request) {
        $productid = $request->json('id');
    
        if (!$productid) {
            return response()->json([
                'message' => 'Product ID is required.',
                'status' => 400
            ]);
        }
    
        $product = Products::find($productid);
    
        if (!$product) {
            return response()->json([
                'message' => 'Product not found.',
                'status' => 404
            ]);
        }
    
        // Soft delete the product
        $product->delete();
    
        return response()->json([
            'message' => 'Product deleted successfully (soft delete).',
            'status' => 200
        ]);
    }
    
    public function admin_get_products() {
        $products = Products::orderBy('id', 'desc')->get()->map(function ($product) {
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
    public function toggleproduct(Request $request) {  
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
                $ALL['contact_us'][] = $request->only(['id','email','phone','name', 'status','description']);
            }
            if($request->type == 'subscribe'){
                $ALL['subscribe'][] = $request->only(['id','email', 'status','name']);
            }
        }

        return response()->json([ 
            // 'message' => 'Poducts updated successfully', 
            "quote" => $ALL['quote'],
            "contact_us" => $ALL['contact_us'],
            "subscribe" => $ALL['subscribe'],
            'status' => 200
        ]); 


    }
    public function changeformstatus(Request $request){
        $form_id = $request->json('id');

        if (!$form_id) {
            return response()->json([
                'message' => 'Category ID is required.',
                'status' => 200
            ], 400);
        }
    
        $from = RequestModel::find($form_id);

        $from->status = $from->status === 'read' ? 'unread' : 'read';
        $from->save(); 

        return response()->json([
            'message' => 'status_update_successfully',
            // 'product' => $responseProduct,
            'status' => 200
        ]);
    
    }
    public function create_update_blog(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'nullable',
            'title' => 'required',
            'image' => 'required',
            'content' => 'nullable|string',
            'meta_description' => 'nullable',
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
            'meta_description' => $request->meta_description,
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
    public function getadminBlogs(){
        $blogs = Blog::all(); 

        return response()->json([
            'status' => 200,
            'blogs' => $blogs
        ]);
    }
    public function toggleblog(Request $request) {  
        $request->validate([ 
            'id' => 'required',
            ]);
        $Blog = Blog::find($request->id); 
        // Toggle value in database 
        $Blog->status = $Blog->status == "active" ? "inactive" : "active"; 
        $Blog->save(); 
        return response()->json([ 
            'message' => 'Blog updated successfully', 
            'status' => 200
        ]); 
    }
    public function getBlogs(){
        $blogs = Blog::where('status', 'active') // Only active blogs
                     ->orderBy('sorting', 'asc') // Sorting
                     ->get(['id', 'title', 'image']); // Fetch only title and image

        return response()->json([
            'status' => 200,
            'blogs' => $blogs
        ]);
    }
    public function getBlogById(Request $request){
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
    public function deleteBlog(Request $request){
        $id = $request->json('id');

        $blog = Blog::find($id);

        if (!$blog) {
            return response()->json(['message' => 'Blog not found','status' => 200], 404);
        }

        $url = $blog->image;
        $relativePath = str_replace(asset('/'), '', $url);
        $filePath = public_path($relativePath);

        if (file_exists($filePath)) {
            unlink($filePath); 
        }

        $blog->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Blog deleted successfully.'
        ]);
    }
    public function create_update_portfolio(Request $request){
        $imageData = $request->input('image');
        
        if (!$imageData) {
            return response()->json([
                'image_url' => '',
                'message' => 'No image provided.',
                'status' => 200
            ], 400);
        }
    
       
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
            $image_url = asset('images/' . $imageName);
    
            // return response()->json([
            //     'image_url' => asset('images/' . $imageName),
            //     'message' => 'Image uploaded successfully.',
            //     'status' => 200
            // ]);
    
        // $validator = Validator::make($request->all(), [
        //     'id' => 'nullable|integer',
        //     'image' => 'required|string', // Assuming image URL or filename
        // ]);

        // if ($validator->fails()) {
        //     return response()->json(['errors' => $validator->errors(),'status'=>200], 422);
        // }

        $portfolioData = [
            'image' => $image_url, 
        ];

        // if (!empty($request->id)) {
        //     $portfolio = Portfolio::find($request->id);
        //     if (!$portfolio) {
        //         return response()->json(['message' => 'Portfolio not found','status' => 200,], 404);
        //     }
        //     $portfolio->update($portfolioData);
        //     $message = 'Portfolio updated successfully.';
        // } else {
            $portfolio = Portfolio::create($portfolioData);
            $message = 'Portfolio created successfully.';
        // }

        return response()->json([
            'status' => 200,
            'message' => $message,
            'portfolio' => $portfolio
        ]);
    }
    public function getPortfolios(){
        $portfolios = Portfolio::all();
        return response()->json([
            'status' => 200,
            'portfolios' => $portfolios
        ]);
    }
    public function deletePortfolio(Request $request){
        $id = $request->json('id');

        $portfolio = Portfolio::find($id);
        if (!$portfolio) {
            return response()->json(['message' => 'Portfolio not found','status' => 200], 404);
        }

        $url = $portfolio->image;
        $relativePath = str_replace(asset('/'), '', $url);
        $filePath = public_path($relativePath);

        if (file_exists($filePath)) {
            unlink($filePath); 
        }

        $portfolio->delete();
        return response()->json([
            'status' => 200,
            'message' => 'Portfolio deleted successfully.'
        ]);
    }
    public function deleteimages(Request $request){
        $request->validate([ 
            'url' => 'required',
        ]);

        $url = $request->url;
        $relativePath = str_replace(asset('/'), '', $url);
        $filePath = public_path($relativePath);

        if (file_exists($filePath)) {
            unlink($filePath); 
            return response()->json([
                'status' => 200,
                'message' => 'Image deleted successfully.'
            ],200);
        } else {
            return response()->json([
                'status' => 200,
                'message' => 'File not found.'
            ],404);
        }
    
    }
    public function get_category_product(){

        $navbarData = Categories::select('id', 'title','nav_id')
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
        ];
        return response()->json($response);
    }
    public function sort_the_categories(Request $request)
    {
        $request->validate([
            'sort_array' => 'required|array', // Ensure it's an array
        ]);

        foreach ($request->sort_array as $index => $id) {
            Categories::where('id', $id)->update(['sorting' => $index + 1]);
        }

        return response()->json(['message' => 'Categories sorted successfully','success' => true,'status' => 200]);
    }
    public function get_unread_status()
    {
        $hasUnread = RequestModel::where('status', 'unread')->exists(); // Check if any row has 'unread' status
    
        return response()->json([
            'message' => 'Checked unread status successfully',
            'success' => true,
            'status' => 200,
            'unread' => $hasUnread, // true if at least one 'unread' status exists, otherwise false
        ]);
    }

    public function force_delete_all_products() {
        $products = Products::onlyTrashed()->get();
    
        if ($products->isEmpty()) {
            return response()->json([
                'message' => 'No soft-deleted products found.',
                'status' => 404
            ]);
        }
    
        foreach ($products as $product) {
            $imageFields = ['image_1', 'image_2', 'image_3', 'image_4', 'image_5'];
    
            foreach ($imageFields as $field) {
                if (!empty($product->$field)) {
                    $url = $product->$field;
                    $relativePath = str_replace(asset('/'), '', $url);
                    $filePath = public_path($relativePath);
    
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
            }
    
            // Permanently delete the product
            $product->forceDelete();
        }
    
        return response()->json([
            'message' => 'All soft-deleted products and their associated images permanently deleted.',
            'status' => 200
        ]);
    }
    public function force_delete_all_categories() {
        // Get all soft-deleted categories
        $categories = Categories::onlyTrashed()->get();
    
        if ($categories->isEmpty()) {
            return response()->json([
                'message' => 'No soft-deleted categories found.',
                'status' => 404
            ]);
        }
    
        foreach ($categories as $category) {
            $imageFields = ['header_img', 'main_img', 'icon'];
    
            foreach ($imageFields as $field) {
                if (!empty($category->$field)) {
                    $url = $category->$field;
                    $relativePath = str_replace(asset('/'), '', $url);
                    $filePath = public_path($relativePath);
    
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
            }
    
            // Permanently delete the category
            $category->forceDelete();
        }
    
        return response()->json([
            'message' => 'All soft-deleted categories and associated images permanently deleted.',
            'status' => 200
        ]);
    }

}

