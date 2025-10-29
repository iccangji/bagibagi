@extends($activeTemplate . 'layouts.master')
@section('content')
    <section>
        <div class="container">
            <h3 class="mb-4">@lang('Submitted Tasks')</h3>
            <div class="row gy-2 justify-content-center">
                @forelse ($userTasks as $userTask)
                    <div class="col-md-12 mb-4">
                        <div class="card bg-transparent">
                            <div class="card-body">
                                <h4>{{ $userTask->task->title }}</h4>
                                <p><a class="mb-2"
                                        href="{{ route('lottery.details', [$userTask->task->lottery->id, $userTask->task->lottery->slug]) }}"><strong>@lang('Raffles')</strong>
                                        {{ $userTask->task->lottery->name }}</a>
                                </p>
                                <p><strong>@lang('Points')</strong> {{ $userTask->task->reward_points }}</p>
                                <p class="my-4">{{ $userTask->task->description }}</p>
                                <div class="flex">
                                    <a href="{{ route('user.tasks.show', $userTask->task->id) }}">
                                        <p>
                                            @if ($userTask->status === 'pending')
                                                <button class="p-2 rounded bg-warning text-dark">@lang('Waiting for verification')</button>
                                            @elseif ($userTask->status === 'verified')
                                                <button class="p-2 rounded bg-success text-light">@lang('Approved')</button>
                                            @elseif ($userTask->status === 'rejected')
                                                <button class="p-2 rounded bg-danger text-light">@lang('Rejected')</button>
                                            @endif
                                        </p>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-message-area">
                        <img src="{{ getImage($activeTemplateTrue . 'images/empty.png') }}" alt="img">
                        <h6 class="text--danger">@lang('No tasks found yet!')</h6>
                    </div>
                @endforelse
                @if ($userTasks->hasPages())
                    {{ paginateLinks($userTasks) }}
                @endif
            </div>
        </div>
    </section>
@endsection
