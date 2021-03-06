<?php

namespace App\Models;
use App\Libs\Helper;

/**
 * App\Models\BiScene
 *
 * @property int $id
 * @property string|null $scenes_name 场景名称
 * @property string|null $scenes_remark 备注
 * @property int $customer_id
 * @property int $ev_model 兼容旧表
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BiScene whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BiScene whereEvModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BiScene whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BiScene whereScenesName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BiScene whereScenesRemark($value)
 * @mixin \Eloquent
 */
class BiScene extends \App\Models\Base\BiScene
{
	protected $fillable = [
		'scenes_name',
		'scenes_remark',
		'customer_id',
		'ev_model'
	];

    public static function getAllIds()
    {
        $rs = self::get()->toArray();
        return Helper::transToOneDimensionalArray($rs, 'id');
    }

    public static function getTypeName()
    {
        $rs = self::orderByDesc('id')->get()->toArray();
        return Helper::transToKeyValueArray($rs, 'id', 'scenes_name');
    }
}
