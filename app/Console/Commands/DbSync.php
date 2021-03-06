<?php

namespace App\Console\Commands;


use App\Libs\Helper;
use App\Logics\DeviceLogic;
use App\Models\BiBrand;
use App\Models\BiChannel;
use App\Models\BiCustomer;
use App\Models\BiDeviceType;
use App\Models\BiEbikeType;
use App\Models\BiProductType;
use App\Models\BiScene;
use App\Models\TDeviceCategory;
use App\Models\TDeviceCategoryDicNew;
use App\Models\TDeviceCode;
use Illuminate\Support\Facades\DB;

class DbSync extends BaseCommand
{

    protected $signature = 'db:sync';
    protected $description = '新老数据库同步';

    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        $this->categoryDicNew();
        //已使用BI发货，不需要老的渠道品牌同步了
        //$this->deviceCode();
    }

    /**
     * 渠道相关表同步
     */
    private function categoryDicNew()
    {
        $dbOperate = DB::connection('care_operate');
        $db = $dbOperate->table('t_device_category_dic_new');

        $res = $db->where(['products' => 6])->orderBy('type')->get()->toArray();

        $arr = BiProductType::getNameMap();

        $arrFlip = array_flip($arr);


        $channelMap = BiChannel::getChannelMap();
        $channelMap = array_flip($channelMap);

        foreach ($res as $row) {
            $name = $row->name;
            if ($row->level == 2) {
                if (isset($arrFlip[$name])) {
                    //产品类型
                    BiProductType::firstOrCreate([
                        'product_name' => $name,
                        'id' => $arrFlip[$name],
                    ], [
                        'product_remark' => $row->remark
                    ]);
                }

            } elseif ($row->level == 3) {
                //渠道
                BiChannel::firstOrCreate([
                    'channel_name' => $name,
                ], [
                    'channel_remark' => $row->remark,
                ]);
                //$a = BiChannel::whereChannelName($name)->first();
                //var_dump($a);exit;
            } elseif ($row->level == 5) {
                //品牌
                try {
                    BiBrand::firstOrCreate([
                        'brand_name' => $name,
                    ], [
                        'id' => $row->type,
                        'brand_remark' => $row->remark,
                    ]);
                }catch (\Exception $e){
                    BiBrand::where([
                        'id'=>$row->type,
                    ])->update([
                        'brand_name' => $name,
                        'brand_remark' => $row->remark,
                    ]);
                }

                $channelName = TDeviceCategoryDicNew::where(['channel' => $row->channel, 'level' => 3])->first()['name'];
                $channelId = $channelMap[$channelName];
                if(!$channelId){
                    continue;
                }
                //客户
                try {
                    BiCustomer::firstOrCreate([
                        'customer_name' => $name,
                        'channel_id'=>$channelId,
                    ], [
                        'id' => $row->type,
                        'customer_remark' => $row->remark,
                    ]);
                }catch (\Exception $e){
                    BiCustomer::where([
                        'id'=>$row->type,
                        'channel_id'=>$channelId,
                    ])->update([
                        'customer_name' => $name,
                        'customer_remark' => $row->remark,
                    ]);
                }

            }
        }

        foreach ($res as $row) {
            if ($row->level == 6) {
                //车型
                $name = $row->name;
                $type = $row->type;
                $val = TDeviceCategoryDicNew::where(['type' => $type, 'level' => 5])->first();
                if ($val) {
                    $brandName = $val->name;
                    $brandModel = BiBrand::whereBrandName($brandName)->first();
                    $brandId = $brandModel->id;

                    BiEbikeType::firstOrCreate([
                        'brand_id' => $brandId,
                        'ev_model'=>$row->ev_model,
                    ], [
                        'ebike_remark' => $row->remark,
                        'ebike_name' => $name,
                    ]);

                    BiScene::firstOrCreate([
                        'ev_model'=>$row->ev_model,
                        'customer_id'=>$brandId,
                    ], [
                        'scenes_name' => $name,
                        'scenes_remark' => $row->remark,
                    ]);
                }
            }
        }

        echo "渠道done" . "\n";
    }

    /**
     * 设备码相关表同步，按照老的渠道品牌逻辑刷新t_device_code
     */
    private function deviceCode()
    {

        $dicNews = TDeviceCategoryDicNew::whereLevel(3)->whereProducts(6)->get()->keyBy('channel')->toArray();

        //手动修改漏掉的渠道
        $dicNews[11] = ['name' => '双马'];

        $page = 1;
        $perPage = 100;
        $model = TDeviceCode::getDeviceModelHasType();

        $typeMap = BiDeviceType::getNameMap();
        $typeMap = array_flip($typeMap);

        do {
            $pagination = $model->simplePaginate($perPage, ['*'], 'page', $page++);

            /** @var TDeviceCode $deviceCode */
            foreach ($pagination->items() as $deviceCode) {
                $udid = $deviceCode->qr;
                echo "begin processing udid: $udid" . "\n";
                $type = $deviceCode->type;
                if ($deviceCode->model == BiProductType::PRODUCT_TYPE_EB001) {
                    $deviceCode->device_type = $typeMap['EB001'];
                } elseif ($deviceCode->model == BiProductType::PRODUCT_TYPE_EB001C) {
                    $deviceCode->device_type = $typeMap['EB001C'];
                }
                if ($row = TDeviceCategory::whereUdid($udid)->first()) {
                    $evModel = substr($row->model, -3);

                    $dicNew = TDeviceCategoryDicNew::whereType($type)->whereEvModel($evModel)->whereLevel(6)->first();
                    if (!$dicNew) {
                        $dicNew = TDeviceCategoryDicNew::whereType($type)->whereLevel(6)->first();
                        if (!$dicNew) {
                            $deviceCode->save();
                            continue;
                        }
                    }

                    $brandName = TDeviceCategoryDicNew::whereType($type)->whereLevel(5)->first()->name;

                    $oldEvName = $dicNew->name;
                    $channel = $dicNew->channel;
                    $channelName = $dicNews[$channel]['name'];

                    $channelId = BiChannel::whereChannelName($channelName)->first()->id;

                    $ebikeId = BiEbikeType::whereEbikeName($oldEvName)->first()->id;
                    $sceneId = BiScene::whereScenesName($oldEvName)->first()->id ?: 0;

                    $brandId = BiBrand::whereBrandName($brandName)->first()->id;
                    $customerId = BiCustomer::whereCustomerName($brandName)->first()->id ?: 0;

                    //$deviceCode->channel_id = $channelId;
                    //$deviceCode->ebike_type_id = $ebikeId;
                    //$deviceCode->brand_id = $brandId;
                    $deviceCode->customer_id = $customerId;
                    $deviceCode->scene_id = $sceneId;


                    //设备状态
                    if ($deviceCode->active > 0) {
                        $deviceCode->device_cycle = TDeviceCode::DEVICE_CYCLE_INUSE;
                    } else {
                        $deviceCode->device_cycle = TDeviceCode::DEVICE_CYCLE_CHANNEL_STORAGE;//渠道库存
                    }


                    //车型更新初始化
                    if (DeviceLogic::isEb001b($udid)) {

                        if (in_array($ebikeId, [47, 61, 51])) {
                            $deviceCode->device_type = $typeMap['B600'];
                        } elseif (in_array($ebikeId, [48])) {
                            $deviceCode->device_type = $typeMap['B605'];
                        } elseif (in_array($ebikeId, [44, 58])) {
                            $deviceCode->device_type = $typeMap['B800'];
                        } elseif (in_array($ebikeId, [27, 39, 45])) {
                            $deviceCode->device_type = $typeMap['B610'];
                        } elseif (in_array($ebikeId, [57])) {
                            $deviceCode->device_type = $typeMap['B611'];
                        } elseif (in_array($ebikeId, [35, 41])) {
                            $deviceCode->device_type = $typeMap['B620'];
                        } elseif (in_array($ebikeId, [62])) {
                            $deviceCode->device_type = $typeMap['B621'];
                        } elseif (in_array($ebikeId, [36])) {
                            $deviceCode->device_type = $typeMap['B630'];
                        } elseif (in_array($ebikeId, [55])) {
                            $deviceCode->device_type = $typeMap['B660'];
                        } elseif (in_array($ebikeId, [63])) {
                            $deviceCode->device_type = $typeMap['B661'];
                        }
                    }

                }

                $deviceCode->save();
                echo "process success udid: $udid" . "\n";

            }

        } while ($pagination->hasMorePages());

    }
}