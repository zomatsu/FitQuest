# FitQuest Android App

This is an Android WebView wrapper around the supplied FitQuest web game. The original HTML/CSS/JavaScript and image assets are bundled into the APK.

## What is included
- FitQuest game UI and battle system
- Existing character and arena artwork
- Camera-based exercise tracking through MediaPipe
- Offline player profiles and progress using localStorage
- Android camera permission handling
- App icon
- Original PHP API + MySQL schema in `backend/` for optional online sync

## Open/build
1. Install Android Studio.
2. Open this `fitquest_app` folder as a project.
3. Let Android Studio download the Android Gradle Plugin/dependencies.
4. Connect an Android phone with USB debugging enabled or start an emulator.
5. Run the `app` configuration.

The generated app uses the bundled HTML at `app/src/main/assets/index.html`.

## Camera / exercise tracking
The app requests Android camera permission and exposes the camera to the WebView. MediaPipe is loaded from jsDelivr, so the exercise-tracking portion needs internet access unless MediaPipe is later bundled locally.

## Optional PHP/MySQL backend
The mobile app is configured for local/offline mode by default. To use the existing PHP API, host `backend/api.php` on an HTTPS PHP/MySQL server and change this line near the top of `index.html`:

    window.FITQUEST_API_BASE = '';

For example, set it to your server's base URL. Do not use `localhost` from the Android phone because `localhost` refers to the phone itself.

Import `backend/fit_quest_db.sql` into MySQL before using the PHP API.
