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
 * This file overwrites the file_pluginfile function in lib/filelib.
 *
 * @package    profilefield_samoocv
 * @copyright 2017 Planificacion de Entornos Tecnologicos SL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

defined('SAMOOCV_VISIBLE_ALL') || define ('SAMOOCV_VISIBLE_ALL', '2');
defined('SAMOOCV_VISIBLE_PRIVATE') || define ('SAMOOCV_VISIBLE_PRIVATE', '1');
defined('SAMOOCV_VISIBLE_NONE') || define ('SAMOOCV_VISIBLE_NONE', '0');

/**
 * This function delegates file serving to individual plugins
 *
 * @param object $course course object
 * @param object $cm course module object
 * @param context $context Context
 * @param string $filearea file area name
 * @param string $args relative path
 * @param bool $forcedownload If true forces download of file
 */
function profilefield_samoocv_pluginfile($course, $cm, context $context, $filearea, $args, $forcedownload) {
    global $DB;
    global $USER;

    if ($context->contextlevel != CONTEXT_USER) {
        return false;
    }

    if (strpos($filearea, 'files_') !== 0) {
        return false;
    }

    require_login($course, false, $cm);

    $fieldid = substr($filearea, strlen('files_'));
    $field = $DB->get_record('user_info_field', array('id' => $fieldid));

    if ($field->visible != SAMOOCV_VISIBLE_ALL) {
        if ($field->visible == SAMOOCV_VISIBLE_PRIVATE) {
            if ($context->instanceid != $USER->id) {
                if (!has_capability('moodle/user:viewalldetails', $context)) {
                    return false;
                }
            }
        } else if (!has_capability('moodle/user:viewalldetails', $context)) {
            return false;
        }
    }

    array_shift($args);
    $relativepath = implode('/', $args);
    $fullpath = "/{$context->id}/profilefield_samoocv/$filearea/0/$relativepath";
    $fs = get_file_storage();

    if (!($file = $fs->get_file_by_hash(sha1($fullpath))) || $file->is_directory()) {
        return false;
    }
    send_stored_file($file, 0, 0, $forcedownload);
}