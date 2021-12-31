<?php
$page_security = 'SA_G2FA';
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");

page(_($help_context = "Two Factor Authentication"));

include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/modules/google2fa/authentication.php");

simple_page_mode(true);

$g2fa = new G2FA();

$uid = $_SESSION['wa_current_user']->user;
$sql = "SELECT google_secret_code as secret FROM ".TB_PREF."users WHERE id = $uid";
$res = db_query($sql, "Failed in retreiving USERS list.");
$google_secret_code = db_fetch($res)['secret'];

if($google_secret_code == "") $enable2fa = "";
else $enable2fa = 1;

if(isset($_POST["submit"])){
  $enable2fa = $_POST['enable2fa'];
  
  if($google_secret_code == "") $google_secret_code = $_SESSION['wa_current_user']->google2fa['secret'];
  $result = $g2fa->verifyCode($google_secret_code, $_POST['code'], 0);
  if($result) {
    if($enable2fa == 1){
      $sql = "UPDATE ".TB_PREF."users SET google_secret_code = '$google_secret_code' WHERE id = $uid";
      display_notification_centered(_("Now that you have <b>Enabled</b> Google Two Factor Authentication"));
    } else {
      $google_secret_code = "";
      $sql = "UPDATE ".TB_PREF."users SET google_secret_code = '$google_secret_code' WHERE id = $uid";
      display_notification_centered(_("Now you have <b>Disabled</b> Google Two Factor Authentication"));
    }
    db_query($sql, "Failed in update USERS list.");
  } else {
    display_error(_("Your code not valid!"));
  }
}
if($google_secret_code == ""){
  $secret = $_SESSION['wa_current_user']->google2fa['secret'] = $g2fa->genRanSecret();
  $host = "tilyanpristka.id";
  
  $qrCodeUrl = $g2fa->getQR($host, $_SESSION['wa_current_user']->google2fa['secret']);
}

// echo "<pre>"; print_r($_SESSION['wa_current_user']); echo "</pre>";
start_form();
start_outer_table();
table_section(1);
table_section_title('Google Two Factor Authentication');

if($google_secret_code == "") {
  echo "<tr><td colspan=\"2\" style=\"text-align: center;\"><img src=\"$qrCodeUrl\"></td></tr>";
  label_row(_("Secret (Save this for recovery)"), _($secret), null);
}
check_row('Two Factor Authentication', 'enable2fa', $enable2fa);
echo "<tr><td colspan=\"2\" style=\"text-align: center;\"><input type=\"text\" name=\"code\" id=\"code\" size=\"10\" maxlength=\"6\" class=\"combo\" rel=\"code\" placeholder=\"Verify Code\" autocomplete=\"off\" style=\"text-align: center; padding-left: 0;\" title=\"\" _last=\"\"></td></tr>";

end_outer_table();
br();   
submit_center('submit',_('Submit'));
end_form();

end_page();