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
                                    <th>@lang('Title')</th>
                                    <th>@lang('Reward')</th>
                                    <th>@lang('Created')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tasks as $task)
                                    <tr>
                                        <td>{{ $task->title }}</td>
                                        <td>{{ $task->reward_points }} pts</td>
                                        <td>{{ $task->created_at->diffForHumans() }}</td>
                                        <td>
                                            <a href="{{ route('admin.tasks.edit', $task) }}"
                                                class="btn btn-sm btn-outline--primary editBtn cuModalBtn">Edit</a>
                                            <form action="{{ route('admin.tasks.destroy', $task) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Hapus tugas ini?')">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{ $tasks->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="showWinModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Winner Ticket Number')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                @csrf
                <div class="modal-body">
                    <ul class="ticketsInfo"></ul>
                    <div class="d-flex flex-wrap gap-3 show-result">

                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <x-search-form placeholder="Search by Name" />
    <a class="btn btn-outline--primary" href="{{ route('admin.tasks.create') }}"><i
            class="las la-plus"></i>@lang('Add New')</a>
@endpush

@push('style')
    <style>
        .ticket-number {
            background-color: #28c76f;
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
        }
    </style>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            $('.showWinBtn').on('click', function() {
                let modal = $("#showWinModal");
                let winningTickets = $(this).data('winning_tickets');
                let html = ``;
                $.each(winningTickets, function(index, winningTicket) {
                    html += `<li class="btn btn-sm btn--primary ticketBtn">${winningTicket}</li>`
                });
                modal.find('.show-result').html(html);
                modal.modal('show');
            });
        })(jQuery)
    </script>
@endpush
