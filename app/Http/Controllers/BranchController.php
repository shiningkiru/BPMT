<?php

namespace App\Http\Controllers;

use App\Branch;
use Illuminate\Http\Request;
use App\Http\Requests\BranchFormRequest;

class BranchController extends Controller
{


    /**
     * @SWG\Post(
     *      path="/v1/branch",
     *      operationId="create Branch",
     *      tags={"Branch"},
     *      summary="Create or update Branch",
     *      description="Create or update Branch",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="Branch Id at the time of update",
     *          required=false,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="branchName",
     *          description="branchName",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="branchCode",
     *          description="branchCode",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="address",
     *          description="address",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="longitude",
     *          description="longitude",
     *          required=false,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="latitude",
     *          description="latitude",
     *          required=false,
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
     * Create or update Branch
     */
    public function create(BranchFormRequest $request){
        $id=$request->id;
        if(empty($id)){
            $branch=new Branch();
        }else
            $branch=Branch::find($id);
        $branch->br_company_id=$request->company_id;
        $branch->branchName=$request->branchName;
        $branch->branchCode=$request->branchCode;
        $branch->address=$request->address;
        $branch->longitude=$request->longitude;
        $branch->latitude=$request->latitude;
        $branch->save();

        return $branch;
    }

    /**
     * @SWG\Get(
     *      path="/v1/branch",
     *      operationId="list of branch",
     *      tags={"Branch"},
     *      summary="Branch list",
     *      description="Returns branch list",
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
     * Returns list of branchs
     */
    public function index(){
        $user = \Auth::user();
        $branchs = Branch::where('br_company_id','=',$user->company_id)->get();
        return $branchs;
    }

    /**
     * @SWG\Get(
     *      path="/v1/branch/{id}",
     *      operationId="single branch",
     *      tags={"Branch"},
     *      summary="branch details",
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
     *          description="branch Id",
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
     * Returns single branch
     */
    public function show($id){
        $branch = Branch::where('id','=',$id)->get()[0];
        return $branch;
    }

    /**
     * @SWG\Delete(
     *      path="/v1/branch/{id}",
     *      operationId="delete branch",
     *      tags={"Branch"},
     *      summary="Delete a branch",
     *      description="Delete a branch",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="Branch Id",
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
     * Deletes a single branch
     */
    public function delete($id){
        $branch = Branch::where('id','=',$id)->get()[0];
        $branch->delete();
        return $branch;
    }
}
