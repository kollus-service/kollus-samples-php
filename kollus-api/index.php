<?php

// kollus CMS 설정 페이지에서 API 접근 토큰을 확인 할 수 있습니다.
$access_token = '{ACCESS_TOKEN}';

// http endpoint API call
$api_url = 'http://api.kr.kollus.com/0/media_auth/upload/create_url.json?access_token='.$access_token;
$params = array(
	'expire_time' => 600,			// 값의 범위는 0 < expire_time <= 21600 입니다. 빈값을 보내거나 항목 자체를 제거하면 기본 600초로 설정됩니다.
	'category_key' => '{CATEGORY_KEY}',			// 업로드한 파일이 속할 카테고리의 키(API를 이용하여 확득 가능)입니다. 빈값을 보내거나 항목 자체를 제거하면 '없음'에 속합니다.
	
	'title' => '{TITLE}',					// 입력한 제목을 컨텐츠의 제목으로 강제지정합니다. 이 값을 보내지 않거나 빈값으로 보내면 기본적으로 파일명이 제목으로 사용됩니다.
	'is_encryption_upload' => 1,	// 0은 일반 업로드, 1은 암호화 업로드입니다. 암호화 업로드시 파일이 암호화 되어 Kollus의 전용 플레이어로만 재생됩니다.
	'is_audio_upload' => 0			// 0은 비디오 업로드, 1은 음원 파일 업로드입니다.
);

$result = http_curl_request($api_url, $params);
if($result) {
	$result = json_decode($result);
	
	if($result->error == '0') {
		$data = $result->result;
	} else {
		// 에러 발생 시 처리는 내부 로직으로 변경하여 처리해주세요.
		// echo '<pre>';
		// print_r($result);
		// echo '</pre>';
	}
} else {
	// 에러 발생 시 처리는 내부 로직으로 변경하여 처리해주세요.
}


function http_curl_request($url, $params = array()) {
	$curl = curl_init();

	// request
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	if(count($params) > 0) {
		curl_setopt($curl, CURLOPT_POST, TRUE);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
	}
	$response = curl_exec($curl);
	
	// response
	$info = curl_getinfo($curl);
	if(element('http_code', $info) != 200) {
		return FALSE;
	} else {
		return $response;
	}
}
function element($key, $array, $default = FALSE) {
	return isset($array[$key]) ? $array[$key] : $default;
}
?>
<html>
<head>
	<meta charset="utf-8" />
	<title>HTTP Upload Endpoint Sample</title>
	<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0,maximum-scale=1.0" />
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script>
function uploadFile(){
	var form = $('#form')[0];
	var formData = new FormData(form);
	formData.append("upload-file", $("#upload-file")[0].files[0]);
	formData.append("accept","application/json");
	$.ajax({
		url: '<?=$data->upload_url?>',
		processData: false,
		contentType: false,
		data: formData,
		type: 'POST',
		success: function(result){
			alert(result.message);
	}
	});
}
</script>
</head>
<body>
<h3>Content Upload</h3>

<form id="form">
	<!-- 업로드 종료시 redirect할 url 설정 -->
	<!-- 업로드 종료시 alert창을 띄우지 않도록 설정 (1) -->
	<input type="hidden" name="disable_alert" value="1" />
	<input type="file" id="upload-file" />
	<input type="button" value="업로드" onclick="uploadFile();" />
</form>
</body>
</html>
