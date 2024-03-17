<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Topic;
use App\Models\level;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\QuizResponse;
use DB;
use App\Models\User;
class QuizController extends Controller
{
    public function get_quiz(Request $request){
    try{
if(intval($request->category_id)==8){
 $quizs =  Quiz::with('QuizQuestions.QuestionAnswer.QuestionOption')
                      ->where('quiz.category_id',$request->category_id)
                      ->where('quiz.level_id',$request->level_id)
                      ->where('quiz.topic_id',$request->topic_id)
                      ->get(); 
       
        }else{
        $quizs =  Quiz::with('QuizQuestions.QuestionAnswer.QuestionOption')
                    //   ->select('`quiz`.`quiz_name`','`quiz_questions`.`question_id`','`quiz_questions`.`question`','`quiz_questions`.`question_type`','`question_answer`.`answer_description`','`question_option`.option_description')
                      ->where('quiz.category_id',$request->category_id)
                      ->where('quiz.level_id',$request->level_id)
                      ->where('quiz.topic_id',$request->topic_id)
                      ->groupBy('quiz.quiz_id')
                      ->get();  
                      
        }
        if($quizs->count() > 0){
            $all_questions = array();
            foreach($quizs as $quiz){
                $video_data = $quiz['video'];
                $question_detail["quiz_id"] = $quiz->quiz_id;
                $Topic_name=Topic::where('id',$quiz['topic_id'])->first();
                $question_detail['cat_id']=$quiz->category_id;

                $question_detail['topic_name']=$Topic_name;
               
                foreach($quiz->QuizQuestions as $question){
                    $option = array();
                    $question_detail["question_id"] = $question->question_id;
                    $question_detail["question"] = $question->question;
                    $question_detail["question_type"] = $question->question_type;

                         
                      foreach($question->QuestionAnswer as $ans){
                        $question_detail["answer"] = $ans->answer_description;
                        }
                       
                      foreach($question->QuestionOption as $options){
                        $option[] = $options->option_description;
                        $question_detail["options"] =  $option;
                        }
                        $all_questions[] = $question_detail;
                 
                   
                }
              
            }
             $response['quiz_video'] = $video_data;
             $response['quiz_question'] = $all_questions;
             sendSuccessRespose('success', $response);
            
        }//if
        else{
            sendSuccessRespose('success','No Quiz Found');
           
        }
        
        }catch(Exception $e){
             return  response()->json(['status' => false, 'msg' => 'get_quiz_failed', 'error' => $e], 500);
        }
        
    }
    
    function save_quiz_response(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'quiz_id' => 'required',
            'quiz_response' => 'required',
            'status'        => 'required'
        ]);
        
        
      
        if($validator->fails()){
            return response()->json(['status'=>false,'message'=>'Validation Fail','error' => $validator->messages()],200);
        }
         try {
            // get the level id
            $level_id= Quiz::select('level_id')->where('quiz_id',$request->quiz_id)->first();
            
            if($level_id){
            // $user = User::where('id', $request->user_id)->update(
            // ['level_status' => intval($level_id->level_id)]
            // );
            
            $levels = [];
            $level_id=intval($level_id->level_id)-1;
            while ( $level_id> 0) {
            $levels[] = "level Completed $level_id";
            $level_id--;
            }
            $levels= json_encode($levels);                
            }else{
            $levels="level is 0";    
            }
            $save_quiz_response = QuizResponse:: updateOrCreate(
                ['user_id'=> $request->user_id,'quiz_id' => $request->quiz_id],
                ['quiz_response' => json_encode($request->quiz_response),'status'=> $request->status,'level_status'=>$levels]
            );
           if($save_quiz_response){
                $response = [
                    'message' => 'Response Save successfully.',
                    'save_quiz_response'=>$save_quiz_response
                ];
                sendSuccessRespose($response);
           }else{
               sendFailResponse('Failed', 'Unable To Save Quiz Response');
           }
             
         } catch (JWTException $e) {
           return  response()->json(['status' => false, 'message' =>'login_failed','error'=>$e],500);
        }
    }
    
    
    function sanitizeString($str) {
    // Remove extra spaces and special characters
    $str = preg_replace('/\s+/', '', $str); // Remove extra spaces
    $str = preg_replace('/[^A-Za-z0-9]/', '', $str); // Remove non-alphanumeric characters
    
    return $str;
}

    
    function old_save_quiz_result(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id'       => 'required',
            'quiz_id'       => 'required',
            'quiz_response' => 'required',
            'watch_time'    => 'required',
            'district_id'    => 'required',
            'category_id'    => 'required'
        ]);
        
        if($validator->fails()){
            return response()->json(['status'=>false,'message'=>'Validation Fail','error' => $validator->messages()],200);
        }
         try {
            $result = array();
            $total_attempt = $total_correct = $total_wrong=$total_question= 0;
            $total_question = Question::where('quiz_id',$request->quiz_id)->count();
            
            $result['user_id'] = $request->user_id;
            $result['quiz_id'] = $request->quiz_id;
            $result['watch_time'] = $request->watch_time;
            $result['district_id'] = $request->district_id;
            $result['category_id'] = $request->category_id;
            $result['total_question'] = $total_question;
            $data = json_decode($request->quiz_response, true);
            $missing_question= $total_question-count($data[0]);
            if($missing_question==-1){
                $total_wrong=0;
            }else{
                $total_wrong=$missing_question;
            }
            $i=0;
            foreach($data[0] as $response){
                // $total_question++;
                
                $actualAnswer = $this->sanitizeString(strtolower($response['actual_answer'])); // Convert to uppercase
                $userAnswer = $this->sanitizeString(strtolower($response['user_answer']));
                
                if(strcasecmp($actualAnswer, $userAnswer) == 0){
                  $total_attempt++;
                  $total_correct++;
                }elseif($response['user_answer']==null || $response['user_answer']==''){
                    $total_wrong++;
                    
                }
                else{
                    $total_attempt++;
                    $total_wrong++;
                    
                }
                $i++;
            }
            $result['correct_answer'] = $total_correct;
            $result['wrong_answer']   = $total_wrong;
            $result['total_question'] = $total_question;    
            $result['total_attempt']  = $total_attempt;
            $result['percent'] = round((($total_correct/$total_question)*100),2);
            $user=User::where('id', $request->user_id)->first();
            $user_level_status= intval($user->level_status);
            $topic_with_respect_to_level=Topic::where('level_id',intval($user_level_status))->where('cat_id',$user->category_id)->count();



            // this topic ids which is save in users tables
            $user_topic_ids=$user->topic_id;
            $user_topic_ids=explode(',',$user_topic_ids);
            array_pop($user_topic_ids);
            if($result['percent']>40){
            $quiz_topic_id=DB::table('quiz')->where('quiz_id',$request->quiz_id)->first();
            $quiz_topic_id=$quiz_topic_id->topic_id;
            if(in_array($quiz_topic_id,$user_topic_ids)){
            } else{
            $user->topic_count+=1;
            $user->topic_id .=$quiz_topic_id.',';
            $user->save();
            }  
            
            if(intval($user->topic_count)>=intval($topic_with_respect_to_level)){
            $user=User::where('id', $request->user_id)->first();
            $user->level_status+=1;
            $user->topic_count=0;
            $user->topic_id =null;
            $user->save();
            }
            }
            $save_quiz_response = DB::table('quiz_result')->insert($result);
              if($save_quiz_response){
                    sendSuccessRespose('Result Save successfully.');
              }else{
                  sendFailResponse('Failed', 'Unable To Save Quiz Result');
              }
             
         } catch (JWTException $e) {
          return  response()->json(['status' => false, 'message' =>'login_failed','error'=>$e],500);
        }
    }
    
    function save_quiz_result(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id'       => 'required',
            'quiz_id'       => 'required',
            'quiz_response' => 'required',
            'watch_time'    => 'required',
            'district_id'    => 'required',
            'category_id'    => 'required'
        ]);
        
        
        if($validator->fails()){
            return response()->json(['status'=>false,'message'=>'Validation Fail','error' => $validator->messages()],200);
        }
         try {
            $result = array();
            $total_attempt = $total_correct = $total_wrong=$total_question= 0;
            $total_question = Question::where('quiz_id',$request->quiz_id)->count();

            
            $result['user_id'] = $request->user_id;
            $result['quiz_id'] = $request->quiz_id;
            $result['watch_time'] = $request->watch_time;
            $result['district_id'] = $request->district_id;
            $result['category_id'] = $request->category_id;
            $result['total_question'] = $total_question;

            $data = json_decode($request->quiz_response, true);
            $missing_question= $total_question-count($data[0]);
            if($missing_question==-1){
                $total_wrong=0;
            }else{
                $total_wrong=$missing_question;
            }
            
            foreach($data[0] as $response){
                // $total_question++;
                $actualAnswer = $this->sanitizeString(strtolower($response['actual_answer'])); 
                $userAnswer = $this->sanitizeString(strtolower($response['user_answer']));
                
                if (strcasecmp($actualAnswer, $userAnswer) == 0) {
                  $total_attempt++;
                  $total_correct++;
                }elseif($response['user_answer']==null || $response['user_answer']==''){
                    $total_wrong++;
                }
                else{
                    $total_attempt++;
                    $total_wrong++;
                }
            }
            $result['correct_answer'] = $total_correct;
            $result['wrong_answer']   = $total_wrong;
            $result['total_question'] = $total_question;    
            $result['total_attempt']  = $total_attempt;
            $result['percent'] = round((($total_correct/$total_question)*100),2);
            $user=User::where('id', $request->user_id)->first();
            $user_level_status= intval($user->level_status);
            $topic_with_respect_to_level=Topic::where('level_id',intval($user_level_status))->where('cat_id',$user->category_id)->count();
            



            // this topic ids which is save in users tables
            $user_topic_ids=$user->topic_id;
            $user_topic_ids=explode(',',$user_topic_ids);
            array_pop($user_topic_ids);
            
            $quiz_topic_id=DB::table('quiz')->where('quiz_id',$request->quiz_id)->first();
            $quiz_topic_id=$quiz_topic_id->topic_id;
          
            if(in_array($quiz_topic_id,$user_topic_ids)){
            } else{
            $user->topic_count+=1;
            $user->topic_id .=$quiz_topic_id.',';
            $user->save();
            }  
        //   ye karna hai     

                
            $quiz_data = DB::table('quiz_result')
            ->select('quiz_id', 'user_id', DB::raw('MAX(percent) as percent'))
            ->where('user_id', $request->user_id)
            ->groupBy('quiz_id')
            ->get();

            
             
            
           

        if(count($quiz_data)>0){
        $grouped_data = [];
        $F=0;
        $P=0;
        $userAverages = [];
        foreach ($quiz_data as $entry) {
            
            // find leevl from  the quiz
            $quiz_data_level = DB::table('quiz')->where('quiz_id', $entry->quiz_id)->select('level_id')->first();
            
           
            // if quiz_level is equal to user level
          
            if($quiz_data_level->level_id==$user_level_status){
               
            $temp['user_id']=$entry->user_id;
            $temp['percentage']=$entry->percent;       
            $grouped_data[] = $temp;  
            $userStats = [];
             

            foreach ($grouped_data as $item) {
            $userId = $item['user_id'];
            $percentage = intval($item['percentage']); // Convert percentage to integer
        
            if (!isset($userStats[$userId])) {
                // If not, initialize with sum and count
                $userStats[$userId] = ['sum' => $percentage, 'count' => 1];
            } else {
                // sum and count
                $userStats[$userId]['sum'] += $percentage;
                $userStats[$userId]['count']++;
            }
        }
           
           foreach ($userStats as $userId => $stats) {
           $average = $stats['sum'] / $stats['count'];
           $userAverages[$userId]['percentage'] = $average;
           
           
          
}
          
           foreach ($userAverages as $percentages) {
            if($percentages['percentage']>40){
                $P++;    
            }else{
                $F++;
            }
        } 
            }
   
        }
        $userAverages=$userAverages[$userId]["percentage"];
        }
        else{
            $userAverages=0;
        }

        
            if(intval($user->topic_count)>=intval($topic_with_respect_to_level) && $userAverages>40){
            $user=User::where('id', $request->user_id)->first();
            $user->level_status+=1;
            $user->topic_count=0;
            $user->topic_id =null;
            $user->save();
            if($user->level_status==2){
                $user->first_level_status='pass';
                $user->save();
            }else if($user->level_status==3){
                $user->second_level_status='pass';
                $user->save();
            }else if($user->level_status==4){
                $user->third_level_status='pass';
                $user->save();
            }
        }
            else if(intval($user->topic_count)>=intval($topic_with_respect_to_level) && ($userAverages>0 && $userAverages<40)){
            $user=User::where('id', $request->user_id)->first();    
                if($user->level_status==1){
                $user->first_level_status='fail';
                $user->save();
            }else if($user->level_status==2){
                $user->second_level_status='fail';
                $user->save();
            }else if($user->level_status==3){
                $user->third_level_status='fail';
                $user->save();
            }
            }
            $save_quiz_response = DB::table('quiz_result')->insert($result);
              if($save_quiz_response){
                    sendSuccessRespose('Result Save successfully.');
              }else{
                  sendFailResponse('Failed', 'Unable To Save Quiz Result');
              }
             
         } catch (JWTException $e) {
          return  response()->json(['status' => false, 'message' =>'login_failed','error'=>$e],500);
        }
    }

    
    function get_quiz_result(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'quiz_id' => 'required'
        ]);
      
        if($validator->fails()){
            return response()->json(['status'=>false,'message'=>'Validation Fail','error' => $validator->messages()],200);
        }
        
         try {
            
           $save_quiz_response = DB::table('quiz_result')->where('user_id',$request->user_id)->where('quiz_id',$request->quiz_id)->orderBy('result_id','DESC')->first();
               
               if($save_quiz_response){
                    sendSuccessRespose('Result',$save_quiz_response);
               }else{
                   sendFailResponse('Failed', 'Unable To get Quiz Result');
               }
             
         } catch (JWTException $e) {
           return  response()->json(['status' => false, 'message' =>'login_failed','error'=>$e],500);
        }
    }
    
    function get_levelwise_result(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required'
            ]);
      
        if($validator->fails()){
            return response()->json(['status'=>false,'message'=>'Validation Fail','error' => $validator->messages()],200);
        }
        
         try {
            
           $quiz_response = DB::table('quiz_result')->where('user_id',$request->user_id)
                                 ->join('quiz','quiz.quiz_id','=','quiz_result.quiz_id')
                                 ->join('level','level.level_id','=','quiz.level_id')
                                 ->join('topic','topic.id','=','quiz.topic_id')
                                 ->select('level.level_name','topic.topic_name','quiz_result.total_question','quiz_result.total_attempt','quiz_result.correct_answer','quiz_result.wrong_answer','quiz_result.percent')
                                 ->orderBy('quiz_result.created_at')
                                 ->get();
               
               if($quiz_response){
                    sendSuccessRespose('Result',$quiz_response);
               }else{
                   sendFailResponse('Failed', 'Unable To get Quiz Result');
               }
            
         } catch (JWTException $e) {
           return  response()->json(['status' => false, 'message' =>'login_failed','error'=>$e],500);
        }
    }
    
    function get_quiz_response(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'quiz_id' => 'required'
            ]);
      
        if($validator->fails()){
            return response()->json(['status'=>false,'message'=>'Validation Fail','error' => $validator->messages()],200);
        }
        
         try {
            
           $quiz = QuizResponse::where('user_id',$request->user_id)
                                         ->where('quiz_id',$request->quiz_id)
                                         ->select('quiz_response')
                                         ->first();
          $quiz_response =  json_decode($quiz->quiz_response);
               if($quiz_response){
                 sendSuccessRespose('Result',$quiz_response);
               }else{
                   sendFailResponse('Failed', 'Unable To get Quiz response');
               }
            
         } catch (JWTException $e) {
           return  response()->json(['status' => false, 'message' =>'failed','error'=>$e],500);
        }
    }
    
    
    public function feedback(Request $request){
        
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'feedback' => 'required'
            ]);
      
        if($validator->fails()){
            return response()->json(['status'=>false,'message'=>'Validation Fail','error' => $validator->messages()],200);
        }
        
        
        
        DB::table('feedback')->insert([
            'user_id' => $request->input('user_id'),
            'feedback' => $request->input('feedback'),
        ]);
        return response()->json(['message' => 'Your Feedback Send Successfully!']);

    }
    
    
         public function filterReportsexample(Request $requets)
        {
    
            $P=0;
            $F=0;
            $count=0;
            $Totaluser=User::all()->count();
            $first_level=User::where('level_status',1)->where('district_id',intval($requets->district))->count();
            $second_level=User::where('level_status',2)->where('district_id',intval($requets->district))->count();
            $third_level=User::where('level_status',3)->where('district_id',intval($requets->district))->count();
             $data = [
                'first_level' => $first_level,
                'second_level' => $second_level,
                'third_level' => $third_level,
            ];
            $quiz_data=DB::table('quiz_result')->where('district_id',$requets->district)->get();
            $grouped_data = [];
            foreach ($quiz_data as $entry) {
                $temp['user_id']=$entry->user_id;
                $temp['percentage']=$entry->percent;       
                $grouped_data[] = $temp;
            }
            
             
    $userStats = [];
    // Process the input data
    foreach ($grouped_data as $item) {
        $userId = $item['user_id'];
        $percentage = intval($item['percentage']); // Convert percentage to integer
    
        // Check if user_id is already in the array
        if (!isset($userStats[$userId])) {
            // If not, initialize with sum and count
            $userStats[$userId] = ['sum' => $percentage, 'count' => 1];
        } else {
            // If yes, update sum and count
            $userStats[$userId]['sum'] += $percentage;
            $userStats[$userId]['count']++;
        }
    }
    $userAverages = [];
    foreach ($userStats as $userId => $stats) {
        $average = $stats['sum'] / $stats['count'];
        $userAverages[$userId]['percentage'] = $average;
    }
    
            foreach ($userAverages as $percentages) {
                if($percentages['percentage']>40){
                    $P++;    
                }else{
                    $F++;
                }
            }
            
                $data = [
                'first_level' => $first_level,
                'second_level' => $second_level,
                'third_level' => $third_level,
                'pass' => $P,
                'fial' => $F,
            ];
            return response()->json($data);
        }
        
        
        public function userInfo($id){
            $user=User::find($id);
            if($user){
                return response()->json(['status'=>true,'message' => $user],200);
            }else{
                return response()->json(['status'=>false,'message'=>'User not found!'],200);

            }
        }
    

}
