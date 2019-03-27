<?php

namespace App\Http\Controllers;

use Response;
use App\Customer;
use App\CustomerCalls;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use App\Http\Requests\CustomerCallRequest;
use App\Repositories\CustomerCallRepository;
use App\Http\Controllers\Master\MasterController;

class CustomerCallController extends MasterController
{
    public function __construct()
    {
         parent::__construct(new CustomerCallRepository());
    }
    
    public function addCall(CustomerCallRequest $request){
        try {
            $id=$request->id;
            $user = \Auth::user();
            if(empty($id)){
                $call=new CustomerCalls();
            } else{
                $call=CustomerCalls::find($id);
            }
            $call->customer_id=$request->customer_id;        
            $call->resp_user=$user->id;        
            $call->dateFor=new \Datetime($request->dateFor);        
            $call->status=$request->status;        
            $call->details=$request->details;

            $call->save();
            $customer=Customer::find($request->customer_id);
            $customer->updated_at=new \Datetime();
            $customer->save();
            return $call;
        }catch(\Exception $e){
            return Response::json(['errors'=>['call'=>[$e->getMessage()]]], 422);
        }
    }

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
        $call = CustomerCalls::leftJoin('customers','customers.id', '=', 'customer_calls.customer_id')
                                ->leftJoin('users as responsible', 'responsible.id', '=', 'customer_calls.resp_user')
                                ->select('customer_calls.id', 'customer_calls.dateFor', 'customer_calls.details', 'customer_calls.resp_user', 'responsible.firstName as rFirstName', 'responsible.lastName as rLastName', 'customer_calls.customer_id', 'customer_calls.created_at', 'customer_calls.updated_at', 'customer_calls.status', 'customers.company');
        if(!empty($request->customer_id))
            $call = $call->where('customer_id','=',$request->customer_id);

        if(!empty($request->searchText)){
            $call = $call->where('customer_calls.details', 'LIKE', '%'.$request->searchText.'%');
        }

        if(!empty($request->startDate) && empty($request->endDate)){
            $startDate = new \Datetime($request->startDate);
            $call = $call->where('dateFor', '=', $startDate->format('Y-m-d'));
        }else if(!empty($request->startDate) && !empty($request->endDate)){
            $startDate = new \Datetime($request->startDate);
            $endDate = new \Datetime($request->endDate);
            $endDate->modify('+1 day');
            $call = $call->whereBetween('dateFor', [$startDate, $endDate]);
        }

        if(!empty($request->searchStatus)){
            $call = $call->where('customer_calls.status', '=', $request->searchStatus);
        }

        if(!empty($request->customerName)){
            $call = $call->where('customers.company', '=', $request->customerName);
        }

        $call = $call->orderBy('customer_calls.dateFor','DESC')->paginate($pageSize);
        return $call;
    }
}
