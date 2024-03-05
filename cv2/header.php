<style>
@keyframes logo_anim {
  0% {top: -1000px;}
  60% {top: 10px;}
  100% {top: 0;}
}
h2{
	font-family: FG !important;
}
.row::-webkit-scrollbar-thumb {
	background: #fff;
}
</style>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<nav class="navbar navbar-expand-sm fixed-top bg-light">
  <div class="container-fluid blurrynavbarcontent">
    <img src='/assets/CloudSeekerBars.png' style='position: fixed; top: 0; left: 0; width: 12vw; height: 12vw;'>
    <a class="navbar-brand" href="/" style='animation-name: logo_anim; animation-duration: 0.75s; position: fixed; top: 0; left: 0; '><b><img src='/assets/Cloud_3D.png' alt='CloudSeeker' style="width: 12vw; height: 12vw;"></b></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" style='position: fixed; top: 0; right: 0;' data-bs-target="#mynavbar">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mynavbar" style='margin-left: 12vw;'>
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" id="home-navlink" href="/cv2/" style="font-family: FG; font-size: 30px; margin-right: 10px; margin-bottom: -20px; margin-top: -10px;">CV2 <!--<span style="font-size: 18px;" class="badge text-bg-primary">BETA</span>--></a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="shows-navlink" href="/cv2/shows/" style="margin-right: 10px;">Show Schedule</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="shops-navlink" href="/cv2/sym-item-shop/" style="margin-right: 10px;">Item Shop</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="creative-navlink" href="/cv2/creative/" style="margin-right: 10px;">Creative</a>
        </li>
	<li class="nav-item">
          <a class="nav-link" id="demonlist-navlink" href="/cv2/demonlist" style="color: var(--bs-warning); margin-right: 10px;">Crowned Levels</a>
        </li>
	<li class="nav-item">
          <a class="nav-link" id="about-navlink" href="/cv2/about.php" style="margin-right: 10px;">About</a>
        </li>
     </ul>
     <div class="d-flex">
	<button class="btn btn-primary" onclick="$('#settingsModal').modal('show')">Settings</button>
     </div>
    </div>
  </div>
</nav>
<div class="modal fade" id="settingsModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id='si_attention'>Settings</h4>
      </div>
      <div class="modal-body">
	<h2>FALL GUYS LOCALE</h2>
	<hr><p>Select which locale you would like to download the Fall Guys content file in.<br>
	<b>Keep in mind that this locale differs from CV2 and will NOT show the CV2 interface in the locale you select, but ONLY the content file/data!</b></p>
	<select class="form-select form-select-lg" id="locale" onchange="selectLocale(value);" aria-label="Choose a locale...">
                <option disabled selected>Choose a locale...</option>
                <option value="en">English</option>
                <option value="fr">Français</option>
                <option value="de">Deutsch</option>
                <option value="es">Español</option>
                <option value="es-LA">Español (Latinoamerica)</option>
                <option value="jp">日本語 (Japanese)</option>
                <option value="zh-CN">中国人 (Chinese Simplified)</option>
                <option value="zh-TW">中國人 (Chinese Traditional)</option>
                <option value="ko">한국어 (Korean)</option>
                <option value="pl">Polski</option>
                <option value="pt">Portugese (Brazil)</option>
                <option value="it">Italiano</option>
                <option value="ru">Русский</option>
        </select><br>
	<h2>THEME</h2>
	<hr><p>Select how you'd like CV2 to look.</p>
	<select class="form-select form-select-lg" id="theme" onchange="setTheme(value);" aria-label="Choose a theme...">
		<option disabled="" selected="">Choose a theme...</option>
		<option value="light">Light theme</option>
		<option value="dark">Dark theme</option>
	</select>
      </div>
      <div class="modal-footer">
  		<button id="settingsAcceptButton" class="btn btn-primary btn-block" data-bs-dismiss="modal">Okay</button>
      </div>

    </div>
  </div>
</div>
<div class="modal fade" id="loginModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Log In</h4>
      </div>

      <!-- Modal body -->
      <div class="modal-body">
        <div class="form-floating mb-3 mt-3">
     		<input type="text" class="form-control" id="LUSER" placeholder="Enter username..." name="Lusername">
      		<label id='init_user' for="Lusername">Username</label>
    	</div>
    	<div class="form-floating mt-3 mb-3">
     		<input type="password" class="form-control" id="LPASS" placeholder="Enter password..." name="Lpassword">
      		<label id='init_pass' for="Lpassword">Password</label>
    	</div>
	<a href='/chatrooms/login/'>Don't have a Chatrooms Hometown account?</a>
	<div id='loginError' class='color-danger'></div>
      </div>
      <!-- Modal footer -->
      <div class="modal-footer">
  		<!--<button onclick="$('#loginModal').modal('hide'); $('#offlineSettingsModal').modal({backdrop: 'static', keyboard: false}); $('#offlineSettingsModal').modal('show');" class="btn btn-secondary">Settings</button>-->
		<button type="button" class="btn btn-danger btn-block" onclick='pingsocket.close(); checkForSatellite();' data-bs-dismiss='modal'>Cancel</button>
		<button type="button" class="btn btn-primary btn-block" id='ConfirmLoginBTN' onclick='innerHTML="<div class=\"spinner-border\"></div>"; processLogin($("#LUSER")[0].value,$("#LPASS")[0].value);'>Log In</button>
      </div>

    </div>
  </div>
</div>
<script>
	if(typeof(Storage) !== "undefined") 
	{
/*		if(localStorage.getItem("sched_maintenance_010423") == "true"){
			console.log("User has consented to policies. No need to show modal.");
		}
		else{
			var showIt = setTimeout(function(){
				$('#consentModal').modal({backdrop: 'static', keyboard: false});
				$("#consentModal").modal("show");
			}, 250);
		}
*/	}

</script>

<nav class="navbar navbar-expand-sm" style="background-color: none;">
  <div class="container-fluid blurrynavbarcontent">
    <a class="navbar-brand" href="/"><b>.</b></a>
  </div>
</nav>
<div class='alert alert-info alert-dismissible fade show text-center' id='17-02-2024-settings'><b>Notice:</b> The locale and theme selector have been moved to the Settings menu!
<button type="button" class="btn-close" data-bs-dismiss="alert" onclick="localStorage.setItem('cv2_has_seen_alert_17_02_2024_settings', true);" aria-label="Close"></button></div>
<noscript>
	<div class='alert alert-danger text-center'><b>Please enable JavaScript to use CV2!</b></div>
</noscript>
