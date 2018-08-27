<?php

/**
 * Created by Reliese Model.
 * Date: Mon, 27 Aug 2018 16:44:42 +0800.
 */

namespace App\Models\Base;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class BiCity
 * 
 * @property int $id
 * @property string $city
 * @property int $pid
 *
 * @package App\Models\Base
 */
class BiCity extends Eloquent
{
	protected $connection = 'bi';
	protected $table = 'bi_city';
	protected $primaryKey = 'city';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'id' => 'int',
		'pid' => 'int'
	];
}
