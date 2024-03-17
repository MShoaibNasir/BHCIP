<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\level;
use App\Models\Topic;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Auth;

class TopicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $topic_list = Topic::join('level','level.level_id','=','topic.level_id')->orderBy('id','DESC')->get();
        return view('admin.topic.topic_list', compact('topic_list'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {  
         $levels = level::all();
         
         return view('admin.topic.create',compact('levels'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        validator::make($request->all(), [
            'topic_name' => 'required',
            'level'  =>'required',
        ])->validate();
        
    
    
            $topic = new Topic();
            $topic->topic_name   = $request->topic_name;
            $topic->created_at   = date('Y-m-d H:i:s');
            $topic->created_by   = Auth::user()->id;
            $topic->level_id     = $request->level;
            $topic->cat_id     = intval($request->cat_id);
            $topic->save();
      
        $request->session()->flash('msg', 'Added Successfully');
        return  redirect(route('manageTopic.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = Topic::find($id);
        $levels = level::all();
        return view('admin.topic.edit',["data"=>$data,"levels"=>$levels]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        Validator::make($request->all(), [
            'topic_name' => 'required',
            'level'      => 'required'
        ])->validate();
        
        $topic = Topic::find($id);
        
        $topic->topic_name = $request->topic_name;
        $topic->cat_id = intval($request->cat_id);
        $topic->level_id   = $request->level; 
        $topic->updated_at = date('Y-m-d H:i:s');
        $topic->updated_by = Auth::user()->id;
        $topic->save();

        $request->session()->flash('msg', 'Update Successfully');
        return  redirect(route('manageTopic.index'));

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
      $level = Topic::find($id);
      $level->delete();

        return  redirect(route('manageTopic.index'))->with('msg', 'Deleted Successfully');
    }
}
