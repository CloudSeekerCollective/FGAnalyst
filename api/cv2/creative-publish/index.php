<?php

	// CV2: A free and open source Fall Guys content viewing and downloading beacon created by The CloudSeeker Collective (https://cloudseeker.xyz) <admin@cloudseeker.xyz>.

	header("Content-Type: application/json");
	header("X-Powered-By: CloudSeeker CV2");
	include("../connect.php");
	$debug = array("thing" => time() >= strtotime($_EOS_EXPIRE));
	$should_try = true;
	$publish = 0;

	if(empty($_POST["share_code"]))
		crashWithErrorCode("x_P_4500", "Please provide a share code using the share_code GET argument!");
	$share_code = stripslashes(htmlspecialchars($_POST["share_code"]));
	if(!isset($_POST["publish"]))
		crashWithErrorCode("x_P_4501", "Please provide a publish status using the publish GET argument!");
	$publish = stripslashes(htmlspecialchars($_POST["publish"]));
	if($publish != "0" and $publish != "1")
		crashWithErrorCode("x_P_4511", "The publish GET argument MUST be equal to 0 (to publish) or 1 (to unpublish).");
	if(!empty($_POST["version"]))
		$version = stripslashes(htmlspecialchars($_POST["version"]));
	else
		crashWithErrorCode("x_P_4502", "Please provide the level version using the version GET argument!");
	if(!isset($_POST["token"]))
		crashWithErrorCode("x_P_4501", "Please provide an EOS token status using the token GET argument!");
	$token = stripslashes(htmlspecialchars($_POST["token"]));
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

	$headers = array("X-Unity-Version: ". $_X_UNITY_VERSION, "Content-Type: application/json");
	$content = '{"type":"EosSignIn","token":"'. $token .'","properties":null,"userParameters":{"lang":"'. $lang .'","locale":"'. $loc .'"},"clientVersion":"'. $_GAME_VERSION .'","clientVersionSignature":"'. $_CLIENT_SIG .'","platform":"win","contentBranch":null}';

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
		CURLOPT_POSTFIELDS => '{"type":"EosSignIn","token":"'. $token .'","properties":null,"userParameters":{"lang":"'. $lang .'","locale":"'. $loc .'"},"clientVersion":"'. $_GAME_VERSION .'","clientVersionSignature":"'. $_CLIENT_SIG .'","platform":"win","contentBranch":null}',
		CURLOPT_HTTPHEADER => array("X-Unity-Version: ". $_X_UNITY_VERSION, "Content-Type: application/json", "User-Agent: UnityPlayer/". $_X_UNITY_VERSION ." (UnityWebRequest/1.0, libcurl/7.84.0-DEV)")
	));
	$_final;
	$curl_res = curl_exec($curl_inst);
	curl_close($curl_inst);

	$curl_done = json_decode($curl_res);
	$cv2_download_link = $curl_done->contentUrl;
	$content_version = $curl_done->contentVersion;

	if($curl_res == false){
		triggerErrorFailsafe("Could not connect to the Fall Guys server at this moment", "x_C_4200");
	}
	$curl_done = json_decode((string)$curl_res);

	if(empty($curl_done)){
		triggerErrorFailsafe("Could not connect to the Fall Guys server at this moment", "x_C_4200");
	}
	if(empty($curl_done->token)){
		triggerErrorFailsafe("Could not connect to the Fall Guys server at this moment", "x_C_4300");
	}
	$cv2_fg_token = $curl_done->token;

	$curl_inst_2 = curl_init();

	if(empty($version))
		$req_url = 'https://level-gateway.fallguys.oncatapult.com/Level/' . $share_code;
	else
		$req_url = 'https://level-gateway.fallguys.oncatapult.com/Level/' . $share_code . "/" . $version;

	curl_setopt_array($curl_inst_2, array(
		CURLOPT_URL => $req_url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'PATCH',
		CURLOPT_POSTFIELDS =>'{ "level_state": '. $publish .', "is_completed": false }',
		CURLOPT_HTTPHEADER => array(
			'User-Agent: UnityPlayer/2021.3.16f1 (UnityWebRequest/1.0, libcurl/7.84.0-DEV)',
			'X-Unity-Version: 2021.3.16f1',
			'Content-Type: application/json',
			'Authorization: Bearer ' . $cv2_fg_token
		),
	));

	//var_dump($req_url);

	$curl_res_2 = curl_exec($curl_inst_2);
	curl_close($curl_inst_2);

	if($curl_res_2 == false){
		triggerErrorFailsafe("Could not connect to the Fall Guys server at this moment", "x_C_4200");
	}
	$level_data = json_decode($curl_res_2);

	if(empty($level_data->snapshot) and empty($version))
		crashWithErrorCode("No level with that code has been found.", "x_P_4440");
	elseif(empty($level_data->snapshot) and !empty($version))
		crashWithErrorCode("This level could not be published!", "x_P_4441");
	//$level_data->snapshot->author->nickname_content_id = getLocalisedString($level_data->snapshot->author->nickname_content_id, $_final->localised_strings);
	$return_object = [
		"xstatus" => "success",
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
