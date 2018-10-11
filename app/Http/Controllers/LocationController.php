<?php

namespace App\Http\Controllers;
use App\Location;
use Illuminate\Http\Request;
use App\Http\Requests\LocationFormRequest;

class LocationController extends Controller
{

    /**
     * @SWG\Post(
     *      path="/v1/location",
     *      operationId="create location",
     *      tags={"Location"},
     *      summary="location creation",
     *      description="Returns location details",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of the location at the time of update",
     *          required=false,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="title",
     *          description="title of the location",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="description",
     *          description="description of the location",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="address",
     *          description="Address of the location",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="longitude",
     *          description="longitude of the location",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="latitude",
     *          description="latitude of the location",
     *          required=false,
     *          type="string",
     *          in="formData"
     *      ),
     *  @SWG\Parameter(
     *          name="project_id",
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
     * Returns list of Projects
     */
    public function create(LocationFormRequest $request)
    {
        $id=$request->id;
        if(empty($id))
            $location=new Location();
        else
            $location=Location::find($id);
        $location->title=$request->title;
        $location->description=$request->description;
        $location->address=$request->address;
        $location->longitude=$request->longitude;
        $location->latitude=$request->latitude;
        $location->project_id=$request->project_id;
        $location->save();
        return $location;
    }

 /**
     * @SWG\Get(
     *      path="/v1/location",
     *      operationId="location list",
     *      tags={"Location"},
     *      summary="Location list",
     *      description="Returns Location list",
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
     * Returns list of locations
     */
    public function index(){
        $location = Location::all();
        return $location;
    }


     /**
     * @SWG\Get(
     *      path="/v1/location/{id}",
     *      operationId="single location",
     *      tags={"Location"},
     *      summary="Location details",
     *      description="Returns Location details",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="Location Id",
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
     * Returns Single location
     */
    public function show($id){
        $location = Location::find($id);
        return $location;
    }

  /**
     * @SWG\Delete(
     *      path="/v1/location/{id}",
     *      operationId="delete Location",
     *      tags={"Location"},
     *      summary="Delete a Location",
     *      description="Delete a Location",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="Location Id",
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
     * Deletes a single Location
     */
    public function delete($id){
        $location = Location::find($id);
        $location->delete();
        return $location;
    }
}