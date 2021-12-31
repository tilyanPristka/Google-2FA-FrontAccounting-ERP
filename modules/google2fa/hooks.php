<?php
/*=======================================================\
|   FRONTACCOUNTING - Google Two Factor Authentication   |
|--------------------------------------------------------|
|  Creator: Muhammad Ardyansyah                          |
|  Date :   01-Dec-2021                                  |
|  Description: Google Two Factor Authentication         |
|  Free software under GNU GPL                           |
|                                                        |
\=======================================================*/
define ('SS_G2FA', 252<<8);
class hooks_google2fa extends hooks {
  function __construct() {
    $this->module_name = 'google2fa';
 	}
    
  function install_options($app) { /* setting sub menu untuk di dalam menu SETUP */
    global $path_to_root;

    switch($app->id) {
      case 'system':       
      $app->add_lapp_function(2, _("Two Factor Authentication"), "modules/google2fa/setup", 'SA_G2FA', MENU_SYSTEM);
    }
  }
  
  function install_access() {
    $security_sections[SS_G2FA] = _("Google 2FA");
    $security_areas['SA_G2FA'] = array(SS_G2FA|1, _("Two Factor Authentication"));
    return array($security_areas, $security_sections);
  }
  
  function activate_extension($company, $check_only=true) {
    global $db_connections;
    
    $updates = array( 'update.sql' => array('google2fa'));
    
    return $this->update_databases($company, $updates, $check_only);
  }

  function deactivate_extension($company, $check_only=true) {
    global $db_connections;

    $updates = array('remove.sql' => array('google2fa'));

    return $this->update_databases($company, $updates, $check_only);
  }
}
