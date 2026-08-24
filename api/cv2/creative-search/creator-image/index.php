<?php
	mb_language('uni');
	mb_internal_encoding('UTF-8');
	include("../../connect.php");

	$level_data = array();
	$from = stripslashes(htmlspecialchars($_GET["author"])) ?? 0;

	if(!empty($from)){
                $query_string = "SELECT * FROM levels;";
                $query = mysqli_query($_WUSHU_ARCHIVE_DATABASE_LINK, $query_string);
        }

	$username = "FGAnalyst";
	$nickname = "Fearless";
	$level_imag = "../../../../fga/ui_placeholder_bean.jpeg";
	$level_imag2 = "../../../../fga/ui_placeholder_bean.jpeg";
	$nameplate_id = "../../../../userstore/8c7dd352313af0ced5df12844ade51fa.png";
	$dislikes_amt = 0;
	$total_levels = 0;
	$likes_amt = 0;
	$plays_amt = 0;

	while($x = mysqli_fetch_assoc($query)){
                $stop = false;
                if(empty($from) or strtolower($from) != strtolower($x["author_name"]))
                        $stop = true;
                if(!$stop){
			$total_levels++;
			$username = $x['author_name'];
			$dislikes_amt += (int)$x['dislikes'];
			$likes_amt += (int)$x['likes'];
			$plays_amt += (int)$x['playcount'];
                        array_push($level_data, ["common_name" => $x["common_name"], "share_code" => $x["share_code"], "tags" => json_decode($x["tags"]), "playcount" => (int)$x["playcount"], "likes" => (int)$x["likes"], "dislikes" => (int)$x["dislikes"], "max_players" => (int)$x["max_players"], "author_name" => $x["author_name"]]);
		}
        }

	if(str_starts_with($username, "psn_") or str_starts_with($username, "xbl_")){
		$username = substr($username, 4);
	}

	usort($level_data, "sortByPlays");

	$most_played_level = $level_data[count($level_data) - 1] ?? 0;

	$levelsharecode = $most_played_level['share_code'] ?? 0;

	usort($level_data, "sortByLikes");

	$most_liked_level = $level_data[count($level_data) - 1] ?? 0;

	$levelsharecode2 = $most_liked_level['share_code'] ?? 0;

	// curly fries :D
	//if($most_played_level != 0){
      $curlyfries = curl_init();
      curl_setopt($curlyfries, CURLOPT_URL, "http://localhost:1337/api/cv2/creative/?share_code=" . $levelsharecode);
      curl_setopt($curlyfries, CURLOPT_RETURNTRANSFER, true);

      // bruh they fried them with palm oil
      $palm_oil = curl_exec($curlyfries);

      // buy our product NOW
      $product = json_decode($palm_oil);
      if(empty($product->error)){
		$nickname = stripslashes(htmlspecialchars($product->level_data[0]->author->nickname_content_id)) ?? "Fearless...?";
		if(file_exists("../../../cv2/images/" . $product->level_data[0]->author->nameplate_content_id . ".png")){
			$nameplate_id = "../../../cv2/images/" . $product->level_data[0]->author->nameplate_content_id . ".png";
		}
		$level_name = stripslashes(htmlspecialchars($product->level_data[0]->version_metadata->title));
                $level_desc = stripslashes(htmlspecialchars($product->level_data[0]->version_metadata->description));
                if(!empty($product->level_data[0]->version_metadata->thumb_url))
			$level_imag = $product->level_data[0]->version_metadata->thumb_url;
      }

	// BEGIN MOST LIKED LEVEL
	$curlyfries = curl_init();
      curl_setopt($curlyfries, CURLOPT_URL, "http://localhost:1337/api/cv2/creative/?share_code=" . $levelsharecode2);
      curl_setopt($curlyfries, CURLOPT_RETURNTRANSFER, true);

      // bruh they fried them with palm oil
      $palm_oil = curl_exec($curlyfries);

      // buy our product NOW
      $product = json_decode($palm_oil);
      if(empty($product->error)){
		$nickname = stripslashes(htmlspecialchars($product->level_data[0]->author->nickname_content_id)) ?? "Fearless...?";
		if(file_exists("../../../cv2/images/" . $product->level_data[0]->author->nameplate_content_id . ".png")){
			$nameplate_id = "../../../cv2/images/" . $product->level_data[0]->author->nameplate_content_id . ".png";
		}
		$level_name = stripslashes(htmlspecialchars($product->level_data[0]->version_metadata->title));
                $level_desc = stripslashes(htmlspecialchars($product->level_data[0]->version_metadata->description));
                if(!empty($product->level_data[0]->version_metadata->thumb_url))
			$level_imag2 = $product->level_data[0]->version_metadata->thumb_url;
      }
	// END MOST LIKED LEVEL
//print_r($level_data);
//exit;

    function drawRoundedBox($image, $x1, $y1, $x2, $y2, $radius, $color, $color2) {
        // Draw main rectangles to fill the shape
        imagefilledrectangle($image, $x1 + $radius, $y1, $x2 - $radius, $y2, $color2);
        imagefilledrectangle($image, $x1, $y1 + $radius, $x2, $y2 - $radius, $color2);

        // Draw the rounded corners using filled arcs
        imagefilledarc($image, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, 180, 270, $color, IMG_ARC_PIE);
        imagefilledarc($image, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, 270, 360, $color, IMG_ARC_PIE);
        imagefilledarc($image, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, 0, 90, $color, IMG_ARC_PIE);
        imagefilledarc($image, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, 90, 180, $color, IMG_ARC_PIE);
    }

    function drawRoundedBoxNoFill($image, $x1, $y1, $x2, $y2, $radius, $color, $color2) {
        // Draw main rectangles to fill the shape
        imagefilledrectangle($image, $x1 + $radius, $y1, $x2 - $radius, $y2, $color2);
        imagefilledrectangle($image, $x1, $y1 + $radius, $x2, $y2 - $radius, $color2);

        // Draw the rounded corners using filled arcs
        imagefilledarc($image, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, 180, 270, $color, IMG_ARC_PIE);
        imagefilledarc($image, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, 270, 360, $color, IMG_ARC_PIE);
        imagefilledarc($image, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, 0, 90, $color, IMG_ARC_PIE);
        imagefilledarc($image, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, 90, 180, $color, IMG_ARC_PIE);
    }

    // Set the content-type
    header('Content-Type: image/png');

    try{

    // Create the image
    //$im = imagecreatetruecolor(400, 30);
    $im = imagecreatefrompng("../../../../userstore/8a38a2d4cc55bee17f8101b3b2f93ba6.png");

    imagealphablending($im, true);
    // Create some colors
    $twhite = imagecolorallocate($im, 255, 255, 255);
    $cwhite = imagecolorallocatealpha($im, 240, 240, 240, 0);
    $white = imagecolorallocatealpha($im, 240, 240, 240, 0);
    $grey = imagecolorallocate($im, 128, 128, 128);
    $black = imagecolorallocate($im, 0, 0, 0);
    $npbd = imagecolorallocatealpha($im, 47, 14, 102, 64);
    $d = imagecolorallocate($im, 38, 38, 38);
    //imagefilledrectangle($im, 0, 0, 399, 29, $white);

    // The text to draw
    $head = "FALL GUYS CREATOR SUMMARY";
    $text = 'https://cloudseeker.xyz/?l=FGCreatorProfile';

    //$nickname = "Fearless";
    // logo
    //maybe another time...: $logo = imagecreatefrompng("../../../../../default/assets/CloudSeekerAdministrative.png");
    // more images
    $dfl = imagecreatefromjpeg($level_imag);
    $dfl2 = imagecreatefromjpeg($level_imag2);
    $dislike = imagecreatefromwebp("../../../../fga/Dislike.webp");
    $like = imagecreatefromwebp("../../../../fga/Like.webp");
    $np = imagecreatefrompng($nameplate_id);
    // Replace path by your own font path
    $font = './fg.ttf';
    $font_jp = './fgjp.ttf';
    $font2 = '../../../../assets/fonts/SourceSansPro-Black.ttf';

    $im = imagescale($im, 1080, 610);

    imagealphablending($im, true);
    imagesavealpha($im, true);

    drawRoundedBox($im, 170, 100, 920, 500, 20, $cwhite, $white);

    // Add some shadow to the text
    imagettftext($im, 20, 0, 13, 583, $npbd, $font, $text);

    //$logo = imagescale($logo, 340, 80);
    //imagecopy($im, $logo, 730, 520, 0, 0, imagesx($logo), imagesy($logo));

    $np = imagescale($np, 440, 120);
    imagecopy($im, $np, 190, 120, 0, 0, imagesx($np), imagesy($np));

    // Add the text
    imagettftext($im, 36, 0, 13, 48, $npbd, $font, $head);
    imagettftext($im, 36, 0, 10, 45, $twhite, $font, $head);

    //nametag

    $unamefont = $font2;
    if(utf8Check($username)){
	    $unamefont = $font_jp;
    }
    imagettftext($im, 24, 0, 353, 178, $npbd, $unamefont, $username);
    imagettftext($im, 24, 0, 350, 175, $twhite, $unamefont, $username);

    imagettftext($im, 16, 0, 353, 208, $npbd, $font2, $nickname);
    imagettftext($im, 16, 0, 350, 205, $twhite, $font2, $nickname);

    // likes, dislikes, plays etc
    imagettftext($im, 16, 0, 650, 140, $black, $font, $total_levels . " PUBLISHED LEVELS");
    drawRoundedBox($im, 650, 145, 900, 170, 5, $d, $d);
    $like = imagescale($like, 20, 20);
    imagecopy($im, $like, 870, 147, 0, 0, imagesx($like), imagesy($like));
    imagettftext($im, 16, 0, 660, 165, $twhite, $font, $likes_amt);
    $dislike = imagescale($dislike, 20, 20);
    drawRoundedBox($im, 650, 175, 900, 200, 5, $d, $d);
    imagecopy($im, $dislike, 870, 177, 0, 0, imagesx($dislike), imagesy($dislike));
    imagettftext($im, 16, 0, 660, 195, $twhite, $font, $dislikes_amt);
    //imagettftext($im, 16, 0, 650, 190, $black, $font, "TOTAL DISLIKES: 51");
    drawRoundedBox($im, 650, 205, 900, 230, 5, $d, $d);
    imagettftext($im, 16, 0, 660, 225, $twhite, $font, $plays_amt);
    imagettftext($im, 16, 0, 825, 225, $twhite, $font, "PLAYS");

    // starr levels
    if($most_played_level != 0){
	    $dfl = imagescale($dfl, 340, 190);
	    imagecopy($im, $dfl, 189, 290, 0, 0, imagesx($dfl), imagesy($dfl));
	    imagettftext($im, 20, 0, 200, 275, $black, $font, "MOST PLAYED LEVEL:");
	    //drawRoundedBox($im, 189, 400, 528, 480, 5, $d, $d);
	    $width = 528;
	    $height = 480;
	    for($y = 400; $y < $height; $y++) {
	        // Calculate alpha value based on position
	        $alpha = round(127 - (($y - 399) * 127 / 100));

	        // Create color with calculated alpha
	        $color = imagecolorallocatealpha($im, 0, 0, 0, $alpha);

	        // Draw line
	        imageline($im, 189, $y, $width, $y, $color);
	    }
	    $level_name_1 = $most_played_level["common_name"];
	    if(strlen($most_played_level["common_name"]) >= 18){
		    $level_name_1 = substr($most_played_level["common_name"], 0, 18) . "...";
	    }
	    $starrlevelfont = $font;
	    if(utf8Check($level_name_1)){
		    $starrlevelfont = $font_jp;
	    }
	    imagettftext($im, 20, 0, 200, 440, $cwhite, $starrlevelfont, $level_name_1);
	    imagettftext($im, 12, 0, 200, 465, $cwhite, $font, $most_played_level["share_code"] . "     " . $most_played_level["playcount"] . " PLAYS");
    }
    else{
	    imagedestroy($im);
	    imagedestroy($like);
	    imagedestroy($dislike);
	    imagedestroy($dfl);
	    imagedestroy($dfl2);
	    imagedestroy($np);
	    errorItOut();
    }

    $dfl2 = imagescale($dfl2, 340, 190);
    imagecopy($im, $dfl2, 549, 290, 0, 0, imagesx($dfl2), imagesy($dfl2));
    imagettftext($im, 20, 0, 560, 275, $black, $font, "MOST LIKED LEVEL:");
    //imagettftext($im, 12, 0, 725, 275, $grey, $font, "(by like/dislike ratio)");
    $width = 888;
    $height = 480;
    for($y = 400; $y < $height; $y++) {
        // Calculate alpha value based on position
        $alpha = round(127 - (($y - 399) * 127 / 100));

        // Create color with calculated alpha
        $color = imagecolorallocatealpha($im, 0, 0, 0, $alpha);

        // Draw line
        imageline($im, 549, $y, $width, $y, $color);
    }
    $level_name_2 = $most_liked_level["common_name"];
    if(strlen($most_liked_level["common_name"]) >= 18){
	    $level_name_2 = substr($most_liked_level["common_name"], 0, 18) . "...";
    }
    $starrlevelfont = $font;
    if(utf8Check($level_name_2)){
	    $starrlevelfont = $font_jp;
    }
    imagettftext($im, 20, 0, 560, 440, $cwhite, $starrlevelfont, $level_name_2);
    imagettftext($im, 12, 0, 560, 465, $cwhite, $font, $most_liked_level["share_code"] . "     " . $most_liked_level["likes"] . " LIKES");

    //drawRoundedBox($im, 550, 400, 889, 480, 5, $d, $d);

    //footer
    imagettftext($im, 20, 0, 10, 580, $twhite, $font, $text);
    imagettftext($im, 10, 0, 10, 600, $grey, $font2, "Fall Guys is a registered trademark of Mediatonic Ltd and Epic Games Inc. The CloudSeeker Collective is not affiliated.");

    imagepng($im);
    imagedestroy($im);
    imagedestroy($like);
    imagedestroy($dislike);
    imagedestroy($dfl);
    imagedestroy($dfl2);
    imagedestroy($np);
    }
    catch(Exception $e){
	    errorItOut();
    }

    function errorItOut(){
	    $im = imagecreatetruecolor(1080, 610);
	    $white = imagecolorallocate($im, 255, 255, 255);
	    $black = imagecolorallocate($im, 0, 0, 0);
	    $font = './fg.ttf';
	    $melodians = imagecreatefrompng('http://localhost:1337/userstore/a60ad9478b32bd319930a678bda89635.png');
	    imagettftext($im, 36, 0, 240, 100, $white, $font, "An error occured while");
	    imagettftext($im, 36, 0, 255, 180, $white, $font, "generating the image!");
	    imagecopy($im, $melodians, 420, 300, 0, 0, imagesx($melodians), imagesy($melodians));
	    imagepng($im);
	    imagedestroy($im);
	    imagedestroy($melodians);
	    exit;
    }

// Using imagepng() results in clearer text compared with imagejpeg()
?>
