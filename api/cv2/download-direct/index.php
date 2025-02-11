<?php

	// CV2: A free and open source Fall Guys content viewing and downloading beacon created by The CloudSeeker Collective (https://cloudseeker.xyz) <admin@cloudseeker.xyz>.

	header("Content-Type: application/json");
	header("X-Powered-By: CloudSeeker CV2");
	include("../connect.php");
	$debug = array();

	if(mt_rand(1,1500) == 1){
		header("HTTP/2 418 I'm a teapot");
		crashWithErrorCode("Did PTJ seriously plug in the teapot instead of the server?", "x_P_4180");
	}

	function triggerErrorFailsafe($error, $errorCode, $extra){
		header("Cache-Control: no-store, must-revalidate");
		if(file_exists("../latest_content")){
			echo('{"xstatus":"successWithPrecautions","download":"https://cloudseeker.xyz/api/cv2/download-direct/'. json_decode(file_get_contents("../latest_content"))->version .'.json","contentVersion":"'. json_decode(file_get_contents("../latest_content"))->version .'","notice":"The robots behind the scenes could not download the latest Fall Guys content file, so this file right here is the latest file known to CV2.", "debug":'. json_encode(["error" => $error, "errorCode" => $errorCode, "extra" => $extra]) .'}');
			exit;
		}
		else{
			crashWithErrorCode($error, $errorCode);
		}
	}

	$headers = array("X-Unity-Version: ". $_X_UNITY_VERSION, "Content-Type: application/json");
	$content = '{"type":"EosSignIn","token":"'. $_EOS_ACCOUNT_TOKEN .'","properties":null,"userParameters":{"lang":"'. $lang .'","locale":"'. $loc .'"},"clientVersion":"'. $_GAME_VERSION .'","clientVersionSignature":"'. $_CLIENT_SIG .'","platform":"ios_ega","contentBranch":null}';

	$curl_inst = curl_init();

	curl_setopt_array($curl_inst, array(
		CURLOPT_URL => $_CATAPULT_LOGIN_URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => '{"type":"EosSignIn","token":"'. $_EOS_ACCOUNT_TOKEN .'","properties":null,"userParameters":{"lang":"'. $lang .'","locale":"'. $loc .'"},"clientVersion":"'. $_GAME_VERSION .'","clientVersionSignature":"'. $_CLIENT_SIG .'","platform":"'. $_DEFAULT_PLATFORM .'","contentBranch":null}',
		CURLOPT_HTTPHEADER => array("X-Unity-Version: ". $_X_UNITY_VERSION, "Content-Type: application/json", "User-Agent: UnityPlayer/". $_X_UNITY_VERSION ." (UnityWebRequest/1.0, libcurl/7.84.0-DEV)")
	));

	$curl_res = curl_exec($curl_inst);
	$curlinfo = curl_getinfo($curl_inst);
	curl_close($curl_inst);

	if($curl_res == false){
		triggerErrorFailsafe("Could not connect to the Fall Guys server at this moment", "x_C_4200");
	}
	$curl_done = json_decode((string)$curl_res);
	$cv2_current = fopen("../../../../fg_response.json", "w+");
        fwrite($cv2_current, json_encode($curl_done));
	if(empty($curl_done->contentUrl)){
		triggerErrorFailsafe("Could not connect to the Fall Guys server at this moment", "x_C_4300", $curlinfo);
	}
	$cv2_download_link = $curl_done->contentUrl;

	$content_file = $curl_done->contentVersion . ".json";
	if(isset($_GET["mobile"])){
		$content_file = $curl_done->contentVersion . "_M.json";
	}

	if(!file_exists($cv2_lang . "/" . $content_file)){
		header("Cache-Control: no-store, must-revalidate");
		$curl_cv2 = curl_init();
		curl_setopt($curl_cv2, CURLOPT_URL, $cv2_download_link);
		curl_setopt($curl_cv2, CURLOPT_USERAGENT, "User-Agent: UnityPlayer/". $_X_UNITY_VERSION ." (UnityWebRequest/1.0, libcurl/7.84.0-DEV)");
		curl_setopt($curl_cv2, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl_cv2, CURLOPT_RETURNTRANSFER, true);

		$curl_cv2_res = curl_exec($curl_cv2);

		if($curl_cv2_res == false){
			crashWithErrorCode("Content file could not be downloaded", "x_F_4010");
		}
		$cv2_current = fopen($cv2_lang . "/" . $content_file, "w+");
		fwrite($cv2_current, $curl_cv2_res);
	}
	else{
		$curl_cv2_res = file_get_contents($cv2_lang . "/" . $curl_done->contentVersion . ".json");
	}
	if(file_exists("../latest_content")){
		file_put_contents("../latest_content", '{"version":"'. $curl_done->contentVersion .'"}');
	}
	$return_object = [
		"xstatus" => "success",
		"locale" => $loc,
		"notice" => null,
		"download" => "https://cloudseeker.xyz/api/cv2/download-direct/" . $cv2_lang . "/" . $content_file,
		"contentVersion" => $curl_done->contentVersion,
		"environment" => [
			"environment_id" => $_CATAPULT_ENVIRONMENT,
			"game_version" => $_GAME_VERSION,
			"client_signature" => $_CLIENT_SIG
		],
		"debug" => $debug
	];
	if($_HAS_SITEWIDE_ANNOUNCEMENT){
		$return_object["notice"] = $_SITEWIDE_ANNOUNCEMENT_CONTENTS;
	}
	echo(json_encode($return_object));

?>
