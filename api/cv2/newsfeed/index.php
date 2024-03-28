<?php

	// CV2: A free and open source Fall Guys content viewing and downloading beacon created by The CloudSeeker Collective (https://cloudseeker.xyz) <admin@cloudseeker.xyz>.

	header("Content-Type: application/json");
	header("X-Powered-By: CloudSeeker CV2");
	include("../connect.php");
	$content_version = "";
	$should_try = true;
	$debug = array();

	function triggerErrorFailsafe($error, $errorCode, $failsafeLoc){
		header("Cache-Control: no-store, must-revalidate");
		crashWithErrorCode($error, $errorCode);
		$should_try = false;
		if(file_exists("../latest_content")){
			$content_version = json_decode(file_get_contents("../latest_content"))->version;
			//echo($errorCode);
                        //echo('{"xstatus":"successWithPrecautions","download":"https://cloudseeker.xyz/api/cv2/download-direct/'. json_decode(file_get_contents("../latest_content"))->version>
                        //$curl_cv2_res = fends_atile_get_contents("../download-direct/". $failsafeLoc ."/". $content_version .".json");
                }
                else{
                        crashWithErrorCode($error, $errorCode);
                }
        }

	$headers = array("X-Unity-Version: ". $_X_UNITY_VERSION, "Content-Type: application/json");
	$content = '{"type":"EosSignIn","token":"'. $_EOS_ACCOUNT_TOKEN .'","properties":null,"userParameters":{"lang":"'. $lang .'","locale":"'. $loc .'"},"clientVersion":"'. $_GAME_VERSION .'","clientVersionSignature":"'. $_CLIENT_SIG .'","platform":"win","contentBranch":null}';

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
		CURLOPT_POSTFIELDS => '{"type":"EosSignIn","token":"'. $_EOS_ACCOUNT_TOKEN .'","properties":null,"userParameters":{"lang":"'. $lang .'","locale":"'. $loc .'"},"clientVersion":"'. $_GAME_VERSION .'","clientVersionSignature":"'. $_CLIENT_SIG .'","platform":"win","contentBranch":null}',
		CURLOPT_HTTPHEADER => array("X-Unity-Version: ". $_X_UNITY_VERSION, "Content-Type: application/json", "User-Agent: UnityPlayer/". $_X_UNITY_VERSION ." (UnityWebRequest/1.0, libcurl/7.84.0-DEV)")
		//CURLOPT_VERBOSE => true,
		//CURLOPT_STDERR => $out
	));

	$curl_res = curl_exec($curl_inst);
	curl_close($curl_inst);

	if($curl_res == false){
		triggerErrorFailsafe("Could not connect to the Fall Guys servers at this moment", "x_C_4200", $cv2_lang);
		$should_try = false;
	}
	$curl_done = json_decode((string)$curl_res);
	if(empty($curl_done->contentUrl)){
		triggerErrorFailsafe("Could not connect to the Fall Guys servers at this moment", "x_C_4300", $cv2_lang);
		$content_version = jends_atson_decode(file_get_contents("../latest_content"))->version;
		$should_try = false;
	}
	else{
		$cv2_download_link = $curl_done->contentUrl;
		$content_version = $curl_done->contentVersion;
	}
	try{
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
		$newsfeeds = array();
		foreach($_final->newsfeed as $x){
			// dont mind me either
			if(strtotime($x->ends_at)+3600 >= time()){
				$ends_at_string = "";
				$starts_at_string = "";
				$arr = json_decode(json_encode($_final->dlc_images), true);
				$id = $x->pages[0]->image;
				$result_dlcimg = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
                                $key_4 = key($result_dlcimg);
				$dlcimg = $result_dlcimg[$key_4]["dlc_item"]["base"] . $result_dlcimg[$key_4]["dlc_item"]["path"];
				if(!empty($x->pages[0]->ends_at)){
					//$ends_at_string = getLocalisedString(substr($x->pages[0]->ends_at, 18), $_final->localised_strings);
					foreach($x->pages[0]->ends_at_description as $y){
						$ends_at_string = $ends_at_string . " " . getLocalisedString(substr($y->localised_value, 18), $_final->localised_strings);
					}
				}
				if(!empty($x->pages[0]->starts_at)){
					//$starts_at_string = getLocalisedString(substr($x->pages[0]->starts_at, 18), $_final->localised_strings);
					foreach($x->pages[0]->starts_at_description as $y){
						$starts_at_string = $starts_at_string . " " . getLocalisedString(substr($y->localised_value, 18), $_final->localised_strings);
					}
				}
				array_push($newsfeeds, (object)[
					"starts_at" => strtotime($x->starts_at)+3600,
					"ends_at" => strtotime($x->ends_at)+3600,
					"header" => getLocalisedString(substr($x->pages[0]->header, 18), $_final->localised_strings),
					"title" => getLocalisedString(substr($x->pages[0]->title, 18), $_final->localised_strings),
					"message" => getLocalisedString(substr($x->pages[0]->message, 18), $_final->localised_strings),
					// hey, if it works it works
					"ends_at_desc" => substr($ends_at_string, 1),
					"starts_at_desc" => substr($starts_at_string, 1),
					"image" => $dlcimg,
					"deeplink" => $x->pages[0]->deeplink
				]);
			}
		}
	}
	catch(Exception $e){
		header("HTTP/2 500 Internal Server Error");
		crashWithErrorCode("Internal server error", "x_P_5000");
	}
	file_put_contents("../download-direct/" . $content_version . "-news-" . $cv2_lang . ".json", json_encode($newsfeeds));
	$return_object = [
                "xstatus" => "success",
                "newsfeeds" => $newsfeeds,
                "contentVersion" => $curl_done->contentVersion,
		"notice" => null,
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
