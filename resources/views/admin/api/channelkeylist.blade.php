@extends('admin.layout')
@section('content')
    <div class="container-fluid">
        <hr>

        <div class="row-fluid">
            <span class="pull-right"><a href="javascript:;" class="btn btn-warning refresh">刷新配置</a></span>
            <span class="pull-right marginright"><a href="<?php echo \Illuminate\Support\Facades\URL::action('Admin\ApiController@channelKeyAdd'); ?>" class="btn btn-success">添加新渠道秘钥</a></span>
        </div>

        <div class="row-fluid">
            <div class="span12">
                <div class="widget-box">
                    <div class="widget-title"><span class="icon"><i class="icon-th"></i></span>
                        <h5>列表</h5>
                    </div>
                    <div class="widget-content nopadding">
                        <table class="table table-bordered data-table">
                            <thead>
                            <tr>
                                <th>渠道id</th>
                                <th>渠道名</th>
                                <th>cid</th>
                                <th>secret</th>
                                <th>推送url</th>
                                <th>推送类型</th>
                                <th>创建时间</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php /** @var \App\Models\BiChannelSecret $data */ ?>
                                @foreach($datas as $data)
                                    <tr class="gradeX">
                                        <td>{{$data->channel_id}}</td>
                                        <td>{{\App\Models\BiChannel::getChannelMap(true)[$data->channel_id]}}</td>
                                        <td>{{$data->channel_name}}</td>
                                        <td>{{$data->secret}}</td>
                                        <td>{{$data->push_url}}</td>
                                        <td>{{$data->push_types}}</td>
                                        <td>{{$data->created_at}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="pager">
                        <?php echo $page_nav; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(function(){
            $(".refresh").click(function () {
                $.ajax({
                    url:'{{URL::action('Admin\ApiController@refreshChannelConfig')}}',
                    success:function (res) {
                        if(ajax_check_res(res)){

                        }
                    }
                })
            })
        })
    </script>
@endsection