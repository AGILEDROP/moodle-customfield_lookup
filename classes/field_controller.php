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

/**
 * Field controller for the lookup custom field.
 */
class field_controller extends \core_customfield\field_controller {
    /**
     * Plugin type.
     */
    public const TYPE = 'lookup';

    /**
     * Registered entity provider classes, keyed by entity type.
     *
     * @var array<string, class-string<entity_provider>>
     */
    private const PROVIDERS = [
        'user' => user_provider::class,
    ];

    /**
     * Resolve the provider class for a given entity type key.
     *
     * @param string $type
     * @return string|null Fully qualified class name or null.
     */
    public static function get_provider_class(string $type): ?string {
        return self::PROVIDERS[$type] ?? null;
    }

    /**
     * Return the registered entity providers as a select-friendly map.
     *
     * @return array type key => display name
     */
    public static function get_entity_providers(): array {
        $options = [];
        foreach (self::PROVIDERS as $type => $class) {
            $options[$type] = $class::get_display_name();
        }
        return $options;
    }

    /**
     * Add fields for editing a lookup field.
     *
     * @param \MoodleQuickForm $mform
     */
    public function config_form_definition(\MoodleQuickForm $mform) {
        $mform->addElement(
            'header',
            'header_specificsettings',
            get_string('specificsettings', 'customfield_lookup')
        );
        $mform->setExpanded('header_specificsettings', true);

        // Entity type selector.
        $mform->addElement(
            'select',
            'configdata[entitytype]',
            get_string('entitytype', 'customfield_lookup'),
            self::get_entity_providers()
        );
        $mform->setType('configdata[entitytype]', PARAM_ALPHA);

        // Multi-select toggle.
        $mform->addElement(
            'selectyesno',
            'configdata[multiselect]',
            get_string('multiselect', 'customfield_lookup')
        );
        $mform->setType('configdata[multiselect]', PARAM_INT);

        // Entity-specific config fields.
        $configdata = $this->get('configdata') ?: [];
        foreach (self::PROVIDERS as $providerclass) {
            $providerclass::config_form_definition($mform, $configdata);
        }
    }
}
