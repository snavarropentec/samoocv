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
 * This file contains the samoocv profile field class.
 *
 * @package profilefield_samoocv
 * @copyright 2017 Planificacion de Entornos Tecnologicos SL
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Handles displaying and editing the cv field.
 *
 * @copyright 2017 Planificacion de Entornos Tecnologicos SL
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
class profile_field_samoocv extends profile_field_base {

    /**
     * Constructor
     *
     * Pulls out the options from the database and sets the
     * corresponding key for the data if it exists
     *
     * @param int $fieldid id of user profile field
     * @param int $userid id of user
     */
    public function __construct($fieldid = 0, $userid = 0) {
        global $DB;

        parent::__construct($fieldid, $userid);

        if (!empty($this->field)) {
            $datafield = $DB->get_field('user_info_data', 'data', array('userid' => $this->userid, 'fieldid' => $this->fieldid));
            if ($datafield !== false) {
                $this->data = $datafield;
            } else {
                $this->data = $this->field->defaultdata;
            }
        }
    }

    /**
     * Add the field to the Other Fields section in edit user profile
     *
     * @param moodleform $mform instance of the moodleform class
     */
    public function edit_field_add($mform) {
        $mform->addElement('filemanager', $this->inputname, format_string($this->field->name), null,
                array('maxbytes' => $this->field->param1, 'subdirs' => 0,
                      'maxfiles' => 1, 'accepted_types' => array('.pdf', '.doc', '.docx', '.word')));
    }

    /**
     * Sets the default data for the field in the form object
     * @param  moodleform $mform instance of the moodleform class
     */
    public function edit_field_set_default($mform) {
        if ($this->userid && ($this->userid !== -1)) {
            $filemanagercontext = context_user::instance($this->userid);
        } else {
            $filemanagercontext = context_system::instance();
        }

        $draftitemid = file_get_submitted_draft_itemid($this->inputname);
        file_prepare_draft_area($draftitemid, $filemanagercontext->id, 'profilefield_samoocv',
                "files_{$this->fieldid}", 0,
                        array('maxbytes' => $this->field->param1, 'subdirs' => 0,
                              'maxfiles' => 1, 'accepted_types' => array('.pdf', '.doc', '.docx', '.word')));
        $mform->setDefault($this->inputname, $draftitemid);
        $this->data = $draftitemid;

        parent::edit_field_set_default($mform);
    }

    /**
     * HardFreeze the field if locked.
     *
     * @param moodleform $mform instance of the moodleform class
     */
    public function edit_field_set_locked($mform) {
        if (!$mform->elementExists($this->inputname)) {
            return;
        }
        if ($this->is_locked() && !has_capability('moodle/user:update', get_context_instance(CONTEXT_SYSTEM))) {
            $mform->hardFreeze($this->inputname);
            $mform->setConstant($this->inputname, $this->data);
        }
    }

    /**
     * Save the uploaded file in the db
     * @param stdClass $usernew data coming from the form
     * @return mixed returns data id if success of db insert/update, false on fail, 0 if not permitted
     */
    public function edit_save_data($usernew) {
        if (!isset($usernew->{$this->inputname})) {
            return;
        }

        $usercontext = context_user::instance($this->userid, MUST_EXIST);
        file_save_draft_area_files($usernew->{$this->inputname}, $usercontext->id, 'profilefield_samoocv',
                "files_{$this->fieldid}", 0,
                        array('maxbytes' => $this->field->param1, 'subdirs' => 0,
                              'maxfiles' => 1, 'accepted_types' => array('.pdf', '.doc', '.docx', '.word')));
        parent::edit_save_data($usernew);
    }

    /**
     * Loads a user object with data for this field ready for the edit profile
     * form
     * @param stdClass $user a user object
     */
    public function edit_load_user_data($user) {
        // Set to null or the data will not appear on filemanager.
        $user->{$this->inputname} = null;
    }

    /**
     * Display a link to download the archives in the user profile
     *
     * @return string data for custom profile field.
     */
    public function display_data() {
        global $CFG;
        global $USER;

        // Default formatting.
        $data = parent::display_data();

        $context = context_user::instance($this->userid, MUST_EXIST);
        $folder = get_file_storage();
        $files = $folder->get_area_files($context->id, 'profilefield_samoocv', "files_{$this->fieldid}", 0, 'timemodified', false);
        $data = array();

        foreach ($files as $file) {
            $path = '/' . $context->id . '/profilefield_samoocv/files_' . $this->fieldid . '/' .
                    $file->get_itemid() .
                    $file->get_filepath() .
                    $file->get_filename();
            $url = file_encode_url("$CFG->wwwroot/pluginfile.php", $path, true);
            $filename = $file->get_filename();
            $data[] = html_writer::link($url, $filename);
        }

        if (empty($data)) {
            $data = get_string('samoocv_nofiles', 'profilefield_samoocv');
        } else {
            $data = implode('<br />', $data);
        }

        // Check if the user can see the link or not.
        // Same user or admin.
        if ($USER->id == $this->userid || is_siteadmin($USER->id)) {
            return $data;
        }
        // Check if the current user has teacher or better capabilities in any course to see the files.
        $courses = enrol_get_all_users_courses($this->userid);
        foreach ($courses as $course) {
            $context = context_course::instance($course->id);
            if (has_capability('moodle/course:manageactivities', $context)) {
                return $data;
            }
        }
        return get_string('samoocv_nopermissionstosee', 'profilefield_samoocv');
    }

}
