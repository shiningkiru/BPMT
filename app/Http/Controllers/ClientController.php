<?php

namespace App\Http\Controllers;

use App\Client;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Http\Requests\ClientFormRequest;

class ClientController extends Controller
{


    /**
     * @SWG\Post(
     *      path="/v1/client",
     *      operationId="create client",
     *      tags={"Client"},
     *      summary="Create or update client",
     *      description="Create or update client",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="Client Id at the time of update",
     *          required=false,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="name",
     *          description="Client Name",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="mobileNumber",
     *          description="Client Mobile Number",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="secondaryMobileNumber",
     *          description="Client Secondary Mobile Number",
     *          required=false,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="email",
     *          description="Client Email",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="secondaryEmail",
     *          description="Client Secondary Email",
     *          required=false,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="address",
     *          description="Client address",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="profilePic",
     *          description="profilePic of the user",
     *          required=false,
     *          type="file",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="status",
     *          description="Client active status (active/inactive)",
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
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation"
     *       ),
     *       @SWG\Response(response=500, description="Internal server error"),
     *       @SWG\Response(response=400, description="Bad request"),
     *     )
     *
     * Create or update client
     */
    public function create(ClientFormRequest $request){
        $id=$request->id;
        if(empty($id))
            $client=new Client();
        else
            $client=Client::find($id);
        $client->name=$request->name;
        $client->email=$request->email;
        $client->secondaryEmail=$request->secondaryEmail;
        $client->mobileNumber=$request->mobileNumber;
        $client->secondaryMobileNumber=$request->secondaryMobileNumber;
        $client->address=$request->address;
        $client->status=$request->status;
        $client->client_company_id=$request->company_id;


        $image = $request->file('profilePic');
        if($image instanceof UploadedFile){
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/uploads/client');
            $image->move($destinationPath, $imageName);
            $client->profilePic = '/uploads/client/'.$imageName;
        }

        $client->save();
        return $client;
    }

    /**
     * @SWG\Get(
     *      path="/v1/client",
     *      operationId="list client",
     *      tags={"Client"},
     *      summary="Client list",
     *      description="Returns client list",
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
     * Returns list of companies
     */
    public function index(){
        $user = \Auth::user();
        $companies = Client::where('client_company_id','=',$user->company_id)->get();
        return $companies;
    }

    /**
     * @SWG\Get(
     *      path="/v1/client/{id}",
     *      operationId="single client",
     *      tags={"Client"},
     *      summary="Client details",
     *      description="Returns client details",
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
     * Returns list of companies
     */
    public function show($id){
        $client = Client::find($id);
        return $client;
    }

    /**
     * @SWG\Delete(
     *      path="/v1/client/{id}",
     *      operationId="delete client",
     *      tags={"Client"},
     *      summary="Delete a client",
     *      description="Delete a client",
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
     * Deletes a single client
     */
    public function delete($id){
        $client = Client::find($id);
        $client->delete();
        return $client;
    }
}
