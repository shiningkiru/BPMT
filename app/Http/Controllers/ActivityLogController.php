<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use App\Repositories\ActivityLogRepository;
use App\Http\Controllers\Master\MasterController;

class ActivityLogController extends MasterController
{
    
   public function __construct()
   {
        parent::__construct(new ActivityLogRepository());
   }

    public function getLogs(Request $request){
        $valid = $this->model->validateRules($request->all(), [
            'fromDate' => 'required|date',
            'endDate' => 'required|date',
            'user_id' => 'exists:users,id',
            'pageNumber' => 'required|numeric',
        ]);
        if($valid->fails()) return response()->json(['errors'=>$valid->errors()], 422);
        $currentPage = $request->pageNumber;
        Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });
        $user_id = $request->user_id ?? null;
        $endDate =new \Datetime($request->endDate);
        $endDate = $endDate->modify('+1 Day');
        return $this->model->getLogs(new \Datetime($request->fromDate), $endDate, $user_id)->select('activity_logs.*','users.firstName','users.lastName')->paginate(20);
    }
}
