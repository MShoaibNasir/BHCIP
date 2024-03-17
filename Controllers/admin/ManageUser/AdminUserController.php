<?php

namespace App\Http\Controllers\admin\ManageUser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Category;
use DB;
class AdminUserController extends Controller
{
    public function __construct()
    {
      //  $this->middleware('auth:admin');
    }

    public function getsubAdminUser($id=''){
        if(!empty($id)){
             $data = User::LeftJoin('category','category.id','=','users.category_id')
                         ->LeftJoin('district','district.district_id','=','users.district_id')
                         ->LeftJoin('facility','facility.id','=','users.facility_id')
                    ->where('users.id',$id)->select('users.*','category.name as category_name',
                    'district.name as district_name','facility.id as facility_id','facility.name as facility_name')->first();
            $permission = '- - - -';
            if($data->permission_arr){
              $arr = json_decode($data->permission_arr);
              $all_permission = \Config::get('constant.admin_permission');
                foreach($arr as $array){
                    $permissions[] = $all_permission[$array];
                }
                $permission = implode(" , ",$permissions);
            }
            $activity = DB::table('admin_log')->where('user_id',$id)->orderBy('id','DESC')->paginate(10);
            return view('admin.adminUser.view',["data"=>$data,"activity"=>$activity,"permission"=>$permission]);
        }
        $user_list = User::where('role',2)->paginate(15);
        return view('admin.adminUser.user_list', compact('user_list'));
    }
      /**
     * create Sub admin.
     */
     
      public function createsubAdminUser()
    {
        return view('admin.adminUser.register');
    }
    
    public function storesubAdminUser(Request $request)
    {
         validator::make($request->all(),[
            'first_name' =>'required|regex:/^[\pL\s\-]+$/u|max:100',
            'last_name' => 'required|regex:/^[\pL\s\-]+$/u|max:100',
            'contact_no' => 'required|min:11|numeric|unique:users',
            'email' => 'required|email|string|max:255|unique:users',
            'password' => 'required|string|confirmed',
            'permission' =>'required'
            ])->validate();
            
           $insert_data = $request->all();
            if(!empty($request->permission)){
              $insert_data['permission_arr'] = '["'.implode('","',$request->permission).'"]';
            } 
            
            $insert_data['password'] = bcrypt($request->password);
            $insert_data['role'] = 2;//sub admin
            $insert_data['status'] = 1;
         
            $user_data = User::create($insert_data);
            if($user_data){
                 $request->session()->flash('msg', 'Added Successfully');
                 return  redirect(route('subAdmin.user'));
            }else{
                 $request->session()->flash('success', 'Added Failed');
                 return  redirect(route('subAdmin.create'));
            }
                
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function editsubAdminUser(string $id)
    {
        $data = User::LeftJoin('category','category.id','=','users.category_id')
                    ->where('users.id',$id)->select('users.*','category.name as category_name')->first();
        $district = getDistrict();
        $category = Category::all();
        return view('admin.adminUser.edit',["data"=>$data,"district"=>$district,"category"=>$category]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function updatesubAdminStatus(Request $request)
    {
        
        
     
      if(empty($request->id) || !isset($request->status)){
          return response()->json(['success'=>true,'msg'=>'Something Went Wrong'],400);
      }
        $update_data = array(
            'status'       => intval($request->status),
            'updated_at'   => date('Y-m-d H:i:s')
            );

        User::where('id', $request->id)->update($update_data);
        return response()->json(['success'=>true,'msg'=>'Status Updated !'],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function deletesubAdminUser(string $id)
    {
      $user = User::find($id);
      $user->delete();

        return  redirect(route('subAdmin.user'))->with('msg', 'Deleted Successfully');
    }
    
     public function updatesubAdminUser(Request $request)
    {
      validator::make($request->all(),[
            'first_name' =>'required|regex:/^[\pL\s\-]+$/u|max:100',
            'last_name' => 'required|regex:/^[\pL\s\-]+$/u|max:100',
            'contact_no' => 'required|min:11|numeric',
            'email' => 'required|email|string|max:255',
            'permission' =>'required'
            ])->validate();
        
        if(!empty($request->permission)){
          $permission = '["'.implode('","',$request->permission).'"]';
        } 
            
        $update_data = array(
        'first_name'   => $request->first_name,
        'last_name'    => $request->last_name,
        'email'        => $request->email,  
        'contact_no'   => $request->contact_no,
        'status'       => $request->user_status== "on" ? 1:0 ,
        'permission_arr' => $permission,
        'updated_at'   => date('Y-m-d H:i:s')
        );
        
        $updated = User::where('id', $request->id)->update($update_data);
        if($updated){
            return  redirect(route('subAdmin.user'))->with('msg', 'Update Successfully');
        }else{
            return  redirect(route('subAdmin.user'))->with('error', 'Update Failed');
        }
    }
}