@extends('admin.layout')
@section('content')
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span8">
                <div class="widget-box">
                    <div class="widget-title"><span class="icon"> <i class="icon-align-justify"></i> </span>
                        <h5>新增客户</h5>
                    </div>
                    <div class="widget-content">
                        <form id="myform" method="post" class="form-horizontal">

                            <div class="control-group">
                                <label class="control-label"><span class="text-error">*</span>客户名称 :</label>
                                <div class="controls">
                                    <input name="customer_name" value="" type="text" class="span11"/>
                                </div>
                            </div>



                            <div class="control-group">
                                <label class="control-label"><span class="text-error">*</span>备注 :</label>
                                <div class="controls">
                                    <input name="customer_remark" value="" type="text" class="span11"/>
                                </div>
                            </div>

                            <div class="control-group">
                                <label class="control-label"><span class="text-error">*</span>所属渠道 :</label>
                                <div class="controls">

                                    <select name="channel_id">
                                        @foreach($channel as $key => $value)
                                        <option value="{{$value->id}}">{{$value->channel_name}}</option>
                                        @endforeach
                                    </select>

                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="button" id="mysubmit" class="btn btn-success">提交</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


    @include('admin.common_submitjs')
@endsection

