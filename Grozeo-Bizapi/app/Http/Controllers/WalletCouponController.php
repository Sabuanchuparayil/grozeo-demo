<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Responses\SuccessWithData;
use App\Http\Repositories\Coupon\Coupon;
use App\Http\Requests\Cart\WalletCouponRequest;

class WalletCouponController extends Controller
{
    public function get(WalletCouponRequest $request)
    {
        $couponData = $request->filled('coupon_code')
            ? Coupon::coupon($request->validated())
            : (new Coupon)->getWalletOnlyDetails($request->validated());

        return new SuccessWithData([
            'labels' => $couponData['style'],
            'net_amount_payable' => $couponData['net_amount_payable'],
        ]);
    }

    public function couponRemove(WalletCouponRequest $request)
    {
        if ($request->filled('coupon_code')) {
            $couponData = (new Coupon)->removeAppliedCoupon($request->validated());
        }      

        return new SuccessWithData([
            'labels' => @$couponData['style'],
            'net_amount_payable' => @$couponData['net_amount_payable'],
        ]);
    }
}
