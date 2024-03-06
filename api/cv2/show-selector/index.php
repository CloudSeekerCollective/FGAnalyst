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
			//echo($errorCode);
                        //echo('{"xstatus":"successWithPrecautions","download":"https://cloudseeker.xyz/api/cv2/download-direct/'. json_decode(file_get_contents("../latest_content"))->version>
                        //$curl_cv2_res = file_get_contents("../download-direct/". $failsafeLoc ."/". $content_version .".json");
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
		$shows_local_2 = array();

		if(file_exists("../download-direct/" . $content_version . "-shows-". $cv2_lang .".json")){
                        $cached_show_list = json_decode(file_get_contents("../download-direct/" . $content_version . "-shows-". $cv2_lang .".json"));
			$shows["live_shows"] = array();
			foreach($cached_show_list->live_shows as $gamma){
				$shows_local = array();
				foreach($gamma as $phi){
                                	if(gettype($phi) == "string")
						$shows_local["section_name"] = $phi;
					elseif(!empty($phi->ends) and $phi->ends >= time() or empty($phi->ends)){
                                        	array_push($shows_local, $phi);
                                	}
				}
				array_push($shows["live_shows"], $shows_local);
			}
			$shows["custom_shows"] = (array)$cached_show_list->custom_shows;
                        $result_object = [
                                "xstatus" => "success",
                                "shows" => $shows,
                                "contentVersion" => $content_version,
				"notice" => null,
                                "debug" => $debug
                        ];
			if($_HAS_SITEWIDE_ANNOUNCEMENT){
                		$result_object["notice"] = $_SITEWIDE_ANNOUNCEMENT_CONTENTS;
        		}
                        echo json_encode($result_object);
                        exit;
                }

		foreach($_final->show_selector_sections as $x){
			// dont mind me either
			if(!empty($x->section->gates)){
				$shows_local = array();
				foreach($x->section->gates as $y){
					$show_id = explode(".", $y->gate);
					$z_count = 0;
					$s_count = 0;
					foreach($_final->schedule_items as $z){
						if($z->id == $show_id[1]){
							$show_info = $z_count;
							$sched_item = $_final->schedule_items[$show_info];
							$show_id_2 = $sched_item->item->item_id;
							$a_count = 0;
							foreach($_final->matchmaking_gates as $alpha){
								if($alpha->id == $show_id_2){
									$show_info_2 = $a_count;
									$mm_gate = $_final->matchmaking_gates[$show_info_2];
									$show_id_3 = $mm_gate->show_id;
									$b_count = 0;
									foreach($_final->shows as $beta){
										if($beta->id == $show_id_3){
											$show_info_3 = $b_count;
											//array_push($debug, ["z" => $z, "beta" => $beta]);
											if(empty($z->config->starts_at))
												$starts_at = null;
											else
												$starts_at = strtotime($z->config->starts_at) + 3600;
											if(empty($z->config->ends_at))
												$ends_at = strtotime("2100-01-01T00:00:00") + 3600;
											else
												$ends_at = strtotime($z->config->ends_at);
											if(!empty($starts_at) and $ends_at > time()){
												$d_count = 0;
												$actual_show = $_final->shows[$show_info_3];
												foreach($_final->localised_strings as $delta){
													$show_name_id = explode(".", $actual_show->show_name);
													if($delta->id == $show_name_id[1]){
														$show_name = $delta->text;
													}
													else{
														$d_count++;
													}
												}
												$d_count = 0;
												foreach($_final->localised_strings as $delta){
													$show_desc_id = explode(".", $actual_show->show_description);
													if($delta->id == $show_desc_id[1]){
														$show_desc = $delta->text;
													}
													else{
														$d_count++;
													}
												}
												if($ends_at == 4102441200){
													$ends_at = null;
												}
												$arr = json_decode(json_encode($_final->dlc_images), true);
                                                                				$id = $beta->show_image_cms;
                                                                				$result_dlcimg = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
                                                                				$key_4 = key($result_dlcimg);
												$dlcimg = $result_dlcimg[$key_4]["dlc_item"]["base"] . $result_dlcimg[$key_4]["dlc_item"]["path"];
												$actual_show_fr = ["id" => $beta->id, "show_name" => $show_name, "show_desc" => $show_desc,  "begins" => $starts_at, "ends" => $ends_at, "roundpool" => $beta->default_episode, "image" => $dlcimg];
												$shows_local[$beta->id] = $actual_show_fr;
											}
											else{
												$b_count++;
											}
										}
										else{
											$b_count++;
										}
									}
								}
								else{
									$a_count++;
								}
							}
						}
						else{
							$z_count++;
						}
					}
				}
				usort($shows_local, 'sortByStartsAtShows');
				$sec_id = explode(".", $x->section_title);
				$shows_local["section_name"] = getLocalisedString($sec_id[1], $_final->localised_strings);
				$shows_local_2[$x->id] = $shows_local;
				$shows["live_shows"] = $shows_local_2;
			}
		}

		foreach($_final->private_lobbies_shows_tabs as $x){
			// dont mind me either
			if($x->id == "alternative" or !$x->is_fraggle and !$x->is_favourite){
				$shows_local_customs = array();
				foreach($_final->private_lobbies_shows as $y){
					$show_id = explode(".", $y->show);
					$z_count = 0;
					$s_count = 0;
					if($y->tab != "private_lobbies_shows_tabs.alternative" or !$y->is_active)
						continue;
					foreach($_final->shows as $beta){
						if($beta->id == $show_id[1]){
							$show_info_3 = $b_count;
							// don't mind me
							if(true){
								$d_count = 0;
								$actual_show = $beta;
								foreach($_final->localised_strings as $delta){
									$show_name_id =	 explode(".", $actual_show->show_name);
									if($delta->id == $show_name_id[1]){
										$show_name = $delta->text;
									}
									else{
										$d_count++;
									}
								}
								$d_count = 0;
								foreach($_final->localised_strings as $delta){
									$show_desc_id = explode(".", $actual_show->show_description);
									if($delta->id == $show_desc_id[1]){
										$show_desc = $delta->text;
									}
								else{
									$d_count++;
								}
							}
							$arr = json_decode(json_encode($_final->dlc_images), true);
                                                        $id = $beta->show_image_cms;
                                                        $result_dlcimg = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
                                                        $key_4 = key($result_dlcimg);
							$dlcimg = $result_dlcimg[$key_4]["dlc_item"]["base"] . $result_dlcimg[$key_4]["dlc_item"]["path"];
							$actual_show_fr = ["id" => $beta->id, "show_name" => $show_name, "show_desc" => $show_desc, "roundpool" => $beta->default_episode, "image" => $dlcimg];
							$shows_local_customs[$beta->id] = $actual_show_fr;
						}
						else{
							$b_count++;
						}
					}
					else{
						$b_count++;
					}
				}
			}
			$sec_id = explode(".", $x->localised_name);
			$shows["custom_shows"] = $shows_local_customs;
		}
	}
	}
	catch(Exception $e){
		header("HTTP/2 500 Internal Server Error");
		crashWithErrorCode("Internal server error", "x_P_5000");
	}
	file_put_contents("../download-direct/" . $content_version . "-shows-" . $cv2_lang . ".json", json_encode($shows));
	$return_object = [
                "xstatus" => "success",
                "shows" => $shows,
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
