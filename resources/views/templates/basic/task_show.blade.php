@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <section class="py-120">
        <div class="container">
            <h2>{{ $task->title }}</h2>
            <p>{{ $task->description }}</p>
            <p><strong>@lang('Hadiah Poin:')</strong> {{ $task->reward_points }}</p>
            @if (!$submitted)
                <form action="{{ route('tasks.submit', $task) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label>@lang('Proof of work (screenshot/foto/pdf)')</label>
                        <input type="file" name="proof" class="form-control" required>
                    </div>

                    <button type="submit" class="btn cmn--btn">@lang('Send to verify')</button>
                </form>
            @else
                {{-- Sudah mengerjakan --}}
                <div class="mt-4">
                    <h5>Status Pengerjaan</h5>
                    @if ($submitted->status === 'pending')
                        <span class="badge bg-warning text-dark">@lang('Waiting for verification')</span>
                    @elseif($submitted->status === 'verified')
                        <span class="badge bg-success">@lang('Approved')</span>
                    @elseif($submitted->status === 'rejected')
                        <span class="badge bg-danger">@lang('Rejected')</span>
                    @endif

                    @if ($submitted->proof)
                        <div class="mt-3">
                            <p>@lang('Uploaded file:')</p>
                            <a href="{{ asset('assets/images/task/proofs/' . $submitted->proof) }}" target="_blank">
                                <img src="{{ asset('assets/images/task/proofs/' . $submitted->proof) }}"
                                    alt="Bukti Pengerjaan" class="img-fluid rounded border" style="max-width: 400px;">
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </section>
@endsection
