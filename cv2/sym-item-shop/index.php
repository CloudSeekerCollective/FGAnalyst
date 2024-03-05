<!DOCTYPE html>
<html data-bs-theme="light">
	<head>
		<title>CV2 / CloudSeeker</title>
		<script src='/jquery-3.6.0.min.js'></script>
		<link href="/bootstrap.min.css" rel="stylesheet">
		<script src="/bootstrap.bundle.min.js"></script>
		<script src="/popper.min.js"></script>
		<link href="/ChatroomsClient.css" rel="stylesheet">
		<script src="../ChatroomsClient.js"></script>
		<meta property="og:type" content="website">
	 	<meta property="og:description" content="CV2 is a Fall Guys content viewer and downloader made by The CloudSeeker Collective.">
	 	<meta property="og:title" content="CV2 / CloudSeeker">
	 	<meta property="og:url" content="https://cloudseeker.xyz">
		<link rel="manifest" href="/cv2/manifest.json">
		<script>var currentResource = "sym-item-shop";</script>
	</head>
<body>
<!-- style='background: pink !important;' -->
<?php include("../header.php"); ?>
<br>
<div class="modal modal-lg fade" id="shopItemsModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="exampleModalLabel"><span id="roundpool_view_show_name"></span>Items in this bundle</h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<p>Items available:</p>
				<div id="items_view">
					<div class="card bg-warning text-white" style="width: 18rem;">
						<div class="card-body">
							<h5 class="card-title"><b>BASKETFALL</b></h5>
							<h6 class="card-subtitle mb-2">basketfall id</h6>
							<p class="card-text">some more info here<br>if you're looking at this, hello</p>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-bs-dismiss="modal">Ok</button>
			</div>
		</div>
	</div>
</div>
<div class="container text-center" id="cv2-base">
	<h1>Item Shop</h1>

	<div class="cv2-download-loading"></div>
	<!--<h2>FEATURED</h2>
	<table class="table table-striped table-bordered">
		<tr>
			<th>Name</th>
			<th>Description</th>
			<th>Begins</th>
			<th>Ends</th>
			<th>Roundpool ID</th>
		</tr>
		<tr>
			<td>Squads</td>
			<td>Win or lose as a Squad of 4 Fall Guys! Join as a party or find a Squad inside!</td>
			<td>4/25/2023, 10:00:00 AM</td>
			<td>Never</td>
			<td>levels_episode.episode_squads_show_s10</td>
		</tr>
	</table>-->
	<div id="cv2-base-shops"></div>
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

