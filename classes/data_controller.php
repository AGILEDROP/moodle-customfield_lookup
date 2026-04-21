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
 * Customfield lookup data controller
 *
 * @package   customfield_lookup
 * @copyright Agiledrop, 2026 <developer@agiledrop.com>
 * @author    Matej Pal <matej.pal@agiledrop.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customfield_lookup;

/**
 * Data controller for the lookup custom field.
 */
class data_controller extends \core_customfield\data_controller {
    /**
     * Return the name of the field where the information is stored.
     *
     * @return string
     */
    public function datafield(): string {
        return 'charvalue';
    }

    /**
     * Add fields for editing a lookup field instance.
     *
     * @param \MoodleQuickForm $mform
     */
    public function instance_form_definition(\MoodleQuickForm $mform) {
        $field = $this->get_field();
        $configdata = $field->get('configdata');
        $config = $configdata ?: [];
        $elementname = $this->get_form_element_name();
        $multiselect = !empty($config['multiselect']);

        // Pre-populate options for already selected values.
        $currentvalue = $this->get_value();
        $options = [];
        if (!empty($currentvalue)) {
            $ids = explode(',', $currentvalue);
            $ids = array_map('intval', $ids);
            $ids = array_filter($ids);

            $entitytype = $config['entitytype'] ?? 'user';
            $providerclass = field_controller::get_provider_class($entitytype);
            if ($providerclass && !empty($ids)) {
                $options = $providerclass::get_display_values($ids, $config);
            }
        }

        $mform->addElement(
            'autocomplete',
            $elementname,
            $field->get_formatted_name(),
            $options,
            [
                'ajax' => 'customfield_lookup/datasource',
                'multiple' => $multiselect,
                'data-fieldid' => $field->get('id'),
            ]
        );

        if ($field->get_configdata_property('required')) {
            $mform->addRule($elementname, null, 'required', null, 'client');
        }
    }

    /**
     * Saves the data coming from form.
     *
     * For multi-select, the autocomplete returns an array of IDs.
     * We store them as a comma-separated string.
     *
     * @param \stdClass $datanew data coming from the form
     */
    public function instance_form_save(\stdClass $datanew) {
        $elementname = $this->get_form_element_name();
        if (!property_exists($datanew, $elementname)) {
            return;
        }

        $value = $datanew->{$elementname};

        // Autocomplete multi-select returns an array.
        if (is_array($value)) {
            $value = implode(',', array_filter($value));
        }

        $this->data->set($this->datafield(), $value);
        $this->data->set('value', $value);
        $this->save();
    }

    /**
     * Prepares the custom field data to pass to mform->set_data().
     *
     * For multi-select, we need to convert the comma-separated string back to an array.
     *
     * @param \stdClass $instance
     */
    public function instance_form_before_set_data(\stdClass $instance) {
        $value = $this->get_value();
        $config = $this->get_field()->get('configdata');

        if (!empty($config['multiselect']) && !empty($value)) {
            $instance->{$this->get_form_element_name()} = explode(',', $value);
        } else {
            $instance->{$this->get_form_element_name()} = $value;
        }
    }

    /**
     * Returns the default value as it would be stored in the database.
     *
     * @return mixed
     */
    public function get_default_value() {
        return '';
    }

    /**
     * Returns value in a human-readable format.
     *
     * @return mixed|null value or null if empty
     */
    public function export_value() {
        $value = $this->get_value();
        if (empty($value)) {
            return null;
        }

        $ids = explode(',', $value);
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids);

        if (empty($ids)) {
            return null;
        }

        $config = $this->get_field()->get('configdata');
        $entitytype = $config['entitytype'] ?? 'user';
        $providerclass = field_controller::get_provider_class($entitytype);

        if (!$providerclass) {
            return $value;
        }

        $displayvalues = $providerclass::get_display_values($ids, $config);

        return implode(', ', $displayvalues);
    }
}
