<?php
/**
 * Created by PhpStorm.
 * User: chabby
 * Date: 18/3/14
 * Time: 下午3:37
 */

namespace App\Http\Controllers\Admin;


use App\Libs\MyPage;
use App\Logics\DeviceLogic;
use App\Logics\OrderLogic;
use App\Models\BiBrand;
use App\Models\BiCustomer;
use App\Models\BiEbikeType;
use App\Models\BiOrder;
use App\Models\BiScene;
use App\Models\TDevice;
use App\Models\TDeviceCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class CustomerController extends BaseController
{

    public function detail(Request $request)
    {
        $id = $this->getId($request);

        $model = BiCustomer::find($id);
        if($model){
            $rs = BiScene::whereCustomerId($model->id)->get();
            $data = $rs->toArray();
            foreach ($rs as $row){
                $data['children'][] = $row->toArray();
            }
            return $this->outPut($data);
        }
        return $this->outPutError();
    }

}