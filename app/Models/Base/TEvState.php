<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 06 Jun 2018 17:54:23 +0800.
 */

namespace App\Models\Base;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class TEvState
 *
 * @property string $udid
 * @property int $create_time
 * @property int $ev_key
 * @property int $ev_lock
 * @property float $voltage
 * @property int $percent
 * @property int $mileage
 * @property int $speed
 * @property int $gear
 * @property int $local_voltage
 * @package App\Models\Base
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TEvState whereCreateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TEvState whereEvKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TEvState whereEvLock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TEvState whereGear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TEvState whereLocalVoltage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TEvState whereMileage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TEvState wherePercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TEvState whereSpeed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TEvState whereUdid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TEvState whereVoltage($value)
 * @mixin \Eloquent
 */
class TEvState extends Eloquent
{
	protected $connection = 'care';
	protected $table = 't_ev_state';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'create_time' => 'int',
		'ev_key' => 'int',
		'ev_lock' => 'int',
		'voltage' => 'float',
		'percent' => 'int',
		'mileage' => 'int',
		'speed' => 'int',
		'gear' => 'int',
		'local_voltage' => 'int'
	];
}
