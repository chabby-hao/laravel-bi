@extends('admin.layout')
@section('content')
    <div class="container-fluid">

        <div class="row-fluid margintop">
            <div class="span12">
                <div class="widget-box">
                    <div class="widget-title"><span class="icon"> <i class="icon-search"></i> </span>
                        <h5>筛选查询</h5>
                    </div>
                    <div class="widget-content">
                        <form id="myform" class="form-search">
                            <div class="control-group">
                                <div class="inline-block">
                                    <label>输入搜索：</label>
                                </div>
                                <div class="inline-block">
                                    <input type="text" id="id" name="id" value="{{Request::input('id')}}" placeholder="设备号/IMEI/IMSI">
                                    <input type="text" id="name" name="name" placeholder="设备名称">
                                </div>

                                <div class="inline-block">
                                    <input type="button" id="mysubmit" class="btn btn-info" value="查询">
                                </div>
                            </div>
                            <div class="control-group">
                                <div class="inline-block">
                                    <label>最近查询：</label>
                                </div>
                                <div class="inline-block">
                                    <a class="btn btn-default" href="">123456789012</a>
                                    <a class="btn" href="">123456789012</a>
                                    <a class="btn" href="">123456789012</a>
                                    <a class="btn" href="">123456789012</a>
                                    <a class="btn" href="">123456789012</a>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>

                <div id="box">

                </div>

                <script id="template" type="x-tmpl-mustache">

                <div class="widget-box">
                    <div class="widget-title"><span class="icon"> <i class="icon-align-justify"></i> </span>
                        <h5>基本信息</h5>
                    </div>
                    <div class="widget-content">
                        <table class="table table-bordered data-table">
                            <thead>
                            <tr>
                                <th>设备号</th>
                                <th>设备型号</th>
                                <th>IMEI</th>
                                <th>IMSI</th>
                                <th>固件版本</th>
                                <th>协议版本</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="gradeX">
                                <td><%udid%></td>
                                <td><%ebikeTypeName%></td>
                                <td><%imei%></td>
                                <td><%imsi%></td>
                                <td><%romVersion%></td>
                                <td><%ver%></td>
                            </tr>
                            </tbody>
                        </table>

                        <%#shipOrder%>
                        <table class="table table-bordered data-table">
                            <thead>
                            <tr>
                                <th>订单号</th>
                                <th>订单日期</th>
                                <th>出货号</th>
                                <th>出货日期</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="gradeX">
                                <td><%order_no%></td>
                                <td><%order_created_at%></td>
                                <td><%ship_no%></td>
                                <td><%actuall_date%></td>
                            </tr>
                            </tbody>
                        </table>
                        <%/shipOrder%>

                        <table class="table table-bordered data-table">
                            <thead>
                            <tr>
                                <th>激活日期</th>
                                <th>设备名称</th>
                                <th>管理员</th>
                                <th>关注者</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="gradeX">
                                <td><%activeAt%></td>
                                <td><%name%></td>
                                <td><%master.phone%></td>
                                <td>
                                    <%#followers%>
                                        <%phone%>
                                    <%/followers%>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="widget-box">
                    <div class="widget-title"><span class="icon"> <i class="icon-align-justify"></i> </span>
                        <h5>设备信息</h5>
                    </div>
                    <div class="widget-content">
                        <table class="table table-bordered data-table">
                            <thead>
                            <tr>
                                <th>设备状态</th>
                                <th>GSM信号</th>
                                <th>卫星数量</th>
                                <th>最近一次通信时间</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="gradeX">
                                <td><%isOnlineTrans%></td>
                                <td>-<%gsm%>db</td>
                                <td><%gpsSatCount%></td>
                                <td><%lastGps%></td>
                            </tr>
                            </tbody>
                        </table>

                        <table class="table table-bordered data-table">
                            <thead>
                            <tr>
                                <th>上报时间</th>
                                <th>定位类型</th>
                                <th>经纬度</th>
                                <th>详细位置</th>
                                <th>标签</th>
                                <th>备用电池</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="gradeX">
                                <td><%lastLocation.dateTime%></td>
                                <td><%lastLocation.type%></td>
                                <td><%#lastLocation.lng%>
                                    <%lastLocation.lng%>,<%lastLocation.lat%>
                                    <%/lastLocation.lng%></td>
                                <td><%lastLocation.address%></td>
                                <td><%lastLocation.landmark%></td>
                                <td><%chipPower%></td>
                                <td><a class="text-success" href="<%locationUrl%>">历史定位</a></td>
                            </tr>
                            </tbody>
                        </table>

                    </div>
                </div>

                <div class="widget-box">
                    <div class="widget-title"><span class="icon"> <i class="icon-align-justify"></i> </span>
                        <h5>车辆信息</h5>
                    </div>
                    <div class="widget-content">
                        <table class="table table-bordered data-table">
                            <thead>
                            <tr>
                                <th>渠道</th>
                                <th>品牌</th>
                                <th>车型</th>
                                <th>车架号</th>
                                <th>电池规格</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="gradeX">
                                <td><%channelName%></td>
                                <td><%brandName%></td>
                                <td><%ebikeTypeName%></td>
                                <td><%chassis%></td>
                                <td><%batterySpecification%></td>
                            </tr>
                            </tbody>
                        </table>

                        <table class="table table-bordered data-table">
                            <thead>
                            <tr>
                                <th>上报时间</th>
                                <th>电门状态</th>
                                <th>锁车状态</th>
                                <th>电瓶电压</th>
                                <th>剩余电量</th>
                                <th>备用电池</th>
                                <th>电瓶是否在位</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="gradeX">
                                <td><%lastContact%></td>
                                <td><%turnonTrans%></td>
                                <td><%isLockTrans%></td>
                                <td><%voltage%></td>
                                <td><%battery%></td>
                                <td><%chipPower%></td>
                                <td><%chargeTrans%></td>
                                <td>
                                    {{--<a class="text-success">电门日志</a>--}}
                                    <a href='<%lockLogUrl%>' class="text-success">锁车日志</a>
                                    <a href='<%historyStateUrl%>' class="text-success">历史状态</a>
                                </td>
                            </tr>
                            </tbody>
                        </table>


                        <table class="table table-bordered data-table">
                            <thead>
                            <tr>
                                <th>控制器状态</th>
                                <th>转把状态</th>
                                <th>电机状态</th>
                                <th>电瓶状态</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="gradeX">
                                <td><%faultControl%></td>
                                <td><%faultSwitch%></td>
                                <td><%faultMotor%></td>
                                <td><%faultCharge%></td>
                            </tr>
                            </tbody>
                        </table>

                    </div>
                </div>


                <div class="widget-box">
                    <div class="widget-title"><span class="icon"> <i class="icon-align-justify"></i> </span>
                        <h5>用车信息</h5>
                    </div>
                    <div class="widget-content">
                        <table class="table table-bordered data-table">
                            <thead>
                            <tr>
                                <th>预计续航</th>
                                <th>行驶里程</th>
                                <th>骑行次数</th>
                                <th>充电次数</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="gradeX">
                                <td><%expectMile%></td>
                                <td><%totalMiles%></td>
                                <td><%ridingTimes%></td>
                                <td><%chargingTimes%></td>
                            </tr>
                            </tbody>
                        </table>

                        <table class="table table-bordered data-table">
                            <thead>
                            <tr>
                                <th>最近用车</th>
                                {{--<th>起点</th>
                                <th>终点</th>--}}
                                <th>行驶里程</th>
                                <th>骑行时长</th>
                                <th>平均速度</th>
                                <th>使用电量</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="gradeX">
                                <td><%lastTrip.dateTime%></td>
                                {{--<td><%lastTrip.addressBegin%></td>
                                <td><%lastTrip.addressEnd%></td>--}}
                                <td><%lastTrip.mile%>公里</td>
                                <td><%lastTrip.duration%>分钟</td>
                                <td><%lastTrip.speed%>km/h</td>
                                <td><%lastTrip.energy%>kw/h</td>
                                <td>
                                    <a class="text-success">历史行程</a>
                                </td>
                            </tr>
                            </tbody>
                        </table>

                    </div>
                </div>







                </script>


            </div>

        </div>

    </div>

    <script>

        $(function () {

            var template = $('#template').html();
            Mustache.parse(template);   // optional, speeds up future uses

            var myform = $("#myform");

            $("#mysubmit").click(function () {
                myform.submit();
            });

            myform.ajaxForm({
                dataType: 'json',
                //beforeSubmit : test,//ajax动画加载
                success: function (data) {
                    if (ajax_check_res(data)) {

                        var target = $("#box");
                        var rendered = Mustache.render(template, data);
                        target.html(rendered);
                        //myalert('保存成功');
                    }
                }
            });


            //设备列表跳转过来的，直接自动查询
            if($("#id").val()){
                myform.submit();
            }

        })


    </script>


@endsection
