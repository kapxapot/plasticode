$(function() {
	// colorbox
	if (colorbox instanceof HTMLDivElement) {
		$('.colorbox').colorbox({rel:'colorbox', transition:'none', maxWidth: '90%', scalePhotos: 'true'});
	}
	
	$('.embed-responsive').parent().removeClass('center');

	// tabs
	$('.nav-tabs a').on('shown.bs.tab', function (e) {
		var hash = e.target.hash.replace('#', '#/');
	    if (history.pushState) {
	        history.pushState(null, null, hash); 
	    } else {
	        window.location.hash = hash;
	    }
	});
	
	if (document.location.hash.match('#/')) {
		var hash = document.location.hash.replace(/^#\//, '');
	    $('.nav-tabs a[href="#' + hash + '"]').tab('show');
	}
});

function search(site, curobj) {
	curobj.q.value="site:" + site + " " + curobj.qfront.value;
}

function switchElements(hideSelector, showSelector) {
	$(hideSelector).addClass('hidden');
	$(showSelector).removeClass('hidden');
}

function loadScript(url, callback) {
	var script = document.createElement("script");
	script.type = "text/javascript";
	
	if (script.readyState) {  //IE
		script.onreadystatechange = function() {
			if (script.readyState === "loaded" || script.readyState === "complete") {
				script.onreadystatechange = null;
				callback();
			}
		};
	}
	else {  //Others
		script.onload = function() {
			callback();
		};
	}

	script.src = url;
	document.getElementsByTagName("head")[0].appendChild(script);
}
