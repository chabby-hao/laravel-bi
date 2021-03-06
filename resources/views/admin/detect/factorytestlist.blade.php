@extends('admin.layout')
@section('content')

    <div class="container-fluid">
        <hr>

        <div class="row-fluid">
            <div class="span12">

                <div class="widget-box">
                    <div class="widget-title"><span class="icon"> <i class="icon-search"></i> </span>
                        <h5>筛选查询</h5>
                    </div>
                    <div class="widget-content">
                        <form method="get" class="form-search">
                            <div class="control-group">
                                <div class="inline-block w10">
                                    <input class="w1 margintop" type="text" name="MstSn" value="{{Request::input('MstSn')}}" placeholder="MstSn">
                                    <input class="w1 margintop" type="text" name="CiNum" value="{{Request::input('CiNum')}}" placeholder="CiNum">
                                    <input class="w1 margintop" type="text" name="CPAct" value="{{Request::input('CPAct')}}" placeholder="CPAct">
                                    <input class="w1 margintop" type="text" name="imsi" value="{{Request::input('imsi')}}" placeholder="imsi">
                                    <input type="submit" class="btn btn-success margintop search" value="查询">

                                </div>

                            </div>

                        </form>
                    </div>
                </div>

                <div class="widget-box">
                    <div class="widget-title"><span class="icon"><i class="icon-th"></i></span>
                        <h5>列表</h5>
                    </div>
                    <div class="widget-content nopadding" style="overflow: auto">
                        <table class="table table-bordered data-table">
                            <thead>
                            <tr>
                                <th>MstSn</th>
                                <th>MInfo</th>
                                <th>CPAct</th>
                                <th>CiNum</th>
                                <th>时间</th>
                                <th>核心板</th>
                                <th>核心板版本</th>
                                <th>imei</th>
                                <th>底板通信</th>
                                <th>加速度传感器</th>
                                <th>蓝牙MAC</th>
                                <th>蓝牙key</th>
                                <th>GPS定位</th>
                                <th>GPRS连接</th>
                                <th>底板</th>
                                <th>底板版本</th>
                                <th>蓝牙状态</th>
                                <th>I2C状态</th>
                                <th>一线通输入</th>
                                <th>一线通输出</th>
                                <th>锁车信号</th>
                                <th>电压</th>
                                <th>在位状态</th>
                                <th>里程</th>
                                <th>速度</th>
                                <th>GPS卫星</th>
                                <th>GSM基站</th>
                                <th>Imsi</th>
                                <th>电门状态</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $t = 0; ?>
                            @foreach($datas as $row)
                                <tr class="gradeX">
                                    <td>{{$row['MstSn']}}          </td>
                                    <td>{{$row['MInfo']}}          </td>
                                    <td>{{$row['CPAct']}}          </td>
                                    <td>{{$row['CiNum']}}          </td>
                                    <td>{{$row['addtime']}}          </td>
                                    <td>{{$row['Ctype']}}          </td>
                                    <td>{{$row['Cver']}}           </td>
                                    <td>{{$row['IMEI']}}           </td>
                                    <td>{{$row['BBCon']}}          </td>
                                    <td>{{$row['Gsen']}}           </td>
                                    <td>{{$row['BTMac']}}          </td>
                                    <td>{{$row['BTkey']}}          </td>
                                    <td>{{$row['GPSSt']}}          </td>
                                    <td>{{$row['GPRSSt']}}         </td>
                                    <td>{{$row['Btype']}}          </td>
                                    <td>{{$row['Bver']}}           </td>
                                    <td>{{$row['BTStat']}}         </td>
                                    <td>{{$row['I2C']}}            </td>
                                    <td>{{$row['OWCin']}}          </td>
                                    <td>{{$row['OWCout']}}         </td>
                                    <td>{{$row['Lock']}}           </td>
                                    <td>{{$row['Volt']/10}}v  </td>
                                    <td>{{$row['Online']}}         </td>
                                    <td>{{$row['Odomte']}}         </td>
                                    <td>{{$row['SPEED']}}          </td>
                                    <td>{{$row['DGPS']}}           </td>
                                    <td>{{$row['DGSM']}}           </td>
                                    <td>{{$row['imsi']}}           </td>
                                    <td>{{$row['PowGate']}} </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="pager">
                        <?php echo $page_nav ?>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @include('admin.common_channel_customer_scenejs')
    @include('admin.common_brand_ebikejs')

    <script>

    </script>

@endsection