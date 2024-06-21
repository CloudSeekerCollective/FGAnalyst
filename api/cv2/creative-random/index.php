<?php

	// CV2: A free and open source Fall Guys content viewing and downloading beacon created by The CloudSeeker Collective (https://cloudseeker.xyz) <admin@cloudseeker.xyz>.

	header("Content-Type: application/json");
	header("X-Powered-By: CloudSeeker CV2");
	include("../connect.php");
	header("Cache-Control: no-store, must-revalidate");
	$debug = array();

	function triggerErrorFailsafe($error, $errorCode){
		header("Cache-Control: no-store, must-revalidate");
		crashWithErrorCode($error, $errorCode);
	}

	$level_data = [];

	$query_string = "SELECT * FROM levels;";
	$query = mysqli_query($_WUSHU_ARCHIVE_DATABASE_LINK, $query_string);
	if(!$query)
		crashWithErrorCode("Internal database error!", "x_P_5400");
	while($x = mysqli_fetch_assoc($query)){
		$stop = false;
		array_push($level_data, ["common_name" => $x["common_name"], "share_code" => $x["share_code"], "tags" => json_decode($x["tags"]), "playcount" => (int)$x["playcount"], "likes" => (int)$x["likes"], "dislikes" => (int)$x["dislikes"], "max_players" => (int)$x["max_players"], "author_name" => $x["author_name"]]);
	}
	$mediatonickable = mt_rand(0, count($level_data));

	$return_object = [
		"xstatus" => "success",
		"share_code" => $level_data[$mediatonickable]["share_code"],
		"debug" => $debug
	];

	if($_HAS_SITEWIDE_ANNOUNCEMENT){
                $return_object["notice"] = $_SITEWIDE_ANNOUNCEMENT_CONTENTS;
        }
	echo(json_encode($return_object));

?>
