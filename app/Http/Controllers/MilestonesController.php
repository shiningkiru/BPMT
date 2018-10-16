<?php

namespace App\Http\Controllers;
use App\Milestones;
use Illuminate\Http\Request;
use App\Http\Requests\MilestonesFormRequest;
use App\Http\Controllers\DB;

class MilestonesController extends Controller
{
    /**
     * @SWG\Post(
     *      path="/v1/milestone",
     *      operationId="create-milestone",
     *      tags={"Milestones"},
     *      summary="milestone creation",
     *      description="Returns created milestone",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of the milestone at the time of update",
     *          required=false,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="title",
     *          description="title for milestone",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="description",
     *          description="description of the milestone",
     *          required=false,
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
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="estimatedHours",
     *          description="Estimated Hours",
     *          required=true,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="status",
     *          description="Milestone Status('created', 'assigned', 'onhold', 'inprogress','completed', 'cancelled',' failed')",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="project_id",
     *          description="Id of the project",
     *          required=true,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="dependent_milestone_id",
     *          description="Id of the Milestone",
     *          required=true,
     *          type="number",
     *          in="formData"
     *      ),
     * 
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation"
     *       ),
     *       @SWG\Response(response=500, description="Internal server error"),
     *       @SWG\Response(response=400, description="Bad request"),
     *     )
     *
     * Returns created  Milestones
     */
    public function create(MilestonesFormRequest $request)
    {
        $id=$request->id;
        if(empty($id))
            $milestone=new Milestones();
        else
            $milestone=Milestones::find($id);
        $milestone->title=$request->title;
        $milestone->description=$request->description;
        $milestone->startDate=new \Datetime($request->startDate);
        $milestone->endDate=new \Datetime($request->endDate);
        $milestone->estimatedHours=$request->estimatedHours;
        $milestone->status=$request->status;
        $milestone->project_milestone_id=$request->project_id;
        $milestone->dependent_milestone_id=$request->dependent_milestone_id;
        $milestone->save();
        return $milestone;
    }

     /**
     * @SWG\Get(
     *      path="/v1/milestone/{id}",
     *      operationId="single Milestone Details",
     *      tags={"Milestones"},
     *      summary="single Milestones details",
     *      description="Returns single Milestone details",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="Milestone Id",
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
     * Returns list Milestones
     */
    public function show($id){
        $milestone = Milestones::find($id);
        return $milestone; 
    }

   /**
     * @SWG\Get(
     *      path="/v1/milestone/by-project/{id}",
     *      operationId="All-Milestone-from-projects",
     *      tags={"Milestones"},
     *      summary="Milestone from projects",
     *      description="Returns Milestone from projects",
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
     * Returns list of Milestone from projects
     */
    public function index($id){
        $milestone = Milestones::with('project')->where('project_milestone_id','=',$id)->get();
        return $milestone;
    }

 /**
    * @SWG\Delete(
    *      path="/v1/milestone/{id}",
    *      operationId="delete Milestone",
    *      tags={"Milestones"},
    *      summary="Delete a Milestone",
    *      description="Delete a Milestone",
    *      @SWG\Parameter(
    *          name="Authorization",
    *          description="authorization header",
    *          required=true,
    *          type="string",
    *          in="header"
    *      ),
    *      @SWG\Parameter(
    *          name="id",
    *          description="Milestone Id",
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
    * Deletes a single Milestone
    */
    public function delete($id){
        $milestone = Milestones::find($id);
        $milestone->delete();
        return $milestone;
    }

    /**
     * @SWG\Get(
     *      path="/v1/milestone/total-milestones/{id}",
     *      operationId="total-milestones",
     *      tags={"Milestones"},
     *      summary="Total Number of Milestones created",
     *      description="Returns Total Number of Milestones created",
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
     * Returns list of Total Number of Milestones created and completed
     */
    public function totalMilestones($id){
        $milestone = Milestones::where('project_milestone_id','=',$id)->get([
        \DB::raw("COUNT(id) as total_milestones"),
        \DB::raw("SUM(IF(status='completed',1,0)) as completed_milestones")
        ]);
        return $milestone[0];
    }
}