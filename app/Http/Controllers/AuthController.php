<?php

namespace App\Http\Controllers;

use App\User;
use App\BranchDepartment;
use App\Mail\ForgotPassword;
use Illuminate\Http\Request;
use App\Helpers\HelperFunctions;
use Illuminate\Http\UploadedFile;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\UserEmailRequest;
use App\Http\Requests\RegisterFormRequest;
use App\Http\Requests\ForgotPasswordRequest;


class AuthController extends Controller
{
    /**
     * @SWG\Post(
     *      path="/v1/user",
     *      operationId="careat-update-user",
     *      tags={"Auth"},
     *      summary="Register user",
     *      description="Returns new user",
     *      @SWG\Parameter(
     *          name="id",
     *          description="User Id at the time of update",
     *          required=false,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="firstName",
     *          description="firstName of the user",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="lastName",
     *          description="lastName of the user",
     *          required=false,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="mobileNumber",
     *          description="mobileNumber of the user",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="email",
     *          description="email of the user",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="address",
     *          description="address of the user",
     *          required=false,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="dob",
     *          description="date of birth of the user",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="doj",
     *          description="date of join of the user",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="salary",
     *          description="salary of the user",
     *          required=true,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="bloodGroup",
     *          description="bloodGroup of the user",
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
     *          name="company_id",
     *          description="company_id of the user",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="designation_id",
     *          description="designation_id of the user",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="profilePic",
     *          description="profilePic of the user",
     *          required=false,
     *          type="file",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="branch_id",
     *          description="branch id of the user",
     *          required=true,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="dept_id",
     *          description="department id of the user",
     *          required=true,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="roles",
     *          description="role of the user(admin/management/hr/team-lead/project-lead/employee)",
     *          required=false,
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
     * Returns new user details
     */
    public function register(RegisterFormRequest $request)
    {
        $id=$request->id;
        if(empty($id)):
            $user = new User();
            $helper = new HelperFunctions();
            $user->employeeId=$helper->getLastEmployeeId();
            $user->password = bcrypt($request->password);
        else:
            $user=User::find($id);
        endif;
        $user->email = $request->email;
        $user->firstName = $request->firstName;
        $user->lastName = $request->lastName;
        $user->mobileNumber = $request->mobileNumber;
        $user->address = $request->address;
        $user->company_id=$request->company_id;
        $user->designation_id=$request->designation_id;
        $user->roles=$request->roles;

        // $branchDepartment=BranchDepartment::leftJoin('branches as br','branch_departments.id','=','br.id')->where('br.id','=',$request->branch_id)->get();
        $branchDepartment=BranchDepartment::where('branches_id','=',$request->branch_id)->where('dept_id','=',$request->dept_id)->get();
        if(sizeof($branchDepartment)>0){
            $branchDepartment=$branchDepartment[0];
        }else{
            $branchDepartment=new BranchDepartment();
            $branchDepartment->branches_id=$request->branch_id;
            $branchDepartment->dept_id=$request->dept_id;
            $branchDepartment->save();
        }
        $user->branch_dept_id=$branchDepartment->id;
        $image = $request->file('profilePic');
        if($image instanceof UploadedFile){
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/uploads/profile');
            $image->move($destinationPath, $imageName);
            $user->profilePic = '/uploads/profile/'.$imageName;
        }


        $user->dob = new \Datetime($request->dob);
        $user->doj = new \Datetime($request->doj);
        $user->salary = $request->salary;
        $user->bloodGroup = $request->bloodGroup;
        $user->save();
        return response([
            'status' => 'success',
            'data' => $user
        ], 200);
    }

    /**
     * @SWG\Post(
     *      path="/v1/login",
     *      operationId="login-user",
     *      tags={"Auth"},
     *      summary="Login user",
     *      description="Returns token for the user",
     *      @SWG\Parameter(
     *          name="email",
     *          description="email of the user",
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
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation"
     *       ),
     *       @SWG\Response(response=500, description="Internal server error"),
     *       @SWG\Response(response=400, description="Bad request"),
     *     )
     *
     * Returns list of menus
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if ( ! $token = JWTAuth::attempt($credentials)) {
                return response([
                    'status' => 'error',
                    'error' => 'invalid.credentials',
                    'msg' => 'Invalid Credentials.'
                ], 400);
        }

        $user = User::find(Auth::user()->id);
        if(!$user->isActive){
            JWTAuth::invalidate($token);
            return response()->json(['access_denied'], 404);
        }
        $user = \Auth::user();
        $customClaims=['user_id'=>$user->id, 'fullName'=>$user->firstName." ".$user->lastName,  'profile_pic' => $user->profilePic, 'email'=>$user->email, 'company_id'=> $user->company_id, 'roles'=> $user->roles];
        $token = JWTAuth::fromUser(\Auth::user(), $customClaims);
        return response([
                'status' => 'success',
                'token' => $token
            ]);
    }

    /**
     * @SWG\Get(
     *      path="/v1/current-user",
     *      operationId="current-logged-in-user",
     *      tags={"Auth"},
     *      summary="Get current user details",
     *      description="Returns details of user",
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
     * Returns list of menus
     */
    public function user(Request $request)
    {
        $user = User::find(Auth::user()->id);
        return response([
                'status' => 'success',
                'data' => $user
            ]);
    }


    /**
     * @SWG\Get(
     *      path="/v1/user/{id}",
     *      operationId="single-user-user",
     *      tags={"Users"},
     *      summary="Get single user details",
     *      description="Returns details of user",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="user id",
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
     * Returns logout user
     */
    public function singleUser($id){
        $user=User::leftJoin('mass_parameters','mass_parameters.id','=','users.designation_id')->leftJoin('branch_departments','branch_departments.id','=','users.branch_dept_id')->where('users.id','=',$id)->select('users.id','users.employeeId', 'users.firstName','users.lastName','users.email','users.mobileNumber','users.dob','users.doj','users.address','users.profilePic','users.salary','users.bloodGroup','users.relievingDate','users.designation_id', 'users.roles','users.company_id','mass_parameters.type','mass_parameters.title',"branch_departments.branches_id","branch_departments.dept_id")->get();
        // $user = User::find($id);
        return $user[0];
    }

    /**
     * @SWG\Post(
     *      path="/v1/auth/logout",
     *      operationId="logout-user",
     *      tags={"Auth"},
     *      summary="Logout user",
     *      description="logout user",
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
     * Returns logout user
     */
    public function logout(Request $request)
    {
        $token = $request->bearerToken();
        JWTAuth::invalidate($token);
        return response([
                'status' => 'success',
                'msg' => 'Logged out Successfully.'
            ], 200);
    }

    public function refresh()
    {
        return response([
         'status' => 'success'
        ]);
    }


    /**
    * @SWG\Post(
    *      path="/v1/forgot-password",
    *      operationId="forgot-email",
    *      tags={"Auth"},
    *      summary="forgot user password",
    *      description="forgot password sends forgot link",
    *      @SWG\Parameter(
    *          name="email",
    *          description="email of the user",
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
   public function forgotVerification(UserEmailRequest $request){
        $user=User::where('email','=',$request->email)->first();
        $hash="";
        $flag=true;
        while($flag):
            $hash = bcrypt(uniqid());
            $exi=User::where('reset_token','=',$hash)->first();
            if(!($exi instanceof User)){
                $user->reset_token = $hash;
                $user->save();
                Mail::to($user)->send(new ForgotPassword($request->email, $hash));
                $flag=false;
            }
        endwhile;
        return $user;
   }


   /**
   * @SWG\Post(
   *      path="/v1/forgot-password/reset",
   *      operationId="forgot-email-reset",
   *      tags={"Auth"},
   *      summary="forgot user password reset",
   *      description="forgot password to reset password",
   *      @SWG\Parameter(
   *          name="email",
   *          description="email of the user",
   *          required=true,
   *          type="string",
   *          in="formData"
   *      ),
   *      @SWG\Parameter(
   *          name="token",
   *          description="token from the link of the user",
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
  public function forgotPasswordReset(ForgotPasswordRequest $request){
       $user=User::where('email','=',$request->email)->where('reset_token','=',$request->token)->first();
       $user->password=bcrypt($request->password);
       $user->reset_token=null;
       $user->save();
       return $user;
  }
}