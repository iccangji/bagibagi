@extends($activeTemplate . 'layouts.master')
@section('content')
    <section>
        <div class="container">
            <h4 class="mb-4">Tasks</h4>
            <div class="row gy-2 justify-content-center">
                @forelse ($tasks as $task)
                    <div class="col-md-12 mb-4">
                        <div class="card bg-transparent">
                            <div class="card-body">
                                <h4>{{ $task->title }}</h4>
                                <p><a class="mb-2"
                                        href="{{ route('lottery.details', [$task->lottery->id, $task->lottery->slug]) }}"><strong>@lang('Raffles')</strong>
                                        {{ $task->lottery->name }}</a>
                                </p>
                                <p><strong>@lang('Points')</strong> {{ $task->reward_points }}</p>
                                <p class="my-4">{{ $task->description }}</p>
                                <a href="{{ route('user.tasks.show', $task) }}" class="btn cmn--btn">@lang('Submit')</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-message-area">
                        <img src="{{ getImage($activeTemplateTrue . 'images/empty.png') }}" alt="img">
                        <h6 class="text--danger">@lang('No tasks found yet!')</h6>
                    </div>
                @endforelse
                @if ($tasks->hasPages())
                    {{ paginateLinks($tasks) }}
                @endif
            </div>
        </div>
    </section>
@endsection
