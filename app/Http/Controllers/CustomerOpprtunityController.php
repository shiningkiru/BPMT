<?php

namespace App\Http\Controllers;

use App\CustomerOpportunity;
use Illuminate\Http\Request;
use App\Helpers\HelperFunctions;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\Paginator;
use App\Http\Requests\CustomerOpportunityRequest;
use App\Http\Controllers\Master\MasterController;
use App\Repositories\CustomerOpportunityRepository;

class CustomerOpprtunityController extends MasterController
{
    public function __construct()
    {
         parent::__construct(new CustomerOpportunityRepository());
    }

    /**
     * @SWG\Post(
     *      path="/v1/opportunity",
     *      operationId="create opportunity",
     *      tags={"Opprtunities"},
     *      summary="Create or update opportunity",
     *      description="Create or update opportunity",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="opportunity Id at the time of update",
     *          required=false,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="dateFor",
     *          description="date of opportunity",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="status",
     *          description="status active/inactive",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="details",
     *          description="Details in opportunity",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="customer_id",
     *          description="Customer Id",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="contacts_id",
     *          description="Contacts Id",
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
     * Create or update opprtunity
     */
    public function addOpportunity(CustomerOpportunityRequest $request){
        $id=$request->id;
        if(empty($id)){
            $opprtunity=new CustomerOpportunity();
        } else{
            $opprtunity=CustomerOpportunity::find($id);
        }
        $opprtunity->customer_op_id=$request->customer_id;        
        $opprtunity->customer_contact_person=$request->contacts_id;        
        $opprtunity->dateFor=new \Datetime($request->dateFor);        
        $opprtunity->status=$request->status;        
        $opprtunity->details=$request->details;

        $opprtunity->save();
        return $opprtunity;
    }

    /**
     * @SWG\Post(
     *      path="/v1/opportunity/by-related",
     *      operationId="list opportunitys by related",
     *      tags={"Opprtunities"},
     *      summary="Todo list by related",
     *      description="Returns opportunity list by related",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="customer_id",
     *          description="related customer Id",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="pageNumber",
     *          description="pageNumber",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="pageSize",
     *          description="page size default is 20",
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
     * Returns list of opportunitys
     */
    public function getByRelated(Request $request){
        $user = \Auth::user();
        
        $currentPage = $request->pageNumber;
        Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });
        $pageSize=$request->pageSize;
        if(empty($pageSize)){
            $pageSize=20;
        }
        $opprtunities = CustomerOpportunity::where('customer_op_id','=',$request->customer_id)->orderBy('id','DESC')->paginate($pageSize);
        return $opprtunities;
    }
}