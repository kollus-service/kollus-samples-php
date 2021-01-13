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
$customKey = 'CUSTOME_KEY';
$mediaContentKey = 'MEDIA_CONTENT_KEY';
$clientUserId = 'CLIENT_USER_ID';
$expireTime = 7200; // 120 min
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
    'mc' => array(),
    'cuid' => $clientUserId,
    'expt' => time() + $expireTime,
);
foreach ($mediaItems as $mediaItem) {
    $mcClaim = array();
    $mcClaim['mckey'] = $mediaItem['media_content_key'];
//    $mcClaim['mcpf'] = $mediaProfileKey;
//    $mcClaim['intr'] = (int)$mediaItem['is_intro'];
//    $mcClaim['seek'] = (int)$mediaItem['is_seekable'];
//    $mcClaim['seekable_end'] = $seekableEnd;
//    $mcClaim['disable_playrate'] = (int)$disablePlayrate;
    $payload['mc'][] = $mcClaim;
}
$jwtToken = jwt_encode($payload, $securityKey);
$webTokenURL = 'http://v.kr.kollus.com/s?jwt=' . $jwtToken . '&custom_key=' . $customKey . '&autoplay';
?>
<html lang="ko">
<head>
    <title></title>
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0,maximum-scale=1.0" />
	<script src="http://code.jquery.com/jquery-latest.min.js"></script>

	<script src="https://uicdn.toast.com/tui.code-snippet/latest/tui-code-snippet.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/ua-parser-js@0/dist/ua-parser.min.js"></script>
	<script src="https://uicdn.toast.com/tui-app-loader/latest/tui-app-loader.js"></script>
	<script type="text/javascript">
	    /**
	    * kollus Player 모바일 전용플레이어 호출
	    *
	    * kollus Player 모바일 전용플레이어 연동 시 스트리밍, 다운로드 호출을 위한 method 입니다.
	    *
	    * @access	public
	    * @param	string		method		    스트리밍, 다운로드 구분 (path : 스트리밍, download : 다운로드
	    * @param	string		jwt     jwt 함수로 암호화된 스트링
	    * @param	string		custom_key     CMS의 설정페에지에 있는 사용자 키
	    * @return	void
	    */
	    function call_player(method, jwt, custom_key) {
	    	var scheme_param = method + '?url='+encodeURIComponent('http://v.kr.kollus.com/si?jwt=' + jwt+'&custom_key='+custom_key);
	        kollus_custom_scheme_call(scheme_param);
		}
		
	    /**
	    * kollus 모바일 전용플레이어 멀티 다운로드 호출
	    *
	    * kollus 모바일 전용플레이어 멀티 다운로드를 위한 method 입니다.
	    *
	    * @access	public
	    * @return	void
	    */
	    function start_downloads() {
	        var chk_info = document.media_form;
	        var count = 0;
	        var url_list = "";

	        for (i = 0; i < chk_info.length; i++) {
	            if (chk_info[i].checked == true) {
	                if (count == 0) {
	                    url_list += "?url=";
	                }
	                if (count > 0) {
	                    url_list += "&url=";
	                }
	                url_list += chk_info[i].value;
	                count += 1;
	            }
	        }

	        if (count == 0) {
	            alert("다운로드 항목을 선택해 주세요.");
	            return;
	        }
	        
	        var scheme_param = 'download' + url_list;
	        kollus_custom_scheme_call(scheme_param);
	    }
	    
	    function kollus_custom_scheme_call(scheme_param) {
			var AppLoader = tui.AppLoader;
			var loader = new AppLoader();
			var osName = new UAParser().getResult().os.name;
				loader.exec({
					ios: {
						scheme: 'kollus://' + scheme_param,
						url: "https://itunes.apple.com/app/id760006888"
					},
					android: {
						intentURI: 'kollus://' + scheme_param
					},
					timerSet: {
						ios: 4000,
						android: 4000
					},
					etcCallback: function() {
						if (osName !== 'iOS' && osName !== 'Android') {
								alert('모바일 단말에서 실행하셔야 합니다.');
							}
						},
					notFoundCallback: function() {
						if (osName === 'iOS') {
							window.location.href = 'https://itunes.apple.com/app/id760006888';
						}
						else if(osName =='Android'){
							window.location.href = 'market://details?id=com.kollus.media';
						}
					}
				});
		}
	</script>
</head>
<body>
	<p>
		<h3>모바일 전용플레이어 다운로드 호출(직접호출)</h3>
		<div id="Div4" style="border:solid;">
			<ol>
				<li>
					<a href="javascript:void(0);" onclick="call_player('download', '<?php echo $jwtToken;?>','<?php echo $customKey;?>','');">Download</a>
				</li>
			</ol>
	    </div>
		<h3>모바일 전용플레이어 스트리밍 호출(직접호출)</h3>
		<div id="Div4" style="border:solid;">
			<ol>
				<li>
					<a href="javascript:void(0);" onclick="call_player('path', '<?php echo $jwtToken;?>','<?php echo $customKey;?>');">Play</a>
				</li>
			</ol>
	    </div>
	</p>
</body>
</html>
