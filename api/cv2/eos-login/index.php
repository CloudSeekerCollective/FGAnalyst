<?php

	// CV2: A free and open source Fall Guys content viewing and downloading beacon created by The CloudSeeker Collective (https://cloudseeker.xyz) <admin@cloudseeker.xyz>.

	header("Content-Type: application/json");
	header("X-Powered-By: CloudSeeker CV2");
	include("../connect.php");
	header("Cache-Control: must-revalidate");
	$debug = array("thing" => time() >= strtotime($_EOS_EXPIRE));
	$should_try = true;

	if(empty($_GET["stage"]))
		crashWithErrorCode("x_P_4500", "Please provide a login stage number to proceed");
	$stage = stripslashes(htmlspecialchars($_GET["stage"]));
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

	if($stage == "1"){
		$curl_inst = curl_init();
		curl_setopt_array($curl_inst, array(
			CURLOPT_URL => 'https://api.epicgames.dev/epic/oauth/v2/deviceAuthorization',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => 'prompt=login&client_id=xyza7891mADED0tPNJFOiF8OmI0DwY0J&client_secret=8w2sDwL5%2FGuUjeVbHZIxe1FAFwi%2BtuQI2msSCVIO%2BEA',
			CURLOPT_HTTPHEADER => array("User-Agent: PeaBoisHQ/1.0")
		));
		$_final;
		$curl_res = curl_exec($curl_inst);
		$curl_done = json_decode($curl_res);
		curl_close($curl_inst);
		$return_object = [];
		if(empty($curl_done->error)){
			$return_object = [
				"xstatus" => "success",
				"device_code_int" => $curl_done->device_code,
				"return_url" => $curl_done->verification_uri_complete,
				"next_stage" => "2",
				"environment" => [
		                        "environment_id" => $_CATAPULT_ENVIRONMENT,
		                        "game_version" => $_GAME_VERSION,
		                        "client_signature" => $_CLIENT_SIG
		                ]
			];
		}
		//echo json_encode($curl_done);
		echo json_encode($return_object);
		exit;
	}
	elseif($stage == "2"){
		$curl_inst = curl_init();
		if(!empty($_GET["device_code"]))
			$devcode = stripslashes(htmlspecialchars($_GET["device_code"]));
		curl_setopt_array($curl_inst, array(
			CURLOPT_URL => 'https://api.epicgames.dev/epic/oauth/v2/token',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => 'grant_type=device_code&deployment_id=8bedfebaf56f406ebab78986ada3f9b3&scope=basic_profile%20friends_list%20presence&device_code=' . $devcode,
			CURLOPT_HTTPHEADER => array("User-Agent: PeaBoisHQ/1.0", "Authorization: Basic eHl6YTc4OTFtQURFRDB0UE5KRk9pRjhPbUkwRHdZMEo6OHcyc0R3TDUvR3VVamVWYkhaSXhlMUZBRndpK3R1UUkybXNTQ1ZJTytFQQ==")
		));
		$_final;
		$curl_res = curl_exec($curl_inst);
		$curl_done = json_decode($curl_res);
		curl_close($curl_inst);
		$return_object = [];
		if(empty($curl_done->error)){
			$return_object = [
				"xstatus" => "success",
				"access_token" => $curl_done->access_token,
				"refresh_token" => $curl_done->refresh_token,
				"next_stage" => "3",
				//"full_response" => $curl_done,
				"environment" => [
		                        "environment_id" => $_CATAPULT_ENVIRONMENT,
		                        "game_version" => $_GAME_VERSION,
		                        "client_signature" => $_CLIENT_SIG
		                ]
			];
		}
		//echo json_encode($curl_done);
		echo json_encode($return_object);
		exit;
	}
	elseif($stage == "3"){
		$curl_inst = curl_init();
		if(!empty($_GET["access_token"]))
			$devcode = stripslashes(htmlspecialchars($_GET["access_token"]));
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
				"token" => $curl_done->token,
				"display_name" => $curl_done->additionalProperties->EosDisplayName,
				"support_id" => $curl_done->identities->CatapultSupport,
				//"full_response" => $curl_done,
				"environment" => [
	                        	"environment_id" => $_CATAPULT_ENVIRONMENT,
	                        	"game_version" => $_GAME_VERSION,
	                        	"client_signature" => $_CLIENT_SIG
	                	]
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
