@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            Dashboard
                        </div>
                        <div class="col-xs-9">
                            <div class="pull-right">
                                <a href="{{request()->fullUrlWithQuery(["type"=>0])}}" class="btn btn-sm btn-default">Text List</a>
                                <a href="{{request()->fullUrlWithQuery(["type"=>1])}}" class="btn btn-sm btn-default">Don't Text List</a>
                                <a href="{{request()->fullUrlWithQuery(["type"=>''])}}" class="btn btn-sm btn-default">Full List</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-7">
                            <a href="{{route('sms-schedule.create')}}" class="btn btn-default">New Schedule</a>
                        </div>
                        <div class="col-md-5">
                            <form class="form-inline pull-right">
                                <div class="form-group">
                                    <!--<label>Number</label>-->
                                    <input type="text" value="{{isset($_GET['num'])?$_GET['num']:''}}" name="num" class="form-control" placeholder="Enter Target Number">
                                </div>
                                <button type="submit" class="btn btn-danger">Search</button>
                            </form>
                        </div>
                    </div>
                    <div class="clear"><br><br></div>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th class="">Number</th>
                                <th class="">Message</th>
                                <th class="">Every</th>
                                <th class="">Started On</th>
                                <th class="">Last Sent SMS</th>
                                <th class="">Don't Text List?</th>
                                <th class="button-column"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($model) > 0)
                            @foreach($model as $i=>$m)
                            <tr>
                                <td>{{$m->target_number}}</td>
                                <td>{{$m->message}}</td>
                                <td>{{$m->schedule_id? $m->scheduleRelation->title:''}}</td>
                                <td>{{$m->send_start_date}}</td>
                                <td>{{$m->last_send_date}}</td>
                                <td>{!!$m->is_suspended?'<i class="label label-danger">Yes</i>':'No'!!}</td>
                                <td class="button-column">
                                    <a class="btn btn-xs btn-success" href="{{ route('sms-marketing.show', $m->id) }}"><i class="glyphicon glyphicon-eye-open"></i> View History</a>
                                    <a class="btn btn-xs btn-primary" href="{{ route('sms-schedule.edit', $m->id) }}"><i class="glyphicon glyphicon-edit"></i> Edit</a>
                                    <form action="{{ route('sms-schedule.destroy', $m->id) }}" method="POST" style="display: inline;" onsubmit="if (confirm('Are you sure you want to delete this item?')) {
                                                return true
                                            } else {
                                                return false
                                            }
                                            ;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <button type="submit" class="btn btn-xs btn-danger"><i class="glyphicon glyphicon-trash"></i> Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                            @else
                            <tr><td colspan="7"><span>Nothing Found</span></td></tr>
                            @endif
                        </tbody>
                    </table>
                    {{ $model->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
