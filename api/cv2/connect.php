<?php

	// This is the CV2 configuration file. Feel free to configure it or add any other checks to your likings!

	set_time_limit(120);
	include("en_us.php");

	// feel free to comment out or change the max age here to customise the server caching
	header("Cache-Control: public, max-age=20800, must-revalidate");

	$lang = "en";
	$cv2_lang = "en";
	$loc = "en_US";
	$_CV2_VERSION = "1.0.0";
	$_X_UNITY_VERSION = "2021.3.16f1";
	$_REQUIRE_EOS_LOGIN = true;
	// NOTE: this cv2token file must contain the FULL epic games authentication response
	$_EOS_BLOB = json_decode(json_encode(file_get_contents("/path/to/your/cv2token")), true);
	$_EOS_BLOB = json_decode($_EOS_BLOB);
	$_REQUIRE_KEYCARD_AUTHENTICATION = false;
	$_CATAPULT_ENVIRONMENT = "production";
	$_CATAPULT_LOGIN_URL = "https://login.fallguys.oncatapult.com/api/v1/login/";
	$_EOS_EXPIRE = $_EOS_BLOB->expires_at;
	$_EOS_ACCOUNT_TOKEN = $_EOS_BLOB->access_token;
	$_EOS_REFRESH_TOKEN = $_EOS_BLOB->refresh_token;
	$_HAS_SITEWIDE_ANNOUNCEMENT = false;
	$_SITEWIDE_ANNOUNCEMENT_CONTENTS = "Welcome to CV2!";
	$_CV2_MOTD = "CV2 is a Fall Guys content viewer and downloader made by The CloudSeeker Collective. Feel free to set your own MOTD here!";
	// NOTE: you'll have to update the game version and signatures manually
	$_GAME_VERSION = "10.8.0";
	$_CLIENT_SIG = "AC88237737F9DA516C3ACBDCD0929796617A7D56E51A914D25AA31179386E5F8";
	// DEPRECATED, doesn't matter what you set this to
	$_REFRESH_TOKEN_LOCATION = "epicgamesrefresh.token";
	$_CV2_ENABLED = true;
	// MAX VALUE 2, set this value accordingly if you want to debug
	$_DEBUG_LEVEL = 0;
	// fixed list of Creative levels which CV2 should not bother actually checking to reduce redundancy
	$_KNOWN_WUSHU_LEVELS = array(
		"1899-0629-5726" => ["name" => "Ruta en la selva", "author" => "AyLa_Revenant"],
		"5780-8198-0947" => ["name" => "ROTATION STATION", "author" => "Daemontail."],
		"0589-3737-0601" => ["name" => "Frosty Frolics", "author" => "Fall Guys Team"],
		"0058-2575-2174" => ["name" => "Ball Park", "author" => "Fall Guys Team"],
		"1612-5875-5936" => ["name" => "Downtown Rush", "author" => "Fall Guys Team"],
		"2127-9536-2330" => ["name" => "roll roll bean", "author" => "Top2-forever"], // lmao wtf
		"8773-4020-1405" => ["name" => "SPEEDROLLING", "author" => "lexusCK"],
		"5021-0663-0427" => ["name" => "Ball Carnival", "author" => "TazMac2"],
		"0127-6614-5946" => ["name" => "Spiral Trial", "author" => "LevelDesignGuy"],
		"9159-9775-0826" => ["name" => "Skyview Derby", "author" => "LevelDesignGuy"],
		"7692-7222-5282" => ["name" => "Rolling Speedway", "author" => "Rato_3010"],
		"1090-9209-3873" => ["name" => "Cloud Run", "author" => "King of Nothing."]
	);

	// some sorting functions
	function sortByStartsAt($a, $b){
		if($a["starts_at"] == $b["starts_at"]){
			return 0;
		}
		return ($a["starts_at"] < $b["starts_at"]) ? -1 : 1;
	}

	function sortByStartsAtShows($a, $b){
		if(empty($a["begins"]) or empty($b["begins"]))
			return 0;
		if($a["begins"] == $b["begins"]){
			return 0;
		}
		return ($a["begins"] < $b["begins"]) ? -1 : 1;
	}

	function sortByStartsAtObject($a, $b){
		if($a->starts_at == $b->starts_at){
			return 0;
		}
		return ($a->starts_at < $b->starts_at) ? -1 : 1;
	}

	// if the epic access token expired, generate a new one
	if(strtotime($_EOS_EXPIRE) <= time()){
		obtainEOSToken();
	}

	if($_DEBUG_LEVEL >= 2){
		ini_set('display_errors', 1);
	}
	else{
		ini_set('display_errors', 0);
	}

	if(!$_CV2_ENABLED){
		crashWithErrorCode($_EN_US_ERROR_P_1000, "x_P_1000");
	}

	// quickly get a localised string
	function getLocalisedString($id, $from){
		$arr = json_decode(json_encode($from), true);
		$result = array_filter($arr, function($obj)use($id){return !empty($obj['id']) && $obj['id'] === $id;});
		if(!empty($result)){
			return $result[key($result)]["text"];
		}
		else{
			return false;
		}
	}

	// display content in different FG locales
	if(!empty($_GET["locale"])){
                switch($_GET["locale"]){
                        case "ru":
                                $lang = "ru";
				$cv2_lang = "ru";
                                $loc = "ru-RU";
                        break;
                        case "es":
                                $lang = "es";
				$cv2_lang = "es";
                                $loc = "es-ES";
                        break;
			case "es-LA":
                                $lang = "es";
				$cv2_lang = "esl";
                                $loc = "es-LA";
                        break;
                        case "it":
                                $lang = "it";
				$cv2_lang = "it";
                                $loc = "it-IT";
                        break;
                        case "fr":
                                $lang = "fr";
				$cv2_lang = "fr";
                                $loc = "fr-FR";
                        break;
                        case "de":
                                $lang = "de";
				$cv2_lang = "de";
                                $loc = "de-DE";
                        break;
                        case "jp":
                                $lang = "ja";
				$cv2_lang = "ja";
                                $loc = "ja-JP";
                        break;
			case "zh-CN":
                                $lang = "zh";
				$cv2_lang = "zh";
                                $loc = "zh-CN";
                        break;
			case "zh-TW":
                                $lang = "zh";
                                $cv2_lang = "zht";
				$loc = "zh-TW";
                        break;
                        case "ko":
                                $lang = "ko";
				$cv2_lang = "ko";
                                $loc = "ko-KO";
                        break;
                        case "pt":
                                $lang = "pt";
				$cv2_lang = "pt";
                                $loc = "pt-BR";
                        break;
                        case "pl":
                                $lang = "pl";
				$cv2_lang = "pl";
                                $loc = "pl-PL";
                        break;
                        default:
                                $lang = "en";
				$cv2_lang = "en";
                                $loc = "en-US";
                        break;
                }
        }
        else{
                $lang = "en";
		$cv2_lang = "en";
                $loc = "en-US";
        }

	function crashWithErrorCode($error, $errorCode){
		header("Cache-Control: no-store, must-revalidate");
		$return_object = [
			"xstatus" => "fail",
			"error" => $error,
			"errorCode" => $errorCode
		];
		echo json_encode($return_object);
		exit;
	}

	// log into epic games using provided refresh token
	function obtainEOSToken(){
		$device_code = json_decode(file_get_contents("/opt/lampp/cv2token"))->refresh_token;
		//this is just too funny not to keep curl_setopt($get_device2, CURLOPT_URL, "https://epicgames.com/id/api/device/". $get_device->user_code ."/Activa Cam :smiling_imp:");

		$headers = array("Authorization: Basic eHl6YTc4OTFtQURFRDB0UE5KRk9pRjhPbUkwRHdZMEo6OHcyc0R3TDUvR3VVamVWYkhaSXhlMUZBRndpK3R1UUkybXNTQ1ZJTytFQQ==", "Content-Type: application/x-www-form-urlencoded");
		$content = array("grant_type" => "refresh_token", "deployment_id" => "8bedfebaf56f406ebab78986ada3f9b3", "scope" => "basic_profile friends_list presence", "refresh_token" => $device_code);
		$get_token2 = curl_init();
		curl_setopt($get_token2, CURLOPT_URL, "https://api.epicgames.dev/epic/oauth/v2/token");
	        curl_setopt($get_token2, CURLOPT_USERAGENT, "User-Agent: CloudSeekerEnterprise/1.0");
	        curl_setopt($get_token2, CURLOPT_HTTPHEADER, $headers);
	        curl_setopt($get_token2, CURLOPT_POST, true);
        	curl_setopt($get_token2, CURLOPT_POSTFIELDS, http_build_query($content));
		curl_setopt($get_token2, CURLOPT_RETURNTRANSFER, true);

		$get_token3 = curl_exec($get_token2);
		$get_token = json_decode($get_token3, true);

		if($get_token != false){
			if(!empty($get_token["access_token"])){
				$file = fopen("/path/to/your/cv2token", "w+");
				fwrite($file, json_encode($get_token));
				crashWithErrorCode("Please try again in a few seconds!", "x_P_1040");
				$_EOS_ACCOUNT_TOKEN = $get_token["access_token"];
			}
			else{
				die('{"xstatus":"fail","error":"Could not connect to Epic Games", "errorCode":"x_C_3300", "detailedErrorEx":"'. $get_token["errorCode"] .'"}');
			}
		}
		else{
			die('{"xstatus":"fail","error":"Could not connect to Epic Games", "errorCode":"x_C_3200"}');
		}
		curl_close($get_token2);
	}

	// in order to enable authentication, you must also have a Chatroom to integrate with CV2 with its database available to the server...
	// you're going to be hosting CV2 on
	if($_REQUIRE_KEYCARD_AUTHENTICATION){
		if(empty($_SERVER["HTTP_AUTHORIZATION"])){
			header("HTTP/2 401 Unauthorized");
			crashWithErrorCode("x_P_4010", "Please log in to use this CV2 instance!");
		}
		$auth = substr($_SERVER["HTTP_AUTHORIZATION"], 7, strlen($_SERVER["HTTP_AUTHORIZATION"]));

		// TODO: set db authentication here
		$mysql = mysqli_connect("localhost", "username", "password", "chrms_universe");
		$mysql_req = mysqli_query($mysql, "SELECT * FROM `accounts` WHERE `authentication`='". $auth ."'");
		$mysql_thing = mysqli_fetch_assoc($mysql_req);
		if($mysql_thing > 0){
			if($mysql_thing["status"] == "BANNED"){
				header("HTTP/2 403 Forbidden");
				crashWithErrorCode("x_P_4010", "Your account is not allowed to use this CV2 instance!");
			}
		}
		else{
			header("HTTP/2 401 Unauthorized");
			crashWithErrorCode("x_P_4010", "Please log in to use this CV2 instance!");
		}
	}

	// fix luca's CORS problem
	header("Access-Control-Allow-Origin: *");

?>
