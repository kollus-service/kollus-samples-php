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

$items = isset($_POST['items']) ? $_POST['items'] : '';
$items = empty($items) ? array() : json_decode($items, true);

$result = array(
    'data' => array()
);

foreach ($items as $item) {
    $kind = isset($item['kind']) ? (int)$item['kind'] : null;
    $clientUserId = isset($item['client_user_id']) ? $item['client_user_id'] : null;
    $mediaContentKey = isset($item['media_content_key']) ? $item['media_content_key'] : null;
    $startAt = isset($item['start_at']) ? $item['start_at'] : null;

    $callbackResult = true;

    $itemResult = [
        'result' => (int)$callbackResult,
        'media_content_key' => $mediaContentKey,
    ];
    switch($kind) {
        case 1:
            $itemResult['kind'] = 1;
            // TODO: try more options

            if (!$itemResult['result']) {
                $itemResult['message'] = 'This video is not permitted to you';
            }
            break;
        case 2:
            // TODO: marking download 'done' to db.
            $itemResult['kind'] = 2;
            // TODO: try more options

            if (!$itemResult['result']) {
                $itemResult['message'] = 'This video is not permitted to you';
            }
            break;
        case 3:
            $itemResult['kind'] = 3;
            // TODO: try more options

            if (isset($data['start_at'])) {
                $itemResult['start_at'] = $startAt;
            }

            if (!$itemResult['result']) {
                $itemResult['message'] = 'This video is not permitted to you';
            }
            break;
    }

    if (!empty($result)) {
        $result['data'][] = $itemResult;
    }
}

header('Content-Type:text/plain; charset=utf-8');
header('X-Kollus-UserKey:' . $customKey);
echo jwt_encode($result, $securityKey);
