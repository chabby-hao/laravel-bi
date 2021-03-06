<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 06 Jun 2018 17:54:23 +0800.
 */

namespace App\Models\Base;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class EbikeModelSource
 *
 * @property int $model_tag
 * @property string $img2x_main
 * @property string $img2x_inspect
 * @property string $img3x_main
 * @property string $img3x_inspect
 * @property string $img2x_lock
 * @property string $img3x_lock
 * @package App\Models\Base
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\EbikeModelSource whereImg2xInspect($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\EbikeModelSource whereImg2xLock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\EbikeModelSource whereImg2xMain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\EbikeModelSource whereImg3xInspect($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\EbikeModelSource whereImg3xLock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\EbikeModelSource whereImg3xMain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\EbikeModelSource whereModelTag($value)
 * @mixin \Eloquent
 */
class EbikeModelSource extends Eloquent
{
	protected $connection = 'care';
	protected $primaryKey = 'model_tag';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'model_tag' => 'int'
	];
}
