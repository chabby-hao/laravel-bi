<?php
/**
 * Created by PhpStorm.
 * User: chabby
 * Date: 18/3/14
 * Time: 下午3:37
 */

namespace App\Http\Controllers\Admin;


use App\Libs\Helper;
use App\Libs\MyPage;
use App\Logics\DeliveryLogic;
use App\Logics\DeviceLogic;
use App\Logics\DewinLogic;
use App\Logics\LocationLogic;
use App\Logics\MileageLogic;
use App\Logics\MsgLogic;
use App\Logics\RedisLogic;
use App\Logics\StatLogic;
use App\Logics\UserLogic;
use App\Models\BiBrand;
use App\Models\BiCardDatum;
use App\Models\BiCardLiangxun;
use App\Models\BiChannel;
use App\Models\BiCustomer;
use App\Models\BiDeviceType;
use App\Models\BiEbikeType;
use App\Models\BiProductType;
use App\Models\BiScene;
use App\Models\BiUser;
use App\Models\TDevice;
use App\Models\TDeviceCode;
use App\Models\TEvMileageGp;
use App\Models\TLockLog;
use App\Models\TUser;
use App\Models\TUserMsg;
use App\Objects\DeviceObject;
use App\Objects\FaultObject;
use App\Objects\LocationObject;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
use App\Logics\FactoryLogic;
use App\Models\BiDeliveryOrder;

class DeviceController extends BaseController
{


    private function getSingleUdid($id)
    {
        $udid = $this->getUdid($id);
        if (!$udid) {
            $udids = UserLogic::getUdidListByAdminPhone($id);
            if ($udids && count($udids) === 1) {
                return $udids[0];
            } elseif ($udids && count($udids) > 1) {
                $str = '';
                foreach ($udids as $udid) {
                    $str .= '<br/>' . '<a href="' . URL::action('Admin\DeviceController@detail', ['id' => $udid]) . '">' . $udid . ' </a>';
                }
                $this->outputErrorWithDie('该手机号绑定了多个设备，请点击下列设备码进行查询:' . $str);
            }
        } else {
            return $udid;
        }
    }

    public function detail(Request $request)
    {

        $cookieKey = 'lastIds';
        $lastIds = $request->cookie($cookieKey, "[]");

        if ($request->isXmlHttpRequest()) {

            $id = $request->input('id');
            $name = $request->input('name');
            if ($id) {
                $udid = $this->getSingleUdid($id);
            } elseif ($name) {
                $udid = DeviceLogic::getUdidByName($name);
            } else {
                return $this->outPutError('请填设备码');
            }
            if (!$udid) {
                return $this->outPutError('查找不到设备信息');
            }

            if (!TDeviceCode::where($this->getWhere())->first()) {
                return $this->outPutError('设备码无权限查看');
            }

            $deviceObj = DeviceLogic::createDeviceByUdid($udid);
            $data = (array)$deviceObj;

            //补充信息
            $data['imsi'] = DeviceLogic::getImsi($data['imei']);
            $data['romVersion'] = DeviceLogic::getRomVersionByUdid($udid);
            //$data['ver'] = DeviceLogic::getVerByUdid($udid);
            $data['mcu'] = DeviceLogic::getMcuByUdid($udid);


            $shipOrder = DeliveryLogic::getOrderShipInfo($data['imei']);

            $data['shipOrder'] = $shipOrder;
            $data['deliveredAt'] = $deviceObj->getDeliverdAt();

            $data['name'] = DeviceLogic::getNameByUdid($udid);
            $data['master'] = DeviceLogic::getAdminInfoByUdid($udid);
            if ($data['master'] && $data['master']['phone']) {
                $data['userConfig'] = UserLogic::getUserConfigByPhone($data['master']['phone']);
            }
            $data['followers'] = DeviceLogic::getFollowersByUdid($udid);
            $data['gpsSatCount'] = DeviceLogic::getGpsSatCount($data['imei']);
            //GPS信号强度
            $snr = DeviceLogic::getGpsSnr($data['imei']);
            if ($snr && $snr['arr']) {
                $snrStr = '';
                foreach ($snr['arr'] as $arr) {
                    $snrStr .= 'id=' . $arr['id'] . ',signal=' . $arr['snr'] . '<br/>';
                }
                $data['snr'] = rtrim($snrStr, ' ');
                $data['snrTime'] = Carbon::createFromTimestamp($snr['time'])->toDateTimeString();
            }
            $data['lastLocation'] = DeviceLogic::getLastLocationInfo($data['imei']);

            $data['chassis'] = DeviceLogic::getChassisByUdid($udid);

            $data['faultControl'] = $data['faultSwitch'] = $data['faultMotor'] = $data['faultCharge'] = '正常';
            $faults = DeviceLogic::getFault($data['imei']);
            if ($faults) {
                //控制器
                if (in_array(FaultObject::EV_MESSAGE_POWER_SYSTEM_CONTROL, $faults)) {
                    $data['faultControl'] = '异常';
                }
                //转把
                if (in_array(FaultObject::EV_MESSAGE_POWER_SYSTEM_SWITCH, $faults)) {
                    $data['faultSwitch'] = '异常';
                }
                //电机状态
                if (in_array(FaultObject::EV_MESSAGE_POWER_SYSTEM_DRIVER, $faults)) {
                    $data['faultMotor'] = '异常';
                }
                //电瓶故障
                if (!$data['charge']) {
                    $data['faultCharge'] = '异常';
                }
            }

            $data['totalMiles'] = DeviceLogic::getTotalMilesByUdid($udid);
            $data['ridingTimes'] = DeviceLogic::getRidingTimesByUdid($udid);
            $data['chargingTimes'] = DeviceLogic::getChargingTimesByUdid($udid);


            if ($lastTrip = DeviceLogic::getLastTripInfoByUdid($udid)) {
                $data['lastTrip'] = $this->getMileageInfo($lastTrip);
            }


            //url
            $data['locationUrl'] = URL::action('Admin\DeviceController@locationList', ['imei' => $data['imei']]);
            $data['lockLogUrl'] = URL::action('Admin\DeviceController@lockLogList', ['imei' => $data['imei']]);
            $data['historyStateUrl'] = Url::action('Admin\DeviceController@historyState', ['imei' => $data['imei']]);
            $data['mileageUrl'] = Url::action('Admin\DeviceController@mileageList', ['id' => $udid]);
            $data['satellite'] = Url::action('Admin\DeviceController@historyStrength', ['imei' => $data['imei']]);
            $data['messageUrl'] = Url::action('Admin\MessageController@list', ['imei' => $data['imei']]);

            $batteryType=RedisLogic::getBatteryTypeByImei($data['imei']);

            if($batteryType==BiDeliveryOrder::BATTERY_TYPE_XINPU || $batteryType==BiDeliveryOrder::BATTERY_TYPE_AIBIKE){
                //张飞
                $data['batteryUrl'] = Url::action('Admin\DeviceController@historyZhangfei', ['imei' => $data['imei']]);
                $zhangfei = RedisLogic::getZhangfeiTransByImei($data['imei']);
                $data['zhangfei'] = $zhangfei;
            }elseif($batteryType==BiDeliveryOrder::BATTERY_TYPE_ZHONGLI){
                //485
                $data['batteryUrl'] = Url::action('Admin\DeviceController@four', ['imei' => $data['imei']]);
                $battery485 = RedisLogic::get485BatteryInfoByImei($data['imei']);
                $data['battery485'] = $battery485;
            }

            //服务信息
            $data['paymentInfo'] = DeviceLogic::getPaymentInfoByUdid($udid);

            //保险
            $data['insureList'] = DeviceLogic::getInsureOrderListByUdid($udid)->toArray();
            $data['insureListHas'] = $data['insureList'] ? true : false;

            //安全区域
            $data['safeZoneList'] = DeviceLogic::getSafeZoneListByUdid($udid)->toArray();
            $data['safeZoneListHas'] = $data['safeZoneList'] ? true : false;

            //提醒消息
            $data['caremsg'] = $this->getMsgCount($udid);

            $realimsi = substr($data['imsi'], 1);

            if($cardInfo = DeviceLogic::getCardInfoByImsi($realimsi)){
                $data['cardInfo'] = $cardInfo;
                $data['cardUrl'] = URL::action('Admin\DeviceController@cardDailyList',['msisdn'=>$cardInfo['msisdn']]);
            }



            $lastIds = json_decode($lastIds, true);

            if ($id) {
                //只存设备码，imei，imsi,不存name
                if (($k = array_search($id, $lastIds)) !== false) {
                    unset($lastIds[$k]);
                    array_unshift($lastIds, $id);
                } else {
                    array_unshift($lastIds, $id);
                    if (count($lastIds) > 5) {
                        array_pop($lastIds);
                    }
                }
            }
            $cookie = Cookie::make($cookieKey, json_encode($lastIds), 60 * 24 * 30);
            //详情AJAX
            return $this->outPutWithCookie($data, $cookie);
        }

        return view('admin.device.detail', [
            'lastIds' => json_decode($lastIds, true),
        ]);
    }

    private function getMsgCount($udid)
    {
        $typeMap = [
            'safe' => [
                //安全
                TUserMsg::MESSAGE_TYPE_INSIDE,//进入安全
                TUserMsg::MESSAGE_TYPE_NEW_OUTSIDE,//离开安全
                TUserMsg::MESSAGE_TYPE_NEW_MOTOR_SHAKE,
                TUserMsg::MESSAGE_TYPE_NEW_MOTOR_FORGET,
            ],

            'inuse' => [
                //用车
                TUserMsg::MESSAGE_TYPE_MOTOR_LOCK,//锁车提醒
                TUserMsg::MESSAGE_TYPE_MOTOR_UNLOCK,//解锁
                TUserMsg::MESSAGE_TYPE_NEW_MOTOR_BATTERY,//电瓶断开
            ],

            'care' => [
                //关怀
                TUserMsg::MESSAGE_TYPE_LOW_POWER,//低电量
                TUserMsg::MESSAGE_TYPE_WEATHER_NOTICE,//天气提醒
                TUserMsg::MESSAGE_TYPE_CARE_NOTICE,//保养提醒
            ],

            'fault' => [
                //故障
                TUserMsg::MESSAGE_TYPE_FAULT_NOTICE,
            ]
        ];

        $data = [];
        foreach ($typeMap as $k => $types) {
            $data[$k] = array_map(function ($type) use ($udid) {
                return TUserMsg::getTypeNameMap($type) . MsgLogic::getMsgTypeCount($udid, $type) . '次';
            }, $types);
        }
        return $data;
    }

    private function getMileageInfo($mileRow)
    {
        $tmp = [];
        $tmp['udid'] = $mileRow->udid;
        $tmp['dateTime'] = Carbon::createFromTimestamp($mileRow->begin)->toDateTimeString();
        /*$tmp['addressBegin'] = $lastTrip->addressBegin;
        $tmp['addressEnd'] = $lastTrip->addressEnd;*/
        $tmp['mile'] = $mileRow->mile;
        $tmp['duration'] = number_format($mileRow->duration / 60, 1);
        $tmp['speed'] = number_format($tmp['mile'] / ($mileRow->duration / 60 / 60), 1);
        $tmp['energy'] = DeviceLogic::getEnergyByMileage($tmp['mile']);
        return $tmp;
    }

    public function exportList()
    {
        $model = TDeviceCode::getDeviceModel();

        $this->listSearch($model);
        $deviceList = $model->orderByDesc('active')->select(['*'])->get()->toArray();

        $deviceTypeMap = BiDeviceType::getNameMap();
        $channelMap = BiChannel::getChannelMap();
        $brandMap = BiBrand::getBrandMap();
        $ebikeTypeMap = BiEbikeType::getTypeName();
        $productTypeMap = BiProductType::getNameMap();
        $deviceCycleMap = TDeviceCode::getCycleMap();

        $data = [];
        foreach ($deviceList as $k => $device) {
            //$data[] = DeviceLogic::createDevice($device->imei);
            //$tmp = DeviceLogic::getDeviceFromCacheByUdid($device['qr']) ?: DeviceLogic::simpleCreateDevice($device['imei']);
            $data[] = [
                'udid' => '`' . $device['qr'],
                'imei' => '`' . $device['imei'],
                'deviceTypeName' => $deviceTypeMap[$device['device_type']],
                'channelName' => $channelMap[$device['channel_id']],
                'brandName' => $brandMap[$device['brand_id']],
                'ebikeTypeName' => $ebikeTypeMap[$device['ebike_type_id']],
                'activeAt' => $device['active'] ? date('Y-m-d H:i:s', $device['active']) : '',
                'deviceCycleTrans' => $deviceCycleMap[$device['device_cycle']],
                'productType' => $productTypeMap[$device['model']],
            ];
            unset($deviceList[$k]);
        }

        $file = 'export-' . date('YmdHis');
        $path = 'export/excel/';

        Helper::exportExcel([
            '设备码',
            'IMEI',
            '型号',
            '渠道',
            '品牌',
            '车型',
            '激活时间',
            '设备周期',
            '产品类型'
        ], $data, $file, public_path($path), false);
        $fileUrl = asset($path . $file . '.xlsx');
        return $this->outputRedictWithoutMsg($fileUrl);
    }

    /**
     * 缓存策略：按照ID缓存,in(1,2,3,4)
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function list(Request $request)
    {

        $model = TDeviceCode::getDeviceModel();

        $this->listSearch($model);

        $devices = $model->orderByDesc('active')->paginate(100);
        $deviceList = $devices->items();

        $data = [];
        $deviceTypeMap = BiDeviceType::getNameMap();
        $brandMap = BiBrand::getBrandMap();
        $ebikeTypeMap = BiEbikeType::getTypeName();
        $channelMap = BiChannel::getChannelMap();
        $customerMap = BiCustomer::getCustomerMap();
        $sceneMap = BiScene::getTypeName();

        /** @var TDeviceCode $device */
        foreach ($deviceList as $device) {
            //$data[] = DeviceLogic::createDevice($device->imei);
            $deviceObj = DeviceLogic::getDeviceFromCacheByUdid($device->qr) ?: DeviceLogic::simpleCreateDevice($device->imei);
            $deviceObj->setDeviceTypeName($deviceTypeMap[$device->device_type]);
            $deviceObj->setEbikeTypeName($ebikeTypeMap[$device->ebike_type_id]);
            $deviceObj->setBrandName($brandMap[$device->brand_id]);
            $deviceObj->setChannelName($channelMap[$device->channel_id]);
            $deviceObj->setCustomerName($customerMap[$device->customer_id]);
            $deviceObj->setSceneName($sceneMap[$device->scene_id]);
            $data[] = $deviceObj;
        }

        if (!$this->isCustomer()) {
            //全部使用缓存,
            list($deviceStatusMap, $deviceCycleMap) = $this->getDeviceCacheKey();
        } else {
            //渠道,品牌
            list($deviceStatusMap, $deviceCycleMap) = $this->getDeviceCountKey();
        }

        $provinceList = DeviceLogic::getProvinceList($this->getWhere());

        return view('admin.device.list', [
            'datas' => $data,
            'page_nav' => MyPage::showPageNav($devices),
            'deviceStatusMap' => $deviceStatusMap,
            'deviceCycleMap' => $deviceCycleMap,
            'provinceList' => $provinceList,
        ]);

    }

    private function getDeviceCountKey()
    {
        $deviceStatusMap = DeviceObject::getDeviceStatusCacheMap();
        $deviceCycleMap = TDeviceCode::getChannelCycleMap();

        $deviceStatusMap = $this->getCountMap($deviceStatusMap);
        $deviceCycleMap = $this->getCountMap($deviceCycleMap);

        return [$deviceStatusMap, $deviceCycleMap];
    }

    private function getCountMap($map)
    {
        /*$keyPre = $this->getCustomerKeyPre();
        $where = $this->getWhere();
        $cacheTime = Carbon::now()->addMinutes(15);
        $typeId = Auth::user()->type_id;
        foreach ($map as $k => $row) {
            $cacheKey = DeviceObject::CACHE_LIST_PRE . $k;
            $sids = Cache::store('file')->get($cacheKey);
            $count = Cache::store('file')->remember(DeviceObject::CACHE_LIST_COUNT_PRE . $keyPre . $typeId . $k, $cacheTime, function () use ($sids, $where) {
                //$count = TDeviceCode::getDeviceModel()->whereIn('qr', $udids)->where($where)->count();
                $count = TDeviceCode::getDeviceModel()->whereIn('sid', $sids)->where($where)->count();
                return $count;
            });

            $map[$k] = $row . "($count)";
        }*/
        foreach ($map as $k => $row) {
            $map[$k] = $row;
        }
        return $map;
    }

    /**
     * @param Model $model
     * @return mixed
     */
    private function listSearch($model)
    {
        if ($status = \Request::input('status')) {
            $cacheKey = DeviceObject::CACHE_LIST_PRE . $status;
            $ids = Cache::store('file')->get($cacheKey) ?: [];
            $model->whereIn('sid', $ids);
        }

        $attach = \Request::input('attach');
        if (is_numeric($attach)) {
            if ($attach == DeviceObject::ONLINE) {
                $cacheKeys = [
                    //DeviceObject::CACHE_LIST_PRE . DeviceObject::CACHE_LIST_RIDING,
                    //DeviceObject::CACHE_LIST_PRE . DeviceObject::CACHE_LIST_PARK,
                    DeviceObject::CACHE_LIST_PRE . DeviceObject::CACHE_LIST_ONLINE,
                ];
            } elseif($attach == DeviceObject::OFFLINE) {
                $cacheKeys = [
                    //DeviceObject::CACHE_LIST_PRE . DeviceObject::CACHE_LIST_OFFLINE_LESS_48,
                    //DeviceObject::CACHE_LIST_PRE . DeviceObject::CACHE_LIST_OFFLINE_MORE_48,
                    DeviceObject::CACHE_LIST_PRE . DeviceObject::CACHE_LIST_OFFLINE,
                ];
            }
            $ids = [];
            foreach ($cacheKeys as $cacheKey) {
                $ids = array_merge($ids, Cache::store('file')->get($cacheKey) ?: []);
            }
            $model->whereIn('sid', $ids);
        }

        $active = \Request::input('active');
        if (is_numeric($active)) {
            if ($active == DeviceObject::ACTIVE) {
                $model->where('active','>',0);
            } elseif($active == DeviceObject::NOT_ACTIVE) {
                $model->where('active','<=', 0);
            }
        }

        if ($id = \Request::input('id')) {
            $udid = $this->getUdid($id);
            if (!$udid) {
                //管理员手机号查询
                $udids = UserLogic::getUdidListByAdminPhone($id);
                if ($udids) {
                    $model->whereIn('qr', $udids);
                } else {
                    $model->whereIn('qr', ['']);
                }
            } else {
                $model->whereQr($udid);
            }
        }

        $rom = \Request::input('rom');
        if ($rom || is_numeric($rom)) {
            $model->whereRom($rom);
        }

        $mcu = \Request::input('mcu');
        if ($mcu || is_numeric($mcu)) {
            $model->whereMcu($mcu);
        }

        if ($deviceType = \Request::input('device_type')) {
            $model->whereDeviceType($deviceType);
        }

        if ($channel = \Request::input('channel_id')) {
            $model->whereChannelId($channel);
        }

        if($customer = \Request::input('customer_id')){
            $model->whereCustomerId($customer);
        }

        if($scene = \Request::input('scene_id')){
            $model->whereSceneId($scene);
        }

        if ($brand = \Request::input('brand_id')) {
            $model->whereBrandId($brand);
        }

        if ($ebikeType = \Request::input('ebike_type_id')) {
            $model->whereEbikeTypeId($ebikeType);
        }

        //省市筛选
        if (\Request::input('province') || $city = \Request::input('city')) {
            $where = [
                'province' => \Request::input('province'),
                'city' => \Request::input('city'),
            ];
            $where = array_filter($where);
            $model->join('t_device', 'qr', '=', 'udid')
                ->where($where)
                ->select('t_device_code.*');
        }

        $model->where($this->getWhere());

        return $model;
    }

    private function getDeviceCacheKey()
    {
        $deviceStatusMap = DeviceObject::getDeviceStatusCacheMap();
        $deviceCycleMap = TDeviceCode::getCycleMap();
        foreach ($deviceStatusMap as $k => $row) {
            //$deviceStatusMap[$k] = $row . '(' . Cache::store('file')->get(DeviceObject::CACHE_LIST_COUNT_PRE . $k) . ')';
            $deviceStatusMap[$k] = $row;
        }
        foreach ($deviceCycleMap as $k => $row) {
            //$deviceCycleMap[$k] = $row . '(' . Cache::store('file')->get(DeviceObject::CACHE_LIST_COUNT_PRE . $k) . ')';
            $deviceCycleMap[$k] = $row;
        }
        return [$deviceStatusMap, $deviceCycleMap];
    }

    public function locationList()
    {

        $imei = Input::get('imei');
        $udid = DeviceLogic::getUdid($imei);

        list($startDatetime, $endDatetime) = $this->getDaterange();

        $where = [];
        $where['udid'] = $imei;
        if ($type = Input::get('type')) {
            if ($type == LocationObject::TYPE_GPS_REPEAT) {
                $where['type'] = LocationObject::TYPE_GPS;
                $where['repeat'] = 1;
            } elseif ($type == LocationObject::TYPE_GPS) {
                $where['type'] = $type;
                $where['repeat'] = 0;
            } else {
                $where['type'] = $type;
            }
        }

        $whereBetween = ['create_time', [Carbon::parse($startDatetime)->getTimestamp(), Carbon::parse($endDatetime)->getTimestamp()]];
        $paginate = $this->getUnionTablePaginate('t_location_new_', $where, $whereBetween, 'location', $startDatetime, $endDatetime);

        $data = $paginate->items();

        foreach ($data as &$row) {
            $row->datetime = Carbon::createFromTimestamp($row->create_time)->toDateTimeString();
            $row->location_type = $row->type . '定位';
            if ($row->repeat == 1 && $row->type == 'GPS') {
                $row->location_type = 'GPS(repeat)';
                $row->address = Carbon::createFromTimestamp($row->begin)->toDateTimeString() . $row->address;
            }
            $row->gsm = $row->gsmStrength ? '-' . $row->gsmStrength . 'DB' : '';
            $row->usb_trans = is_numeric($row->usb) ? ($row->usb ? '是' : '否') : '';
        }

        return view('admin.device.locationlist', [
            'datas' => $data,
            'page_nav' => MyPage::showPageNav($paginate),
            'udid' => $udid,
            'start' => $startDatetime,
            'end' => $endDatetime,
        ]);
    }

    public function historyState()
    {
        $imei = Input::get('imei');
        $udid = DeviceLogic::getUdid($imei);
        list($startDatetime, $endDatetime) = $this->getDaterange();

        $where = ['udid' => $udid];
        $whereBetween = ['create_time', [Carbon::parse($startDatetime)->getTimestamp(), Carbon::parse($endDatetime)->getTimestamp()]];
        $paginate = $this->getUnionTablePaginate('t_ev_state_', $where, $whereBetween, 'care', $startDatetime, $endDatetime);

        $data = $paginate->items();

        foreach ($data as $row) {
            $row->datetime = Carbon::createFromTimestamp($row->create_time)->toDateTimeString();
            $row->ev_key_trans = $row->ev_key ? '开' : '关';
            $row->ev_lock_trans = $row->ev_lock ? '已锁' : '未锁';
            $row->voltage = max($row->voltage, $row->local_voltage) / 10;
            $row->usb_trans = $row->usb ? '是' : '否';
        }

        return view('admin.device.historystate', [
            'datas' => $data,
            'page_nav' => MyPage::showPageNav($paginate),
            'udid' => $udid,
            'start' => $startDatetime,
            'end' => $endDatetime,
        ]);

    }

    public function lockLogList()
    {
        $imei = Input::get('imei');
        $udid = DeviceLogic::getUdid($imei);

        list($startDatetime, $endDatetime) = $this->getDaterange();

        $map = DeviceObject::getLockTypeMap();
        $keys = array_keys($map);

        $paginate = TLockLog::whereUdid($udid)->whereIn('act', $keys)
            ->whereBetween('add_time', [$startDatetime, $endDatetime])
            ->orderByDesc('id')->paginate();

        $data = $paginate->items();
        /** @var TLockLog $row */
        foreach ($data as $row) {
            $row->act_trans = $map[$row->act];
            if (!$row->uid) {
                list($user, $from) = explode('-', $row->username);
                $row->user = $user;
                $row->from = $from;
            } else {
                $row->from = $row->login_log_id ? '超牛管家' : $row->username;
                $row->user = $row->phone;
            }
            $row->lock_type_trans = TLockLog::getLockTypeMap($row->type);
        }

        //$devices = TLockLog::->orderByDesc('sid')->select('t_device_code.*')->paginate();

        return view('admin.device.lockloglist', [
            'datas' => $data,
            'page_nav' => MyPage::showPageNav($paginate),
            'udid' => $udid,
            'start' => $startDatetime,
            'end' => $endDatetime,
        ]);

    }

    public function mileageList()
    {

        list($model, $startDatetime, $endDatetime) = $this->getTripModel();

        $paginate = $model->orderByDesc('begin')->select('t_ev_mileage_gps.*')->paginate();

        $data = $paginate->items();

        $rs = [];
        foreach ($data as $row) {
            $rs[] = $this->getMileageInfo($row);
        }

        //数量


        return view('admin.device.mileagelist', [
            'datas' => $rs,
            'page_nav' => MyPage::showPageNav($paginate),
            //'countMap' => MileageLogic::getMileCountMap($whereBetween, $where),
            'start' => $startDatetime,
            'end' => $endDatetime,
        ]);

    }

    public function tripTrails(Request $request)
    {
        $id = $this->getId($request);
        $udid = $this->getUdid($id);
        if (!$udid) {
            return $this->outPutError('查询不到数据');
        }
        list($model, ,) = $this->getTripModel();

        $data = $model->orderByDesc('end')->select('t_ev_mileage_gps.*')->get();

        $rs = [];
        foreach ($data as $row) {
            $info = $this->getMileTripsInfo($row);
            $info && $rs[] = $info;
        }
        return $this->outPut([
            'trip' => $rs,
        ]);

    }

    private function getMileTripsInfo($mileRow)
    {
        $tmp = [];
        $tmp['udid'] = $mileRow->udid;
        $tmp['begin'] = Carbon::createFromTimestamp($mileRow->begin)->toDateTimeString();
        $tmp['end'] = Carbon::createFromTimestamp($mileRow->end)->toDateTimeString();
        $tmp['time'] = Carbon::createFromTimestamp($mileRow->begin)->format('H:i') . '-' . Carbon::createFromTimestamp($mileRow->end)->format('H:i');
        $tmp['date'] = Carbon::createFromTimestamp($mileRow->begin)->toDateString();

        $locs = LocationLogic::getLocationListFromDb($mileRow->udid, $mileRow->begin, $mileRow->end);
        if (!$locs) {
            return false;
        }
        //$tmp['locs'] = $locs;
        $t = [];
        $loc = [];
        foreach ($locs as $loc) {
            $t[] = [floatval($loc['lng']), floatval($loc['lat'])];
        }
        $tmp['locs'] = $t;
        $first = $locs[0];
        $tmp['addressBegin'] = $first['address'] ?: $first['lng'] . ',' . $first['lat'];
        $tmp['addressEnd'] = array_pop($locs)['address'] ?: $loc['lng'] . ',' . $loc['lat'];

        $tmp['mile'] = $mileRow->mile;
        $tmp['duration'] = number_format($mileRow->duration / 60, 1);
        $tmp['use_time'] = Helper::secToTime($mileRow->duration);
        $tmp['speed'] = number_format($tmp['mile'] / ($mileRow->duration / 60 / 60), 1);
        $tmp['energy'] = DeviceLogic::getEnergyByMileage($tmp['mile']);
        return $tmp;
    }

    private function getTripModel()
    {
        $type = Input::get('type');
        $id = Input::get('id');

        $model = TEvMileageGp::join('t_device_code', 'udid', '=', 'qr')->where($this->getWhere());


        list($startDatetime, $endDatetime) = $this->getDaterange(Carbon::now()->startOfDay()->subDays(1)->toDateTimeString());

        $whereBetween = ['begin', [Carbon::parse($startDatetime)->getTimestamp(), Carbon::parse($endDatetime)->getTimestamp()]];
        $model->whereBetween($whereBetween[0], $whereBetween[1]);

        $where = [];
        if ($id && $udid = $this->getUdid($id)) {
            $where['udid'] = $udid;
            $model->where($where);
        } elseif ($id && empty($udid)) {
            return $this->outPutError('设备码有误');
        }

        if ($type == MileageLogic::MILE_TYPE_NORMAL) {
            $model->where('mile', '<', MileageLogic::MAX_MILE);
        } elseif ($type == MileageLogic::MILE_TYPE_ABNORMAL) {
            $model->where('mile', '>=', MileageLogic::MAX_MILE);
        }
        return [$model, $startDatetime, $endDatetime];
    }

    /**
     * 导入地区
     */
    public function importCity(Request $request)
    {
        $inputFileName = 'myfile';
        if ($request->isXmlHttpRequest() && $request->hasFile($inputFileName)) {
            //上传文件

            $data = Helper::readExcelFromRequest($request, $inputFileName);

            if (!$data) {
                return $this->outPutError('请确认文件格式正确');
            }

            //取出第一行
            array_shift($data);

            $udids = Helper::transToOneDimensionalArray($data, 0);

            //重复
            if (max(array_count_values($udids)) > 1) {
                $unique = array_unique($udids);
                $diff = array_diff_assoc($udids, $unique);
                $repeat = array_values(array_unique($diff));
                $text = implode("<br>", $repeat);
                return $this->outPutError('设备码重复:<br>' . $text);//重复的IMEI返回回去
            }

            $rs = TDeviceCode::whereIn('qr', $udids)->select('qr')->get()->toArray();

            $realUdids = Helper::transToOneDimensionalArray($rs, 'qr');

            if (count($realUdids) !== count($udids)) {
                //udid有不正确的
                $arr = array_diff($udids, $realUdids);
                $text = implode("<br>", $arr);
                return $this->outPutError('设备码错误:<br>' . $text);
            }

            if (DeviceLogic::importCity($data)) {
                return $this->outPutSuccess();
            } else {
                return $this->outPutError('导入失败,请稍后重试');
            }

        }
    }

    public function searchCity(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $province = $request->input('province');

            $rs = TDevice::whereProvince($province)->select('city')->distinct()->get()->toArray();
            $citys = Helper::transToOneDimensionalArray($rs, 'city');

            return $this->outPut(['list' => $citys]);
        }
    }

    public function map()
    {
        return view('admin.device.map');
    }

    public function romStatList()
    {
        $model = TDeviceCode::getDeviceModel();
        $this->listSearch($model);

        $datas = $model->groupBy(['rom', 'mcu'])->whereNotNull('rom')->whereNotNull('mcu')->selectRaw("count(sid) as total,rom,mcu")->orderByDesc('total')->get();
        return view('admin.device.romstatelist', [
            'datas' => $datas,
        ]);
    }

    public function stat(Request $request)
    {

        if($request->isXmlHttpRequest() || $request->input('a') == 1){
            $keypre = $this->getCustomerKeyPre();
            $id = Auth::user()->type_id;
            $data = [
                'dailyActive' => StatLogic::getDailyActive($keypre, $id),
                'travelTimes' => StatLogic::getTravelTimes($keypre, $id),
                'travelFrequency' => StatLogic::getTravelFrequency($keypre, $id),
                'tripDistance' => StatLogic::getTripDistance($keypre, $id),
                'activeGeographicalDistribution' => StatLogic::getActiveGeographicalDistribution($keypre, $id),
                'vehicleDistribution' => StatLogic::getVehicleDistribution($keypre, $id),
                'activeCurve' => StatLogic::getActiveCurve($keypre, $id),
                'tripFrequencyDistribution' => StatLogic::getTripFrequencyDistribution($keypre, $id),
            ];
            return $this->outPut($data);
        }

        return view('admin.device.stat');
    }

    public function cardList()
    {

        $model = BiCardLiangxun::join('care.t_device_code', function (JoinClause $join){
            $join->whereRaw('substr(`t_device_code`.`imsi`,2) = `bi_card_liangxun`.`imsi`');
        });

        $this->listSearch($model);

        $paginate = $model
            ->select(['bi_card_liangxun.*','imei','qr as udid','channel_id','rom','mcu','t_device_code.active as active_time'])
            ->orderByDesc('data_usage')->paginate();


        $datas = $paginate->items();

        return view('admin.device.cardlist', [
            'datas' => $datas,
            'page_nav' => MyPage::showPageNav($paginate),
        ]);

    }

    public function cardDailyList()
    {
        $msisdn = Input::get('msisdn');
        $udid = $this->getUdid($msisdn);

        list($startDatetime, $endDatetime) = $this->getDaterange(Carbon::today()->subMonths(2), 'Ymd');


        $paginate = BiCardDatum::whereMsisdn($msisdn)
            ->whereBetween('date',[$startDatetime, $endDatetime])
            ->orderByDesc('date')->paginate();

        $data = $paginate->items();

        return view('admin.device.carddailylist', [
            'datas' => $data,
            'udid'=>$udid,
            'page_nav' => MyPage::showPageNav($paginate),
            'start' => $startDatetime,
            'end' => $endDatetime,
        ]);
    }

    public function historyStrength()
    {

        $imei = Input::get('imei');

        $udid = DeviceLogic::getUdid($imei);

        list($startDatetime, $endDatetime) = $this->getDaterange();

        $where = ['udid' => $udid];
        $whereBetween = ['create_time', [Carbon::parse($startDatetime)->getTimestamp(), Carbon::parse($endDatetime)->getTimestamp()]];
        $paginate = $this->getUnionTablePaginate('t_ev_strength_', $where, $whereBetween, 'care', $startDatetime, $endDatetime);

        $data = $paginate->items();
        foreach ($data as $row) {
            $row->datetime = Carbon::createFromTimestamp($row->create_time)->toDateTimeString();

            $r=json_decode($row->snr_json,true);
            $arr = [];
            if($r){

            }else{
                $r=substr($row->snr_json,0,strripos($row->snr_json,'}') + 1);
                $r=$r.']';
                $r=json_decode($r,true);
            }
            $row->satCount = count($r);
            foreach ($r as $v) {
                foreach ($v as $k => $vs){
                    if($k == 'snr'){
                        $arr[]= $k . '=' . $vs;
                    }
                }
            }
            $str = implode(' , ', $arr);
            $row->snr_json = $str;
        }

        return view('admin.device.historystrength', [
            'datas' => $data,
            'page_nav' => MyPage::showPageNav($paginate),
            'udid' => $udid,
            'start' => $startDatetime,
            'end' => $endDatetime,
        ]);

    }
    //张飞电池
    public function historyZhangfei()
    {
        $imei = Input::get('imei');
        $udid = DeviceLogic::getUdid($imei);
        list($startDatetime, $endDatetime) = $this->getDaterange();

        $where = ['udid' => $udid];
        $whereBetween = ['create_time', [Carbon::parse($startDatetime)->getTimestamp(), Carbon::parse($endDatetime)->getTimestamp()]];
        $paginate = $this->getUnionTablePaginate('t_ev_zhangfei_', $where, $whereBetween, 'care', $startDatetime, $endDatetime);

        $data = $paginate->items();

        foreach ($data as $row) {
            $row->datetime = Carbon::createFromTimestamp($row->create_time)->toDateTimeString();
            $row->create_time=date("Y-m-d H:i:s",$row->create_time);

            if($row->battery_onlie_state=0){
                $row->battery_onlie_state='电池未连接，备用电池供电';
            }elseif ($row->battery_onlie_state=1){
                $row->battery_onlie_state='电池已连接，备用电池供电';
            }elseif ($row->battery_onlie_state=2){
                $row->battery_onlie_state='电池未连接，电瓶供电';
            }elseif ($row->battery_onlie_state=3){
                $row->battery_onlie_state='电池已连接，电瓶供电';
            }
            $row->line_state = 1? '正常' : '异常';
            $row->battery_io_current=max($row->battery_io_current, $row->battery_io_current_plus);
            $row->abk_battery_lock_status = 1? '已锁' : '未锁';
            //$row->battery_voltage=$row->battery_voltage.'mV';
//            $row->ev_lock_trans = $row->ev_lock ? '已锁' : '未锁';
//            $row->voltage = max($row->voltage, $row->local_voltage) / 10;
//            $row->usb_trans = $row->usb ? '是' : '否';
        }

        return view('admin.device.historyzhangfei', [
            'datas' => $data,
            'page_nav' => MyPage::showPageNav($paginate),
            'udid' => $udid,
            'start' => $startDatetime,
            'end' => $endDatetime,
        ]);
    }

    //485电池
    public function four(){
        $imei = Input::get('imei');
        $udid = DeviceLogic::getUdid($imei);
        list($startDatetime, $endDatetime) = $this->getDaterange();

        $where = ['udid' => $udid];
        $whereBetween = ['create_time', [Carbon::parse($startDatetime)->getTimestamp(), Carbon::parse($endDatetime)->getTimestamp()]];
        $paginate = $this->getUnionTablePaginate('t_ev_device485status_', $where, $whereBetween, 'care', $startDatetime, $endDatetime);

        $data = $paginate->items();

        foreach ($data as $row) {
            $row->datetime = Carbon::createFromTimestamp($row->create_time)->toDateTimeString();

            $row->create_time=date("Y-m-d H:i:s",$row->create_time);

            //BIT0-1： 充电状态 0-非充非放状态； 1-充电状态； 2-放电状态 BIT2： 电池状态 0-正常状态； 1-故障状态BIT3： 开关状态 0-输出关闭； 1-输出开通
            if($row->batt_charge_status_485 == 0){
                $row->batt_charge_status_485_trans = '非充非放状态';
            }elseif($row->batt_charge_status_485 == 1){
                $row->batt_charge_status_485_trans = '充电状态';
            }elseif($row->batt_charge_status_485 == 2){
                $row->batt_charge_status_485_trans = '放电状态';
            }

            if($row->batt_batt_status_485 == 0){
                $row->batt_batt_status_485_trans = '正常状态';
            }elseif($row->batt_batt_status_485 == 1){
                $row->batt_batt_status_485_trans = '故障状态';
            }

            if($row->batt_ioput_status_485 == 0){
                $row->batt_ioput_status_485_trans = '输出关闭';
            }elseif($row->batt_ioput_status_485 == 1){
                $row->batt_ioput_status_485_trans = '输出开通';
            }

        }

        return view('admin.device.four', [
            'datas' => $data,
            'page_nav' => MyPage::showPageNav($paginate),
            'udid' => $udid,
            'start' => $startDatetime,
            'end' => $endDatetime,
        ]);
    }

    //型号列表
    public function types(){

        $paginates=DB::table('bi_device_types')->orderBy('id','desc')->paginate();
        $data = $paginates->items();

        foreach ($data as $row){
            if($row->options){
                $row->options = implode("\r\n", json_decode($row->options, true));
            }
        }

        return view('admin.device.types',
            [   'datas'=>$data,
                'page_nav' => MyPage::showPageNav($paginates)
            ]);
    }

    public function typesadd(Request $request){
        if($request->isMethod('get')){
            return view('admin.device.typesadd');
        }

        if($request->isMethod('post')){
            $name=$request->input('name');
            $remark=$request->input('remark');
            $options = $request->input('options');
            if($options){
                $options = json_encode($options);
            }
            if(DB::table('bi_device_types')->insert(['name'=>$name,'remark'=>$remark,'options'=>$options])){
                return $this->outPutRedirect(URL::action('Admin\DeviceController@types'));
            }else{
                return $this->outPutError('添加失败');
            }
        }

    }

    public function typesedit(Request $request){
        if($request->isMethod('get')){
            $id=$request->input('id');
            $type=DB::table('bi_device_types')->where('id',$id)->first();
            $options = [];
            if($type->options){
                $options = json_decode($type->options, true);
            }
            return view('admin.device.typesedit',['datas'=>$type,'options'=>$options]);
        }

        if($request->isMethod('post')){
            $id=$request->input('id');
            $name=$request->input('name');
            $remark=$request->input('remark');
            $options = $request->input('options');
            if($options){
                $options = json_encode($options);
            }
            if(DB::table('bi_device_types')->where('id',$id)->update(['name'=>$name,'remark'=>$remark,'options'=>$options])){
                return $this->outPutRedirect(URL::action('Admin\DeviceController@types'));
            }else{
                return $this->outPutError('无修改信息');
            }
        }
    }



}