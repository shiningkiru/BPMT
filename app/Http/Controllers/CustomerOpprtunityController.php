<?php

namespace App\Http\Controllers;

use App\Customer;
use App\CustomerOpportunity;
use Illuminate\Http\Request;
use App\Helpers\HelperFunctions;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\Paginator;
use App\Http\Controllers\Master\MasterController;
use App\Http\Requests\CustomerOpportunityRequest;
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
     *          description="status open/close",
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
        $opprtunity->closeComment=$request->closeComment;
        $opprtunity->wonComment=$request->wonComment;
        $opprtunity->lostComment=$request->lostComment;

        $opprtunity->save();
        $customer=Customer::find($request->customer_id);
        $customer->updated_at=new \Datetime();
        $customer->save();
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
        $opprtunities = CustomerOpportunity::leftJoin('customers','customers.id', '=', 'customer_opportunities.customer_op_id')->select('customer_opportunities.id', 'customer_opportunities.dateFor', 'customer_opportunities.details', 'customer_opportunities.closeComment', 'customer_opportunities.wonComment', 'customer_opportunities.lostComment', 'customer_opportunities.customer_contact_person', 'customer_opportunities.customer_op_id', 'customer_opportunities.created_at', 'customer_opportunities.updated_at', 'customer_opportunities.status', 'customers.company');
        if(!empty($request->customer_id))
            $opprtunities = $opprtunities->where('customer_op_id','=',$request->customer_id);

        if(!empty($request->searchText)){
            $opprtunities = $opprtunities->where('customer_opportunities.details', 'LIKE', '%'.$request->searchText.'%');
        }

        if(!empty($request->startDate) && empty($request->endDate)){
            $startDate = new \Datetime($request->startDate);
            $opprtunities = $opprtunities->where('dateFor', '=', $startDate->format('Y-m-d'));
        }else if(!empty($request->startDate) && !empty($request->endDate)){
            $startDate = new \Datetime($request->startDate);
            $endDate = new \Datetime($request->endDate);
            $endDate->modify('+1 day');
            $opprtunities = $opprtunities->whereBetween('dateFor', [$startDate, $endDate]);
        }

        if(!empty($request->searchStatus)){
            $opprtunities = $opprtunities->where('customer_opportunities.status', '=', $request->searchStatus);
        }

        if(!empty($request->customerName)){
            $opprtunities = $opprtunities->where('customers.company', '=', $request->customerName);
        }

        $opprtunities = $opprtunities->orderBy('customer_opportunities.dateFor','DESC')->paginate($pageSize);
        return $opprtunities;
    }

    /**
     * @SWG\Post(
     *      path="/v1/opportunity/by-contact",
     *      operationId="list opportunitys by contact",
     *      tags={"Opprtunities"},
     *      summary="Todo list by contact",
     *      description="Returns opportunity list by contact",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="contact_id",
     *          description="related contact Id",
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
     * Returns list of opportunitys
     */
    public function getByContact(Request $request){
        $user = \Auth::user();
        
        $opprtunities = CustomerOpportunity::where('customer_contact_person','=',$request->contact_id);
        return $opprtunities->orderBy('id','DESC')->get();
    }
}
