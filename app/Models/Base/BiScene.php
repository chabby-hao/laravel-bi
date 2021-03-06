<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 11 Oct 2018 16:13:45 +0800.
 */

namespace App\Models\Base;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class BiScene
 * 
 * @property int $id
 * @property string $scenes_name
 * @property string $scenes_remark
 * @property int $customer_id
 * @property int $ev_model
 *
 * @package App\Models\Base
 */
class BiScene extends Eloquent
{
	protected $connection = 'bi';
	public $timestamps = false;

	protected $casts = [
		'customer_id' => 'int',
		'ev_model' => 'int'
	];
}
