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
 * External function for searching lookup entities.
 *
 * @package   customfield_lookup
 * @copyright Agiledrop, 2026 <developer@agiledrop.com>
 * @author    Matej Pal <matej.pal@agiledrop.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customfield_lookup\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use customfield_lookup\field_controller;

/**
 * External function that searches entities for the lookup custom field autocomplete.
 */
class search extends external_api {
    /**
     * Describes the parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'query' => new external_value(PARAM_RAW, 'The search query'),
            'fieldid' => new external_value(PARAM_INT, 'The custom field ID'),
        ]);
    }

    /**
     * Search for entities.
     *
     * @param string $query
     * @param int $fieldid
     * @return array
     */
    public static function execute(string $query, int $fieldid): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'query' => $query,
            'fieldid' => $fieldid,
        ]);
        $query = clean_param($params['query'], PARAM_TEXT);
        $fieldid = $params['fieldid'];

        $context = \context_system::instance();
        self::validate_context($context);

        // Load the field and its config.
        $field = \core_customfield\field_controller::create($fieldid);
        $configdata = $field->get('configdata') ?: [];

        $entitytype = $configdata['entitytype'] ?? 'user';
        $providerclass = field_controller::get_provider_class($entitytype);

        if (!$providerclass) {
            return ['results' => []];
        }

        $results = $providerclass::search($query, $configdata);

        return ['results' => $results];
    }

    /**
     * Describes the return value.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'results' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Entity ID'),
                    'fullname' => new external_value(PARAM_RAW, 'Display name'),
                ])
            ),
        ]);
    }
}
