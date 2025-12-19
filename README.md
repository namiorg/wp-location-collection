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

## Build and Deployment
<!-- [![Build Status](https://img.shields.io/badge/build-passing-brightgreen.svg)](https://example.com/build-status) -->
In order to build and deploy the WordPress Location Collection Plugin, follow these steps:
1. Clone the repository to your local development environment.
2. Install necessary dependencies using Composer.
3. Make your code changes and commit them to your local repository.
4. Push your changes to the remote repository.
5. Tag a new release in Git and push the tag to the remote repository, example:
	```
	git tag -a v1.0.0 -m "Release version 1.0.0"
	git push origin v1.0.0
	```
6. Navigate to GitHub Actions workflow page to verify that the build passes successfully.
7. Then navigate to the Releases page on GitHub to confirm that the new release is available for download.
8. Download the release ZIP file and upload it to your WordPress site via the Plugins > Add New > Upload Plugin interface. (Note: do not download the source code ZIP from GitHub, use the release ZIP instead.)
9. Now navigate to the Plugins page in your WordPress admin dashboard and click on the Add Plugin button.
10. Click on the Upload Plugin button and select the ZIP file you downloaded in step 8.
11. Click on the Install Now button to install the plugin.
12. After installation, click on the Activate Plugin button to activate the plugin on your WordPress site.
13. Configure the plugin settings by navigating to Settings > Location Data in your WordPress admin dashboard.
14. Enter your Radar API key and select the Gravity Forms form you want to use for location collection.
15. Save your settings and test the location collection functionality on your selected form.

## Overview
The WordPress Location Collection Plugin integrates with Gravity Forms to collect and validate user location data during registration

## Primary Objectives
* [ ] Collect city/state information during user registration
* [ ] Validate location data against real US locations
* [ ] Generate daily reports for affiliate analytics
* [ ] Migrate existing ZIP code data to location format
* [x] Support international users with country selection (handled by Radar)
* [ ] ask are you ok with us sharing location

## Success Criteria
* [x] Secure, accessible registration form integration.
* [ ] Real-time location validation with fallback.
	- If Radar API is unreachable, allow manual entry with a warning message?
* [x] WCAG 2.1 AA compliance (this is handled by WordPress and Gravity Forms).
* [ ] Daily reporting capability for course analytics.
* [ ] Migration of legacy ZIP code data.
* [x] Data Team requirement wants to know: if a zip code is the only info required, city and state is determined automatically.
	- If zip code is provided, Radar automatically determines city and state. These can be recorded via additional fields in the form. These fields can be hidden.

## Data Flow

1. User enters their signup information.
2. In the first address field, user inputs location data (address, coordinates).
3. An autocomplete menu powered by Radar API suggests locations as the user types.
4. User selects a location from the list of suggestions.
5. This field, and any other location-based fields, are pre-filled with the selected location data.
6. Upon form submission, the plugin captures the location data in the form's entry.
7. This data is also saved as part of the User's metadata in WordPress.

## Implementation Steps
* Form data is captured automatically by GravityForms—no additional development is needed.
* Radar API handles the address validation via autocomplete.
* [ ] Log errors and handle API failures gracefully.

## Security
* Store Radar API key securely is stored in the WordPress data (`wp_options` table).
* Form field validation and sanitization is handled using Gravity Forms built-in functions.
* [ ] Ensure compliance with GDPR and other data protection regulations.

## Configuration How-To
* Navigate to **Settings > Location Data**
* Enter your Radar API key in the provided field.
* Select which form in Gravity Forms should use the location collection feature.
* Field-mapping happens automatically based on field types (address, coordinates) so no additional configuration is needed.

## Testing
* [ ] Unit and integration tests for API calls.
* [ ] Test with various location inputs.
* [ ] Handle edge cases (invalid addresses, API downtime).

## Documentation
* [x] Setup instructions for admins.
* [x] Example form field mappings.
* [ ] Troubleshooting guide.
