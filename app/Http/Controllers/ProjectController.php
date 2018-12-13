<?php

namespace App\Http\Controllers;

use Cfun;
use Response;
use App\Project;
use App\Milestones;
use Illuminate\Http\Request;
use App\Helpers\HelperFunctions;
use App\Repositories\ProjectRepository;
use App\Http\Requests\ProjectFormRequest;

class ProjectController extends Controller
{
    /**
     * @SWG\Post(
     *      path="/v1/project",
     *      operationId="create project",
     *      tags={"Project"},
     *      summary="project creation",
     *      description="Returns project details",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of the project at the time of update",
     *          required=false,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="projectName",
     *          description="name of the project",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="description",
     *          description="description of the project",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="projectCode",
     *          description="project code",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="projectCategory",
     *          description="project category internal/external",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="startDate",
     *          description="start date",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="endDate",
     *          description="End Date",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="budget",
     *          description="Project Budget",
     *          required=false,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="status",
     *          description="Status of the project(received/pending/started/in-progress/in-hold/completed/cancelled)",
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
     *      @SWG\Parameter(
     *          name="client_project_id",
     *          description="client ID",
     *          required=true,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="project_lead_id",
     *          description="User ID",
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
     * Returns created  Projects
     */
    public function create(ProjectFormRequest $request)
    {
        $helper = new HelperFunctions();
        try{
            \DB::transaction(function() use ($helper, $request){
                $id=$request->id;
                $processType="edit";
                if(empty($id)):
                    $project=new Project();
                else:
                    $project=Project::find($id);
                endif;
                
                $projectType = $request->projectType;
                if($projectType == 'support'):
                    $processType="new";
                endif;
                $project->projectName=$request->projectName;
                $project->description=$request->description;
                $startdate=new \Datetime($request->startDate);
                $project->startDate=$startdate->format('Y/m/d');
                $enddate=new \Datetime($request->endDate);
                $project->endDate=$enddate->format('Y/m/d');
                $project->budget=$request->budget;
                $project->estimatedHours=$helper->timeConversion($request->estimatedHours);
                $project->status=$request->status;
                if($project->projectCategory!=$request->projectCategory)
                    $project->projectCode=$helper->getInternalProjectId($request->projectCategory);
                $project->projectCategory=$request->projectCategory;
                $project->client_project_id=$request->client_project_id;
                $project->project_lead_id=$request->project_lead_id;
                $project->project_company_id=$request->company_id;
                $project->save();
                $helper->updateProjectTeam($request->project_lead_id, $project->id, 'active');

                if($processType == 'new'):dd("hjdhs");
                    $milestone = $project->milestones->first();
                    if(!($milestone instanceof Milestones))
                        $milestone = new Milestones();
                    $milestone->title=$project->projectName;
                    $milestone->startDate=$project->startDate;
                    $milestone->endDate=$project->endDate;
                    $milestone->estimatedHours=$helper->timeConversion($project->estimatedHours);
                    $milestone->status=($project->status == 'completed')?'complted':'inprogress';
                    $milestone->project_milestone_id=$project->id;
                    $milestone->save();
                endif;

                return $project;
            });
            
        }catch(\Exception $e){
            return Response::json(['errors'=>['server'=>[$e]]], 400);
        }
    }


  /**
     * @SWG\Get(
     *      path="/v1/project",
     *      operationId="project list",
     *      tags={"Project"},
     *      summary="Project list",
     *      description="Returns Project list",
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
     * Returns list of projects
     */
    public function index(){
        $user = \Auth::user();
        $projects=Project::leftJoin('clients','clients.id','=','client_project_id')->select('projects.id','projects.projectName', 'projects.description','projects.projectCode','projects.startDate','projects.endDate','projects.budget','projects.status','projects.client_project_id','clients.email','clients.name as clientName')->orderBy('projects.startDate','ASC')->where('projects.project_company_id','=',$user->company_id)->paginate(500);
        return $projects;
    }


    /**
       * @SWG\Get(
       *      path="/v1/project/by-client/{id}",
       *      operationId="project list by client",
       *      tags={"Project"},
       *      summary="Project list by client",
       *      description="Returns Project list by client",
       *      @SWG\Parameter(
       *          name="Authorization",
       *          description="authorization header",
       *          required=true,
       *          type="string",
       *          in="header"
       *      ),
       *      @SWG\Parameter(
       *          name="id",
       *          description="Client Id",
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
       * Returns list of projects by client
       */
      public function byClient(Request $request,$id){
          $user = \Auth::user();
          $projects=Project::leftJoin('clients','clients.id','=','client_project_id')->select('projects.id','projects.projectName', 'projects.description','projects.projectCode','projects.startDate','projects.endDate','projects.budget','projects.status','projects.client_project_id','clients.email','clients.name as clientName')->where('projects.project_company_id','=',$user->company_id)->where('clients.id','=',$id)->orderBy('projects.startDate','ASC')->get();
          return $projects;
      }


     /**
     * @SWG\Get(
     *      path="/v1/project/{id}",
     *      operationId="single project",
     *      tags={"Project"},
     *      summary="Project details",
     *      description="Returns Project details",
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
     * Returns list of Projects
     */
    public function show($id){
        $projects = Project::find($id);
        return $projects;
    }

    /**
     * @SWG\Delete(
     *      path="/v1/project/{id}",
     *      operationId="delete Project",
     *      tags={"Project"},
     *      summary="Delete a Project",
     *      description="Delete a Project",
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
     * Deletes a single Project
     */
    public function delete($id){
        $projects = Project::find($id);
        $projects->delete();
        return $projects;
    }

    public function taskChartList()
    {
        $path = public_path(). "/js/sampleweather.json"; // ie: /var/www/laravel/app/storage/json/filename.json
        $json = json_decode(file_get_contents($path), true); 
        return response()->json($json , 200);
    }

    public function searchproject(Request $request)
    {
        $user = \Auth::user();
        $projectstart= date('Y-m-d',strtotime($request->get('startDate'))); 
        $projectend= date('Y-m-d',strtotime($request->get('endDate')));
        $projects = Project::leftJoin('clients','clients.id','=','client_project_id')->select('projects.id','projects.projectName', 'projects.description','projects.projectCode','projects.startDate','projects.endDate','projects.budget','projects.status','projects.client_project_id','clients.email','clients.name as clientName')->where('projects.project_company_id','=',$user->company_id);
        if (!empty($request->get('projectName')))
            $projects->where('projects.projectName', 'like', '%'. $request->get('projectName').'%');
        if (!empty($request->get('status')))
            $projects->where('projects.status', $request->get('status'));
        if (!empty($request->get('name')))
            $projects->where('clients.name', 'like', '%'.$request->get('name').'%');
        if (!empty($request->get('startDate')) && !empty($request->get('endDate')))
            $projects->WhereBetween('projects.startDate', [$projectstart,$projectend]);
        if (!empty($request->get('startDate')) && empty($request->get('endDate')))
            $projects->where('projects.startDate','=',$projectstart);
        return  $projects->get();
    } 

    public function setProjectStatus(Request $request)
    {
        $projectRepository=new ProjectRepository();
        $valid = $projectRepository->validateRules($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'status' => 'required|in:received,pending,started,in-progress,in-hold,completed,cancelled'
        ]);

        if($valid->fails()) return response()->json(['errors'=>$valid->errors()], 422);
        
        return $projectRepository->updateProjectStatus($request->project_id,$request->status);
        
    }

    /**
     * @SWG\Get(
     *      path="/v1/project/assigned",
     *      operationId="project-assigned",
     *      tags={"Project"},
     *      summary="Project list of assigned to logged in user",
     *      description="Returns Project list of assigned to logged in user",
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
     * Returns Project list of assigned to logged in user
     */
    public function assignedPrjects(Request $request){
        $user = \Auth::user();
        $projects = Project::leftJoin('milestones','milestones.project_milestone_id', '=', 'projects.id')
                            ->leftJoin('sprints','sprints.milestone_id','=','milestones.id')
                            ->leftJoin('tasks','tasks.sprint_id','=','sprints.id')
                            ->leftJoin('task_members','task_members.task_identification','=','tasks.id')
                            ->where('task_members.member_identification','=',$user->id)
                            ->groupBy('projects.id','projects.projectName','projects.description','projects.projectCode','projects.startDate','projects.endDate','projects.status')
                            ->selectRaw('projects.id, projects.projectName,projects.description,projects.projectCode,projects.startDate,projects.endDate,projects.status, sum(IF(task_members.member_identification = 1,1,0)) as countTasks')
                            ->get();
        return $projects;
    }

      /**
     * @SWG\Get(
     *      path="/v1/project/project-code/{type}",
     *      operationId="project-code",
     *      tags={"Project"},
     *      summary="Project code for the Employee (type=internal/external)",
     *      description="Returns Project code for the Employee",
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
     * Returns Project code for the Employee
     */
    public function projectCode($type){
        $helper = new HelperFunctions();
        $projectCode=$helper->getInternalProjectId($type);
        return ['projectCode'=>$projectCode];
    }
}
