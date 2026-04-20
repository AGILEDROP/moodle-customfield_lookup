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
 * Customfield lookup field controller
 *
 * @package   customfield_lookup
 * @copyright Agiledrop, 2026 <developer@agiledrop.com>
 * @author    Matej Pal <matej.pal@agiledrop.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customfield_lookup;

use customfield_lookup\entity\entity_provider;
use customfield_lookup\entity\user_provider;

defined('MOODLE_INTERNAL') || die;

class field_controller extends \core_customfield\field_controller {

    /**
     * Plugin type.
     */
    const TYPE = 'lookup';

    /**
     * Return the registered entity providers.
     *
     * @return array type key => display name
     */
    public static function get_entity_providers(): array {
        return [
            user_provider::get_type() => user_provider::get_display_name(),
        ];
    }

    /**
     * Resolve the provider class for a given entity type key.
     *
     * @param string $type
     * @return string|null Fully qualified class name or null.
     */
    public static function get_provider_class(string $type): ?string {
        $map = [
            'user' => user_provider::class,
        ];
        return $map[$type] ?? null;
    }

    /**
     * Add fields for editing a lookup field.
     *
     * @param \MoodleQuickForm $mform
     */
    public function config_form_definition(\MoodleQuickForm $mform) {
        $mform->addElement('header', 'header_specificsettings',
            get_string('specificsettings', 'customfield_lookup'));
        $mform->setExpanded('header_specificsettings', true);

        // Entity type selector.
        $mform->addElement('select', 'configdata[entitytype]',
            get_string('entitytype', 'customfield_lookup'),
            self::get_entity_providers());
        $mform->setType('configdata[entitytype]', PARAM_ALPHA);

        // Multi-select toggle.
        $mform->addElement('selectyesno', 'configdata[multiselect]',
            get_string('multiselect', 'customfield_lookup'));
        $mform->setType('configdata[multiselect]', PARAM_INT);

        // Entity-specific config fields.
        $configdata = $this->get('configdata') ?: [];
        foreach (self::get_entity_providers() as $type => $name) {
            $providerclass = self::get_provider_class($type);
            if ($providerclass) {
                $providerclass::config_form_definition($mform, $configdata);
            }
        }
    }
}