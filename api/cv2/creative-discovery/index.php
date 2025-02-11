<?php

	// CV2: A free and open source Fall Guys content viewing and downloading beacon created by The CloudSeeker Collective (https://cloudseeker.xyz) <admin@cloudseeker.xyz>.

	header("Content-Type: application/json");
	header("X-Powered-By: CloudSeeker CV2");
	include("../connect.php");
	$debug = array("thing" => time() >= strtotime($_EOS_EXPIRE));

	function triggerErrorFailsafe($error, $errorCode){
		header("Cache-Control: no-store, must-revalidate");
		crashWithErrorCode($error, $errorCode);
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
		triggerErrorFailsafe("Could not connect to the Fall Guys server at this moment", "x_C_4200");
	}
	$curl_done = json_decode((string)$curl_res);
	if(empty($curl_done->token)){
		triggerErrorFailsafe("Could not connect to the Fall Guys server at this moment", "x_C_4300");
	}
	$cv2_download_link = $curl_done->contentUrl;
	$cv2_fg_token = $curl_done->token;

	if(!file_exists("../download-direct/" . $cv2_lang . "/" . $curl_done->contentVersion . ".json")){
                header("Cache-Control: no-store, must-revalidate");
		$curl_cv2 = curl_init();
                curl_setopt($curl_cv2, CURLOPT_URL, $cv2_download_link);
                curl_setopt($curl_cv2, CURLOPT_USERAGENT, "User-Agent: UnityPlayer/". $_X_UNITY_VERSION ." (UnityWebRequest/1.0, libcurl/7.84.0-DEV)");
                curl_setopt($curl_cv2, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($curl_cv2, CURLOPT_RETURNTRANSFER, true);

                $curl_cv2_res = curl_exec($curl_cv2);

                if($curl_cv2_res == false){
                        crashWithErrorCode("Content file could not be downloaded", "x_F_4010");
                }
                $cv2_current = fopen("../download-direct/" . $cv2_lang . "/" . $curl_done->contentVersion . ".json", "w+");
                fwrite($cv2_current, $curl_cv2_res);
        }
        else{
                $curl_cv2_res = file_get_contents("../download-direct/" . $cv2_lang . "/" . $curl_done->contentVersion . ".json");
        }
	$_final = json_decode($curl_cv2_res);
	$final_level_data = array();

	foreach($_final->show_selector_sections as $alpha){
		if($alpha->section->type != "discovery")
			continue;
		$discovery_query = explode(".", $alpha->section->discovery_query);
		$curl_inst_2 = curl_init();

		curl_setopt_array($curl_inst_2, array(
			CURLOPT_URL => 'https://level-gateway.fallguys.oncatapult.com/api/v1/round_pools?id=' . $discovery_query[1],
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_HTTPHEADER => array(
				'User-Agent: UnityPlayer/2021.3.16f1 (UnityWebRequest/1.0, libcurl/7.84.0-DEV)',
				'X-Unity-Version: 2021.3.16f1',
				'Content-Type: application/json',
				'Authorization: Bearer ' . $cv2_fg_token
			),
		));
		$curl_res_2 = curl_exec($curl_inst_2);
		curl_close($curl_inst_2);

		if($curl_res_2 == false){
			triggerErrorFailsafe("Could not connect to the Fall Guys server at this moment", "x_C_4200");
		}
		$level_data = json_decode($curl_res_2);
		if(empty($level_data))
			continue;
		$sec_name = explode(".", $alpha->section_title);
		$final_level_data[$discovery_query[1]] = array("section_name" => getLocalisedString($sec_name[1], $_final->localised_strings));
		foreach($level_data[0]->levels as $beta){
			if($_LOG_WUSHU_LEVELS){
                		if(empty($beta->snapshot->author->name_per_platform->eos)){
                		        // you are given one fucking job to implement epic online services properly and not even your APIs fucking work
                		        $usernames = array_values((array)$beta->snapshot->author->name_per_platform);
					if(!empty($usernames[0]))
                		        	$epic_username = key((array)$beta->snapshot->author->name_per_platform) . "_" . $usernames[0];
                			else
						$epic_username = "";
				}
                		else{
                		        $epic_username = $beta->snapshot->author->name_per_platform->eos;
        		        }
				$lfdl = mysqli_query($_WUSHU_ARCHIVE_DATABASE_LINK, "INSERT INTO levels (common_name, share_code, tags, playcount, likes, dislikes, min_players, max_players, author_name) VALUES ('". stripslashes(htmlspecialchars($beta->snapshot->version_metadata->title)) ."', '". $beta->snapshot->share_code ."', '". json_encode($beta->snapshot->version_metadata->creator_tags) ."', ". $beta->snapshot->stats->play_count .", ". $beta->snapshot->stats->likes .", ". $beta->snapshot->stats->dislikes .", 1, ". $beta->snapshot->version_metadata->max_player_count .", '". $epic_username ."') ON DUPLICATE KEY UPDATE common_name = VALUES(common_name),tags = VALUES(tags),playcount = VALUES(playcount),likes = VALUES(likes),dislikes = VALUES(dislikes),min_players = VALUES(min_players),max_players = VALUES(max_players),author_name = VALUES(author_name);");
        		}
			if(!empty($beta->snapshot->images->PreviewImage[0]->url))
				$lvl_image = $beta->snapshot->images->PreviewImage[0]->url;
			else
				$lvl_image = "";
			array_push($final_level_data[$discovery_query[1]], (object)[
				"title" => $beta->snapshot->version_metadata->title, 
				"share_code" => $beta->snapshot->share_code, 
				"author" => $beta->snapshot->author->name_per_platform, 
				"description" => $beta->snapshot->version_metadata->description, 
				"image" => $lvl_image, 
				"ratings" => (object)[
					"playcount" => $beta->snapshot->stats->play_count,
					"likes" => $beta->snapshot->stats->likes, 
					"dislikes" => $beta->snapshot->stats->dislikes
				],
				"gamemode" => $beta->snapshot->version_metadata->game_mode_id,
				"tags" => $beta->snapshot->version_metadata->creator_tags,
				"is_verified" => $beta->snapshot->version_metadata->is_completed]
			);
			if($_DEBUG_LEVEL >= 2)
				array_push($debug, $beta->snapshot);
		}
	}
	$return_object = [
		"xstatus" => "success",
		"level_data" => $final_level_data,
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
