<?php

/**
 * Created by Reliese Model.
 * Date: Tue, 06 Mar 2018 18:26:39 +0800.
 */

namespace App\Models\Base;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class BiBrand
 *
 * @property int $id
 * @property string $brand_name
 * @property string $brand_remark
 * @package App\Models\Base
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BiBrand whereBrandName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BiBrand whereBrandRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BiBrand whereId($value)
 * @mixin \Eloquent
 */
class BiBrand extends Eloquent
{
	protected $connection = 'bi';
	public $timestamps = false;
}