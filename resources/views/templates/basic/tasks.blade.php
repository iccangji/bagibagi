@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <section class="py-120">
        <div class="container">
            <div class="row gy-2 justify-content-center">
                @forelse ($tasks as $task)
                    <div class="col-md-12 mb-4">
                        <div class="card bg-transparent">
                            <div class="card-body">
                                <h5>{{ $task->title }}</h5>
                                <p><strong>@lang('Points')</strong> {{ $task->reward_points }}</p>
                                <p class="my-4">{{ $task->description }}</p>
                                <a href="{{ route('tasks.show', $task) }}" class="btn cmn--btn">@lang('Submit')</a>
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
    @if ($sections)
        @foreach (json_decode($sections) as $sec)
            @include($activeTemplate . 'sections.' . $sec)
        @endforeach
    @endif
@endsection
