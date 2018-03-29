<?php

namespace App\Logics;

use App\Models\BiBrand;
use App\Models\BiChannel;
use App\Models\BiEbikeType;
use App\Models\BiProductType;
use App\Models\TDevice;
use App\Models\TDeviceCode;
use App\Objects\DeviceObject;
use Illuminate\Support\Facades\Cache;

class DeviceLogic extends BaseLogic
{

    private static $devices = [];//imei=>deviceObject

    private static $imeisToUdids = [];//imei=>udid

    public static function getDevices()
    {
        return self::$devices;
    }

    public static function getDevice($imei)
    {
        if (isset(self::$devices[$imei])) {
            return self::$devices[$imei];
        }
        return false;
    }

    public static function getDeviceByUdid($udid)
    {
        $imei = static::getImei($udid);
        return static::getDevice($imei);
    }

    /**
     * 工厂模式生成1个数据
     * @param $imei
     * @return DeviceObject
     */
    public static function createDevice($imei)
    {
        $device = new DeviceObject();
        $udid = static::getUdid($imei);
        $device->setUdid($udid);
        $device->setImei($imei);
        $device->setProductType(static::getProductTypeByUdid($udid));
        $device->setProductTypeName(static::getProductTypeNameByUdid($udid));
        $device->setEbikeTypeId(static::getEbikeTypeIdByUdid($udid));
        $device->setEbikeTypeName(static::getEbikeTypeNameByUdid($udid));
        $device->setBrandId(static::getBrandIdByUdid($udid));
        $device->setBrandName(static::getBrandNameByUdid($udid));
        $device->setChannelId(static::getChannelIdByUdid($udid));
        $device->setChannelName(static::getChannelNameByUdid($udid));
        $device->setDeliverdAt(static::getDeliverdAtByUdid($udid));
        $device->setRegisterAt(static::getRegisterAtByUdid($udid));
        $device->setIsOnline(static::isOnline($imei) ? 1 : 0);
        $device->setIsOnlineTrans($device->getisOnline() ? '在线' : '离线');
        $device->setIsContact(static::isContanct($imei) ? 1 : 0);
        $device->setIsContactTrans($device->getisContact() ? '在联' : '失联');
        $loc = static::getLastLocationInfo($imei);
        if ($loc) {
            $device->setLat($loc['lat']);
            $device->setLng($loc['lng']);
            $device->setAddress($loc['address']);
        }
        $device->setGsm(static::getGsm($imei));
        $device->setChipPower(static::getChipPower($imei));
        $device->setCharge(static::isBatteryConnect($imei) ? 1 : 0);
        $device->setVoltage(static::getCurrentVoltage($imei));//0.1v
        $device->setBatteryCount(static::getBatteryCount($imei));
        $device->setBattery(static::getBattery($imei));
        $device->setExpectMile(static::getExpectMile($imei));
        $device->setTurnon(static::isTurnOn($imei) ? DeviceObject::SWITCH_STATUS_TURNON : DeviceObject::SWITCH_STATUS_TURNOFF);
        $device->setTurnonTrans($device->getTurnon() === DeviceObject::SWITCH_STATUS_TURNON ? '启动' : '关闭');
        $device->setLastContact($device->setLastContact(static::getLastContact($imei)));
        self::$devices[$imei] = $device;
        return $device;
    }

    /**
     * @param $udid
     * @return DeviceObject
     */
    public static function createDeviceByUdid($udid)
    {
        $imei = static::getImei($udid);
        return static::createDevice($imei);
    }

    public static function getUdid($imei)
    {
        if (isset(self::$imeisToUdids[$imei])) {
            return self::$imeisToUdids[$imei];
        }
        $deviceCode = TDeviceCode::whereImei($imei)->first();
        if ($deviceCode) {
            $udid = $deviceCode->qr;
            self::$imeisToUdids[$imei] = $udid;
            return $udid;
        } else {
            return false;
        }
    }

    public static function getImei($udid)
    {

        $udidsToImeis = array_flip(self::$imeisToUdids);
        if (isset($udidsToImeis[$udid])) {
            return $udidsToImeis[$udidsToImeis];
        }

        $deviceCode = TDeviceCode::whereQr($udid)->first();
        if ($deviceCode) {
            $imei = $deviceCode->imei;
            self::$imeisToUdids[$imei] = $udid;
            return $imei;
        } else {
            return false;
        }
    }

    public static function getUdidImei($udidOrImei)
    {
        if($imei = self::getImei($udidOrImei)){
            return [$udidOrImei, $imei];
        }elseif($udid = self::getUdid($udidOrImei)){
            return [$udid, $udidOrImei];
        }else{
            return false;
        }
    }

    private static function deviceCodeCallBack($udid, $func)
    {
        $deviceCode = TDeviceCode::getByUdid($udid);
        if ($deviceCode) {
            return $func($deviceCode);
        } else {
            return '';
        }
    }

    /**
     * 是否使用001B的逻辑，001C的逻辑和001B的逻辑在有些地方不一样
     * @param $udid
     * @return bool
     */
    public static function isEb001b($udid)
    {
        $deviceType = self::getDeviceTypeByUdid($udid);
        if (in_array($deviceType, [BiProductType::PRODUCT_TYPE_EB001B, BiProductType::PRODUCT_TYPE_EB001D])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 别名
     * @param $udid
     * @return string
     */
    public static function getDeviceTypeByUdid($udid)
    {
        return self::getProductTypeByUdid($udid);
    }

    public static function getProductTypeByUdid($udid)
    {
        return self::deviceCodeCallBack($udid, function ($deviceCode) {
            return $deviceCode->model;
        });
    }

    /**
     * @param $udid
     * @return string
     */
    public static function getProductTypeNameByUdid($udid)
    {
        return self::deviceCodeCallBack($udid, function ($deviceCode) {
            $productType = BiProductType::find($deviceCode->model);
            return $productType ? $productType->product_name : '';
        });
    }

    public static function getEbikeTypeIdByUdid($udid)
    {
        return self::deviceCodeCallBack($udid, function ($deviceCode) {
            return $deviceCode->ebike_type_id;
        });
    }

    public static function getEbikeTypeNameByUdid($udid)
    {

        return self::deviceCodeCallBack($udid, function ($deviceCode) {
            $ebikeTypeName = BiEbikeType::find($deviceCode->ebike_type_id);
            return $ebikeTypeName ? $ebikeTypeName->ebike_name : '';
        });
    }

    public static function getBrandId($imei)
    {
        $udid = self::getUdid($imei);
        return self::getBrandIdByUdid($udid);
    }

    public static function getBrandIdByUdid($udid)
    {
        return self::deviceCodeCallBack($udid, function ($deviceCode) {
            return $deviceCode->brand_id;
        });
    }

    public static function getBrandName($imei)
    {
        $udid = self::getUdid($imei);
        return self::getBrandNameByUdid($udid);
    }

    public static function getBrandNameByUdid($udid)
    {
        return self::deviceCodeCallBack($udid, function ($deviceCode) {
            $brand = BiBrand::find($deviceCode->brand_id);
            return $brand ? $brand->brand_name : '';
        });
    }

    public static function getChannelIdByUdid($udid)
    {
        return self::deviceCodeCallBack($udid, function ($deviceCode) {
            return $deviceCode->channel_id;
        });
    }

    public static function getChannelName($imei)
    {
        $udid = self::getUdid($imei);
        return self::getChannelNameByUdid($udid);
    }

    public static function getChannelNameByUdid($udid)
    {
        return self::deviceCodeCallBack($udid, function ($deviceCode) {
            $channel = BiChannel::find($deviceCode->channel_id);
            return $channel ? $channel->channel_name : '';
        });
    }

    /**
     * 获取出货时间 Y-m-d H:i:s
     * @param $udid
     * @return string
     */
    public static function getDeliverdAtByUdid($udid)
    {
        return self::deviceCodeCallBack($udid, function ($deviceCode) {
            return $deviceCode->delivered_at;
        });
    }

    /**
     * 获取设备码生成时间 Y-m-d H:i:s
     * @param $udid
     * @return string
     */
    public static function getRegisterAtByUdid($udid)
    {
        return self::deviceCodeCallBack($udid, function ($deviceCode) {
            return $deviceCode->registerAt;
        });
    }

    public static function isOnlineByUdid($udid, $delay = 1800)
    {
        $imei = self::getImei($udid);
        return self::isOnline($imei, $delay);
    }

    public static function isOnline($imei, $delay = 1800)
    {
        $data = RedisLogic::getDevDataByImei($imei);
        $udid = self::getUdid($imei);
        if (isset($data['attach']) && $data['attach'] == 1) {
            if (self::isEb001b($udid)) {
                return true;
            }
            if (isset($data['online']) && $data['online'] >= time() - $delay) {
                return true;
            }
        }
        return false;
    }

    public static function isContanct($imei, $delay = 1800)
    {
        $data = RedisLogic::getDevDataByImei($imei);
        if (isset($data['offline']) && $data['offline'] > time() - $delay) {
            return true;
        }
        return false;
    }

    public static function isContactByUdid($udid, $delay = 1800)
    {
        $imei = static::getImei($udid);
        return static::isContanct($imei, $delay);
    }

    public static function getLastGsmLocationInfo($imei)
    {
        return RedisLogic::getLocationByImei($imei, 'lastGSM');
    }

    public static function getLastLocationInfo($imei)
    {
        return RedisLogic::getLocationByImei($imei);
        //位置数据
        /*$location = array(
            'id' => intval($lid),	//位置数据唯一ID
            'time' => 0,			//位置时间
            'record' => 0,			//记录时间
            'begin' => 0,			//开始时间
            'type' => 0,			//类型(GPS/AGPS)
            'status' => 0,			//状态(是否在安全区域内...)
            'coord' => 0,			//坐标系类型(bti 1:WGS84; 2:GCJ02; 4:BD09)
            'store' => 0,			//位置数据存储在Redis热数据中
            'accuracy' => 0,		//位置精度
            'lat' => 0.0,			//纬度(百度坐标)
            'lng' => 0.0,			//经度(百度坐标)
            'lat_wgs' => 0.0,		//纬度(GPS坐标)
            'lng_wgs' => 0.0,		//经度(GPS坐标)
            'lat_gcj' => 0.0,		//纬度(高德坐标)
            'lng_gcj' => 0.0,		//经度(高德坐标)
            'address' => '',		//地址(百度坐标)
            'landmark' => null,		//地标(百度坐标)
            'address_wgs' => null,	//地址(GPS坐标)
            'landmark_wgs' => null,	//地标(GPS坐标)
            'address_gcj' => null,	//地标(高德坐标)
            'landmark_gcj' => null, //地标(高德坐标)
            'satCount' => 0,        //gps信号强度
            'gsmStrength' => 0,     //gsm信号强度
            'speed' =>  0,          //GPS当前速度
            'course' => -1);        //GPS当前角度(正北方向)*/

    }

    public static function getLastLocationInfoByUdid($udid)
    {
        $imei = self::getImei($udid);
        return self::getLastLocationInfo($imei);
    }

    public static function getGsm($imei)
    {
        $devData = RedisLogic::getDevDataByImei($imei);
        $gsm = 0;
        if (isset($devData['gsmStrength']) && $devData['gsmStrength']) {
            $gsm = $devData['gsmStrength'];
        } elseif ($locData = RedisLogic::getLocationByImei($imei) && isset($locData['gsmStrength'])) {
            $gsm = $locData['gsmStrength'];
        }
        return $gsm;
    }

    public static function getGsmByUdid($udid)
    {
        $imei = self::getImei($udid);
        return self::getGsm($imei);
    }

    public static function getChipPower($imei)
    {
        $data = RedisLogic::getDevDataByImei($imei);
        if (!empty($data['battery'])) {
            return $data['battery'];
        } else {
            return 0;
        }
    }

    public static function getChipPowerByUdid($udid)
    {
        $imei = static::getImei($udid);
        return static::getChipPower($imei);
    }

    /**
     * 电瓶是否在位
     * @param $imei
     */
    public static function isBatteryConnect($imei)
    {
        $data = RedisLogic::getDevDataByImei($imei);
        if (!empty($data['charge']) && $data['charge'] == 1) {
            return true;
        } else {
            return false;
        }
    }

    public static function isBatteryConnectByUdid($udid)
    {
        $imei = static::getImei($udid);
        return static::isBatteryConnect($imei);
    }

    public static function getCurrentVoltage($imei)
    {
        $data = RedisLogic::getDevDataByImei($imei);
        $vcur = 0;
        if (array_key_exists('vcur', $data)) {   //当前电压
            $vcur = intval($data['vcur']);
        } elseif (array_key_exists('localvoltage', $data)) {
            //EB001B当前电压
            $vcur = intval($data['localvoltage']);
        }
        return $vcur;
    }

    public static function getCurrentVoltageByUdid($udid)
    {
        $imei = self::getimei($udid);
        return self::getCurrentVoltage($imei);
    }

    public static function getMaxVoltage($imei)
    {
        $data = RedisLogic::getDevDataByImei($imei);
        if (array_key_exists('preV', $data)) {
            //上一次最大电压
            $vmax = intval($data['preV']);
        } elseif (array_key_exists('vmax', $data)) {   //最大电压
            $vmax = intval($data['vmax']);
        } else {
            $vmax = 620;
        }

        if ($vmax > (1333 + 100)) {   //电压值 * 100的情况
            $vmax = $vmax / 10;
        } else if ($vmax < 200) {   //真实电压值
            $vmax = $vmax * 10;
        }

        return $vmax;
    }

    public static function getMaxVoltageByUdid($udid)
    {
        $imei = self::getImei($udid);
        return self::getMaxVoltage($imei);
    }

    public static function getBatteryCount($imei)
    {

        //按照用户上传的品牌
        $device = TDevice::whereImei($imei)->first();
        if ($device && $device->user_voltage) {
            return $device->user_voltage / 12;
        }

        //大坑 这里先用name来判断，以后稳定可以替换成id.
        $brandName = self::getBrandName($imei);
        if ($brandName && $brandName == '云创慧') {
            //云创慧用48V的车
            return 4;
        } elseif ($brandName && $brandName == '云创惠二代') {
            //众星用36V的车
            return 3;
        }

        $vmax = self::getMaxVoltage($imei);

        if ($vmax == 0) {
            return 0;
        }

        //计算电池组数
        if ($vmax > 960) {
            $gcount = 8;
        } else if ($vmax > 840) {
            $gcount = 7;
        } else if ($vmax > 720) {
            $gcount = 6;
        } else if ($vmax > 600) {
            $gcount = 5;
        } else if ($vmax > 480) {
            $gcount = 4;
        } else if ($vmax > 360) {
            $gcount = 3;
        } else if ($vmax > 240) {
            $gcount = 2;
        } else if ($vmax > 120) {
            $gcount = 1;
        } else {
            $gcount = 0;
        }
        return $gcount;
    }

    public static function getBatteryCountByUdid($udid)
    {
        $imei = self::getImei($udid);
        return self::getBatteryCount($imei);
    }

    public static function getBattery($imei)
    {
        $data = RedisLogic::getDevDataByImei($imei);

        $udid = self::getUdid($imei);

        $battery = 0;

        if (array_key_exists('percent', $data)) {

            //糖果充电器
            if (!empty($data['charger_type']) && $data['charger_type'] == 2) {
                //直接返回电压
                return intval($data['charger_chargerVoltage']);
            } else {
                $vcur = static::getCurrentVoltage($imei);
            }

            /** 剩余电量校正 **/
            $gcount = self::getBatteryCount($imei);
            $umax = !empty($data['umax']) ? $data['umax'] : 135;//单位满电电池的电压
            $umax = max($umax, 135);//最大13.5V
            $vunder = $gcount * 105; //欠压总电压

            if (in_array($udid, ['928730994076', '929950255881', '921664606798'])) {
                $umax = 130;
            }
            if (in_array($udid, ['921753803719'])) {
                $umax = 128.6;
            }

            $vfull = $gcount * $umax;//满电总电压

            //欠压
            if ($vcur <= $vunder) {
                return 0;
            }

            //满电
            if ($vcur >= $vfull) {
                return 100;
            }

            //当前电量 = [(当前电压-欠压电压)/(满电电压-欠压电压)] ^ 2
            $battery = pow((($vcur - $vunder) / ($vfull - $vunder)), 2) * 100;

            $battery = intval($battery);    //round($percent)
        }
        return $battery;
    }

    public static function getBatteryByUdid($udid)
    {
        $imei = self::getImei($udid);
        return self::getBattery($imei);
    }

    /**
     * @param $battery
     * @param $gcount
     * @param int $total 最大里层
     */
    private static function getMile($battery, $gcount, $total = 0)
    {

        //如何设置了额定里程
        if ($total) {
            return intval($battery * $total / 100);
        } elseif ($battery && $gcount) {
            if ($gcount >= 8) {
                $total = 80;    //80km(96V)
            } else if ($gcount >= 7) {
                $total = 70;    //70km(84V)
            } else if ($gcount >= 6) {
                $total = 60;    //60km(72V)
            } else if ($gcount >= 5) {
                $total = 50;    //50km(60V)
            } else if ($gcount >= 4) {
                $total = 40;    //40km(48V)
            } else if ($gcount >= 3) {
                $total = 30;    //30km(36V)
            } else if ($gcount >= 2) {
                $total = 20;    //20km(24V)
            } else {
                $total = 10;    //10km(12V)
            }
            return intval($battery * $total / 100);
        } else {
            return intval($battery * 50 / 100);
        }
    }

    public static function getExpectMile($imei)
    {
        $channelName = self::getChannelName($imei);
        $battery = self::getBattery($imei);
        $gcount = self::getBatteryCount($imei);

        $mile = self::getMile($battery, $gcount);

        //得威电商的肯定比一般续航算出来的大，单独对得威进行判断
        //大坑 稳定后可以用id判断
        if ($channelName == '得威电商') {
            $mile = self::getMile($battery, $gcount, 90);
        } elseif (!empty($data['permile'])) {
            $perMiles = $data['permile'];
            if ($perMiles >= 0.3 && $perMiles <= 1.5) {
                $mile2 = intval($battery * $perMiles);
                $diff = $mile2 - $mile;
                //于原来最多波动20公里
                if (abs($diff) <= 20) {
                    $mile = $mile2;
                } else {
                    if ($mile2 > $mile) {
                        $mile += 20;
                    } else {
                        $mile -= 20;
                    }
                }
            }
        }

        return intval($mile);
    }

    public static function getExpectMileByUdid($udid)
    {
        $imei = self::getImei($udid);
        return self::getExpectMile($imei);
    }

    /**
     * @param $imei
     * @return bool
     */
    public static function isTurnOn($imei)
    {
        $data = RedisLogic::getDevDataByImei($imei);
        if (array_key_exists('turnon', $data) && $data['turnon'] == 1) {
            return true;
        } else {
            return false;
        }
    }

    public static function isTurnOnByUdid($udid)
    {
        $imei = self::getImei($udid);
        return self::isTurnOn($imei);
    }

    /**
     * @param $imei
     * @return string
     */
    public static function getLastContact($imei)
    {
        $loc = RedisLogic::getLocationByImei($imei);
        if ($loc && array_key_exists('time', $loc)) {
            return date('Y-m-d H:i:s', $loc['time']);
        }
        return '';
    }

    public static function getLastContactByUdid($udid)
    {
        $imei = self::getUdid($udid);
        return self::getLastContact($imei);
    }

    public static function storeDeviceStatusToCache($key)
    {
        TDeviceCode::getDeviceModel()->get();
    }

    public static function getRuningUdidsByCache()
    {
        $udids = Cache::remember('');
    }

}