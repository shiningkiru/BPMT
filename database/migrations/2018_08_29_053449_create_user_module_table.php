<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserModuleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('mobileNumber')->unique();
            $table->string('logo')->nullbale();
            $table->string('address');
            $table->string('longitude')->nullbale();
            $table->string('latitude')->nullbale();
            $table->timestamps();
        });

        Schema::create('mass_parameters', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->enum('type', ['department', 'designation', 'role']);
            $table->unsignedInteger('company_id');
            $table->foreign('company_id')
                    ->references('id')
                    ->on('companies')
                    ->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->increments('id');
            $table->string('branchName');
            $table->string('branchCode')->unique();;
            $table->string('address');
            $table->string('longitude')->nullbale();
            $table->string('latitude')->nullbale();
            $table->unsignedInteger('company_id');
            $table->foreign('company_id')
                    ->references('id')
                    ->on('companies')
                    ->onDelete('cascade');
            $table->timestamps();
        });


        Schema::create('branch_departments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('branches_id');
            $table->foreign('branches_id')
                    ->references('id')
                    ->on('branches')
                    ->onDelete('cascade');
            $table->unsignedInteger('dept_id');
            $table->foreign('dept_id')
                    ->references('id')
                    ->on('mass_parameters')
                    ->onDelete('cascade');
            $table->timestamps();
        });


        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('employeeId')->unique();
            $table->string('firstName');
            $table->string('lastName')->nullbale();
            $table->string('email')->unique();
            $table->string('mobileNumber')->unique();
            $table->string('password');
            $table->text('address')->nullbale();
            $table->string('profilePic')->nullbale();
            $table->dateTimeTz('dob');
            $table->dateTimeTz('doj');
            $table->string('salary');
            $table->string('bloodGroup')->nullbale();
            $table->string('relievingDate')->nullbale();
            $table->string('isActive');
            $table->unsignedInteger('branch_dept_id');
            $table->foreign('branch_dept_id')
                    ->references('id')
                    ->on('branch_departments')
                    ->onDelete('cascade');
            $table->unsignedInteger('designation_id');
            $table->foreign('designation_id')
                    ->references('id')
                    ->on('mass_parameters')
                    ->onDelete('cascade');
            $table->unsignedInteger('company_id');
            $table->foreign('company_id')
                    ->references('id')
                    ->on('companies')
                    ->onDelete('cascade');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::table('branch_departments', function($table) {
            $table->unsignedInteger('hod_id');
            $table->foreign('hod_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
        });


        Schema::create('clients', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('mobileNumber')->unique();
            $table->string('secondaryMobileNumber')->nullbale();
            $table->string('email')->unique();
            $table->string('secondaryEmail')->nullbale();
            $table->string('profilePic')->nullbale();
            $table->string('address');
            $table->string('longitude')->nullbale();
            $table->string('latitude')->nullbale();
            $table->timestamps();
        });


        Schema::create('company_clients', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTimeTz('doj');
            $table->unsignedInteger('company_client_id');
            $table->foreign('company_client_id')
                    ->references('id')
                    ->on('clients')
                    ->onDelete('cascade');
            $table->unsignedInteger('client_company_id');
            $table->foreign('client_company_id')
                    ->references('id')
                    ->on('companies')
                    ->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->increments('id');
            $table->string('projectName')->unique();;
            $table->string('description')->nullbale();
            $table->string('projectCode')->unique();;
            $table->dateTimeTz('startDate')->nullbale();
            $table->dateTimeTz('endDate')->nullbale();
            $table->string('budget')->nullbale();
            $table->string('status');
            $table->unsignedInteger('client_project_id');
            $table->foreign('client_project_id')
                    ->references('id')
                    ->on('clients')
                    ->onDelete('cascade');
            $table->unsignedInteger('project_lead_id');
            $table->foreign('project_lead_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            $table->timestamps();
        });


        Schema::create('locations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->text('description')->nullbale();
            $table->string('address');
            $table->string('longitude')->nullbale();
            $table->string('latitude')->nullbale();
            $table->unsignedInteger('project_id');
            $table->foreign('project_id')
                    ->references('id')
                    ->on('projects')
                    ->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('project_teams', function (Blueprint $table) {
            $table->increments('id');
            $table->string('status');
            $table->unsignedInteger('team_project_id');
            $table->foreign('team_project_id')
                    ->references('id')
                    ->on('projects')
                    ->onDelete('cascade');
            $table->unsignedInteger('team_user_id');
            $table->foreign('team_user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            $table->unsignedInteger('team_role_id');
            $table->foreign('team_role_id')
                    ->references('id')
                    ->on('mass_parameters')
                    ->onDelete('cascade');
            $table->timestamps();
        });


        Schema::create('milestones', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->text('description')->nullbale();
            $table->dateTimeTz('startDate');
            $table->dateTimeTz('endDate');
            $table->float('estimatedHours');
            $table->float('progress')->default(0.0);
            $table->string('status');
            $table->unsignedInteger('project_milestone_id');
            $table->foreign('project_milestone_id')
                    ->references('id')
                    ->on('projects')
                    ->onDelete('cascade');
            $table->unsignedInteger('assigned_to');
            $table->foreign('assigned_to')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('taskName');
            $table->text('description')->nullbale();;
            $table->dateTimeTz('startDate');
            $table->dateTimeTz('endDate');
            $table->float('estimatedHours');
            $table->float('takenHours')->default(0.0);
            $table->string('status');
            $table->integer('priority');
            $table->unsignedInteger('dependent_task_id');
            $table->foreign('dependent_task_id')
                    ->references('id')
                    ->on('tasks')
                    ->onDelete('cascade');
            $table->unsignedInteger('milestone_id');
            $table->foreign('milestone_id')
                    ->references('id')
                    ->on('milestones')
                    ->onDelete('cascade');
            $table->unsignedInteger('task_assigned_to');
            $table->foreign('task_assigned_to')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            $table->unsignedInteger('task_assigned_by');
            $table->foreign('task_assigned_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('sprints', function (Blueprint $table) {
            $table->increments('id');
            $table->string('sprintTitle');
            $table->dateTimeTz('startDate');
            $table->dateTimeTz('endDate');
            $table->string('status');
            $table->integer('priority');
            $table->unsignedInteger('dependent_sprint_id');
            $table->foreign('dependent_sprint_id')
                    ->references('id')
                    ->on('sprints')
                    ->onDelete('cascade');
            $table->unsignedInteger('task_id');
            $table->foreign('task_id')
                    ->references('id')
                    ->on('tasks')
                    ->onDelete('cascade');
            $table->unsignedInteger('sprint_assigned_by');
            $table->foreign('sprint_assigned_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            $table->unsignedInteger('sprint_handled_by');
            $table->foreign('sprint_handled_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('sprint_work_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTimeTz('startDate');
            $table->dateTimeTz('endDate');
            $table->float('totalHours');
            $table->string('work');
            $table->enum('type', ['work', 'other']);
            $table->unsignedInteger('sprint_id');
            $table->foreign('sprint_id')
                    ->references('id')
                    ->on('sprints')
                    ->onDelete('cascade');
            $table->unsignedInteger('sprint_handled_user');
            $table->foreign('sprint_handled_user')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            $table->unsignedInteger('sprint_next_user');
            $table->foreign('sprint_next_user')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('document_managers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('fileUrl')->nullbale();
            $table->text('description')->nullbale();
            $table->enum('documentType', ['file', 'image', 'no-file']);
            $table->unsignedInteger('doc_project_id');
            $table->foreign('doc_project_id')
                    ->references('id')
                    ->on('projects')
                    ->onDelete('cascade');
            $table->unsignedInteger('doc_task_id');
            $table->foreign('doc_task_id')
                    ->references('id')
                    ->on('tasks')
                    ->onDelete('cascade');
            $table->unsignedInteger('doc_milestone_id');
            $table->foreign('doc_milestone_id')
                    ->references('id')
                    ->on('milestones')
                    ->onDelete('cascade');
            $table->unsignedInteger('doc_sprint_id');
            $table->foreign('doc_sprint_id')
                    ->references('id')
                    ->on('sprints')
                    ->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->text('task');
            $table->dateTimeTz('entryTime');
            $table->unsignedInteger('activity_project_id');
            $table->foreign('activity_project_id')
                    ->references('id')
                    ->on('projects')
                    ->onDelete('cascade');
            $table->unsignedInteger('activity_milestone_id');
            $table->foreign('activity_milestone_id')
                    ->references('id')
                    ->on('milestones')
                    ->onDelete('cascade');
            $table->unsignedInteger('activity_tasks_id');
            $table->foreign('activity_tasks_id')
                    ->references('id')
                    ->on('tasks')
                    ->onDelete('cascade');
            $table->unsignedInteger('entry_by');
            $table->foreign('entry_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('document_managers');
        Schema::dropIfExists('company_clients');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('mass_parameters');
        Schema::dropIfExists('project_teams');
        Schema::dropIfExists('users');
        Schema::dropIfExists('branch_departments');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('sprint_work_logs');
        Schema::dropIfExists('sprints');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('milestones');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('companies');
    }
}
