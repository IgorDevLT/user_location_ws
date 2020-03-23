# Drupal user location module based on WS call

## Description

New mandatory field is being added to user profile registration form.
WebService is used to obtained municipality data.

## Installation

- Install module : you will be prompted with missing API Key
- Do not forget to add WS API key : admin/config/people/user-location-ws-settings
- Admin can alter final list of allowed municipalities per field settings : admin/config/people/accounts/fields/user.user.field_user_location_ws
- Set up location field in user account settings : admin/config/people/accounts/fields
- Set up location field display : admin/config/people/accounts/display
- Set up location field form display : admin/config/people/accounts/form-display

