<?php

namespace App\Http\Controllers;

use App\Todo;
use Illuminate\Http\Request;
use App\Helpers\HelperFunctions;

class CalendarController extends Controller
{
    public function getCalendarEntries(Request $request){
        $helper = new HelperFunctions();
        $user = \Auth::user();
        $user_id=$request->user_id;
        if(empty($user_id)){
            $user_id=$user->id;
        }
        $dateGap = $helper->getMonthStartEndDate($request->dateFor);
        $dateEntry = new \Datetime($request->dateFor);

        //todos for calendar
        $todos = Todo::where('to_do_resp_user','=',$user_id)
                        ->where('status','=','open')
                        ->where(function($query) use ($dateEntry){
                            $query->where(function($quer) use ($dateEntry){
                                $quer->whereYear('dateFor', $dateEntry->format('Y'))
                                    ->whereMonth('dateFor', $dateEntry->format('m'));
                            })
                            ->orWhere(function($quer) use ($dateEntry) {
                                $quer->whereYear('endDate', $dateEntry->format('Y'))
                                    ->whereMonth('endDate', $dateEntry->format('m'));
                            });
                        })
                        ->get();
        $result['todo']=$todos;
        return $result;
    }
}
