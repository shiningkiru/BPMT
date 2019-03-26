<?php

namespace App\Http\Controllers;

use Cfun;
use App\User;
use Response;
use App\Tasks;
use App\Sprint;
use App\Project;
use App\Milestones;
use App\Notification;
use Illuminate\Http\Request;
use App\Helpers\HelperFunctions;
use App\Events\NotificationFired;
use App\Repositories\ProjectRepository;
use App\Http\Requests\ProjectFormRequest;
use App\Repositories\ActivityLogRepository;
use App\Repositories\NotificationRepository;

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
     *          name="customer_project_id",
     *          description="customer ID",
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
        $user = \Auth::user();
        $helper = new HelperFunctions();

        $dta = explode('-', $request->projectCode);
        if($request->projectCategory == 'internal' && $dta[0] != 'IPR'){
            return response()->json(['errors'=>['projectCode'=>['Internal project code format invalid']]], 422);
        } else if($request->projectCategory == 'external' && $dta[0] != 'PR'){
            return response()->json(['errors'=>['projectCode'=>['External project code format invalid']]], 422);
        }

        try{
            \DB::transaction(function() use ($helper, $request, $user){
                $id=$request->id;
                $processType="new";
                $notificationRepository = new NotificationRepository();
                $logRepository = new ActivityLogRepository();
                if(empty($id)):
                    $project=new Project();
                    $project->projectType=$request->projectType;
                else:
                    $project=Project::find($id);
                endif;
                $oldProject =clone $project;
                $projectType = $request->projectType;
                if($projectType == 'support'):
                    $processType="new-support";
                endif;
                $project->projectName=$request->projectName;
                $project->description=$request->description;
                $startdate=new \Datetime($request->startDate);
                $project->startDate=$startdate->format('Y-m-d 00:00:00');
                $enddate=new \Datetime($request->endDate);
                $project->endDate=$enddate->format('Y-m-d 00:00:00');
                $project->budget=$request->budget;
                $project->estimatedHours=$helper->timeConversion($request->estimatedHours);
                $project->status=$request->status;
                $project->projectCode=$request->projectCode;
                $project->projectCategory=$request->projectCategory;
                $project->customer_project_id=$request->customer_project_id;
                $project->project_lead_id=$request->project_lead_id;
                $project->project_company_id=$request->company_id;
                $project->save();


                if($processType == 'new-support'):
                    $milestone = $project->milestones()->first();
                    if(!($milestone instanceof Milestones))
                        $milestone = new Milestones();
                    $milestone->title=$project->projectName;
                    $milestone->startDate=$project->startDate;
                    $milestone->endDate=$project->endDate;
                    $milestone->estimatedHours=$helper->timeConversion($project->estimatedHours);
                    $milestone->status=($project->status == 'completed')?'complted':'inprogress';
                    $milestone->project_milestone_id=$project->id;
                    $milestone->save();

                    $sprint = $milestone->sprints()->first();
                    if(!($sprint instanceof Sprint))
                        $sprint = new Sprint();
                    $sprint->sprintTitle = $project->projectName;
                    $sprint->startDate = $project->startDate;
                    $sprint->endDate = $project->endDate;
                    $sprint->estimatedHours = $helper->timeConversion($project->estimatedHours);
                    $sprint->status = ($project->status == 'completed')?'complted':'inprogress';
                    $sprint->priority = "medium";
                    $sprint->milestone_id=$milestone->id;
                    $sprint->save();

                    
                    $task = $sprint->tasks()->first();
                    if(!($task instanceof Tasks))
                        $task = new Tasks();
                    $task->taskName = $project->projectName;
                    $task->startDate = $project->startDate;
                    $task->endDate = $project->endDate;
                    $task->estimatedHours = $helper->timeConversion($project->estimatedHours);
                    $task->takenHours = '00';
                    $task->status = ($project->status == 'completed')?'complted':'inprogress';
                    $task->priority = "medium";
                    $task->sprint_id = $sprint->id;
                    $task->save();
                endif;

                
                if($oldProject->project_lead_id != $project->project_lead_id){
                    $message = "You are assigned for a new project ".$project->projectName. " as a lead";
                    $notificationType = 'project-team';
                    if($projectType == 'support')
                        $notificationType = 'direct-project-team';
                    $notificationRepository->sendNotification($user, User::find($project->project_lead_id), $message, $notificationType, $project->id);
                }

                $helper->updateProjectTeam($request->project_lead_id, $project->id, 'active');
                
                return $project;
            });
            
          
        }catch(\Exception $e){
            return Response::json(['errors'=>['server'=>[$e]]], 422);
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
        $projects=Project::leftJoin('customers','customers.id','=','customer_project_id')->select('projects.id','projects.projectName', 'projects.description','projects.projectCode','projects.startDate','projects.endDate','projects.budget','projects.status','projects.customer_project_id','projects.projectType','customers.email','customers.company')->orderBy('projects.startDate','ASC')->where('projects.project_company_id','=',$user->company_id);
        
        if($user->roles != 'admin' && $user->roles != 'management'){
            $projects = $projects->leftJoin('project_teams','project_teams.team_project_id', '=', 'projects.id')
                                ->where('project_teams.team_user_id','=',$user->id)
                                ->distinct('projects.id');
        }
        $projects=$projects->paginate(500);
        return $projects;
    }


    /**
       * @SWG\Get(
       *      path="/v1/project/by-customer/{id}",
       *      operationId="project list by customer",
       *      tags={"Project"},
       *      summary="Project list by customer",
       *      description="Returns Project list by customer",
       *      @SWG\Parameter(
       *          name="Authorization",
       *          description="authorization header",
       *          required=true,
       *          type="string",
       *          in="header"
       *      ),
       *      @SWG\Parameter(
       *          name="id",
       *          description="Customer Id",
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
       * Returns list of projects by customer
       */
      public function byCustomer(Request $request,$id){
          $user = \Auth::user();
          $projects=Project::leftJoin('customers','customers.id','=','customer_project_id')->select('projects.id','projects.projectName', 'projects.description','projects.projectCode','projects.startDate','projects.endDate','projects.budget','projects.status','projects.customer_project_id','projects.projectType','customers.email','customers.company')->where('projects.project_company_id','=',$user->company_id)->where('customers.id','=',$id)->orderBy('projects.startDate','ASC')->get();
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
        $project = Project::find($id);
        if(!$project instanceof Project)return Response::json("Project Not Found", 404);
        if($project->projectType == 'support')
            $project['task']=$project->milestones()->first()->sprints()->first()->tasks()->first();
        return $project;
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
        $projects = Project::leftJoin('customers','customers.id','=','customer_project_id')
                        ->select('projects.id','projects.projectName', 'projects.description','projects.projectCode','projects.projectType','projects.startDate','projects.endDate','projects.budget','projects.status','projects.customer_project_id','customers.email','customers.company')
                        ->where('projects.project_company_id','=',$user->company_id);
        if (!empty($request->get('projectName')))
            $projects->where('projects.projectName', 'like', '%'. $request->get('projectName').'%');
        if (!empty($request->get('status')))
            $projects->where('projects.status', $request->get('status'));
        if (!empty($request->get('customer_name')))
            $projects->where('customers.company', 'like', '%'.$request->get('customer_name').'%');
        if (!empty($request->get('startDate')) && !empty($request->get('endDate')))
            $projects->WhereBetween('projects.startDate', [$projectstart,$projectend]);
        if (!empty($request->get('startDate')) && empty($request->get('endDate')))
            $projects->where('projects.startDate','=',$projectstart);
        if($user->roles != 'admin' && $user->roles != 'management'){
            $projects = $projects->leftJoin('project_teams','project_teams.team_project_id', '=', 'projects.id')
                                ->where('project_teams.team_user_id','=',$user->id)
                                ->distinct('projects.id');
        }
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
                            ->selectRaw('projects.id, projects.projectName,projects.description,projects.projectCode,projects.projectType,projects.startDate,projects.endDate,projects.status, sum(IF(task_members.member_identification = 1,1,0)) as countTasks')
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
    public function projectCode($type) {
        $helper = new HelperFunctions();
        $projectCode=$helper->getInternalProjectId($type);
        return ['projectCode'=>$projectCode];
    }

    public function employeeProjectReport($id) {
        $user = \Auth::user();
        $projects=Project::leftJoin('project_teams','project_teams.team_project_id', '=', 'projects.id')
        ->where('project_teams.team_user_id','=',$id)
        ->groupBy('projects.id','projects.projectName','projects.description','projects.projectCode','projects.startDate','projects.endDate','projects.status')
        ->select('projects.id','projects.projectName', 'projects.description','projects.projectCode','projects.startDate','projects.endDate','projects.budget','projects.status','projects.customer_project_id','projects.projectType')
        ->orderBy('projects.startDate','ASC')
        ->get();
        return $projects;       
    }
}
