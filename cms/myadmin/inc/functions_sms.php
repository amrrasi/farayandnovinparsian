<?php
///////////////////////////////////////////////////////// GHASEDAK SMS
function send_sms_ghasedak($mobile,$sms_code,$template)
{
    $curl = curl_init();
    curl_setopt_array($curl,
        array(
            CURLOPT_URL => "https://api.ghasedak.me/v2/verification/send/simple",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "type=1&param1=".$sms_code."&receptor=".$mobile."&template=".$template,
            CURLOPT_HTTPHEADER => array(
                "apikey: 75a685e6bf88e7ec9bc3fe46377deed6aaed9784c97e5a34a702dd57aa8d59d5",
                "cache-control: no-cache",
                "content-type: application/x-www-form-urlencoded",
            ),
        ));
    $response = curl_exec($curl);

    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        return false;
    } else {
        return $response;
    }

}
function send_sms_template($mobile,$array,$template){
    $param_list='';
    $i=1;
    foreach ($array as $param){
        $param_list.="&param".$i."=".$param;
        $i++;
    }

    $curl = curl_init();
    curl_setopt_array($curl,
        array(
            CURLOPT_URL => "https://api.ghasedak.me/v2/verification/send/simple",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "type=1".$param_list."&receptor=".$mobile."&template=".$template,
            CURLOPT_HTTPHEADER => array(
                "apikey: 75a685e6bf88e7ec9bc3fe46377deed6aaed9784c97e5a34a702dd57aa8d59d5",
                "cache-control: no-cache",
                "content-type: application/x-www-form-urlencoded",
            ),
        ));
    $response = curl_exec($curl);

    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        return false;
    } else {
        return $response;
    }

}
function send_sms($receptor, $message){

    $curl = curl_init();
    curl_setopt_array($curl,
        array(
            CURLOPT_URL => "https://api.ghasedak.me/v2/sms/send/simple",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "receptor=".$receptor."&message=".$message,
            CURLOPT_HTTPHEADER => array(
                "apikey: 75a685e6bf88e7ec9bc3fe46377deed6aaed9784c97e5a34a702dd57aa8d59d5",
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded',
                'charset: utf-8'
            ),
        ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        return false;
    } else {
        return $response;
    }


}
?>