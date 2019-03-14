<?php

namespace App\Http\Controllers;

use App\Todo;
use Illuminate\Http\Request;
use App\Helpers\HelperFunctions;
use Illuminate\Http\UploadedFile;
use App\Http\Requests\ToDoRequest;
use App\Repositories\TodoRepository;
use Illuminate\Pagination\Paginator;
use App\Http\Controllers\Master\MasterController;

class TodoController extends MasterController
{
    public function __construct()
    {
         parent::__construct(new TodoRepository());
    }

    /**
     * @SWG\Post(
     *      path="/v1/todo",
     *      operationId="create todo",
     *      tags={"ToDo"},
     *      summary="Create or update todo",
     *      description="Create or update todo",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="Todo Id at the time of update",
     *          required=false,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="to_do_resp_user",
     *          description="User id who is responsible for todo",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="dateFor",
     *          description="date of todo",
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
     *          description="Details in todo",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="linkeId",
     *          description="Id of respective relations",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="relatedTo",
     *          description="specify to which it belongs to customer/project/general",
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
     * Create or update todo
     */
    public function addTodo(ToDoRequest $request){
        $id=$request->id;
        if(empty($id)){
            $todo=new Todo();
        } else{
            $todo=Todo::find($id);
        }
        $todo->to_do_resp_user=$request->to_do_resp_user;        
        $todo->dateFor=new \Datetime($request->dateFor);        
        $todo->status=$request->status;        
        $todo->linkId=$request->linkId;
        $todo->relatedTo=$request->relatedTo;
        $todo->details=$request->details;

        $todo->save();
        return $todo;
    }

    /**
     * @SWG\Post(
     *      path="/v1/todo/by-related",
     *      operationId="list todos by related",
     *      tags={"Todo"},
     *      summary="Todo list by related",
     *      description="Returns todo list by related",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="relatedTo",
     *          description="related to customer/project/general",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="linkId",
     *          description="related Link Id",
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
     * Returns list of todos
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
        $customerTodo = Todo::select('todos.id', 'todos.details', 'todos.endDate', 'todos.status', 'todos.fullDay', 'todos.to_do_resp_user', 'todos.linkId', 'todos.relatedTo', 'todos.created_at', 'todos.updated_at', \DB::raw('DATE_FORMAT(todos.dateFor, "%Y-%m-%d") as dateFor'));
        if($request->relatedTo == 'customer')
            $customerTodo = $customerTodo->leftJoin('customers','customers.id','=','todos.linkId')
                                        ->addSelect('customers.company');
                        
        if(!empty($request->linkId))
            $customerTodo = $customerTodo->where('linkId','=',$request->linkId);

        if(!empty($request->searchText)){
            $customerTodo = $customerTodo->where('todos.details', 'LIKE', '%'.$request->searchText.'%');
        }

        if(!empty($request->startDate) && empty($request->endDate)){
            $startDate = new \Datetime($request->startDate);
            $customerTodo = $customerTodo->where('dateFor', '=', $startDate->format('Y-m-d'));
        }else if(!empty($request->startDate) && !empty($request->endDate)){
            $startDate = new \Datetime($request->startDate);
            $endDate = new \Datetime($request->endDate);
            $endDate->modify('+1 day');
            $customerTodo = $customerTodo->whereBetween('dateFor', [$startDate, $endDate]);
        }

        if(!empty($request->searchStatus)){
            $status = $request->searchStatus;
            if($status == 'overdue'){
                $status = 'open';
                $today = new \Datetime();
                $customerTodo = $customerTodo->where('dateFor', '<' , $today->format('Y-m-d'));
            }
            $customerTodo = $customerTodo->where('todos.status', '=', $status);
        }

        if(!empty($request->customerName)){
            $customerTodo = $customerTodo->where('customers.company', '=', $request->customerName);
        }
        $customerTodo = $customerTodo->where('relatedTo','=',$request->relatedTo)
                        ->orderBy('todos.dateFor','DESC')
                        ->paginate($pageSize);
        return $customerTodo;
    }

    /**
     * @SWG\Post(
     *      path="/v1/todo/by-month",
     *      operationId="list todos by month and user ",
     *      tags={"Todo"},
     *      summary="Todo list by month and user ",
     *      description="Returns todo list by month and user ",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="user_id",
     *          description="user_id",
     *          required=false,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="dateFor",
     *          description="dateFor to get the exact month and year",
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
     * Returns list of todos
     */
    public function getByUserAndDate(Request $request){
        $helper = new HelperFunctions();
        $user = \Auth::user();
        $user_id=$request->user_id;
        if(empty($user_id)){
            $user_id=$user->id;
        }
        $dateGap = $helper->getMonthStartEndDate($request->dateFor);
        $dateEntry = new \Datetime($request->dateFor);
        $todos = Todo::where('to_do_resp_user','=',$user_id)
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
        return $todos;
    }
}
