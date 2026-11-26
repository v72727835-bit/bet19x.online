<?php

namespace App\Http\Controllers;

/** Serves the Android app (APK) that is built from /android and stored in public/downloads. */
class AppDownloadController extends Controller
{
    public const FILE = 'downloads/bet19x.apk';

    public static function path(): string
    {
        return public_path(self::FILE);
    }

    public static function exists(): bool
    {
        return is_file(self::path());
    }

    /** "6.8 MB", or "77 KB" for a small file */
    public static function sizeLabel(): string
    {
        $bytes = filesize(self::path());

        return $bytes < 1048576 ? number_format(max(1, round($bytes / 1024))) . ' KB' : number_format($bytes / 1048576, 1) . ' MB';
    }

    public function download()
    {
        if (! self::exists()) {
            return redirect()->route('dashboard')->with('error', 'The Android app is not available right now. You can still add the site to your home screen from the browser menu.');
        }

        return response()->download(self::path(), 'BET19X.apk', [
            'Content-Type' => 'application/vnd.android.package-archive',
            'Cache-Control' => 'no-cache, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
