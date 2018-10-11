<?php

namespace App\Http\Controllers;

use App\MassParameter;
use Illuminate\Http\Request;
use App\Http\Requests\MassParamFormRequest;

class DesignationController extends Controller
{

    /**
     * @SWG\Post(
     *      path="/v1/designation",
     *      operationId="create designation",
     *      tags={"Designation"},
     *      summary="Create or update designation",
     *      description="Create or update designation",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="designation Id at the time of update",
     *          required=false,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="title",
     *          description="designation title",
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
     * Create or update designation
     */
    public function create(MassParamFormRequest $request){
        $id=$request->id;
        if(empty($id)){
            $designation=new MassParameter();
            $designation->type="designation";
        }else
            $designation=MassParameter::find($id);
        $designation->ms_company_id=$request->company_id;
        $designation->title=$request->title;
        $designation->save();

        return $designation;
    }

    /**
     * @SWG\Get(
     *      path="/v1/designation",
     *      operationId="list of designation",
     *      tags={"Designation"},
     *      summary="Designation list",
     *      description="Returns designation list",
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
     * Returns list of designations
     */
    public function index(){
        $user = \Auth::user();
        $designations = MassParameter::where('type','=','designation')->where('ms_company_id','=',$user->company_id)->get();
        return $designations;
    }

    /**
     * @SWG\Get(
     *      path="/v1/designation/{id}",
     *      operationId="single designation",
     *      tags={"Designation"},
     *      summary="designation details",
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
     *          description="designation Id",
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
     * Returns single designation
     */
    public function show($id){
        $designation = MassParameter::where('id','=',$id)->where('type','=','designation')->first();
        return $designation;
    }

    /**
     * @SWG\Delete(
     *      path="/v1/designation/{id}",
     *      operationId="delete designation",
     *      tags={"Designation"},
     *      summary="Delete a designation",
     *      description="Delete a designation",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="Designation Id",
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
     * Deletes a single designation
     */
    public function delete($id){
        $designation = MassParameter::where('id','=',$id)->where('type','=','designation')->get()[0];
        $designation->delete();
        return $designation;
    }
}
