<!DOCTYPE html>
<html>
	<head>
		<title>CV2 / CloudSeeker</title>
		<script src='/jquery-3.6.0.min.js'></script>
		<link href="/bootstrap.min.css" rel="stylesheet">
		<script src="/bootstrap.bundle.min.js"></script>
		<link href="/ChatroomsClient.css" rel="stylesheet">
		<script src="../ChatroomsClient.js"></script>
		<meta property="og:type" content="website">
	 	<meta property="og:description" content="CV2 is a Fall Guys content viewer and downloader made by The CloudSeeker Collective.">
	 	<meta property="og:title" content="CV2 / CloudSeeker">
	 	<meta property="og:url" content="https://cloudseeker.xyz">
		<meta property="og:image" content="https://cloudseeker.xyz/assets/CloudSeekerSplash.jpg">
		<meta property="twitter:image" content="https://cloudseeker.xyz/assets/CloudSeekerSplash.jpg">
		<link rel="manifest" href="/cv2/manifest.json">
		<script>var currentResource = "creative";</script>
	</head>
<body>
<!-- style='background: pink !important;' -->
<?php include("../header.php"); ?>
<br>
<div class="container text-center" id="cv2-base">
	<h1>Look up a Fall Guys Creative level</h1>
	<input class="form-control form-control-lg" maxlength="14" type="text" placeholder="Insert your level share code!" pattern="^\d{4}-\d{4}-\d{4}$" oninput="if(value.length == 4 || value.length == 9){value += '-';} getCreativeLevel(value)" aria-label="Level share code goes here...">
	<br><div class="cv2-download-loading"></div>
	<div id="cv2_level_sec" class="rounded border container p-4" style="display: none; text-align: left !important;">
		<div class="row">
			<div class="col-sm-7">
				<h2 id="cv2_level_name">LEVEL NAME <span class="badge rounded-pill text-bg-success">RACE</span> <span class="badge rounded-pill text-bg-primary">VERIFIED!</span> <span class="badge rounded-pill text-bg-light" style="display: none;">NOT VERIFIED</span></h2>
				<p id="cv2_level_basic">1234-5678-9012 | Version 99 | 1000 plays | <span class="badge rounded-pill text-bg-secondary">CHILL</span> <span class="badge rounded-pill text-bg-secondary">FALLJAM</span> | <span class="badge rounded-pill text-bg-danger" style="display: none;">UNMODERATED</span> <span class="badge rounded-pill text-bg-primary" style="display: none;">APPROVED!</span></p>
				<small id="cv2_level_authors_header">
					Level author:
				</small><br>
				<p id="cv2_level_authors">
					<span style="opacity: 0.5;">(Author unknown)</span>
				</p>
				<p id="cv2_level_ratings">👍 0 | 👎 0</p>
				<p id="cv2_level_desc">An awesome level by someone awesome!</p>
			</div>
			<div class="col-sm-5">
				<img src="https://fallguys-prod-player-level.azureedge.net/assets/5210-1471-1902/PreviewImage/0992232199.jpeg" id="cv2_level_image" style="" class="img-fluid rounded border">
				<small style="opacity: 0.5;" id="cv2_level_basic_2">40 max players<br>
				70% qualify<br>
				180 seconds<br>
				Classic theme<br>
				Published on game version 10.7.1</small><br><br>
				<a id="cv2_level_download" class="btn btn-primary" download href="#">Download this level!</a>
				<button id="cv2_level_share" class="btn btn-secondary" onclick="navigator.clipboard.writeText('https://cloudseeker.xyz/cv2/creative/?share_code=' + last_viewed_level); alert('Level code copied to clipboard!');">Share this level</button>
			</div>
		</div>
	</div>
	<hr>
	<div class="alert alert-warning">More features are coming soon! Follow us on <a href="https://twitter.com/CloudSeekerEN" target="_blank">Twitter/X</a> for more CV2 updates!</div>
	<div class="copyright">CV2 is a Fall Guys content viewer and downloader made by The CloudSeeker Collective.<br>
	The CloudSeeker Collective is not affiliated with Mediatonic and Epic Games. Fall Guys and the "Fall Guys" characters are registered trademarks of Mediatonic and Epic Games.<br>
	<b>All information on this website is subject to change.</b></div>
</div>
</div>
</div class='specialGuest'></div>
</body>
</html>

