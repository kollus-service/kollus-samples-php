<?php
/**
 * base64_urlencode
 *
 * @param string $str
 * @return string
 */
function base64_urlencode($str) {
    return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
}
/**
 * jwt_encode
 *
 * @param array $payload
 * @param string $key
 * @return string
 */
function jwt_encode($payload, $key) {
    $jwtHead = base64_urlencode(json_encode(array('typ' => 'JWT', 'alg' => 'HS256')));
    $jsonPayload = base64_urlencode(json_encode($payload));
    $signature = base64_urlencode(hash_hmac('SHA256', $jwtHead . '.' . $jsonPayload, $key, true));
    return $jwtHead . '.' . $jsonPayload . '.' . $signature;
}
$securityKey = 'SECURITY_KEY';
$customKey = 'CUSTOM_USER_KEY';
$lmckey = 'LIVE_CHANNEL_KEY';
$clientUserId = 'CLIENT_USER_ID';
$expireTime = 600; // 10 min

$payload = array(
    'lmckey' => $lmckey,
    'cuid' => $clientUserId,
    'expt' => time() + $expireTime,
);
$jwtToken = jwt_encode($payload, $securityKey);
$webTokenURL = 'https://v-live-kr.kollus.com/s?jwt=' . $jwtToken . '&custom_key=' . $customKey;
?>
<html lang="ko">
<head>
<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0,maximum-scale=1.0" />
</head>
<body>
<iframe width="100%" height="100%" src="<?=$webTokenURL?>" allowfullscreen webkitallowfullscreen mozallowfullscreen></iframe>
</body>
</html>
