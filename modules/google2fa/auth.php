<?php
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/modules/google2fa/authentication.php");

$g2fa = new G2FA();

if(isset($_POST["submit"])){
  $result = $g2fa->verifyCode($google_secret_code, $_POST['code'], 0);
  if($result) {
    $_SESSION['wa_current_user']->google2fa['success'] = true;
    $app->display();
  } else {
    echo "<center><h3>Your code not valid!</h3></center>";
  }
}

start_form();
start_outer_table();
table_section(1);
table_section_title('Google Two Factor Authentication');
echo "<tr><td colspan=\"2\" style=\"text-align: center;\"><input type=\"text\" name=\"code\" id=\"code\" size=\"10\" maxlength=\"6\" class=\"combo\" rel=\"code\" placeholder=\"Verify Code\" autocomplete=\"off\" style=\"text-align: center; padding-left: 0;\" title=\"\" _last=\"\"></td></tr>";

end_outer_table();
br();   
submit_center('submit',_('Submit'));
end_form();