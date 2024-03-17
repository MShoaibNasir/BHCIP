<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendCodeResetPassword;
use Illuminate\Support\Facades\Password;
use App\Models\ResetCodePassword;
use App\Models\User;
use App\Models\Quiz;
use App\Models\Topic;
use App\Models\Question;

class ForgetPasswordController extends Controller
{
    function send_code(Request $request){
         //validate credentials
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|string|max:255|exists:users'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->messages()], 400);
        }
        try{
       
        $data = [
            'email'=> $request->email,
            'code' => mt_rand(1000, 9999),
            'created_at' => now(),
        ];
        
        
        
        ResetCodePassword::where('email', $request->email)->delete();
        $codeData = ResetCodePassword::create($data);

        Mail::to($request->email)->send(new SendCodeResetPassword($codeData->code));
       
        sendSuccessRespose(trans('passwords.sent'));

        }
         catch (Exception $exception) {
            return response()->json(['status' => 'false', 'message' =>'failed','error'=>$e],500);
        }
    }
    
    public function CodeCheck(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'code' => 'required|string|exists:reset_code_passwords',
        ]);

        if ($validation->fails()) {
            return response()->json(['status'=>false,'message'=>'Validation Fail','error' => $validation->errors()],401);
        }
        $passwordReset = ResetCodePassword::firstWhere('code', $request->code);
        if ($passwordReset->isExpire()) {
            sendFailResponse('Failed',trans('passwords.code_is_expire'));
        }
        $res = array('code' => $passwordReset->code);
        sendSuccessRespose(trans('passwords.code_is_valid'),$res);
    }
    
      public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'password' => 'required|string|confirmed',

        ]);

        if ($validator->fails()) {
            return response()->json(['status'=>false,'message'=>'Validation Fail','error' => $validator->errors()],401);
        }
        
        $passwordReset = ResetCodePassword::firstWhere('code', $request->code);
        
        if(!empty($passwordReset)){
            if ($passwordReset->isExpire()) {
                return response()->json(['message' => trans('passwords.code_is_expire')], 422);
            }
            $user = User::firstWhere('email', $passwordReset->email);
           
            $user->password = $request->password;
            $user->save();
            $passwordReset->delete();
            sendSuccessRespose('Password has been successfully reset');
        }
            sendFailResponse('Invalid OTP');

        }
        
        public function gettopicbylevelIDTest(Request $request,$category_id,$level_id){
           
           $quiz_w_r_t_category = Quiz::where('category_id',$category_id)->where('level_id',$level_id)->get();
           $count_quizes= count($quiz_w_r_t_category);
           if($count_quizes>0){
               foreach($quiz_w_r_t_category as $quizes){
                //   $Question=Question::where('quiz_id',intval($quizes->quiz_id))->get();
                   $topic=Tpoic::where('id',$quizes->topic_id)->first();
                   return $topic; 
                   die;
                   return $quiz_w_r_t_category;
                   die;
                   $quiz_w_r_t_category['Quiz_question']=$Question['question'];
                   
               }
           }
           return $quiz_w_r_t_category;
           die;
           
           
           

    }
}
