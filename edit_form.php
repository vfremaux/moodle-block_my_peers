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
defined('MOODLE_INTERNAL') || die();
/**
 * Form for editing profile block settings
 *
 * @package    block
 * @subpackage myprofile
 * @copyright  2010 Remote-Learner.net
 * @author     Olav Jordan <olav.jordan@remote-learner.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Joseph Rézeau <joseph@rezeau.org> March 2012
 * changed editing interface display dropdown 'Yes/No' lists to checkboxes, for improved ergonomy
 * added feature: disabling extra information parameters when config_admin_personal_info is unchecked
 * fixed typo in config_admin_personnal_info -> config_admin_personal_info
 * replaced display_un with more understandable display_username
 * added display_role setting (forgotten?)
 */
//require_once('../myprofile/edit_form.php');
 
class block_my_peers_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        global $CFG, $COURSE;
        
        $context = get_context_instance(CONTEXT_COURSE, $COURSE->id);
        // var_dump($context);exit;
        $assignableroles = get_assignable_roles($context); 
        
        $mform->addElement('header', 'configheader', get_string('pluginname', 'block_my_peers'));
        //$mform->addElement('html', get_string("config_instance_preamble", "block_my_peers"));
        //$mform->addElement('html', get_string("config_admin_title", "block_my_peers"));
        //$mform->addElement('html', get_string("config_admin_blabla", "block_my_peers"));
         // var_dump($this->block->config);exit;
        $mform->addElement('text', 'config_title', get_string('config_title', 'block_my_peers'), $this->block->get_param('title'));        
        $mform->addElement('editor', 'config_admin_blabla', get_string('config_admin_blabla', 'block_my_peers'), $this->block->get_param('admin_blabla'));
        $mform->setType('config_admin_blabla', PARAM_RAW);
        
        $mform->addElement('html', get_string("config_role_to_display", "block_my_peers"), $this->block->get_param('role_to_display'));
        $assignableroles2 = $assignableroles;
        $table_role = '<table><tr><td></td>'."\n";
        foreach($assignableroles as $role) {
            $table_role .=  '<th>'.$role.'</th>'."\n";
        }
        $table_role .= '</tr>'."\n";
        $mform->addElement('html', $table_role);
        $config_role_to_display = $this->block->get_param('role_to_display');
        foreach($assignableroles as $role_id => $role) {
            $mform->addElement('html',"<tr><td>$role  </td>");
            foreach($assignableroles2 as $role2_id => $role2) {
                 $mform->addElement('html',"<td>");
                $mform->addElement('checkbox', "config_role_to_display[$role_id][$role2_id]");
                $mform->setDefault("config_role_to_display[$role_id][$role2_id]", (isset($this->block->config->role_to_display[$role_id][$role2_id])?true:''));
                $mform->addElement('html',"</td>");
            }
            $mform->addElement('html', "</tr>\n");
        }	
        $mform->addElement('html',"</table>");

        $mform->addElement('advcheckbox', 'config_admin_by_group','', ' '.get_string('config_admin_by_group', 'block_my_peers'),
        		array('group' => 1), array(0, 1));
        $mform->setDefault('config_admin_by_group', $this->block->get_param('admin_by_group'));
        
        $yesnoonly = array( 0 => get_string( 'no' ), 1 => get_string( 'yes' ), 2 => get_string( 'only', 'block_my_peers' ) );
        $biglittle = array( 16 => get_string( 'reallylittle', 'block_my_peers'),36 => get_string( 'little', 'block_my_peers'), 50 => get_string( 'notsobig', 'block_my_peers'), 100 => get_string( 'big', 'block_my_peers'));
        
        //$mform->addElement('select', 'config_admin_photo', get_string('config_admin_photo', 'block_my_peers'), $yesnoonly, $this->block->get_param('admin_photo'));
        $mform->addElement('advcheckbox', 'config_admin_photo', '', ' '.get_string('config_admin_photo', 'block_my_peers'),
        		array('group' => 1), array(0, 1));
        $mform->setDefault('config_admin_photo', $this->block->get_param('admin_photo'));
        
        $mform->addElement('select', 'config_admin_photo_size', get_string('config_admin_photo_size', 'block_my_peers'), $biglittle, $this->block->get_param('admin_photo_size'));
        $mform->setDefault('config_admin_photo_size', $this->block->get_param('admin_photo_size'));
        $mform->disabledIf('config_admin_photo_size', 'config_admin_photo');
        
        
        $mform->addElement('advcheckbox', 'config_display_username', '', ' '.get_string('display_un', 'block_myprofile'),
        		array('group' => 1), array(0, 1));
        $mform->setDefault('config_display_username', $this->block->get_param('display_username'));
        
        $mform->addElement('advcheckbox', 'config_admin_display_role', '', ' '.get_string('config_admin_display_role', 'block_my_peers'),
        		array('group' => 1), array(0, 1));
        $mform->setDefault('config_admin_display_role', $this->block->get_param('admin_display_role'));
                
        $mform->addElement('html', '<hr />');
        $mform->addElement('advcheckbox', 'config_admin_personal_info', '', ' '.get_string('config_admin_personal_info', 'block_my_peers'),
        		array('group' => 1), array(0, 1));
        $mform->addElement('html', '<hr />');
        
        $mform->addElement('advcheckbox', 'config_display_country','', ' '.get_string('display_country', 'block_myprofile'),
        		array('group' => 1), array(0, 1));
        $mform->disabledIf('config_display_country', 'config_admin_personal_info');
        
        $mform->addElement('advcheckbox', 'config_display_city','', ' '.get_string('display_city', 'block_myprofile'),
        		array('group' => 1), array(0, 1));
        $mform->disabledIf('config_display_city', 'config_admin_personal_info');
        
        $mform->addElement('advcheckbox', 'config_display_email','', ' '.get_string('display_email', 'block_myprofile'),
        		array('group' => 1), array(0, 1));
        $mform->disabledIf('config_display_email', 'config_admin_personal_info');
        
        $mform->addElement('advcheckbox', 'config_display_webpage','', ' '.get_string('display_webpage', 'block_my_peers'),
        		array('group' => 1), array(0, 1));
        $mform->disabledIf('config_display_webpage', 'config_admin_personal_info');
        
        $mform->addElement('advcheckbox', 'config_display_icq','', ' '.get_string('display_icq', 'block_myprofile'),
        		array('group' => 1), array(0, 1));
        $mform->disabledIf('config_display_icq', 'config_admin_personal_info');
        
        $mform->addElement('advcheckbox', 'config_display_skype','', ' '.get_string('display_skype', 'block_myprofile'),
        		array('group' => 1), array(0, 1));
        $mform->disabledIf('config_display_skype', 'config_admin_personal_info');
        
        $mform->addElement('advcheckbox', 'config_display_yahoo','', ' '.get_string('display_yahoo', 'block_myprofile'),
        		array('group' => 1), array(0, 1));
        $mform->disabledIf('config_display_yahoo', 'config_admin_personal_info');
        
        $mform->addElement('advcheckbox', 'config_display_aim','', ' '.get_string('display_aim', 'block_myprofile'),
        		array('group' => 1), array(0, 1));
        $mform->disabledIf('config_display_aim', 'config_admin_personal_info');
        
        $mform->addElement('advcheckbox', 'config_display_msn','', ' '.get_string('display_msn', 'block_myprofile'),
        		array('group' => 1), array(0, 1));
        $mform->disabledIf('config_display_msn', 'config_admin_personal_info');
        
        $mform->addElement('advcheckbox', 'config_display_phone1','', ' '.get_string('display_phone1', 'block_myprofile'),
        		array('group' => 1), array(0, 1));
        $mform->disabledIf('config_display_phone1', 'config_admin_personal_info');
        
        $mform->addElement('advcheckbox', 'config_display_phone2','', ' '.get_string('display_phone2', 'block_myprofile'),
        		array('group' => 1), array(0, 1));
        $mform->disabledIf('config_display_phone2', 'config_admin_personal_info');
        
        $mform->addElement('advcheckbox', 'config_display_institution','', ' '.get_string('display_institution', 'block_myprofile'),
        		array('group' => 1), array(0, 1));
        $mform->disabledIf('config_display_institution', 'config_admin_personal_info');
        
        $mform->addElement('advcheckbox', 'config_display_address','', ' '.get_string('display_address', 'block_myprofile'),
        		array('group' => 1), array(0, 1));
        $mform->disabledIf('config_display_address', 'config_admin_personal_info');
        
        $mform->addElement('advcheckbox', 'config_display_firstaccess','', ' '.get_string('display_firstaccess', 'block_myprofile'),
        		array('group' => 1), array(0, 1));
        $mform->disabledIf('config_display_firstaccess', 'config_admin_personal_info');
        
        $mform->addElement('advcheckbox', 'config_display_lastaccess','', ' '.get_string('display_lastaccess', 'block_myprofile'),
        		array('group' => 1), array(0, 1));
        $mform->disabledIf('config_display_lastaccess', 'config_admin_personal_info');
        
        $mform->addElement('advcheckbox', 'config_display_currentlogin','', ' '.get_string('display_currentlogin', 'block_myprofile'),
        		array('group' => 1), array(0, 1));
        $mform->disabledIf('config_display_currentlogin', 'config_admin_personal_info');
        
        $mform->addElement('advcheckbox', 'config_display_lastip','', ' '.get_string('display_lastip', 'block_myprofile'),
        		array('group' => 1), array(0, 1));
        $mform->disabledIf('config_display_lastip', 'config_admin_personal_info');
    }
}