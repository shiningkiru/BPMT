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
                $table->string('logo')->nullable(true);
                $table->string('address');
                $table->string('longitude')->nullable(true);
                $table->string('latitude')->nullable(true);
                $table->timestamps();
        });

        Schema::create('mass_parameters', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title');
                $table->enum('type', ['department', 'designation', 'role']);
                $table->unsignedInteger('ms_company_id');
                $table->foreign('ms_company_id')
                        ->references('id')
                        ->on('companies')
                        ->onDelete('cascade');
                $table->timestamps();
        });

        Schema::create('branches', function (Blueprint $table) {
                $table->increments('id');
                $table->string('branchName');
                $table->string('branchCode')->unique();
                $table->string('address');
                $table->string('longitude')->nullable(true);
                $table->string('latitude')->nullable(true);
                $table->unsignedInteger('br_company_id');
                $table->foreign('br_company_id')
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
                $table->unsignedInteger('employeeId')->unique();
                $table->string('firstName');
                $table->string('lastName')->nullable(true);
                $table->string('email')->unique();
                $table->string('mobileNumber')->unique();
                $table->string('password');
                $table->text('reset_token')->nullable(true);
                $table->text('address')->nullable(true);
                $table->string('profilePic')->nullable(true);
                $table->dateTimeTz('dob');
                $table->dateTimeTz('doj');
                $table->string('salary');
                $table->string('bloodGroup')->nullable(true);
                $table->string('relievingDate')->nullable(true);
                $table->string('isActive')->default(1);
                $table->enum('roles', ['admin', 'management', 'hr', 'team-lead', 'project-lead', 'employee']);
                $table->unsignedInteger('branch_dept_id')->nullable(true);
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


        Schema::create('clients', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('mobileNumber');
                $table->string('secondaryMobileNumber')->nullable(true);
                $table->string('email');
                $table->string('secondaryEmail')->nullable(true);
                $table->string('profilePic')->nullable(true);
                $table->string('address');
                $table->string('longitude')->nullable(true);
                $table->string('latitude')->nullable(true);
                $table->enum('status', ['active', 'inactive']);
                $table->unsignedInteger('client_company_id');
                $table->foreign('client_company_id')
                        ->references('id')
                        ->on('companies')
                        ->onDelete('cascade');
                $table->unique(['mobileNumber', 'client_company_id']);
                $table->unique(['email', 'client_company_id']);
                $table->timestamps();
        });


        Schema::create('projects', function (Blueprint $table) {
                $table->increments('id');
                $table->string('projectName')->unique();
                $table->string('description')->nullable(true);
                $table->string('projectCode')->start_from(140000)->unique();
                $table->dateTimeTz('startDate')->nullable(true);
                $table->dateTimeTz('endDate')->nullable(true);
                $table->string('budget')->nullable(true);
                $table->text('estimatedHours');
                $table->enum('projectCategory', ['internal', 'external'])->default('internal');
                $table->enum('projectType', ['support', 'service'])->default('service');
                $table->enum('status', ['new', 'received', 'pending', 'started', 'in-progress', 'on-hold', 'completed', 'cancelled']);
                $table->unsignedInteger('project_company_id');
                $table->foreign('project_company_id')
                        ->references('id')
                        ->on('companies')
                        ->onDelete('cascade');
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
                $table->text('description')->nullable(true);
                $table->string('address');
                $table->string('longitude')->nullable(true);
                $table->string('latitude')->nullable(true);
                $table->unsignedInteger('project_id');
                $table->foreign('project_id')
                        ->references('id')
                        ->on('projects')
                        ->onDelete('cascade');
                $table->timestamps();
        });

        Schema::create('project_teams', function (Blueprint $table) {
                $table->increments('id');
                $table->enum('status', ['active', 'inactive']);
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
                $table->unique(['team_project_id', 'team_user_id']);
                $table->timestamps();
        });


        Schema::create('milestones', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title');
                $table->text('description')->nullable(true);
                $table->dateTimeTz('startDate')->nullable(true);
                $table->dateTimeTz('endDate')->nullable(true);
                $table->text('estimatedHours');
                $table->float('progress')->default(0.0);
                $table->enum('status', ['created', 'assigned', 'onhold', 'inprogress','completed', 'cancelled','failed']);
                $table->unsignedInteger('dependent_milestone_id')->nullable(true);
                $table->foreign('dependent_milestone_id')
                        ->references('id')
                        ->on('milestones')
                        ->onDelete('cascade');
                $table->unsignedInteger('project_milestone_id');
                $table->foreign('project_milestone_id')
                        ->references('id')
                        ->on('projects')
                        ->onDelete('cascade');
                $table->unique(['title', 'project_milestone_id']);
                $table->timestamps();
        });

        Schema::create('sprints', function (Blueprint $table) {
                $table->increments('id');
                $table->string('sprintTitle');
                $table->dateTimeTz('startDate');
                $table->dateTimeTz('endDate');
                $table->text('estimatedHours');
                $table->enum('status', ['created', 'assigned', 'onhold', 'inprogress','completed', 'cancelled',' failed']);
                $table->enum('priority', ['critical', 'high', 'medium', 'low']);
                $table->unsignedInteger('dependent_sprint_id')->nullable(true);
                $table->foreign('dependent_sprint_id')
                        ->references('id')
                        ->on('sprints')
                        ->onDelete('cascade');
                $table->unsignedInteger('milestone_id');
                $table->foreign('milestone_id')
                        ->references('id')
                        ->on('milestones')
                        ->onDelete('cascade');
                $table->unique(['sprintTitle', 'milestone_id']);
                $table->timestamps();
        });

        Schema::create('tasks', function (Blueprint $table) {
                $table->increments('id');
                $table->string('taskName');
                $table->text('description')->nullable(true);
                $table->dateTimeTz('startDate');
                $table->dateTimeTz('endDate');
                $table->text('estimatedHours');
                $table->text('takenHours');
                $table->enum('status', ['created', 'assigned', 'onhold', 'inprogress','completed', 'cancelled',' failed']);
                $table->enum('priority', ['critical', 'high', 'medium', 'low']);
                $table->unsignedInteger('dependent_task_id')->nullable(true);
                $table->foreign('dependent_task_id')
                        ->references('id')
                        ->on('tasks')
                        ->onDelete('cascade');
                $table->unsignedInteger('sprint_id');
                $table->foreign('sprint_id')
                        ->references('id')
                        ->on('sprints')
                        ->onDelete('cascade');
                $table->unique(['taskName', 'sprint_id']);
                $table->timestamps();
        });

        
        Schema::create('task_members', function (Blueprint $table) {
                $table->increments('id');
                $table->text('estimatedHours');
                $table->text('takenHours');
                $table->unsignedInteger('task_identification');
                $table->foreign('task_identification')
                        ->references('id')
                        ->on('tasks')
                        ->onDelete('cascade');
                $table->unsignedInteger('member_identification');
                $table->foreign('member_identification')
                        ->references('id')
                        ->on('users')
                        ->onDelete('cascade');
                $table->unique(['task_identification', 'member_identification']);
                $table->timestamps();
        });

        
        Schema::create('week_validations', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('weekNumber');
                $table->integer('entryYear');
                $table->enum('status', ['entried', 'requested', 'accepted', 'reassigned'])->default('entried');
                $table->date('startDate');
                $table->date('endDate');
                $table->dateTimeTz('request_time')->nullable(true);
                $table->dateTimeTz('accept_time')->nullable(true);
                $table->unsignedInteger('user_id');
                $table->foreign('user_id')
                        ->references('id')
                        ->on('users')
                        ->onDelete('cascade');
                $table->unsignedInteger('accepted_user_id')->nullable(true);
                $table->foreign('accepted_user_id')
                        ->references('id')
                        ->on('users')
                        ->onDelete('cascade');
                $table->unique(['weekNumber', 'entryYear','user_id']);
                $table->timestamps();
        });

        Schema::create('work_time_tracks', function (Blueprint $table) {
                $table->increments('id');
                $table->text('description');
                $table->text('takenHours');
                $table->date('dateOfEntry');
                $table->boolean('isUpdated')->default(false);
                $table->unsignedInteger('task_member_identification');
                $table->foreign('task_member_identification')
                        ->references('id')
                        ->on('task_members')
                        ->onDelete('cascade');
                $table->unsignedInteger('week_number');
                $table->foreign('week_number')
                        ->references('id')
                        ->on('week_validations')
                        ->onDelete('cascade');
                $table->unique(['task_member_identification', 'dateOfEntry']);
                $table->timestamps();
        });

        Schema::create('document_managers', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title');
                $table->string('fileUrl')->nullable(true);
                $table->text('description')->nullable(true);
                $table->string('documentType')->nullable(true);
                $table->enum('relatedTo', ['project', 'milestone', 'sprint', 'task']);
                $table->unsignedInteger('doc_project_id')->nullable(true);
                $table->foreign('doc_project_id')
                        ->references('id')
                        ->on('projects')
                        ->onDelete('cascade');
                $table->unsignedInteger('doc_task_id')->nullable(true);
                $table->foreign('doc_task_id')
                        ->references('id')
                        ->on('tasks')
                        ->onDelete('cascade');
                $table->unsignedInteger('doc_milestone_id')->nullable(true);
                $table->foreign('doc_milestone_id')
                        ->references('id')
                        ->on('milestones')
                        ->onDelete('cascade');
                $table->unsignedInteger('doc_sprint_id')->nullable(true);
                $table->foreign('doc_sprint_id')
                        ->references('id')
                        ->on('sprints')
                        ->onDelete('cascade');
                $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
                $table->increments('id');
                $table->text('message');
                $table->enum('targetObjects', [0, 1, 2]);
                $table->string('module');
                $table->string('linkId')->nullable(true);
                $table->text('original')->nullable(true);
                $table->text('changes')->nullable(true);
                $table->dateTimeTz('entryTime');
                $table->unsignedInteger('entry_by');
                $table->foreign('entry_by')
                        ->references('id')
                        ->on('users')
                        ->onDelete('cascade');
        });

        Schema::create('access_previleges', function (Blueprint $table) {
                $table->increments('id');
                $table->string('module_name');
                $table->string('roles');
                $table->enum('access_previlage', ['read-only', 'editable', 'full-access', 'denied']);
                $table->unique(['module_name', 'roles']);
                $table->timestamps();
        });

           
        Schema::create('notifications', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title');
                $table->text('description')->nullable(true);
                $table->string('linkId')->nullable(true);
                $table->text('urlLink')->nullable(true);
                $table->boolean('isRead')->default(false);
                $table->string('notificationType');
                $table->integer('firstDeletedUser')->nullabale(true);
                $table->unsignedInteger('from_user_id');
                $table->foreign('from_user_id')
                        ->references('id')
                        ->on('users')
                        ->onDelete('cascade');
                $table->unsignedInteger('to_user_id');
                $table->foreign('to_user_id')
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
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('document_managers');
        Schema::dropIfExists('work_time_tracks');
        Schema::dropIfExists('week_validations');
        Schema::dropIfExists('task_members');
        Schema::dropIfExists('mass_parameters');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('project_teams');
        Schema::dropIfExists('users');
        Schema::dropIfExists('branch_departments');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('sprints');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('milestones');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('access_previleges');
    }
}
