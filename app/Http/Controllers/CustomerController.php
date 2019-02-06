<?php

namespace App\Http\Controllers;

use App\Customer;
use Illuminate\Http\Request;
use App\Helpers\HelperFunctions;
use Illuminate\Http\UploadedFile;
use App\Http\Requests\CustomerFormRequest;

class CustomerController extends Controller
{


    /**
     * @SWG\Post(
     *      path="/v1/customer",
     *      operationId="create customer",
     *      tags={"Customer"},
     *      summary="Create or update customer",
     *      description="Create or update customer",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="Customer Id at the time of update",
     *          required=false,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="company",
     *          description="Customer Company name Name",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="streetNo",
     *          description="Street number",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="street",
     *          description="Street name",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="postCode",
     *          description="postal code",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="city",
     *          description="City name",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="country",
     *          description="Cuntru Name",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="officeTel",
     *          description="Office telephone number",
     *          required=false,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="branch",
     *          description="Branch",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="homepage",
     *          description="Home page url",
     *          required=false,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="email",
     *          description="Customer Email",
     *          required=false,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="details",
     *          description="Customer Details",
     *          required=false,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="status",
     *          description="Customer active status (active/inactive)",
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
     * Create or update customer
     */
    public function create(CustomerFormRequest $request){
        $helper = new HelperFunctions();
        $id=$request->id;
        if(empty($id)){
            $customer=new Customer();
            $customer->customerNumber=$helper->getCustomerNumber();
            $customer->status=$request->status;
            $customer->customer_company_id=$request->company_id;
            $customer->responsible_user_id=\Auth::user()->id;
        } else{
            $customer=Customer::find($id);
        }
        $customer->company=$request->company;
        $customer->streetNo=$request->streetNo;
        $customer->street=$request->street;
        $customer->postCode=$request->postCode;
        $customer->city=$request->city;
        $customer->country=$request->country;
        $customer->officeTel=$request->officeTel;
        $customer->branch=$request->branch;
        $customer->homepage=$request->homepage;
        $customer->email=$request->email;
        $customer->details=$request->details;

        $customer->save();
        return $customer;
    }

    /**
     * @SWG\Get(
     *      path="/v1/customer",
     *      operationId="list customer",
     *      tags={"Customer"},
     *      summary="Customer list",
     *      description="Returns customer list",
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
        $user = \Auth::user();
        $companies = Customer::with('responsible_person')->where('customer_company_id','=',$user->company_id)->get();
        return $companies;
    }

    /**
     * @SWG\Post(
     *      path="/v1/customer/change-status",
     *      operationId="change customer status",
     *      tags={"Customer"},
     *      summary="change customer status",
     *      description="change customer status",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="customer_id",
     *          description="Customer Id",
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
     * Returns change customer status
     */
    public function changeStatus(Request $request){
        $user = \Auth::user();
        $company = Customer::find($request->customer_id);
        $company->status = ($company->status == 'active')?"inactive":"active";
        $company->save();
        return $company;
    }

    /**
     * @SWG\Post(
     *      path="/v1/customer/change-responsible-person",
     *      operationId="change customer status",
     *      tags={"Customer"},
     *      summary="change customer responsible-person",
     *      description="change customer responsible-person",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="Customer Id",
     *          required=true,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="responsible_user_id",
     *          description="Responsible user Id",
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
     * Returns change customer responsible-person
     */
    public function changeResponsiblePersons(Request $request){
        $user = \Auth::user();
        $company = Customer::find($request->id);
        $company->responsible_user_id = $request->responsible_user_id;
        $company->save();
        return $company;
    }

    /**
     * @SWG\Get(
     *      path="/v1/customer/{id}",
     *      operationId="single customer",
     *      tags={"Customer"},
     *      summary="Customer details",
     *      description="Returns customer details",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="Customer Id",
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
        $customer = Customer::with('responsible_person')->find($id);
        return $customer;
    }

    /**
     * @SWG\Delete(
     *      path="/v1/customer/{id}",
     *      operationId="delete customer",
     *      tags={"Customer"},
     *      summary="Delete a customer",
     *      description="Delete a customer",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="Customer Id",
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
     * Deletes a single customer
     */
    public function delete($id){
        $customer = Customer::find($id);
        $customer->delete();
        return $customer;
    }
}
