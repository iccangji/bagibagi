@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="container mb-4">
        <div class="row justify-content-center">
            <div class="col-12">
                <form action="{{ route('user.deposit.insert') }}" method="post" class="deposit-form">
                    @csrf
                    <input type="hidden" name="currency">
                    <div class="gateway-card card custom--card">
                        <div class="card-body">
                            <h5 class="card-title">@lang('Payment Method')</h5>
                            <div class="row justify-content-center gy-sm-4 gy-3">
                                <div class="col-xl-6">
                                    <div class="payment-system-list is-scrollable gateway-option-list">
                                        @foreach ($gatewayCurrency as $data)
                                            <label for="{{ titleToKey($data->name) }}"
                                                class="payment-item @if ($loop->index > 4) d-none @endif gateway-option">
                                                <div class="payment-item__info">
                                                    <span class="payment-item__check"></span>
                                                    <span class="payment-item__name">{{ __($data->name) }}</span>
                                                </div>
                                                <div class="payment-item__thumb">
                                                    <img class="payment-item__thumb-img"
                                                        src="{{ getImage(getFilePath('gateway') . '/' . $data->method->image) }}"
                                                        alt="@lang('payment-thumb')">
                                                </div>
                                                <input class="payment-item__radio gateway-input"
                                                    id="{{ titleToKey($data->name) }}" hidden
                                                    data-gateway='@json($data)' type="radio"
                                                    name="gateway" value="{{ $data->method_code }}"
                                                    @checked(old('gateway', $loop->first) == $data->method_code)
                                                    data-min-amount="{{ showAmount($data->min_amount) }}"
                                                    data-max-amount="{{ showAmount($data->max_amount) }}">
                                            </label>
                                        @endforeach
                                        @if ($gatewayCurrency->count() > 4)
                                            <button type="button" class="payment-item__btn more-gateway-option">
                                                <p class="payment-item__btn-text">@lang('Show All Payment Options')</p>
                                                <span class="payment-item__btn__icon"><i
                                                        class="fas fa-chevron-down"></i></span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="payment-system-list bg-color-lg p-3">
                                        <div class="deposit-info">
                                            <div class="deposit-info__title">
                                                <p class="text mb-0">@lang('Amount')</p>
                                            </div>
                                            <div class="deposit-info__input">
                                                <div class="deposit-info__input-group input-group">
                                                    <input type="text" class="form-control form--control amount"
                                                        name="amount" autocomplete="off" value="0">
                                                    <span
                                                        class="deposit-info__input-group-text pe-2 mt-1">{{ 'Points' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="deposit-info">
                                            <div class="deposit-info__title">
                                                <p class="text has-icon"> @lang('Limit')
                                                    <span></span>
                                                </p>
                                            </div>
                                            <div class="deposit-info__input">
                                                <p class="text"><span class="gateway-limit">@lang('0.00')</span>
                                                </p>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="deposit-info">
                                            <div class="deposit-info__title">
                                                <p class="text mb-0">@lang('Price')</p>
                                            </div>
                                            <div class="deposit-info__input">
                                                <span class="deposit-info__input-group-text gateway-conversion"></span>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn--base w-100" disabled>
                                            @lang('Confirm Payment')
                                        </button>
                                        <div class="info-text pt-3">
                                            <p class="text">@lang('Ensuring your funds grow safely through our secure deposit process with world-class payment options.')</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="container">
        <form class="search-box-wrapper mb-3">
            <h4 class="search-box-wrapper-title">@lang('Payment History')</h4>
            <div class="search-box">
                <input type="search" name="search" class="form--control" value="{{ request()->search }}"
                    placeholder="@lang('Search by transactions')">
                <button type="button" class="search-box__button"><i class="fas fa-search"></i></button>
            </div>
        </form>

        <table class="table table--responsive--xl">
            <thead>
                <tr>
                    <th>@lang('Gateway | Transaction')</th>
                    <th>@lang('Initiated')</th>
                    <th>@lang('Amount')</th>
                    <th>@lang('Conversion')</th>
                    <th>@lang('Status')</th>
                    <th>@lang('Details')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deposits as $deposit)
                    <tr>
                        <td>
                            <div>
                                <span class="fw-bold">
                                    <span class="text--base">
                                        @if ($deposit->method_code < 5000)
                                            {{ __(@$deposit->gateway->name) }}
                                        @else
                                            @lang('Google Pay')
                                        @endif
                                    </span>
                                </span>
                                <br>
                                <small> {{ $deposit->trx }} </small>
                            </div>
                        </td>

                        <td>
                            <span>{{ showDateTime($deposit->created_at) }}<br>{{ diffForHumans($deposit->created_at) }}</span>
                        </td>
                        <td>
                            <div>
                                {{ showAmount($deposit->amount) }} + <span class="text--danger" data-bs-toggle="tooltip"
                                    title="@lang('Processing Charge')">{{ showAmount($deposit->charge) }} </span>
                                <br>
                                <strong data-bs-toggle="tooltip" title="@lang('Amount with charge')">
                                    {{ showAmount($deposit->amount + $deposit->charge) }}
                                </strong>
                            </div>
                        </td>
                        <td>
                            <div>
                                {{ showAmount(1) }} = {{ showAmount($deposit->rate) }}
                                {{ __($deposit->method_currency) }}
                                <br>
                                <strong>{{ showAmount($deposit->final_amount) }}
                                    {{ __($deposit->method_currency) }}</strong>
                            </div>
                        </td>
                        <td>
                            @php echo $deposit->statusBadge @endphp
                        </td>
                        @php
                            $details = [];
                            if ($deposit->method_code >= 1000 && $deposit->method_code <= 5000) {
                                foreach (@$deposit->detail ?? [] as $key => $info) {
                                    $details[] = $info;
                                    if ($info->type == 'file') {
                                        $details[$key]->value = route(
                                            'user.download.attachment',
                                            encrypt(getFilePath('verify') . '/' . $info->value),
                                        );
                                    }
                                }
                            }
                        @endphp

                        <td>
                            @if (($deposit->method_code >= 1000 && $deposit->method_code <= 5000) || $deposit->method_code === 200)
                                <a href="javascript:void(0)" class="btn btn--base btn--sm detailBtn"
                                    data-info="{{ json_encode($details) }}"
                                    @if ($deposit->status == Status::PAYMENT_REJECT) data-admin_feedback="{{ $deposit->admin_feedback }}" @endif>
                                    <i class="las la-desktop"></i>
                                </a>
                            @else
                                <button type="button" class="btn btn--success btn--sm" data-bs-toggle="tooltip"
                                    title="@lang('Automatically processed')">
                                    <i class="las la-check"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="100%" class="text-center">{{ __($emptyMessage) }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if ($deposits->hasPages())
            {{ paginateLinks($deposits) }}
        @endif
    </div>

    {{-- APPROVE MODAL --}}
    <div id="detailModal" class="modal custom--modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Details')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="list-group userData mb-2"></ul>
                    <div class="feedback"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('style')
    <style>
        .list-group-item {
            background-color: transparent;
            border: 1px solid #a7a7a747;
            color: #fff;
        }
    </style>
@endpush
@push('script')
    <script>
        "use strict";
        (function($) {

            var amount = 0;
            var gateway, minAmount, maxAmount;

            $('.amount').on('input', function(e) {
                amount = parseFloat($(this).val());
                if (!amount) {
                    amount = 0;
                }
                calculation();
            });

            $('.gateway-input').on('change', function(e) {
                gatewayChange();
            });

            function gatewayChange() {

                let gatewayElement = $('.gateway-input:checked');
                let methodCode = gatewayElement.val();

                gateway = gatewayElement.data('gateway');
                minAmount = gatewayElement.data('min-amount');
                maxAmount = gatewayElement.data('max-amount');

                $(".gateway-conversion").html(
                    `1 Points = <span class="rate">${parseFloat(gateway.rate)}</span>  <span class="method_currency">${gateway.currency}</span>`
                );

                let processingFeeInfo =
                    `0%  {{ __(gs('cur_text')) }} charge for payment gateway processing fees`
                $(".proccessing-fee-info").attr("data-bs-original-title", processingFeeInfo);
                calculation();
            }

            gatewayChange();

            $(".more-gateway-option").on("click", function(e) {
                let paymentList = $(".gateway-option-list");
                paymentList.find(".gateway-option").removeClass("d-none");
                $(this).addClass('d-none');
                paymentList.animate({
                    scrollTop: (paymentList.height() - 60)
                }, 'slow');
            });

            function calculation() {
                if (!gateway) return;
                $(".gateway-limit").text(minAmount + " - " + maxAmount);

                if (amount < Number(gateway.min_amount) || amount > Number(gateway.max_amount)) {
                    $(".deposit-form button[type=submit]").attr('disabled', true);
                } else {
                    $(".deposit-form button[type=submit]").removeAttr('disabled');
                }

                if (gateway.currency != "{{ gs('cur_text') }}" && gateway.method.crypto != 1) {
                    if (amount !== 0) {
                        $(".gateway-conversion").html(
                            `Rp. <span class="rate">${parseFloat(gateway.rate) * amount}</span>`
                        );
                    }
                } else {
                    console.log(gateway);
                    if (amount !== 0) {
                        $(".gateway-conversion").html(
                            `Rp. <span class="rate">${parseFloat(gateway.rate) * amount}</span>`
                        );
                    }
                }
            }

            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
            $('.gateway-input').change();
        })(jQuery);
    </script>

    <script>
        (function($) {
            "use strict";
            $('.detailBtn').on('click', function() {
                var modal = $('#detailModal');

                var userData = $(this).data('info');
                var html = '';
                if (userData) {
                    userData.forEach(element => {
                        if (element.type != 'file') {
                            html += `
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>${element.name}</span>
                                <span">${element.value}</span>
                            </li>`;
                        } else {
                            html += `
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>${element.name}</span>
                                <span"><a class="text--base" href="${element.value}"><i class="fa-regular fa-file"></i> @lang('Attachment')</a></span>
                            </li>`;
                        }
                    });
                }

                modal.find('.userData').html(html);

                if ($(this).data('admin_feedback') != undefined) {
                    var adminFeedback = `
                        <div class="my-3">
                            <strong>@lang('Admin Feedback')</strong>
                            <p>${$(this).data('admin_feedback')}</p>
                        </div>
                    `;
                } else {
                    var adminFeedback = '';
                }

                modal.find('.feedback').html(adminFeedback);


                modal.modal('show');
            });

            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title], [data-title], [data-bs-title]'))
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

        })(jQuery);
    </script>
@endpush
