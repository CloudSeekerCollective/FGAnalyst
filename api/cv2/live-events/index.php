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
		$shops = array();
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
		foreach($_final->live_events as $x){
			// dont mind me either
			$roundpool;
			if($x->target_ids[0] == "disabled_year_1900"){
				continue;
			}
			// Yes I recycle code. How could you tell? :chadus:
			$alpha = $x;
			if(empty($alpha->target_ids)){
				continue;
			}
			$arr1 = json_decode(json_encode($_final->targets), true);
			$id = $alpha->target_ids[0];
                	$result1 = array_filter($arr1, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
			$key1 = key($result1);
			if(!empty($result1) and !empty($result1[$key1]["condition_ids"][0])){
				$id = $result1[$key1]["condition_ids"][0];
			}
			else{
				continue;
			}
			$arr = json_decode(json_encode($_final->target_conditions), true);
			//$id = $alpha->target_ids[0];
                	$result = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
			$key = key($result);
                	if(!empty($result) and
				!empty($result[$key]["parameters"]) and
				$result[$key]["parameters"]["data"]["operator"] == "between" and
				strtotime($result[$key]["parameters"]["data"]["end"]) >= time()
			){
				$starts_at = 0;
				$ends_at = 0;
				if(!empty($result[$key]["parameters"]["data"]["start"]))
					$starts_at = strtotime($result[$key]["parameters"]["data"]["start"]);
					if(!empty($result[$key]["parameters"]["data"]["end"]))
						$ends_at = strtotime($result[$key]["parameters"]["data"]["end"]);
					$title = explode(".", $alpha->title);
					$description = explode(".", $alpha->description);
					$title = getLocalisedString($title[1], $_final->localised_strings);
					$description = getLocalisedString($description[1], $_final->localised_strings);
					$tooltip = $alpha->tool_tip_image ?? null;
					$arr = json_decode(json_encode($_final->dlc_images), true);
					$id = $tooltip;
					$milestones = (array)[];
					$challenges = (array)[];
                			$result_3 = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
					$key_4 = key($result_3);
					if(!empty($result_3)){
						$tooltip = $result_3[$key_4]["dlc_item"]["base"] . $result_3[$key_4]["dlc_item"]["path"];
					}
					// bg image
					$bg = $alpha->background_image ?? null;
					$arr = json_decode(json_encode($_final->dlc_images), true);
					$id = $bg;
                			$result_3 = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
					$key_4 = key($result_3);
					if(!empty($result_3)){
						$bg = $result_3[$key_4]["dlc_item"]["base"] . $result_3[$key_4]["dlc_item"]["path"];
					}
					// promotional_image
					$prom = $alpha->promotional_image ?? null;
					$arr = json_decode(json_encode($_final->dlc_images), true);
					$id = $prom;
                			$result_3 = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
					$key_4 = key($result_3);
					if(!empty($result_3)){
						$prom = $result_3[$key_4]["dlc_item"]["base"] . $result_3[$key_4]["dlc_item"]["path"];
					}
					// currency
					$currency = $alpha->reward_icon_image ?? null;
					$arr = json_decode(json_encode($_final->dlc_images), true);
					$id = $currency;
                			$result_3 = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
					$key_4 = key($result_3);
					if(!empty($result_3)){
						$currency = $result_3[$key_4]["dlc_item"]["base"] . $result_3[$key_4]["dlc_item"]["path"];
					}
					foreach($alpha->challenges as $beta2){
						$xp_accredited = $beta2->xp_awarded;
						$arr = json_decode(json_encode($_final->goals), true);
						$id = $beta2->goal_id;
                				$result = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
						$key_2 = key($result);
						$challenge_description = explode(".", $result[$key_2]["description"]);
						$challenge_description = getLocalisedString($challenge_description[1], $_final->localised_strings);
						$desc_params = [];
						$amount_times = $result[$key_2]["target"]["value"];
						foreach($result[$key_2]["description_parameters"] as $dp){
							if($dp["type"] == "localised"){
								$cd1 = explode(".", $dp["localised_value"]);
								$cd1 = getLocalisedString($cd1[1], $_final->localised_strings);
								array_push($desc_params, (object)["type" => "localised", "localised_value" => $cd1]);
							}
							else{
								array_push($desc_params, $dp);
							}
						}
						$challenges[$beta2->goal_id] = [
							"id" => $beta2->goal_id,
							"main_description" => $challenge_description,
							"description_params" => $desc_params,
							"xp_accredited" => $xp_accredited,
							"times" => $amount_times
						];
					}
					foreach($alpha->milestones as $beta){
						$required_xp = $beta->xp_required;
						//////////////////////////////////////////////
						$cosmetics = (array)[];
						$arr = json_decode(json_encode($_final->rewards), true);
						$id = $beta->reward_id;
                				$result = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
						$key_2 = key($result);
						$delta = $result[$key_2]["contents"]["items"][0];
						switch($delta["group_id"]){
							case "currencies":
								$arr = json_decode(json_encode($_final->currencies), true);
							break;
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
							case "cosmetics_nicknames":
								$arr = json_decode(json_encode($_final->cosmetics_nicknames), true);
							break;
							case "cosmetics_nameplates":
								$arr = json_decode(json_encode($_final->cosmetics_nameplates), true);
							break;
							case "costumes_colour_schemes":
								$arr = json_decode(json_encode($_final->costumes_colour_schemes), true);
							break;
							default:
								//Idk
								continue 2;
							break;
						}
						$bundle_tile_image = "https://cloudseeker.xyz/fga/Question.png";
						if(file_exists("../images/" . $delta["item_id"] . ".png")){
							$bundle_tile_image = "https://cloudseeker.xyz/api/cv2/images/" . $delta["item_id"] . ".png";
						}
						// i'm tired man
						elseif($delta["group_id"] == "costumes_patterns" || $delta["group_id"] == "costumes_upper" || $delta["group_id"] == "costumes_lower" || $delta["group_id"] == "cosmetics_punchlines" || $delta["group_id"] == "costumes_emotes" || $delta["group_id"] == "cosmetics_nameplates"){
							$get_bundle_image = curl_init();
							$thing3 = "default";
							switch($delta["group_id"]){
								case "costumes_patterns":
									$thing3 = "Pattern";
								break;
								case "costumes_upper":
									$thing3 = "Costume";
								break;
								case "costumes_lower":
									$thing3 = "Costume";
								break;
								case "cosmetics_punchlines":
									$thing3 = "Celebration";
								break;
								case "costumes_emotes":
									$thing3 = "Emote";
								break;
								case "cosmetics_nameplates":
									$thing3 = "Banner";
								break;
							}
							curl_setopt($get_bundle_image, CURLOPT_URL, "https://fallguysultimateknockout.fandom.com/api.php?action=cargoquery&format=json&limit=100&tables=". $thing3 ."&fields=id%2Cicon&where=id%3D'". $delta["item_id"] ."'");
							curl_setopt($get_bundle_image, CURLOPT_RETURNTRANSFER, true);
							curl_setopt($get_bundle_image, CURLOPT_USERAGENT, "CloudSeekerEnterprise/1.0");
							curl_setopt($get_bundle_image, CURLOPT_FOLLOWLOCATION, true);
							curl_setopt($get_bundle_image, CURLOPT_MAXREDIRS, 10);
							$gt = curl_exec($get_bundle_image);
							curl_close($get_bundle_image);
							if($gt){
								$xgt = json_decode($gt);
								//var_dump($xgt);
								if(!empty($xgt->cargoquery[0])){
									$dl = curl_init();
									curl_setopt($dl, CURLOPT_URL, "https://fallguysultimateknockout.fandom.com/wiki/Special:FilePath/" . rawurlencode($xgt->cargoquery[0]->title->icon));
									curl_setopt($dl, CURLOPT_RETURNTRANSFER, true);
									curl_setopt($dl, CURLOPT_USERAGENT, "CloudSeekerEnterprise/1.0");
									curl_setopt($dl, CURLOPT_FOLLOWLOCATION, true);
									curl_setopt($dl, CURLOPT_MAXREDIRS, 10);
									$xdl = curl_exec($dl);
									curl_close($get_bundle_image);
									if($xdl){
										file_put_contents("../images/" . $delta["item_id"] . ".png", $xdl);
										$bundle_tile_image = "https://cloudseeker.xyz/api/cv2/images/" . $actual_cosmetics[0]->id . ".png";
									}
								}
							}
						}
						$id = $delta["item_id"];
						$item_q = $delta["quantity"] ?? 1;
                				$result_4 = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
						$key_5 = key($result_4);
						if(!empty($result_4)){
							$item_name = explode(".", $result_4[$key_5]["name"]);
							array_push($cosmetics, (object)["name" => getLocalisedString($item_name[1], $_final->localised_strings), "rarity" => $result_4[$key_5]["rarity"], "id" => $id, "type" => $delta["group_id"], "quantity" => $item_q, "image" => $bundle_tile_image]);
						}
						$milestones[$beta->reward_id] = [
							"xp_required" => $required_xp,
							"items" => $cosmetics
						];
					}
					$data_local[$alpha->id] = [
						"name" => $title,
						"description" => $description,
						"id" => $alpha->id,
						"images" => [
							"tooltip" => $tooltip,
							"background" => $bg,
							"promotional" => $prom,
							"currency" => $currency
						],
						"milestones" => $milestones,
						"challenges" => $challenges,
						"starts_at" => $starts_at,
						"ends_at" => $ends_at,
						"target_ids" => $alpha->target_ids
					];
				}
				$events = $data_local ?? [];
			}
		}
	catch(Exception $e){
		header("HTTP/2 500 Internal Server Error");
		crashWithErrorCode("Internal server error", "x_P_5000");
	}
	file_put_contents("../download-direct/archive/" . $content_version . "-events-". $cv2_lang .".json", json_encode($shops));
	usort($events, 'sortByStartsAt');
	$result_object = [
		"xstatus" => "success",
		"live_events" => $events,
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
