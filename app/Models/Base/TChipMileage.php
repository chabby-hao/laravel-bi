<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 06 Jun 2018 17:54:23 +0800.
 */

namespace App\Models\Base;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class TChipMileage
 *
 * @property int $id
 * @property int $last_mile
 * @property int $history_mile
 * @property int $add_time
 * @property int $update_time
 * @property string $udid
 * @package App\Models\Base
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TChipMileage whereAddTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TChipMileage whereHistoryMile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TChipMileage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TChipMileage whereLastMile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TChipMileage whereUdid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TChipMileage whereUpdateTime($value)
 * @mixin \Eloquent
 */
class TChipMileage extends Eloquent
{
	protected $connection = 'care';
	protected $table = 't_chip_mileage';
	public $timestamps = false;

	protected $casts = [
		'last_mile' => 'int',
		'history_mile' => 'int',
		'add_time' => 'int',
		'update_time' => 'int'
	];
}
