<?php

	// This is the CV2 configuration file. Feel free to configure it or add any other checks to your likings!

	$checkMod = 0;
	$lastmodded = time();
	$etag = '"' . md5($lastmodded) . '"';

	set_time_limit(120);
	include("en_us.php");

	// feel free to comment out or change the max age here to customise the server caching
	header("Cache-Control: public, max-age=20800");

	$_LOADED_CONFIG = json_decode(file_get_contents("exconfig.json"));

	$lang = "en";
	$cv2_lang = "en";
	$loc = "en_US";
	$_CV2_VERSION = "1.0.0";
	$_X_UNITY_VERSION = "2021.3.16f1";
	$_COMMON_FGPS_NAME = "Fall Guys";
	$_DEFAULT_PLATFORM = "win";
	$_REQUIRE_EOS_LOGIN = true;
	$_LOG_WUSHU_LEVELS = true;
	$_EOS_BLOB = json_decode(json_encode(file_get_contents("/your/locally/saved/cv2token")), true);
	$_EOS_BLOB = json_decode($_EOS_BLOB);
	$_REQUIRE_KEYCARD_AUTHENTICATION = false;
	$_CATAPULT_ENVIRONMENT = "production";
	$_CATAPULT_LOGIN_URL = "https://login.fallguys.oncatapult.com/api/v1/login/";
	$_EOS_EXPIRE = $_EOS_BLOB->expires_at ?? 0;
	$_EOS_ACCOUNT_TOKEN = $_EOS_BLOB->access_token ?? 0;
	$_EOS_REFRESH_TOKEN = $_EOS_BLOB->refresh_token ?? 0;
	$_HAS_SITEWIDE_ANNOUNCEMENT = true;
	$_CV2_RED_KITE_ALARM = true;
	$_CV2_USE_ARCHIVED = true;
	$_SITEWIDE_ANNOUNCEMENT_CONTENTS = "Hey, this is my own locally hosted FGAnalyst/CV2 instance!";
	$_CV2_MOTD = "CV2 is a Fall Guys content viewer and downloader made by The CloudSeeker Collective.<br>The CloudSeeker Collective is not affiliated with Mediatonic and Epic Games. Fall Guys and the \"Fall Guys\" characters are registered trademarks of Mediatonic and Epic Games.<br><b>All information on this website is subject to change.</b>";
	$_GAME_VERSION = $_LOADED_CONFIG->client_version;
	$_CLIENT_SIG = $_LOADED_CONFIG->client_signature;
	$_REFRESH_TOKEN_LOCATION = "/your/locally/saved/epicgamesrefresh.token";
	$_CV2_ENABLED = true;
	$_DISCORD_LOGGING = false;
	$_DISCORD_LOGGING_URL = "https://your-discord-webhook-here.com"
	$_DEBUG_LEVEL = 2;
	if($_LOG_WUSHU_LEVELS){
		$_WUSHU_ARCHIVE_DATABASE_LINK = mysqli_connect("MYSQL_SERVER", "MYSQL_USERNAME", "MYSQL_PASSWORD", "cv2_wushu_levels_arch");
	}
	$_ALLOWED_ACCOUNTS = array(
		1,
		2,
		69,
		110
	);
	$_REJECTED_WUSHU_LEVELS = array();
	$_KNOWN_WUSHU_LEVELS = array(
		"1899-0629-5726" => ["name" => "Ruta en la selva", "author" => "AyLa_Revenant"],
		"5780-8198-0947" => ["name" => "ROTATION STATION", "author" => "Daemontail."],
		"0589-3737-0601" => ["name" => "Frosty Frolics", "author" => "Fall Guys Team"],
		"0058-2575-2174" => ["name" => "Ball Park", "author" => "Fall Guys Team"],
		"1612-5875-5936" => ["name" => "Downtown Rush", "author" => "Fall Guys Team"],
		"2127-9536-2330" => ["name" => "roll roll bean", "author" => "Top2-forever"],
		"8773-4020-1405" => ["name" => "SPEEDROLLING", "author" => "lexusCK"],
		"5021-0663-0427" => ["name" => "Ball Carnival", "author" => "TazMac2"],
		"0127-6614-5946" => ["name" => "Spiral Trial", "author" => "LevelDesignGuy"],
		"9159-9775-0826" => ["name" => "Skyview Derby", "author" => "LevelDesignGuy"],
		"7692-7222-5282" => ["name" => "Rolling Speedway", "author" => "Rato_3010"],
		"1090-9209-3873" => ["name" => "Cloud Run", "author" => "King of Nothing."]
	);

		// some sorting functions
		function utf8Check($str) {
    			return preg_match('%(?:
        		[\xC2-\xDF][\x80-\xBF]                 # non-overlong 2-byte
        		|\xE0[\xA0-\xBF][\x80-\xBF]           # excluding overlongs
        		|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}    # straight 3-byte
        		|\xED[\x80-\x9F][\x80-\xBF]           # excluding surrogates
        		|\xF0[\x90-\xBF][\x80-\xBF]{2}        # planes 1-3
        		|[\xF1-\xF3][\x80-\xBF]{3}            # planes 4-15
        		|\xF4[\x80-\x8F][\x80-\xBF]{2}        # plane 16
    			)+%xs', $str);
		}

		function properText($text){
			$text = mb_convert_encoding($text, "HTML-ENTITIES", "UTF-8");
			$text = preg_replace('~^(&([a-zA-Z0-9]);)~', htmlentities('${1}'), $text);
			$text = html_entity_decode($text, ENT_NOQUOTES, "ISO-8859-1");
			return $text;
		}
		function sortByLikes($a, $b){
                        if($a["likes"] == $b["likes"]){
                                return 0;
                        }
                        return ($a["likes"] < $b["likes"]) ? -1 : 1;
                }
                function sortByPlays($a, $b){
                        if($a["playcount"] == $b["playcount"]){
                                return 0;
                        }
                        return ($a["playcount"] < $b["playcount"]) ? -1 : 1;
                }
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

		function crashWithErrorCode($error, $errorCode, $feeling = "sad", $extra = []){
			header("Cache-Control: no-store, must-revalidate");
			$return_object = [
				"xstatus" => "fail",
				"error" => $error,
				"errorCode" => $errorCode,
				"serverFeeling" => $feeling
			];

			// optional Discord webhook error logging - just change the URL to your webhook URL
			$ignoreErrors = ["x_P_4440", "x_P_4441", "x_P_1040", "x_F_4040", "x_P_4500"];
			if(!in_array($errorCode, $ignoreErrors) and $_DISCORD_LOGGING){
				$get_token2 = curl_init();
	                        $content = array("content" => "**An FGAnalyst error just occured!**\n Error code: " . $errorCode . "\n Error desc: " . $error . "\n Accessed URI: " . $_SERVER['REQUEST_URI']);
				curl_setopt($get_token2, CURLOPT_URL, $_DISCORD_LOGGING_URL);
	                        // NOTE: Discord does not accept custom useragents: curl_setopt($get_token2, CURLOPT_USERAGENT, "User-Agent: CloudSeekerEnterprise/1.0");
	                        curl_setopt($get_token2, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	                        curl_setopt($get_token2, CURLOPT_POST, true);
	                        curl_setopt($get_token2, CURLOPT_POSTFIELDS, json_encode($content));
	                        curl_setopt($get_token2, CURLOPT_RETURNTRANSFER, true);
                        	$get_token3 = curl_exec($get_token2);
			}
			echo json_encode($return_object);
			exit;
		}

		// log into epic games using provided refresh token
		function obtainEOSToken(){
			$device_code = json_decode(file_get_contents("/home/user-data/cv2token"))->refresh_token ?? 0;
			//this is just too funny not to keep curl_setopt($get_device2, CURLOPT_URL, "https://epicgames.com/id/api/device/". $get_device->user_code ."/Activa Cam :smiling_imp:");

			// NOTE: this is NOT specific authentication, this is just a token that tells Epic Games
			// "oh yeah the user is signing into Fall Guys"
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
					$file = fopen("/home/user-data/cv2token", "w+");
					fwrite($file, json_encode($get_token));
					crashWithErrorCode("FGAnalyst is refreshing the EOS session. Please try again in a few seconds!", "x_P_1040");
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

		// in order to enable authentication, you must also have a Chatroom
		// to integrate with CV2 with its database available to the server
		// you're going to be hosting CV2 on
		if($_REQUIRE_KEYCARD_AUTHENTICATION){
			if(empty($_SERVER["HTTP_AUTHORIZATION"])){
				crashWithErrorCode("Please log in to use FGAnalyst!", "x_P_4010");
			}
			$auth = substr($_SERVER["HTTP_AUTHORIZATION"], 7, strlen($_SERVER["HTTP_AUTHORIZATION"]));

			// TODO: set db authentication here
			$mysql = mysqli_connect("MYSQL_SERVER", "MYSQL_USERNAME", "MYSQL_PASSWORD", "chrms_universe");
			$mysql_req = mysqli_query($mysql, "SELECT * FROM `accounts` WHERE `authentication`='". $auth ."'");
			$mysql_thing = mysqli_fetch_assoc($mysql_req);
			if($mysql_thing > 0){
				if($mysql_thing["status"] == "BANNED"){
					crashWithErrorCode("Your account is not allowed to use FGAnalyst!", "x_P_4010");
				}
			}
			else{
				crashWithErrorCode("Please log in to use FGAnalyst!", "x_P_4010");
			}
		}

		// fix luca's CORS problem
		header("Access-Control-Allow-Origin: *");

?>
