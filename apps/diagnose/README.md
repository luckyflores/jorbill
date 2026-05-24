# JorBill / FourLeaf — Diagnose App

Android tech & customer diagnostic app. Talks to JorBill at
`https://jorbill.maltixtech.xyz/api`.

## First-time setup (on your Windows dev machine)

Requires: Flutter SDK + Android SDK + a connected Android device or emulator.

```powershell
cd path\to\jorbill\apps\diagnose

# 1. Fill in Flutter platform scaffolding (gradle, manifest, etc) — preserves
#    our existing lib/, pubspec.yaml, custom AndroidManifest.xml.
flutter create --platforms=android --project-name diagnose --org xyz.maltixtech .

# 2. Pull deps
flutter pub get

# 3. Build a debug APK
flutter build apk --debug
# APK lands at: build/app/outputs/flutter-apk/app-debug.apk

# 4. Install on a connected device
flutter install
```

If `flutter create` overwrites our custom `AndroidManifest.xml`, run
`git checkout android/app/src/main/AndroidManifest.xml` to restore it (our version
has the WiFi/location/camera permissions the app needs).

## Build a release APK

```powershell
flutter build apk --release
```

For Play Store distribution later: `flutter build appbundle --release` (signs
with your keystore).

## Default API target

`lib/api/api_client.dart` points at `https://jorbill.maltixtech.xyz/api`. To
develop against a local API instead, edit `_baseUrl` there.

## Credentials

The app accepts both **tech** and **customer** logins:
- Tech:     uses the `users` table on JorBill (`role = 'tech'`)
- Customer: uses the `customers` table (`portal_enabled = true`)

The role select on the login screen tells the API which table to authenticate
against.
