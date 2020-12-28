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

$securityKey = ''; //콜러스 콘솔의 설정 페이지 -> 암호화 키
$customKey = ''; //콜러스 콘솔의 설정 페이지 -> 사용자 키
$mediaContentKey = ''; // 미디어 컨텐츠 키
$clientUserId = ''; // 홈페이지 사용자 아이디
$expireTime = 300; // 암호화 url 만료 시간
$mediaItems = array(
    array(
        'media_content_key' => $mediaContentKey,
    ),
//    array(
//        'media_content_key' => $otherMediaContentKey,
//        'intr' => true,
//        'is_seekable' => true,
//    ),
);

$payload = array(
	'video_watermarking_code_policy' => array(),
    'mc' => array(),
    'cuid' => $clientUserId,
    'expt' => time() + $expireTime,
);

$videoWaterMark = array(
	'code_kind' => '', //표시될 문자
	'font_size' => 7, //폰트 사이즈
	'font_color' => "FFFFFF", //폰트 색, html color code
	'show_time' => 10, //노출 시간
	'hide_time' => 1, //노출 시간 이후 숨겨질 시간
	'alpha' => 255, //투명도, 0 ~ 255까지 지정
	'enable_html5_player' => true // false 로 지정 시 전용 플레이어로 실행
);
$payload['video_watermarking_code_policy'] = $videoWaterMark;
foreach ($mediaItems as $mediaItem) {
    $mcClaim = array();
    $mcClaim['mckey'] = $mediaItem['media_content_key'];
    $payload['mc'][] = $mcClaim;
}

$jwtToken = jwt_encode($payload, $securityKey);

$webTokenURL = 'http://v.kr.kollus.com/s?jwt=' . $jwtToken . '&custom_key=' . $customKey . '&autoplay';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
</head>
<body>
    <iframe width="1280" height="720" src="<?php echo $webTokenURL; ?>" allowfullscreen webkitallowfullscreen mozallowfullscreen></iframe>
</body>
</html>
