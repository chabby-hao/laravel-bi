<?php

namespace App\Models;

use App\Libs\Helper;

/**
 * App\Models\BiDeviceType
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $remark
 * @property string|null $options
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BiDeviceType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BiDeviceType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BiDeviceType whereRemark($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BiDeviceType whereOptions($value)
 */
class BiDeviceType extends \App\Models\Base\BiDeviceType
{

    private static $data = [];

	protected $fillable = [
		'name',
		'remark',
        'options',
	];

    public static function getNameMap($cache = true)
    {
        if($cache && self::$data){
            return self::$data;
        }
        $rs = self::orderByDesc('id')->get();
        self::$data = Helper::transToKeyValueArray($rs, 'id', 'name');
        return self::$data;
    }

    public static function getNameMapNoCache()
    {
        return self::getNameMap(false);
    }

    public static function getAllIds()
    {
        $rs = self::get()->toArray();
        return Helper::transToOneDimensionalArray($rs, 'id');
    }

}
