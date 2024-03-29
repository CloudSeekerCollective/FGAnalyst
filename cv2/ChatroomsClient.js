var downloadAPI = "https://cloudseeker.xyz/api/cv2/download-direct/";
var showsAPI = "https://cloudseeker.xyz/api/cv2/show-selector/";
var newsAPI = "https://cloudseeker.xyz/api/cv2/newsfeed/";
var basicAPI = "https://cloudseeker.xyz/api/cv2/basic/";
var shopsAPI = "https://cloudseeker.xyz/api/cv2/symphony-item-shop/";
var showsRoundpoolAPI = "https://cloudseeker.xyz/api/cv2/show-roundpools/";
var creativeAPI = "https://cloudseeker.xyz/api/cv2/creative/";
var discoveryShowsAPI = "https://cloudseeker.xyz/api/cv2/creative-discovery/";
var downloadLocale = "en";
var downloadLink = "";
var last_viewed_level = "0000-0000-0000";

function filterText(text) {
    const specialCharacters = {
        	'&': '&amp;',
        	'<': '&lt;',
        	'>': '&gt;',
	        '"': '&quot;',
		"'": '&#39;'
	};

	return text.replace(/[&<>"']/g, match => specialCharacters[match]);
}

document.addEventListener("DOMContentLoaded", function(){
	if(typeof(localStorage.getItem("cv2_locale")) != "undefined"){
		downloadLocale = localStorage.getItem("cv2_locale");
		document.getElementById("locale").value = downloadLocale;
	}
	else{
		localStorage.setItem("cv2_locale", "en");
	}
	if(typeof(localStorage.getItem("cv2_has_seen_alert_17_02_2024_settings")) != "undefined" && localStorage.getItem("cv2_has_seen_alert_17_02_2024_settings") == "true"){
		$("#17-02-2024-settings").attr("style", "display: none;");
	}
	if(typeof(localStorage.getItem("cv2_theme")) != "undefined"){
		var theme = localStorage.getItem("cv2_theme");
		$("html").attr("data-bs-theme", theme);
		document.getElementById("theme").value = theme;
		if(theme == "dark"){
			$(".navbar")[0].classList = $(".navbar")[0].classList.toString().replace("bg-light", "bg-dark");
		}
	}
	else{
		localStorage.setItem("cv2_theme", "light");
		//$("html").attr("data-bs-theme", "light");
	}
	if(currentResource == "download"){
		requestDownload();
	}
	else if(currentResource == "shows"){
		getShows();
		getDiscoveryShows();
	}
	else if(currentResource == "sym-item-shop"){
		getShops();
	}
	else if(currentResource == "news"){
		getNewsfeeds();
	}
	else if(currentResource == "creative"){
		let get_args = new URLSearchParams(window.location.search);
		console.log(get_args);
		if(typeof(get_args.get("share_code")) != "undefined"){
			getCreativeLevel(get_args.get("share_code"));
			$("input.form-control-lg")[0].value = get_args.get("share_code");
		}
	}
});

function renderCurrency(q, t){
	if(q == 0){
		return "FREE!";
	}
	switch(t){
		// o7
		case "crowns":
			return "<img src='/cv2/Crowns.png' width='20' height='20'> " + q;
		break;
		case "kudos":
			return "<img src='/cv2/Kudos.png' width='20' height='20'> " + q;
		break;
		case "gems":
			return "<img src='/cv2/Primos.png' width='20' height='20'> " + q;
		break;
		case "crown_shards":
			return "<img src='/cv2/CrownShards.png' width='20' height='20'> " + q;
		break;
		case "fame":
			return "<img src='/cv2/Fame.png' width='20' height='20'> " + q;
		break;
		default:
			return q;
		break;
	}
}

function getRNG(min, max) {
	min = Math.ceil(min);
	max = Math.floor(max);
	return Math.floor(Math.random() * (max - min + 1)) + min;
}

function setTheme(newTheme){
	console.log(newTheme);
	// why the FUCK does this not work??????
	//if(newTheme == "light" || newTheme == "dark"){
	//	return "Theme not light or dark! Got: " + newTheme + " with type " + typeof(newTheme);
	//}
	localStorage.setItem("cv2_theme", newTheme);
	$("html").attr("data-bs-theme", newTheme);
	if(newTheme == "dark"){
		$(".navbar")[0].classList = $(".navbar")[0].classList.toString().replace("bg-light", "bg-dark");
	}
	else{
		$(".navbar")[0].classList = $(".navbar")[0].classList.toString().replace("bg-dark", "bg-light");
	}
	return "Done! Got: " + newTheme;
}

function selectLocale(loc){
	localStorage.setItem("cv2_locale", loc);
	downloadLocale = loc;
	if(currentResource == "download"){
		requestDownload();
	}
	else if(currentResource == "shows"){
		getShows();
		getDiscoveryShows();
	}
	else if(currentResource == "sym-item-shop"){
		getShops();
	}
	return "Locale updated to " + loc;
}

function getShows(){
	var totalShows = 0;
	$("#locale")[0].disabled = true;
	$("#cv2-base-shows").html('');
	$(".cv2-download-loading").html('<div class="spinner-border"></div>');
	$.get(showsAPI + "?locale=" + downloadLocale, function(data, status){
		console.log(status.toString());
		if(status.toString() == "success"){
			if(data.xstatus == "success"){
				//$("#cv2-download-content")[0].href = data.download;
				$(".cv2-download-loading").html('');
				Object.entries(data.shows.live_shows).forEach(function(currentValue){
					if(Object.entries(currentValue[1]).length <= 2)
						return;
					var div = document.createElement("div");
					div.id = "cv2-show-id-" + currentValue[0];
					document.getElementById("cv2-base-shows").appendChild(div);
					var thing = document.createElement("h2");
					document.getElementById(div.id).appendChild(thing);
					var table = document.createElement("table");
					table.classList = "table table-bordered";
					table.id = "cv2-show-table-" + currentValue[0];
					table.innerHTML = "<tr><th>Image</th><th>Name</th><th>Description</th><th>IDs</th><th>Begins</th><th>Ends</th><th>Rounds</th></tr>";
					document.getElementById(div.id).appendChild(table);
					Object.entries(currentValue[1]).forEach(function(currentValue2){
						if(typeof(currentValue2[1]) == "string")
							return;
						totalShows++;
						var tr_classlist = "";
						thing.innerHTML = currentValue[1].section_name.toUpperCase();// + ' <button data-bs-toggle="collapse" data-bs-target="#'+ table.id +'" aria-expanded="true" class="btn btn-primary"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-toggles" viewBox="0 0 16 16"><path d="M4.5 9a3.5 3.5 0 1 0 0 7h7a3.5 3.5 0 1 0 0-7zm7 6a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5m-7-14a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5m2.45 0A3.5 3.5 0 0 1 8 3.5 3.5 3.5 0 0 1 6.95 6h4.55a2.5 2.5 0 0 0 0-5zM4.5 0h7a3.5 3.5 0 1 1 0 7h-7a3.5 3.5 0 1 1 0-7"/></svg></button>';
						if(typeof(currentValue2[1].begins) == "number"){
							var start_date = new Date(currentValue2[1].begins * 1000);
							var start_date = start_date.toLocaleString();
						}
						else{
							var start_date = "N/A";
						}
						if(typeof(currentValue2[1].ends) == "number"){
							var end_date = new Date(currentValue2[1].ends * 1000);
							var end_date = end_date.toLocaleString();
						}
						else{
							var end_date = "Never";
						}
						if(Math.round(Date.now() / 1000) > currentValue2[1].begins && Math.round(Date.now() / 1000) < (currentValue2[1].ends * 1000) || currentValue2[1].ends == null){
							tr_classlist = "table-success";
						}
						//console.log(currentValue2[1].ends);
						table.innerHTML += "<tr class='"+ tr_classlist +"'><td><img src='"+ currentValue2[1].image +"' class='img-fluid rounded' width='234' height='256' /></td><td>"+ currentValue2[1].show_name +"</td><td>"+ currentValue2[1].show_desc +"</td><td>Show ID: "+ currentValue2[1].id +"<br>Roundpool ID: "+ currentValue2[1].roundpool.replace("levels_episode.", "") +"</td><td>"+ start_date +"</td><td>"+ end_date +"</td><td><button class='btn btn-outline-primary' onclick='getShowRoundpool(\""+ currentValue2[1].roundpool +"\")'>View roundpool</button></td></tr>";
					});
				});
				var div = document.createElement("div");
				div.id = "cv2-custom-show";
				document.getElementById("cv2-base-custom-shows").appendChild(div);
				var thing = document.createElement("h2");
				document.getElementById(div.id).appendChild(thing);
				var table = document.createElement("table");
				table.classList = "table table-bordered";
				table.id = "cv2-custom-show-table";
				table.innerHTML = "<tr><th>Image</th><th>Name</th><th>Description</th><th>IDs</th><th>Rounds</th></tr>";
				document.getElementById(div.id).appendChild(table);
				totalShows++;
				thing.innerHTML = "CUSTOM SHOWS";
				Object.entries(data.shows.custom_shows).forEach(function(currentValue){
					table.innerHTML += "<tr class=''><td><img src='"+ currentValue[1].image +"' class='rounded' width='234' height='256' /></td><td>"+ currentValue[1].show_name +"</td><td>"+ currentValue[1].show_desc +"</td><td>Show ID: "+ currentValue[1].id +"<br>Roundpool ID: "+ currentValue[1].roundpool.replace("levels_episode.", "") +"</td><td><button class='btn btn-outline-primary' onclick='getShowRoundpool(\""+ currentValue[1].roundpool +"\", true)'>View roundpool</button></td></tr>";
				});
				// lol, Lmao even
				if(getRNG(1, 1000) == 1){
					console.log(totalShows);
					$(".cv2-download-loading").html('<div class="alert alert-primary"><b>Note:</b> ALL upcoming shows are visible. Only '+ totalShows +' out of '+ totalShows +' shows are being shown above. ALL will be revealed right here, right now on CV2.</div>');
				}
				$("#locale")[0].disabled = false;
				if(typeof(data.notice) != "undefined" && data.notice != null)
					$(".cv2-download-loading").html('<div class="alert alert-primary">'+ data.notice +'</div>');
			}
			else if(data.xstatus == "successWithPrecautions"){
				$("#cv2-download-content").css("display", "block");
				$("#cv2-download-content").html("Download content file (" + data.contentVersion + ")");
				$("#cv2-download-content")[0].href = data.download;
				$(".cv2-download-loading").html('<div class="alert alert-warning">' + data.notice + '</div>');
				$("#locale")[0].disabled = false;
			}
			else{
				if(typeof(data.errorCode) == "undefined"){
					$(".cv2-download-loading").html('<div class="alert alert-danger">An unknown error occured...</div>');
					return;
				}
				if(data.errorCode == "x_P_1000")
					$(".cv2-download-loading").html('<div class="alert alert-primary">' + data.error + '</div>');
				else
					$(".cv2-download-loading").html('<div class="alert alert-danger">' + data.error + ' (Error code '+ data.errorCode +')</div>');
			}
		}
		else{
			$(".cv2-download-loading").html('<div class="alert alert-danger">A network error occured!</div>');
		}
	}).fail(function(){
		$(".cv2-download-loading").html('<div class="alert alert-danger">A network error occured!<br>Retrying in a few seconds...</div>');
		var retry = setTimeout(function(){getShows();}, 5000);
	});
}

function getNewsfeeds(){
	var totalShows = 0;
	$("#cv2_newsfeeds_carousel").css("display", "none");
	$("#locale")[0].disabled = true;
	$("#cv2_newsfeeds").html('');
	$(".carousel-indicators")[0].innerHTML = "";
	$(".cv2-download-loading").html('<div class="spinner-border"></div>');
	$.get(newsAPI + "?locale=" + downloadLocale, function(data, status){
		console.log(status.toString());
		if(status.toString() == "success"){
			if(data.xstatus == "success"){
				//$("#cv2-download-content")[0].href = data.download;
				$(".cv2-download-loading").html('');
				data.newsfeeds.sort((a, b) => a.starts_at - b.starts_at);
				Object.entries(data.newsfeeds).forEach(function(currentValue){
					if(Object.entries(currentValue[1]).length <= 2)
						return;
					$(".carousel-indicators")[0].innerHTML += '<button type="button" data-bs-target="#cv2_newsfeeds_carousel" data-bs-slide-to="'+ (totalShows) +'" class="carousel-indicator"></button>';
					var div = document.createElement("div");
					div.id = "cv2-news-id-" + currentValue[0];
					div.classList = "carousel-item";
					var title = "";
					var ead = "";
					var colour = "0,0,0";
					if(typeof(currentValue[1].header) != "null" && currentValue[1].header != ""){
						title = currentValue[1].header + ": " + currentValue[1].title;
					}
					else{
						title = currentValue[1].title;
					}
					if(typeof(currentValue[1].ends_at_desc) != "null" && currentValue[1].ends_at_desc != ""){
						ead = "<span class=\"badge rounded-pill text-bg-dark\">Ends At: " + currentValue[1].ends_at_desc + "</span>";
					}
					if(Math.round(Date.now() / 1000) > currentValue[1].starts_at && Math.round(Date.now() / 1000) < (currentValue[1].ends_at * 1000) || currentValue[1].ends_at == null){
						colour = "25,135,84";
					}
					var starts = new Date(currentValue[1].starts_at * 1000);
					starts = starts.toLocaleString();
					var ends = new Date(currentValue[1].ends_at * 1000);
					ends = ends.toLocaleString();
					div.innerHTML = '<img class="" alt="..." src="'+ currentValue[1].image +'"><div class="carousel-caption rounded text-white" style="background: rgba('+ colour +',0.5)"><h2>'+ title +'</h2><h3>'+ ead +'</h3><p>'+ currentValue[1].message +'<br>Starts At: '+ starts +'<br>Ends At: '+ ends +'</p></div>';
					document.getElementById("cv2_newsfeeds").appendChild(div);
					$("#cv2_newsfeeds_carousel").css("display", "block");
					totalShows++;
				});
				// lol, Lmao even
				if(getRNG(1, 1000) == 1){
					console.log(totalShows);
					$(".cv2-download-loading").html('<div class="alert alert-primary"><b>Note:</b> ALL upcoming shows are visible. Only '+ totalShows +' out of '+ totalShows +' shows are being shown above. ALL will be revealed right here, right now on CV2.</div>');
				}
				$(".carousel-indicator")[0].classList = "carousel-indicator active";
				$(".carousel-item")[0].classList = "carousel-item active";
				$("#locale")[0].disabled = false;
				if(typeof(data.notice) != "undefined" && data.notice != null)
					$(".cv2-download-loading").html('<div class="alert alert-primary">'+ data.notice +'</div>');
			}
			else if(data.xstatus == "successWithPrecautions"){
				$("#cv2-download-content").css("display", "block");
				$("#cv2-download-content").html("Download content file (" + data.contentVersion + ")");
				$("#cv2-download-content")[0].href = data.download;
				$(".cv2-download-loading").html('<div class="alert alert-warning">' + data.notice + '</div>');
				$("#locale")[0].disabled = false;
			}
			else{
				if(typeof(data.errorCode) == "undefined"){
					$(".cv2-download-loading").html('<div class="alert alert-danger">An unknown error occured...</div>');
					return;
				}
				if(data.errorCode == "x_P_1000")
					$(".cv2-download-loading").html('<div class="alert alert-primary">' + data.error + '</div>');
				else
					$(".cv2-download-loading").html('<div class="alert alert-danger">' + data.error + ' (Error code '+ data.errorCode +')</div>');
			}
		}
		else{
			$(".cv2-download-loading").html('<div class="alert alert-danger">A network error occured!</div>');
		}
	}).fail(function(){
		$(".cv2-download-loading").html('<div class="alert alert-danger">A network error occured!<br>Retrying in a few seconds...</div>');
		var retry = setTimeout(function(){getShows();}, 5000);
	});
}

function getDiscoveryShows(){
	var totalShows = 0;
	$("#locale")[0].disabled = true;
	$("#cv2-base-discovery-shows").html('<div class="spinner-border"></div>');
	//$(".cv2-download-loading").html('<div class="spinner-border"></div>');
	$.get(discoveryShowsAPI + "?locale=" + downloadLocale, function(data, status){
		console.log(status.toString());
		if(status.toString() == "success"){
			if(data.xstatus == "success"){
				//$("#cv2-download-content")[0].href = data.download;
				$("#cv2-base-discovery-shows").html('');
				//$(".cv2-download-loading").html('');
				Object.entries(data.level_data).forEach(function(currentValue){
					if(Object.entries(currentValue[1]).length <= 2)
						return;
					var div = document.createElement("div");
					div.id = "cv2-show-id-" + currentValue[0];
					document.getElementById("cv2-base-discovery-shows").appendChild(div);
					var thing = document.createElement("h2");
					document.getElementById(div.id).appendChild(thing);
					var table = document.createElement("div");
					table.classList = "collapse show";
					table.id = "cv2-discovery-show-table-" + currentValue[0];
					//table.innerHTML = "";
					document.getElementById(div.id).appendChild(table);
					// currentValue2 can be shortened to cV2... CV2 reference in CV2??????
					Object.entries(currentValue[1]).forEach(function(currentValue2){
						// make sure that no fallback values are being returned
						if(typeof(currentValue2[1]) == "string")
							return;
						let author = "(Author unknown)";
						let tags = "";
						let gamemode = "";
						switch(currentValue2[1].gamemode){
							case "GAMEMODE_GAUNTLET":
								gamemode = '<span class="badge rounded-pill text-bg-success" style="font-size: 20px;">RACE</span>';
							break;
							case "GAMEMODE_SURVIVAL":
								gamemode = '<span class="badge rounded-pill" style="background-color: #6f42c1;" style="font-size: 20px;">SURVIVAL</span>';
							break;
						}
						currentValue2[1].tags.forEach(function(cv3){
							// if it works, it works
							//console.log(cv3);
							tags += "<span class='badge rounded-pill text-bg-secondary'>"+ cv3.toUpperCase() +"</span> ";
						});
						// set the level desc to the provided description, or if it's empty/is == "...", make it show "(No description provided"
						let level_desc = currentValue2[1].description;
						if(currentValue2[1].description == "...")
							level_desc = "<span style='opacity: 0.5;'>(No description provided)</span>";
						// mediatonic moment
						if(typeof(currentValue2[1].author.eos) == "undefined" && Object.keys(currentValue2[1].author).length > 0){
							console.log(currentValue2[1].author);
							author = Object.entries(currentValue2[1].author)[0][1] + " (" + Object.entries(currentValue2[1].author)[0][0] + ")";
						}
						// an even mediatonic-er moment
						else if(Object.keys(currentValue2[1].author).length == 0)
							author = "<span style='opacity: 0.5;'>(Author unknown)</span>";
						// no mediatonic moment? Awesome! show the epic username
						else
							author = currentValue2[1].author.eos;
						totalShows++;
						var tr_classlist = "";
						thing.innerHTML = currentValue[1].section_name.toUpperCase() + ' <button data-bs-toggle="collapse" data-bs-target="#'+ table.id +'" aria-expanded="true" class="btn btn-primary"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-toggles" viewBox="0 0 16 16"><path d="M4.5 9a3.5 3.5 0 1 0 0 7h7a3.5 3.5 0 1 0 0-7zm7 6a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5m-7-14a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5m2.45 0A3.5 3.5 0 0 1 8 3.5 3.5 3.5 0 0 1 6.95 6h4.55a2.5 2.5 0 0 0 0-5zM4.5 0h7a3.5 3.5 0 1 1 0 7h-7a3.5 3.5 0 1 1 0-7"/></svg></button>';
						table.innerHTML += "<div class='card mb-2' style='text-align: left !important;'><div class='card-body'><div class='row'><div class='col-sm-7'><h5 class='card-title'><b>"+ currentValue2[1].title +" "+ gamemode +"</b></h5><h6 class='card-subtitle'>By <b>"+ author +"</b> | "+ currentValue2[1].share_code +" | "+ tags +"</h6><p class='card-text'>"+ level_desc +"<br>👍 "+ currentValue2[1].ratings.likes +" | 👎 "+ currentValue2[1].ratings.dislikes +"</p><a href='/cv2/creative/?share_code="+ currentValue2[1].share_code +"'>(Click here to view more info about this level)</a></div><div class='col-sm-5'><img src='"+ currentValue2[1].image  +"' class='img-fluid rounded' /></div></div></div><br>";
					});
				});
				//Object.entries(data.shows.custom_shows).forEach(function(currentValue){
				//	table.innerHTML += "<tr class=''><td>"+ currentValue[1].show_name +"</td><td>"+ currentValue[1].show_desc +"</td><td><button class='btn btn-outline-primary' onclick='getShowRoundpool(\""+ currentValue[1].roundpool +"\")'>View roundpool</button></td></tr>";
				//});
				// lol, Lmao even
				if(getRNG(1, 1000) == 1){
					console.log(totalShows);
					$(".cv2-download-loading").html('<div class="alert alert-primary"><b>Note:</b> ALL upcoming shows are visible. Only '+ totalShows +' out of '+ totalShows +' shows are being shown above. ALL will be revealed right here, right now on CV2.</div>');
				}
				if(data.notice != null && typeof(data.notice) != "undefined")
					$(".cv2-download-loading").html('<div class="alert alert-primary">' + data.notice + '</div>');
				$("#locale")[0].disabled = false;
			}
			/*else if(data.xstatus == "successWithPrecautions"){
				$("#cv2-download-content").css("display", "block");
				$("#cv2-download-content").html("Download content file (" + data.contentVersion + ")");
				$("#cv2-download-content")[0].href = data.download;
				$(".cv2-download-loading").html('<div class="alert alert-warning">' + data.notice + '</div>');
				$("#locale")[0].disabled = false;
			}*/
			else{
				if(typeof(data.errorCode) == "undefined"){
					$(".cv2-download-loading").html('<div class="alert alert-danger">An unknown error occured...</div>');
					return;
				}
				if(data.errorCode == "x_P_1000")
					$(".cv2-download-loading").html('<div class="alert alert-primary">' + data.error + '</div>');
				else
					$(".cv2-download-loading").html('<div class="alert alert-danger">' + data.error + ' (Error code '+ data.errorCode +')</div>');
			}
		}
		else{
			$(".cv2-download-loading").html('<div class="alert alert-danger">A network error occured!</div>');
		}
	}).fail(function(){
		$(".cv2-download-loading").html('<div class="alert alert-danger">A network error occured!<br>Retrying in a few seconds...</div>');
		var retry = setTimeout(function(){getShows();}, 5000);
	});
}

function getShops(){
	var totalShops = 0;
	$("#locale")[0].disabled = true;
	$("#cv2-base-shops").html('');
	$(".cv2-download-loading").html('<div class="spinner-border"></div>');
	$.get(shopsAPI + "?locale=" + downloadLocale, function(data, status){
		console.log(JSON.stringify(data));
		if(status.toString() == "success"){
			if(data.xstatus == "success"){
				//$("#cv2-download-content")[0].href = data.download;
				$(".cv2-download-loading").html('');
				Object.entries(data.shops).forEach(function(currentValue){
					let hasNoBundles = false;
					if(Object.entries(currentValue[1].bundles).length < 0)
						hasNoBundles = true;
					var div = document.createElement("div");
					div.id = "cv2-shop-id-" + currentValue[1].id;
					document.getElementById("cv2-base-shops").appendChild(div);
					var thing = document.createElement("p");
					document.getElementById(div.id).appendChild(thing);
					var div2 = document.createElement("div");
					div2.classList = "row p-2 rounded";
					//div2.style = "overflow: auto; white-space: nowrap; display: inline-block;";
					div2.id = "cv2-shop-section-" + currentValue[0];
					//div2.innerHTML = "<tr><th>Name</th><th>Description</th><th>Begins</th><th>Ends</th><th>Rounds</th></tr>";
					document.getElementById(div.id).appendChild(div2);
					Object.entries(currentValue[1].bundles).forEach(function(currentValue2){
						//if(typeof(currentValue2[1]) == "string")
							//return;
						totalShops++;
						var tr_classlist = "";
						if(typeof(currentValue[1].starts_at) == "number"){
							var start_date = new Date(currentValue[1].starts_at * 1000);
							var start_date = start_date.toLocaleString();
						}
						else{
							var start_date = "N/A";
						}
						if(typeof(currentValue[1].ends_at) == "number"){
							var end_date = new Date(currentValue[1].ends_at * 1000);
							var end_date = end_date.toLocaleString();
						}
						else{
							var end_date = "Never";
						}
						if(Math.round(Date.now() / 1000) > currentValue[1].starts_at && Math.round(Date.now() / 1000) < currentValue[1].ends_at || currentValue[1].ends_at == null){
							console.log("bundle is active");
							tr_classlist = "bg-success";
							div2.style = /*overflow: auto; white-space: nowrap; display: inline-block;*/ "background-color: #a3cfbb;";
						}
						thing.innerHTML = "<b>" + currentValue[1].name.toUpperCase() + "</b> | Starts At: " + start_date + " | Ends At: " + end_date;
						//console.log(currentValue2[1].ends);
						let bundle_image = "https://cloudseeker.xyz/cv2/Question.png";
						if(currentValue2[1].images.bundle_tile_image != null)/*typeof(currentValue2[1].images.bundle_tile_image) != "undefined" && typeof(currentValue2[1].images.bundle_tile_image) != "null"*/
							bundle_image = currentValue2[1].images.bundle_tile_image;
						let howBig = 3;
						if(currentValue2[1].layout.width == 2)
							howBig = 6;
						let ffs = filterText(JSON.stringify(currentValue2[1].items).replace(/[\r\n]+/gm, ""));
						div2.innerHTML += '<div class="col-sm-'+ howBig +'" style="display: inline-block;"><div class="card"><div class="card-header"><h5 style="font-family: FG;">'+ currentValue2[1].name.toUpperCase() +'</h5></div><div class="card-body" style="background-size: cover; background-image: url(\''+ currentValue2[1].images.bundle_background_custom_gradient_image +'\');"><img src="'+ bundle_image +'" style="width: 100%; height: 100%;"></div><div class="card-footer"><h5 style="font-family: FG;">'+ renderCurrency(currentValue2[1].cost.quantity, currentValue2[1].cost.currency) +'</h5> <button class="btn btn-primary" onclick=\'getItemList(JSON.parse(`' + ffs + '`))\'>VIEW ITEMS</button></div></div></div>';
						//table.innerHTML += "<tr class='"+ tr_classlist +"'><td>"+ currentValue2[1].show_name +"</td><td>"+ currentValue2[1].show_desc +"</td><td>"+ start_date +"</td><td>"+ end_date +"</td><td><button class='btn btn-outline-primary' onclick='getShowRoundpool(\""+ currentValue2[1].roundpool +"\")'>View roundpool</button></td></tr>";
					});
					div2.outerHTML += "<hr>";
				});
				// lol, Lmao even
				if(getRNG(1, 1000) == 1){
					console.log(totalShops);
					$(".cv2-download-loading").html('<div class="alert alert-primary"><b>Note:</b> ALL upcoming shows are visible. Only '+ totalShows +' out of '+ totalShows +' shows are being shown above. ALL will be revealed right here, right now on CV2.</div>');
				}
				if(data.notice != null && typeof(data.notice) != "undefined")
					$(".cv2-download-loading").html('<div class="alert alert-primary">' + data.notice + '</div>');
				$("#locale")[0].disabled = false;
			}
			else if(data.xstatus == "successWithPrecautions"){
				$("#cv2-download-content").css("display", "block");
				$("#cv2-download-content").html("Download content file (" + data.contentVersion + ")");
				$("#cv2-download-content")[0].href = data.download;
				$(".cv2-download-loading").html('<div class="alert alert-warning">' + data.notice + '</div>');
				$("#locale")[0].disabled = false;
			}
			else{
				if(typeof(data.errorCode) == "undefined"){
					$(".cv2-download-loading").html('<div class="alert alert-danger">An unknown error occured...</div>');
					return;
				}
				if(data.errorCode == "x_P_1000")
					$(".cv2-download-loading").html('<div class="alert alert-primary">' + data.error + '</div>');
				else
					$(".cv2-download-loading").html('<div class="alert alert-danger">' + data.error + ' (Error code '+ data.errorCode +')</div>');
			}
		}
		else{
			$(".cv2-download-loading").html('<div class="alert alert-danger">A network error occured!</div>');
		}
	}).fail(function(){
		$(".cv2-download-loading").html('<div class="alert alert-danger">A network error occured!<br>Retrying in a few seconds...</div>');
		var retry = setTimeout(function(){getShops();}, 5000);
	});
}

function getItemList(items){
	console.log(items);
	$("#shopItemsModal").modal("show");
	$("#items_view").html('');
	Object.entries(items).forEach(function(c){
		let type = "Cosmetic";
		let rarity = "COMMON";
		let rarity_colour = "#78848d";
		let rarity_colour_2 = "white";
		switch(c[1].type){
			case "upper":
				type = "Costume upper";
			break;
			case "lower":
				type = "Costume lower";
			break;
			case "faceplates":
				type = "Faceplate";
			break;
			case "_punchlines":
				type = "Celebration";
			break;
			case "nicknames":
				type = "Nickname";
			break;
			case "colour_schemes":
				type = "Colour";
			break;
			case "nameplates":
				type = "Nameplate";
			break;
			case "patterns":
				type = "Pattern";
			break;
			case "_emotes":
				type = "Emote";
			break;
		}
		switch(c[1].rarity.replace("rarities.", "")){
			case "common":
				rarity = "COMMON";
				rarity_colour = "#78848d";
			break;
			case "uncommon":
				rarity = "UNCOMMON";
				rarity_colour = "#6ce068";
			break;
			case "rare":
				rarity = "RARE";
				rarity_colour = "#8ceded";
				rarity_colour_2 = "black";
			break;
			case "epic":
				rarity = "EPIC";
				rarity_colour = "#b916d6";
			break;
			case "legendary":
				rarity = "LEGENDARY";
				rarity_colour = "#ec8515";
			break;
			case "special_4":
				rarity = "SPECIAL";
				rarity_colour = "#252525";
			break;
		}
		document.getElementById("items_view").innerHTML += '<div class="card" style="background-color: '+ rarity_colour +'; color: '+ rarity_colour_2 +';"><div class="card-body"><h5 class="card-title"><b>'+ c[1].name.toUpperCase() +' <span class="badge bg-light text-black">'+ rarity +'</span></b></h5><h6 class="card-subtitle mb-2">Item ID: '+ c[1].id +'</h6><p class="card-text">'+ type +'</p></div></div><br>';
	});
}

function getShowRoundpool(roundpool, custom){
	var totalShows = 0;
	let xcustom = "live";
	if(!roundpool.toString().startsWith("levels_episode.")){
		roundpool = "levels_episode." + roundpool;
	}
	$("#cv2_roundpool_sec").attr("style", "display: none;");
	$("#roundpoolModal").modal("show");
	$("#locale")[0].disabled = true;
	$("#fallback-round").html("Loading...");
	$("#roundpool_view").html('<div class="spinner-border"></div>');1
	if(typeof(custom) == "boolean" && custom)
		xcustom = "custom";
	$.get(showsRoundpoolAPI + "?locale=" + downloadLocale + "&roundpool=" + roundpool + "&intent=" + xcustom, function(data, status){
		console.log(status.toString());
		if(status.toString() == "success"){
			if(data.xstatus == "success"){
				//$("#cv2-download-content")[0].href = data.download;
				$(".cv2-download-loading").html('');
				$("#roundpool_view").html('');
				var moreInfo = "";
				var selectedColour = "";
				if(data.shows.fallback_round != ""){
					if(data.shows.fallback_round.type == "wushu"){
						moreInfo = data.shows.fallback_round.wushu_id;
					}
					switch(data.shows.fallback_round.archetype){
						case "archetype_final":
							selectedColour = "var(--bs-warning)";
						break;
						case "archetype_race":
							selectedColour = "var(--bs-success)";
						break;
						case "archetype_hunt":
							selectedColour = "var(--bs-primary-bg-subtle)";
						break;
						case "archetype_survival":
							selectedColour = "#6f42c1";
						break;
						case "archetype_logic":
							selectedColour = "#20c997";
						break;
						case "archetype_invisibeans":
							selectedColour = "black";
						break;
						case "archetype_timeattack":
							selectedColour = "var(--bs-success)";
						break;
						case "archetype_team":
							selectedColour = "#F85200";
						break;
					}
					$("#fallback-round").html('<br><br><div class="card" style="background-color: '+ selectedColour +';"><div class="card-body text-white"><h5 class="card-title"><b>' + data.shows.fallback_round.name + '</b></h5><h6 class="card-subtitle mb-2">' + data.shows.fallback_round.id +'</h6><p class="card-text">' + moreInfo + '</p></div></div>');
				}
				else{
					$("fallback-round").html("N/A");
				}
				Object.entries(data.shows.roundpool).forEach(function(c){
					console.log(c);
					switch(c[1].archetype){
						case "archetype_final":
							selectedColour = "var(--bs-warning)";
						break;
						case "archetype_race":
							selectedColour = "var(--bs-success)";
						break;
						case "archetype_hunt":
							selectedColour = "var(--bs-primary-bg-subtle)";
						break;
						case "archetype_survival":
							selectedColour = "#6f42c1";
						break;
						case "archetype_invisibeans":
							selectedColour = "black";
						break;
						case "archetype_logic":
							selectedColour = "#20c997";
						break;
						case "archetype_timeattack":
							selectedColour = "var(--bs-success)";
						break;
						case "archetype_team":
							selectedColour = "#F85200";
						break;
					}
					let level_name = c[1].name.replace("<br><pos=15%>", "").replace("<br><pos=12%>", "").replace("<br>", "");
					let cant_be_on = "";
					let only_be_on = "";
					let wushu_id = "";
					let wushu_author = "";
					if(typeof(c[1].wushu_id) != "undefined"){
						level_name += ' <span class="badge text-bg-info">CREATIVE</span>';
						wushu_id = "Level share code: " + c[1].wushu_id + " <a href='https://cloudseeker.xyz/cv2/creative/?share_code="+ c[1].wushu_id +"' target='_blank' class='text-white'>(Click here to view more info about this level)</a><br>";
						if(c[1].wushu_author != ""){
							//level_name += ' <span class="badge text-bg-primary">'+ c[1].wushu_author +'</span>';
							wushu_author = "Level created by: " + c[1].wushu_author + "<br>";
						}
					}
					if(c[1].cannot_be_on_stages.length > 0){
						cant_be_on = "Cannot be on stage: " + c[1].cannot_be_on_stages + "<br>";
					}
					if(c[1].can_only_be_on_stages.length > 0){
						only_be_on = "Can only be on stage: " + c[1].can_only_be_on_stages + "<br>";
					}
					let time_limit;
					if(typeof(c[1].time_remaining) == "number")
						time_limit = "Level time limit: " + c[1].time_remaining + " seconds<br>";
					else
						time_limit = "";
					$("#roundpool_view")[0].innerHTML += '<br><div class="card text-white" style="background-color: ' + selectedColour + ';"><div class="card-body"><h5 class="card-title"><b>' + level_name + '</b></h5><h6 class="card-subtitle mb-2">Level ID: ' + c[1].id +' | '+ c[1].min_players +'-'+ c[1].max_players +' players</h6><p class="card-text">'+ wushu_author +'' + wushu_id + '' + cant_be_on + '' + only_be_on + '' + time_limit + '</p></div></div>';
				});
				// lol, Lmao even
				if(getRNG(1, 1000) == 1){
					console.log(totalShows);
					$(".cv2-download-loading").html('<div class="alert alert-primary"><b>Note:</b> ALL upcoming shows are visible. Only '+ totalShows +' out of '+ totalShows +' shows are being shown above. ALL will be revealed right here, right now on CV2.</div>');
				}
				$("#locale")[0].disabled = false;
				$("#cv2_roundpool_sec").attr("style", "");
			}
			else if(data.xstatus == "successWithPrecautions"){
				$("#cv2-download-content").css("display", "block");
				$("#cv2-download-content").html("Download content file (" + data.contentVersion + ")");
				$("#cv2-download-content")[0].href = data.download;
				$(".cv2-download-loading").html('<div class="alert alert-warning">' + data.notice + '</div>');
				$("#locale")[0].disabled = false;
			}
			else{
				$("#locale")[0].disabled = false;
				$("#fallback-round").html("N/A");
				if(typeof(data.errorCode) == "undefined"){
					$("#roundpool_view").html('<div class="alert alert-danger">An unknown error occured...</div>');
					return;
				}
				if(data.errorCode == "x_P_1000")
					$("#roundpool_view").html('<div class="alert alert-primary">' + data.error + '</div>');
				else
					$("#roundpool_view").html('<div class="alert alert-danger">' + data.error + ' (Error code '+ data.errorCode +')</div>');
			}
		}
		else{
			$("#fallback-round").html("N/A");
			$(".cv2-download-loading").html('<div class="alert alert-danger">A network error occured!</div>');
		}
	}).fail(function(){
		$("#fallback-round").html("N/A");
		$("#roundpool_view").html('<div class="alert alert-danger">A network error occured!<br>Retrying in a few seconds...</div>');
		var retry = setTimeout(function(){getShows();}, 5000);z
	});
}

function getCreativeLevel(levelCode){
	var totalShows = 0;
	if(levelCode.match(/^\d{4}-\d{4}-\d{4}$/) == null){
		return "Still not matching the format!";
	}
	$("#cv2_level_sec").attr("style", "display: none;");
	//$("#roundpoolModal").modal("show");
	//$("#locale")[0].disabled = true;
	//$("#fallback-round").html("Loading...");
	$(".cv2-download-loading").html('<div class="spinner-border"></div>');
	$.get(creativeAPI + "?share_code=" + levelCode, function(data, status){
		console.log(status.toString());
		if(status.toString() == "success"){
			if(data.xstatus == "success"){
				//$("#cv2-download-content")[0].href = data.download;
				$("#cv2_level_sec").attr("style", "text-align: left; display: block;");
				$(".cv2-download-loading").html('');
				var verified = '<span class="badge rounded-pill text-bg-light">NOT VERIFIED</span>';
				var archetype = "";
				var tags = "No tags";
				var theme = "Classic";
				var approved = '<span class="badge rounded-pill text-bg-danger">UNMODERATED</span>';
				last_viewed_level = levelCode;
				if(data.level_data[0].version_metadata.is_completed){
					verified = '<span class="badge rounded-pill text-bg-primary">VERIFIED!</span>';
				}
				if(data.level_data[0].version_metadata.status == "Published"){
					approved = '<span class="badge rounded-pill text-bg-primary">APPROVED!</span>';
				}
				switch(data.level_data[0].version_metadata.level_theme_id){
					case "THEME_VANILLA":
						theme = "Classic";
					break;
					case "THEME_RETRO":
						theme = "Digital";
					break;
				}
				switch(data.level_data[0].version_metadata.game_mode_id){
					case "GAMEMODE_GAUNTLET":
						archetype = '<span class="badge rounded-pill text-bg-success" style="font-size: 20px;">RACE</span>';
					break;
					case "GAMEMODE_SURVIVAL":
						archetype = '<span class="badge rounded-pill" style="background-color: #6f42c1;" style="font-size: 20px;">SURVIVAL</span>'
						console.log("¯\_(ツ)_/¯");
					break;
				}
				data.level_data[0].version_metadata.creator_tags.forEach(function(c){
					// if it works it works
					tags = tags.replace("No tags", "");
					tags += '<span class="badge rounded-pill text-bg-secondary">'+ c.toUpperCase() +'</span> ';
				});
				console.log(data.level_data[0]);
				var level_date = new Date(Date.parse(data.level_data[0].version_metadata.last_modified_date));
				$("#cv2_level_name").html(data.level_data[0].version_metadata.title.toUpperCase() + " " + archetype);
				$("#cv2_level_ratings").html(data.level_data[0].stats.play_count + " plays | 👍 "+ data.level_data[0].stats.likes +" ("+ Math.round(data.level_data[0].stats.likes / (data.level_data[0].stats.likes + data.level_data[0].stats.dislikes) * 100) +"%) | 👎 " + data.level_data[0].stats.dislikes +" ("+ Math.round(data.level_data[0].stats.dislikes / (data.level_data[0].stats.likes + data.level_data[0].stats.dislikes) * 100) +"%)");
				$("#cv2_level_basic").html(data.level_data[0].share_code + " | Version " + data.level_data[0].version_metadata.version + " | " + tags + " | " + approved + " " + verified);
				$("#cv2_level_basic_2").html("Last updated on: " + level_date + "<br>" + data.level_data[0].version_metadata.max_player_count + " max players<br>" + data.level_data[0].version_metadata.config.qualification_percentage + "% qualify<br>" + data.level_data[0].version_metadata.config.time_limit_seconds + " seconds<br>" + theme + " theme<br>Published on game version " + data.level_data[0].version_metadata.client_version);
				if(data.level_data[0].version_metadata.thumb_url != null){
					$("#cv2_level_image")[0].style = "";
					$("#cv2_level_image")[0].src = data.level_data[0].version_metadata.thumb_url;
				}
				else
					$("#cv2_level_image")[0].style = "filter: grayscale();";
				$("#cv2_level_authors").html(data.level_data[0].author.name_per_platform.eos);
				$("#cv2_level_desc").html(data.level_data[0].version_metadata.description);
				$("#cv2_level_download").attr("href", data.level_data[0].version_metadata.scene_url);
				if(data.notice != null && typeof(data.notice) != "undefined")
					$(".cv2-download-loading").html('<div class="alert alert-primary">' + data.notice + '</div>');
				$("#locale")[0].disabled = false;
			}
			else if(data.xstatus == "successWithPrecautions"){
				$("#cv2-download-content").css("display", "block");
				$("#cv2-download-content").html("Download content file (" + data.contentVersion + ")");
				$("#cv2-download-content")[0].href = data.download;
				$(".cv2-download-loading").html('<div class="alert alert-warning">' + data.notice + '</div>');
				$("#locale")[0].disabled = false;
			}
			else{
				$("#locale")[0].disabled = false;
				//$("#fallback-round").html("N/A");
				if(typeof(data.errorCode) == "undefined"){
					$(".cv2-download-loading").html('<div class="alert alert-danger">An unknown error occured...</div>');
					return;
				}
				if(data.errorCode == "x_P_1000")
					$(".cv2-download-loading").html('<div class="alert alert-primary">' + data.error + '</div>');
				else
					$(".cv2-download-loading").html('<div class="alert alert-danger">' + data.error + ' (Error code '+ data.errorCode +')</div>');
			}
		}
		else{
			//$("#fallback-round").html("N/A");
			$(".cv2-download-loading").html('<div class="alert alert-danger">A network error occured!</div>');
		}
	}).fail(function(){
		//$("#fallback-round").html("N/A");
		$(".cv2-download-loading").html('<div class="alert alert-danger">A network error occured!<br>Retrying in a few seconds...</div>');
		var retry = setTimeout(function(){getShows();}, 5000);z
	});
}

function requestDownload(){
	$("#locale")[0].disabled = true;
	$("#cv2-download-content").css("display", "none");
	$("#cv2-download-content-2").css("display", "none");
	$(".cv2-download-loading").html('<div class="spinner-border"></div>');
	$.get(downloadAPI + "?locale=" + downloadLocale, function(data, status){
		if(status.toString() == "success"){
			if(data.xstatus == "success"){
				$("#cv2-download-content").css("display", "block");
				$("#cv2-download-content-2").css("display", "block");
				$("#content_version").html(data.contentVersion);
				$("#cv2-download-content")[0].href = data.download;
				$("#cv2-download-content-2")[0].href = data.download;
				$(".cv2-download-loading").html('');
				$("#locale")[0].disabled = false;
				if(data.notice != null && typeof(data.notice) != "undefined")
					$(".cv2-download-loading").html('<div class="alert alert-primary">' + data.notice + '</div>');
			}
			else if(data.xstatus == "successWithPrecautions"){
				$("#cv2-download-content").css("display", "block");
				$("#cv2-download-content-2").css("display", "block");
				$("#content_version").html(data.contentVersion);
				$("#cv2-download-content")[0].href = data.download;
				$("#cv2-download-content-2")[0].href = data.download;
				$(".cv2-download-loading").html('<div class="alert alert-warning">' + data.notice + '</div>');
				$("#locale")[0].disabled = false;
			}
			else{
				if(typeof(data.errorCode) == "undefined"){
					$(".cv2-download-loading").html('<div class="alert alert-danger">An unknown error occured...</div>');
					return;
				}
				if(data.errorCode == "x_P_1000")
					$(".cv2-download-loading").html('<div class="alert alert-primary">' + data.error + '</div>');
				else
					$(".cv2-download-loading").html('<div class="alert alert-danger">' + data.error + ' (Error code '+ data.errorCode +')</div>');
			}
		}
		else{
			$(".cv2-download-loading").html('<div class="alert alert-danger">A network error occured!</div>');
		}
	}).always(function(data){
		console.log(data);
		data = data.responseJSON;
		if(typeof(data.errorCode) != "undefined" && data.errorCode == "x_P_1000")
			$(".cv2-download-loading").html('<div class="alert alert-primary">' + data.error + '</div>');
		else if(typeof(data.errorCode) != "undefined")
			$(".cv2-download-loading").html('<div class="alert alert-danger">' + data.error + ' (Error code '+ data.errorCode +')</div>');
	});
}
