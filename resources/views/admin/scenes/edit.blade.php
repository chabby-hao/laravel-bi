@extends('admin.layout')
@section('content')
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span8">
                <div class="widget-box">
                    <div class="widget-title"><span class="icon"> <i class="icon-align-justify"></i> </span>
                        <h5>场景修改</h5>
                    </div>
                    <div class="widget-content">
                        <form id="myform" method="post" class="form-horizontal">

                            <div class="control-group">
                                <label class="control-label"><span class="text-error">*</span>场景名称 :</label>
                                <div class="controls">
                                    <input name="scenes_name" value="{{$scenes[0]->scenes_name}}" type="text" class="span11"/>
                                </div>
                            </div>



                            <div class="control-group">
                                <label class="control-label"><span class="text-error">*</span>备注 :</label>
                                <div class="controls">
                                    <input name="scenes_remark" value="{{$scenes[0]->scenes_remark}}" type="text" class="span11"/>
                                </div>
                            </div>
                           <input type="hidden" name="customer_id" value="{{$scenes[0]->customer_id}}" />
                            {{--<div class="control-group">--}}
                                {{--<label class="control-label"><span class="text-error">*</span>所属客户 :</label>--}}
                                {{--<div class="controls">--}}

                                    {{--<select name="customer_id">--}}
                                        {{--<option value="{{$scenes[0]->customer_id}}">{{$scenes[0]->customer_name}}</option>--}}

                                        {{--@foreach($customer as $key => $value)--}}
                                        {{--<option value="{{$value->id}}">{{$value->customer_name}}</option>--}}
                                        {{--@endforeach--}}

                                    {{--</select>--}}

                                {{--</div>--}}
                            {{--</div>--}}

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

