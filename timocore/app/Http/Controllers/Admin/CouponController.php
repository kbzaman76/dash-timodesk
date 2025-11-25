<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index()
    {
        $pageTitle = 'Manage Coupons';
        $coupons   = Coupon::orderBy('id', 'desc')->paginate(getPaginate());

        return view('admin.coupon.index', compact('pageTitle', 'coupons'));
    }

    //save
    public function save(Request $request, $id = 0)
    {
        $request->validate([
            'description'      => 'required',
            'code'             => 'required|unique:coupons,code,' . $id,
            'discount_percent' => 'required|numeric|gt:0,max:100',
            'max_uses'         => 'required|integer|min:1',
            'discount_months'  => 'required|integer|min:-1',
        ]);

        if ($id) {
            $coupon   = Coupon::findOrFail($id);
            $notify[] = ['success', 'Coupon updated successfully'];
        } else {
            $coupon   = new Coupon();
            $notify[] = ['success', 'Coupon added successfully'];
        }

        $coupon->description      = $request->description;
        $coupon->code             = $request->code;
        $coupon->discount_percent = $request->discount_percent;
        $coupon->max_uses         = $request->max_uses;
        $coupon->discount_months  = $request->discount_months;
        $coupon->save();

        return back()->withNotify($notify);
    }

    //status
    public function status($id)
    {
        return Coupon::changeStatus($id);
    }

}
