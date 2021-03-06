<?php

namespace App\Console\Commands;


use App\Libs\Helper;
use App\Logics\DeviceLogic;
use App\Logics\LocationLogic;
use App\Models\BiActiveCityDevice;
use App\Models\BiActiveDevice;
use App\Models\BiBrand;
use App\Models\BiChannel;
use App\Models\BiEbikeType;
use App\Models\BiProductType;
use App\Models\BiProvince;
use App\Models\TDevice;
use App\Models\TDeviceCategory;
use App\Models\TDeviceCategoryDicNew;
use App\Models\TDeviceCode;
use App\Objects\DeviceObject;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeviceAddress extends BaseCommand
{

    protected $signature = 'device:address';
    protected $description = '设备地址统计';

    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {

        $provinceMap = BiProvince::getAllProvinceMap();

        $statActive = [];
        foreach ($provinceMap as $pid => $v) {
            $statActive[$pid] = 0;
        }

        $provinceMap = array_flip($provinceMap);

        $dayStartTime = Carbon::today()->startOfDay()->getTimestamp();
        $date = Carbon::today()->toDateString();
        $model = TDeviceCode::getDeviceModel();

        $this->batchSearch($model, function (TDeviceCode $deviceCode) use ($provinceMap, &$statActive, $dayStartTime, $date) {
            static $t = 0;
            $imei = $deviceCode->imei;
            $udid = $deviceCode->qr;

            echo ++$t . '------------------' . "\n";

            $loc = DeviceLogic::getLastLocationInfo($imei);
            DeviceLogic::clear();
            if ($loc['address']) {
                if (preg_match('/^[^省市区]+[省市区]/u', $loc['address'], $match)) {
                    $province = $match[0];
                    if (isset($provinceMap[$province]) && $pid = $provinceMap[$province]) {
                        echo 'process success ------------' . $imei . "\n";
                        $deviceCode->pid = $pid;
                        $deviceCode->save();


                        if ($loc['time'] && $loc['time'] > $dayStartTime) {
                            BiActiveCityDevice::updateOrCreate([
                                'date'=>$date,
                                'pid'=>$pid,
                                'udid'=>$udid,
                            ]);

                            $statActive[$pid]++;
                        }
                    }
                }
            }
            return [];
        });

        $total = array_sum($statActive);

        BiActiveDevice::updateOrCreate([
            'date'=>$date,
        ],[
            'total'=>$total
        ]);

        Log::notice('device address stat complete, total : ' . $total, $statActive);

    }


}