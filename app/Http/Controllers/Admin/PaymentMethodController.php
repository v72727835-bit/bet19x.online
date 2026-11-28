<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** Admin sets up where players should send their deposits: UPI ID, QR image, bank account. */
class PaymentMethodController extends Controller
{
    public function index()
    {
        $ready = PaymentMethod::ready();
        $methods = $ready ? PaymentMethod::orderBy('sort_order')->orderBy('id')->get() : collect();

        return view('admin.payment-methods.index', compact('methods', 'ready'));
    }

    public function edit(PaymentMethod $method)
    {
        return view('admin.payment-methods.edit', compact('method'));
    }

    private function rules(): array
    {
        return [
            'title' => 'required|string|max:80',
            'upi_id' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9._\-]{2,}@[A-Za-z0-9.\-]{2,}$/'],
            'payee_name' => 'nullable|string|max:100',
            'qr_image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'bank_name' => 'nullable|string|max:100',
            'account_holder' => 'nullable|string|max:100',
            'account_number' => ['nullable', 'string', 'max:40', 'regex:/^[0-9A-Za-z\- ]+$/'],
            'ifsc' => ['nullable', 'string', 'max:20', 'regex:/^[A-Za-z0-9]+$/'],
            'instructions' => 'nullable|string|max:1000',
            'min_amount' => 'required|numeric|min:1|max:1000000',
            'max_amount' => 'nullable|numeric|gte:min_amount|max:10000000',
            'sort_order' => 'nullable|integer|min:0|max:9999',
        ];
    }

    private const MESSAGES = [
        'upi_id.regex' => 'UPI ID aise honi chahiye: name@bank (jaise shop@okaxis).',
        'account_number.regex' => 'Account number me sirf akshar, number aur - ho sakte hain.',
        'ifsc.regex' => 'IFSC code me sirf akshar aur number ho sakte hain.',
        'qr_image.image' => 'QR ek image honi chahiye (PNG, JPG ya WebP).',
        'qr_image.mimes' => 'QR image PNG, JPG ya WebP hi ho sakti hai.',
        'qr_image.max' => 'QR image 2 MB se chhoti honi chahiye.',
    ];

    private function hasDetails(array $d, bool $hasQr): bool
    {
        return ! empty($d['upi_id']) || $hasQr || ! empty($d['account_number']);
    }

    private function clean(array $d): array
    {
        foreach (['upi_id', 'payee_name', 'bank_name', 'account_holder', 'account_number', 'ifsc', 'instructions'] as $f) {
            $d[$f] = isset($d[$f]) && trim((string) $d[$f]) !== '' ? trim($d[$f]) : null;
        }
        if ($d['ifsc']) {
            $d['ifsc'] = strtoupper($d['ifsc']);
        }
        $d['sort_order'] = $d['sort_order'] ?? 0;
        $d['max_amount'] = ($d['max_amount'] ?? null) === null || $d['max_amount'] === '' ? null : $d['max_amount'];

        return $d;
    }

    public function store(Request $request)
    {
        if (! PaymentMethod::ready()) {
            return back()->withErrors(['title' => 'Pehle server par migration chalaiye (php artisan migrate).']);
        }

        $data = $request->validate($this->rules(), self::MESSAGES);
        $data = $this->clean($data);

        if (! $this->hasDetails($data, $request->hasFile('qr_image'))) {
            return back()->withInput()->withErrors(['upi_id' => 'Kam se kam ek cheez daalein: UPI ID, QR image ya bank account number.']);
        }

        $data['qr_image_path'] = $request->hasFile('qr_image') ? $request->file('qr_image')->store('payment-qr', 'public') : null;
        unset($data['qr_image']);
        $data['is_active'] = true;

        $method = PaymentMethod::create($data);
        AuditLog::record('Payment Methods', 'Create', "Added payment method #{$method->id} ({$method->title}): " . $method->summary());

        return redirect()->route('admin.payment-methods.index')->with('status', "{$method->title} add ho gaya. Ab ye users ke Deposit page par dikhega.");
    }

    public function update(Request $request, PaymentMethod $method)
    {
        $data = $this->clean($request->validate($this->rules(), self::MESSAGES));

        $newQr = $request->hasFile('qr_image');
        $removeQr = $request->boolean('remove_qr');
        $keepsQr = $newQr || ($method->qr_image_path && ! $removeQr);
        if (! $this->hasDetails($data, $keepsQr)) {
            return back()->withInput()->withErrors(['upi_id' => 'Kam se kam ek cheez rakhni hogi: UPI ID, QR image ya bank account number.']);
        }

        $old = $method->qr_image_path;
        if ($newQr) {
            $data['qr_image_path'] = $request->file('qr_image')->store('payment-qr', 'public');
        } elseif ($removeQr) {
            $data['qr_image_path'] = null;
        }
        unset($data['qr_image']);

        $method->update($data);
        if ($old && $old !== $method->qr_image_path) {
            Storage::disk('public')->delete($old);
        }

        AuditLog::record('Payment Methods', 'Update', "Updated payment method #{$method->id} ({$method->title})");

        return redirect()->route('admin.payment-methods.index')->with('status', "{$method->title} update ho gaya.");
    }

    public function toggle(PaymentMethod $method)
    {
        $method->update(['is_active' => ! $method->is_active]);
        AuditLog::record('Payment Methods', $method->is_active ? 'Activate' : 'Deactivate', "{$method->title} (#{$method->id})");

        return back()->with('status', $method->title . ($method->is_active ? ' ab users ko dikh raha hai.' : ' users ke liye band ho gaya.'));
    }

    public function destroy(PaymentMethod $method)
    {
        $title = $method->title;
        if ($method->qr_image_path) {
            Storage::disk('public')->delete($method->qr_image_path);
        }
        $method->delete();
        AuditLog::record('Payment Methods', 'Delete', "Deleted payment method {$title}");

        return redirect()->route('admin.payment-methods.index')->with('status', "{$title} hata diya gaya. Purani deposit requests par asar nahi padega.");
    }
}
