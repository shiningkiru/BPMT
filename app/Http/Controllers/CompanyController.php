<?php

namespace App\Http\Controllers;

use App\Company;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Http\Requests\CompanyFormRequest;

class CompanyController extends Controller
{

    /**
     * @SWG\Post(
     *      path="/v1/company",
     *      operationId="create company",
     *      tags={"Company"},
     *      summary="Company creation",
     *      description="Returnscompany details",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of the company at the time of update",
     *          required=false,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="name",
     *          description="name of the company",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="email",
     *          description="email of the company",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="mobileNumber",
     *          description="mobileNumber of the company",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="address",
     *          description="address of the company",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="logo",
     *          description="logo of the company",
     *          required=false,
     *          type="file",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="longitude",
     *          description="longitude of the address",
     *          required=false,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="latitude",
     *          description="latitude of the address",
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
     * Returns list of menus
     */
    public function create(CompanyFormRequest $request){
        $id=$request->id;
        if(empty($id))
            $company=new Company();
        else
            $company=Company::find($id);
        $company->name=$request->name;
        $company->email=$request->email;
        $company->mobileNumber=$request->mobileNumber;
        $company->address=$request->address;


        $image = $request->file('logo');
        if($image instanceof UploadedFile){
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/uploads/company');
            $image->move($destinationPath, $imageName);
            $company->logo = '/uploads/company/'.$imageName;
        }

        $company->longitude=$request->longitude;
        $company->latitude=$request->latitude;
        $company->save();
        return $company;
    }

    /**
     * @SWG\Get(
     *      path="/v1/company",
     *      operationId="list company",
     *      tags={"Company"},
     *      summary="Company list",
     *      description="Returns company list",
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
     * Returns list of companies
     */
    public function index(){
        $companies = Company::all();
        return $companies;
    }

    /**
     * @SWG\Get(
     *      path="/v1/company/{id}",
     *      operationId="single company",
     *      tags={"Company"},
     *      summary="Company details",
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
     *          description="Company Id",
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
     * Returns list of companies
     */
    public function show($id){
        $company = Company::find($id);
        return $company;
    }

    /**
     * @SWG\Delete(
     *      path="/v1/company/{id}",
     *      operationId="delete company",
     *      tags={"Company"},
     *      summary="Delete a company",
     *      description="Delete a company",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="Company Id",
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
     * Deletes a single company
     */
    public function delete($id){
        $company = Company::find($id);
        $company->delete();
        return $company;
    }

}