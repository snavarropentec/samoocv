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
 * This file contains the cv profile field definition class.
 *
 * @package profilefield_samoocv
 * @copyright 2017 Planificacion de Entornos Tecnologicos SL
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

 defined('MOODLE_INTERNAL') || die();

/**
 * Define samoocv fields.
 *
 * @copyright 2017 Planificacion de Entornos Tecnologicos SL
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
class profile_define_samoocv extends profile_define_base {

    /**
     * Set the additional options of the field
     *
     * @param moodleform $form reference to moodleform for adding elements.
     */
    public function define_form_specific($form) {
        global $CFG;

        // Default data.
        $form->addElement('hidden', 'defaultdata', '');
        $form->setType('defaultdata', PARAM_TEXT);

        // Maximum size of the archive.
        $choices = get_max_upload_sizes($CFG->maxbytes);
        $form->addElement('select', 'param1', get_string('maximumupload'), $choices);
        $form->setDefault('param1', $CFG->maxbytes);
        $form->setType('param1', PARAM_INT);
    }

}