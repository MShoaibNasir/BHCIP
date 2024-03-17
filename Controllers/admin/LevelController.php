<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\level;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use DB;

class LevelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $level_list = level::all();
        return view('admin.levels.level_list', compact('level_list'));
    }
    public function usersList()
    {
        $users = DB::table('users')->get();
        return view('admin.users.list', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         return view('admin.levels.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        validator::make($request->all(), [
            'level_name' => 'required'
        ])->validate();

        $insert_data = array(
            'level_name'          => $request->level_name,
            'created_at'   => date('Y-m-d H:i:s')
        );

        level::create($insert_data);

        $request->session()->flash('msg', 'Added Successfully');
        return  redirect(route('manageLevel.index'));
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
        $data = level::find($id);
        return view('admin.levels.edit',["data"=>$data]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        Validator::make($request->all(), [
            'level_name' => 'required'
        ])->validate();
        $update_data = array(
            'level_name'          => $request->level_name,
            'updated_at'   => date('Y-m-d H:i:s')
            );

        level::where('level_id', $id)->update($update_data);

        $request->session()->flash('msg', 'Update Successfully');
        return  redirect(route('manageLevel.index'));

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
      $level = level::find($id);
      $level->delete();

        return  redirect(route('manageLevel.index'))->with('success', 'Deleted Successfully');
    }
}
