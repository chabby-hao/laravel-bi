<?php

namespace App\Logics;


//工厂逻辑

use App\Models\BiDeliveryDevice;
use App\Models\BiDeliveryOrder;
use App\Models\BiOrder;
use App\Models\BiUser;
use App\Models\TDeviceCode;
use Carbon\Carbon;

class FactoryLogic extends BaseLogic
{

    //工厂角色名称
    public static $roleName = 'factory';

    public static function getAccountList()
    {
        $users = BiUser::all();
        $data = [];
        foreach ($users as $user){
            if($user->hasRole(self::$roleName)){
                $data[$user->id] = $user->nickname ? : $user->username;
            }
        }
        return $data;
    }

    public function shipment($id, $imeis)
    {

        $shipOrder = BiDeliveryOrder::find($id);
        if(!$shipOrder || in_array($shipOrder->state,[BiDeliveryOrder::DELIVERY_ORDER_STATE_FINISH, BiDeliveryOrder::DELIVERY_ORDER_STATE_CANCEL])){
            return false;
        }

        $insert = [];
        foreach ($imeis as $imei){
            if(!DeviceLogic::getUdid($imei)){
                return false;
            }
            $insert[] = [
                'delivery_order_id'=>$id,
                'imei'=>$imei,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ];
        }

        $res = BiDeliveryDevice::insert($insert);

        if($res){
            $shipOrder->state = BiDeliveryOrder::DELIVERY_ORDER_STATE_FINISH;//已完成
            $shipOrder->actuall_date = Carbon::now()->format('Y-m-d');
            $shipOrder->save();

            $order = BiOrder::find($shipOrder->order_id);
            $count = count($imeis);
            $order->actuall_quantity += $count;//实际订单出货量
            if($count >= $order->ship_quantity){
                $order->state = BiOrder::ORDER_STATE_FINISH;//订单状态
            }
            $order->save();

            TDeviceCode::whereIn('imei',$imeis)->update([
                'delivered_at'=>Carbon::now(),
                'device_cycle'=>TDeviceCode::DEVICE_CYCLE_CHANNEL_STORAGE,
            ]);

        }
        return $res;
    }

}