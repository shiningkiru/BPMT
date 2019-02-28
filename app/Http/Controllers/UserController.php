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
        $allusers=User::leftJoin('mass_parameters','mass_parameters.id','=','users.designation_id')->select('users.id','users.employeeId', 'users.firstName','users.lastName','users.email','users.mobileNumber','users.dob','users.doj','users.roles','users.address','users.profilePic','users.salary','users.bloodGroup','users.relievingDate','mass_parameters.type','mass_parameters.title')->get();
        return $allusers;
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
   //rolewise user list end

   public function designationFilter($id)
    {
        $allusers=User::leftJoin('mass_parameters','mass_parameters.id','=','users.designation_id')->select('users.id','users.employeeId', 'users.firstName','users.lastName','users.email','users.mobileNumber','users.dob','users.doj','users.roles','users.address','users.profilePic','users.salary','users.bloodGroup','users.relievingDate','mass_parameters.type','mass_parameters.title')->where('designation_id','=', $id)->paginate(10);
        return $allusers;
    }
}