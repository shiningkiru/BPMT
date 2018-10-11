<?php

namespace App\Http\Controllers;
use App\DocumentManager;
use Illuminate\Http\Request;
use App\Http\Requests\DocumentManagerFormRequest;
use Illuminate\Http\UploadedFile;

class DocumentManagerController extends Controller
{
 /**
     * @SWG\Post(
     *      path="/v1/document-manager",
     *      operationId="create-document",
     *      tags={"Document"},
     *      summary="Document creation",
     *      description="Returns Document details",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of the Document at the time of update",
     *          required=false,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="title",
     *          description="title of the Document",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="fileUrl",
     *          description="File URL of the project",
     *          required=true,
     *          type="file",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="description",
     *          description="Description",
     *          required=true,
     *          type="string",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="project_id",
     *          description="Id of the Project",
     *          required=true,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="milestone_id",
     *          description="ID of the Milestone",
     *          required=true,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="doc_task_id",
     *          description="Task ID",
     *          required=true,
     *          type="number",
     *          in="formData"
     *      ),
     *      @SWG\Parameter(
     *          name="doc_sprint_id",
     *          description="Sprint ID",
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
     * Returns created  Document
     */
    public function create(DocumentManagerFormRequest $request)
    {
        $id=$request->id;
        if(empty($id))
            $document=new DocumentManager();
        else
            $document=DocumentManager::find($id);
        $document->title=$request->title;
        $image = $request->file('fileUrl');
        if($image instanceof UploadedFile){
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/uploads/document');
            $image->move($destinationPath, $imageName);
            $document->fileUrl = '/uploads/document/'.$imageName;
        }
        $document->description=$request->description;
        $document->documentType=$image->getClientMimeType();
        $document->doc_project_id=$request->project_id;
        $document->doc_milestone_id=$request->milestone_id;
        $document->doc_task_id=$request->doc_task_id;
        $document->doc_sprint_id=$request->doc_sprint_id;
        $document->relatedTo=$request->relatedTo;
        $document->save();
        return $document;
    }

    /**
     * @SWG\Get(
     *      path="/v1/document-manager/{id}",
     *      operationId="project-related",
     *      tags={"Document"},
     *      summary="project related documents",
     *      description="Returns project related documents",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="project Id",
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
     * Returns single document
     */
    public function show($id){
        $document = DocumentManager::where('doc_project_id','=',$id)->get();
        return $document;
    }

     /**
     * @SWG\Get(
     *      path="/v1/document-manager",
     *      operationId="list-of-document",
     *      tags={"Document"},
     *      summary="Document list",
     *      description="Returns Document list",
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
     * Returns list of documents
     */
    public function index(){
        $document = DocumentManager::all();
        return $document;
    }

     /**
     * @SWG\Delete(
     *      path="/v1/document-manager/{id}",
     *      operationId="delete-document",
     *      tags={"Document"},
     *      summary="Delete a document",
     *      description="Delete a document",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          description="Document Id",
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
     * Deletes a single document
     */
    public function delete($id){
        $document = DocumentManager::find($id);
        $document->delete();
        return $document;
    }

    /**
     * @SWG\Get(
     *      path="/v1/document-manager/download-file/{id}",
     *      operationId="download-file",
     *      tags={"Download File"},
     *      summary="Download File",
     *      description="Returns Download File",
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
     * Returns Downloadable Document
     */
    public function downloadFile($id){
        $document = DocumentManager::where('id', '=', $id)->firstOrFail();
        $fileUrl=$document->fileUrl;
        $documentType=$document->documentType;
        $file_path = public_path($fileUrl);
        return response()->download($file_path);
    }
}