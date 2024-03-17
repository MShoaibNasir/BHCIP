<?php

namespace App\Http\Controllers\admin\ManageUser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Category;
use DB;
class AppUserController extends Controller
{
    public function __construct()
    {
      //  $this->middleware('auth:admin');
    }

    public function getAppUser($id=''){
      
        if(!empty($id)){
             $data = User::LeftJoin('category','category.id','=','users.category_id')
                         ->LeftJoin('district','district.district_id','=','users.district_id')
                         ->LeftJoin('facility','facility.id','=','users.facility_id')
                    ->where('users.id',$id)->select('users.*','category.name as category_name',
                    'district.name as district_name','facility.id as facility_id','facility.name as facility_name')->first();
                    
            $performance = DB::select("SELECT count(quiz.topic_id) as tot_topic,count(case when percent >= 75 then 1 end) as tot_pass, 
                            count(case when percent <= 75 then 1 end) as tot_fail, count(DISTINCT quiz_result.quiz_id) as tot_quiz
                            FROM quiz_result LEFT join quiz on quiz.quiz_id = quiz_result.quiz_id 
                             WHERE result_id IN(SELECT max(result_id) FROM quiz_result where user_id = '$id' GROUP by quiz_result.quiz_id);");
            
             $result = DB::select("SELECT quiz_name,quiz.quiz_id,percent FROM quiz_result join quiz on quiz.quiz_id = quiz_result.quiz_id
                         WHERE result_id IN(SELECT max(result_id) FROM quiz_result where user_id = '$id' GROUP by quiz_id)");
                            
            $activity = DB::table('user_activity')->where('user_id',$id)->orderBy('id','DESC')->paginate(10);
            
            $attachment = DB::table('user_attachments')->select('file_name')->where('user_id',$id)->orderBy('id','DESC')->first();

            return view('admin.resgisterUser.view',["data"=>$data,"activity"=>$activity,"attachment"=>$attachment,'performance'=>$performance,'results' =>$result]);
        }
        
        $user_list = User::where('role', 3)
        ->orderBy('created_at', 'desc')
        ->get();
        
        
      
      
     
        return view('admin.resgisterUser.user_list', compact('user_list'));
    }
    
    
    /**
     * Show the form for editing the specified resource.
     */
    public function editAppUser(string $id)
    {
        $data = User::LeftJoin('category','category.id','=','users.category_id')
                    ->where('users.id',$id)->select('users.*','category.name as category_name')->first();
        $district = getDistrict();
        $category = Category::all();
        return view('admin.resgisterUser.edit',["data"=>$data,"district"=>$district,"category"=>$category]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateUserStatus(Request $request)
    {
       
      if(empty($request->id) || !isset($request->status)){
          return response()->json(['success'=>true,'msg'=>'Something Went Wrong'],400);
      }
        $update_data = array(
            'status'       => $request->status,
            'updated_at'   => date('Y-m-d H:i:s')
            );

        User::where('id', $request->id)->update($update_data);
        return response()->json(['success'=>true,'msg'=>'Status Updated !'],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function deleteAppUser(string $id)
    {
      $user = User::find($id);
      $user->delete();

        return  redirect(route('app.user'))->with('msg', 'Deleted Successfully');
    }
    
     public function updateAppUser(Request $request)
    {
    
        $update_data = array(
        'first_name'   => $request->first_name,
        'last_name'    => $request->last_name,
        'email'        => $request->email,  
        'contact_no'   => $request->contact_no,
        'district_id'  => $request->district_id,
        'category_id'  => $request->category_id,
        'status'       => $request->user_status== "on" ? 1:0 ,
        'updated_at'   => date('Y-m-d H:i:s')
        );

        User::where('id', $request->id)->update($update_data);
        return  redirect(route('app.user'))->with('msg', 'Update Successfully');

    }
    
    public function userPerformance($id){
            $data = User::LeftJoin('category','category.id','=','users.category_id')
                    ->where('users.id',$id)->select('users.*','category.name as category_name')->first();
                    
            $performance = DB::select("SELECT level.level_name , topic.topic_name,quiz_name,quiz.quiz_id,quiz_result.total_question,
                               quiz_result.total_attempt,quiz_result.correct_answer,quiz_result.wrong_answer,percent
                               FROM quiz_result join quiz on quiz.quiz_id = quiz_result.quiz_id 
                               join level on level.level_id = quiz.level_id 
                               join topic on topic.id = quiz.topic_id 
                               WHERE result_id IN(SELECT max(result_id) FROM quiz_result where user_id = $id GROUP by quiz_id);");
            
        return view('admin.resgisterUser.quiz_detail',compact('performance','data'));
    }
}
