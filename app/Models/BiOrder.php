<?php

namespace App\Models;
use App\Libs\Helper;

/**
 * App\Models\BiOrder
 *
 * @property int $id
 * @property int $state 状态,0=未完成，1=已完成，2=已作废
 * @property int $channel_id 渠道
 * @property int $customer_id 客户
 * @property int $scene_id 场景
 * @property int $order_quantity 订单数量
 * @property int $product_type 设备型号,B
 * @property \Carbon\Carbon|null $expect_delivery 期望交货时间
 * @property int $after_sale 售后订单,0=不是，1=是
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BiOrder whereAfterSale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BiOrder whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BiOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BiOrder whereExpectDelivery($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BiOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BiOrder whereOrderQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BiOrder whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BiOrder whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string|null $remark 备注
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BiOrder whereRemark($value)
 * @property int $device_type 设备型号,B
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BiOrder whereDeviceType($value)
 * @property int|null $user_id
 * @property string|null $order_no
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BiOrder whereOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BiOrder whereUserId($value)
 * @property int $ship_quantity 出货数量
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BiOrder whereShipQuantity($value)
 * @property int $actuall_quantity 实际已出货量
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BiOrder whereActuallQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BiOrder whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BiOrder whereSceneId($value)
 */
class BiOrder extends \App\Models\Base\BiOrder
{

    const ORDER_STATE_INIT = 0;//未完成
    const ORDER_STATE_FINISH = 1;//已完成
    const ORDER_STATE_CANCEL = 2;//已作废

    //是否售后
    const AFTER_SALE_NO = 0;
    const AFTER_SALE_YES = 1;

    protected $dates = [];

    protected $fillable = [
        'state',
        'user_id',
        'order_no',
        'channel_id',
        'customer_id',
        'scene_id',
        'order_quantity',
        'device_type',
        'expect_delivery',
        'after_sale',
        'remark',
        'ship_quantity',
    ];

    public static function getAfterSaleTypeName($type = null)
    {
        $map = [
            self::AFTER_SALE_NO => '否',
            self::AFTER_SALE_YES => '是',
        ];
        return $type === null ? $map : $map[$type];
    }

    public static function getStateTypeName($type = null)
    {
        $map = [
            self::ORDER_STATE_INIT =>'未完成',
            self::ORDER_STATE_FINISH =>'已完成',
            self::ORDER_STATE_CANCEL =>'已作废',
        ];
        return $type === null ? $map : $map[$type];
    }

    /**
     * @return array
     */
    public static function getUnfinishedordersMap()
    {
        $data = self::whereState(self::ORDER_STATE_INIT)->get();
        $data = $data ? $data->toArray() : [];
        return Helper::transToKeyValueArray($data, 'id','order_no');
    }

    public function incrementShipQuantity( $amount = 1, array $extra = [])
    {
        return parent::increment('ship_quantity', $amount, $extra); // TODO: Change the autogenerated stub
    }

    public function decrementShipQuantity( $amount = 1, array $extra = [])
    {
        return parent::decrement('ship_quantity', $amount, $extra); // TODO: Change the autogenerated stub
    }

    public function incrementActuallQuantity( $amount = 1, array $extra = [])
    {
        return parent::increment('actuall_quantity', $amount, $extra); // TODO: Change the autogenerated stub
    }

}
