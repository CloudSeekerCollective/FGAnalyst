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
		$should_try = false;
		if(file_exists("../latest_content")){
			$content_version = json_decode(file_get_contents("../latest_content"))->version;
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
		$content_version = json_decode(file_get_contents("../latest_content"))->version;
		$should_try = false;
	}
	else{
		$cv2_download_link = $curl_done->contentUrl;
		$content_version = $curl_done->contentVersion;
	}
	$content_file = $curl_done->contentVersion . ".json";
        if(isset($_GET["mobile"])){
                $content_file = $curl_done->contentVersion . "_M.json";
        }

	try{
		if(!file_exists("../download-direct/". $cv2_lang ."/". $content_file) and $should_try == true or empty(file_get_contents("../download-direct/". $cv2_lang ."/". $content_file)) and $should_try == true){
			header("Cache-Control: no-store, must-revalidate");
			$curl_cv2 = curl_init();
			curl_setopt($curl_cv2, CURLOPT_URL, $cv2_download_link);
			curl_setopt($curl_cv2, CURLOPT_USERAGENT, "User-Agent: UnityPlayer/". $_X_UNITY_VERSION ." (UnityWebRequest/1.0, libcurl/7.84.0-DEV)");
			curl_setopt($curl_cv2, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($curl_cv2, CURLOPT_RETURNTRANSFER, true);

			$curl_cv2_res = curl_exec($curl_cv2);
			$cv2_current = fopen("../download-direct/". $cv2_lang ."/". $content_file, "w+");
			fwrite($cv2_current, $curl_cv2_res);
			if($curl_cv2_res == false){
				crashWithErrorCode("Content file could not be downloaded", "x_F_4010");
			}
		}
		$curl_cv2_res = file_get_contents("../download-direct/". $cv2_lang ."/". $content_file);
		$_final = json_decode($curl_cv2_res);
		$crown_ranks = array();
		$dlc_images = array();
		$boosts = array();
		if(false and file_exists("../download-direct/" . $content_version . "-shops-". $cv2_lang .".json")){
			foreach(json_decode(file_get_contents("../download-direct/" . $content_version . "-shops-". $cv2_lang .".json")) as $gamma){
				array_push($debug, ["converted" => strtotime($gamma->ends_at), "actual" => $gamma->ends_at]);
				if($gamma->ends_at >= time()){
					array_push($shops, $gamma);
				}
			}
			usort($shops, 'sortByStartsAtObject');
			$result_object = [
				"xstatus" => "success",
				"shops" => $shops,
				"notice" => null,
				"contentVersion" => $content_version,
				"debug" => $debug
			];
			if($_HAS_SITEWIDE_ANNOUNCEMENT){ 
                		$result_object["notice"] = $_SITEWIDE_ANNOUNCEMENT_CONTENTS;
        		}
			echo json_encode($result_object);
			exit;
		}
		foreach($_final->dlc_images as $x){
			$dlc_images[$x->id] = ["id" => $x->id, "path" => $x->dlc_item->base . $x->dlc_item->path];
		}
		foreach($_final->settings_economy_boost_schedules as $x){
			if(strtotime($x->ends_at) >= time()){
				$crownboost = $x->crowns_boost ?? 0;
				$crownshardboost = $x->crowns_shards_boost ?? 0;
				$fameboost = $x->fame_boost ?? 0; // most likely a fameboost tho
				$boosts[$x->id] = ["id" => $x->id, "name" => $x->internal_name, "starts_at" => strtotime($x->starts_at), "ends_at" => strtotime($x->ends_at), "crown_boost" => $crownboost, "crown_shard_boost" => $crownshardboost, "fame_boost" => $fameboost];
			}
		}
		foreach($_final->player_levels[0]->player_levels as $x){
			// dont mind me either
			$actual_cosmetics = array();
			if(!empty($x->reward)){
				switch($x->reward->contents->items[0]->group_id){
					case "costumes_upper":
						$arr = json_decode(json_encode($_final->costumes_upper), true);
					break;
					case "costumes_lower":
						$arr = json_decode(json_encode($_final->costumes_lower), true);
					break;
					case "costumes_faceplates":
						$arr = json_decode(json_encode($_final->costumes_faceplates), true);
					break;
					case "costumes_patterns":
						$arr = json_decode(json_encode($_final->costumes_patterns), true);
					break;
					case "cosmetics_punchlines":
						$arr = json_decode(json_encode($_final->cosmetics_punchlines), true);
					break;
					case "cosmetics_emotes":
						$arr = json_decode(json_encode($_final->cosmetics_emotes), true);
					break;
					case "cosmetics_nameplates":
						$arr = json_decode(json_encode($_final->cosmetics_nameplates), true);
					break;
					case "cosmetics_nicknames":
						$arr = json_decode(json_encode($_final->cosmetics_nicknames), true);
					break;
					case "costumes_colour_schemes":
						$arr = json_decode(json_encode($_final->costumes_colour_schemes), true);
					break;
					default:
						//Idk
						continue 2;
					break;
				}
				$id = $x->reward->contents->items[0]->item_id;
                		$result_4 = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
				$key_5 = key($result_4);
				if(!empty($result_4)){
					$item_name = explode(".", $result_4[$key_5]["name"]);
					array_push($actual_cosmetics, (object)["name" => getLocalisedString($item_name[1], $_final->localised_strings), "crowns_required" => $x->unlocks_at, "rarity" => $result_4[$key_5]["rarity"], "id" => $id, "type" => $x->reward->contents->items[0]->group_id]);
				}
			}
			array_push($crown_ranks, $actual_cosmetics);
		}
	}
	catch(Exception $e){
		header("HTTP/2 500 Internal Server Error");
		crashWithErrorCode("Internal server error", "x_P_5000");
	}
	//file_put_contents("../download-direct/" . $content_version . "-shops-". $cv2_lang .".json", json_encode($shops));
	//usort($shops, 'sortByStartsAt');
	$result_object = [
		"xstatus" => "success",
		"fgps_name" => $_COMMON_FGPS_NAME,
		"crown_ranks" => $crown_ranks,
		"dlc_images" => $dlc_images,
		"economy_boosts" => $boosts,
		"contentVersion" => $content_version,
		"environment" => [
                        "environment_id" => $_CATAPULT_ENVIRONMENT,
                        "game_version" => $_GAME_VERSION,
                        "client_signature" => $_CLIENT_SIG
                ],
		"debug" => $debug
	];
	if($_HAS_SITEWIDE_ANNOUNCEMENT){ 
                $result_object["notice"] = $_SITEWIDE_ANNOUNCEMENT_CONTENTS;
        }
	echo json_encode($result_object);
?>
