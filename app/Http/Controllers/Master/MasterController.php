<?php
namespace App\Http\Controllers\Master;

use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Master\Repository;

class MasterController extends Controller {
    protected $model;
    protected $validationRules=[];
    protected $validationCreateRules=[];
    protected $validationUpdateRules=[];

    
   public function __construct(Repository $model)
   {
       $this->model = $model;
   }

   public function index()
   {
       return $this->model->all();
   }

   public function get(Request $request, $id)
   {
       return $this->model->show($id);
   }

   public function create(Request $request)
   {
        $validator = $this->validateRules($request->all());
        
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        
        return $this->model->create($request->all());
   }

   public function update(Request $request, $id)
   {
        $validator = $this->validateRules($request->all(), 'update');
        
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        
        return $this->model->update($request->all(), $id);
   }

   public function validateRules($data, $mode='create'){
    if($mode == 'create'){
        if(sizeof($this->validationCreateRules) > 0)
            $this->validationRules = $this->validationCreateRules;
    }else if($mode == 'update'){
        if(sizeof($this->validationUpdateRules) > 0)
            $this->validationRules = $this->validationUpdateRules;
    }
    return Validator::make($data, $this->validationRules);
   }

}