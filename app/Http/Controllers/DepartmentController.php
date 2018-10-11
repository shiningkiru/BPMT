<?php

namespace App\Http\Controllers;

use App\MassParameter;
use Illuminate\Http\Request;
use App\Http\Requests\MassParamFormRequest;

class DepartmentController extends Controller
{

    /**
     * @SWG\Post(
     *      path="/v1/department",
     *      operationId="create department",
     *      tags={"Department"},
     *      summary="Create or update department",
     *      description="Create or update department",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="department Id at the time of update",
     *          required=false,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="title",
     *          description="department title",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="company_id",
     *          description="Id of the company",
     *          required=true,
     *          type="number",
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
     * Create or update department
     */
    public function create(MassParamFormRequest $request){
        $id=$request->id;
        if(empty($id)){
            $department=new MassParameter();
            $department->type="department";
        }else
            $department=MassParameter::find($id);
        $department->ms_company_id=$request->company_id;
        $department->title=$request->title;
        $department->save();

        return $department;
    }

    /**
     * @SWG\Get(
     *      path="/v1/department",
     *      operationId="list of department",
     *      tags={"Department"},
     *      summary="Department list",
     *      description="Returns department list",
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
     * Returns list of departments
     */
    public function index(){
        $user = \Auth::user();
        $departments = MassParameter::where('type','=','department')->where('ms_company_id','=',$user->company_id)->get();
        return $departments;
    }

    /**
     * @SWG\Get(
     *      path="/v1/department/{id}/users",
     *      operationId="list of users according to department",
     *      tags={"Department"},
     *      summary="list of users according to department",
     *      description="Returns list of users according to department",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="department Id",
     *          required=true,
     *          type="number",
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
     * Returns list of users according to department
     */
    public function departmentUser($id){
        $users=[];
        $user = \Auth::user();
        $departments = MassParameter::with('branch_departments.users','branch_departments.users.designation')->where('type','=','department')->where('ms_company_id','=',$user->company_id)->where('id','=',$id)->first();
        foreach($departments->branch_departments as $dep){
            $users=array_merge($users,$dep->users->toArray());
        }
        return $users;
    }

    /**
     * @SWG\Get(
     *      path="/v1/department/{id}",
     *      operationId="single department",
     *      tags={"Department"},
     *      summary="department details",
     *      description="Returns company details",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="department Id",
     *          required=true,
     *          type="number",
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
     * Returns single department
     */
    public function show($id){
        $department = MassParameter::where('id','=',$id)->where('type','=','department')->get()[0];
        return $department;
    }

    /**
     * @SWG\Delete(
     *      path="/v1/department/{id}",
     *      operationId="delete department",
     *      tags={"Department"},
     *      summary="Delete a department",
     *      description="Delete a department",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="Department Id",
     *          required=true,
     *          type="number",
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
     * Deletes a single department
     */
    public function delete($id){
        $department = MassParameter::where('id','=',$id)->where('type','=','department')->get()[0];
        $department->delete();
        return $department;
    }
}
