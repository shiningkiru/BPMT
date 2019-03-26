<?php

namespace App\Http\Controllers;
use App\User;
use Response;
use App\BranchDepartment;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterFormRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Controllers\Master\MasterController;


class UserController extends MasterController
{
    
   public function __construct()
   {
        parent::__construct(new UserRepository());
   }


     /**
     * @SWG\Get(
     *      path="/v1/user",
     *      operationId="list of Users",
     *      tags={"Users"},
     *      summary="Users list",
     *      description="Returns Users list",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation"
     *       ),
     *       @SWG\Response(response=500, description="Internal server error"),
     *       @SWG\Response(response=400, description="Bad request"),
     *     )
     *
     * Returns list of Users
     */
    public function getAllUsers()
    {
        //$allusers=User::leftJoin('mass_parameters','mass_parameters.id','=','users.designation_id')->select('users.id','users.employeeId', 'users.firstName','users.lastName','users.email','users.mobileNumber','users.dob','users.doj','users.roles','users.address','users.profilePic','users.salary','users.bloodGroup','users.team_lead','users.relievingDate','mass_parameters.type','mass_parameters.title')->get();
        
        $user = Auth::user();

        $users = User::leftJoin('mass_parameters as designation_t', 'designation_t.id', 'users.designation_id')
                        ->leftJoin('branch_departments', 'branch_departments.id', '=', 'users.branch_dept_id')
                        ->leftJoin('branches', 'branches.id', '=', 'branch_departments.branches_id')
                        ->leftJoin('mass_parameters as department_tb', 'department_tb.id', '=', 'branch_departments.dept_id')
                        ->where('users.id', '<>', $user->id)
                        ->select('users.id', 'users.firstName', 'users.lastName', 'users.email', 'users.employeeId', 'users.profilePic', \DB::raw('CONCAT(users.firstName, " ", users.lastName) as fullName'), 'users.mobileNumber', 'designation_t.title as designation', 'department_tb.title as department', 'branches.branchName')
                        ->distinct('users.id', 'users.firstName', 'users.lastName', 'users.email', 'users.employeeId', 'users.profilePic', 'users.mobileNumber', 'designation_t.title', 'department_tb.title', 'branches.branchName')
                        ->get();
        return $users;
    }

     /**
     * @SWG\Get(
     *      path="/v1/user/show-email/{email}",
     *      operationId="show-email",
     *      tags={"Users"},
     *      summary="Current User Profile pic",
     *      description="Returns User Profile pic",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="email",
     *          description="email of the user",
     *          required=true,
     *          type="string",
     *          in="path"
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation"
     *       ),
     *       @SWG\Response(response=500, description="Internal server error"),
     *       @SWG\Response(response=400, description="Bad request"),
     *     )
     *
     * Returns User Profile Pic
     */
    public function show($email)
    {
        $user=User::where('email','=',$email)->select('profilePic')->first();
        $status= response()->json($user, 200);
        if($user){
            return $status= response()->json($user, 200);
        }else{
            return response("Invalid Email ID",500);
        }  
    }

    public function deleteUser(Request $request, $id){
        $user = User::find($id);
        try{
            $user->delete();
        }catch(\Exception $e){
            return Response::json(['errors'=>['user'=>['Can not delete user']]], 422);
        }
    }


    /**
    * @SWG\Post(
    *      path="/v1/reset-password",
    *      operationId="reset-password",
    *      tags={"Users"},
    *      summary="reset user password",
    *      description="reset user password",
    *      @SWG\Parameter(
    *          name="Authorization",
    *          description="authorization header",
    *          required=true,
    *          type="string",
    *          in="header"
    *      ),
    *      @SWG\Parameter(
    *          name="user_id",
    *          description="id of the user",
    *          required=true,
    *          type="string",
    *          in="formData"
    *      ),
    *      @SWG\Parameter(
    *          name="password",
    *          description="password of the user",
    *          required=true,
    *          type="string",
    *          in="formData"
    *      ),
    *      @SWG\Parameter(
    *          name="password_confirmation",
    *          description="confirm password",
    *          required=true,
    *          type="string",
    *          in="formData"
    *      ),
    *      @SWG\Response(
    *          response=200,
    *          description="successful operation"
    *       ),
    *       @SWG\Response(response=500, description="Internal server error"),
    *       @SWG\Response(response=400, description="Bad request"),
    *     )
    *
    * Returns success message
    */
   public function resetPassword(ResetPasswordRequest $request){
        $user=User::find($request->user_id);
        $user->password=bcrypt($request->password);
        $user->save();
        return $user;
   }



   //rolewise user list

     /**
     * @SWG\Get(
     *      path="/v1/user/admin",
     *      operationId="admin-show",
     *      tags={"Users"},
     *      summary="Admin User list",
     *      description="Admin User list",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation"
     *       ),
     *       @SWG\Response(response=500, description="Internal server error"),
     *       @SWG\Response(response=400, description="Bad request"),
     *     )
     *
     * Returns User Profile Pic
     */
    public function adminShow()
    {
        return $this->model->findByRole('admin');
    }

    
     /**
     * @SWG\Get(
     *      path="/v1/user/hr",
     *      operationId="hr-show",
     *      tags={"Users"},
     *      summary="hr User list",
     *      description="hr User list",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation"
     *       ),
     *       @SWG\Response(response=500, description="Internal server error"),
     *       @SWG\Response(response=400, description="Bad request"),
     *     )
     *
     * Returns User Profile Pic
     */
    public function hrShow()
    {
        return $this->model->findByRole('hr');
    }

    
    /**
    * @SWG\Get(
    *      path="/v1/user/management",
    *      operationId="management-show",
    *      tags={"Users"},
    *      summary="management User list",
    *      description="management User list",
    *      @SWG\Parameter(
    *          name="Authorization",
    *          description="authorization header",
    *          required=true,
    *          type="string",
    *          in="header"
    *      ),
    *      @SWG\Response(
    *          response=200,
    *          description="successful operation"
    *       ),
    *       @SWG\Response(response=500, description="Internal server error"),
    *       @SWG\Response(response=400, description="Bad request"),
    *     )
    *
    * Returns User Profile Pic
    */
   public function managementShow()
   {
       return $this->model->findByRole('management');
   }

    
   /**
   * @SWG\Get(
   *      path="/v1/user/team-lead",
   *      operationId="team-lead-show",
   *      tags={"Users"},
   *      summary="team-lead User list",
   *      description="team-lead User list",
   *      @SWG\Parameter(
   *          name="Authorization",
   *          description="authorization header",
   *          required=true,
   *          type="string",
   *          in="header"
   *      ),
   *      @SWG\Response(
   *          response=200,
   *          description="successful operation"
   *       ),
   *       @SWG\Response(response=500, description="Internal server error"),
   *       @SWG\Response(response=400, description="Bad request"),
   *     )
   *
   * Returns User Profile Pic
   */
  public function teamleadShow()
  {
      return $this->model->findByRole('team-lead');
  }

    
  /**
  * @SWG\Get(
  *      path="/v1/user/project-lead",
  *      operationId="project-lead-show",
  *      tags={"Users"},
  *      summary="project-lead User list",
  *      description="project-lead User list",
  *      @SWG\Parameter(
  *          name="Authorization",
  *          description="authorization header",
  *          required=true,
  *          type="string",
  *          in="header"
  *      ),
  *      @SWG\Response(
  *          response=200,
  *          description="successful operation"
  *       ),
  *       @SWG\Response(response=500, description="Internal server error"),
  *       @SWG\Response(response=400, description="Bad request"),
  *     )
  *
  * Returns User Profile Pic
  */
 public function projectleadShow()
 {
     return $this->model->findByRole('project-lead');
 }

    
 /**
 * @SWG\Get(
 *      path="/v1/user/project-lead-and-management",
 *      operationId="project-lead-show",
 *      tags={"Users"},
 *      summary="project-lead User list",
 *      description="project-lead User list",
 *      @SWG\Parameter(
 *          name="Authorization",
 *          description="authorization header",
 *          required=true,
 *          type="string",
 *          in="header"
 *      ),
 *      @SWG\Response(
 *          response=200,
 *          description="successful operation"
 *       ),
 *       @SWG\Response(response=500, description="Internal server error"),
 *       @SWG\Response(response=400, description="Bad request"),
 *     )
 *
 * Returns User Profile Pic
 */
public function projectleadAndManagementShow()
{
    return User::where('roles','project-lead')->orWhere('roles','management')->get();
}

    
    /**
     * @SWG\Get(
     *      path="/v1/user/employee",
     *      operationId="employee-show",
     *      tags={"Users"},
     *      summary="employee User list",
     *      description="employee User list",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation"
     *       ),
     *       @SWG\Response(response=500, description="Internal server error"),
     *       @SWG\Response(response=400, description="Bad request"),
     *     )
     *
     * Returns User Profile Pic
     */
    public function employeeShow()
    {
        return $this->model->findByRole('employee');
    }

    
    /**
     * @SWG\Get(
     *      path="/v1/user/reporting-manager",
     *      operationId="reporting-manager-show",
     *      tags={"Users"},
     *      summary="reporting-manager User list",
     *      description="reporting-manager User list",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="dept_id",
     *          description="Department ID",
     *          required=true,
     *          type="string",
     *          in="path"
     *      ),
     *      @SWG\Parameter(
     *          name="branch_id",
     *          description="Branch ID",
     *          required=true,
     *          type="string",
     *          in="path"
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation"
     *       ),
     *       @SWG\Response(response=500, description="Internal server error"),
     *       @SWG\Response(response=400, description="Bad request"),
     *     )
     *
     * Returns User Profile Pic
     */
    public function reportingManagerShow(Request $request)
    {
        $user= \Auth::user();
        $branchDepartment=BranchDepartment::where('branches_id','=',$request->branch_id)->where('dept_id','=',$request->dept_id)->first();
        if(!($branchDepartment instanceof BranchDepartment)){
            $branchDepartment=new BranchDepartment();
            $branchDepartment->branches_id=$request->branch_id;
            $branchDepartment->dept_id=$request->dept_id;
            $branchDepartment->save();
        }
        return User::where(function($query) use ($branchDepartment){
                $query->where('branch_dept_id','=',$branchDepartment->id)
                    ->where('roles', '=', 'team-lead');
            })
            ->orWhere(function($query){
                $query->where('roles','=','management')
                    ->orWhere('roles','=','admin');
            })
            ->get();
    }

   //rolewise user list end
   public function designationFilter($id)
    {
        $allusers=User::leftJoin('mass_parameters','mass_parameters.id','=','users.designation_id')->select('users.id','users.employeeId', 'users.firstName','users.lastName','users.email','users.mobileNumber','users.dob','users.doj','users.roles','users.address','users.profilePic','users.salary','users.bloodGroup','users.relievingDate','mass_parameters.type','mass_parameters.title')->where('designation_id','=', $id)->paginate(10);
        return $allusers;
    }

    //get all the members of project lead
    public function getProjectMembers(Request $request){
        $user = Auth::user();

        $users = User::leftJoin('project_teams', 'users.id', '=', 'project_teams.team_user_id')
                        ->leftJoin('projects', 'projects.id', '=', 'project_teams.team_project_id')
                        ->leftJoin('mass_parameters as designation_t', 'designation_t.id', 'users.designation_id')
                        ->leftJoin('branch_departments', 'branch_departments.id', '=', 'users.branch_dept_id')
                        ->leftJoin('branches', 'branches.id', '=', 'branch_departments.branches_id')
                        ->leftJoin('mass_parameters as department_tb', 'department_tb.id', '=', 'branch_departments.dept_id')
                        ->where('projects.project_lead_id', '=', $user->id)
                        ->where('users.id', '<>', $user->id)
                        ->select('users.id', 'users.email', \DB::raw('CONCAT(users.firstName, " ", users.lastName) as fullName'), 'users.mobileNumber', 'designation_t.title as designation', 'department_tb.title as department', 'branches.branchName')
                        ->distinct('users.id', 'users.firstName', 'users.lastName', 'users.email', 'users.mobileNumber', 'designation_t.title', 'department_tb.title', 'branches.branchName')
                        ->get();
        return $users;
    }

    //get all the members of team lead
    public function getMyReportingMembers(Request $request){
        $user = Auth::user();

        $users = User::leftJoin('mass_parameters as designation_t', 'designation_t.id', 'users.designation_id')
                        ->leftJoin('branch_departments', 'branch_departments.id', '=', 'users.branch_dept_id')
                        ->leftJoin('branches', 'branches.id', '=', 'branch_departments.branches_id')
                        ->leftJoin('mass_parameters as department_tb', 'department_tb.id', '=', 'branch_departments.dept_id')
                        ->where('users.team_lead', '=', $user->id)
                        ->where('users.id', '<>', $user->id)
                        ->select('users.id', 'users.email', \DB::raw('CONCAT(users.firstName, " ", users.lastName) as fullName'), 'users.mobileNumber', 'designation_t.title as designation', 'department_tb.title as department', 'branches.branchName')
                        ->distinct('users.id', 'users.firstName', 'users.lastName', 'users.email', 'users.mobileNumber', 'designation_t.title', 'department_tb.title', 'branches.branchName')
                        ->get();
        return $users;
    }
}