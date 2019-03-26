<?php

namespace App\Http\Controllers;

use App\CustomerMeeting;
use Illuminate\Http\Request;
use App\Http\Requests\CustomerMeetingRequest;
use App\Repositories\CustomerMeetingRepository;
use App\Http\Controllers\Master\MasterController;

class CustomerMeetingController extends MasterController
{
    public function __construct()
    {
         parent::__construct(new CustomerMeetingRepository());
    }
    
    public function addMeeting(CustomerMeetingRequest $request){
        $id=$request->id;
        $user = \Auth::user();
        if(empty($id)){
            $meeting=new CustomerMeeting();
        } else{
            $meeting=CustomerMeeting::find($id);
        }
        $meeting->customer_id=$request->customer_id;        
        $meeting->resp_user=$user->id;        
        $meeting->dateFor=new \Datetime($request->dateFor);        
        $meeting->status=$request->status;        
        $meeting->details=$request->details;

        $meeting->save();
        $customer=Customer::find($request->customer_id);
        $customer->updated_at=new \Datetime();
        $customer->save();
        return $meeting;
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
        $meeting = CustomerMeeting::leftJoin('customers','customers.id', '=', 'customer_meetings.customer_id')
                                ->leftJoin('users as responsible', 'users.id', '=', 'customer_meetings.resp_user')
                                ->select('customer_meetings.id', 'customer_meetings.dateFor', 'customer_meetings.details', 'customer_meetings.resp_user', 'responsible.firstName as rFirstName', 'responsible.lastName as rLastName', 'customer_meetings.customer_id', 'customer_meetings.created_at', 'customer_meetings.updated_at', 'customer_meetings.status', 'customers.company');
        if(!empty($request->customer_id))
            $meeting = $meeting->where('customer_id','=',$request->customer_id);

        if(!empty($request->searchText)){
            $meeting = $meeting->where('customer_meetings.details', 'LIKE', '%'.$request->searchText.'%');
        }

        if(!empty($request->startDate) && empty($request->endDate)){
            $startDate = new \Datetime($request->startDate);
            $meeting = $meeting->where('dateFor', '=', $startDate->format('Y-m-d'));
        }else if(!empty($request->startDate) && !empty($request->endDate)){
            $startDate = new \Datetime($request->startDate);
            $endDate = new \Datetime($request->endDate);
            $endDate->modify('+1 day');
            $meeting = $meeting->whereBetween('dateFor', [$startDate, $endDate]);
        }

        if(!empty($request->searchStatus)){
            $meeting = $meeting->where('customer_meetings.status', '=', $request->searchStatus);
        }

        if(!empty($request->customerName)){
            $meeting = $meeting->where('customers.company', '=', $request->customerName);
        }

        $meeting = $meeting->orderBy('customer_meetings.dateFor','DESC')->paginate($pageSize);
        return $meeting;
    }
}
