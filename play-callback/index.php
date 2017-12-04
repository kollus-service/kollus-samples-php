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
$customKey = 'CUSTOM_KEY';

$kind = isset($_POST['kind']) ? (int)$_POST['kind'] : null;
$clientUserId = isset($_POST['client_user_id']) ? $_POST['client_user_id'] : null;
$mediaContentKey = isset($_POST['media_content_key']) ? $_POST['media_content_key'] : null;

$result = array(
    'data' => array()
);

$callbackResult = true;

switch($kind) {
    case 1:
        $result['data']['result'] =  (int)$callbackResult;
        $result['data']['expiration_date'] = time() + 60 * 10; // 10 min
        // TODO: try more options

        if (!$result['data']['result']) {
            $result['data']['message'] = 'This video is not permitted to you';
        }

        break;
    case 3:
        $result['data']['result'] =  (int)$callbackResult;
        // TODO: try more options

        if (!$result['data']['result']) {
            $result['data']['message'] = 'This video is not permitted to you';
        }

        break;
}

header('Content-Type', 'text/plain; charset=utf-8');
header('X-Kollus-UserKey', $customKey);
echo jwt_encode($result, $securityKey);
