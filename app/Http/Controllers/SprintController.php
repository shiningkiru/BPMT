<?php

namespace App\Http\Controllers;
use Response;
use App\Tasks;
use App\Sprint;
use App\Milestones;
use Illuminate\Http\Request;
use App\Helpers\HelperFunctions;
use App\Http\Requests\SprintFormRequest;

class SprintController extends Controller
{
        /**
     * @SWG\Post(
     *      path="/v1/sprint",
     *      operationId="create-sprint",
     *      tags={"Sprint"},
     *      summary="Sprint creation",
     *      description="Returns created Sprint",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of the sprint at the time of update",
     *          required=false,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="sprintTitle",
     *          description="Sprint Title",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="startDate",
     *          description="start datetime",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="endDate",
     *          description="End Datetime",
     *          required=false,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="estimatedHours",
     *          description="estimatedHours",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *   @SWG\Parameter(
     *          name="status",
     *          description="Sprint Status('created', 'assigned', 'onhold', 'inprogress','completed', 'cancelled',' failed')",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     * @SWG\Parameter(
     *          name="priority",
     *          description="Sprint Priority('critical', 'high', 'medium', 'low')",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *    @SWG\Parameter(
     *          name="milestone_id",
     *          description="Id of the Milestone",
     *          required=true,
     *          type="number",
     *          in="formData"
     *      ),
     *     @SWG\Parameter(
     *          name="dependent_sprint_id",
     *          description="Dependent Sprint ID",
     *          required=false,
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
     * Returns created Sprint
     */
    public function create(SprintFormRequest $request)
    {
        $helper = new HelperFunctions();
        $id=$request->id;
        if(empty($id))
            $sprint=new Sprint();
        else
            $sprint=Sprint::find($id); 
        $oldSprint = clone $sprint;
        $sprint->sprintTitle=$request->sprintTitle;
        $sprint->startDate=new \Datetime($request->startDate);
        $sprint->endDate=new \Datetime($request->endDate);
        $sprint->status=$request->status;
        $sprint->priority=$request->priority;
        $sprint->dependent_sprint_id=$request->dependent_sprint_id;
        $sprint->milestone_id=$request->milestone_id;
        $sprint->estimatedHours=$helper->timeConversion($request->estimatedHours);

        
        //estimated hour calculation
        $milestone=Milestones::find($request->milestone_id);
        $sprintTotal = Sprint::where('milestone_id','=',$milestone->id)->selectRaw('SUM(TIME_TO_SEC(estimatedHours)) as total')->groupBy('sprints.milestone_id')->first();

        $totalSeconds = ($sprintTotal->total ?? 00);
        $estimatedHours=$helper->timeToSec($request->estimatedHours);
        $oldEstimatedHours=$helper->timeToSec($oldSprint->estimatedHours ?? 00);
        $milestoneEstimatedHour = $helper->timeToSec($milestone->estimatedHours);
        $total = (int)$totalSeconds + (int)$estimatedHours - (int)$oldEstimatedHours; 
        
        if($total > $milestoneEstimatedHour){
            return Response::json(['errors'=>['estimatedHours'=>['Estimated limit crossed']]], 422);
        }
        //estimated hour calculation end

        $sprint->save();
        return $sprint;
    }

     /**
     * @SWG\Get(
     *      path="/v1/sprint/{id}",
     *      operationId="single-Sprint",
     *      tags={"Sprint"},
     *      summary="Sprint details",
     *      description="Returns Sprint details",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="Sprint Id",
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
     * Returns Single Sprint
     */
    public function show($id){
        $sprint = Sprint::find($id);
        return $sprint;
    }

       /**
     * @SWG\Get(
     *      path="/v1/sprint/by-milestone/{id}",
     *      operationId="Dependent-Sprint-Details",
     *      tags={"Sprint"},
     *      summary="Dependent Sprint details from milestone",
     *      description="Returns Dependent Sprint details",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="milestone Id",
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
     * Returns list Sprint from milestone
     */

    public function index($id)
    {
        // $sprint = Sprint::where('milestone_id','=',$id)->get();
        // return $sprint;

        $sprint = Sprint::leftjoin('tasks','sprint_id','=','sprints.id')
        ->leftJoin('milestones','milestones.id','=','milestone_id')
        ->leftJoin('projects','projects.id','=','milestones.project_milestone_id')
        ->where('milestone_id','=',$id)
        ->selectRaw('sprints.id,sprints.sprintTitle,sprints.startDate,sprints.endDate,sprints.status,sprints.priority,sprints.estimatedHours,ifnull(count(tasks.id),0) as total_tasks')
        ->groupBy('sprints.id','sprints.sprintTitle','sprints.startDate','sprints.endDate','sprints.status','sprints.priority','sprints.estimatedHours')
        ->get();
        return  $sprint; 
    } 
    
   /**
    * @SWG\Delete(
    *      path="/v1/sprint/{id}",
    *      operationId="delete Sprint",
    *      tags={"Sprint"},
    *      summary="Delete a Sprint",
    *      description="Delete a Sprint",
    *      @SWG\Parameter(
    *          name="Authorization",
    *          description="authorization header",
    *          required=true,
    *          type="string",
    *          in="header"
    *      ),
    *      @SWG\Parameter(
    *          name="id",
    *          description="Sprint Id",
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
    * Deletes a single Sprint
    */
    public function delete($id){
        $sprint = Sprint::find($id);
        $sprint->delete();
        return $sprint;
    }

       /**
     * @SWG\Get(
     *      path="/v1/sprint/total-sprints/{id}",
     *      operationId="total-sprints",
     *      tags={"Sprints"},
     *      summary="Total Number of Sprints created",
     *      description="Returns Total Number of Sprints created",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="Project Id",
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
     * Returns list of Total Number of Sprints created and completed
     */
    public function totalSprints($id){
        $sprints = Sprint::leftjoin('milestones','milestones.id','=','milestone_id')
        ->leftJoin('projects','projects.id','=','milestones.project_milestone_id')
        ->selectRaw('COUNT(sprints.id) as total_sprints, SUM(IF(sprints.status="completed",1,0)) as completed_sprints')
        ->where('projects.id','=',$id)->get();
        return $sprints[0];
    }

    public function getSprintsRelatedToMilestoneByTask(Request $request){
        $task = Tasks::find($request->task_id);
        return $task->sprint->milestone->sprints;
    }
}