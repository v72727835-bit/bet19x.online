<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    public function index(Request $request)
    {
        // Tenant model always reads from the landlord/default connection (it has
        // no $connection override), so this works correctly no matter which
        // tenant DB is currently active for this request.
        $tenant = Tenant::where('domain', strtolower(preg_replace('/^www\./', '', $request->getHost())))->first();

        return view('admin.license', compact('tenant'));
    }
}
