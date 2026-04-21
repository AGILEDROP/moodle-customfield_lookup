# Customfield Lookup

Customfield Lookup is a Moodle custom field type plugin that lets administrators select entities through an autocomplete control.  
The current implementation focuses on user lookup and supports both single and multi-select values.

It is designed for custom field use cases where IDs are stored internally but human-readable labels are shown in forms and exports.

## Key Features

- User Entity Lookup: Supports user-based lookup through the `Users` entity type.
- Autocomplete Search: AJAX-powered search for fast user selection in large sites.
- Single or Multi-Select: Optional multiple selection mode per field configuration.
- Role-Based Filtering: Limit selectable users by one or more selected roles.
- Configurable Display Format: Show users as full name, first name, last name, or combined formats.
- Readable Export Values: Stored IDs are rendered as user labels, with multi-values separated by commas.
- Moodle-Native Integration: Built on `core_customfield` APIs and Moodle form components.

## Requirements

- Moodle 4.5 or higher
- PHP 8.1 or higher
- No additional plugin dependencies

## Installation

### Installing via uploaded ZIP file

1. Log in as an admin and go to `Site administration > Plugins > Install plugins`.
2. Upload the ZIP file with the plugin code.
3. Check the plugin validation report and finish the installation.

### Installing manually

Copy the plugin directory to:

`{your/moodle/dirroot}/customfield/field/lookup`

Then visit `Site administration > Notifications` to complete installation.  
Alternatively, run:

```bash
php admin/cli/upgrade.php
```

## Usage

### Creating a lookup custom field

1. Open the relevant custom fields area (for example course custom fields).
2. Add a new field of type `Lookup`.
3. Configure field settings:
   - Entity type (currently `Users`)
   - Allow multiple selections
   - Filter by roles (optional)
   - Display format
4. Save the field.

### Filling field values

1. Open an item that contains this custom field (for example a course edit form).
2. Search users in the autocomplete input.
3. Select one or more users based on field configuration.
4. Save changes.

### Behavior notes

- When multi-select is enabled, selected user IDs are stored as a comma-separated list.
- In form display and export output, selected values are converted to user labels.
- Multiple selected users are shown as a comma-separated list of labels.
- If role filter is empty, all active non-deleted users are searchable.

## Notes

- The plugin stores its field data in Moodle custom field storage (`charvalue`).
- Search uses a custom AJAX service: `customfield_lookup_search`.
- The plugin excludes deleted and suspended users from search results.
- Existing fields default to `Full name` display format when no format is explicitly configured.

## Privacy

This plugin does not add separate personal data storage beyond Moodle core custom field tables.  
It references existing Moodle user records for lookup and display.

## License

2026 Agiledrop ltd. <developer@agiledrop.com>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program. If not, see https://www.gnu.org/licenses/.
