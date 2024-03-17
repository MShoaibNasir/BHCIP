<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\level;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\Topic;
use App\Models\QuizResponse;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth;
class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $all_quiz = Quiz::select('quiz.*','level.level_name','category.name as category_name')
        //                         ->join('level','level.level_id','=','quiz.level_id')
        //                         ->join('category', 'category.id', '=', 'quiz.category_id')
        //                         ->paginate(15);
                                
    $all_quiz = Quiz::select('quiz.*', 'level.level_name', 'category.name as category_name')
    ->join('level', 'level.level_id', '=', 'quiz.level_id')
    ->join('category', 'category.id', '=', 'quiz.category_id')
    ->orderBy('category_id','Asc') // Corrected 'order by' to 'orderBy'
    ->paginate(15);                          
        return view('admin.quiz.quiz_list',compact('all_quiz'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $category = Category::all();
        $levels = level::all();
        $topics = Topic::all();
        return view('admin.quiz.add_quiz',compact('category','levels','topics'));
    }
    public function createFacility()
    {
        $district=DB::table('district')->get();
        return view('admin.quiz.add_facility',compact('district'));
    }
    public function storeFacility(Request $requets)
    {
        $facility=DB::table('facility')->where('id',$requets->id)->first();
        if($facility){
            return redirect()->back()->with('error','Duplicate Facility id not allowed!');
        }
        $facility = DB::table('facility')->insert([
            'id' => $requets->id,    
            'name' => $requets->name,    
            'district' => $requets->district,    
        ]);
        return redirect()->back()->with('success','Insert data successfully!');
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     $validation = Validator::make($request->all(),[
    //         'quiz_name' => 'required',
    //         'category' => 'required',
    //         'level' => 'required',
    //         'topic' => 'required'
    //     ])->validate();
        
    //     if(!$request->file('quiz_video') || $request->file('quiz_video')->getClientOriginalExtension() !== 'mp4'){
    //         return redirect(route('manageQuestion.create'))->with('error', 'Please Add Video with type mp4')->withInput();
    //     }
        
    //     $file_name = $_FILES['question_sheet']['name'];
    //     $file_tmp_name = $_FILES["question_sheet"]["tmp_name"];
    //     $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    //     if (!$request->file('question_sheet') || $file_type != 'csv' || $_FILES["question_sheet"]["size"] <= 0) {
    //         return redirect(route('manageQuestion.create'))->with('error', 'Please choose CSV File for Question Sheet.')->withInput();
    //     }
    
    //     $quiz_data = new Quiz;
    //     $quiz_data->quiz_name    = $request->quiz_name;
    //     $quiz_data->category_id   = $request->category;
    //     $quiz_data->level_id          = $request->level;
    //     $quiz_data->topic_id          = $request->topic;
    //     $quiz_data->status            = '1 ';
    //     $quiz_data->created_by    = Auth::user()->id;

    //   //check quiz video
    //     if($request->file('quiz_video')){
    //         $destination_path = public_path('uploads/quiz_video');
    //         $video = $request->file('quiz_video');
    //         $filePath = time() . '.' . $video->extension();
    //         $video->move('uploads/quiz_video', $filePath);
    //         $quiz_data->video = $filePath;
    //     }
        
    //     $quiz_data->save();
    //     $quiz_id = $quiz_data->quiz_id;

    //     //check quiz question file
    //             if ($_FILES["question_sheet"]["size"] > 0) {
    //                     $file = fopen($file_tmp_name,"r");
    //                     fgetcsv($file, 10000, ",");
    //                 while (($column = fgetcsv($file, 10000, ",")) !== FALSE) {
    //                             $question = '';
    //                             $type = '';
    //                             $options =[];
    //                             $answer = [];

    //                             if (isset($column[0])) {
    //                                 $question = $column[0];
    //                             }

    //                             if (isset($column[3])) {
    //                                 $type = $column[3];
    //                             }
    //                             //insert question
    //                             $question_data = array(
    //                                 'quiz_id' => $quiz_id,
    //                                 'question' => $question,
    //                                 'question_type' => $type,
    //                                 'created_by'  => Auth::user()->id
    //                             );
    //                             $question_id = Question::create($question_data);
    //                             //option
    //                             if(isset($column[1])){
    //                                 $options = explode("|",$column[1]);
    //                                 $data_option = [];
    //                                 foreach ($options as $option) {
    //                                     array_push($data_option,[
    //                                         'question_id' => $question_id->question_id,
    //                                         'option_description' =>$option,
    //                                         'created_by'  => Auth::user()->id
    //                                     ]);
    //                                 }
    //                             QuestionOption::insert($data_option);
    //                             }
    //                             //answer
    //                             if (isset($column[2])) {
    //                                 $answer = explode("|", $column[2]);
    //                                 $data_answer = [];
    //                                 foreach ($answer as $ans) {
    //                                     array_push($data_answer, [
    //                                         'question_id' => $question_id->question_id,
    //                                          'answer_description' => $ans,
    //                                         'created_by'  => Auth::user()->id
    //                                     ]);
    //                                 }
    //                                 QuestionAnswer::insert($data_answer);
    //                             }
    //                 }
    //             }
    //     return  redirect(route('manageQuestion.index'))->with('success', 'Added Successfully');
    // }
    public function storequizes(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'quiz_name' => 'required',
            'category' => 'required',
            'level' => 'required',
            'topic' => 'required'
        ])->validate();
        
        if(!$request->file('quiz_video') || $request->file('quiz_video')->getClientOriginalExtension() !== 'mp4'){
            return redirect(route('manageQuestion.create'))->with('error', 'Please Add Video with type mp4')->withInput();
        }
        
        $file_name = $_FILES['question_sheet']['name'];
        $file_tmp_name = $_FILES["question_sheet"]["tmp_name"];
        $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (!$request->file('question_sheet') || $file_type != 'csv' || $_FILES["question_sheet"]["size"] <= 0) {
            return redirect(route('manageQuestion.create'))->with('error', 'Please choose CSV File for Question Sheet.')->withInput();
        }
    
        $quiz_data = new Quiz;
        $quiz_data->quiz_name    = $request->quiz_name;
        $quiz_data->category_id   = $request->category;
        $quiz_data->level_id          = $request->level;
        $quiz_data->topic_id          = $request->topic;
        $quiz_data->status            = '1 ';
        $quiz_data->created_by    = Auth::user()->id;

       //check quiz video
        if($request->file('quiz_video')){
            $destination_path = public_path('uploads/quiz_video');
            $video = $request->file('quiz_video');
            $filePath = time() . '.' . $video->extension();
            $video->move('uploads/quiz_video', $filePath);
            $quiz_data->video = $filePath;
        }
        
        $quiz_data->save();
        $quiz_id = $quiz_data->quiz_id;

        //check quiz question file
                if ($_FILES["question_sheet"]["size"] > 0) {
                        $file = fopen($file_tmp_name,"r");
                        fgetcsv($file, 10000, ",");
                    while (($column = fgetcsv($file, 10000, ",")) !== FALSE) {
                                $question = '';
                                $type = '';
                                $options =[];
                                $answer = [];

                                if (isset($column[0])) {
                                    $question = $column[0];
                                }

                                if (isset($column[3])) {
                                    $type = $column[3];
                                }
                                //insert question
                                $question_data = array(
                                    'quiz_id' => $quiz_id,
                                    'question' => $question,
                                    'question_type' => $type,
                                    'created_by'  => Auth::user()->id
                                );
                               
                                $question_id = Question::create($question_data);
                                //option
                                if(isset($column[1])){
                                    $options = explode("|",$column[1]);
                                    $data_option = [];
                                    foreach ($options as $option) {
                                        array_push($data_option,[
                                            'question_id' => $question_id->question_id,
                                            'quiz_id' => $quiz_id,
                                            'option_description' =>$option,
                                            'created_by'  => Auth::user()->id
                                        ]);
                                    }
                                  
                                QuestionOption::insert($data_option);
                                }
                                  
                                //answer
                                if (isset($column[2])) {
                                    $answer = explode("|", $column[2]);
                                    $data_answer = [];
                                    foreach ($answer as $ans) {
                                        array_push($data_answer, [
                                            'question_id' => $question_id->question_id,
                                            'quiz_id' => $quiz_id,
                                             'answer_description' => $ans,
                                            'created_by'  => Auth::user()->id
                                        ]);
                                    }
                                    QuestionAnswer::insert($data_answer);
                                }
                            
                    }
                        
                }
                
        return  redirect(route('manageQuestion.index'))->with('success', 'Added Successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
 
         
        $quiz = Quiz::where('quiz.quiz_id',$id)->with('QuizQuestions','QuizQuestions.QuestionOption', 'QuizQuestions.QuestionAnswer')
                            ->join('level', 'level.level_id', '=', 'quiz.level_id')
                            ->join('category', 'category.id', '=', 'quiz.category_id')
                            ->join('topic', 'topic.id', '=', 'quiz.topic_id')
                            ->select('quiz.*', 'level.level_name', 'category.name as category_name','topic.topic_name')
                            ->first();
                            

                           
        return view('admin.quiz.view_quiz',compact('quiz'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $quiz = Quiz::where('quiz.quiz_id',$id)
                            ->with('QuizQuestions', 'QuizQuestions.QuestionOption', 'QuizQuestions.QuestionAnswer')->first();
                          
        $category =Category::all();
        $levels = level::all();
        // $topics = Topic::all();
        
        return view('admin.quiz.edit_quiz', compact('quiz', 'category', 'levels'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validation = Validator::make($request->all(), [
            'quiz_name' => 'required',
            'category' => 'required',
            'level' => 'required',
            'topic' =>'required',
        ])->validate();

        if ($request->file('quiz_video') && $request->file('quiz_video')->getClientOriginalExtension() !== 'mp4') {
            return redirect(route('manageQuestion.edit',$id))->with('error', 'Please Add Quiz Video with type mp4')->withInput();
        }

        $quiz_data = Quiz::find($id);
        $quiz_data->quiz_name    = $request->quiz_name;
        $quiz_data->category_id   = $request->category;
        $quiz_data->level_id          = $request->level;
        $quiz_data->topic_id          = $request->topic;
        $quiz_data->status            = $request->quiz_status== "on" ? 1:0 ;
        $quiz_data->updated_by    = Auth::user()->id;

        //check quiz video
        if ($request->file('quiz_video')) {
            $destination_path = public_path('uploads/quiz_video');
            $video = $request->file('quiz_video');
            $filePath = time() . '.' . $video->extension();
            $video->move('uploads/quiz_video', $filePath);
            $quiz_data->video = $filePath;
        }
        $quiz_data->save();

        return  redirect(route('manageQuestion.edit',$id))->with('success', 'Update Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $quiz = Quiz::find($id);
        DB::table('quiz_questions')->where('quiz_id',$id)->delete();
        DB::table('question_option')->where('quiz_id',$id)->delete();
        DB::table('question_answer')->where('quiz_id',$id)->delete();
        $quiz->delete();
        return  redirect(route('manageQuestion.index'))->with('success', 'Deleted Successfully');
    }

    function deleteQuestion($id,$quiz_id) {
        //delete from question table
        $quiz = Question::find($id);
        $quiz->delete();
        //delete question option
        QuestionOption::where('question_id',$id)->delete();
        //delete question answer
        QuestionAnswer::where('question_id', $id)->delete();

        return  redirect(route('manageQuestion.edit', $quiz_id))->with('success', 'Deleted Successfully');
    }
    
     /**
     * Update single question of a quiz.
     */
    public function UpdateQuestion(Request $request,string $id,string $quiz_id)
    {
        $validation = Validator::make($request->all(),[
            'edit_question' => 'required',
            'edit_option' => 'required',
            'edit_answer' => 'required',
        ])->validate();
        
        try{ 
        //question
        $question = Question::find($id);
        $question->question   = $request->edit_question;
        $question->updated_by = Auth::user()->id;
        $question->save();
                               
        //option
        $data_option = [];
        foreach ($request->edit_option as $option) {
            if($option){
                array_push($data_option,[
                    'question_id' => $id,
                    'option_description' =>$option,
                    'created_by'  => Auth::user()->id
                ]);
            }
        } 
        QuestionOption::where('question_id',$id)->delete();
        QuestionOption::insert($data_option);
                             
        //answer
        $data_answer = [];
        foreach ($request->edit_answer as $ans) {
            if($ans){
              array_push($data_answer, [
                'question_id' => $id,
                'answer_description' => $ans,
                'created_by'  => Auth::user()->id
              ]);
            }
        }
        QuestionAnswer::where('question_id',$id)->delete();
        QuestionAnswer::insert($data_answer);
                               
        return  redirect(route('manageQuestion.edit',$quiz_id))->with('success', 'Update Successfully');
        }
        catch(Exception $e)
        {
          return  redirect(route('manageQuestion.edit',$quiz_id))->with('error', 'Something Went Wrong');
        }
    }
    
    function get_quiz_response(Request $request){
        $quiz = QuizResponse::where('user_id',$request->user_id)
                                         ->where('quiz_id',$request->quiz_id)
                                         ->select('quiz_response')
                                         ->first();
        $quiz_response =  json_decode($quiz->quiz_response)[0];
           
        if($quiz_response){
                $quiz_detail = Quiz::where('quiz.quiz_id',$request->quiz_id)->with('QuizQuestions','QuizQuestions.QuestionOption', 'QuizQuestions.QuestionAnswer')
                        ->select('quiz.*')
                        ->first();
               $response = '';
                 foreach($quiz_detail->QuizQuestions as $question){
                     $a = in_array($question->question_id,json_decode(json_encode($quiz_response), true));
                    var_dump($a);
                    $response .='<h6 class="modal-title font-weight-normal" id="modal-title-question">';
                    $response .= '<b>Question:</b>'.$question->question.'</h6><br>';
                           foreach ($question->QuestionOption as $option){
                   $response .='<div class="form-check">';
                   $response .='<input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault1">';
                   $response .='<label class="form-check-label" for="flexRadioDefault1">'.$option->option_description.'</label>';
                   $response .='</div>';
                           }
                     $response .= '<br>';
                     foreach ($question->QuestionAnswer as $ans){
                            $response .= '<b>Actual Answer:'.$ans->answer_description.'</b>';
                     }
                 }
                                   
                   echo $response;exit;
        }else{
              return response()->json(['Unable To get Quiz response'],400);
        }
            
      
    }
    
    public function topicsByLevelId(Request $request,$id){
        $data=  $request->all();
        $Topic = Topic::where('level_id', $id)
        ->where('cat_id', $data['category_id'])
        ->orderBy('topic_name', 'asc')
        ->get();

        if(!$Topic){
           return response()->json(['failure' => 'Topic not found']);
        }else{
            sendSuccessRespose('success', $Topic);
        }
    }
    
}
