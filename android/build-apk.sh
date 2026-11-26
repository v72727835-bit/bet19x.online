#!/usr/bin/env bash
# Builds the signed BET19X Android app and copies it to public/downloads/bet19x.apk
#
# Needs: JDK 17+, the Android SDK (ANDROID_HOME) with platform 35 and build-tools 35.0.0,
#        gradle 8.9+ on PATH, and your signing key:
#
#   BX_KEYSTORE=/path/to/bet19x-release.jks BX_STORE_PASS=... [BX_KEY_ALIAS=bet19x] [BX_KEY_PASS=...] \
#   ./android/build-apk.sh [versionCode] [versionName] [siteUrl]
#
# Always sign updates with the SAME keystore, otherwise phones that have the app
# installed must uninstall it first. Never commit the keystore.
set -euo pipefail
cd "$(dirname "$0")"

: "${BX_KEYSTORE:?set BX_KEYSTORE to your .jks file}"
: "${BX_STORE_PASS:?set BX_STORE_PASS}"
: "${ANDROID_HOME:?set ANDROID_HOME to the Android SDK folder}"

CODE="${1:-1}"; NAME="${2:-1.0}"; SITE="${3:-https://bet19x.online}"

echo "sdk.dir=$ANDROID_HOME" > local.properties
gradle --no-daemon -q assembleRelease -PappVersionCode="$CODE" -PappVersionName="$NAME" -PsiteUrl="$SITE"

APK=app/build/outputs/apk/release/app-release.apk
mkdir -p ../public/downloads
cp "$APK" ../public/downloads/bet19x.apk
echo "$ANDROID_HOME/build-tools/35.0.0/apksigner verify --print-certs ../public/downloads/bet19x.apk"
"$ANDROID_HOME/build-tools/35.0.0/apksigner" verify --print-certs ../public/downloads/bet19x.apk | head -3
ls -la ../public/downloads/bet19x.apk
