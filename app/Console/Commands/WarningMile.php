<?php

namespace App\Console\Commands;

use App\Logics\MileageLogic;
use App\Models\TEvMileageGp;

class WarningMile extends BaseWarning
{

    protected $signature = 'warning:mile';
    protected $description = '里程预警';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {

        //$db = DB::connection('care');

//     "first_page_url":"http://localhost?page=1",
//    "from":11,
//    "next_page_url":"http://localhost?page=3",
//    "path":"http://localhost",
//    "per_page":10,
//    "prev_page_url":"http://localhost?page=1",
//    "to":20

        $maxFile = MileageLogic::MAX_MILE;

        $page = 1;
        $perPage = 100;
        $mids = [];
        $whereEnd = [
            strtotime('-1 hours'),
            time(),
        ];
        $warningData = [];
        do {
            $pagination = TEvMileageGp::whereBetween('end',$whereEnd)->simplePaginate($perPage, ['*'], 'page', $page++);
            //$pagination = TEvMileageGp::simplePaginate($perPage, ['*'], 'page', $page++); //test

            /** @var TEvMileageGp $item */
            foreach ($pagination->items() as $item) {
                if ($item->mile > $maxFile) {
                    //满足报警条件，报警
                    $log = 'find mile>' . $maxFile . ' with data:' . json_encode($item);
                    $this->warning($log);
                    $mids[] = $item->mid;
                    $text = '设备: ' . $item->udid. ' 从 ' . date('Y-m-d H:i:s', $item->begin) . ' 至 ' . date('Y-m-d H:i:s', $item->end) . ' 单次行驶 ' . $item->mile . 'km';
                    $warningData[] = $text;
                    echo $log . "\n";
                }
            }

        } while ($pagination->hasMorePages());

        if($warningData){
            $this->sendEmail($warningData);
        }

    }
}