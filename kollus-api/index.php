<?php

$apiAccessToken = 'API_ACCESS_TOKEN';

$url = "https://api.kr.kollus.com/0/media_auth/upload/create_url.json";
$isPost = true;
$postParams = 'access_token='.$apiAccessToken;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, $isPost);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postParams);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec ($ch);
$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "status_code:".$status_code."\n";

curl_close ($ch);
if($status_code == 200) {
    echo "MESSAGE:".$response;
} else {
    echo "ERROR MESSAGE:".$response;
}
