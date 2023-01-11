@extends("layouts.tasks")
@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"></h3>

                    <div class="card-tools">
                        <a class="btn btn-app" href="{{route('admin.task.create')}}">
                            <i class="fas fa-plus"></i> New Task
                        </a>
                        <a class="btn btn-app" href="{{route('admin.tasks.export')}}">
                            <i class="fas fa-download"></i> Export
                        </a>
                    </div>
                    
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title" style="float: left;">List Of Task</h3>
            
                            <div class="card-tools" style="float: right;">
                                <div class="" style="width: 150px;">
                                    {!! Form::open([
                                        'id' => 'totem__search__form',
                                        'url' => Request::fullUrl(),
                                        'method' => 'GET',
                                        'class' => 'input-group input-group-sm'
                                    ]) !!}
                                    {!! Form::text('q', request('q'), ['class' => 'form-control float-right', 'placeholder' => 'Search...']) !!}
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-default">
                                        <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                    {!! Form::close() !!}
                                </div>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>{!! Html::columnSort('Description', 'description') !!}</th>
                                        <th>{!! Html::columnSort('Average Runtime', 'average_runtime') !!}</th>
                                        <th>{!! Html::columnSort('Last Run', 'last_ran_at') !!}</th>
                                        <th>Next Run</th>
                                        <th class="uk-text-center">Execute</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($tasks as $task)
                                        <tr is="task-row"
                                            :data-task="{{$task}}"
                                            showHref="{{route('admin.task.view', $task)}}"
                                            executeHref="{{route('admin.task.execute', $task)}}">
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="uk-text-center" colspan="5">
                                                <img class="uk-svg" width="50" height="50" src="{{asset('/vendor/totem/img/funnel.svg')}}">
                                                <p>No Tasks Found.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer clearfix">
                    {{$tasks->links('totem::partials.pagination', ['params' => '&' . http_build_query(array_filter(request()->except('page')))])}}
                </div>
            </div>
            
        <!-- /.card -->
        </div>
    </div>
@stop
