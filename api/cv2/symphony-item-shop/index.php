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
		if(/*false and */file_exists("../download-direct/" . $content_version . "-shops-". $cv2_lang .".json")){
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
		foreach($_final->sym_stores as $x){
			// dont mind me either
			$roundpool;
			if($x->target_ids[0] == "disabled_year_1900"){
				continue;
			}
			if(!empty($x->storefronts)){
				$data_local = [];
				foreach($_final->sym_store_sections as $alpha){
					$sf = $alpha->storefront;
					if($sf != $x->storefronts[0]){
						continue;
					}
					$arr = json_decode(json_encode($_final->target_conditions), true);
					if(empty($alpha->target_ids)){
						continue;
					}
					$id = $alpha->target_ids[0];
                			$result = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
					$key = key($result);
                			if(!empty($result) and
						!empty($result[$key]["parameters"]) and
						$result[$key]["parameters"]["data"]["operator"] == "between" and
						strtotime($result[$key]["parameters"]["data"]["end"]) >= time()
					){
						$bundles = array();
						$starts_at = 0;
						$ends_at = 0;
						if(!empty($result[$key]["parameters"]["data"]["start"]))
							$starts_at = strtotime($result[$key]["parameters"]["data"]["start"]) + 3600;
						if(!empty($result[$key]["parameters"]["data"]["end"]))
							$ends_at = strtotime($result[$key]["parameters"]["data"]["end"]) + 3600;
						foreach($alpha->bundle_slots as $beta){
							$cosmetics = (array)[];
							$layout = (object)["width" => $beta->layout->tile_width, "height" => $beta->layout->tile_height];
							$currency = (object)["currency" => "crowns", "quantity" => 0];
							$arr = json_decode(json_encode($_final->sym_store_bundles), true);
							$id = $beta->bundle_slot;
                					$result = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
							$key_2 = key($result);
							$discount = 0;
							if(empty($result) or substr($id, 0, 4) == "slot"){
								$arr = json_decode(json_encode($_final->sym_store_bundle_slots), true);
								$id = $beta->bundle_slot;
                						$result = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
								$key_2 = key($result);
								if(empty($result)){
									continue;
								}
								else{
									$arr = json_decode(json_encode($_final->sym_store_bundles), true);
									$id = $result[$key_2]["bundle"][0]["bundle_id"];
                							$result = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
									$key_2 = key($result);
								}
							}
							$actual_cosmetics = array();
							if(!empty($result)){
								if(!empty($result[$key_2]["bundle_type"]["cosmetics"])){
									$cosmetics = $result[$key_2]["bundle_type"]["cosmetics"];
								}
								if(!empty($result[$key_2]["bundle_type"]["extra"])){
									$cosmetics += $result[$key_2]["bundle_type"]["extra"];
								}
								if(!empty($result[$key_2]["bundle_type"]["purchase_options"]["payment_items"]["item_id"]))
									$currency->currency = $result[$key_2]["bundle_type"]["purchase_options"]["payment_items"]["item_id"];
								if(!empty($result[$key_2]["bundle_type"]["purchase_options"]["payment_items"]["quantity"]))
									$currency->quantity = $result[$key_2]["bundle_type"]["purchase_options"]["payment_items"]["quantity"];
								foreach($cosmetics as $delta){
									$arr_2 = json_decode(json_encode($_final->costume_sets), true);
									$id_2 = $delta["item_id"];
                							$result_2 = array_filter($arr_2, function($obj)use($id_2){return !empty($obj['id']) && $obj['id'] === $id_2;});
									$key_3 = key($result_2);
									$upper_cost = 0;
									$lower_cost = 0;
									if(!empty($result_2)){
										if(!empty($result_2[$key_3]["upper"])){
											$upper_cost = $result_2[$key_3]["upper"]["cost"];
											$arr = json_decode(json_encode($_final->costumes_upper), true);
											$id = $result_2[$key_3]["upper"]["item_id"];
                									$result_4 = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
											$key_5 = key($result_4);
											if(!empty($result_4)){
												$item_name = explode(".", $result_4[$key_5]["name"]);
												array_push($actual_cosmetics, (object)["name" => getLocalisedString($item_name[1], $_final->localised_strings), "rarity" => $result_4[$key_5]["rarity"], "id" => $id, "type" => "upper"]);
											}
										}
										else
											$upper_cost = 0;
										if(!empty($result_2[$key_3]["lower"])){
											$lower_cost = $result_2[$key_3]["lower"]["cost"];
											$arr = json_decode(json_encode($_final->costumes_lower), true);
											$id = $result_2[$key_3]["lower"]["item_id"];
                									$result_4 = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
											$key_5 = key($result_4);
											if(!empty($result_4)){
												$item_name = explode(".", $result_4[$key_5]["name"]);
												array_push($actual_cosmetics, (object)["name" => getLocalisedString($item_name[1], $_final->localised_strings), "rarity" => $result_4[$key_5]["rarity"], "id" => $id, "type" => "lower"]);
											}
										}
										else
											$lower_cost = 0;
										if(!empty($result_2[$key_3]["discount"]))
											$discount = $result_2[$key_3]["discount"];
										else
											$discount = 0;
										$currency->currency = $result[$key_2]["bundle_type"]["purchase_options"]["item_id"];
									}
									if(!empty($delta) and !empty($result_2) and $result[$key_2]["bundle_type"]["bundle_type"] == "costume_set"){
										if($result[$key_2]["bundle_type"]["bundle_type"] == "costume_set"){
											if($upper_cost + $lower_cost == 0)
												continue;
											$currency->quantity += (int)($upper_cost + $lower_cost);
										}
									}
									elseif($result[$key_2]["bundle_type"]["bundle_type"] == "discount"){

										if($delta["type"] != "costume_sets"){
											$arr;
											switch($delta["type"]){
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
											$id = $delta["item_id"];
                									$result_4 = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
											$key_5 = key($result_4);
											if(!empty($result_4)){
												$item_name = explode(".", $result_4[$key_5]["name"]);
												array_push($actual_cosmetics, (object)["name" => getLocalisedString($item_name[1], $_final->localised_strings), "rarity" => $result_4[$key_5]["rarity"], "id" => $id, "type" => substr($delta["type"], 9)]);
											}
										}
										$currency->currency = "gems";
										$discount = $result[$key_2]["bundle_type"]["discount"];
										$upper_cost_2 = 0;
										$lower_cost_2 = 0;
										// We made it to here!
										if(!empty($delta["cost"])){
											$upper_cost_2 += $delta["cost"];
										}
										if($upper_cost != 0 and $lower_cost != 0){
											$upper_cost_2 = $upper_cost;
											$lower_cost_2 = $lower_cost;
										}
										$currency->quantity += (int)($upper_cost_2 + $lower_cost_2);
									}
									else{
										if(true){
											$arr;
											switch($delta["type"]){
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
											$id = $delta["item_id"];
                									$result_4 = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
											$key_5 = key($result_4);
											if(!empty($result_4)){
												$item_name = explode(".", $result_4[$key_5]["name"]);
												array_push($actual_cosmetics, (object)["name" => getLocalisedString($item_name[1], $_final->localised_strings), "rarity" => $result_4[$key_5]["rarity"], "id" => $id, "type" => substr($delta["type"], 9)]);
											}
										}
									}
								}

								if($result[$key_2]["bundle_type"]["bundle_type"] == "discount" and empty($result[$key_2]["bundle_type"]["purchase_options"]["payment_items"]["quantity"])){
									$currency->quantity -= (int)$discount;
								}
								elseif($result[$key_2]["bundle_type"]["bundle_type"] == "default" and $result[$key_2]["bundle_type"]["purchase_options"]["payment_type"] == "iap"){
									$currency->quantity = 0;
									$currency->currency = "iap";
									$arr = json_decode(json_encode($_final->iaps), true);
									$id4 = $result[$key_2]["bundle_type"]["purchase_options"]["iap"];
                							$result4 = array_filter($arr, function($obj)use($id4){return !empty($obj['id']) && $obj['id'] === $id4;});
									$key_5 = key($result4);
									if(empty($result4)){
										var_dump($id4);
									}
									else{
										$currency->quantity = (int)$result4[$key_5]["dailyspendincrease"] / 100 ?? 0;
									}
								}
								elseif($result[$key_2]["bundle_type"]["bundle_type"] == "costume_set" and empty($result[$key_2]["bundle_type"]["purchase_options"]["payment_items"]["quantity"])){
									$currency->quantity -= (int)$discount;
								}
								$bundle_tile_image = $result[$key_2]["bundle_tile_image"] ?? null;
								$arr = json_decode(json_encode($_final->dlc_images), true);
								$id = $bundle_tile_image;
                						$result_3 = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
								$key_4 = key($result_3);
								if(!empty($result_3)){
									$bundle_tile_image = $result_3[$key_4]["dlc_item"]["base"] . $result_3[$key_4]["dlc_item"]["path"];
								}
								elseif(empty($result_3) and count($actual_cosmetics) == 1 and file_exists("../images/" . $actual_cosmetics[0]->id . ".png")){
									$bundle_tile_image = "https://cloudseeker.xyz/api/cv2/images/" . $actual_cosmetics[0]->id . ".png";
								}
								elseif(empty($result_3) and count($actual_cosmetics) == 1 and !file_exists("../images/" . $actual_cosmetics[0]->id . ".png")){
									$get_bundle_image = curl_init();
									$thing3 = "Pattern";
									switch($actual_cosmetics[0]->type){
										case "patterns":
											$thing3 = "Pattern";
										break;
										case "_punchlines":
											$thing3 = "Celebration";
										break;
										case "_emotes":
											$thing3 = "Emote";
										break;
										case "_nameplates":
											$thing3 = "Banner";
										break;
									}
									curl_setopt($get_bundle_image, CURLOPT_URL, "https://fallguysultimateknockout.fandom.com/api.php?action=cargoquery&format=json&limit=100&tables=". $thing3 ."&fields=id%2Cicon&where=id%3D'". $actual_cosmetics[0]->id ."'");
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
												file_put_contents("../images/" . $actual_cosmetics[0]->id . ".png", $xdl);
												$bundle_tile_image = "https://cloudseeker.xyz/api/cv2/images/" . $actual_cosmetics[0]->id . ".png";
											}
										}
									}
								}
								$bundle_bg = $result[$key_2]["bundle_background_custom_gradient_image"] ?? null;
								$id = $bundle_bg;
                						$result_4 = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
								$key_5 = key($result_4);
								if(!empty($result_4)){
									$bundle_bg = $result_4[$key_5]["dlc_item"]["base"] . $result_4[$key_5]["dlc_item"]["path"];
								}
								$dn = $result[$key_2]["display_name"] ?? "";
								$bundles[$result[$key_2]["id"]] = [
									"cost" => $currency,
									"name" => getLocalisedString($dn, $_final->localised_strings),
									"rarity" => $result[$key_2]["rarity"],
									"layout" => $layout,
									"images" => [
										"bundle_tile_image" => $bundle_tile_image,
										"bundle_background_custom_gradient_image" => $bundle_bg
									],
									"items" => $actual_cosmetics
								];
							}
						}
						$data_local[$alpha->id] = [
							"name" => getLocalisedString($alpha->name, $_final->localised_strings),
							"id" => $alpha->id,
							"bundles" => $bundles,
							"starts_at" => $starts_at,
							"ends_at" => $ends_at,
							"target_ids" => $alpha->target_ids
						];
					}
               			}
				$shops = $data_local;
			}
		}
	}
	catch(Exception $e){
		header("HTTP/2 500 Internal Server Error");
		crashWithErrorCode("Internal server error", "x_P_5000");
	}
	file_put_contents("../download-direct/" . $content_version . "-shops-". $cv2_lang .".json", json_encode($shops));
	usort($shops, 'sortByStartsAt');
	$result_object = [
		"xstatus" => "success",
		"shops" => $shops,
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
