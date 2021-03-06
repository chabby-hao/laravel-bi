<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 06 Jun 2018 17:54:23 +0800.
 */

namespace App\Models\Base;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class TApiLog
 *
 * @property int $sid
 * @property int $type
 * @property \Carbon\Carbon $call_tm
 * @property string $key
 * @property int $response
 * @package App\Models\Base
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TApiLog whereCallTm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TApiLog whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TApiLog whereResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TApiLog whereSid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TApiLog whereType($value)
 * @mixin \Eloquent
 */
class TApiLog extends Eloquent
{
	protected $connection = 'care';
	protected $table = 't_api_log';
	protected $primaryKey = 'sid';
	public $timestamps = false;

	protected $casts = [
		'type' => 'int',
		'response' => 'int'
	];

	protected $dates = [
		'call_tm'
	];
}
