<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Topic;
use App\Models\level;
use App\Models\Quiz;
use App\Models\User;
use App\Models\Category;
use DB;
use Auth;

class LevelController extends Controller
{
    public function get_levels($id=''){
        $levels = Level::select('level_id','level_name')
                  ->orderBy('level.level_id','ASC')->get();
        sendSuccessRespose('success', $levels);
    }
    
     public function get_topic_by_levelID($id,$cat_id,$user_id=''){
         
               $levels = Topic::join('level','level.level_id','=','topic.level_id')
                  ->where('topic.level_id',$id)
                  ->where('topic.cat_id',$cat_id)
                  ->select('topic.id as topic_id','topic.cat_id as category_id','topic_name','topic.level_id','level_name');
           
           
       
        if(!$levels){
           return response()->json(['failure' => 'Topic not found']);
        }
        
        if(!empty($user_id)){
        $levels = $levels->selectRaw("case when EXISTS (select quiz.topic_id from quiz_result join quiz on quiz.quiz_id= quiz_result.quiz_id where quiz_result.user_id = '$user_id' and quiz.topic_id = topic.id ) then true else false END as is_complete");
        }
        
        $levels = $levels->orderBy('topic_name', 'ASC')->get();
        return $levels;
        
        // sendSuccessRespose('success', $levels);
        
    }
    
     
     public function get_topic_detail($id){
        $levels = Topic::where('id',$id)->get();
        sendSuccessRespose('success', $levels);

        
    }
    
     public function get_unlock_level($user_id){

        $levels  =  DB::select("SELECT level.level_id,level.level_name,t.quiz_level, 
                        (select count(id) from topic where level_id = level.level_id order by topic.level_id ) as actual_topic,
                        t.attempt_topic from (select quiz.level_id as quiz_level,count(quiz.topic_id) as attempt_topic 
                        from quiz_result join quiz on quiz.quiz_id = quiz_result.quiz_id 
                        where quiz_result.user_id = $user_id group by quiz.level_id ) as t 
                        right join level on level.level_id = t.quiz_level 
                        order By level.level_id");
        // $unlock_level =[];
        // $start_unlock= false;       
        // foreach($levels as $level){
        //     if(empty($level->quiz_level)){var_dump($level->quiz_level);
        //          $start_unlock= true;
        //     }
        // }
        // if($start_unlock == false ){
        //     $unlock_level[] = $levels[0]->level_id; 
        // }
        // dd($unlock_level);
        $lock = true;
        $level_info = [];
        $all_levels = [];
        foreach($levels as $level){
            if(empty($level->quiz_level)){
                 $lock= false;
            }
            if(!empty($level->quiz_level) && ($level->attempt_topic == $level->actual_topic)){$lock= true;}
            $level_info['level_id'] = $level->level_id;
            $level_info['level_name'] = $level->level_name;
            $level_info['unlock'] = $lock;
            
            $all_levels[] = $level_info;
        }
        
        sendSuccessRespose('success', $all_levels);

        
    }
    
    
    
    
    public function printReport(Request $request)
    {
    $P = $F = $count = $Totaluser = 0;
    $facility = $district = '';
    $districtCondition = ['district_id' => intval($request->district)];
    $facilityCondition = ['facility_id' => intval($request->facility_id)];
    $first_level = User::where('level_status', 1)->where($districtCondition)->when($request->facility_id, function ($query) use ($facilityCondition) {
        return $query->where($facilityCondition);
    })->count();
    $second_level = User::where('level_status', 2)->where($districtCondition)->when($request->facility_id, function ($query) use ($facilityCondition) {
        return $query->where($facilityCondition);
    })->count();
    $third_level = User::where('level_status', 3)->where($districtCondition)->when($request->facility_id, function ($query) use ($facilityCondition) {
        return $query->where($facilityCondition);
    })->count();
    $quiz_data = DB::table('quiz_result')->where($districtCondition)->when($request->facility_id, function ($query) use ($facilityCondition) {
        return $query->where($facilityCondition);
    })->get();
    $district = $request->district ? DB::table('district')->where($districtCondition)->value('name') : '';
    if ($request->facility_id) {
        $facility = DB::table('facility')->where('id', $facilityCondition['facility_id'])->value('name');
    }
    $inactive_user = User::where('status', 0)->where($districtCondition)->when($request->facility_id, function ($query) use ($facilityCondition) {
        return $query->where($facilityCondition);
    })->count();
    $active_user = User::where('level_status', 1)->where($districtCondition)->when($request->facility_id, function ($query) use ($facilityCondition) {
        return $query->where($facilityCondition);
    })->count();
    $Totaluser = User::where($districtCondition)->when($request->facility_id, function ($query) use ($facilityCondition) {
        return $query->where($facilityCondition);
    })->count();
    $grouped_data = $quiz_data->map(function ($entry) {
        return ['user_id' => $entry->user_id, 'percentage' => $entry->percent];
    });
    $userAverages = $grouped_data->groupBy('user_id')->map(function ($group) {
        return $group->avg('percentage');
    });
    $userAverages->each(function ($average) use (&$P, &$F) {
        $average > 40 ? $P++ : $F++;
    });
    $data = compact('Totaluser', 'inactive_user', 'active_user', 'P', 'F', 'district', 'facility', 'first_level', 'second_level', 'third_level', 'facility');
    $pdf = PDF::loadView('admin.reporting.pdfReport', compact('data'));
    return $pdf->download('pdfview.pdf');
}

    
    
    
    
    
    
    
    
    
}
