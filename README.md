# WordPress Location Collection Plugin

## Requirements
* WordPress 6.8.3 or newer
* Gravity Forms 2.9.23 or newer
* Gravity Forms User Registration Add-On 3.2.6 or newer (if using user registration features)
* PHP 8.3 or newer
* MySQL 8.0 or newer
* Radar API account and API key

## Development Environment
* Local WordPress installation (Laravel Herd, Valet, Local by Flywheel, or any LAMP/LEMP stack, etc.) with Gravity Forms plugin activated.
* Access to Radar API for testing location data validation.

## Overview

The WordPress Location Collection Plugin integrates with Gravity Forms to collect and validate user location data during registration using the Radar API. It enhances user registration forms by adding location fields and ensuring the accuracy of the provided location information.

## Setup

A step-by-step guide to get the plugin running on your form. You'll need an administrator account in WordPress, and Gravity Forms must already be installed and active.

### 1. Activate the plugin

1. In the WordPress admin, go to **Plugins**.
2. Find **Location Data Collection** in the list and click **Activate**.
   - If it isn't in the list yet, install it first with **Plugins → Add New → Upload Plugin**, choose the plugin `.zip` file, then click **Activate**.

### 2. Get your Radar API key

1. Log in to your Radar account at [radar.com](https://radar.com).
2. Copy a **publishable** key — it begins with `prj_live_pk_` (live) or `prj_test_pk_` (test).

> ⚠️ **Do not use a secret key** (one that begins with `sk`). The publishable key is meant to be used in visitors' browsers and is safe here. A secret key must never be entered — the plugin will refuse to save it.

### 3. Enter the plugin settings

1. Go to **Settings → Location Data** in the WordPress admin menu.
2. **API Key** — paste your Radar publishable key.
3. **Gravity Forms Form ID** — pick the form you want location collection on from the dropdown.
4. **Limit to Country** *(optional)* — enter one or more two-letter country codes separated by commas (for example, `US`) to restrict address suggestions to those countries. Leave it blank to allow all countries.
5. Click **Save Changes**.

### 4. Tell the plugin which fields are which

The plugin needs to know which fields on your form are the **ZIP / postal code**, **city**, and **state**. The address autocomplete attaches to the ZIP / postal code field, and the city and state fields are filled in automatically when a visitor selects a suggestion.

You can identify each field in one of three ways. The plugin checks them **in this order** and uses the first one it finds:

1. **Admin Field Label** *(recommended)* — In the Gravity Forms editor, click the field, find **Admin Field Label** in its settings, and set it to `Zip`, `City`, or `State`. This label is only seen by administrators and is never translated, so it works on forms in any language.
2. **CSS class** — In the field's **Appearance** settings, add a **Custom CSS Class** of `radar-zip`, `radar-city`, or `radar-state`.
3. **Field label** — If you do nothing, the plugin reads the visible field label (for example, a field labeled "Zip Code" is treated as the ZIP field). This is the easy option for **English** forms, but it does **not** work on translated forms.

> 🌐 **Translated forms (e.g. Spanish):** the visible labels are translated, so the plugin can't recognize them by label. On a translated form, set the **Admin Field Label** (option 1) or a **CSS class** (option 2) on the ZIP, city, and state fields — those identifiers are not translated, so the fields are recognized in every language.

> 📍 **Using a Gravity Forms "Address" field?** No tagging is needed. The plugin recognizes the street, city, state, ZIP, and country parts automatically by their fixed positions in the Address field, so it works in any language without admin labels or CSS classes. (The three options above are only for forms built from separate single-line fields.)

Recognized keywords (not case-sensitive): **Zip** or **Postal** → ZIP / postal code · **City** → city · **State** or **Province** → state · **Street** → street address · **Country** → country.

### 5. Test it

1. Open the page that contains your form on the front end of the site.
2. Start typing in the ZIP / postal code field — address suggestions should appear.
3. Pick a suggestion and confirm the city and state fields fill in automatically.

## Documentation
* [Workflow Guide](docs/WORKFLOW.md)
<!-- * [User Guide](docs/USER_GUIDE.md) -->
<!-- * [Developer Guide](docs/DEVELOPER_GUIDE.md) -->
* [Contributing Guide](docs/CONTRIBUTING.md)
<!-- * [Changelog](docs/CHANGELOG.md) -->
