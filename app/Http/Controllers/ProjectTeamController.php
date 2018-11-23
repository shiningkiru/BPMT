<?php

namespace App\Http\Controllers;
use App\User;
use App\Project;
use App\ProjectTeam;
use App\MassParameter;
use Illuminate\Http\Request;
use App\Http\Requests\ProjectTeamFormRequest;

class ProjectTeamController extends Controller
{
    /**
     * @SWG\Post(
     *      path="/v1/project-team",
     *      operationId="add new member to project",
     *      tags={"Project"},
     *      summary="adding team member to project",
     *      description="creating a project team",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of the project team at the time of update",
     *          required=false,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="team_user_id",
     *          description="User ID",
     *          required=true,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="team_project_id",
     *          description="Project ID",
     *          required=true,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="status",
     *          description="Status of the team member(active/inactive)",
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
     * Returns created  Projects
     */
    public function create(ProjectTeamFormRequest $request)
    {
        $id=$request->id;
        if(empty($id))
            $team=new ProjectTeam();
        else
            $team=ProjectTeam::find($id);
        $team->team_user_id=$request->team_user_id;
        $team->team_project_id=$request->team_project_id;
        $team->status=$request->status;
        $team->save();
        return $team;
    }


    /**
    * @SWG\Get(
    *      path="/v1/project-team/{id}",
    *      operationId="single project team",
    *      tags={"Project"},
    *      summary="Project team",
    *      description="Returns Project team",
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
    * Returns list of team members
    */
   public function show($id){
       //$teams = ProjectTeam::with('user')->where('team_project_id','=',$id)->first();
       $teams = User::join('project_teams','project_teams.team_user_id','=','users.id')->leftJoin('mass_parameters','designation_id','=','mass_parameters.id')->where('project_teams.team_project_id','=',$id)->select('users.id', 'users.firstName', 'users.lastName', 'users.email', 'users.mobileNumber', 'users.profilePic', 'users.id', 'mass_parameters.title as designation')->get();
       return $teams;
   }

   /**
    * @SWG\Delete(
    *      path="/v1/project-team/{id}",
    *      operationId="delete team member",
    *      tags={"Project"},
    *      summary="Delete a team member",
    *      description="Delete a team member",
    *      @SWG\Parameter(
    *          name="Authorization",
    *          description="authorization header",
    *          required=true,
    *          type="string",
    *          in="header"
    *      ),
    *      @SWG\Parameter(
    *          name="id",
    *          description="Project Team Id",
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
    * Deletes a single team member
    */

   public function delete($id){
       $team = ProjectTeam::find($id);
       $team->delete();
       return $team;
   }

   /**
    * @SWG\Get(
    *      path="/v1/project-team/{id}/{prid}",
    *      operationId="delete team member",
    *      tags={"Project"},
    *      summary="Delete a team member",
    *      description="Delete a team member",
    *      @SWG\Parameter(
    *          name="Authorization",
    *          description="authorization header",
    *          required=true,
    *          type="string",
    *          in="header"
    *      ),
    *      @SWG\Parameter(
    *          name="id",
    *          description="User Id",
    *          required=true,
    *          type="number",
    *          in="path"
    *      ),
    *      @SWG\Parameter(
    *          name="prid",
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
    * Deletes a single team member
    */

   public function deletebyUserAndProject($id, $prid){
       $team = ProjectTeam::where('team_user_id','=',$id)->where('team_project_id','=',$prid)->first();
       $team->delete();
       return $team;
   }


    /**
    * @SWG\Get(
    *      path="/v1/project-team/members/{id}",
    *      operationId="team-members",
    *      tags={"Project"},
    *      summary="Project Team Members",
    *      description="Returns Project Team Members",
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
    * Returns list of team members department wise
    */
   public function teamMembers($id){
        $project = Project::find($id);
        $teamData=[];
        $departments=MassParameter::where('ms_company_id','=',$project->project_company_id)->where('type','=','department')->get();
        $i=0;
        foreach($departments as $dept):
            $users=User::leftJoin('project_teams','users.id','=','project_teams.team_user_id')
            ->leftJoin('branch_departments','users.branch_dept_id','=','branch_departments.id')
            ->leftJoin('mass_parameters as designation','designation_id','=','designation.id')
            ->leftjoin('task_members','member_identification','=','users.id')
            ->where('project_teams.team_project_id','=',$id)
            ->where('branch_departments.dept_id','=',$dept->id)
            ->select(\DB::raw("ifnull(count(sprints.id),0) as total_sprints"),
            \DB::raw("users.id as id"),
            \DB::raw("users.firstName as firstName"),
            \DB::raw("users.lastName as lastName"),
            \DB::raw("users.email as email"),
            \DB::raw("users.mobileNumber as mobileNumber"),
            \DB::raw("users.profilePic as profilePic"),
            \DB::raw("designation.title as designation"))
            ->get();
            $dept['teamMembers']=$users;
            $teamData[$i]=$dept;
            $i++;
        endforeach;
        return $teamData;
    }

    public function TotalteamMembers($id){
         $tasks = ProjectTeam::leftjoin('projects','projects.id','=','team_project_id')
        ->leftjoin('users','users.id','=','project_lead_id')
        -> select(\DB::raw("COUNT(project_teams.id) as team_members"),
                  \DB::raw("users.firstName as lead_name") ) 
        ->where('projects.id','=',$id)
        ->groupBy('users.firstName')
        ->get();
        return $tasks[0];
    }
}