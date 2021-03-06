<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 06 Jun 2018 17:54:23 +0800.
 */

namespace App\Models\Base;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class TDeviceExt
 *
 * @property string $udid
 * @property string $person
 * @property int $gender
 * @property int $age
 * @property string $place
 * @property \Carbon\Carbon $birthday
 * @property string $address
 * @property string $phone
 * @property string $contact
 * @package App\Models\Base
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TDeviceExt whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TDeviceExt whereAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TDeviceExt whereBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TDeviceExt whereContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TDeviceExt whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TDeviceExt wherePerson($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TDeviceExt wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TDeviceExt wherePlace($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TDeviceExt whereUdid($value)
 * @mixin \Eloquent
 */
class TDeviceExt extends Eloquent
{
	protected $connection = 'care';
	protected $table = 't_device_ext';
	protected $primaryKey = 'udid';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'gender' => 'int',
		'age' => 'int'
	];

	protected $dates = [
		'birthday'
	];
}
