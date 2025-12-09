# WordPress Location Collection Plugin

## Requirements
* WordPress 6.8.3 or newer
* Gravity Forms 2.9.23 or newer
* PHP 8.3 or newer
* Radar API account and API key

## Primary Objectives
* [ ] Collect city/state information during user registration
* [ ] Validate location data against real US locations
* [ ] Generate daily reports for affiliate analytics
* [ ] Migrate existing ZIP code data to location format
* [x] Support international users with country selection (handled by Radar)
* [ ] Ability for Affiliates to view their data in 720 (https://720.nami.org/aggregate-reporting/create)
* [ ] ask are you ok with us sharing location

### Success Criteria
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

Technical specifications [here](https://www.notion.so/naminational/WordPress-Location-Collection-Plugin-Technical-Specification-2657631ddc6180e68a51d81b01f18417)
