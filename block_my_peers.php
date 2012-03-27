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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
* This is a one-line short description of the file
*
* You can have a rather longer description of the file as well, 
* if you like, and it can span multiple lines.
*
* @package block_my_peers
* @category block
* @copyright 2012 Étienne Rozé
* @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

/**
 * Joseph Rézeau <joseph@rezeau.org> March 2012
 * fixed typo in config_admin_personnal_info -> config_admin_personal_info
 * replaced display_un with more understandable display_username
 * activated feature $CFG->block_my_peers_timetosee
 * set default_param->admin_photo_size = 50
 * moved function my_peers_profile_display_fields($userid) from lib_my_peers.php to block_my_peers.php file
 * ... and removed lib_my_peers.php which is now apparently useless
 * removed 2 files which are no longer used in Moodle 2: config_global.html and config_instance.html 
 */

// no longer needed
//require_once('lib_my_peers.php');

class block_my_peers extends block_base {

    private $default_param;

    function init() {

        global $COURSE, $CFG;

        /// JR note: those default settings with a value of zero (0) are not used in the edit_form

        $this->default_param->title = $this->title = get_string('pluginname', 'block_my_peers');

        $this->default_param->admin_title = '';
        $this->default_param->admin_blabla = array('text'=>'','format'=>2);
        $this->default_param->admin_display_role = 1;
        $this->default_param->admin_by_group = 1;
        $this->default_param->admin_personal_info = 0;
        $this->default_param->admin_photo = 1;
        $this->default_param->admin_photo_size = 50; // medium
        $this->default_param->display_username  = 1;
        $this->default_param->display_country   = 0;
        $this->default_param->display_city  = 0;
        $this->default_param->display_email  = 0;
        $this->default_param->display_webpage  = 0;
        $this->default_param->display_icq  = 0;
        $this->default_param->display_skype  = 0;
        $this->default_param->display_yahoo  = 0;
        $this->default_param->display_aim  = 0;
        $this->default_param->display_msn  = 0;
        $this->default_param->display_phone1  = 0;
        $this->default_param->display_phone2  = 0;
        $this->default_param->display_institution  = 0;
        $this->default_param->display_address  = 0;
        $this->default_param->display_firstaccess  = 0;
        $this->default_param->display_lastaccess  = 0;
        $this->default_param->display_currentlogin  = 0;
        $this->default_param->display_lastip  = 0;

        $this->default_param->role_to_display = array('5' => array( '2' => true, '3' => true, '4' => true));

        // var_dump($COURSE);
        /*if (isset($COURSE->defaultrole)) {
$defaultrole = $COURSE->defaultrole;
} else {
$defaultrole = $CFG->defaultcourseroleid;
}*/
        //    $this->default_param->role_to_display = array( $defaultrole => array( '2' => true, '3' => true, '4' => true));

    }

    /*function user_can_addto($page) {
        // Don't allow people to add the block if they can't even use it
        if (!has_capability('moodle/community:add', $page->context)) {
            return false;
        }

        return parent::user_can_addto($page);
    }

    function user_can_edit() {
        // Don't allow people to edit the block if they can't even use it
        if (!has_capability('moodle/community:add',
                        get_context_instance_by_id($this->instance->parentcontextid))) {
            return false;
        }
        return parent::user_can_edit();
    }
    */

    /**
* This function indicate that the my_peers has an administrator config interface
* @ return true
*/  
    function has_config() {
        return false; // JR not sure this is being used for this block! this is a leftover from the online_users block I think
    	return true;
    }

    /**
* This function return the value of the parameter $param_name :
* if the parameter is set for the block instance return this value
* else return the value set on site level
* else return false
*/

    function get_param($param_name, $instance = true) {
        global $CFG;
       
        if (isset($this->config->$param_name) and is_string($this->config->$param_name) and ! strcmp($this->config->$param_name, '')) {
            unset($this->config->$param_name);
        }
        if (isset($this->config->$param_name) and $instance) {
            return $this->config->$param_name;
        } else {
            if (isset($CFG->$param_name)) {
                return $CFG->$param_name;
            } else {
                return $this->default_param->$param_name;
            }
        }
    }

    /**
* This function display persons with role wanted wich belong the group with the $group identifiant
* If $group is false, display all persons with role wanted.
* Return an array of user object
*/
    function get_and_display_persons( $group ) {

        global $USER, $CFG, $COURSE, $OUTPUT, $DB;

        global $switchrole;

        $persons = array();

        // get the role to display ( saved in a block parameter)
        $roles_to_display = $this->get_param('role_to_display');
        // exit;
        if ($COURSE->id == SITEID) {
            $coursecontext = get_context_instance(CONTEXT_SYSTEM, SITEID); // SYSTEM context
        } else {
            $coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id); // Course context
        }

        if (isloggedin()) {
            $usercontext = get_context_instance(CONTEXT_USER, $USER->id); // User context
        }
        
        $userroles = array();

        // Verification if there is a role switching to use an other role that the current role
        if ($switchrole > 0) {
            $userrole = $DB->get_record('role', array('id'=>$switchrole));
            //role_switch($switchrole, $coursecontext);
            $userroles[]->roleid=$userrole->id;
        } else {
            // Get the role in the course of current user.
            $userroles = get_user_roles($coursecontext);
        }
        
        // make sure user can view this user's profile
        $details = true;
        if ( !has_capability('moodle/user:viewdetails', $coursecontext)
                || !has_capability('moodle/user:viewdetails', $usercontext)) {
            $details = false;
        }

        $rolesids = array();
        // Search for each role of the current user, persons we have to display
        foreach ($userroles as $userrole) {
            if (! empty($roles_to_display[$userrole->roleid])) {
                
                foreach ($roles_to_display[$userrole->roleid] as $roleid => $tps) {
                    $persons_tmp = array();
                    if ($group ) {
                        $persons_tmp = get_role_users($roleid, $coursecontext, true, '', 'u.lastname ASC', false, $group->id);
                    } else {
                        $persons_tmp = get_role_users($roleid, $coursecontext, true, '', 'u.lastname ASC', false);
                    }
                    if (! empty($persons_tmp)) {
                        foreach ($persons_tmp as $ptp) {
                            $rolename = role_fix_names(array($ptp->roleid => $ptp->rolename), $coursecontext);
                            if(! isset($persons[$ptp->id]) ){
                                $persons[$ptp->id] = $ptp;
                                $persons[$ptp->id]->rolename = $rolename[$ptp->roleid];
                            } else {
                                $persons[$ptp->id]->rolename .= ', '.$rolename[$ptp->roleid];
                            }

                        }
                    }
                }
            }
        }

        // Display

        $text = '';

        
        if(!empty($persons)) {

            // creation of the list of id to have no double
            $list_id = array();
            foreach ($persons as $person) {
                if ($person->id == $USER->id) {
                    continue;
                }
                
                if ( isset($list_id[$person->id]) ) {
                    // The person has been already displayed, so not again
                    continue;
                } else {
                    $list_id[$person->id]=true;
                }

                if ( $this->get_param('admin_photo') < 2 ) {
                    $text .= "<div>";
                }
                
                // JR workaround to prevent moodle notice "Missing imagealt property in $user object, etc."
                // a better solution should be found 
                $person->imagealt = '';
                // end JR
                
                if ($this->get_param('admin_photo')) {
                        $text .= $OUTPUT->user_picture($person, 
                        array('courseid' => $COURSE->id, 
                                'size' => $this->get_param('admin_photo_size'),
                                'class' => 'my_peers_picture'
                        )
                    );
                }
                if ( $this->get_param('admin_photo') == 2 ) {
                    continue;
                }
                if ($this->get_param('display_username')){
                    if ($details) {
                        /* $text .= '<strong><a href="'.$CFG->wwwroot.'/user/view.php?id='.$person->id.'&amp;course='.
                        $COURSE->id.'">'.fullname($person).'</a></strong>'; */
                        
                        $text .= '<div class="myprofileitem fullname">'.fullname($person).'</div>';
                        
                    } else {
                        //$text .= '<strong>'.fullname($person).'</strong>';
                        $text .= '<div class="myprofileitem fullname">'.fullname($person).'</div>';
                    }
                }
                $role = '';
                if ($this->get_param('admin_display_role')) {
                    //$role = get_user_roles_in_context($person->id, $coursecontext);
                    $role = $person->rolename;
                }
                $text .= ' '.'<a title="'.get_string('messageselectadd').'" target="message_'.
                $person->id.'" href="'.$CFG->wwwroot.'/message/discussion.php?id='.
                $person->id.'" onclick="return openpopup(\'/message/discussion.php?id='.
                $person->id.'\', \'message_'.$person->id.
                '\', \'menubar=0, location=0, scrollbars, status, resizable, width=400, height=500\', 0);">'.
                '<img class="icon message" src="'.$OUTPUT->pix_url('/t/message').'" alt="'.
                get_string('messageselectadd') .'" /></a><br />'.$role.' ';
                $text .= "</div>";

                if ($this->get_param('admin_personal_info')) {

                    if (! $user = $DB->get_record("user", array("id" =>$person->id))) {
                        error("User ID was incorrect");
                    }
                    if (has_capability('moodle/user:viewhiddendetails', $coursecontext)) {
                        $hiddenfields = array();
                        } else {
                        $hiddenfields = array_flip(explode(',', $CFG->hiddenuserfields));
                    }       

                    
                    if (($user->maildisplay == 1  
                        or has_capability('moodle/course:useremail', $coursecontext)
                        or ($user->maildisplay == 2 and enrol_sharing_course($user, $USER)))
                        and $this->get_param('display_email')) {
                        $text .= '<div>'.obfuscate_mailto($user->email).'</div>';
                    }

                    if (! isset($hiddenfields['country']) && $user->country && $this->get_param('display_country')) {
                        $text .= '<div>'.get_string($user->country, 'countries').'</div>';
                    }

                    if (! isset($hiddenfields['city']) && $user->city && $this->get_param('display_city')) {
                         $text .= '<div>'.s($user->city);
                    }

                    if (has_capability('moodle/user:viewhiddendetails', $coursecontext)) {
                        if ($user->address && $this->get_param('display_address')) {
                             $text .= '<div>'.s($user->address).'</div>';
                        }
                        if ($user->phone1 && $this->get_param('display_phone1')) {
                            $text .= '<div>'.s($user->phone1).'</div>';
                        }
                        if ($user->phone2 && $this->get_param('display_phone2')) {
                            $text .= '<div>'.s($user->phone2).'</div>';
                        }
                    } 

                    
                    if ($user->url && !isset($hiddenfields['webpage']) && $this->get_param('display_webpage')) {
                         $url = $user->url;
                         if (strpos($user->url, '://') === false) {
                            $url = 'http://'. $url;
                        }
                        $text .= '<div><a href="'.s($url).'">'.get_string('webpage').'</a></div>';
                    }

                    if ($user->icq && !isset($hiddenfields['icqnumber']) && $this->get_param('display_icq')) {
                        $text .= '<a href="http://web.icq.com/wwp?uin='.urlencode($user->icq).'">'
                                .s($user->icq)." <img src=\"http://web.icq.com/whitepages/online?icq="
                                .urlencode($user->icq).'&amp;img=5" alt="" /></a>';
                    }

                    if ($user->skype && !isset($hiddenfields['skypeid']) && $this->get_param('display_skype')) {
                        $text .= '<a href="callto:'.urlencode($user->skype).'">'.s($user->skype)
                                    .' <img src="http://mystatus.skype.com/smallicon/'
                                    .urlencode($user->skype).'" alt="'.get_string('status').'"/></a>';
                    }
                    
                    if ($user->yahoo && !isset($hiddenfields['yahooid']) && $this->get_param('display_yahoo')) {
                        $text .=  '<a href="http://edit.yahoo.com/config/send_webmesg?.target='.urlencode($user->yahoo)
                                    .'&amp;.src=pg">'.s($user->yahoo).' <img src="http://opi.yahoo.com/online?u='.urlencode($user->yahoo)
                                    .'&m=g&t=0" alt=""></a>';
                    }
                    
                    if ($user->aim && !isset($hiddenfields['aimid']) && $this->get_param('display_aim')) {
                        $text .= '<a href="aim:goim?screenname='.urlencode($user->aim).'">'.s($user->aim).'</a>';
                    }
                    if ($user->msn && !isset($hiddenfields['msnid']) && $this->get_param('display_msn')) {
                        $text .= 'MSN:'.s($user->msn).'';
                    }
                    
                    if ($user->firstaccess && !isset($hiddenfields['firstaccess']) && $this->get_param('display_firstaccess')) {
                        $text .= '<div>'.get_string('firstaccess').': ' .s(userdate($user->firstaccess)).'</div>';
                    }
                    
                    if ($user->lastaccess && !isset($hiddenfields['lastaccess']) && $this->get_param('display_lastaccess')) {
                        $text .= '<div>'.get_string('lastaccess').': ' .s(userdate($user->lastaccess)).'</div>';
                    }
                    
                    if ($user->currentlogin && !isset($hiddenfields['currentlogin']) && $this->get_param('display_currentlogin')) {
                        $text .= '<div>'.get_string('login').': ' .s(userdate($user->currentlogin)).'</div>';
                    }
                    
                    if ($user->lastip && !isset($hiddenfields['lastip']) && $this->get_param('display_lastip')) {
                        $text .= '<div>IP : ' .s($user->lastip).'</div>';
                    }
                    
                    $this->my_peers_profile_display_fields($user->id);
                }
            }
        } else {
            // Do nothing : $text is empty;
        }
        return $text;
    }

    /**
* This function construct and return the html content 
*/
    function get_content() {
        global $USER, $CFG, $COURSE;

        if ($this->content !== null) {
            return $this->content;
        }
        
        if (!isloggedin() or isguestuser()) {
            return '';      // Never useful unless you are logged in as real users
        }
        
        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        if (empty($this->instance)) {
            return $this->content;
        }

        $groups = array();

        if ($COURSE->id == SITEID) {
            $coursecontext = get_context_instance(CONTEXT_SYSTEM, SITEID); // SYSTEM context
        } else {
            $coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id); // Course context
        }

        // Have a look If the user can see all people or only his group
        // If he can see only his group, force the admin_by_group to be 1
        // I don't integrate the capability 'moodle/site:accessallgroups' because I don't understand yet how it works
        // (! has_capability('moodle/site:accessallgroups', $coursecontext)

        if (($COURSE->groupmode == SEPARATEGROUPS and $COURSE->groupmodeforce) ) {
            $force_by_group = true;
            $this->config->admin_by_group = 1;
        } else {
            $force_by_group = false;
            if (($COURSE->groupmode == NOGROUPS and $COURSE->groupmodeforce) ) {
                $this->config->admin_by_group = 0;
            }
        }

        if ( $this->get_param('admin_by_group') ) {
            $groups = groups_get_all_groups($COURSE->id, $USER->id);
        }

        $text = '';
        if ($groups) {
            foreach ($groups as $groupid => $group) {
                $text_group = $this->get_and_display_persons($group);
                if ($text_group) {
                    $text .= '<hr /><a href="'.$CFG->wwwroot.'/user/index.php?id='.
                    $COURSE->id.'&amp;group='.$group->id.'">'.$group->name.'</a><hr />'.$text_group;
                }
            }
        } else if (! $force_by_group) {
            $text .= $this->get_and_display_persons(false);
        }
        $this->content->text = $text ;
        
        // Footer is added only if there somebody to display
        if ($text) {
            
            $temp = $this->get_param('admin_blabla');
            $this->content->footer= format_text($temp['text'],$temp['format']);
        }
        

        return $this->content;
    }

    /**
* This function indicate on which sort of page this block can be display
*/
    function applicable_formats() {
        return array('site-index' => false, 
        'course' => true, 
        'mod' => true );
    }

    /**
* This function is nedeed for define the title of this instance block
*/
    function specialization() {
        global $CFG;

        if ($this->get_param('title') ) {
            $this->title = $this->get_param('title');
        } else {
            $this->title = get_string('mypeers', 'block_my_peers');
        }

    }


    /**
* This function indicate that multiple instance of this block can be instanciate on one page
* @return true
*/
    function instance_allow_multiple() {
        return true;
    }

    // moved here by JR
    function my_peers_profile_display_fields($userid) {
    	global $CFG, $USER, $DB;
    	$text = '';
    	if ($categories = $DB->get_records('user_info_category', null, 'sortorder ASC')) {
    		foreach ($categories as $category) {
    			if ($fields = $DB->get_records('user_info_field', array('categoryid'=>$category->id), 'sortorder ASC')) {
    				foreach ($fields as $field) {
    					require_once($CFG->dirroot.'/user/profile/field/'.$field->datatype.'/field.class.php');
    					$newfield = 'profile_field_'.$field->datatype;
    					$formfield = new $newfield($field->id, $userid);
    					if ($formfield->is_visible() and !$formfield->is_empty()) {
    						$text .= '<div>'.$formfield->display_data().'<div>';
    					}
    				}
    			}
    		}
    	}
    	return $text;
    }
    
}


