<?php
/* header('Content-Type: application/json');

//insert notif
$params = '{Raw:"",Data1:"Information  company",Data2:"37421590",Data3:"EAAVq2DTNaZAwKhGyIU0j08t4nBGyAPnwZD",Data4:"",Data5:" ",Data6:"",Data7:"",Data8:"",Data9:"",Data10:""}';
$params_final = str_replace(' ', '%20', $params);
//API Url send agent
$url = 'https://invision.ddns.net:6008/ApiBounty2/Service1.svc/insert_mana?value='.$params_final;


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
// curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POST, 0);

$headers = array();
$headers[] = 'Content-Type: application/json';
// $headers[] = 'Header: Dota2';
// $headers[] = 'Content-Length: 0';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$result = curl_exec($ch);
curl_close($ch);
echo $result; */


// Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
$ch = curl_init();
//insert notif
$params = '{Raw:"",Data1:"data test malik",Data2:"1231",Data3:"1231",Data4:"",Data5:" ",Data6:"",Data7:"",Data8:"",Data9:"",Data10:""}';
$params_final = str_replace(' ', '%20', $params);
//API Url send agent
// $url = 'http://118.99.73.10:30008/ApiBounty2/Service1.svc/insert_mana?value='.$params_final;
// $url = 'https://invision.ddns.net:6008/ApiBounty2/Service1.svc/insert_mana?value='.$params_final;
$url = 'https://118.99.73.10:30008/ApiBounty2/Service1.svc/insert_mana?value='.$params_final;

// curl_setopt($ch, CURLOPT_URL, $url);
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POST, 1);

$headers = array();
$headers[] = 'Content-Type: application/json';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$result = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
}
curl_close($ch);


?>

