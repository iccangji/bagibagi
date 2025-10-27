@extends($activeTemplate . 'layouts.master')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card custom--card">
                    <div class="card-header card--header">
                        <h5 class="card-title">{{ __($pageTitle) }}</h5>
                    </div>
                    <div class="card-body  ">
                        <form action="{{ route('user.deposit.manual.update') }}" method="POST" class="disableSubmission"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="alert alert--base">
                                        <p class="mb-0"><i class="las la-info-circle"></i> @lang('You are requesting')
                                            <b>{{ showAmount($data['amount']) }}</b> @lang('to deposit.') @lang('Please pay')
                                            <b>{{ 'Rp. ' . number_format($data['final_amount'], 0, '.', '.') }}
                                            </b> @lang('for successful payment.')
                                        </p>
                                    </div>

                                    <div class="mb-3 manual-deposit">@php echo  $data->gateway->description @endphp</div>

                                </div>

                                <x-viser-form identifier="id" identifierValue="{{ $gateway->form_id }}" />

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <button type="submit" class="btn btn--base w-100"
                                            id="pay-button">@lang('Pay Now')</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .manual-deposit>div {
            background: transparent !important;
        }
    </style>
@endpush

@push('script')
    <script src="https://app{{ $midtransConfig['is_production'] ? '' : '.sandbox' }}.midtrans.com/snap/snap.js"
        data-client-key="{{ $midtransConfig['client_key'] }}"></script>

    <script type="text/javascript">
        function handlePaymentResult(result) {
            console.log(result);
            $.ajax({
                url: "{{ route('user.deposit.midtrans.update') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    order_id: result.order_id,
                    transaction_status: result.transaction_status,
                    payment_type: result.payment_type
                },
                success: function(res) {
                    window.location.href = "{{ route('user.deposit.index') }}";
                },
                error: function(err) {
                    console.error(err);
                    alert('Terjadi kesalahan pada callback pembayaran.');
                }
            });
        }

        $(document).ready(function() {
            $('#pay-button').on('click', function(e) {
                e.preventDefault();
                snap.pay('{{ $snapToken }}', {
                    onSuccess: function(result) {
                        handlePaymentResult(result);
                    },
                    onPending: function(result) {
                        handlePaymentResult(result);
                    },
                    onError: function(result) {
                        handlePaymentResult(result);
                    },
                    onClose: function() {
                        alert('Anda menutup popup tanpa menyelesaikan pembayaran.');
                    }
                });
            });
        });
    </script>
@endpush
