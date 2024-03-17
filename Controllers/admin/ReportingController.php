<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\level;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Models\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Response;
use PDF;




class ReportingController extends Controller
{
   
    public function index()
    {
        $level_list = level::all();
        return view('admin.levels.level_list', compact('level_list'));
    }
    public function reporting()
    {
        $total_district = DB::table('district')->get();
         $category = DB::table('category')->get();
        $quiz_results = DB::table('quiz_result')
       ->orderByDesc('created_at')
       ->paginate(10);
        return view('admin.reporting.list', compact('quiz_results','total_district','category'));
    }
    public function overallReporting()
    {
        $Totaluser=User::count();
        $first_level=User::where('level_status',1)->count();
        $second_level=User::where('level_status',2)->count();
        $third_level=User::where('level_status',3)->count();
        $inactive_user=User::where('status',0)->count();
        $total_district = DB::table('district')->get();
        $category = DB::table('category')->get();
        
        $quiz_data=DB::table('quiz_result')->get();
        $grouped_data = [];
        $F=0;
        $P=0;
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
                // sum and count
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
        
        return view('admin.reporting.reports',['Totaluser'=>$Totaluser,'first_level'=>$first_level,'second_level'=>$second_level,'third_level'=>$third_level,'inactive_user'=>$inactive_user,
        'total_district'=>$total_district,
        'pass'=>$P,
        'fail'=>$F,
        'category'=>$category
        ]);
    }
    public function over_all_report()
    {
        $Totaluser=User::count();
        $first_level=User::where('level_status',1)->count();
        $second_level=User::where('level_status',2)->count();
        $third_level=User::where('level_status',3)->count();
        
        $first_level_pass_users=User::where('first_level_status','pass')->count();
        $second_level_pass_users=User::where('second_level_status','pass')->count();
        $third_level_pass_users=User::where('third_level_status','pass')->count();
        
        
        $first_level_fail_users=User::where('first_level_status','fail')->count();
        $second_level_fail_users=User::where('second_level_status','fail')->count();
        $third_level_fail_users=User::where('third_level_status','fail')->count();
        
        $inactive_user=User::where('status',0)->count();
        $total_district = DB::table('district')->get();
        $category = DB::table('category')->get();


        return view('admin.reporting.BHCIP_Report',['Totaluser'=>$Totaluser,'first_level'=>$first_level,'second_level'=>$second_level,'third_level'=>$third_level,'inactive_user'=>$inactive_user,
        'total_district'=>$total_district,
        'category'=>$category,
        'first_level_pass_users'=>$first_level_pass_users,
        'second_level_pass_users'=>$second_level_pass_users,
        'third_level_pass_users'=>$third_level_pass_users,
        'first_level_fail_users'=>$first_level_fail_users,
        'second_level_fail_users'=>$second_level_fail_users,
        'third_level_fail_users'=>$third_level_fail_users
        
        
        ]);
    }
    public function over_all_dummy_report()
    {
        $Totaluser=User::count();
        $first_level=User::where('level_status',1)->count();
        $second_level=User::where('level_status',2)->count();
        $third_level=User::where('level_status',3)->count();
        
        $first_level_pass_users=User::where('first_level_status','pass')->count();
        $second_level_pass_users=User::where('second_level_status','pass')->count();
        $third_level_pass_users=User::where('third_level_status','pass')->count();
        
        
        $first_level_fail_users=User::where('first_level_status','fail')->count();
        $second_level_fail_users=User::where('second_level_status','fail')->count();
        $third_level_fail_users=User::where('third_level_status','fail')->count();
        
        $inactive_user=User::where('status',0)->count();
        $total_district = DB::table('district')->get();
        $category = DB::table('category')->get();


        return view('admin.reporting.dummy_report',['Totaluser'=>$Totaluser,'first_level'=>$first_level,'second_level'=>$second_level,'third_level'=>$third_level,'inactive_user'=>$inactive_user,
        'total_district'=>$total_district,
        'category'=>$category,
        'first_level_pass_users'=>$first_level_pass_users,
        'second_level_pass_users'=>$second_level_pass_users,
        'third_level_pass_users'=>$third_level_pass_users,
        'first_level_fail_users'=>$first_level_fail_users,
        'second_level_fail_users'=>$second_level_fail_users,
        'third_level_fail_users'=>$third_level_fail_users
        
        
        ]);
    }
 
    
    
    
    
     public function filterReports(Request $requets)
    {
        $P=0;
        $F=0;
        $count=0;
        $Totaluser=User::all()->count();
        $first_level=User::where('level_status',1)->where('district_id',intval($requets->district))->count();
        $second_level=User::where('level_status',2)->where('district_id',intval($requets->district))->count();
        $third_level=User::where('level_status',3)->where('district_id',intval($requets->district))->count();
        $quiz_data=DB::table('quiz_result')->where('district_id',$requets->district)->get();
        
        if ($requets->facility_id) {
            $first_level = User::where('level_status', 1)
                ->where('district_id', intval($requets->district))
                ->Where('facility_id', intval($requets->facility_id))
                ->count();
        
            $second_level = User::where('level_status', 2)
                ->where('district_id', intval($requets->district))
                ->Where('facility_id', intval($requets->facility_id))
                ->count();
        
            $third_level = User::where('level_status', 3)
                ->where('district_id', intval($requets->district))
                ->where('facility_id', intval($requets->facility_id))
                ->count();
                $quiz_data=DB::table('quiz_result')->where('district_id',$requets->district)->where('facility_id', intval($requets->facility_id))->get();
        }
        
         $data = [
            'first_level' => $first_level,
            'second_level' => $second_level,
            'third_level' => $third_level,
        ];
        
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
                    'fial' => $F
                ];
                return response()->json($data);
            }
            
    
    public function printReport(Request $requets)
    {
       
       
     $requets->validate([
            'district' => 'required',
            'facility_id' => 'required',
        ], [
            'facility_id.required' => 'The facility field is required!',
            // Add more custom messages for other fields as needed
        ]);   
    $P = $F = $count = $Totaluser = 0;
    $facility = $district = '';
    // first level user
    $first_level_query = User::where('level_status', 1);
    $consultant_count=DB::table('users')->where('district_id',intval($requets->district))->where('facility_id',intval($requets->facility_id))->where('category_id',4)->count();
    $doctor_count=DB::table('users')->where('district_id',intval($requets->district))->where('facility_id',intval($requets->facility_id))->where('category_id',6)->count();
    $lhw_count=DB::table('users')->where('district_id',intval($requets->district))->where('facility_id',intval($requets->facility_id))->where('category_id',7)->count();
    $health_care_count=DB::table('users')->where('district_id',intval($requets->district))->where('facility_id',intval($requets->facility_id))->where('category_id',8)->count();
    $paramedics_count=DB::table('users')->where('district_id',intval($requets->district))->where('facility_id',intval($requets->facility_id))->where('category_id',9)->count();
    if (isset($requets->district)) {
    $first_level_query->where('district_id', intval($requets->district));
    $district_name=DB::table('district')->where('district_id',intval($requets->district))->first();
    }
    if (isset($requets->facility_id)) {
        $first_level_query->where('facility_id', intval($requets->facility_id));
        $facility_name=DB::table('facility')->where('id',intval($requets->facility_id))->first();
    }
    if (isset($requets->category_id)) {
        $first_level_query->where('category_id', intval($requets->category_id));
    }
    if(isset($requets->start_date) && isset($requets->end_date)) {
    $startDate = $requets->start_date . ' 00:00:00';
    $endDate = $requets->end_date . ' 23:59:59';
    $first_level_query->whereBetween('created_at', [$startDate, $endDate]);
    }
    $first_level_users = $first_level_query->count();
    
    // second level user
    
    $second_level_query = User::where('level_status', 2);
    if (isset($requets->district)) {
    $second_level_query->where('district_id', intval($requets->district));
    }
    if (isset($requets->facility_id)) {
       $second_level_query->where('facility_id', intval($requets->facility_id));
    }
    if (isset($requets->category_id)) {
       $second_level_query->where('category_id', intval($requets->category_id));
    }
    if(isset($requets->start_date) && isset($requets->end_date)) {
    $startDate = $requets->start_date . ' 00:00:00';
    $endDate = $requets->end_date . ' 23:59:59';
    $second_level_query->whereBetween('created_at', [$startDate, $endDate]);
    }
     $second_level_users = $second_level_query->count();
      
      
    // third level user
    $third_level_query = User::where('level_status', 3);
    if (isset($requets->district)) {
    $third_level_query->where('district_id', intval($requets->district));
    }
    if (isset($requets->facility_id)) {
        $third_level_query->where('facility_id', intval($requets->facility_id));
    }
    if (isset($requets->category_id)) {
        $third_level_query->where('category_id', intval($requets->category_id));
    }
    
    if(isset($requets->start_date) && isset($requets->end_date)) {
    $startDate = $requets->start_date . ' 00:00:00';
    $endDate = $requets->end_date . ' 23:59:59';
    $third_level_query->whereBetween('created_at', [$startDate, $endDate]);
    }
    
    $third_level_users = $third_level_query->count();
    
    // total user 
    $Totaluser_query = User::query();
    if (isset($requets->district)) {
    $Totaluser_query->where('district_id', intval($requets->district));
    }
    if (isset($requets->facility_id)) {
        $Totaluser_query->where('facility_id', intval($requets->facility_id));
    }
    if (isset($requets->category_id)) {
        $Totaluser_query->where('category_id', intval($requets->category_id));
    }
    if(isset($requets->start_date) && isset($requets->end_date)) {
    $startDate = $requets->start_date . ' 00:00:00';
    $endDate = $requets->end_date . ' 23:59:59';
    $Totaluser_query->whereBetween('created_at', [$startDate, $endDate]);
    }
    
    
    $Totaluser = $Totaluser_query->count();
    
    // inactive user 
    $inactive_user_query = User::where('status', 0);
    if (isset($requets->district)) {
    $inactive_user_query->where('district_id', intval($requets->district));
    }
    if (isset($requets->facility_id)) {
        $inactive_user_query->where('facility_id', intval($requets->facility_id));
    }
    if (isset($requets->category_id)) {
        $inactive_user_query->where('category_id', intval($requets->category_id));
    }
    
    if(isset($requets->start_date) && isset($requets->end_date)) {
    $startDate = $requets->start_date . ' 00:00:00';
    $endDate = $requets->end_date . ' 23:59:59';
    $inactive_user_query->whereBetween('created_at', [$startDate, $endDate]);
    }
    
    $inactive_users = $inactive_user_query->count();
    // quiz result
    $quiz_data_query=DB::table('quiz_result');
    if (isset($requets->district)) {
    $quiz_data_query->where('district_id', intval($requets->district));
    }
    if (isset($requets->facility_id)) {
        $quiz_data_query->where('facility_id', intval($requets->facility_id));
    }
    if (isset($requets->category_id)) {
        $quiz_data_query->where('category_id', intval($requets->category_id));
    }
    
    if(isset($requets->start_date) && isset($requets->end_date)) {
    $startDate = $requets->start_date . ' 00:00:00';
    $endDate = $requets->end_date . ' 23:59:59';
    $quiz_data_query->whereBetween('created_at', [$startDate, $endDate]);
    }
    $quiz_data = $quiz_data_query->get();
    
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
    
    // active result
    
    $active_user_query = User::where('status', 1);
    if (isset($requets->district)) {
    $active_user_query->where('district_id', intval($requets->district));
    }
    if (isset($requets->facility_id)) {
        $active_user_query->where('facility_id', intval($requets->facility_id));
    }
    if (isset($requets->category_id)) {
        $active_user_query->where('category_id', intval($requets->category_id));
    }
    if(isset($requets->start_date) && isset($requets->end_date)) {
    $startDate = $requets->start_date . ' 00:00:00';
    $endDate = $requets->end_date . ' 23:59:59';
    $active_user_query->whereBetween('created_at', [$startDate, $endDate]);
    }
    
    
    $active_users = $active_user_query->count();
    // facility
    $facility = DB::table('facility')->where('id', intval($requets->facility_id))->select('name')->first() ?? false;
    // district
    $district=DB::table('district')->where('district_id',$requets->district)->select('name')->first() ?? false;
    if(isset($requets->start_date) && isset($requets->end_date)) {
    $startDate=explode(' ',$startDate);
    $startDate = date("d-m-Y", strtotime($startDate[0]));
    $endDate=explode(' ',$endDate);
    $endDate = date("d-m-Y", strtotime($endDate[0]));
    }
        $data = [
            'Total_user' => $Totaluser,
            'inactive_user' => $inactive_users,
            'active_user' => $active_users,
            'pass' => $P,
            'fail' => $F,
            'district'=>$district,
            'facility'=>$facility,
            'first_level'=>$first_level_users,
            'second_level'=>$second_level_users,
            'third_level'=>$third_level_users,
            'startDate'=>$startDate ?? null,
            'endDate'=>$endDate ?? null,
            'district_name'=>$district_name ?? null,
            'facility_name'=>$facility_name ?? null,
            'consultant_count'=>$consultant_count ?? null,
            'doctor_count'=>$doctor_count ?? null,
            'lhw_count'=>$lhw_count ?? null,
            'health_care_count'=>$health_care_count ?? null,
            'paramedics_count'=>$paramedics_count ?? null,
            
        ];
        
     $pdf = PDF::loadView('admin.reporting.pdfReport', compact('data'));
     return $pdf->download('pdfview.pdf');

}
    public function print_report(Request $requets)
    {
        // its done
        $total_pass_user_in_first_level=DB::table('users')->where('first_level_status','pass')->count();
        $total_fail_user_in_first_level=DB::table('users')->where('first_level_status','fail')->count();

        
        if($requets->district && $requets->facility_id && $requets->category_id && $requets->start_date){
        $data=[];
        $data['district']=$requets->district;
        $data['facility_id']=$requets->facility_id;
        $data['category_id']=$requets->category_id;
        $data['start_date']=$requets->start_date;
        $data= $this->districtWithFacilityCategoryPrintReportWithdate($data);
        

        $start_date=$data[1];
        $end_date=$data[2];
        $data=$data[0];
        
        $pdf = PDF::loadView('admin.reporting.BHCIPPrintReport', compact('data','start_date','end_date','total_pass_user_in_first_level','total_fail_user_in_first_level'))->setPaper('a4', 'landscape');
        return $pdf->stream('BHCIP Report.pdf');
        }
        // its done
        if($requets->district && $requets->facility_id && $requets->category_id){
        $data=[];
        $data['district']=$requets->district;
        $data['facility_id']=$requets->facility_id;
        $data['category_id']=$requets->category_id;
        $data= $this->districtWithFacilityCategoryPrintReport($data);
        $pdf = PDF::loadView('admin.reporting.BHCIPPrintReport', compact('data','total_pass_user_in_first_level','total_fail_user_in_first_level'))->setPaper('a4', 'landscape');
        return $pdf->stream('BHCIP Report.pdf');
        
        }
        if($requets->district && $requets->facility_id){
        $data=[];
        $data['district']=$requets->district;
        $data['facility_id']=$requets->facility_id;
        $data= $this->districtWithFacilityPrintReport($data);
        $pdf = PDF::loadView('admin.reporting.BHCIPSecondPrintReport', compact('data','total_pass_user_in_first_level','total_fail_user_in_first_level'))->setPaper('a4', 'landscape');
        return $pdf->stream('BHCIP Report.pdf');
        
        }
        
        
        if($requets->district){
        $data= $this->districtOnlyPrintReport($requets->district);
        $pdf = PDF::loadView('admin.reporting.BHCIPSecondPrintReport', compact('data','total_pass_user_in_first_level','total_fail_user_in_first_level'))->setPaper('a4', 'landscape');
        return $pdf->stream('BHCIP Report.pdf');
        
        }
     }
    
    public function print_report_user(Request $request)
    {
        $district =  "'".implode("','", $request->district)."'";
        $facility_id=$request->facility_id;
        $category_id=$request->category_id;
        $end_date = $request->end_date;
        $start_date=$request->start_date;
        $merge="select distinct a.*,b.name as user_name,b.email as user_email,c.quiz_name,d.level_name,d.level_id,e.name as district_name,f.name as facility_name
        from quiz_result a
        inner join users b
        on b.id=a.user_id
        inner join quiz c
        on a.quiz_id = c.quiz_id
        inner join level d
        on c.level_id=d.level_id
        inner join district e
        on a.district_id=e.district_id
        inner join facility f
        on b.facility_id=f.id
        WHERE a.district_id IN ($district) and a.percent=(select max(percent)from quiz_result where user_id=a.user_id and quiz_id=a.quiz_id)";
      
        if($facility_id!=null){
            $facility_id="'".implode("','", $request->facility_id)."'";
            $merge.="AND b.facility_id IN ($facility_id) ";
        }
        if($category_id!=null){
            $category_id="'".implode("','", $request->category_id)."'";
            $merge.="AND a.category_id IN ($category_id) ";
        }
        if($end_date || $start_date){
            $merge.="AND a.created_at between '$start_date' AND '$end_date' ";
        }
        $merge.="GROUP BY a.user_id,c.quiz_name
         ORDER BY d.level_id, a.user_id,quiz_id ASC";
      
        $Records=DB::select($merge);
   
        
        $PassFirstLevel=DB::table('users')->where('first_level_status','pass')->count();
        $FailFirstLevel=DB::table('users')->where('first_level_status','fail')->count();
        $PassSecondLevel=DB::table('users')->where('second_level_status','pass')->count();
        $FailSecondLevel=DB::table('users')->where('second_level_status','fail')->count();
        $PassThirdLevel=DB::table('users')->where('third_level_status','pass')->count();
        $FailThirdLevel=DB::table('users')->where('third_level_status','fail')->count();
        $pdf = PDF::loadView('admin.reporting.BHCIPThirdPrintReport', compact('Records','PassFirstLevel','FailFirstLevel','PassSecondLevel','FailSecondLevel','PassThirdLevel','FailThirdLevel'))->setPaper('a4', 'landscape');
        return $pdf->stream('BHCIP Report.pdf');
       
    }

    function districtOnlyPrintReport($str) {
    $facility = [];
    foreach ($str as $item) {
        $facility[] = DB::table('facility')->where('district', $item)->get()->toArray();
    }
    $combinedFacility = array_merge(...$facility);
            $data=[];
            $active_user=[];
            $in_active_user=[];
            $countUser=[];
            $facility_name=[];
            $consultant_count=[];
            $doctor_count=[];
            $lhw_count=[];
            $health_care_count=[];
            $paramedics_count=[];
            $first_level_users_count=[];
            $second_level_users_count=[];
            $third_level_query_count=[];
            $first_level_pass=[];
            $second_level_pass=[];
            $third_level_pass=[];
            $first_level_fail=[];
            $second_level_fail=[];
            $third_level_fail=[];
            $district_name=[];
            
            foreach($combinedFacility as $f){
            $facility_name[]=$f->name.'('.$f->id.')';
            $countUser[]=DB::table('users')->where('facility_id',$f->id)->count();
            $consultant_count[]=DB::table('users')->where('facility_id',intval($f->id))->where('category_id',4)->count();
            $doctor_count[]=DB::table('users')->where('facility_id',intval($f->id))->where('category_id',6)->count();
            $lhw_count[]=DB::table('users')->where('facility_id',intval($f->id))->where('category_id',7)->count();
            $health_care_count[]=DB::table('users')->where('facility_id',intval($f->id))->where('category_id',8)->count();
            $paramedics_count[]=DB::table('users')->where('facility_id',intval($f->id))->where('category_id',9)->count();
            $first_level_users_count[]=DB::table('users')->where('facility_id', intval($f->id))->where('level_status',1)->count();
            $second_level_users_count[]=DB::table('users')->where('facility_id', intval($f->id))->where('level_status',2)->count();
            $third_level_query_count[]=DB::table('users')->where('facility_id', intval($f->id))->where('level_status',3)->count();
            $active_user[]=DB::table('users')->where('facility_id', intval($f->id))->where('status',1)->count();
            $in_active_user[]=DB::table('users')->where('facility_id', intval($f->id))->where('status',0)->count();
            
            
            $first_level_pass[]=DB::table('users')->where('facility_id', $f->id)->where('first_level_status', 'pass')->count();
            $second_level_pass[]=DB::table('users')->where('facility_id', $f->id)->where('second_level_status', 'pass')->count();
            $third_level_pass[]=DB::table('users')->where('facility_id', $f->id)->where('third_level_status', 'pass')->count();
            
            $first_level_fail[]=DB::table('users')->where('facility_id', $f->id)->where('first_level_status', 'fail')->count();
            $second_level_fail[]=DB::table('users')->where('facility_id', $f->id)->where('second_level_status', 'fail')->count();
            $third_level_fail[]=DB::table('users')->where('facility_id', $f->id)->where('third_level_status', 'fail')->count();
            $district_name[] = DB::table('facility')->where('id', $f->id)
            ->join('district', 'facility.district', '=', 'district.district_id')
            ->select('district.name')->first();
            }
            $data['total_user']=$countUser;
            $data['facility_name']=$facility_name;
            $data['consultant_count']=$consultant_count;
            $data['doctor_count']=$doctor_count;
            $data['lhw_count']=$lhw_count;
            $data['health_care_count']=$health_care_count;
            $data['paramedics_count']=$paramedics_count;
            $data['first_level_users_count']=$first_level_users_count;
            $data['second_level_users_count']=$second_level_users_count;
            $data['third_level_query_count']=$third_level_query_count;
            $data['active_user']=$active_user;
            $data['in_active_user']=$in_active_user;
            
            $data['first_level_pass']=$first_level_pass;
            $data['second_level_pass']=$second_level_pass;
            $data['third_level_pass']=$third_level_pass;
            $data['first_level_fail']=$first_level_fail;
            $data['second_level_fail']=$second_level_fail;
            $data['third_level_fail']=$third_level_fail;
            $data['district_name']=$district_name;
            return $data;
        
        }
    function districtWithFacilityPrintReport($str) {
        // if only district is selected
        $facility = [];
        foreach($str['facility_id'] as $f){
        $facility[]=DB::table('facility')->where('id',$f)->first();
        }

        
        $data=[];
        $active_user=[];
        $in_active_user=[];
        $countUser=[];
        $facility_name=[];
        $consultant_count=[];
        $doctor_count=[];
        $lhw_count=[];
        $health_care_count=[];
        $paramedics_count=[];
        $first_level_users_count=[];
        $second_level_users_count=[];
        $third_level_query_count=[];
        $first_level_pass=[];
        $second_level_pass=[];
        $third_level_pass=[];
        $first_level_fail=[];
        $second_level_fail=[];
        $third_level_fail=[];
        $district_name=[];
        
        foreach($facility as $f){
        $facility_name[]=$f->name.'('.$f->id.')';
        $countUser[]=DB::table('users')->where('facility_id',$f->id)->count();
        $consultant_count[]=DB::table('users')->where('facility_id',intval($f->id))->where('category_id',4)->count();
        $doctor_count[]=DB::table('users')->where('facility_id',intval($f->id))->where('category_id',6)->count();
        $lhw_count[]=DB::table('users')->where('facility_id',intval($f->id))->where('category_id',7)->count();
        $health_care_count[]=DB::table('users')->where('facility_id',intval($f->id))->where('category_id',8)->count();
        $paramedics_count[]=DB::table('users')->where('facility_id',intval($f->id))->where('category_id',9)->count();
        $first_level_users_count[]=DB::table('users')->where('facility_id', intval($f->id))->where('level_status',1)->count();
        $second_level_users_count[]=DB::table('users')->where('facility_id', intval($f->id))->where('level_status',2)->count();
        $third_level_query_count[]=DB::table('users')->where('facility_id', intval($f->id))->where('level_status',3)->count();
        $active_user[]=DB::table('users')->where('facility_id', intval($f->id))->where('status',1)->count();
        $in_active_user[]=DB::table('users')->where('facility_id', intval($f->id))->where('status',0)->count();
        
        
        $first_level_pass[]=DB::table('users')->where('facility_id', $f->id)->where('first_level_status', 'pass')->count();
        $second_level_pass[]=DB::table('users')->where('facility_id', $f->id)->where('second_level_status', 'pass')->count();
        $third_level_pass[]=DB::table('users')->where('facility_id', $f->id)->where('third_level_status', 'pass')->count();
        
        $first_level_fail[]=DB::table('users')->where('facility_id', $f->id)->where('first_level_status', 'fail')->count();
        $second_level_fail[]=DB::table('users')->where('facility_id', $f->id)->where('second_level_status', 'fail')->count();
        $third_level_fail[]=DB::table('users')->where('facility_id', $f->id)->where('third_level_status', 'fail')->count();
         $district_name[] = DB::table('facility')->where('id', $f->id)
        ->join('district', 'facility.district', '=', 'district.district_id')
        ->select('district.name')->first();
        }
        $data['total_user']=$countUser;
        $data['facility_name']=$facility_name;
        $data['consultant_count']=$consultant_count;
        $data['doctor_count']=$doctor_count;
        $data['lhw_count']=$lhw_count;
        $data['health_care_count']=$health_care_count;
        $data['paramedics_count']=$paramedics_count;
        $data['first_level_users_count']=$first_level_users_count;
        $data['second_level_users_count']=$second_level_users_count;
        $data['third_level_query_count']=$third_level_query_count;
        $data['active_user']=$active_user;
        $data['in_active_user']=$in_active_user;
        
        $data['first_level_pass']=$first_level_pass;
        $data['second_level_pass']=$second_level_pass;
        $data['third_level_pass']=$third_level_pass;
        $data['first_level_fail']=$first_level_fail;
        $data['second_level_fail']=$second_level_fail;
        $data['third_level_fail']=$third_level_fail;
        $data['district_name']=$district_name;
        return $data;
        
        }
//     function districtWithFacilityPrintReport($str) {
        
        

//         $data=[];
//         $active_user=[];
//         $in_active_user=[];
//         $pass=[];
//         $fail=[];
//         $countUser=[];
//         $facility_name=[];
//         $first_level=[];
//         $first_level_users_count=[];
//         $second_level_users_count=[];
//         $third_level_query_count=[];
//         $third_level_query_count=[];
//         $quiz_data=[];
//         $user_profession=[];
//         $user_profession_name=[];
//         $category=$str['category_id'];
//         $facilities = [];
//         $user_data = [];
//         $user_data_count = [];
        
        
//         $facilities = [];
//         $user_data = [];
//         foreach ($str['facility_id'] as $f) {
//             $facilities[] = DB::table('facility')->where('id', $f)->first();
//         }
        
//         foreach ($facilities as $facility) {
//         $user_data['user_facility_name'] = $facility->name . '(' . $facility->id . ')';
//         $user_data['user_data_count'] = DB::table('users')->where('facility_id', $facility->id)->count();
//         $user_data['first_level_user']=DB::table('users')->where('facility_id', $facility->id)->where('level_status',1)->count();
//         $user_data['second_level_user']=DB::table('users')->where('facility_id', $facility->id)->where('level_status',2)->count();
//         $user_data['third_level_user']=DB::table('users')->where('facility_id', $facility->id)->where('level_status',3)->count();
//         $user_data['active_user']=DB::table('users')->where('facility_id', $facility->id)->where('status',1)->count();
//         $user_data['inactive_user']=DB::table('users')->where('facility_id', $facility->id)->where('status',0)->count();
//         $user_data['first_level_pass']=DB::table('users')->where('facility_id', $facility->id)->where('first_level_status','pass')->count();
//         $user_data['second_level_pass']=DB::table('users')->where('facility_id', $facility->id)->where('second_level_status','pass')->count();
//         $user_data['third_level_pass']=DB::table('users')->where('facility_id', $facility->id)->where('third_level_status','pass')->count();
//         $user_data['first_level_fail']=DB::table('users')->where('facility_id', $facility->id)->where('third_level_status','fail')->count();
//         $user_data['second_level_fail']=DB::table('users')->where('facility_id', $facility->id)->where('third_level_status','fail')->count();
//         $user_data['third_level_fail']=DB::table('users')->where('facility_id', $facility->id)->where('third_level_status','fail')->count();
        
//         $district_name = DB::table('facility')->where('id', $facility->id)
//         ->join('district','facility.district','=','district.district_id')
//         ->select('district.name')->first();
//         $user_data[$catId]['district_name'][]=$district_name;
//         }
     
    
//   dd($user_data);
//   return $user_data;
//         }
    function districtWithFacilityCategoryPrintReport($str) {
        
        
        $quiz_data_query=DB::table('quiz_result');
        $data=[];
        $active_user=[];
        $in_active_user=[];
        $pass=[];
        $fail=[];
        $countUser=[];
        $facility_name=[];
        $first_level=[];
        $first_level_users_count=[];
        $second_level_users_count=[];
        $third_level_query_count=[];
        $third_level_query_count=[];
        $quiz_data=[];
        $user_profession=[];
        $user_profession_name=[];
        $category=$str['category_id'];
        $facilities = [];
        $user_data = [];
        $user_data_count = [];
        
        
        $facilities = [];
        $user_data = [];
        foreach ($str['facility_id'] as $f) {
            $facilities[] = DB::table('facility')->where('id', $f)->first();
        }
        
        foreach ($category as $catId) {
        $user_category = DB::table('category')->where('id', $catId)->select('name')->first();
    // Check if the category is found
    if ($user_category) {
        $user_data[$catId]['user_cateroy_name'] = $user_category;
        $user_data[$catId]['user_facility_name'] = [];
        $user_data[$catId]['user_data_count'] = [];
        $user_data[$catId]['first_level_user'] = []; 
        $user_data[$catId]['second_level_user'] = [];
        $user_data[$catId]['third_level_user'] = []; 
        $user_data[$catId]['active_user'] = []; 
        $user_data[$catId]['inactive_user'] = []; 
        $user_data[$catId]['first_level_pass'] = []; 
        $user_data[$catId]['second_level_pass'] = []; 
        $user_data[$catId]['third_level_pass'] = []; 
        $user_data[$catId]['first_level_fail'] = []; 
        $user_data[$catId]['second_level_fail'] = []; 
        $user_data[$catId]['third_level_fail'] = [];
        $user_data[$catId]['district_name'][]=[];
        foreach ($facilities as $facility) {
        $user_data[$catId]['user_facility_name'][] = $facility->name . '(' . $facility->id . ')';
        $user_data[$catId]['user_data_count'][] = DB::table('users')->where('category_id', $catId)->where('facility_id', $facility->id)->count();
        // $user_data[$catId]['user_data_count'][] = DB::table('users')->where('category_id', $catId)->where('facility_id', $facility->id)->count();
        $user_data[$catId]['first_level_user'][]=DB::table('users')->where('category_id',$catId)->where('facility_id', $facility->id)->where('level_status',1)->count();
        $user_data[$catId]['second_level_user'][]=DB::table('users')->where('category_id',$catId)->where('facility_id', $facility->id)->where('level_status',2)->count();
        $user_data[$catId]['third_level_user'][]=DB::table('users')->where('category_id',$catId)->where('facility_id', $facility->id)->where('level_status',3)->count();
        $user_data[$catId]['active_user'][]=DB::table('users')->where('category_id',$catId)->where('facility_id', $facility->id)->where('status',1)->count();
        $user_data[$catId]['inactive_user'][]=DB::table('users')->where('category_id',$catId)->where('facility_id', $facility->id)->where('status',0)->count();
        $user_data[$catId]['first_level_pass'][]=DB::table('users')->where('category_id',$catId)->where('facility_id', $facility->id)->where('first_level_status','pass')->count();
        $user_data[$catId]['second_level_pass'][]=DB::table('users')->where('category_id',$catId)->where('facility_id', $facility->id)->where('second_level_status','pass')->count();
        $user_data[$catId]['third_level_pass'][]=DB::table('users')->where('category_id',$catId)->where('facility_id', $facility->id)->where('third_level_status','pass')->count();
        $user_data[$catId]['first_level_fail'][]=DB::table('users')->where('category_id',$catId)->where('facility_id', $facility->id)->where('third_level_status','fail')->count();
        $user_data[$catId]['second_level_fail'][]=DB::table('users')->where('category_id',$catId)->where('facility_id', $facility->id)->where('third_level_status','fail')->count();
        $user_data[$catId]['third_level_fail'][]=DB::table('users')->where('category_id',$catId)->where('facility_id', $facility->id)->where('third_level_status','fail')->count();
        
        $district_name = DB::table('facility')->where('id', $facility->id)
        ->join('district','facility.district','=','district.district_id')
        ->select('district.name')->first();
        $user_data[$catId]['district_name'][]=$district_name;
        }
    } 
    
}
  return $user_data;
        }
        
        
        
        
        
        
        
        
        
        
        
        
    function districtWithFacilityCategoryPrintReportWithdate($str) {
        
        
        $quiz_data_query=DB::table('quiz_result');
        $data=[];
        $active_user=[];
        $in_active_user=[];
        $pass=[];
        $fail=[];
        $countUser=[];
        $facility_name=[];
        $first_level=[];
        $first_level_users_count=[];
        $second_level_users_count=[];
        $third_level_query_count=[];
        $third_level_query_count=[];
        $quiz_data=[];
        $user_profession=[];
        $user_profession_name=[];
        $category=$str['category_id'];
        $facilities = [];
        $user_data = [];
        $user_data_count = [];
        
        
        $facilities = [];
        $user_data = [];
        foreach ($str['facility_id'] as $f) {
            $facilities[] = DB::table('facility')->where('id', $f)->first();
        }
        
        foreach ($category as $catId) {
        $user_category = DB::table('category')->where('id', $catId)->select('name')->first();
    // Check if the category is found
    if ($user_category) {
        $user_data[$catId]['user_cateroy_name'] = $user_category;
        $user_data[$catId]['user_facility_name'] = [];
        $user_data[$catId]['user_data_count'] = [];
        $user_data[$catId]['first_level_user'] = []; 
        $user_data[$catId]['second_level_user'] = [];
        $user_data[$catId]['third_level_user'] = []; 
        $user_data[$catId]['active_user'] = []; 
        $user_data[$catId]['inactive_user'] = []; 
        $user_data[$catId]['first_level_pass'] = []; 
        $user_data[$catId]['second_level_pass'] = []; 
        $user_data[$catId]['third_level_pass'] = []; 
        $user_data[$catId]['first_level_fail'] = []; 
        $user_data[$catId]['second_level_fail'] = []; 
        $user_data[$catId]['third_level_fail'] = [];
        $user_data[$catId]['district_name'][]=[];
        if ($str['start_date']) {
            $startDate = $str['start_date'];
            $endDate = $str['end_date'] ?? date('Y-m-d');
        
            $dateRangeCondition = function ($query) use ($startDate, $endDate) {
                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }
               };
              }
              
        foreach ($facilities as $facility) {
        $user_data[$catId]['user_facility_name'][] = $facility->name . '(' . $facility->id . ')';
        $user_data[$catId]['user_data_count'][] = DB::table('users')->where('category_id', $catId)->where('facility_id', $facility->id)->where($dateRangeCondition)->count();
        // $user_data[$catId]['user_data_count'][] = DB::table('users')->where('category_id', $catId)->where('facility_id', $facility->id)->where($dateRangeCondition)->count();
        $user_data[$catId]['first_level_user'][]=DB::table('users')->where('category_id',$catId)->where('facility_id', $facility->id)->where('level_status',1)->where($dateRangeCondition)->count();
        $user_data[$catId]['second_level_user'][]=DB::table('users')->where('category_id',$catId)->where('facility_id', $facility->id)->where('level_status',2)->where($dateRangeCondition)->count();
        $user_data[$catId]['third_level_user'][]=DB::table('users')->where('category_id',$catId)->where('facility_id', $facility->id)->where('level_status',3)->where($dateRangeCondition)->count();
        $user_data[$catId]['active_user'][]=DB::table('users')->where('category_id',$catId)->where('facility_id', $facility->id)->where('status',1)->where($dateRangeCondition)->count();
        $user_data[$catId]['inactive_user'][]=DB::table('users')->where('category_id',$catId)->where('facility_id', $facility->id)->where('status',0)->where($dateRangeCondition)->count();
        $user_data[$catId]['first_level_pass'][]=DB::table('users')->where('category_id',$catId)->where('facility_id', $facility->id)->where('first_level_status','pass')->where($dateRangeCondition)->count();
        $user_data[$catId]['second_level_pass'][]=DB::table('users')->where('category_id',$catId)->where('facility_id', $facility->id)->where('second_level_status','pass')->where($dateRangeCondition)->count();
        $user_data[$catId]['third_level_pass'][]=DB::table('users')->where('category_id',$catId)->where('facility_id', $facility->id)->where('third_level_status','pass')->where($dateRangeCondition)->count();
        $user_data[$catId]['first_level_fail'][]=DB::table('users')->where('category_id',$catId)->where('facility_id', $facility->id)->where('third_level_status','fail')->where($dateRangeCondition)->count();
        $user_data[$catId]['second_level_fail'][]=DB::table('users')->where('category_id',$catId)->where('facility_id', $facility->id)->where('third_level_status','fail')->where($dateRangeCondition)->count();
        $user_data[$catId]['third_level_fail'][]=DB::table('users')->where('category_id',$catId)->where('facility_id', $facility->id)->where('third_level_status','fail')->where($dateRangeCondition)->count();
        
        $district_name = DB::table('facility')->where('id', $facility->id)
        ->join('district','facility.district','=','district.district_id')
        ->select('district.name')->first();
        $user_data[$catId]['district_name'][]=$district_name;
        }
    } 
    
}
  $start_date = date('d-n-Y', strtotime($startDate));
  $end_date = date('d-n-Y', strtotime($endDate));

  return [$user_data,$start_date,$end_date];
        }
     public function filterFacility(Request $requests)
    {
        $facility = [];
        if($requests->district){
        foreach ($requests->district as $district_id) {
        $facilities = DB::table('facility')->where('district', intval($district_id))->get()->toArray();
        $facility[] = $facilities;
        $result = [];
        foreach ($facility as $facilities) {
        $result = array_merge($result, $facilities);
        }    
        }
        }
        else{
            $result=[];
        }
        $data = [
            'facility' => $result,
        ];
        

        return response()->json($data);
        
    }
     public function dummyFacility(Request $requets)
    {
        return view('admin.reporting.dymmyreqport');
    }
    

  
}
