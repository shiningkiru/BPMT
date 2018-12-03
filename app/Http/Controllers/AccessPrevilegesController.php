<?php

namespace App\Http\Controllers;

use Response;
use App\AccessPrevileges;
use Illuminate\Http\Request;
use App\Helpers\HelperFunctions;
use App\Http\Requests\AccessPrevilegesRequest;

class AccessPrevilegesController extends Controller
{
    
    /**
     * @SWG\Post(
     *      path="/v1/access-previlege",
     *      operationId="Access-previlege-post",
     *      tags={"Access-previlege"},
     *      summary="Update access previleges",
     *      description="Update access previleges",
     *      @SWG\Parameter(
     *          name="Authorization",
     *          description="authorization header",
     *          required=true,
     *          type="string",
     *          in="header"
     *      ),
     *      @SWG\Parameter(
     *          name="access_previlages",
     *          description="array of previleges",
     *          required=true,
     *          type="string",
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
     * Updates access previleges
     */
    public function updatePrevilages(Request $request){
        $helper = new HelperFunctions();
        $access_previlages = $request->access_previlages;
        $ac_db = AccessPrevileges::count();
        $roles = $helper->getRoles();
        $module = $helper->getModels();
        foreach($access_previlages as $prev){
            try{
                $accs = AccessPrevileges::find($prev['id']);
                if($accs instanceof AccessPrevileges){
                    $accs->access_previlage = $prev['access_previlage'];
                    $accs->save();
                }
            }catch(\Exception $e){
                return Response::json(['error'=>['internal'=>$e->getMessage()]], 400);
            }
        }
        return AccessPrevileges::all()->groupBy('roles');
    }

    
    /**
     * @SWG\Get(
     *      path="/v1/access-previlege",
     *      operationId="Access-previlege-get",
     *      tags={"Access-previlege"},
     *      summary="Get all access previlleges",
     *      description="Get all access previlleges",
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
     * Get all access previlleges
     */
    public function getAllAccessPrevileges(){
        $this->managePrevileges();
        return AccessPrevileges::all()->groupBy('roles');
    }

    /**
     * @SWG\Get(
     *      path="/v1/access-previlege/user",
     *      operationId="Access-previlege-user",
     *      tags={"Access-previlege"},
     *      summary="Get user access previlleges",
     *      description="Get user access previlleges",
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
     * Get user access previlleges
     */
    public function getAccessForUser(Request $request){
        $user=\Auth::user();
        $accs = AccessPrevileges::where('roles', '=', $user->roles)->select('module_name', 'access_previlage')->orderBy('module_name','DESC')->get();
        $prev=[];
        foreach($accs as $acc){
            $prev[$acc['module_name']] = $acc['access_previlage'];
        }
        return $prev;
    }

    public function managePrevileges(){
        $helper = new HelperFunctions();
        $roles = $helper->getRoles();
        $module = $helper->getModels();
        foreach($roles as $role){
            foreach($module as $mod){
                try{
                    $old_ac = AccessPrevileges::where('module_name','=',$mod)->where('roles','=',$role)->first();
                    if(!($old_ac instanceof AccessPrevileges)){
                        $access_previlege = new AccessPrevileges();
                        $access_previlege->module_name = $mod;
                        $access_previlege->roles = $role;
                        $access_previlege->access_previlage = 'full-access';
                        $access_previlege->save();
                    }
                }catch(\Exception $e){
                }
            }
        }
    }

    /**
     * @SWG\Get(
     *      path="/v1/access-previlege/roles",
     *      operationId="Access-previlege-roles",
     *      tags={"Access-previlege"},
     *      summary="Get user roles",
     *      description="Get user roles",
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation"
     *       ),
     *       @SWG\Response(response=500, description="Internal server error"),
     *       @SWG\Response(response=400, description="Bad request"),
     *     )
     *
     * Get user roles
     */
    public function getRoles(){
        $helper = new HelperFunctions();
        return $helper->getRoles();
    }
}
