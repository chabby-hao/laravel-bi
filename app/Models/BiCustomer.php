<?php

namespace App\Models;

/**
 * App\Models\BiCustomer
 *
 * @property int $id 兼容原dic_new 下面的type
 * @property int $channel_id
 * @property string|null $customer_name 品牌名
 * @property string|null $customer_remark
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BiCustomer whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BiCustomer whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BiCustomer whereCustomerRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BiCustomer whereId($value)
 * @mixin \Eloquent
 */
class BiCustomer extends \App\Models\Base\BiCustomer
{
	protected $fillable = [
        'id',
		'channel_id',
		'customer_name',
		'customer_remark'
	];
}
