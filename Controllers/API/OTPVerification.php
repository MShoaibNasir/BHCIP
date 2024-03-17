<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\sendOTPCode;
use Illuminate\Support\Facades\Password;
use App\Models\VerificationCode;
use App\Models\User;

class OTPVerification extends Controller
{
    function send_otp_code(Request $request){
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
        
        VerificationCode::where('email', $request->email)->delete();
        $codeData = VerificationCode::create($data);

        Mail::to($request->email)->send(new sendOTPCode($codeData->code));
       
        sendSuccessRespose('OTP has been send to your email');

        }
         catch (Exception $exception) {
            return response()->json(['status' => 'false', 'message' =>'failed','error'=>$e],500);
        }
    }
    
    public function CodeOTPCheck(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'code' => 'required|string|exists:verification_codes',
        ]);

        if ($validation->fails()) {
            return response()->json(['status'=>false,'message'=>'Validation Fail','error' => $validation->errors()],401);
        }
        $passwordReset = VerificationCode::firstWhere('code', $request->code);
        if ($passwordReset->isExpire()) {
            sendFailResponse('Failed','OTP Code is Expired');
        }
        $res = array('code' => $passwordReset->code);
        sendSuccessRespose('Code is Valid',$res);
    }
    
}