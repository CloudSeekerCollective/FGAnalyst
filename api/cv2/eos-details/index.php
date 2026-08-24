<?php

	// CV2: A free and open source Fall Guys content viewing and downloading beacon created by The CloudSeeker Collective (https://cloudseeker.xyz) <admin@cloudseeker.xyz>.

	header("Content-Type: application/json");
	header("X-Powered-By: CloudSeeker CV2");
	include("../connect.php");
	header("Cache-Control: must-revalidate");
	$debug = array("thing" => time() >= strtotime($_EOS_EXPIRE));
	$should_try = true;

	function triggerErrorFailsafe($error, $errorCode){
		header("Cache-Control: no-store, must-revalidate");
		crashWithErrorCode($error, $errorCode);
		if(file_exists("../latest_content")){
			echo('{"xstatus":"successWithPrecautions","download":"https://cloudseeker.xyz/api/cv2/download-direct/'. json_decode(file_get_contents("../latest_content"))->version .'.json","contentVersion":"'. json_decode(file_get_contents("../latest_content"))->version .'","notice":"The robots behind the scenes could not download the latest Fall Guys content file, so this file right here is the latest file known to CV2.", "debug":'. json_encode(["error" => $error, "errorCode" => $errorCode, "token" => $_EOS_ACCOUNT_TOKEN]) .'}');
			exit;
		}
		else{
			crashWithErrorCode($error, $errorCode);
		}
	}

	if(true){
		$curl_inst = curl_init();
		$body = json_decode(file_get_contents('php://input'));
		if(!empty($body->access_token))
			$devcode = stripslashes(htmlspecialchars($body->access_token));
		else
			crashWithErrorCode("Please provide an access token to continue!" . var_dump($body), "x_P_4700");
		$headers = array("X-Unity-Version: ". $_X_UNITY_VERSION, "Content-Type: application/json");
		$content = '{"type":"EosSignIn","token":"'. $devcode .'","properties":null,"userParameters":{"lang":"'. $lang .'","locale":"'. $loc .'"},"clientVersion":"'. $_GAME_VERSION .'","clientVersionSignature":"'. $_CLIENT_SIG .'","platform":"win","contentBranch":null}';

		$curl_inst = curl_init();
		curl_setopt_array($curl_inst, array(
			CURLOPT_URL => 'https://login.fallguys.oncatapult.com/api/v1/login',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => '{"type":"EosSignIn","token":"'. $devcode .'","properties":null,"userParameters":{"lang":"'. $lang .'","locale":"'. $loc .'"},"clientVersion":"'. $_GAME_VERSION .'","clientVersionSignature":"'. $_CLIENT_SIG .'","platform":"win","contentBranch":null}',
			CURLOPT_HTTPHEADER => array("X-Unity-Version: ". $_X_UNITY_VERSION, "Content-Type: application/json", "User-Agent: UnityPlayer/". $_X_UNITY_VERSION ." (UnityWebRequest/1.0, libcurl/7.84.0-DEV)")
		));
		$_final;
		$curl_res = curl_exec($curl_inst);
		curl_close($curl_inst);

		$curl_done = json_decode($curl_res);
		$return_object = [];
		if(!empty($curl_done->token)){
			$return_object = [
				"xstatus" => "success",
				"display_name" => $curl_done->additionalProperties->EosDisplayName,
				"fallguys_response" => $curl_done,
				"environment" => [
	                        	"environment_id" => $_CATAPULT_ENVIRONMENT,
	                        	"game_version" => $_GAME_VERSION,
	                        	"client_signature" => $_CLIENT_SIG
	                	]
			];
		}
		else{
			$return_object = [
				"xstatus" => "fail",
				"error" => "Something went wrong while getting Fall Guys account information!",
				"errorCode" => "x_C_4200",
				"fallguys_response" => $curl_done,
				"serverFeeling" => "sad"
			];
		}
		//echo json_encode($curl_done);
		echo json_encode($return_object);
		exit;
	}
	else{
		exit;
	}

?>
