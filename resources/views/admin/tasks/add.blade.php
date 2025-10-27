@extends('admin.layouts.app')
@section('panel')
    <form action="{{ route('admin.tasks.store', @$task->id) }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <div class="row">
                            <div class="col-12">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group ">
                                            <label>@lang('Title')</label>
                                            <input class="form-control" name="title" required type="text"
                                                value="{{ old('title', @$task->title) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group ">
                                            <label>@lang('Points')</label>
                                            <input class="form-control" name="reward_points" required type="number"
                                                value="{{ old('reward_points', @$task->reward_points) }}">
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Description')</label>
                                    <textarea name="description" class="form-control nicEdit" rows="10" required>
                                        {{ old('description', @$task->description) }}
                                    </textarea>
                                </div>

                            </div>
                        </div>

                        <button class="btn btn--primary h-45 w-100 mt-3" type="submit">@lang('Submit')</button>
                    </div>
                </div>
            </div>
        </div>

    </form>
@endsection

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.tasks.index') }}" />
@endpush
