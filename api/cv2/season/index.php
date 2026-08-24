<?php

	// CV2: A free and open source Fall Guys content viewing and downloading beacon created by The CloudSeeker Collective (https://cloudseeker.xyz) <admin@cloudseeker.xyz>.
	// Frame Glass?

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
	function sortByFame($a, $b){
             if($a["xp_required"] == $b["xp_required"]){
                  return 0;
             }
             return ($a["xp_required"] < $b["xp_required"]) ? -1 : 1;
        }

	function disambiguateItemType($gid, $_final){
		$arr;
		switch($gid){
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
			case "cosmetics_emoticons":
				$arr = json_decode(json_encode($_final->cosmetics_emoticons), true);
			break;
			case "cosmetics_phrases":
				$arr = json_decode(json_encode($_final->cosmetics_phrases), true);
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
				return null;
			break;
		}
		return $arr;
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
		$boosts = array();
		foreach($_final->settings_economy_boost_schedules as $x){
			if(strtotime($x->ends_at) >= time()){
				$crownboost = $x->crowns_boost ?? 0;
				$crownshardboost = $x->crowns_shards_boost ?? 0;
				$fameboost = $x->fame_boost ?? 0; // most likely a fameboost tho
				$boosts[$x->id] = ["id" => $x->id, "name" => $x->internal_name, "starts_at" => strtotime($x->starts_at), "ends_at" => strtotime($x->ends_at), "crown_boost" => $crownboost, "crown_shard_boost" => $crownshardboost, "fame_boost" => $fameboost];
			}
		}
		if(file_exists("../download-direct/archive/" . $content_version . "-season-". $cv2_lang .".json")){
			foreach(json_decode(file_get_contents("../download-direct/archive/" . $content_version . "-season-". $cv2_lang .".json")) as $gamma){
				array_push($debug, ["converted" => strtotime($gamma->ends_at), "actual" => $gamma->ends_at]);
				if($gamma->ends_at >= time()){
					array_push($shops, $gamma);
				}
			}
			usort($shops, 'sortByStartsAtObject');
			$result_object = [
				"xstatus" => "success",
				"seasons" => $shops,
				"economy_boosts" => $boosts,
				"notice" => null,
				"cached" => true,
				"contentVersion" => $content_version,
				"debug" => $debug
			];
			if($_HAS_SITEWIDE_ANNOUNCEMENT){ 
                		$result_object["notice"] = $_SITEWIDE_ANNOUNCEMENT_CONTENTS;
        		}
			echo json_encode($result_object);
			exit;
		}
		foreach($_final->seasons as $x){
			// Yes I recycle code. How could you tell? :chadus:
			$alpha = $x;
			if(empty($alpha->target_ids)){
				continue;
			}
			if($x->target_ids[0] == "disabled_year_1900"){
				continue;
			}
                	if(!empty($alpha->start_time) and
				!empty($alpha->end_time) and
				strtotime($alpha->end_time) >= time()
			){
				$starts_at = 0;
				$ends_at = 0;
				if(!empty($alpha->start_time))
					$starts_at = strtotime($alpha->start_time);
					if(!empty($alpha->end_time))
						$ends_at = strtotime($alpha->end_time);
					$title = explode(".", $alpha->name);
					//$description = explode(".", $alpha->description);
					$title = getLocalisedString($title[1], $_final->localised_strings);
					//$description = getLocalisedString($description[1], $_final->localised_strings);
					$actual_pass = str_replace("season_passes.", "", $alpha->season_pass);
					$arr = json_decode(json_encode($_final->season_passes), true);
					$id = $actual_pass;
					$milestones = (array)[];
                			$irs = (array)[];
					$result_3 = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
					$key_4 = key($result_3);
					if(!empty($result_3)){
						$actual_pass = $result_3[$key_4];
					}
					// bg image
					/*$bg = $alpha->background_image ?? null;
					$arr = json_decode(json_encode($_final->dlc_images), true);
					$id = $bg;
                			$result_3 = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
					$key_4 = key($result_3);
					if(!empty($result_3)){
						$bg = $result_3[$key_4]["dlc_item"]["base"] . $result_3[$key_4]["dlc_item"]["path"];
					}*/
					$instant_rewards = str_replace("sym_store_bundles.", "", $actual_pass["data_symphony_store_bundle_data"]);
					$arr = json_decode(json_encode($_final->sym_store_bundles), true);
					$id = $instant_rewards;
                			$result_4 = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
					$key_5 = key($result_4);
					if(!empty($result_4)){
						$instant_rewards = $result_4[$key_5];
						foreach($instant_rewards["bundle_type"]["bundle_options"] as $d2){
							$irs2 = (array)[];
							foreach($d2["items"] as $d1){
								if($d1["type"] != "season_passes" and $d1["type"] != "fame_tier_skip"){
									$arr = disambiguateItemType($d1["type"], $_final);
									$id = $d1["item_id"];
									$item_q = $d1["quantity"] ?? 1;
			                				$result_5 = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
									$key_6 = key($result_5);
									if(!empty($result_5)){
										$item_name = explode(".", $result_5[$key_6]["name"]);
										array_push($irs2, (object)["name" => getLocalisedString($item_name[1], $_final->localised_strings), "rarity" => str_replace("rarities.", "", $result_5[$key_6]["rarity"]), "id" => $id, "type" => $d1["type"], "quantity" => $item_q, "image" => null]);
									}
								}
								elseif($d1["type"] == "fame_tier_skip"){
									$id = $d1["item_id"];
									$item_q = $d1["quantity"] ?? 1;
									array_push($irs2, (object)["name" => "Fame Tier Skip", "rarity" => "tier_skip", "id" => $id, "type" => $d1["type"], "quantity" => $item_q]);
								}
							}
							array_push($irs, (object)["cost" => $d2["cost"], "contents" => $irs2]);
						}
					}
					foreach($alpha->fame_tiers as $beta){
						$required_xp = $beta->unlocks_at;
						//////////////////////////////////////////////
						$cosmetics = (array)[];
						$arr = json_decode(json_encode($_final->rewards), true);
						$gid = $beta->rewards[0]->reward->contents->items[0]->group_id;
						$id = $beta->rewards[0]->reward->id;
						$premium = false;
						if(!empty($beta->rewards[0]->season_pass_id))
							$premium = true;
                				$result = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
						//if(empty($result)) continue;
						$key_2 = key($result);
						$delta = $result[$key_2]["contents"]["items"][0];
						$arr = disambiguateItemType($gid, $_final);
						$bundle_tile_image = "https://cloudseeker.xyz/fga/Question.png";
						if(true or $delta["group_id"] == "costumes_patterns" || $delta["group_id"] == "costumes_upper" || $delta["group_id"] == "costumes_lower" || $delta["group_id"] == "cosmetics_punchlines" || $delta["group_id"] == "cosmetics_emotes" || $delta["group_id"] == "cosmetics_nameplates" || $delta["group_id"] == "cosmetics_phrases" || $delta["group_id"] == "cosmetics_emoticons"){
							$bundle_tile_image = "https://fga-images.terminal.cloudseeker.xyz/" . $delta["group_id"] . "/" . $delta["item_id"] . ".png";
						}
						elseif($delta["group_id"] == "costumes_colour_schemes" or $delta["group_id"] == "costumes_faceplates"){
							$get_bundle_image = curl_init();
							$thing3;
							if($delta["group_id"] == "costumes_faceplates"){
								$thing3 = "Faceplate";
								$bundle_image_url = "https://fallguysultimateknockout.fandom.com/api.php?action=cargoquery&format=json&limit=100&tables=". $thing3 ."&fields=id%2CfaceColor%2CeyesColor&where=id%3D'". $delta["item_id"] ."'&formatversion=2";
							}
							else{
								$thing3 = "Colour";
								$bundle_image_url = "https://fallguysultimateknockout.fandom.com/api.php?action=cargoquery&format=json&limit=100&tables=". $thing3 ."&fields=id%2CprimaryColor%2CsecondaryColor&where=id%3D'". $delta["item_id"] ."'&formatversion=2";
							}
							curl_setopt($get_bundle_image, CURLOPT_URL, $bundle_image_url);
							curl_setopt($get_bundle_image, CURLOPT_RETURNTRANSFER, true);
							curl_setopt($get_bundle_image, CURLOPT_USERAGENT, "CloudSeekerEnterprise/1.0");
							curl_setopt($get_bundle_image, CURLOPT_FOLLOWLOCATION, true);
							curl_setopt($get_bundle_image, CURLOPT_MAXREDIRS, 10);
							$gt = curl_exec($get_bundle_image);
							curl_close($get_bundle_image);
							if($gt){
								$xgt = json_decode($gt);
								//var_dump($bundle_image_url);
								if(!empty($xgt->cargoquery[0])){
									$dl = curl_init();
									// Now I know there's an easier way to do this but i'm too lazy Lmfao
									if($delta["group_id"] == "costumes_faceplates"){
										$colour1 = str_replace("#", "", $xgt->cargoquery[0]->title->faceColor);
										$colour2 = str_replace("#", "", $xgt->cargoquery[0]->title->eyesColor);
										$imurl = "https://cloudseeker.xyz/api/cv2/generate_faceplate.php?c1=" . $colour1 . "&c2=" . $colour2;
									}
									else{
										$colour1 = str_replace("#", "", $xgt->cargoquery[0]->title->primaryColor);
										$colour2 = str_replace("#", "", $xgt->cargoquery[0]->title->secondaryColor);
										$imurl = "https://cloudseeker.xyz/api/cv2/generate_colour.php?c1=" . $colour1 . "&c2=" . $colour2;
									}
									curl_setopt($dl, CURLOPT_URL, $imurl);
									curl_setopt($dl, CURLOPT_RETURNTRANSFER, true);
									curl_setopt($dl, CURLOPT_USERAGENT, "CloudSeekerEnterprise/1.0");
									curl_setopt($dl, CURLOPT_FOLLOWLOCATION, true);
									curl_setopt($dl, CURLOPT_MAXREDIRS, 10);
									$xdl = curl_exec($dl);
									curl_close($get_bundle_image);
									if($xdl){
										file_put_contents("../images/" . $delta["item_id"] . ".png", $xdl);
										$bundle_tile_image = "https://cloudseeker.xyz/api/cv2/images/" . $delta["item_id"] . ".png";
									}
								}
							}
						}
						//array_push($debug, $delta);
						$id = $delta["item_id"];
						$item_q = $delta["quantity"] ?? 1;
                				$result_4 = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
						$key_5 = key($result_4);
						if(!empty($result_4)){
							$item_name = explode(".", $result_4[$key_5]["name"]);
							array_push($cosmetics, (object)["name" => getLocalisedString($item_name[1], $_final->localised_strings), "rarity" => str_replace("rarities.", "", $result_4[$key_5]["rarity"]), "id" => $id, "type" => $delta["group_id"], "quantity" => $item_q, "image" => $bundle_tile_image]);
						}
						$milestones[count($milestones) + 1] = [
							"xp_required" => $required_xp,
							"is_premium" => $premium,
							"items" => $cosmetics
						];
					}
					usort($milestones, 'sortByFame');
					$data_local[$alpha->id] = [
						"name" => $title,
						"id" => $alpha->id,
						"prototype" => [
							"season_passes" => $actual_pass
						],
						"instant_unlocks" => $irs,
						"tiers" => $milestones,
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
	file_put_contents("../download-direct/archive/" . $content_version . "-season-". $cv2_lang .".json", json_encode($events));
	usort($events, 'sortByStartsAt');
	$result_object = [
		"xstatus" => "success",
		"seasons" => $events,
		"economy_boosts" => $boosts,
		"cached" => false,
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
