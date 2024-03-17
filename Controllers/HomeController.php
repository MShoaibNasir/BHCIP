<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Category;
use App\Models\User;

use Illuminate\Support\Facades\File;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }
    public function view_dashboard(Request $request)
    {   
        //apply filters
        $total_user = 0;
        $active_user = 0;
        $register_user = 0;
        $qualified_user = 0;
        $filter = '1=1'; 
        if(!empty($request->category)){
            $filter .= ' and category_id = "'.$request->category.'"';
        }
        if(!empty($request->facility)){
            $filter .= ' and facility_id = "'.$request->facility.'"';
        }
        if(!empty($request->district)){
            $filter .= ' and district_id = "'.$request->district.'"';
        }
       
        $total_user     =  User::whereRaw($filter)->count(); 
        $active_user    =  User::where('status','1')->whereRaw($filter)->count(); 
        // $register_user  =  User::where('role','3')->whereRaw($filter)->count(); 
        $register_user  =  User::whereRaw($filter)->count(); 
        $qualified_user =  User::where('is_qualified',1)->whereRaw($filter)->count(); 
        
        $district = getDistrict();
        $category = Category::all();
        return view('admin.dashboard',compact('district','category','total_user','active_user','register_user','qualified_user'));
    }

    public function setting()
    {
        $logo =  Setting::where('name', '=', 'logo')->first();
        $favicon =  Setting::where('name', '=', 'favicon')->first();
        return view('admin.setting', compact('logo', 'favicon'));
    }

    public function update_setting(Request $request)
    {


        if ($request->hasfile('logo')) {
            $setting =  Setting::where('name', '=', 'logo')->first();
            if ($setting) {
                $destination = "uploads/logo/" . $setting->value;

                if (File::exists($destination)) {
                    File::delete($destination);
                }
            }
            $file = $request->logo;
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $request->logo->move('uploads/logo', $filename);
            if ($setting) {
                $setting->value = $filename;
                $setting->save();
            } else {
                $data = ['name' => 'logo', 'value' => $filename];
                Setting::create($data);
            }
        }
        if ($request->hasfile('favicon')) {
            $setting =  Setting::where('name', '=', 'favicon')->first();
            if ($setting) {
                $destination = "uploads/logo/" . $setting->value;

                if (File::exists($destination)) {
                    File::delete($destination);
                }
            }
            $file = $request->favicon;
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $request->favicon->move('uploads/logo', $filename);
            if ($setting) {
                $setting->value = $filename;
                $setting->save();
            } else {
                $data = ['name' => 'favicon', 'value' => $filename];
                Setting::create($data);
            }
        }

        $logo =  Setting::where('name', '=', 'logo')->first();
        return view('admin.setting', compact('logo'));
    }
}
