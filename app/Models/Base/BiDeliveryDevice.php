<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 11 Oct 2018 16:13:45 +0800.
 */

namespace App\Models\Base;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class BiDeliveryDevice
 * 
 * @property int $id
 * @property int $delivery_order_id
 * @property string $imei
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property int $state
 *
 * @package App\Models\Base
 */
class BiDeliveryDevice extends Eloquent
{
	protected $connection = 'bi';

	protected $casts = [
		'delivery_order_id' => 'int',
		'state' => 'int'
	];
}
