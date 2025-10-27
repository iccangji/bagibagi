@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10 bg--transparent shadow-none">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two bg-white">
                            <thead>
                                <tr>
                                    <th>@lang('User')</th>
                                    <th>@lang('Task')</th>
                                    <th>@lang('Proof')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tasks as $ut)
                                    <tr>
                                        <td>{{ $ut->user->username }}</td>
                                        <td>{{ $ut->task->title }}</td>
                                        <td>
                                            @if ($ut->proof)
                                                <a href="{{ asset('assets/images/task/proofs/' . $ut->proof) }}"
                                                    target="_blank">Lihat
                                                    Bukti</a>
                                            @endif
                                        </td>
                                        <td>{{ ucfirst($ut->status) }}</td>
                                        <td>
                                            <form action="{{ route('admin.tasks.submission.verify', $ut) }}" method="POST">
                                                @csrf
                                                <button type="submit" name="action" value="verified"
                                                    class="btn btn-success btn-sm">Approve</button>
                                                <button type="submit" name="action" value="rejected"
                                                    class="btn btn-danger btn-sm">Reject</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
