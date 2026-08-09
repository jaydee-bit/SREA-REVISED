# SREA – Complete System Memory File (Resident App + Responder App)

> **Last updated:** August 9, 2026  
> **Version:** 3.2  
> **Purpose:** Full project context for the SREA emergency reporting system. Includes all architectural decisions, code changes, database migrations, UI/UX improvements, issue resolutions, and next steps. Use this to restore context instantly after a chat reset.

---

## 1. Project Overview

**SREA (San Rafael Emergency Alert System)** – a mobile‑based emergency reporting system for the Municipality of San Rafael, Bulacan, Philippines.  
Developed for the MDRRMO (Municipal Disaster Risk Reduction and Management Office).

**Two mobile clients:**
1. **Resident App (Anonymous)** – no login required. Residents report emergencies using selfie + video, GPS location, and polygon‑based barangay detection. Daily limit: 3 reports per device.
2. **Responder App (Authenticated)** – for MDRRMO responders. Login via Sanctum. View, assign, and resolve incidents. Resolve dialog includes incident type, description, resolution notes, and optional reporter name.

**Backend:** Laravel 11 with MySQL, using Sanctum for responder authentication.

---

## 2. Current Status (August 9, 2026)

| Component | Status | Notes |
|-----------|--------|-------|
| **Resident App – Reporting Flow** | ✅ Complete | Selfie + video upload, storage, display all working. |
| **Resident App – UI/UX** | ✅ Complete | Consistent design across all screens. Badges, icons, and metadata unified. |
| **Resident App – Media Display** | ✅ Complete | Photo + video display with full‑screen viewer for photos. |
| **Resident App – Search Location** | ✅ Complete | Testing feature to search addresses outside San Rafael. |
| **Resident App – Notifications** | ✅ Complete | In‑app notifications with polling (10s) and foreground checks. Red dot with persistent read status. |
| **Resident App – Notification Bell** | ✅ Complete | Bell icon with red dot in AppBar; sidebar entry removed. |
| **Resident App – My Reports** | ✅ Complete | Tabs (Active/Resolved) with white background and blue underline. |
| **Resident App – Emergency Call** | ✅ Complete | Confirmation dialog → opens dialer with prefilled number. |
| **Backend – Incident Storage** | ✅ Complete | `photo_path` and `video_path` saved; `reporter_name` stored. |
| **Backend – API Endpoints** | ✅ Complete | All public and authenticated endpoints working. |
| **Android Manifest** | ✅ Complete | Cleartext traffic allowed; map intents configured. |
| **Responder App – Incident List** | ✅ Complete | Photo thumbnail with video indicator. |
| **Responder App – Incident Detail** | ✅ Complete | Photo + video display, full‑screen photo viewer. |
| **Responder App – Notifications** | ✅ Complete | Real incidents as notifications, read status persisted. |
| **Responder App – Actions** | ✅ Complete | Respond, Resolve (with validation), Reassign, Reject, Notes. |
| **UI Consistency** | ✅ Complete | Badges, icons, 12‑hour time, unified detail screens. |
| **Push Notifications (FCM)** | ⏳ Planned | Not started – in roadmap for future implementation. |
| **Admin Panel** | ⏳ Planned | Future feature. |
| **Live Deployment** | ⏳ Pending | Backend and apps ready for deployment. |

---

## 3. Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────┐
│                        Laravel Backend                             │
│              (admin_backend / Laravel 11 + Sanctum)               │
│                                                                     │
│  Public endpoints (no auth) – for Resident App:                    │
│  • GET    /user/alerts              (public – alerts)             │
│  • GET    /user/announcements       (public – announcements)      │
│  • GET    /user/traffic             (public – traffic advisories)  │
│  • POST   /public/incidents         (create anonymous report)     │
│  • GET    /public/incidents/{uuid}  (fetch single report)         │
│  • POST   /public/upload-reporter-media  (upload selfie/video)    │
│                                                                     │
│  Authenticated endpoints – for Responder App:                      │
│  • GET    /api/responder/incidents                                │
│  • GET    /api/responder/incidents/{uuid}                         │
│  • POST   /api/responder/incidents/{uuid}/respond                 │
│  • POST   /api/responder/incidents/{uuid}/reassign                │
│  • POST   /api/responder/incidents/{uuid}/resolve                 │
│  • POST   /api/responder/incidents/{uuid}/reject   (Admin only)  │
│  • POST   /api/responder/incidents/{uuid}/notes                   │
└─────────────────────────────────────────────────────────────────────┘
                                    │
                ┌───────────────────┴───────────────────┐
                ▼                                       ▼
┌──────────────────────────────┐          ┌──────────────────────────────┐
│     Resident App (Flutter)    │          │     Responder App (Flutter)   │
│     package: mobile_user_app  │          │     package: mobile_responder │
│  - No authentication          │          │  - Sanctum token auth        │
│  - Starts at HomeScreen       │          │  - HomeScreen with bottom nav│
│  - Sidebar navigation         │          │  - Incident list (filterable)│
│  - Anonymous reporting        │          │  - Incident detail with      │
│    (selfie + video required)  │          │    action buttons            │
│  - Soft selfie verification   │          │  - Resolve dialog with:      │
│  - Daily limit 3/day          │          │    • Incident Type dropdown  │
│  - Local history (UUIDs)      │          │    • Description (required)  │
│  - Notifications with updates │          │    • Resolution Notes        │
│  - OpenStreetMap + polygons   │          │    • Reporter Name (optional)│
│  - "Open in Maps" button      │          │  - Reject dialog (Admin only)│
│  - Search location for test   │          │  - "Potential Duplicate"     │
│  - Consistent UI titles       │          │    badge (visual hint)       │
│  - Cards with accent icons    │          │  - Permission checks:        │
│  - Unified badge styling      │          │    • Resolve only for        │
│  - Tappable selfie photo      │          │      assigned responder      │
│  - 12‑hour time format        │          │    • Reject only for Admin   │
│  - Metadata rows with icons   │          │  - Photo + video display      │
│  - Notification bell + red dot│          │                               │
│  - My Reports tabs (Active/Resolved) │    │                               │
└──────────────────────────────┘          └──────────────────────────────┘
```

---

## 4. Tech Stack – Detailed

| Category | Technology | Version | Notes |
|----------|------------|---------|-------|
| **Backend Framework** | Laravel | 11.x | REST API |
| **Authentication (Responder)** | Laravel Sanctum | ^4.0 | Token‑based |
| **Database** | MySQL | 8.0 | Relational |
| **Mobile Framework** | Flutter | 3.27+ | SDK `^3.11.4` |
| **Language** | Dart | 3.6+ | Null safety |
| **Shared UI** | `srea_shared` (local package) | – | Responsive theme, widgets |
| **HTTP Client** | `dio` | ^5.9.2 | Interceptors, file upload |
| **Local Storage** | `shared_preferences` | ^2.2.0 | Store report IDs, daily count, notification timestamps |
| **Location** | `geolocator` + `geocoding` | ^14.0.2, ^4.0.0 | GPS, reverse geocoding, search |
| **Maps** | `flutter_map` + `latlong2` | ^8.3.0, ^0.9.1 | OpenStreetMap (no API key) |
| **Camera** | `image_picker` | ^1.1.2 | Selfie + video capture |
| **Image Compression** | `flutter_image_compress` | ^2.1.0 | Quality 70 |
| **URL Launcher** | `url_launcher` | ^6.3.2 | Emergency calls, "Open in Maps" |
| **Date Formatting** | `intl` | ^0.20.2 | Localised dates |
| **Fonts** | `google_fonts` | ^8.0.2 | Plus Jakarta Sans, Montserrat |
| **Face Detection** | `google_mlkit_face_detection` | ^0.10.0 | Soft selfie verification (resident app only) |
| **Video Player** | `video_player` | ^2.9.1 | Video playback in detail screens |
| **Design Tokens** | `SreaColors`, `SreaText`, `SreaRadius`, `SreaSpacing` | – | Defined in `srea_shared` |

---

## 5. Database Schema – Incidents Table (Key Columns)

Now includes `video_path` and `reporter_name` (nullable). The `photo_path` is now actually used.

| Column | Type | Nullable | Notes |
|--------|------|----------|-------|
| `id` | BIGINT UNSIGNED | No | Auto‑increment (internal) |
| `uuid` | CHAR(36) | No | Public identifier (unique) |
| `user_id` | BIGINT UNSIGNED | Yes | NULL = anonymous |
| `reporter_name` | VARCHAR(255) | Yes | Optional – filled by responder |
| `type` | VARCHAR(255) | No | Initially "Emergency" |
| `description` | TEXT | No | Initially "Emergency report with selfie and video" |
| `photo_path` | VARCHAR(255) | Yes | Path to selfie image |
| `video_path` | VARCHAR(255) | Yes | Path to video recording |
| `status` | ENUM | No | Pending, Responding, Resolved, Escalated, Rejected |
| `barangay` | VARCHAR(255) | No | Auto‑detected |
| `latitude` / `longitude` | DECIMAL(10,7) | No | GPS coordinates |

**Dropped columns:** `persons_involved`, `reporter_role`, `reporter_is_verified`.

**Emergency Calls:** `user_id` must be nullable for anonymous calls.

---

## 6. API Routes – Full List (`routes/api.php`)

### Public Routes (no auth)

| Method | Endpoint | Controller | Method | Description |
|--------|----------|------------|--------|-------------|
| GET | `/user/alerts` | `AlertController` | `index` | List alerts (all) |
| GET | `/user/announcements` | `AnnouncementController` | `index` | List announcements (all) |
| GET | `/user/traffic` | `TrafficController` | `index` | List traffic advisories |
| POST | `/public/incidents` | `UserIncidentController` | `store` | Create anonymous report |
| GET | `/public/incidents/{uuid}` | `UserIncidentController` | `show` | Fetch single incident by UUID |
| POST | `/public/upload-reporter-media` | `UploadController` | `store` | Upload selfie/video |

### Authenticated – Responder

| Method | Endpoint | Controller | Method | Description |
|--------|----------|------------|--------|-------------|
| GET | `/responder/incidents` | `IncidentController` | `index` | List all incidents |
| GET | `/responder/incidents/{uuid}` | `IncidentController` | `show` | Show incident by UUID |
| POST | `/responder/incidents/{uuid}/respond` | `IncidentController` | `respond` | Assign to self |
| POST | `/responder/incidents/{uuid}/reassign` | `IncidentController` | `reassign` | Reassign |
| POST | `/responder/incidents/{uuid}/resolve` | `IncidentController` | `resolve` | Resolve with type, description, notes |
| POST | `/responder/incidents/{uuid}/reject` | `IncidentController` | `reject` | Reject (Admin only) |
| POST | `/responder/incidents/{uuid}/notes` | `IncidentController` | `updateNotes` | Add notes |

---

## 7. File Inventory – Resident App (All Updated Files)

| File | Status | Notes |
|------|--------|-------|
| `lib/main.dart` | ✅ Updated | No auth, starts at `HomeScreen` |
| `lib/screens/home_screen.dart` | ✅ Restructured | Added notification bell with red dot (open = mark as read). Removed `/notifications` from route map. Added periodic polling (10s), app lifecycle observer, and listener to notification service. |
| `lib/screens/report_incident_screen.dart` | ✅ Active | Search‑location feature + nested‑response parsing. **Do not replace with `.d` file.** |
| `lib/screens/report_incident_screen.d` | ⏳ Original reference | No search location – kept for history. |
| `lib/screens/my_reports_screen.dart` | ✅ Enhanced | Tabs (Active/Resolved) with white background and blue underline. Status filtering. |
| `lib/screens/incident_report_detail_screen.dart` | ✅ Restructured | Badge next to title; unified metadata row; 12‑hour time; tappable selfie. |
| `lib/screens/alerts_screen.dart` | ✅ Restructured | Colored icon circle; consistent badge style. |
| `lib/screens/alert_detail_screen.dart` | ✅ Restructured | Badge next to title; unified metadata row; 12‑hour time. |
| `lib/screens/announcements_screen.dart` | ✅ Restructured | Colored icon circle; consistent card layout. |
| `lib/screens/announcement_detail_screen.dart` | ✅ Restructured | Unified metadata row; 12‑hour time. |
| `lib/screens/traffic_advisories_screen.dart` | ✅ Restructured | Colored traffic icon circle; consistent badge style. |
| `lib/screens/traffic_advisory_detail_screen.dart` | ✅ Restructured | Badge next to title; unified metadata row; 12‑hour time. |
| `lib/screens/notifications_screen.dart` | ✅ Enhanced | In‑app notifications (alerts, announcements, traffic, incident status). Fixed `'body'` not `'content'`. |
| `lib/services/notification_service.dart` | ✅ Enhanced | Handles incident status changes with `ChangeNotifier`. Stores last known status. Shows "In Progress" instead of "Responding". |
| `lib/models/incident_report_model.dart` | ✅ Enhanced | `videoPath` and `reporterName` fields added. |
| `lib/services/api_service.dart` | ✅ Updated | Added `getFullImageUrl()` helper. |
| `lib/widgets/srea_sidebar.dart` | ✅ Updated | Removed Notifications entry – bell handles it. |
| `lib/widgets/srea_bottom_nav.dart` | ❌ Deleted | Not used |
| `android/app/src/main/AndroidManifest.xml` | ✅ Fixed | Cleartext traffic + map intents added. |

---

## 8. File Inventory – Responder App

| File | Status | Notes |
|------|--------|-------|
| `lib/services/api_service.dart` | ✅ Updated | Resolve, reject, UUID support. |
| `lib/screens/incident_detail_screen.dart` | ✅ Updated | Photo + video display, full‑screen photo viewer, resolve dialog with inline validation (min 10 chars). |
| `lib/screens/incident_list_screen.dart` | ✅ Updated | Video indicator on thumbnails, duplicate badges, anonymous label. |
| `lib/screens/home_screen.dart` | ✅ Updated | App bar with RESPONDER badge, notification bell with badge. |
| `lib/screens/profile_screen.dart` | ✅ Updated | Admin role, stats. |
| `lib/models/incident_report_model.dart` | ✅ Updated | `videoPath` field added. |
| `lib/services/notification_service.dart` | ✅ Updated | Real incidents as notifications with `SharedPreferences` persistence. |
| `lib/screens/notifications_screen.dart` | ✅ Updated | Real incident notifications, tap to navigate to detail. |
| `android/app/src/main/AndroidManifest.xml` | ✅ Fixed | Cleartext traffic + map intents added. |

---

## 9. UI/UX Design System (Unified)

### 9.1 Badge Styling (Consistent Across All Screens)

All badges now use the same styling:

```dart
Container(
  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
  decoration: BoxDecoration(
    color: color.withOpacity(0.12),
    borderRadius: BorderRadius.circular(20),
    border: Border.all(color: color.withOpacity(0.3)),
  ),
  child: Text(
    label.toUpperCase(),
    style: TextStyle(
      color: color,
      fontSize: 10,
      fontWeight: FontWeight.w600,
    ),
  ),
)
```

### 9.2 List Screen Layout

| Element | Implementation |
|---------|----------------|
| **Accent** | Colored icon in circle background (replaces left border) |
| **Badge** | Colored background + border (same as My Reports) |
| **Title** | Bold, single line |
| **Description** | Two lines, secondary color |
| **Metadata** | Icons with date, location (if available) |

### 9.3 Detail Screen Layout (Unified Structure)

1. **Title + Badge** (same row)
2. **Metadata Row** – icons with date, location, reporter, assigned to (as applicable)
3. **Divider**
4. **Description** (with section header)
5. **Additional sections** (image, effective period, contact footer)
6. **Optional extras** (map, notes, resolution notes)

### 9.4 Time Format

All dates now use **12‑hour format with AM/PM**:

```
Before: Aug 08, 2026 10:36
After:  Aug 8, 2026 at 10:36 AM
```

### 9.5 My Reports Screen

- **Tabs**: Active (Pending, In Progress, Responding) and Resolved (Resolved, Rejected)
- **White tab background** with blue underline on selected tab
- **Status dot** replaces left border on cards
- **Photo thumbnail** for visual reference

---

## 10. Notification System (Current + Planned)

### ✅ Current – In‑App Notifications (Resident)

| Feature | Implementation | Status |
|---------|----------------|--------|
| **Polling** | Every 10 seconds while app is open | ✅ Working |
| **Foreground check** | Immediate check when app returns from background | ✅ Working |
| **Red dot** | Shows if `latest_notification_timestamp` > `last_notification_view_time` | ✅ Working |
| **Mark as read** | Bell tap or opening `NotificationsScreen` updates timestamp | ✅ Working |
| **Status display** | Shows "In Progress" instead of "Responding" | ✅ Working |
| **Notification screen** | Merges alerts, announcements, traffic, and incident status changes | ✅ Working |

### ✅ Current – In‑App Notifications (Responder)

| Feature | Implementation | Status |
|---------|----------------|--------|
| **Load notifications** | Fetches recent incidents on app launch and refresh | ✅ Working |
| **Read status** | Persisted in `SharedPreferences` | ✅ Working |
| **Tap notification** | Navigates to incident detail | ✅ Working |
| **UUID handling** | Uses `uuid` field from backend | ✅ Working |

### 🚀 Planned – Push Notifications (FCM)

| Feature | Status | Notes |
|---------|--------|-------|
| Firebase Cloud Messaging (FCM) setup | ⏳ Planned | Future implementation. |
| Resident device token storage | ⏳ Planned | Store tokens locally or with backend. |
| Backend FCM integration (Laravel) | ⏳ Planned | Send notifications on status changes, new alerts, etc. |
| Flutter FCM integration | ⏳ Planned | Handle foreground, background, terminated states. |
| System tray notifications | ⏳ Planned | Show notifications even when app is closed. |

---

## 11. Critical Implementation Details to Preserve

### 11.1 Search Location Feature

- **Active file:** `report_incident_screen.dart` contains the search‑location feature.
- **Purpose:** Temporary testing aid to test reporting from anywhere.
- **Behavior:** Searches any address via `geocoding`, moves the map, detects barangay.
- **Submission:** Still blocked for out‑of‑bounds locations via `_isLocationValid`.
- **Original file:** `report_incident_screen.d` is the original without search – **never use it**.

### 11.2 Video Playback on Android

- **Manifest:** `android:usesCleartextTraffic="true"` is required for `video_player` to stream HTTP videos in local development.
- **Do not remove** without adding HTTPS support.

### 11.3 Open in Maps

- **Manifest:** `<queries>` intents for `geo:` and `https:` schemes are required for `canLaunchUrl()` on Android 11+.
- **Behavior:** Tries `geo:` URI first, falls back to Google Maps web URL.

### 11.4 Submission Parsing

- **Critical:** `_submitReport()` correctly extracts data from nested `'incident'` key.
- **Do not revert** to top‑level parsing – this caused `FormatException`.

### 11.5 Video URL Construction

- **Detail screen:** Uses `ApiService().getFullImageUrl(videoPath)`.
- **Do not hardcode** URL concatenation – the helper handles base URL and `/storage/` properly.

### 11.6 Video Error Handling

- **Behavior:** Shows "Video failed to load" if `initialize()` fails.
- **Distinction:** Separate from "No video submitted" – preserves this distinction.

### 11.7 Resident Polling

- **Interval:** 10 seconds while app is in foreground.
- **Lifecycle:** Immediate check when app returns from foreground.
- **Listener:** Notification service notifies UI to rebuild red dot.

### 11.8 My Reports Tabs

- **Custom tabs** (not `TabController`) using `IndexedStack`.
- **White background** with **blue underline** on selected tab.
- **Active tab:** Pending, Responding, In Progress.
- **Resolved tab:** Resolved, Rejected.

---

## 12. Backend Controllers – Critical Fixes

### `UserIncidentController.php` – store()

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'latitude' => 'required|numeric',
        'longitude' => 'required|numeric',
        'barangay' => 'required|string|max:255',
        'address' => 'required|string',
        'location_details' => 'nullable|string',
        'reporter_image' => 'nullable|string',
        'reporter_video' => 'nullable|string',
    ]);

    $incident = Incident::create([
        'user_id' => null,
        'type' => 'Emergency',
        'description' => 'Emergency report with selfie and video',
        'barangay' => $validated['barangay'],
        'latitude' => $validated['latitude'],
        'longitude' => $validated['longitude'],
        'address' => $validated['address'],
        'location_details' => $validated['location_details'] ?? null,
        'photo_path' => $validated['reporter_image'] ?? null,
        'video_path' => $validated['reporter_video'] ?? null,
        'status' => 'Pending',
        'reported_at' => now(),
    ]);

    return response()->json([
        'id' => $incident->uuid,
        'incident' => $incident,
    ], 201);
}
```

### `IncidentController.php` – show() and index() (Reporter Name Fix)

```php
public function show($uuid)
{
    $incident = Incident::with(['assignedTo', 'reporter'])
        ->where('uuid', $uuid)
        ->firstOrFail();

    $data = $incident->toArray();

    // ✅ Use reporter_name if available, otherwise fallback to "Anonymous"
    if ($incident->user_id === null) {
        $reporterName = $incident->reporter_name ?? 'Anonymous';
        $data['reporter'] = [
            'name' => $reporterName,
            'role' => null,
            'is_verified' => false,
        ];
        $data['reporter_name'] = $reporterName;
    }

    return response()->json($data);
}
```

### `Incident.php` – Model

```php
protected $fillable = [
    'uuid', 'user_id', 'reporter_name', 'type', 'description',
    'photo_path', 'video_path', 'barangay', 'location_details',
    'latitude', 'longitude', 'address', 'status', 'reported_at',
    'assigned_to', 'responder_notes', 'escalation_reason',
    'escalated_by', 'escalated_at', 'resolution_notes', 'resolved_at',
];
```

---

## 13. Complete List of Fixes Applied

| Issue | Root Cause | Fix | Date |
|-------|-----------|-----|------|
| Video not playing on Android | Cleartext traffic blocked; URL construction wrong | Added `usesCleartextTraffic`; fixed URL helper | Aug 8 |
| "Open in Maps" not working | Missing `<queries>` intents | Added geo and https intents | Aug 8 |
| Submission shows "fail to submit" despite saving | Parsing error from nested API response | Fixed to use `newIncident['incident']` | Aug 8 |
| Video player shows "No video submitted" when video exists | Missing error handler on `initialize()` | Added `.catchError()` | Aug 8 |
| My Reports card shows duplicate barangay | Subtitle misused | Fixed to show address or type | Aug 8 |
| Resident detail shows "Unverified Resident" badge | Unintended UI | Removed | Aug 8 |
| Status badge not at top-right | Layout misplacement | Moved to top-right | Aug 8 |
| App bar centered | Inconsistent | Removed `centerTitle` | Aug 8 |
| Left border looks ugly | Design choice | Replaced with colored icon circle | Aug 9 |
| Badges inconsistent across screens | Different styling | Unified badge styling across all screens | Aug 9 |
| Detail screens inconsistent structure | Different layouts | Unified structure: badge + title row, metadata row, divider, description, extras | Aug 9 |
| Time format 24-hour (military) | Default parsing | Changed to 12-hour with AM/PM | Aug 9 |
| Selfie photo not tappable | Missing GestureDetector | Added full-screen image viewer | Aug 9 |
| Detail screen badge floating above title | Layout issue | Badge now next to title in same row | Aug 9 |
| Announcement notifications blank | Used `'content'` instead of `'body'` | Fixed to use `'body'` | Aug 9 |
| Resident notifications not working | No polling/listener | Added 10s polling + foreground check + listener | Aug 9 |
| Notification red dot not appearing | `latest_notification_timestamp` not updated | Added `_updateLatestTimestamp()` in `addNotification()` | Aug 9 |
| Notification shows "Responding" instead of "In Progress" | Raw status used directly | Added `_getDisplayStatus()` mapping | Aug 9 |
| Resolve dialog "Action failed" | Backend requires min 10 chars | Added inline validation (min 10 chars) | Aug 9 |
| Responder notification 404 | Used integer ID instead of UUID | Use `uuid` field from backend | Aug 9 |
| Report screen not clearing after submission | State not reset | Added `setState` reset before navigation | Aug 9 |
| My Reports tabs not working | `TabController` initialization issues | Replaced with custom tabs + `IndexedStack` | Aug 9 |
| Emergency call button | No confirmation | Added confirmation dialog with hotline number | Aug 9 |

---

## 14. Known Issues & Resolutions

| Issue | Root Cause | Status |
|-------|-----------|--------|
| `personsInvolved`, `reporterRole`, `reporterIsVerified` in Flutter | Obsolete fields in model | ✅ Fixed |
| Notifications screen errors | Used `api.getUser()` (removed) | ✅ Fixed |
| Bottom nav conflict | Still referenced | ✅ Fixed |
| SreaDropdown crash | Missing `Material` wrapper | ✅ Fixed |
| Location detection inaccurate | Reverse geocoding | ✅ Fixed (polygon) |
| Daily limit not persisted | No local storage | ✅ Fixed |
| Selfie/video hidden until location valid | UX flaw | ✅ Fixed (always visible) |
| No "Open in Maps" | Missing feature | ✅ Fixed |
| Incidents lack proper classification | Resident only sends "Emergency" | ✅ Fixed (responder selects) |
| Sequential IDs are guessable | Auto‑increment `id` exposed | ✅ Fixed (UUID) |
| Regular responders could reject reports | No authorization check | ✅ Fixed |
| Duplicates hard to spot | No visual hint | ✅ Fixed (badge) |
| Unassigned responders could see Resolve button | No `assigned_to` check | ✅ Fixed |
| 500 Error on Alerts, Announcements, Traffic | Controllers using `$request->user()` without null check | ✅ Fixed |
| 500 Error on Emergency Calls | `$request->user()->id` for anonymous users | ✅ Fixed |
| 413 Payload Too Large | PHP upload limit too low | ✅ Fixed (100M) |
| Video not showing on Android | Cleartext traffic blocked; URL construction wrong | ✅ Fixed |
| "Open in Maps" not working | Missing `<queries>` intents | ✅ Fixed |
| Submission shows "fail to submit" despite saving | Parsing error from nested API response | ✅ Fixed |
| Video player shows "No video submitted" when video exists | Missing error handler on `initialize()` | ✅ Fixed |
| My Reports card shows duplicate barangay | Subtitle misused | ✅ Fixed |
| Resident detail shows "Unverified Resident" badge | Unintended UI | ✅ Fixed |
| Status badge not at top-right | Layout misplacement | ✅ Fixed |
| App bar centered | Inconsistent | ✅ Fixed |
| Left border looks ugly | Design choice | ✅ Fixed (colored icon circle) |
| Badges inconsistent across screens | Different styling | ✅ Fixed (unified styling) |
| Detail screens inconsistent | Different layouts | ✅ Fixed (unified structure) |
| Time format 24-hour | Default parsing | ✅ Fixed (12‑hour AM/PM) |
| Selfie photo not tappable | Missing GestureDetector | ✅ Fixed |
| Resident notifications not updating | No polling/listener | ✅ Fixed |
| Notification red dot not appearing | Timestamp not updated | ✅ Fixed |
| Notification shows "Responding" | Raw status used | ✅ Fixed |
| Resolve dialog validation | No frontend validation | ✅ Fixed |
| Responder notification 404 | Wrong ID format | ✅ Fixed |
| Report screen not clearing | State not reset | ✅ Fixed |
| My Reports tabs | `TabController` issues | ✅ Fixed (custom tabs) |
| Emergency call button | No confirmation | ✅ Fixed |

---

## 15. Next Steps (Roadmap)

### ✅ Completed

1. All critical bugs and UI issues fixed.
2. Backend fully updated (`video_path`, controller, model).
3. Android manifest fixed.
4. UI unified (badges, icons, detail screens, 12‑hour time).
5. Selfie tappable (full‑screen viewer).
6. Notification bell with red dot (in‑app).
7. Resident notifications with polling + foreground checks.
8. Sidebar cleaned up (notifications removed).
9. My Reports tabs (Active/Resolved).
10. Emergency call with confirmation.
11. Responder app – video display, resolve validation, notifications.

### 🔴 In Progress / Testing

1. **Test the Full Anonymous Flow** – submit → respond → resolve → resident sees status update.
2. **Ensure Backend is Running Correctly** – restart server, verify ADB reverse, test endpoints.

### ⏳ Planned (Future)

3. **Push Notifications (FCM)** – implement real‑time push notifications (Messenger‑like).
4. **Deploy backend to live server** – update `baseUrl` in apps.
5. **Admin panel** – Filament (future).
6. **Responder app improvements** – refine duplicate handling, reject flow.

---

## 16. Environment & Setup Instructions

### Backend (Laravel)
```bash
cd admin_backend
composer install
cp .env.example .env
# Edit .env: DB_DATABASE, DB_USERNAME, DB_PASSWORD, and ensure APP_URL matches baseUrl
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve --host=127.0.0.1 --port=8080
```

### ADB Reverse (for Android device)
```bash
adb devices
adb -s <device-id> reverse tcp:8080 tcp:8080
```

### Flutter Resident App
```bash
cd mobile_user_app
flutter clean
flutter pub get
flutter run
```

### Flutter Responder App
```bash
cd mobile_responder_app
flutter clean
flutter pub get
flutter run
```

### Testing with Search Location
- Open the resident app.
- Enter an address in the search bar (e.g., "San Rafael, Bulacan").
- The map will move to that location, and the barangay will be detected.
- Proceed with selfie, video, and submit.

**Important:** The search feature is **temporary** for testing. The app will still block out‑of‑bounds submissions via `_isLocationValid`.

---

## 17. Design System Reference

### Color Mapping

| Severity/Status | Color Token |
|-----------------|-------------|
| Critical / High | `SreaColors.critical` |
| Medium / Warning | `SreaColors.warning` |
| Low / Success | `SreaColors.low` / `SreaColors.success` |
| Pending | `SreaColors.warning` |
| Responding / In Progress | `SreaColors.high` |
| Resolved | `SreaColors.success` |
| Rejected | `SreaColors.error` |

### Icon Mapping

| Screen | Icon |
|--------|------|
| Alerts | `Icons.warning_amber_rounded` |
| Announcements | `Icons.announcement_outlined` |
| Traffic Advisories | `Icons.traffic_rounded` |
| Incident Reports (My Reports) | Status dot + photo thumbnail |

### Badge Colors

- **Background:** `color.withOpacity(0.12)`
- **Border:** `color.withOpacity(0.3)`
- **Text:** `color`
- **Dot (detail screens):** `color`

### My Reports Tabs

- **Background:** White (`SreaColors.surface`)
- **Selected text:** `SreaColors.primary` (blue)
- **Unselected text:** `SreaColors.textSecondary` (gray)
- **Selected underline:** `SreaColors.primary` (blue)
- **Unselected underline:** Transparent

---

## 18. How to Use This Memory File

- **Start a new chat** with a fresh assistant.
- **Paste this entire document.**
- The assistant will instantly have full context: all code changes, database schema, routes, controller logic, Flutter screens, known issues, and next steps.
- You can then ask for specific code snippets, debugging help, or guidance on next features.

**Critical Reminders:** 
- Active resident report screen is `report_incident_screen.dart` (with search). The `.d` file is original reference only.
- Do not remove search location, cleartext traffic, or submission parsing logic.
- For video playback, ensure `video_player` is added and `ApiService.getFullImageUrl()` is used.
- All badges now use the unified styling (colored background + border).
- All detail screens follow the same structure: Title + Badge → Metadata → Divider → Description → Extras.
- All times are in 12‑hour format with AM/PM.
- Selfie photos are tappable for full‑screen viewing.
- My Reports uses custom tabs (not `TabController`) to avoid initialization errors.

---

**End of Memory File** – Save as `SREA_COMPLETE_MEMORY_v3.2.md`