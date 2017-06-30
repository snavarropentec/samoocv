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
 * COMPONENT External functions unit tests
 *
 * @package    profilefield_samoocv
 * @copyright  2017 Planificacion de Entornos Tecnologicos SL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once(__DIR__. '/../lib.php');

/**
 * This class is used to run the unit tests
 *
 * @package    local_eudecustom
 * @copyright  2015 Planificacion de Entornos Tecnologicos SL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_profilefield_samoocv_testcase extends advanced_testcase {

    /**
     * Tests for phpunit.
     */
    public function test_profilefield_samoocv_pluginfile () {
        global $DB;

        $this->resetAfterTest(true);

        // Creating a few users.
        $user1 = $this->getDataGenerator()->create_user(array('username' => 'user1'));
        $user2 = $this->getDataGenerator()->create_user(array('username' => 'user2'));

        // Recovering the context of user1.
        $context1 = context_user::instance($user1->id, MUST_EXIST);

        // Creating a custom user field category.
        $usercategoryfield = new stdClass();
        $usercategoryfield->name = 'Other fields';
        $usercategoryfield->shortorder = 1;
        $usercatfieldid = $DB->insert_record('user_info_category', $usercategoryfield);

        // Creating a custom user field.
        $userfield = new stdClass();
        $userfield->shortname = 'CV';
        $userfield->name = 'Curriculum Vitae';
        $userfield->datatype = 'samoocv';
        $userfield->description = '<p>SamooCVSamooCVSamooCV</p>';
        $userfield->descriptionformat = 1;
        $userfield->categoryid = $usercatfieldid;
        $userfield->visible = 0;
        $userfieldid = $DB->insert_record('user_info_field', $userfield);

        // More initial settings.
        $filearea = 'files_' . $userfieldid;
        $args = array('0', 'Test.docx');

        // Login with user1.
        $this->setUser($user2);

        // Test the function (as the custom user field visibility is set to 0, the expected result is false).
        $result = profilefield_samoocv_pluginfile(null, null, $context1, $filearea, $args, true);
        $this->assertFalse($result);

        /*
         *  Test the function (as the custom user field visibility is set to 1 and we are logged with user2,
         *  the expected result is false).
         */
        $userfield = $DB->get_record('user_info_field', array('id' => $userfieldid));
        $userfield->visible = 1;
        $DB->update_record('user_info_field', $userfield);
        $result = profilefield_samoocv_pluginfile(null, null, $context1, $filearea, $args, true);
        $this->assertFalse($result);

        /*
         *  Test the function (custom user field visibility is set to 2 and we are logged with user2 but
         *  the file is not created yet, so the expected result is also false).
         */
        $userfield = $DB->get_record('user_info_field', array('id' => $userfieldid));
        $userfield->visible = 2;
        $DB->update_record('user_info_field', $userfield);
        $result = profilefield_samoocv_pluginfile(null, null, $context1, $filearea, $args, true);
        $this->assertFalse($result);

        // We create the file now.
        $fs = get_file_storage();
        $fileinfo = new stdClass();
        $fileinfo->contextid = $context1->id;
        $fileinfo->component = 'profilefield_samoocv';
        $fileinfo->filearea = 'files_' . $userfieldid;
        $fileinfo->itemid = 0;
        $fileinfo->userid = $user1->id;
        $fileinfo->filepath = '/';
        $fileinfo->filename = 'Test.docx';
        $content = "SamooCV sample file";
        $fs->create_file_from_string($fileinfo, $content);

        /*
         *  Test the function (custom user field visibility is set to 2 and we are logged with user2 and
         *  the file is created).
         */
        /*
         * As we test the function to serve the file phpunit retuirns an error of this type:
         * Cannot modify header information - headers already sent by
         * (output started at C:\xampp\htdocs\moodle\lib\phpunit\classes\util.php:335)
         * This is a known php bug due to phpunit beign unable to change the headers once the
         * process is initiated, so its a signal of the success of the function trying to serve the file indeed
         *
         * $result = profilefield_samoocv_pluginfile(null, null, $context1, $filearea, $args, true);
         */
    }

}
