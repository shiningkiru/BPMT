<?php
namespace App\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Master\Repository;

class MasterController extends Controller {
    protected $model;
    protected $validationRules=[];
    protected $validationCreateRules;
    protected $validationUpdateRules;

    
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

   public function create(Request $request) {
        $validator = $this->model->validateRules($request->all(),$this->validationCreateRules ?? $this->validationRules);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        
        return $this->model->create($request->all());
   }

   public function update(Request $request, $id) {
        $validator = $this->model->validateRules($request->all(),$this->validationUpdateRules ?? $this->validationRules);
        
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        
        return $this->model->update($request->all(), $id);
   }

}