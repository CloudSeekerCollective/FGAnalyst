<?php

	// CV2: A free and open source Fall Guys content viewing and downloading beacon created by The CloudSeeker Collective (https://cloudseeker.xyz) <admin@cloudseeker.xyz>.

	header("Content-Type: application/json");
	header("X-Powered-By: CloudSeeker CV2");
	include("../connect.php");
	$content_version = "";
	$should_try = true;
	$debug = array();
	$intent = "live";

	if(empty($_GET["roundpool"])){
		crashWithErrorCode("Please provide a roundpool ID in the roundpool GET field!", "x_P_4500");
	}

	if(!empty($_GET["intent"]) and stripslashes(htmlspecialchars($_GET["intent"])) == "custom"){
		$intent = "custom";
	}

	$roundpool_obj = stripslashes(htmlspecialchars($_GET["roundpool"]));

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
		$shows = array();
		if($intent == "live" and file_exists("../download-direct/" . $content_version . "-roundpool-". $cv2_lang ."-". $roundpool_obj .".json")){
                        $shows = json_decode(file_get_contents("../download-direct/" . $content_version . "-roundpool-". $cv2_lang ."-". $roundpool_obj .".json"));
                        $result_object = [
                                "xstatus" => "success",
                                "shows" => $shows,
                                "contentVersion" => $content_version,
                                "debug" => $debug
                        ];
                        echo json_encode($result_object);
                        exit;
                }
		elseif($intent == "custom" and file_exists("../download-direct/" . $content_version . "-customs-roundpool-". $cv2_lang ."-". $roundpool_obj .".json")){
                        $shows = json_decode(file_get_contents("../download-direct/" . $content_version . "-customs-roundpool-". $cv2_lang ."-". $roundpool_obj .".json"));
                        $result_object = [
                                "xstatus" => "success",
                                "shows" => $shows,
                                "contentVersion" => $content_version,
                                "debug" => $debug
                        ];
                        echo json_encode($result_object);
                        exit;
                }
		$share_codes_list = array();
		$wushu_levels_ids = array();
		$wushu_levels_list = array();
		foreach($_final->levels_episode as $x){
			// dont mind me either
			$selected_roundpool = explode(".", $roundpool_obj);
			if($selected_roundpool[1] != $x->id){
				continue;
			}
			// what a load of shit
			if(!empty($x->roundpool)){
				$data_local = array();
				$y = array();
				if(!empty($x->fallback_round)){
					$arr = json_decode(json_encode($_final->levels_round), true);
					$id = explode(".", $x->fallback_round);
	                		$result = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id[1];});
	                		if(!empty($result)){
	                        		$y = $result[key($result)];
	                		}
					$level_id = $id;
					$z_count = 0;
					$s_count = 0;
					if($y['id'] == $level_id[1]){
						if(!empty($y["display_name"])){
							$fb_display = explode(".", $y['display_name']);
							$sec_id = getLocalisedString($fb_display[1], $_final->localised_strings);
						}
						else{
							$fb_display = "";
							$sec_id = "";
						}
						$type = "unity";
						$wushu_id = "0000-0000-0000";
						if(empty($sec_id)){
							if(!empty($y["scene_type"]["dlc_level"])){
								$wle = $y["scene_type"]["dlc_level"];
								$arr = json_decode(json_encode($_final->dlc_levels), true);
								$id = $wle;
	        	       					$wlv = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
								if(!empty($wlv)){
									$wushu_id = $wlv[key($wlv)]["sharecode"];
								}
							}
							$data_local["fallback_round"] = ["name" => "No name (presumably creative level?)", "id" => $y['id'], "archetype" => $y['level_archetype'], "type" => "wushu", "wushu_id" => $wushu_id];
						}
						else{
							$data_local["fallback_round"] = ["name" => $sec_id, "id" => $y['id'], "archetype" => $y['level_archetype'], "type" => $type];
						}
					}
				}else{
					$data_local["fallback_round"] = "";
				}
				$r_count = 0;
				$roundpool = array();
				$pool_id = explode(".", $x->roundpool);
				$arr = json_decode(json_encode($_final->levels_roundpool), true);
				$id = $pool_id;
                		$result = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id[1];});
                		if(!empty($result)){
                        		$r = $result[key($result)];
                		}
				foreach($r["stages"] as $alpha){
					$arr = json_decode(json_encode($_final->levels_round), true);
					$id = explode(".", $alpha["round"]);
                			$result = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id[1];});
                			if(!empty($result)){
                        			$beta = $result[key($result)];
						if(!empty($beta["display_name"])){
							$fb_display2 = explode(".", $beta['display_name']);
							$lvl_id = getLocalisedString($fb_display2[1], $_final->localised_strings);
						}
						else{
							$fb_display2 = "";
							$lvl_id = "";
						}
						$type = "unity";
						$min_players = 1;
						$max_players = 1;
						$wushu_id = "0000-0000-0000";
						$time_remaining = 300;
						$cant_be_on = array();
						$only_be_on = array();
						$variations = array();
						if(!empty($beta["game_rules"])){
							$gamerules = explode(".", $beta["game_rules"]);
							$arr = json_decode(json_encode($_final->game_rules), true);
							$id = $gamerules[1];
        	        				$gr = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
							if(!empty($gr)){
								if($intent == "custom"){
									$min_players = $gr[key($gr)]["min_participants_private_lobby"];
									$max_players = $gr[key($gr)]["max_participants_private_lobby"];
								}
								else{
									$min_players = $gr[key($gr)]["min_participants"];
									$max_players = $gr[key($gr)]["max_participants"];
								}
								if(!empty($gr[key($gr)]["has_timer"]) and $gr[key($gr)]["has_timer"])
									$time_remaining = $gr[key($gr)]["duration"];
								else
									$time_remaining = 300;
							}
						}
						if(!empty($beta["level_variation"])){
							$levelvars = explode(".", $beta["level_variation"]);
							$arr = json_decode(json_encode($_final->levels_variation), true);
							$id = $levelvars[1];
                					$lv = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
							if(!empty($lv)){
								$variations = $lv[key($lv)]["set_switchers"];
							}
						}
						if(isset($alpha["cannot_be_on_these_stages"]) and isset($alpha["can_only_be_on_these_stages"])){
							$cant_be_on = $alpha["cannot_be_on_these_stages"];
							$only_be_on = $alpha["can_only_be_on_these_stages"];
						}
						if(empty($lvl_id)){
							$wle_name = "No name (presumably creative level?)";
							$level_author = "";
							if(!empty($beta["scene_type"]["dlc_level"])){
								$wle = $beta["scene_type"]["dlc_level"];
								$arr = json_decode(json_encode($_final->dlc_levels), true);
								$id = $wle;
        	        					$wlv = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
								if(!empty($wlv)){
									$wushu_id = $wlv[key($wlv)]["sharecode"];
								}
							}
							array_push($share_codes_list, $wushu_id);
							array_push($wushu_levels_list, (object)[
								"name" => $wle_name, 
								"id" => $beta['id'], 
								"archetype" => $beta['level_archetype'],
								"type" => "wushu", 
								"cannot_be_on_stages" => $cant_be_on, 
								"can_only_be_on_stages" => $only_be_on,
								"min_players" => $min_players,
								"max_players" => $max_players,
								"time_remaining" => $time_remaining,
								"variations" => $variations,
								"wushu_id" => $wushu_id,
								"wushu_author" => $level_author
							]);
							array_push($wushu_levels_ids, $beta['id']);
						}
						else{
							$roundpool[$beta["id"]] = [
								"name" => $lvl_id, 
								"id" => $beta['id'], 
								"archetype" => $beta['level_archetype'], 
								"type" => $type, 
								"cannot_be_on_stages" => $cant_be_on, 
								"can_only_be_on_stages" => $only_be_on,
								"min_players" => $min_players,
								"max_players" => $max_players,
								"time_remaining" => $time_remaining,
								"variations" => $variations
							];
						}
                			}
					else{
						exit; // Error!
					}
				}
				$curl_inst_2 = curl_init();
				curl_setopt_array($curl_inst_2, array(
						CURLOPT_URL => 'https://level-gateway.fallguys.oncatapult.com/level/batch',
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_ENCODING => '',
						CURLOPT_MAXREDIRS => 10,
						CURLOPT_TIMEOUT => 0,
						CURLOPT_FOLLOWLOCATION => true,
						CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
						CURLOPT_CUSTOMREQUEST => 'POST',
						CURLOPT_POSTFIELDS =>'{"share_codes":'. json_encode($share_codes_list) .'}',
						CURLOPT_HTTPHEADER => array(
                        			'User-Agent: UnityPlayer/2021.3.16f1 (UnityWebRequest/1.0, libcurl/7.84.0-DEV)',
                        			'X-Unity-Version: 2021.3.16f1',
                        			'Content-Type: application/json',
                        			'Authorization: Bearer ' . $curl_done->token
                			),
        			));

       				$curl_res_2 = curl_exec($curl_inst_2);
       				curl_close($curl_inst_2);

        			if($curl_res_2 == false){
                			triggerErrorFailsafe("Could not connect to the Fall Guys server at this moment", "x_C_4200");
        			}
				$thing_counter = 0;
        			$level_data = json_decode($curl_res_2);
				foreach($level_data->snapshots as $xyz){
					if(empty($xyz))
                				continue;
					$arr = json_decode(json_encode($level_data->snapshots), true);
					$id = $share_codes_list[$thing_counter];
                			$result = array_filter($arr, function($obj)use($id){return !empty($obj['share_code']) && $obj['share_code'] === $id;});
					$rk = key($result);
					//var_dump($result);
					//var_dump($arr);
					//exit;
					//array_push($debug, ["api" => $xyz->share_code, "arr" => ]);
					$wle_name = $result[$rk]["version_metadata"]["title"];
					$level_author = "";
					if(empty($result[$rk]["author"]["name_per_platform"]["eos"])){
						//$level_data2 = json_decode(json_encode($xyz), true);
						$level_author = $result[$rk]["author"]["name_per_platform"];
					}
					else
						$level_author = $result[$rk]["author"]["name_per_platform"]["eos"];
					$wushu_levels_list[$thing_counter]->name = $wle_name;
					$wushu_levels_list[$thing_counter]->wushu_author = $level_author;
					$roundpool[$wushu_levels_ids[$thing_counter]] = $wushu_levels_list[$thing_counter];
					$thing_counter++;
					if($_LOG_WUSHU_LEVELS){
                				if(empty($xyz->author->name_per_platform->eos)){
                        				// you are given one fucking job to implement epic online services properly and not even your APIs fucking work
                        				$usernames = array_values((array)$xyz->author->name_per_platform);
                        				// IT GETS WORSE!!!
							if(!empty($usernames[0]))
								$epic_username = key((array)$xyz->author->name_per_platform) . "_" . $usernames[0];
                					else
								$epic_username = "";
						}
                				else{
                        				$epic_username = $xyz->author->name_per_platform->eos;
                				}
                				$lfdl = mysqli_query($_WUSHU_ARCHIVE_DATABASE_LINK, "INSERT INTO levels (common_name, share_code, tags, playcount, likes, dislikes, min_players, max_players, author_name) VALUES ('". stripslashes(htmlspecialchars($xyz->version_metadata->title)) ."', '". $xyz->share_code ."', '". json_encode($xyz->version_metadata->creator_tags) ."', ". $xyz->stats->play_count .", ". $xyz->stats->likes .", ". $xyz->stats->dislikes .", 1, ". $xyz->version_metadata->max_player_count .", '". $epic_username ."') ON DUPLICATE KEY UPDATE common_name = VALUES(common_name),tags = VALUES(tags),playcount = VALUES(playcount),likes = VALUES(likes),dislikes = VALUES(dislikes),min_players = VALUES(min_players),max_players = VALUES(max_players),author_name = VALUES(author_name);");
        				}
				}
				array_push($debug, $share_codes_list);
				$data_local["roundpool"] = $roundpool;
				$shows = $data_local;
			}
		}
	}
	catch(Exception $e){
		//header("HTTP/2 500 Internal Server Error");
		crashWithErrorCode("Internal server error (". stripslashes(htmlspecialchars($e)), "x_P_5000");
	}
	$result_object = [
		"xstatus" => "success",
		"shows" => $shows,
		"contentVersion" => $content_version,
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
	if(!empty($shows)){
		echo json_encode($result_object);
	}
	else{
		crashWithErrorCode("Roundpool with this ID is not available!", "x_F_4040");
	}
	if($intent == "live")
		file_put_contents("../download-direct/" . $content_version . "-roundpool-". $cv2_lang ."-". $roundpool_obj .".json", json_encode($shows));
	else
		file_put_contents("../download-direct/" . $content_version . "-customs-roundpool-". $cv2_lang ."-". $roundpool_obj .".json", json_encode($shows))

?>
