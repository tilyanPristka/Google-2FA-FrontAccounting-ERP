<?php
/**********************************************************************
  Copyright (C) FrontAccounting, LLC.
  Released under the terms of the GNU General Public License, GPL, 
  as published by the Free Software Foundation, either version 3 
  of the License, or (at your option) any later version.
  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
  See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
$path_to_root=".";
if (!file_exists($path_to_root.'/config_db.php'))
  header("Location: ".$path_to_root."/install/index.php");

$page_security = 'SA_OPEN';
ini_set('xdebug.auto_trace',1);
include_once("includes/session.inc");

add_access_extensions();
$app = &$_SESSION["App"];
if (isset($_GET['application']))
  $app->selected_application = $_GET['application'];

// $app->display();
$uid = $_SESSION['wa_current_user']->user;
$sql = "SELECT google_secret_code as secret FROM ".TB_PREF."users WHERE id = $uid";
$res = db_query($sql, "Failed in retreiving USERS list.");
$google_secret_code = db_fetch($res)['secret'];

if($google_secret_code != "" && (!isset($_SESSION['wa_current_user']->google2fa['success']) || $_SESSION['wa_current_user']->google2fa['success'] == "")){
  $_SESSION["App"] = new front_accounting();
  $_SESSION["App"]->init();
  include_once($path_to_root . "/modules/google2fa/auth.php");
} else $app->display();
