<?php

namespace App\Http\Controllers;

use App\Contacts;
use App\ContactUpdates;
use App\CustomerOpportunity;
use Illuminate\Http\Request;
use App\Events\NotificationFired;
use Illuminate\Pagination\Paginator;
use App\Repositories\ContactRepository;
use App\Http\Controllers\Master\MasterController;

class ContactController extends MasterController
{
   public function __construct()
   {
        parent::__construct(new ContactRepository());
   }

   public function addContact(Request $request){
        $valid = $this->model->validateRules($request->all(), [
            'id' => 'nullable|exists:contacts,id',
            'firstName' => 'required',
            'lastName' => 'required',
            'streetNo' => 'required',
            'postalCode' => 'required',
            'city' => 'required',
            'country' => 'required',
            'telephone' => 'nullable|numeric',
            'mobile' => 'nullable|numeric',
            'email' => 'required|email',
            'dateOfBirth' => 'nullable|date',
            'contact_customer_id' => 'required|exists:customers,id',
            'status' => 'required|in:active,inactive',
            'updates' => 'nullable',
            'updates.*.id' => 'nullable|exists:contact_updates,id',
            'updates.*.details' => 'required',
            'opprtunities' => 'nullable',
            'opprtunities.*.id' => 'nullable|exists:customer_opportunities,id',
            'opprtunities.*.details' => 'required',
            'opprtunities.*.dateFor' => 'required|date',
       ]);
       
       if($valid->fails()) return response()->json(['errors'=>$valid->errors()], 422);
        $contact=null;
        $oldUpdates=[];
       if(empty($request->id)){
            $contact=new Contacts();
       }else{
            $contact=Contacts::find($request->id);
            foreach($contact->updates as $updt){
                 $oldUpdates[] = $updt->id;
            }
       }

       $contact->firstName=$request->firstName;
       $contact->lastName=$request->lastName;
       $contact->email=$request->email;
       $contact->mobile=$request->mobile;
       $contact->telephone=$request->telephone;
       $contact->designation=$request->designation;
       $contact->streetNo=$request->streetNo;
       $contact->postalCode=$request->postalCode;
       $contact->city=$request->city;
       $contact->country=$request->country; 
       $contact->dateOfBirth=new \Datetime($request->dateOfBirth); 
       $contact->interests=$request->interests; 
       $contact->status=$request->status;
       $contact->contact_customer_id=$request->contact_customer_id;

       try {
            $contact->save();

            
          if(is_array($request->updates)) {
               foreach($request->updates as $updt){
                    if(empty($updt['id'])){
                         $update = new ContactUpdates();
                    }else {
                         $update = ContactUpdates::find($updt['id']);
                         if (($key = array_search($update->id, $oldUpdates)) !== false) {
                              unset($oldUpdates[$key]);
                          }
                    }
                    $update->details = $updt['details'];
                    $update->contact_id = $contact->id;
                    $update->save();
               }
               foreach($oldUpdates as $updt){
                    $update = ContactUpdates::find($updt);
                    $update->delete();
               }
          }
          if(is_array($request->opportunities)) {
               foreach($request->opportunities as $opty){
                    if(empty($opty['id'])){
                         $opprtunity = new CustomerOpportunity();
                         $opprtunity->customer_op_id=$request->contact_customer_id;        
                         $opprtunity->customer_contact_person=$contact->id;             
                         $opprtunity->status="open";        
                    }else {
                         $opprtunity = CustomerOpportunity::find($opty['id']);
                    }
                    $opprtunity->details = $opty['details'];
                    $opprtunity->dateFor = new \Datetime($opty['dateFor']);
                    $opprtunity->save();
               }
          }
       }catch(\Exception $e){
          return response()->json(['errors'=>['email'=>["Email seems to be added."]]], 422);
       }
       return $contact;
   }

   public function getByCustomer(Request $request){
        $valid = $this->model->validateRules($request->all(), [
            'customer_id' => 'required|exists:customers,id'
        ]);
        
        if($valid->fails()) return response()->json(['errors'=>$valid->errors()], 422);
        
        return $this->model->getCustomers($request->customer_id)->with('updates')->get();
   }
}