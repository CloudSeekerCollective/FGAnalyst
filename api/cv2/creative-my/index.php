<?php

	// CV2: A free and open source Fall Guys content viewing and downloading beacon created by The CloudSeeker Collective (https://cloudseeker.xyz) <admin@cloudseeker.xyz>.

	header("Content-Type: application/json");
	header("X-Powered-By: CloudSeeker CV2");
	include("../connect.php");
	$debug = array("thing" => time() >= strtotime($_EOS_EXPIRE));
	$should_try = true;
	//var_dump(getallheaders());
	//var_dump($_SERVER);

	//var_dump($_POST);
	//exit;
	if(empty($_POST["token"]))
		crashWithErrorCode("x_P_4500", "Please log into Fall Guys to proceed!!!");
	// the amount of trimming this thing has to go through is unreal. all in the name of not getting hacked
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
	//var_dump($curl_done);
	$cv2_download_link = $curl_done->contentUrl;
	$content_version = $curl_done->contentVersion;

		if(!file_exists("../download-direct/". $cv2_lang ."/". $content_version . ".json") and $should_try == true or empty(file_get_contents("../download-direct/". $cv2_lang ."/". $content_version . ".json")) and $should_try == true){
                        header("Cache-Control: no-store, must-revalidate");
                        $curl_cv2 = curl_init();
                        curl_setopt($curl_cv2, CURLOPT_URL, $cv2_download_link);
                        curl_setopt($curl_cv2, CURLOPT_USERAGENT, "User-Agent: UnityPlayer/". $_X_UNITY_VERSION ." (UnityWebRequest/1.0, libcurl/7.84.0-DEV)");
                        curl_setopt($curl_cv2, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($curl_cv2, CURLOPT_RETURNTRANSFER, true);

                        $curl_cv2_res = curl_exec($curl_cv2);
                        $cv2_current = fopen("../download-direct/". $cv2_lang ."/". $content_version . ".json", "w+");
                        fwrite($cv2_current, $curl_cv2_res);
                        if($curl_cv2_res == false){
                                crashWithErrorCode("Content file could not be downloaded", "x_F_4010");
                        }
                }
                $curl_cv2_res = file_get_contents("../download-direct/". $cv2_lang ."/". $content_version .".json");
                $_final = json_decode($curl_cv2_res);

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

	$req_url = 'https://level-gateway.fallguys.oncatapult.com/Level';

	curl_setopt_array($curl_inst_2, array(
		CURLOPT_URL => $req_url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		//CURLOPT_CUSTOMREQUEST => 'GET',
		//CURLOPT_POSTFIELDS =>'{"share_codes":[""]}',
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

	$return_object = [
		"xstatus" => "success",
		"level_data" => $level_data,
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
