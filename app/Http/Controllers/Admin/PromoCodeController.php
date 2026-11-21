<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PromoCode;
use Illuminate\Http\Request;

class PromoCodeController extends Controller
{
    public function index()
    {
        $promoCodes = PromoCode::latest()->paginate(15);

        return view('admin.promo-codes.index', compact('promoCodes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:promo_codes,code',
            'value_type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
        ]);

        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = true;
        $promo = PromoCode::create($data);

        AuditLog::record('Promo Codes', 'Create Promo', "Created promo code: {$promo->code}");

        return back()->with('status', 'Promo code create ho gaya.');
    }

    public function toggle(PromoCode $promoCode)
    {
        $promoCode->update(['is_active' => ! $promoCode->is_active]);

        return back()->with('status', 'Promo code status update ho gaya.');
    }

    public function destroy(PromoCode $promoCode)
    {
        $promoCode->delete();

        AuditLog::record('Promo Codes', 'Delete Promo', "Deleted promo code: {$promoCode->code}");

        return back()->with('status', 'Promo code delete ho gaya.');
    }
}
