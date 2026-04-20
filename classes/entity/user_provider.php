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

defined('MOODLE_INTERNAL') || die;

class user_provider implements entity_provider {

    public static function get_type(): string {
        return 'user';
    }

    public static function get_display_name(): string {
        return get_string('entitytype_user', 'customfield_lookup');
    }

    public static function config_form_definition(\MoodleQuickForm $mform, array $configdata): void {
        $roles = role_fix_names(get_all_roles(), \context_system::instance(), ROLENAME_ORIGINAL);
        $roleoptions = [];
        foreach ($roles as $role) {
            $roleoptions[$role->id] = $role->localname;
        }

        $mform->addElement('autocomplete', 'configdata[roles]',
            get_string('rolefilter', 'customfield_lookup'),
            $roleoptions,
            ['multiple' => true]);
        $mform->addHelpButton('configdata[roles]', 'rolefilter', 'customfield_lookup');
    }

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
            list($insql, $inparams) = $DB->get_in_or_equal($roleids, SQL_PARAMS_NAMED, 'role');
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
                'fullname' => fullname($record),
            ];
        }

        return $results;
    }

    public static function get_display_values(array $ids): array {
        global $DB;

        if (empty($ids)) {
            return [];
        }

        list($insql, $params) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED, 'uid');
        $records = $DB->get_records_select('user', "id $insql", $params);

        $values = [];
        foreach ($records as $record) {
            $values[$record->id] = fullname($record);
        }

        return $values;
    }

    public static function validate_ids(array $ids, array $configdata): array {
        global $DB;

        if (empty($ids)) {
            return [];
        }

        list($insql, $params) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED, 'uid');
        $existing = $DB->get_fieldset_select('user', 'id', "id $insql AND deleted = 0", $params);

        return array_diff($ids, $existing);
    }
}