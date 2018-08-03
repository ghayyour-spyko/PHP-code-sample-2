<?php namespace App;

use App\Http\Requests\Request;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

	use Authenticatable, CanResetPassword;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name', 'email', 'password', 'role_id','first_name','last_name'];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token'];

	Public function setPasswordAttribute($password) 
	{ 
		return $this->attributes['password'] = bcrypt($password); 
	}

    public function assessments(){
        return $this->belongsToMany('App\self_assessment_tests','user_assessment');
    }

    public function tests(){
        return $this->hasMany('App\user_assessment','user_id');
    }
    public function community(){
        return $this->hasManyThrough('App\CommunityUser','App\Community')->whereNotNull('community_id');
    }

    public function permission(){
    	return $this->belongsToMany('App\Community','community_users')->withPivot(['user_id','role_id','community_id']);
    	// return $this->belongsToMany('App\Community');
    }
    public function site_permissions(){ return $this->hasOne('App\Site');}
    
    public function role(){
    	return $this->belongsToMany('App\Roles','community_users','id','role_id')->withPivot(['user_id','role_id','community_id']);

    }

    public function user_role() {
        return $this->belongsTo('App\Roles', 'role_id');
    }

    public function getFullNameAttribute() {
    	if(empty($this->first_name) || empty($this->last_name)){
    		return $this->name;
    	}
        return ucfirst($this->first_name) . ' ' . ucfirst($this->last_name);
    }
    
    public function hasPermissionOnSite($site_id){
    	if(is_null($this)){ return null; }
    	if(!is_null($this->permission)){
	    	$permission = $this->permission->where('pivot.community_id',$site_id);
	    	foreach ($permission as $idx=>$perm) {
	    		$role = Roles::findOrFail($perm->pivot->role_id);
	    		# code...
	    		$permission[$idx]->role = $role||null;
	    	}
	    	return $permission->count()>0;
	    }
	    else
	    {
	    	return null;
	    }


    }
    public function hasPermissionOnCommunity($community_id){
    	if(is_null($this)){ return null; }
    	if(!is_null($this->site_permissions)){
	    	$permission = $this->community_permissions->where('pivot.community_id',$community_id);
	    	foreach ($permission as $idx=>$perm) {
	    		$role = Roles::findOrFail($perm->pivot->role_id);
	    		# code...
	    		$permission[$idx]->role = $role||null;
	    	}
	    	return $permission->count()>0;
	    }
	    else
	    {
	    	return null;
	    }


    }

    public function getPermissionMap()
    {	
    	if($this->role_id==1)
    	{
    		$perm = new \stdClass();
    		$perm->communities = Community::where('status',1)->get();
    		$perm->role = Roles::find(1);
    		$perm->sites = Site::get();
    		return $perm;
    	}
    	else if($this){
	    	$perm = $this->permission->first();
	    	if($perm){
	    		$role = Roles::findOrFail($perm->pivot->role_id);
	    			$perm->role = $role;
	    			return $perm;
	    	}
	    	else{
	    		// $this->role = Roles::find(2);
                $perm = $this->site_permissions;
                // dd($this->site_permissions);
                if(is_null($perm)){
                	$perm = new \stdClass();
                }
                $perm->role = Roles::findOrFail(2);
                // dd($perm);
            //    // $perm->site_permissions = $this->site_permissions;
	    		return $perm;
	    	}
    	}
    	else {

            return "user not found";
        }
    }

    public function getSite(){
    	return Site::where('user_id',$this->id)->first();
    }

    public function getAssessments(){
    	return user_assessment::where('user_id', $this->id)->get();
    }
    public function orders(){
        return $this->hasMany('App\ResOrder','users_id');
    }
}
