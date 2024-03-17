<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use DB;
use App\Models\Category;
use Illuminate\Support\Str;

class Authentication extends Controller
{
    public function __construct() {
        $this->middleware('auth:api' ,  ['only'=>['logout','userUpdate','changePassword']]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email','password');
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

          
        if($validator->fails()){
            return response()->json(['status'=>false,'message'=>'Validation Fail','error' => $validator->messages()],200);
        }
       
         try {
            if(!$token  = JWTAuth::attempt($credentials)){
                $user = User::where('email',$request->email)->first();
             
                if(!$user){
                    
                    // sendFailResponse('invalid credential', 'unauthorized');
                        return  response()->json(['status' => false, 'message' =>'invalid credential'],402);
                }
                
                if(!$user->status==1){
                    return  response()->json(['status' => false, 'message' =>'Your Account Is Not Active'],402);
                }

                if (!Hash::check($credentials['password'], $user->password)) {
                     return  response()->json(['status' => false, 'message' =>'Login credential is invalid'],402);
                    
                }
                $token =  JWTAuth::fromUser($user);
            }
        } catch (JWTException $e) {
           return  response()->json(['status' => false, 'message' =>'login_failed','error'=>$e],500);
        }
       
        $user = User::where('email',$request->email)->first();
    
        if(!$user){
            
            sendFailResponse('invalid credential', 'Unauthorized');
        }
        if(!$user->status==1){
            
               return  response()->json(['status' => false, 'message' =>'Your Account Is Not Active'],402);
        }
        $category=Category::where('id',$user->category_id)->first();
        if ($category) {
            $categoryName = $category->name;
            }
       //Token created, return with success response and jwt token
        $user   = JWTAuth::user();
        $response = $user;
        $user['categoryName']=$categoryName;
        $response['token'] = $token;
      
        return response()->json(['status'=>true, 'message' => 'Authentication successful.','response'=> $response], 200);
    }

    public function register(Request $request)
    {
       
        //validate credentials
        $validator = Validator::make($request->all(), [
            'first_name' =>'required|regex:/^[\pL\s\-]+$/u|max:100',
            'last_name' => 'required|regex:/^[\pL\s\-]+$/u|max:100',
            'contact_no' => 'required|min:11|numeric|unique:users',
            'district_id' => 'required',
            'category_id'  =>'required',
            'facility_id'  =>'required',
            'email' => 'required|email|string|max:255|unique:users',
            'password' => 'required|string|confirmed'
        ], [
            'first_name.regex' => 'First name only contains Alphabets',
            'last_name.regex' => 'Last name only contains Alphabets',
]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->messages()->toArray(), 'error' => $validator->messages()], 200);
        }
        try {
            $insert_data = $request->all();
            $category=Category::where('id',$insert_data['category_id'])->first();
            if ($category) {
               $categoryName = $category->name;
             }
               
 
            $insert_data['password'] = bcrypt($request->password);
            $insert_data['role'] = 3;//app user
            
           
            
            
            $user_data = User::create($insert_data);
            $user_data['token'] = JWTAuth::fromUser($user_data);
            $user_data['categoryName'] = $categoryName;
         

        } catch (\Exception $e) {
            return  response()->json(['status' => false, 'message' => 'registration_failed', 'error' => $e], 500);
        }
       
        
        //Token created, return with success response and jwt token
        sendSuccessRespose('Register successful.', $user_data);
    }

    public function logout(Request $request){
        $token = $request->header('Authorization');
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            sendSuccessRespose('You logged out successfully');
        } catch (JWTException $e) {
            return response()->json(['status' => false, 'message'=>'logout_failed','error' => 'Failed to logout, please try again'], 500);

        }

    }
    
      public function changePassword(Request $request)
    {
        //validate credentials
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|string|max:255',
            'password' => 'required|string|min:5|confirmed',
            'old_password' =>'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->messages()], 200);
        }
         $get_user = User::where('email',$request->email)->first();
                if(!$get_user) {
                    sendFailResponse('invalid credential', 'Unauthorized');
                }
                if (!Hash::check($request->old_password, $get_user->password)) {
                    sendFailResponse('Invalid Current Password');return true;
                }
        $user = User::where('email',$request->email)->update(['password'=>bcrypt($request->password)]);
         if($user){
                sendSuccessRespose('Password Updated');
         }else{
                sendFailResponse('Unable To Update Passsword');
         }
    }
    
      public function userUpdate(Request $request)
    {
           
        try{
               
            $user   = JWTAuth:: user();
            $user_id = $user->id;
            $user_data = $request->all();
           
            // if(!empty($request->profile_image)){
            //     $image_64 = $request->profile_image;
            //     $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1]; // .jpg .png .pdf
            //     $replace = substr($image_64, 0, strpos($image_64, ',') + 1); 
            //     $image = str_replace($replace, '', $image_64);
            //     $image = str_replace(' ', '+', $image);
            //     $imageName = time().'.'. $extension;
            //     Storage::disk('public')->put('/uploads/user/'.$user_id.'/' . $imageName, base64_decode($image));  
            //     $user_data['profile_image'] = $imageName;
            // }
            
            // if($request->profile_image){
            // $image_64 = $request->profile_image; //your base64 encoded data
            // $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1]; // .jpg .png .pdf
            // $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
            // $image = str_replace($replace, '', $image_64);
            // $image = str_replace(' ', '+', $image);
            // $imageName = Str::random(10) . '.' . $extension;
            // Storage::disk('public')->put('/images/profiles/' . $imageName, base64_decode($image));
            // $user_data['profile_image']=$imageName;
            // }
            
            
             if ($image_64 = $request->profile_image) {
                $extension = 'png';
                $image = $image_64;
                $imageName = Str::random(10) . '.' . $extension;
                Storage::disk('public')->put('/images/profiles/' . $imageName, base64_decode($image));
                $user_data['profile_image']=$imageName;
        }
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
            
            
            
            
            
            
            
            
             if(!empty($request->cnic_doc)){
                // $image_64 = $request->cnic_doc;
                // $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1]; // .jpg .png .pdf
                // $replace = substr($image_64, 0, strpos($image_64, ',') + 1); 
                // $image = str_replace($replace, '', $image_64);
                // $image = str_replace(' ', '+', $image);
                // $imageName = 'cnic_'.time().'.'. $extension;
                // Storage::disk('public')->put('/uploads/user/'.$user_id.'/' . $imageName, base64_decode($image));  
                
                $image_64 =$request->cnic_doc;
                $extension = '.png';
                $image = $image_64;
                $imageName = Str::random(10) . '.' . $extension.time();
                Storage::disk('public')->put('/uploads/user/' . $imageName, base64_decode($image));
                
                
                
                
                DB::table('user_attachments')->insert(
                    array(
                        'user_id'   => $user_id,
                        'document'  => 'cnic',
                        'file_name' => $imageName,
                        'created_at' => date('Y-m-d H:i:s')
                        )
                    );
             
            }
            
            $user_update = User::find($user_id);
            if($user_update){
            $category=Category::where('id',$user_update['category_id'])->first();
            if ($category) {
               $user_update['categoryName'] = $category->name;
             }                
            }
            $user_update->fill($user_data);
            $user_update->save();
            if($user_update){
                sendSuccessRespose('User Updated',$user_update);
            }else {
                sendFailResponse('Unable To Update User');
            }
        }
        catch(Exception $e){
            return response()->json(['status' => false, 'message'=>'update_failed','error' => 'Failed to Update, please try again'], 500);
        }
    }
      public function deleteUser(Request $request,$id)
    {
        $user=User::find($id);
        if(!$user){
         return response()->json(['status' => false, 'message'=>'user not found'], 400);
        }
        if($user->delete()){
         return response()->json(['status' => true, 'message'=>'user delete successfully'], 200);   
        }
    }
    
    
    public function uploadImage(Request $request,$id){


        $user_data = $request->all();
        if ($request->profile_image) {
        $image_64 = $request->profile_image; // Your base64 encoded data
        $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1]; // .jpg .png .pdf
        $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
        $image = str_replace($replace, '', $image_64);
        $image = str_replace(' ', '+', $image);
        $imageName = Str::random(10) . '.' . $extension;
        
        // Get the absolute path to the public directory
        $publicPath = public_path();
        
        // Save the image in the public/uploads/user/ directory
        file_put_contents($publicPath . '/uploads/user/' . $imageName, base64_decode($image));
        
        // Save the image filename in the user_data array
        $user_data['profile_image'] = $imageName;
        }


            $user_update = User::find($user_id);
           
            $user_update->fill($user_data);
            $user_update->save();    
            
            
    }
    
}

