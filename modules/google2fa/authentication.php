<?php
class G2FA {
  protected $length = 6;
  public function genRanSecret($len = 32) {
    $char = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; $randstr = "";
    for ($i=0; $i<$len; $i++){ $randstr .= $char[rand(0, strlen($char) - 1)]; }
    return $randstr;
  }
  public function getCode($secret, $sliceTime = null) {
    if($sliceTime === null) {
      $sliceTime = floor(time() / 30);
    }
    $secretkey = $this->debase32($secret);
    $time = chr(0).chr(0).chr(0).chr(0).pack('N*', $sliceTime);
    $hm = hash_hmac('SHA1', $time, $secretkey, true);
    $offset = ord(substr($hm, -1)) & 0x0F;
    $hashpart = substr($hm, $offset, 4);
    $value = unpack('N', $hashpart);
    $value = $value[1];
    $value = $value & 0x7FFFFFFF;
    $modulo = pow(10, $this->length);
    return str_pad($value % $modulo, $this->length, '0', STR_PAD_LEFT);
  }
  public function getQR($name, $secret, $title = null, $params = array()) {
    $width = !empty($params['width']) && (int) $params['width'] > 0 ? (int) $params['width'] : 250;
    $height = !empty($params['height']) && (int) $params['height'] > 0 ? (int) $params['height'] : 250;
    $level = !empty($params['level']) && array_search($params['level'], array('L', 'M', 'Q', 'H')) !== false ? $params['level'] : 'M';
    $urlencoded = urlencode('otpauth://totp/'.$name.'?secret='.$secret.'');
    if(isset($title)) {
      $urlencoded .= urlencode('&issuer='.urlencode($title));
    }
    return 'https://chart.googleapis.com/chart?chs='.$width.'x'.$height.'&chld='.$level.'|0&cht=qr&chl='.$urlencoded.'';
  }
  public function verifyCode($secret, $code, $discrepancy = 1, $curSliceTime = null) {
    if($curSliceTime === null) {
      $curSliceTime = floor(time() / 30);
    }
    if(strlen($code) != 6) {
      return false;
    }
    for($i = -$discrepancy; $i <= $discrepancy; ++$i) {
      $calcCode = $this->getCode($secret, $curSliceTime + $i);
      if($this->timingSafeEquals($calcCode, $code)) {
        return true;
      }
    }
    return false;
  }
  public function setCodeLength($length) {
    $this->length = $length;
    return $this;
  }
  protected function debase32($secret) {
    if(empty($secret)) {
      return '';
    }
    $base32chars = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '2', '3', '4', '5', '6', '7', '=', );
    $base32charsFlipped = array_flip($base32chars);
    $paddingCharCount = substr_count($secret, $base32chars[32]);
    $allowedValues = array(6, 4, 3, 1, 0);
    if(!in_array($paddingCharCount, $allowedValues)) {
      return false;
    }
    for($i = 0; $i < 4; ++$i) {
      if($paddingCharCount == $allowedValues[$i] && substr($secret, -($allowedValues[$i])) != str_repeat($base32chars[32], $allowedValues[$i])) {
        return false;
      }
    }
    $secret = str_replace('=', '', $secret);
    $secret = str_split($secret);
    $binaryString = '';
    for($i = 0; $i < count($secret); $i = $i + 8) {
      $x = '';
      if(!in_array($secret[$i], $base32chars)) {
        return false;
      }
      for($j = 0; $j < 8; ++$j) {
        $x .= str_pad(base_convert(@$base32charsFlipped[@$secret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
      }
      $eightBits = str_split($x, 8);
      for($z = 0; $z < count($eightBits); ++$z) {
        $binaryString .= (($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48) ? $y : '';
      }
    }
    return $binaryString;
  }
  private function timingSafeEquals($safeString, $userString) {
    if(function_exists('hash_equals')) {
      return hash_equals($safeString, $userString);
    }
    $safeLen = strlen($safeString);
    $userLen = strlen($userString);
    if($userLen != $safeLen) {
      return false;
    }
    $result = 0;
    for($i = 0; $i < $userLen; ++$i) {
      $result |= (ord($safeString[$i]) ^ ord($userString[$i]));
    }
    return $result === 0;
  }
}