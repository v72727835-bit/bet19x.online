<x-master-layout title="Panel Branding">

    <div style="max-width:600px;">
        <div class="mp-card" style="padding:24px;">
            <p style="font-size:12px; color: var(--mp-muted); margin:0 0 16px 0;">Yeh sirf tumhare khud ke Master Panel (/master) ka logo/naam hai — yeh alag hai har client ke apne white-label branding se (wo unke apne Admin Panel &gt; Settings se hoti hai).</p>
            <form method="POST" action="{{ route('master.settings.update') }}" enctype="multipart/form-data">
                @csrf
                <div style="margin-bottom:16px;">
                    <label style="font-size:12px; color: var(--mp-muted); display:block; margin-bottom:4px;">Company / Panel Name</label>
                    <input type="text" name="company_name" value="{{ $setting->company_name }}" class="mp-input" required>
                </div>
                <div style="margin-bottom:16px;">
                    <label style="font-size:12px; color: var(--mp-muted); display:block; margin-bottom:4px;">Logo</label>
                    @if($setting->logo_path)
                        <img src="{{ asset('storage/'.$setting->logo_path) }}" style="height:56px; margin-bottom:8px; border-radius:8px; display:block;">
                    @endif
                    <input type="file" name="logo" class="mp-input">
                </div>
                <div style="margin-bottom:16px;">
                    <label style="font-size:12px; color: var(--mp-muted); display:block; margin-bottom:4px;">Accent Color</label>
                    <input type="color" name="primary_color" value="{{ $setting->primary_color ?? '#8B5CF6' }}" class="mp-input" style="height:40px;">
                </div>
                <button class="mp-btn">Save Branding</button>
            </form>
        </div>
    </div>

</x-master-layout>
