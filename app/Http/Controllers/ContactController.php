<?php

namespace App\Http\Controllers;

use App\Contacts;
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
       ]);
       
       if($valid->fails()) return response()->json(['errors'=>$valid->errors()], 422);
        $contact=null;
       if(empty($request->id)){
            $contact=new Contacts();
       }else{
            $contact=Contacts::find($request->id);
       }

       $contact->firstName=$request->firstName;
       $contact->lastName=$request->lastName;
       $contact->email=$request->email;
       $contact->streetNo=$request->streetNo;
       $contact->postalCode=$request->postalCode;
       $contact->city=$request->city;
       $contact->country=$request->country;
       $contact->status=$request->status;
       $contact->contact_customer_id=$request->contact_customer_id;
       try {
            $contact->save();
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
        
        return $this->model->getCustomers($request->customer_id)->get();
   }
}