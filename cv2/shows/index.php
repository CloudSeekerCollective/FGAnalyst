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
		<script>var currentResource = "shows";</script>
	</head>
<body>
<!-- style='background: pink !important;' -->
<?php include("../header.php"); ?>
<br>
<div class="modal modal-lg fade" id="roundpoolModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="exampleModalLabel"><span id="roundpool_view_show_name"></span>Roundpool</h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<p>Fallback round: <span id="fallback-round">Loading...</span></p>
				<p>Rounds available:</p>
				<div id="roundpool_view">
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
	<h1>Show Schedule</h1>
	<!--<div class="alert alert-primary"><b>Note:</b> The first live show in the first section is personalised for everyone. <b>That show will appear differently for you in game.</b> Usually, it's a rotation between Solos, Squads and another limited time show.</div>-->

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
	<ul class="nav nav-tabs" id="cv2-base-show-tabs" role="tablist">
		<li class="nav-item" role="presentation">
			<button class="nav-link active" id="live-tab" data-bs-toggle="tab" data-bs-target="#cv2-base-shows" type="button" role="tab" aria-controls="live-tab-pane" aria-selected="true">Live Shows</button>
		</li>
		<li class="nav-item" role="presentation">
			<button class="nav-link" id="discovery-tab" data-bs-toggle="tab" data-bs-target="#cv2-base-discovery-shows" type="button" role="tab" aria-controls="discovery-tab-pane" aria-selected="false">Discovery</button>
		</li>
		<li class="nav-item" role="presentation">
			<button class="nav-link" id="customs-tab" data-bs-toggle="tab" data-bs-target="#cv2-base-custom-shows" type="button" role="tab" aria-controls="customs-tab-pane" aria-selected="false">Custom Shows</button>
		</li>
	</ul>
	<div class="tab-content" id="cv2-base-show-contents">
		<div class="tab-pane fade show active" id="cv2-base-shows" role="tabpanel" aria-labelledby="cv2-base-shows" style="padding-top: 10px;" tabindex="0">...</div>
		<div class="tab-pane fade" id="cv2-base-discovery-shows" role="tabpanel" aria-labelledby="cv2-base-discovery-shows" style="padding-top: 10px;" tabindex="0"></div>
		<div class="tab-pane fade" id="cv2-base-custom-shows" role="tabpanel" aria-labelledby="cv2-base-custom-shows" style="padding-top: 10px;" tabindex="0"></div>
	</div>
	<hr>

	<div class="input-group mb-3" id="cv2_roundpool_sec">
		<div class="form-floating">
			<input type="text" class="form-control form-control-lg" id="cv2_roundpool_lookup" placeholder="Insert something like levels_episode.episode_s10_solo_show">
			<label for="cv2_roundpool_lookup">Search for a roundpool by ID...</label>
		</div>
		<button onclick="getShowRoundpool($('#cv2_roundpool_lookup')[0].value);" type="button" class="btn btn-primary btn-lg">Check</button>
	</div><hr>
	<div class="alert alert-warning">More features are coming soon! Follow us on <a href="https://twitter.com/CloudSeekerEN" target="_blank">Twitter/X</a> for more CV2 updates!</div>
	<div class="copyright">CV2 is a Fall Guys content viewer and downloader made by The CloudSeeker Collective.<br>
	The CloudSeeker Collective is not affiliated with Mediatonic and Epic Games. Fall Guys and the "Fall Guys" characters are registered trademarks of Mediatonic and Epic Games.<br>
	<b>All information on this website is subject to change.</b></div>
</div>
</div>
</div class='specialGuest'></div>
</body>
</html>

