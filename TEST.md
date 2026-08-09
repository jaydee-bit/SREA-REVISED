```markdown
# SREA App – Testing Guide for Verification States

This guide explains how to test the different user states (Unverified, Pending, Verified, Non‑resident) by toggling mock data in `home_screen.dart` and `profile_screen.dart`.

---

## 1. Files to modify

- **`mobile_user_app/lib/screens/home_screen.dart`** – controls home screen banners and incident report lock.
- **`mobile_user_app/lib/screens/profile_screen.dart`** – controls what you see in the profile screen (address, ID, verification badge).

---

## 2. Key flags in `home_screen.dart`

Inside `_HomeScreenState`, locate these three booleans:

```dart
final bool _isResident = true;           // true = resident, false = non-resident
final bool _hasAddressAndId = false;     // false = unverified, true = pending
final bool _isVerified = false;          // false = not admin-verified, true = verified
```

> **Note:** These flags also affect the sidebar because `isVerified: _isVerified` is passed to `SreaSidebar`.

---

## 3. Mock data in `profile_screen.dart`

Inside `_ProfileScreenState`, locate the `_user` map.  
Change the values according to the state you want to test:

| State | `barangay` | `street` | `validIdType` | `validIdPhoto` | `isVerified` |
|-------|------------|----------|---------------|----------------|--------------|
| Unverified | `''` (empty) | `''` (empty) | `''` (empty) | `null` | `false` |
| Pending | `'Poblacion'` | `'Rizal St. #123'` | `'Driver\'s License'` | `'uploaded.jpg'` | `false` |
| Verified | `'Poblacion'` | `'Rizal St. #123'` | `'Driver\'s License'` | `'uploaded.jpg'` | `true` |

Example for **Unverified**:

```dart
final Map<String, dynamic> _user = {
  'firstName': 'Leon',
  'middleName': 'Scott',
  'lastName': 'Kennedy',
  'email': 'leon.kennedy@example.com',
  'phone': '09123456789',
  'gender': 'Male',
  'birthDate': '1990-05-15',
  'barangay': '',              // empty
  'street': '',                // empty
  'isVerified': false,
  'role': 'resident',
  'validIdType': '',           // empty
  'validIdPhoto': null,
  'profileImage': null,
  'isProfileComplete': false,
};
```

---

## 4. Testing each state

### 🔹 State 1: Unverified Resident (no address/ID, not pending)

**Goal:** Blue banner “Complete your profile” appears; incident report shows “Complete Now” dialog; profile shows “Not set” for address/ID.

**Changes in `home_screen.dart`:**

```dart
final bool _isResident = true;
final bool _hasAddressAndId = false;
final bool _isVerified = false;
```

**Changes in `profile_screen.dart`:** Use the **Unverified** mock data from the table above (all address/ID fields empty, `isVerified = false`).

**Expected behaviour:**
- Home screen → Blue banner “Complete your profile”
- Tap “Report Incident” → Lock dialog with “Complete Now” button → opens `CompleteProfileScreen`
- Sidebar → Shows “Pending Verification” (because `_isVerified = false`)
- Profile screen → Address & Verification section shows “Not set” for Barangay, Street, ID Type, ID Photo; header shows “Pending Verification”

---

### 🔹 State 2: Pending Resident (address/ID submitted, waiting for admin)

**Goal:** Yellow banner “Verification pending” appears; incident report shows “Verification Pending” dialog; profile shows filled address/ID (read‑only).

**Changes in `home_screen.dart`:**

```dart
final bool _isResident = true;
final bool _hasAddressAndId = true;   // ← changed to true
final bool _isVerified = false;
```

**Changes in `profile_screen.dart`:** Use the **Pending** mock data (address/ID filled, `isVerified = false`).

**Expected behaviour:**
- Home screen → Yellow banner “Verification pending”
- Tap “Report Incident” → Lock dialog with “OK” button (no action)
- Sidebar → Shows “Pending Verification”
- Profile screen → Address & Verification section shows filled values (read‑only); header shows “Pending Verification”

---

### 🔹 State 3: Verified Resident (full access)

**Goal:** No banner; incident report opens the report form; profile shows filled address/ID (read‑only) with “Verified Account” badge.

**Changes in `home_screen.dart`:**

```dart
final bool _isResident = true;
final bool _hasAddressAndId = true;
final bool _isVerified = true;        // ← changed to true
```

**Changes in `profile_screen.dart`:** Use the **Verified** mock data (address/ID filled, `isVerified = true`).

**Expected behaviour:**
- Home screen → No banner
- Tap “Report Incident” → Opens `IncidentReportScreen` (shows placeholder form)
- Sidebar → Shows “Verified” badge
- Profile screen → Header shows “Verified Account”; address/ID are read‑only

---

### 🔹 State 4: Non‑Resident

**Goal:** No banner; incident report shows snackbar “Only San Rafael residents can report incidents”; no address/ID fields in profile.

**Changes in `home_screen.dart`:**

```dart
final bool _isResident = false;
final bool _hasAddressAndId = false;
final bool _isVerified = false;
```

**Changes in `profile_screen.dart`:** Not relevant (non‑residents don’t see address/ID section). You can keep any mock data.

**Expected behaviour:**
- Home screen → No banner
- Tap “Report Incident” → Snackbar message “Only San Rafael residents can report incidents”
- Sidebar → Shows no verification badge (or pending? depending on `_isVerified`; set `_isVerified = false` for consistency)
- Profile screen → Address & Verification section **not shown** (because `isResident` condition in profile screen uses `_user['role'] == 'resident'` – ensure `_user['role']` is set to `'non_resident'` in `profile_screen.dart` if you want to test that UI)

> **Note:** For a complete non‑resident test in the profile screen, also change `_user['role'] = 'non_resident'` in `profile_screen.dart`. Otherwise the address/ID section will still appear.

---

## 5. How to apply changes and test

1. **Edit the files** (`home_screen.dart` and optionally `profile_screen.dart`) with the desired mock data for the state you want to test.
2. **Save all files**.
3. **Hot restart** the app (stop and run again, or press `R` in the terminal). Hot reload may not reset state correctly.
4. **Observe** the home screen banner, the sidebar badge, the incident report dialog, and the profile screen.

---

## 6. Important notes

- The **sidebar** verification badge is controlled by the `isVerified` flag passed from `home_screen.dart`. No extra changes needed.
- The **profile screen** header verification badge is controlled by `_user['isVerified']` inside `profile_screen.dart`. Update it separately if you want consistency.
- The **address/ID fields** in the profile screen are **read‑only** after submission. This is by design – they cannot be edited once submitted.
- The **`CompleteProfileScreen`** is only shown when the user taps the “Complete Now” button from the lock dialog. It is **not** part of the profile screen.

---

## 7. Quick reference table

| State               | `_isResident` | `_hasAddressAndId` | `_isVerified` | Profile `_user` data               |
|---------------------|---------------|--------------------|---------------|-------------------------------------|
| Unverified resident | `true`        | `false`            | `false`       | empty address/ID, `isVerified=false` |
| Pending resident    | `true`        | `true`             | `false`       | filled address/ID, `isVerified=false` |
| Verified resident   | `true`        | `true`             | `true`        | filled address/ID, `isVerified=true`  |
| Non‑resident        | `false`       | `false`            | `false`       | role = `'non_resident'`              |

Use this table to set the correct values before each test.
```

Save this as `Test.md` in your project root. It contains everything you need to test all four states, including exactly which lines to change and what to expect.