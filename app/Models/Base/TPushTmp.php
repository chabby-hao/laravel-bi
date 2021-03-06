<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 06 Jun 2018 17:54:24 +0800.
 */

namespace App\Models\Base;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class TPushTmp
 *
 * @property int $uid
 * @property string $name
 * @property string $push_id
 * @property int $rom
 * @property string $phone
 * @property int $status
 * @package App\Models\Base
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TPushTmp whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TPushTmp wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TPushTmp wherePushId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TPushTmp whereRom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TPushTmp whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\TPushTmp whereUid($value)
 * @mixin \Eloquent
 */
class TPushTmp extends Eloquent
{
	protected $connection = 'care';
	protected $table = 't_push_tmp';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'uid' => 'int',
		'rom' => 'int',
		'status' => 'int'
	];
}
