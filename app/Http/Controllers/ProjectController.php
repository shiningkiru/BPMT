<?php

namespace App\Http\Controllers;
use App\Project;
use Illuminate\Http\Request;
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
        $id=$request->id;
        if(empty($id))
            $project=new Project();
        else
            $project=Project::find($id);
        $project->projectName=$request->projectName;
        $project->description=$request->description;
        $project->projectCode=$request->projectCode;
        $project->startDate=new \Datetime($request->startDate);
        $project->endDate=new \Datetime($request->endDate);
        $project->budget=$request->budget;
        $project->status=$request->status;
        $project->client_project_id=$request->client_project_id;
        $project->project_lead_id=$request->project_lead_id;
        $project->project_company_id=$request->company_id;
        $project->save();
        return $project;
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
        $projects=Project::leftJoin('clients','clients.id','=','client_project_id')->select('projects.id','projects.projectName', 'projects.description','projects.projectCode','projects.startDate','projects.endDate','projects.budget','projects.status','projects.client_project_id','clients.email','clients.name as clientName')->orderBy('projects.startDate','ASC')->where('projects.project_company_id','=',$user->company_id)->paginate(10);
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
}