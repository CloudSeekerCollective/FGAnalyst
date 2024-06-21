<?php

	// CV2: A free and open source Fall Guys content viewing and downloading beacon created by The CloudSeeker Collective (https://cloudseeker.xyz) <admin@cloudseeker.xyz>.

	header("Content-Type: application/json");
	header("X-Powered-By: CloudSeeker CV2");
	include("../connect.php");
	$debug = array();

	if(empty($_GET["name"]))
		crashWithErrorCode("x_P_4500", "Please provide a level name...");
	$share_code = stripslashes(htmlspecialchars($_GET["name"]));
	// ABSOLUTE DOGWATER?
	if(!empty($_GET["min_likes"]))
		$min_likes = stripslashes(htmlspecialchars($_GET["min_likes"]));
	if(!empty($_GET["max_likes"]))
		$max_likes = stripslashes(htmlspecialchars($_GET["max_likes"]));
	if(!empty($_GET["min_dislikes"]))
		$min_dislikes = stripslashes(htmlspecialchars($_GET["min_dislikes"]));
	if(!empty($_GET["max_dislikes"]))
		$max_dislikes = stripslashes(htmlspecialchars($_GET["max_dislikes"]));
	if(!empty($_GET["min_playcount"]))
		$min_playcount = stripslashes(htmlspecialchars($_GET["min_playcount"]));
	if(!empty($_GET["max_playcount"]))
		$max_playcount = stripslashes(htmlspecialchars($_GET["max_playcount"]));
	if(!empty($_GET["min_max_players"]))
		$min_players = stripslashes(htmlspecialchars($_GET["min_max_players"]));
	if(!empty($_GET["max_max_players"]))
		$max_players = stripslashes(htmlspecialchars($_GET["max_max_players"]));
	if(!empty($_GET["author"]))
		$from = stripslashes(htmlspecialchars($_GET["author"]));
	if(!empty($_GET["tag1"])){
		$tags[0] = stripslashes(htmlspecialchars($_GET["tag1"]));
	}
	if(!empty($_GET["tag2"])){
		$tags[1] = stripslashes(htmlspecialchars($_GET["tag2"]));
	}

	function triggerErrorFailsafe($error, $errorCode){
		header("Cache-Control: no-store, must-revalidate");
		crashWithErrorCode($error, $errorCode);
	}

	$level_data = [];

	// because of an engine limitation this is the only workaround to this issue.
	// PHP IS A FUCK
	if($share_code == "*" and !empty($from)){
		$query_string = "SELECT * FROM levels;";
		$query = mysqli_query($_WUSHU_ARCHIVE_DATABASE_LINK, $query_string);
	}
	elseif($share_code == "*" and empty($from))
		$query = mysqli_query($_WUSHU_ARCHIVE_DATABASE_LINK, "SELECT * FROM levels WHERE author_name != 'Fall Guys Team' ORDER BY playcount DESC LIMIT 100;");
	else
		$query = mysqli_query($_WUSHU_ARCHIVE_DATABASE_LINK, "SELECT * FROM levels WHERE common_name LIKE '%". $share_code ."%';");

	if(!$query)
		crashWithErrorCode("Internal database error!", "x_P_5400");
	while($x = mysqli_fetch_assoc($query)){
		$stop = false;
		if(!empty($min_likes) and $min_likes > $x["likes"] or !empty($max_likes) and $max_likes < $x["likes"])
			$stop = true;
		if(!empty($min_dislikes) and $min_dislikes > $x["dislikes"] or !empty($max_dislikes) and $max_dislikes < $x["dislikes"])
			$stop = true;
		if(!empty($min_playcount) and $min_playcount > $x["playcount"] or !empty($max_playcount) and $max_playcount < $x["playcount"])
			$stop = true;
		//if(!empty($min_players) and $min_players > $x["max_players"] or !empty($max_players) and $max_players < $x["max_players"])
			//$stop = true;
		if(!empty($from) and !stripos($from, $x["author_name"]))
			$stop = true;
		if(!empty($tags[0])){
			if(!in_array($tags[0], json_decode($x["tags"])))
				$stop = true;
		}
		if(!empty($tags[1])){
			if(!in_array($tags[1], json_decode($x["tags"])))
				$stop = true;
		}

		if(!$stop)
			array_push($level_data, ["common_name" => $x["common_name"], "share_code" => $x["share_code"], "tags" => json_decode($x["tags"]), "playcount" => (int)$x["playcount"], "likes" => (int)$x["likes"], "dislikes" => (int)$x["dislikes"], "max_players" => (int)$x["max_players"], "author_name" => $x["author_name"]]);
	}

//	if(empty($level_data))
//		crashWithErrorCode("No levels have been found!", "x_P_4440");
	$return_object = [
		"xstatus" => "success",
		"level_data" => $level_data,
		"debug" => $debug
	];

	if($_HAS_SITEWIDE_ANNOUNCEMENT){
                $return_object["notice"] = $_SITEWIDE_ANNOUNCEMENT_CONTENTS;
        }
	echo(json_encode($return_object));

?>
