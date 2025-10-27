<?php

namespace App\Http\Controllers\Gateway;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Models\AdminNotification;
use App\Models\Cart;
use App\Models\Deposit;
use App\Models\GatewayCurrency;
use App\Models\PickedTicket;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function deposit()
    {
        $gatewayCurrency = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })->with('method')->orderby('name')->get();
        $deposits  = auth()->user()->deposits()->searchable(['trx'])->with(['gateway'])->orderBy('id', 'desc')->paginate(getPaginate());
        $pageTitle = 'Payment';

        return view('Template::user.payment.deposit', compact('gatewayCurrency', 'deposits', 'pageTitle'));
    }

    public function depositInsert(Request $request)
    {
        $request->validate([
            'amount'   => 'required|numeric|gt:0',
            'gateway'  => 'required',
            'currency' => 'nullable|string',
        ]);

        $request['currency'] = $request['currency'] ?? 'idr';

        // if (getAmount($request->amount) != getAmount(session('cartItemPriceTotal'))) {
        //     $notify[] = ['error', 'Payment amount mismatch'];
        //     return back()->withNotify($notify);
        // }

        $user = auth()->user();
        $gate = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })->where('method_code', $request->gateway)->where('currency', $request->currency)->first();
        if (!$gate) {
            $notify[] = ['error', 'Invalid gateway'];
            return back()->withNotify($notify);
        }

        if ($gate->min_amount > $request->amount || $gate->max_amount < $request->amount) {
            $notify[] = ['error', 'Please follow deposit limit'];
            return back()->withNotify($notify);
        }

        $charge      = $gate->fixed_charge + ($request->amount * $gate->percent_charge / 100);
        $payable     = $request->amount + $charge;
        $finalAmount = $payable * $gate->rate;

        $data                  = new Deposit();
        $data->user_id         = $user->id;
        $data->method_code     = $gate->method_code;
        $data->method_currency = strtoupper($gate->currency);
        $data->amount          = $request->amount;
        $data->charge          = $charge;
        $data->rate            = $gate->rate;
        $data->final_amount    = $finalAmount;
        $data->btc_amount      = 0;
        $data->btc_wallet      = "";
        $data->trx             = 'DEPOSIT-' . getTrx();
        $data->success_url     = urlPath('user.deposit.index');
        $data->failed_url      = urlPath('user.deposit.index');
        $data->save();
        session()->put('Track', $data->trx);
        return to_route('user.deposit.confirm');
    }

    public function depositConfirm()
    {
        $track   = session()->get('Track');
        $deposit = Deposit::where('trx', $track)->where('status', Status::PAYMENT_INITIATE)->orderBy('id', 'DESC')->with('gateway')->firstOrFail();
        if ($deposit->method_code >= 1000 || $deposit->method_code === 200) {
            return to_route('user.deposit.manual.confirm');
        }

        $dirName = $deposit->gateway->alias;
        $new     = __NAMESPACE__ . '\\' . $dirName . '\\ProcessController';

        $data = $new::process($deposit);
        $data = json_decode($data);

        if (isset($data->error)) {
            $notify[] = ['error', $data->message];
            return back()->withNotify($notify);
        }
        if (isset($data->redirect)) {
            return redirect($data->redirect_url);
        }

        // for Stripe V3
        if (@$data->session) {
            $deposit->btc_wallet = $data->session->id;
            $deposit->save();
        }

        $pageTitle = 'Payment Confirm';
        return view("Template::$data->view", compact('data', 'pageTitle', 'deposit'));
    }

    public function midtransDepositUpdate(Request $request)
    {
        $orderId = $request->order_id;
        $status  = $request->transaction_status;
        $type    = $request->payment_type;

        $deposit = Deposit::where('trx', $orderId)->first();

        if (!$deposit) {
            return response()->json(['error' => 'Deposit not found'], 404);
        }

        // Jika status berhasil dari Midtrans
        if (in_array($status, ['capture', 'settlement'])) {
            DB::beginTransaction();
            try {
                self::userDataUpdate($deposit);
                DB::commit();
                return response()->json(['success' => true, 'message' => 'Payment success']);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        // Jika pending / gagal
        $deposit->status = Status::PAYMENT_PENDING;
        $deposit->save();

        return response()->json(['success' => false, 'message' => 'Payment pending']);
    }

    public static function userDataUpdate($deposit, $isManual = null)
    {
        if ($deposit->status == Status::PAYMENT_INITIATE || $deposit->status == Status::PAYMENT_PENDING) {
            $deposit->status = Status::PAYMENT_SUCCESS;
            $deposit->save();

            $user = User::find($deposit->user_id);
            $user->balance += $deposit->amount;
            $user->save();

            $methodName = $deposit->methodName();

            $transaction               = new Transaction();
            $transaction->user_id      = $deposit->user_id;
            $transaction->amount       = $deposit->amount;
            $transaction->post_balance = $user->balance;
            $transaction->charge       = $deposit->charge;
            $transaction->trx_type     = '+';
            $transaction->details      = 'Payment Via ' . $methodName;
            $transaction->trx          = $deposit->trx;
            $transaction->remark       = 'payment';
            $transaction->save();

            // if (!$isManual) {
            //     $cartItems = Cart::where('user_id', $user->id)->get();

            //     foreach ($cartItems as $cartItem) {
            //         $pickedTicket                  = new PickedTicket();
            //         $pickedTicket->user_id         = $cartItem->user_id;
            //         $pickedTicket->lottery_id      = $cartItem->lottery_id;
            //         $pickedTicket->quantity        = $cartItem->quantity;
            //         $pickedTicket->choosen_tickets = $cartItem->choosen_tickets;
            //         $pickedTicket->price           = $cartItem->lottery->price * $pickedTicket->quantity;
            //         $pickedTicket->status          = Status::PAYMENT_SUCCESS;
            //         $pickedTicket->save();

            //         $cartItem->lottery->num_of_available_tickets -= $cartItem->quantity;
            //         $cartItem->lottery->save();

            //         notify($user, 'LOTTERY_BUY', [
            //             'method_name'     => $methodName,
            //             'lottery_name'    => $cartItem->lottery->name,
            //             'choosen_tickets' => implode(", ", $cartItem->choosen_tickets),
            //         ]);

            //         $cartItem->delete();
            //     }
            // }

            // if ($isManual) {
            //     // $pickedTickets = PickedTicket::where('deposit_id', $deposit->id)->get();
            //     // foreach ($pickedTickets as $pickedTicket) {
            //     //     $pickedTicket->status = Status::PAYMENT_SUCCESS;
            //     //     $pickedTicket->save();

            //     //     notify($user, 'LOTTERY_BUY', [
            //     //         'method_name'     => $methodName,
            //     //         'lottery_name'    => $pickedTicket->lottery->name,
            //     //         'choosen_tickets' => implode(", ", $pickedTicket->choosen_tickets),
            //     //     ]);
            //     // }
            // }

            // $user->balance -= $deposit->amount;
            // $user->save();

            // $transaction               = new Transaction();
            // $transaction->user_id      = $deposit->user_id;
            // $transaction->amount       = $deposit->amount;
            // $transaction->post_balance = $user->balance;
            // $transaction->trx_type     = '-';
            // $transaction->details      = 'Payment Via ' . $methodName;
            // $transaction->trx          = $deposit->trx;
            // $transaction->remark       = 'payment';
            // $transaction->save();

            if (!$isManual) {
                $adminNotification            = new AdminNotification();
                $adminNotification->user_id   = $user->id;
                $adminNotification->title     = 'Payment successful via ' . $methodName;
                $adminNotification->click_url = urlPath('admin.deposit.successful');
                $adminNotification->save();
            }

            notify($user, $isManual ? 'PAYMENT_APPROVE' : 'PAYMENT_COMPLETE', [
                'method_name'     => $methodName,
                'method_currency' => $deposit->method_currency,
                'method_amount'   => showAmount($deposit->final_amount, currencyFormat: false),
                'amount'          => showAmount($deposit->amount, currencyFormat: false),
                'charge'          => showAmount($deposit->charge, currencyFormat: false),
                'rate'            => showAmount($deposit->rate, currencyFormat: false),
                'trx'             => $deposit->trx,
                'post_balance'    => showAmount($user->balance),
            ]);
        }
    }

    public function manualDepositConfirm()
    {
        $track = session()->get('Track');
        $data  = Deposit::with('gateway')->where('status', Status::PAYMENT_INITIATE)->where('trx', $track)->first();
        abort_if(!$data, 404);
        if ($data->method_code == 1000) {
            $pageTitle = 'Confirm Payment';
            $method    = $data->gatewayCurrency();
            $gateway   = $method->method;
            return view('Template::user.payment.manual', compact('data', 'pageTitle', 'method', 'gateway'));
        }
        if ($data->method_code == 200) {
            $gatewayParams = json_decode($data->gateway->gateway_parameters, true);
            $serverKey = $gatewayParams['secret_key'] ?? [];
            $clientKey = $gatewayParams['client_key'] ?? [];
            $midtransConfig = [
                'is_production' => env('MIDTRANS_IS_PRODUCTION'),
                'client_key'    => $clientKey['value'],
                'server_key'    => $serverKey['value'],
            ];
            // Setup Midtrans
            \Midtrans\Config::$serverKey = $midtransConfig['server_key'];
            \Midtrans\Config::$clientKey = $midtransConfig['client_key'];
            \Midtrans\Config::$isProduction = $midtransConfig['is_production'];
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;
            $orderId = $data->trx;
            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $data->final_amount,
                ],
                'customer_details' => [
                    'first_name' => auth()->user()->username ?? 'Guest',
                    'email' => auth()->user()->email ?? 'guest@example.com',
                ],
                'callbacks' => [
                    'finish' => route('user.deposit.index'),
                ],
            ];


            $snapToken = \Midtrans\Snap::getSnapToken($params);
            $pageTitle = 'Pay via Midtrans';
            $method    = $data->gatewayCurrency();
            $gateway   = $method->method;
            return view('Template::user.payment.midtrans', compact('data', 'pageTitle', 'method', 'gateway',  'midtransConfig', 'snapToken'));
        }
        abort(404);
    }

    public function manualDepositUpdate(Request $request)
    {
        $track = session()->get('Track');
        $data  = Deposit::with('gateway')->where('status', Status::PAYMENT_INITIATE)->where('trx', $track)->first();
        abort_if(!$data, 404);
        $gatewayCurrency = $data->gatewayCurrency();
        $gateway         = $gatewayCurrency->method;
        $formData        = $gateway->form->form_data;

        $formProcessor  = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);
        $request->validate($validationRule);
        $userData = $formProcessor->processFormData($request, $formData);

        $data->detail = $userData;
        $data->status = Status::PAYMENT_PENDING;
        $data->save();

        $adminNotification            = new AdminNotification();
        $adminNotification->user_id   = $data->user->id;
        $adminNotification->title     = 'Payment request from ' . $data->user->username;
        $adminNotification->click_url = urlPath('admin.deposit.details', $data->id);
        $adminNotification->save();

        // $cartItems = Cart::where('user_id', auth()->id())->get();

        // foreach ($cartItems as $cartItem) {
        //     $pickedTicket                  = new PickedTicket();
        //     $pickedTicket->user_id         = $cartItem->user_id;
        //     $pickedTicket->lottery_id      = $cartItem->lottery_id;
        //     $pickedTicket->quantity        = $cartItem->quantity;
        //     $pickedTicket->choosen_tickets = $cartItem->choosen_tickets;
        //     $pickedTicket->price           = $cartItem->lottery->price * $pickedTicket->quantity;
        //     $pickedTicket->deposit_id      = $data->id;
        //     $pickedTicket->status          = Status::PAYMENT_PENDING;
        //     $pickedTicket->save();

        //     $cartItem->lottery->num_of_available_tickets -= $cartItem->quantity;
        //     $cartItem->lottery->save();

        //     $cartItem->delete();
        // }

        notify($data->user, 'PAYMENT_REQUEST', [
            'method_name'     => $data->gatewayCurrency()->name,
            'method_currency' => $data->method_currency,
            'method_amount'   => showAmount($data->final_amount, currencyFormat: false),
            'amount'          => showAmount($data->amount, currencyFormat: false),
            'charge'          => showAmount($data->charge, currencyFormat: false),
            'rate'            => showAmount($data->rate, currencyFormat: false),
            'trx'             => $data->trx,
        ]);

        $notify[] = ['success', 'You have payment request has been taken'];
        return to_route('user.deposit.index')->withNotify($notify);
    }
}
