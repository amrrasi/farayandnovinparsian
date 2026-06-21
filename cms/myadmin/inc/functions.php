<?php
 /////////////////////define nohack function		  ////////////////////
function no_hack($string){
	
	$string = sanitizeString($string);
	
	$string = str_replace(">","-",$string);
	$string = str_replace("<","-",$string);
	$string = str_replace("*","-",$string);
	$string = str_replace("?","-",$string);
	$string = str_replace("//","-",$string);
	$string = str_replace("^","-",$string);
	$string = str_replace("'","-",$string);
	$string = str_replace("-","-",$string);
	$string = str_replace("_","-",$string);
	$string = str_replace(";","-",$string);
	$string = str_replace(",","-",$string);
	$string = str_replace("!","-",$string);
	$string = str_replace("=","-",$string);
	$string = str_replace("OR","-",$string);
	$string = str_replace("or","-",$string);
	$string = str_replace("AND","-",$string);
	$string = str_replace("and","-",$string);
	$string = str_replace("|","-",$string);
	$string = str_replace("&","-",$string);
	$string = str_replace("/","-",$string);
	$string = str_replace("SELECT","-",$string);
	$string = str_replace("select","-",$string);
	$string = str_replace("Select","-",$string);
	$string = str_replace("FROM","-",$string);
	$string = str_replace("from","-",$string);
	$string = str_replace("From","-",$string);
	$string = str_replace("UNION","-",$string);
	$string = str_replace("union","-",$string);
	$string = str_replace("Union","-",$string);
	$string = str_replace("DELETE","-",$string);
	$string = str_replace("delete","-",$string);
	$string = str_replace("Delete","-",$string);
	$string = str_replace("UPDATE","-",$string);
	$string = str_replace("update","-",$string);
	$string = str_replace("Update","-",$string);
	
	return($string);
}
///////////////////////////////////////////////////

//---
function CheckUser($username, $password) {
    global $mysqli;

    $stmt = $mysqli->prepare("SELECT id, password FROM admins WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $hashedPassword);
        $stmt->fetch();

        if (password_verify($password, $hashedPassword)) {
            return $id;
        }
    }

    return false;
}

function CheckUserById($username, $id) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT `id` FROM `admins` WHERE `id` = ? AND `username` = ?");
    $stmt->bind_param("is", $id, $username);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows === 1;
}


///////////////////////////////////////////////////////// get IP function
function getRealIpAddr()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
    {
      $ip=$_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
    {
      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else
    {
      $ip=$_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}
//////////////////////////////////////////////////////////////////////////////

function head($header) 
{
	print "<script> document.location = '$header'; </script>";
}
//////////////////////////////////////////////////////////////////////////////
function CreateThumb ($src, $dir, $name, $thumb_width)
{
	List($Width, $Height) = GetImageSize($src);
	$k = $thumb_width / max($Width, $Height);
	$newWidth = $Width * $k;
	$newHeight = $Height * $k;
	$Source = ImageCreateFromJpeg($src);
	$Thumb = ImageCreateTrueColor($newWidth, $newHeight);
	ImageCopyreSampled($Thumb, $Source, 0, 0, 0, 0, $newWidth, $newHeight, $Width, $Height);
	if (ImageJpeg($Thumb, $dir.$name,100)) { return true; } else { return false; }
}
/////////////////////////////////////////////////// CreateThumbwithWatermark function:
function CreateThumbwithWatermark ($src, $dir, $name, $thumb_width)
{
List($Width, $Height) = GetImageSize($src);
$k = $thumb_width / max($Width, $Height);
$newWidth = $Width * $k;
$newHeight = $Height * $k;
$Source = ImageCreateFromJpeg($src);
$image = ImageCreateTrueColor($newWidth, $newHeight);
ImageCopyreSampled($image, $Source, 0, 0, 0, 0, $newWidth, $newHeight, $Width, $Height);
// creating png image of watermark
$watermark = imagecreatefrompng('watermark.png');   
// getting dimensions of watermark image
$watermark_width = imagesx($watermark);  
$watermark_height = imagesy($watermark);  
//something went wrong 
if ($image === false) {
    return false;
} 
// getting the dimensions of original image
//$size = getimagesize("$image");  
// placing the watermark 5px from bottom and right
$dest_x = ($newWidth - $watermark_width)/2;  
$dest_y = ($newHeight - $watermark_height)/2;
// blending the images together
imagealphablending($image, true);
imagealphablending($watermark, true); 
// creating the new image
imagecopy($image,$watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height); 
imagejpeg($image,$dir.$name,100);  
// destroying and freeing memory
imagedestroy($image);  
imagedestroy($watermark); 
return True; 
}
//////////////////////////////////////////////////////////// get all parents
function generate_menu_list($current_group_parent_id, $direction = 0) {
	global $mysqli;
	$result=$mysqli->query("SELECT * FROM `menu` WHERE `id`='".$current_group_parent_id."' AND `active`='1' LIMIT 1");
	if ($result->num_rows > 0)
	{
		$row = $result-> fetch_array();
		if($row['parent_id']!='0'){
			generate_menu_list($row['parent_id'],$direction);
		}
		$name_slug=url_slug($row['name']);
		if($direction){
			$direction_text = 'left';
		}else{
			$direction_text = 'right';
		}
		echo '<li class="item"><i class="fa fa-angle-'.$direction_text.'" ></i><a href="menu/'.$row['id'].'/'.$name_slug.'/" title="'.$row['name'].'">'.$row['name'].'</a></li>
                        ';

	}		
}
//////////////////////////////////////////////////////////// get all parents
function ShortenText($string, $character = 30, $dot= true) {
    $length =strlen($string);
    $text = preg_replace (array('/<[^>]*>/','/[ -]+/'),array(' ',' ') , $string);
    if($dot == true){
        if($length > $character){
            return mb_substr($text, 0, $character,"UTF-8").' ...';
        }else{
            return mb_substr($text, 0, $character,"UTF-8");
        }
    }else if($dot == false){
        return mb_substr($text, 0, $character,"UTF-8");
    }

}
/////////////////////////////////////////////////////////
function sanitizeString($data,$type ='') {
    global $mysqli;
    $data =trim ( $data );
    if($data != null && $data != ''){

        if($type != 'date') $data = stripslashes ( $data );
        $data = htmlspecialchars($data);
        $data = mysqli_real_escape_string ($mysqli, $data );
        $data = filter_var($data, FILTER_SANITIZE_STRING);
        return $data;
    }else
        return false;

}


function sanitizeInt($int) {

    $int = trim($int);
    $int = stripslashes($int);
    $int = htmlspecialchars($int);
    if ($int != null && $int != '' && is_numeric($int))
        return $int;
    else
        return false;

}

function sanitizeEmail($email) {

    $email = trim($email);
    $email = stripslashes($email);
    $email = htmlspecialchars($email);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    // Validate e-mail
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        return $email;
    } else {
        return false;
    }

}
function sanitizeFloat($float) {

    $float = trim($float);
    $float = filter_var($float, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_SCIENTIFIC);
    if (!filter_var($float, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_SCIENTIFIC) === false)
        return $float;
    else
        return false;

}
//////////////////////////////////////////////////////////// get count of page of each menu
function menu_count_page($menu_id,$language) {
    global $mysqli;
    $result=$mysqli->query("SELECT `id` FROM `page` WHERE FIND_IN_SET('" . $menu_id . "', `parent_id`)>0 AND `active`='1' AND `deleted`='0' AND `language`='".$language."'");
    return ($result->num_rows);

}
function check_user_login()
{
    global $mysqli;

    // بررسی سشن
    if (isset($_SESSION['currentuser_mobile'], $_SESSION['currentuser_id'])) {
        $stmt = $mysqli->prepare("SELECT * FROM `user` WHERE `id` = ? AND `mobile` = ? AND `deleted` = 0 LIMIT 1");
        $stmt->bind_param("is", $_SESSION['currentuser_id'], $_SESSION['currentuser_mobile']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $currentuser = $result->fetch_assoc();
            $_SESSION["currentuser_name"] = $currentuser['name'];
            $_SESSION["currentuser_tel"] = $currentuser['tel'];
            $_SESSION["currentuser_email"] = $currentuser['email'];
            $_SESSION["code_meli"] = $currentuser['code_meli'];
            return true;
        }
    }

    // بررسی کوکی
    if (!empty($_COOKIE["user_cookie"])) {
        $user_cookie_readed = $_COOKIE["user_cookie"];
        $stmt = $mysqli->prepare("SELECT * FROM `user` WHERE `user_cookie` = ? AND `deleted` = 0 LIMIT 1");
        $stmt->bind_param("s", $user_cookie_readed);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $currentuser = $result->fetch_assoc();
            $_SESSION["currentuser_id"] = $currentuser['id'];
            $_SESSION["currentuser_mobile"] = $currentuser['mobile'];
            $_SESSION["currentuser_name"] = $currentuser['name'];
            $_SESSION["currentuser_tel"] = $currentuser['tel'];
            $_SESSION["currentuser_email"] = $currentuser['email'];
            $_SESSION["currentuser_code_meli"] = $currentuser['code_meli'];
            log_user_set($_SESSION["currentuser_id"], "ورود با کوکی");
            return true;
        }
    }

    return false;
}

// ثبت لاگ کاربر
function log_user_set($user_id, $log_type)
{
    global $mysqli;
    $ip = getRealIpAddr();
    $stmt = $mysqli->prepare("INSERT INTO `user_log` (`user_id`, `type`, `ip`) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $log_type, $ip);
    $stmt->execute();
}
function json_response($ok, $msg) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["ok"=>$ok, "message"=>$msg], JSON_UNESCAPED_UNICODE);
    exit;
}

function clean_phone($phone) {
    $p = preg_replace('/[^0-9+]/', '', $phone ?? '');
    return trim($p);
}

function send_otp_sms($phone, $code) {
    error_log("OTP to {$phone}: {$code}");
    return true;
}
function set_auth_cookie($token, $days = 30) {
    $params = [
        'expires'  => time() + (86400 * $days),
        'path'     => '/',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ];
    setcookie('auth_token', $token, $params);
}

?>