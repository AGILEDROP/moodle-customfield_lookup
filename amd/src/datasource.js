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
 * AJAX datasource for the lookup custom field autocomplete.
 *
 * @module     customfield_lookup/datasource
 * @copyright  Agiledrop, 2026 <developer@agiledrop.com>
 * @author     Matej Pal <matej.pal@agiledrop.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';

/**
 * Fetch results from the server.
 *
 * @param {String} selector The selector of the autocomplete element.
 * @param {String} query The search query.
 * @param {Function} callback Callback to pass results to.
 * @param {Function} failure Callback on failure.
 */
export async function transport(selector, query, callback, failure) {
    const el = document.querySelector(selector);
    const fieldid = el ? el.dataset.fieldid : 0;

    try {
        const response = await Ajax.call([{
            methodname: 'customfield_lookup_search',
            args: {
                query: query,
                fieldid: fieldid,
            },
        }])[0];

        callback(response.results);
    } catch (e) {
        failure(e);
    }
}

/**
 * Process results for the autocomplete element.
 *
 * @param {String} selector The selector of the autocomplete element.
 * @param {Array} results The results from transport.
 * @return {Array} Processed results with value and label keys.
 */
export function processResults(selector, results) {
    return results.map(result => ({
        value: result.id,
        label: result.fullname,
    }));
}