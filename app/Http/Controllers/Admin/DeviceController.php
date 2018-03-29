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
use App\Models\BiBrand;
use App\Models\TDevice;
use App\Models\TDeviceCode;

class DeviceController extends BaseController
{
    public function list()
    {
        $devices = TDeviceCode::getDeviceModel()->orderByDesc('sid')->select('t_device_code.*')->paginate();
        $deviceList = $devices->items();

        $data = [];

        /** @var TDevice $device */
        foreach ($deviceList as $device){
            $data[] = DeviceLogic::createDevice($device->imei);
        }

        dd($data);

        return view('admin.device.list', [
            'devices' => $data,
            'page_nav' => MyPage::showPageNav($devices),
        ]);


    }

}