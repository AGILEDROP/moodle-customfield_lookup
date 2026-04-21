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
 * Entity provider interface
 *
 * @package   customfield_lookup
 * @copyright Agiledrop, 2026 <developer@agiledrop.com>
 * @author    Matej Pal <matej.pal@agiledrop.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customfield_lookup\entity;

defined('MOODLE_INTERNAL') || die;

interface entity_provider {

    /**
     * Return the entity type key.
     *
     * @return string
     */
    public static function get_type(): string;

    /**
     * Return the display name for this entity type.
     *
     * @return string
     */
    public static function get_display_name(): string;

    /**
     * Add entity-specific config fields to the field configuration form.
     *
     * @param \MoodleQuickForm $mform
     * @param array $configdata
     */
    public static function config_form_definition(\MoodleQuickForm $mform, array $configdata): void;

    /**
     * Search entities matching a query string.
     *
     * @param string $query
     * @param array $configdata
     * @param int $limit
     * @return array Array of ['id' => int, 'fullname' => string]
     */
    public static function search(string $query, array $configdata, int $limit = 20): array;

    /**
     * Get display value for one or more entity IDs.
     *
     * @param array $ids
     * @param array $configdata
     * @return array Array of id => display string
     */
    public static function get_display_values(array $ids, array $configdata = []): array;

    /**
     * Validate that the given IDs are valid and selectable.
     *
     * @param array $ids
     * @param array $configdata
     * @return array Array of invalid IDs
     */
    public static function validate_ids(array $ids, array $configdata): array;
}
