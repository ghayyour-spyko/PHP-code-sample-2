<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use Validator;
use App\User;
use App\Community;
use Illuminate\Support\Facades\Mail;
use App\CommunityUser;
use App\Category;
use App\Standard;

class CommunityController extends Controller {

	public $community_selected;
	public $sites;
	public $role;
	public $permissions;

	public function __construct(){
		parent::__construct();
        if(!Auth::Guest()) {
            $this->role =  $this->returnRole();
            $user = Auth::User();
            $perm = $user->getPermissionMap();
            if ($perm->role->id == 1) {
                $this->community_selected = $perm->communities;
                $this->sites = $perm->sites;
            } else if ($perm->role->id == 3) {
                $this->community_selected = $perm;
                $this->sites = $this->community_selected->sites;

            } else {
                $this->sites = $perm;
                $this->community_selected = isset($perm->community) ? $perm->community : null;
            }
            $role = $this->role;
        }else{
            return redirect('/portal/');
        }
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{

	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		// dd($request->all());
		
	}

	public function addNewCommunity(Request $request){
		$edit = $request->input('edit_id');
		$validation=Validator::make($request->all(),
			[
				'community_name' => 'string|min:3|required',
				'email' => 'email|required',
				'password' => 'string|min:6|required'
			]);
		if($validation->fails())
		{	
			return redirect()->back()->withErrors($validation->errors());
		}

			$community_name = $request->input('community_name');
			$username = $request->input('username');
			$email =  $request->input('email');
			$password =  $request->input('password');

		if(empty($edit)) {
			$user = User::create(['name' => $username, 'email' => $email, 'password' => $password, 'role_id' => 3]);
			// dd($user);
			$community = Community::create(['name' => $community_name, 'user_id' => $user->id ]);
			$community_user = CommunityUser::create(['community_id' => $community->id, 'user_id' => $user->id, 'role_id' => 3 ]);

			$obj = new \stdClass();
			$obj->name = $user->name;
			$obj->password = $password;
			$obj->email = $email;
			$this->sendMail($obj);
		}
		else
		{
		// dd($edit);
			$community = Community::find($edit);
			// dd($community);
			$community->name = $community_name;
			$user = $community->user;
			dd($user);
			$user->name = $username;
			$user->email = $email;
			$user->password = $password;
			dd($user);
			$user->save();
			$community->save();

		}
		return redirect()->action('AdminController@index');
	}

	public function editCommunityUser(Request $request) {
		$validation=Validator::make($request->all(),
			[
				'community_name' => 'string|min:3|required',
				'email' => 'email|required',
				'password' => 'string|min:6|required'
			]);
		if($validation->fails())
		{	
			return redirect()->back()->withErrors($validation->errors());
		}

		$user = User::find($id);
		$user->email = $request->input('email');
	}
	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	// public function update($id)
	// {
	// 	//
	// }

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

	public function sendMail($user, $template = null){

		if(is_null($template))
		{
			$template = 'admin.email.user-register';	
		}
		$data=['username' => $user->email, 'password' => $user->password];
		$email = $user->email;
		Mail::send($template, $data, function($message) use($email)
		{
			$message->to($email, 'Fitkit')->subject('Welcome!');
            $message->bcc('qadeercloudtech@gmail.com','qadeer ,CloudTech');
            $message->bcc('jeremysilva@silvatechsolutions.com','jeremy silva');
            $message->bcc('shayan.iplex@gmail.com','Shayan');
		});
		// return view('admin.email-cp-administration');
	}
	public function getAllAssessmentsForAdmin($sites){
		if(is_null($sites)) {
		}
		$users = $sites->map(function($site){
			return $site->user;
		});
		$assessments = $users->map(function($user) {

			return $user->getAssessments();
		});
		// $assessments_arr = $assessments->flatten()->all();
		$assessments = $assessments->reject(function($obj){	
			return $obj->count()==0;
		})->map(function($obj){
			return $obj->all();
		});
		$assessments = $assessments->flatten();
	
		return $assessments;
	}

	public function calcChartData($assessments){
		$chart_data = collect();
		$standard = collect();
		foreach ($assessments as $aid => $aval) {
			
			$obj = new \stdClass;
			$obj->answers = null;
			$obj->id = $aval->id;

			if($aval->status==1){
				$_ans = $aval->getAnswers();
				$_cd = $aval->getChartData();
				$obj->answers = $_ans;
				$obj->chart = $_cd;
				$obj->assessment_id = $aval->id;
				$standards = Standard::get();
				$chart = $standards->map(function($standard) use($_cd) {
					$chart_val = new \stdClass();
					$rt = $_cd->where('standard_id', $standard->id)->first();
					if(!is_null($rt)){
						$chart_val->answer = $rt->ans_value;
						
					}
					$chart_val->standard_id = $standard->id;
					return $chart_val;

				});
				$obj->chart_data = $chart;
			}

			$chart_data->put($aval->id , $obj);
		}
		return $chart_data;
	}

	public function updateCommunity(Request $request){
        $validation=Validator::make($request->all(),
            [
                'community_name' => 'string|min:3|required',
                'email' => 'email|required',
                'password' => 'string|min:6|required'
            ]);
        if($validation->fails())
        {
            return redirect()->back()->withErrors($validation->errors());
        }
	   $com=Community::find($request->input('edit_id'))
            ->update(['name'=>$request->input('community_name'),'status'=>$request->input('selector')[0]]);
        $user=User::find($request->input('user_id'))
            ->update(['name'=>$request->input('username'),'email'=>$request->input('email'),'first_name'=>$request->input('first_name'),'last_name'=>$request->input('last_name'),'password'=>$request->input('password')]);
        return redirect('/portal/sadmin/owner');
    }
	private function getStandardValue($assessment_id, $standard_id, $chart) {
		$assessment_info = $chart[$assessment_id];
		$arr = $assessment_info->toArray();
		$answer = 0;
		if(array_key_exists($standard_id, $arr)) {
				$value = $assessment_info[$standard_id];

				// dd($value->answer);
				$answer = (int)$value->answer;
		}
		return $answer;
	}

	public function processAssessments($assessments) {
		$standards = Standard::orderBy('id')->get();
		$chart = $assessments->map(function($assessment) use($standards){
			if(isset($assessment->chart_data)){
				$chrt = $assessment->chart_data;
				$char = collect();
				$stan = $standards->map(function($st) use($chrt, $char) {
					$dt = $chrt->where('standard_id', $st->id)->first();
					if(isset($dt->answer))
						$char->put($st->id,$dt);
					return $dt;
				});
				return $char;
			}
				
		});
		// dd($chart->reject(null));
		// dd($chart);
		$chart = $chart->reject(function($c) {return is_null($c);});
		
		foreach ($standards as $sk => $sv) {
			$char[$sv->id] = $chart->filter(function($ch) use($sv) {
				return $ch->where('standard_id', $sv->id);
			});
		}
		return $chart;
	}

	/**
	 * calculates average for each assessment passed
	 * @param  Collection/Assessments $data assessments collection
	 * @return object       returns the average object
	 */
	public function calcAvg($data){
		$cat = Category::orderBy('category_name', 'ASC')->get();
		$standards = Standard::get();
		$arr = collect();

		// iterate and create the standard results for all data, each data item represent an assessment

			// foreach ($cat as $ck => $cv) {
			// 	${"cat_". $ck} = 0;
			// 	$count =0;
			// 		foreach ($cv->standards as $k => $v) {
			// 			${"cat_".$ck} += $this->getStandardValue($dkl, $v->id ,$data);
			// 			if(!empty($v->type)) {
			// 				$count++;
			// 			}
			// 	}
			// 	$avg = ${"cat_".$ck}/$count;
			// 	$favg = number_format($avg);
			// 	$arr->put($cv->id, $favg);
			// }
		// dd($arr);
		$average_obj = $cat->map(function($ct) use($data) {
			$ct_obj = new \stdClass();
			$ct_obj->category = $ct;
			$ct_obj->averages = collect();

			$cvalues = collect();
			$values = $ct->standards;
			foreach ($values as $sk => $sv) {
				$avg_obj = new \stdClass();
				$avg_obj->standard = $sv;
				
				${"st_avg_". $sk} = 0;
				$count =0;
				foreach ($data as $dkl => $dvl) {
					${"st_avg_".$sk} += $this->getStandardValue($dkl, $sv->id ,$data);
					if(!empty($sv->type)) {
						$count++;
					}
				}
				$avg = ${"st_avg_".$sk}==0?0:${"st_avg_".$sk}/$count;
				$favg = number_format($avg);
				$avg_obj->average = $favg;
				// $avg_obj->__data = $data;
				$ct_obj->averages->push($avg_obj);
			}
				return $ct_obj;
			// return $cvalues;
	
		});
		return ($average_obj);
	}
}
