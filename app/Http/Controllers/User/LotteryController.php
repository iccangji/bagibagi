<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Lottery;
use App\Models\PickedTicket;
use App\Models\Winner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LotteryController extends Controller
{
    public function cartItems()
    {
        $cartItems = Cart::where('user_id', auth()->id())->get();

        $cartItemPrice = 0;

        foreach ($cartItems as $cartItem) {
            $cartItemPrice += $cartItem->lottery->price * $cartItem->quantity;
        }

        session()->forget('cartItemPriceTotal');
        session()->put('cartItemPriceTotal', $cartItemPrice);

        return redirect()->route('user.deposit.index');
    }

    public function purchasedLottery()
    {
        $pageTitle = "My Tickets";
        $lotteries = Lottery::pickedAndWonByUser(auth()->id())->searchable(['name'])->orderBy('id', 'desc')->paginate(getPaginate());
        return view('Template::user.lottery.purchased', compact('pageTitle', 'lotteries'));
    }

    public function purchasedLotteryDetail(Request $request, $slug)
    {
        $lottery = Lottery::where('slug', $slug)->whereHas('pickedTickets')->first();

        if (!$lottery) {
            return redirect()->back()->withErrors(['error' => 'Lottery not found.']);
        }

        $pageTitle = $lottery->name;

        $pickedTickets = PickedTicket::where('lottery_id', $lottery->id)->where('user_id', auth()->id())->get();
        $totalPrice    = @$pickedTickets->sum('price');

        $allPickedTickets = $pickedTickets->pluck('choosen_tickets')->toArray();
        $pickedTickets    = array_merge(...$allPickedTickets);
        sort($pickedTickets);

        $winningTickets    = Winner::where('lottery_id', $lottery->id)->where('user_id', auth()->id())->get();
        $allWinningTickets = [];
        foreach ($winningTickets as $pickedTicket) {
            $allWinningTickets[] = $pickedTicket->ticket_number;
        }
        sort($allWinningTickets);
        return view('Template::user.lottery.purchased_detail', compact('pageTitle', 'lottery', 'pickedTickets', 'allWinningTickets', 'totalPrice'));
    }

    public function cartCheckout()
    {
        $cartItems = Cart::where('user_id', auth()->id())->get();

        $cartItemPrice = 0;

        foreach ($cartItems as $cartItem) {
            $cartItemPrice += $cartItem->lottery->price * $cartItem->quantity;
        }

        $balance = auth()->user()->balance;

        if ($balance < $cartItemPrice) {
            return redirect()->back()->withErrors(['error' => 'Insufficient balance.']);
        } else {

            $user = auth()->user();
            $user->balance -= $cartItemPrice;
            $user->save();

            DB::table('transactions')->insert([
                'user_id'      => $user->id,
                'amount'       => $cartItemPrice,
                'post_balance' => $user->balance,
                'trx_type'     => '-',
                'trx' => 'CHECKOUT-' . strtoupper(uniqid()),
                'details'      => 'Cart Checkout',
                'remark'       => 'cart_checkout',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);


            foreach ($cartItems as $cart) {
                DB::table('picked_tickets')->insert([
                    'lottery_id'    => $cart['lottery_id'],
                    'user_id'       => auth()->id(),
                    'choosen_tickets' => json_encode($cart['choosen_tickets']),
                    'quantity'      => $cart['quantity'],
                    'price'         => $cart['lottery']['price'],
                    'status'        => 1,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            DB::table('carts')->where('user_id', $user->id)->delete();

            return redirect()->route('user.lottery.purchased')->with('success', 'Cart checkout successfully.');
        }
    }
}
