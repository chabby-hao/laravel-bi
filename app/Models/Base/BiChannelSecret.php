<?php

/**
 * Created by Reliese Model.
 * Date: Mon, 23 Jul 2018 16:51:04 +0800.
 */

namespace App\Models\Base;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class BiChannelSecret
 * 
 * @property int $id
 * @property int $channel_id
 * @property string $channel_name
 * @property string $secret
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $push_url
 * @property string $push_types
 *
 * @package App\Models\Base
 */
class BiChannelSecret extends Eloquent
{
	protected $connection = 'bi';
	protected $table = 'bi_channel_secret';

	protected $casts = [
		'channel_id' => 'int'
	];
}
