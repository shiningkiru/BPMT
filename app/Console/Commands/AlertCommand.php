<?php

namespace App\Console\Commands;

use App\User;
use App\Contacts;
use App\Customer;
use Illuminate\Console\Command;
use App\Repositories\NotificationRepository;

class AlertCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'day:alert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Alert for users';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $flag = true;
        while($flag){
            
            $flag =\DB::transaction(function()use ($flag) {

                try{
                    $notificationRepository = new NotificationRepository();
                    $todaysDate = new \Datetime();
                    $tommorow =new \Datetime();
                    $tommorow = $tommorow->modify('+1 day');
            
                    //customer contact person birthdate alert
                    $contactPersons = Contacts::leftJoin('customers', 'customers.id', '=', 'contacts.contact_customer_id')
                                                ->where(function($query) use($todaysDate, $tommorow) {
                                                    $query->where('contacts.dateOfBirth', 'LIKE', "____-".$todaysDate->format('m-d'))
                                                        ->orWhere('contacts.dateOfBirth', 'LIKE', "____-".$tommorow->format('m-d'));
                                                })
                                                ->select('contacts.*', 'customers.responsible_user_id', 'customers.company')
                                                ->distinct('contacts.*', 'customers.responsible_user_id', 'customers.company')
                                                ->get();
                    foreach($contactPersons as $contact) {
                        $bdate = new \Datetime($contact->dateOfBirth);
                        $diff = date_diff($todaysDate, $bdate);
                        
                        $message = $contact->firstName. " " . $contact->lastName . " of ". $contact->company . " have ". $diff->y .'th birthday on '.$bdate->format('d M');
                        $notificationRepository->sendNotification(null, User::find($contact->responsible_user_id), $message, 'customer-birthday', $contact->contact_customer_id);
                    }
                    //end of customer contact person birthday alert
            
                    //customer todo alert
                    $todoCustomer = Customer::leftJoin('todos', 'todos.linkId', '=', 'customers.id')
                                                ->where(function($query) use($todaysDate, $tommorow) {
                                                    $query->where('todos.dateFor', $todaysDate->format('Y-m-d')."%")
                                                        ->orWhere('todos.dateFor', $tommorow->format('Y-m-d')."%");
                                                })
                                                ->where('todos.status', '=', 'open')
                                                ->select('customers.*', 'todos.id as todoId', 'todos.details', 'todos.dateFor', 'todos.to_do_resp_user')
                                                ->distinct('customers.*', 'todos.id as todoId', 'todos.details', 'todos.dateFor', 'todos.to_do_resp_user')
                                                ->get();
                    foreach($todoCustomer as $customer) {
                        $tdate = new \Datetime($customer->dateFor);
                        $message = 'You have a todo of '.$customer->details. " on ".$tdate->format('d M Y');
                        $notificationRepository->sendNotification(null, User::find($customer->to_do_resp_user), $message, 'customer-todo', $customer->id);
                    }                         
                    //end of customer todo alert
            
                    //customer opprtunity alert
                    $oppCustomer = Customer::leftJoin('customer_opportunities', 'customer_opportunities.customer_op_id', '=', 'customers.id')
                                                ->leftJoin('contacts', 'contacts.id', 'customer_opportunities.customer_contact_person')
                                                ->where(function($query) use($todaysDate, $tommorow) {
                                                    $query->where('customer_opportunities.dateFor', $todaysDate->format('Y-m-d')."%")
                                                        ->orWhere('customer_opportunities.dateFor', $tommorow->format('Y-m-d')."%");
                                                })
                                                ->where('customer_opportunities.status', '=', 'open')
                                                ->select('customers.*', 'customer_opportunities.dateFor', 'customer_opportunities.details', 'customer_opportunities.customer_contact_person', 'contacts.firstName as cfName', 'contacts.lastName as clName')
                                                ->distinct('customers.*', 'customer_opportunities.dateFor', 'customer_opportunities.details', 'customer_opportunities.customer_contact_person', 'contacts.firstName', 'contacts.lastName')
                                                ->get();
                    foreach($oppCustomer as $customer){
                        $odate = new \Datetime($customer->dateFor);
                        $message  = "You have ".$customer->details. " Follow up with ".$customer->cfName." ".$customer->clName. " on ".$odate->format('d M Y');
                        $notificationRepository->sendNotification(null, User::find($customer->responsible_user_id), $message, 'customer-opportunity', $customer->id);
                    }
                    //end of customer opprtunity alert
            
                    //customer meeting alert
                    $mtCustomer = Customer::leftJoin('customer_meetings', 'customer_meetings.customer_id', '=', 'customers.id')
                            ->where(function($query) use($todaysDate, $tommorow) {
                                $query->where('customer_meetings.dateFor', $todaysDate->format('Y-m-d')."%")
                                    ->orWhere('customer_meetings.dateFor', $tommorow->format('Y-m-d')."%");
                            })
                            ->where('customer_meetings.status', '=', 'open')
                            ->select('customers.*', 'customer_meetings.dateFor', 'customer_meetings.details')
                            ->distinct('customers.*', 'customer_meetings.dateFor', 'customer_meetings.details')
                            ->get();
                    foreach($mtCustomer as $customer){
                        $odate = new \Datetime($customer->dateFor);
                        $message  = "You have ".$customer->details. " meeting on ".$odate->format('d M Y');
                        $notificationRepository->sendNotification(null, User::find($customer->responsible_user_id), $message, 'customer-meeting', $customer->id);
                    }
                    //end of customer meeting alert
            
                    //customer call alert
                    $mtCustomer = Customer::leftJoin('customer_calls', 'customer_calls.customer_id', '=', 'customers.id')
                            ->where(function($query) use($todaysDate, $tommorow) {
                                $query->where('customer_calls.dateFor', $todaysDate->format('Y-m-d')."%")
                                    ->orWhere('customer_calls.dateFor', $tommorow->format('Y-m-d')."%");
                            })
                            ->where('customer_calls.status', '=', 'open')
                            ->select('customers.*', 'customer_calls.dateFor', 'customer_calls.details')
                            ->distinct('customers.*', 'customer_calls.dateFor', 'customer_calls.details')
                            ->get();
                    foreach($mtCustomer as $customer){
                        $odate = new \Datetime($customer->dateFor);
                        $message  = "You have ".$customer->details. " call on ".$odate->format('d M Y');
                        $notificationRepository->sendNotification(null, User::find($customer->responsible_user_id), $message, 'customer-call', $customer->id);
                    }
                    //end of customer call alert
            
                    //task due date alert
                    $taskDueUsers = User::leftJoin('task_members', 'task_members.member_identification', '=', 'users.id')
                                        ->leftJoin('tasks', 'tasks.id', '=', 'task_members.task_identification')
                                        ->leftJoin('sprints', 'tasks.sprint_id', 'sprints.id')
                                        ->where(function($query) use($todaysDate, $tommorow) {
                                            $query->where('tasks.endDate', $todaysDate->format('Y-m-d')."%")
                                                ->orWhere('tasks.endDate', $tommorow->format('Y-m-d')."%");
                                        })
                                        ->where(function($query) {
                                            $query->where('tasks.status', '=', 'created')
                                                ->orWhere('tasks.status', '=', 'assigned')
                                                ->orWhere('tasks.status', '=', 'onhold')
                                                ->orWhere('tasks.status', '=', 'inprogress');
                                        })
                                        ->select('users.*', 'tasks.taskName', 'sprints.sprintTitle', 'tasks.endDate as taskEndDate')
                                        ->distinct('users.*', 'tasks.taskName', 'sprints.sprintTitle', 'tasks.endDate')
                                        ->get();
                    foreach($taskDueUsers as $user) {
                        $tdate = new \Datetime($user->taskEndDate);
                        $message = " Your task ".$user->taskName."(".$user->sprintTitle.") is due on ".$tdate->format('d M Y');
                        $notificationRepository->sendNotification(null, User::find($user->id), $message, 'task-due', $user->id);
                    }
                    //end of task due date alert
                    $flag=false;
                    return $flag;
                }catch(\Exception $e){
                    $flag=true;
                    return $flag;
                }
            });
        }
    }
}
