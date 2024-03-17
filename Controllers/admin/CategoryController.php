<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $category_list = Category::all();
        return view('admin.category.category_list',compact('category_list'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.category.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        validator::make($request->all(),[
            'area_name' => 'required'
        ])->validate();

        $insert_data = array(
            'name'          => $request->area_name,
            'created_at'   => date('Y-m-d H:i:s'),
            'created_by'  => $request->user()->id
        );

        Category::create($insert_data);

        $request->session()->flash('msg', 'Added Successfully');
        return  redirect(route('manageCategory.index'));
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
        $data = Category::find($id);
        return view('admin.category.edit',["data"=>$data]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        Validator::make($request->all(),[
            'area_name' => 'required'
        ])->validate();
        $update_data = array(
            'name'          => $request->area_name,
            'updated_at'   => date('Y-m-d H:i:s'),
            'updated_by'  => $request->user()->id
        );

        Category::where('id',$id)->update($update_data);

        $request->session()->flash('msg', 'Update Successfully');
        return  redirect(route('manageCategory.index'));

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $Category = Category::find($id);
        $Category->delete();

        return  redirect(route('manageCategory.index'))->with('msg', 'Deleted Successfully');
    }
}
