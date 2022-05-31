$(function() {
    $('.embed-responsive').parent().removeClass('center');

    // tabs / pills
    const nav = function (e) {
        var hash = e.target.hash.replace('#', '#/');
        if (history.pushState) {
            history.pushState(null, null, hash); 
        } else {
            window.location.hash = hash;
        }
    };
    
    $('.nav-tabs a').on('shown.bs.tab', nav);
    $('.nav-pills a').on('shown.bs.tab', nav);
    
    if (document.location.hash.match('#/')) {
        var hash = document.location.hash.replace(/^#\//, '');
        $('.nav-tabs a[href="#' + hash + '"]').tab('show');
        $('.nav-pills a[href="#' + hash + '"]').tab('show');
    }
    
    const clickFunc = function() {
        window.location = $(this).find("a").attr("href");
        return false;
    };

    $(".card").click(clickFunc);
});

function search(site, curobj) {
    curobj.q.value="site:" + site + " " + curobj.qfront.value;
}

function switchElements(hideSelector, showSelector) {
    $(hideSelector).toggleClass('hidden');
    $(showSelector).toggleClass('hidden');
}

function loadScript(url, callback) {
    var script = document.createElement("script");
    script.type = "text/javascript";

    if (script.readyState) { //IE
        script.onreadystatechange = function() {
            if (script.readyState === "loaded" || script.readyState === "complete") {
                script.onreadystatechange = null;
                callback();
            }
        };
    }
    else { //Others
        script.onload = function() {
            callback();
        };
    }

    script.src = url;
    document.getElementsByTagName("head")[0].appendChild(script);
}
