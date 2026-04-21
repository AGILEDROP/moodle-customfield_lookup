<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * User entity provider
 *
 * @package   customfield_lookup
 * @copyright Agiledrop, 2026 <developer@agiledrop.com>
 * @author    Matej Pal <matej.pal@agiledrop.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customfield_lookup\entity;

/**
 * Entity provider that exposes Moodle users to the lookup custom field.
 */
class user_provider implements entity_provider {
    /**
     * Return the entity type key.
     *
     * @return string
     */
    public static function get_type(): string {
        return 'user';
    }

    /**
     * Return the human readable name for the user entity type.
     *
     * @return string
     */
    public static function get_display_name(): string {
        return get_string('entitytype_user', 'customfield_lookup');
    }

    /**
     * Add user-specific configuration fields to the field configuration form.
     *
     * @param \MoodleQuickForm $mform
     * @param array $configdata
     */
    public static function config_form_definition(\MoodleQuickForm $mform, array $configdata): void {
        $roles = role_fix_names(get_all_roles(), \context_system::instance(), ROLENAME_ORIGINAL);
        $roleoptions = [];
        foreach ($roles as $role) {
            $roleoptions[$role->id] = $role->localname;
        }

        $mform->addElement(
            'autocomplete',
            'configdata[roles]',
            get_string('rolefilter', 'customfield_lookup'),
            $roleoptions,
            ['multiple' => true]
        );
        $mform->addHelpButton('configdata[roles]', 'rolefilter', 'customfield_lookup');

        $displayoptions = [
            'fullname' => get_string('displayformat_fullname', 'customfield_lookup'),
            'firstname' => get_string('displayformat_firstname', 'customfield_lookup'),
            'lastname' => get_string('displayformat_lastname', 'customfield_lookup'),
            'firstname_lastname' => get_string('displayformat_firstname_lastname', 'customfield_lookup'),
            'lastname_firstname' => get_string('displayformat_lastname_firstname', 'customfield_lookup'),
        ];

        $mform->addElement(
            'select',
            'configdata[displayformat]',
            get_string('displayformat', 'customfield_lookup'),
            $displayoptions
        );
        $mform->setType('configdata[displayformat]', PARAM_ALPHA);

        if (empty($configdata['displayformat'])) {
            $mform->setDefault('configdata[displayformat]', 'fullname');
        }
    }

    /**
     * Search users matching the given query, filtered by the configured roles.
     *
     * @param string $query
     * @param array $configdata
     * @param int $limit
     * @return array
     */
    public static function search(string $query, array $configdata, int $limit = 20): array {
        global $DB;

        $params = [];
        $whereclauses = ['u.deleted = 0', 'u.suspended = 0', 'u.id > 2'];

        // Name search.
        $fullname = $DB->sql_fullname('u.firstname', 'u.lastname');
        $whereclauses[] = $DB->sql_like($fullname, ':query', false);
        $params['query'] = '%' . $DB->sql_like_escape($query) . '%';

        // Role filter.
        $joinsql = '';
        if (!empty($configdata['roles'])) {
            $roleids = $configdata['roles'];
            [$insql, $inparams] = $DB->get_in_or_equal($roleids, SQL_PARAMS_NAMED, 'role');
            $joinsql = "JOIN {role_assignments} ra ON ra.userid = u.id AND ra.roleid $insql";
            $params = array_merge($params, $inparams);
        }

        $sql = "SELECT DISTINCT u.id, u.firstname, u.lastname, u.email
                  FROM {user} u
                       $joinsql
                 WHERE " . implode(' AND ', $whereclauses) . "
              ORDER BY u.lastname, u.firstname";

        $records = $DB->get_records_sql($sql, $params, 0, $limit);

        $results = [];
        foreach ($records as $record) {
            $results[] = [
                'id' => (int) $record->id,
                'fullname' => self::format_user_label($record, $configdata),
            ];
        }

        return $results;
    }

    /**
     * Return human readable labels for the given user IDs.
     *
     * @param array $ids
     * @param array $configdata
     * @return array id => display string
     */
    public static function get_display_values(array $ids, array $configdata = []): array {
        global $DB;

        if (empty($ids)) {
            return [];
        }

        [$insql, $params] = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED, 'uid');
        $records = $DB->get_records_select('user', "id $insql", $params);

        $values = [];
        foreach ($records as $record) {
            $values[$record->id] = self::format_user_label($record, $configdata);
        }

        return $values;
    }

    /**
     * Return any of the supplied user IDs that do not exist or have been deleted.
     *
     * @param array $ids
     * @param array $configdata
     * @return array invalid IDs
     */
    public static function validate_ids(array $ids, array $configdata): array {
        global $DB;

        if (empty($ids)) {
            return [];
        }

        [$insql, $params] = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED, 'uid');
        $existing = $DB->get_fieldset_select('user', 'id', "id $insql AND deleted = 0", $params);

        return array_diff($ids, $existing);
    }

    /**
     * Format a user record according to the configured display format.
     *
     * @param \stdClass $user
     * @param array $configdata
     * @return string
     */
    private static function format_user_label(\stdClass $user, array $configdata): string {
        $format = $configdata['displayformat'] ?? 'fullname';

        switch ($format) {
            case 'firstname':
                return (string) $user->firstname;
            case 'lastname':
                return (string) $user->lastname;
            case 'firstname_lastname':
                return trim($user->firstname . ' ' . $user->lastname);
            case 'lastname_firstname':
                return trim($user->lastname . ', ' . $user->firstname);
            case 'fullname':
            default:
                return fullname($user);
        }
    }
}
