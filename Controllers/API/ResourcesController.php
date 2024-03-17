<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class ResourcesController extends Controller
{
    public function get_district(Request $request)
    {
        $id = $request->id  ?? '';
        $district = getDistrict($id);
        sendSuccessRespose('success', $district);
    }

    public function get_facility(Request $request)
    {
        $district_id = $request->district_id  ?? '';
        $facility = getFacility($district_id);
        sendSuccessRespose('success', $facility);
    }
    
    public function get_category(Request $request)
    {  
        if($request->id){
            $category = Category::select('id','name')->where('id',$request->id)->get();
        }else{
            $category = Category::select('id','name')->get();
        }
        sendSuccessRespose('success', $category);
    }
    
    public function add_user_activity_log(Request $request){
    
        try{
        DB::table('user_activity')->insert($request);
        sendSuccessResponse('success');
        }
        catch(Exception $e){
             return  response()->json(['status' => false, 'msg' => 'get_quiz_failed', 'error' => $e], 500);
        }
            
        }
        
        
        
  
    
    
    
    
    
    
    
      
}
