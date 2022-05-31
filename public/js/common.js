var delay = (function() {
    var timer = 0;
    return function(callback, ms) {
        clearTimeout(timer);
        timer = setTimeout(callback, ms);
    };
})();

// lazy loads elements with default selector as '.lozad'
const lozadObserver = (typeof lozad !== 'undefined')
    ? lozad()
    : null;

if (lozadObserver) {
    lozadObserver.observe();
}

function updateUi() {
    // colorbox
    if (typeof colorbox !== 'undefined' && colorbox instanceof HTMLDivElement) {
        //console.log('colorbox detected');
        $('.colorbox').colorbox({rel:'colorbox', maxWidth: '90%', scalePhotos: 'true'});
    }

    $('[data-toggle="tooltip"]').tooltip();
    $('.carousel').carousel();

    if (lozadObserver) {
        lozadObserver.observe();
    }
}

$(function() {
    focusOnModals();
    hideAlerts();

    $('[data-hide]').on('click', function() {
        $(this).closest('.' + $(this).attr('data-hide')).hide();
    });

    // smooth collapse opening
    $('.collapse').on('shown.bs.collapse', function(e) {
        const panel = $(this).closest('.panel');
        $('html,body').animate({
            scrollTop: panel.offset().top
        }, 500);
    });

    initSwipes();

    updateUi();
});

function isCarouselImg(element) {
    return $(element).hasClass('carousel-slide-img');
}

function findCarousel(element) {
    return $(element).closest('.carousel');
}

function initSwipes() {
    // check if swipes are enabled
    if (typeof Hammer !== 'function') {
        return;
    }

    $('.swipable').each((index, swipable) => {
        const mc = new Hammer(swipable);

        // listen to events...
        mc.on("swipeleft", ev => {
            const element = ev.target;

            // carousel?
            if (isCarouselImg(element)) {
                const carousel = findCarousel(element);

                if (carousel) {
                    carousel.carousel('next');
                }
            }
            else {
                navigateToNext();
            }
        });
        
        mc.on("swiperight", ev => {
            const element = ev.target;

            // carousel?
            if (isCarouselImg(element)) {
                const carousel = findCarousel(element);

                if (carousel) {
                    carousel.carousel('prev');
                }
            }
            else {
                navigateToPrev();
            }
        });
    });
}

function autofocus() {
    $('[data-focus]').focus();
}

function focusOnModals() {
    $('.modal').on('shown.bs.modal', function() {
        $('[data-modalfocus]', this).focus();
    });
}

function showAlert(selector, delay = null) {
    $(selector).show();

    if (delay) {
        $(selector).fadeTo(delay, 0.8).slideUp(500, function() {
            $(this).slideUp(500);
        });
    }
}

function showAlertSuccess() {
    showAlert('.alert-success', 2000);
}

function showAlertError() {
    showAlert('.alert-danger');
}

function showModalAlertError() {
    showAlert('.modal .alert-danger');
}

function hideAlert(selector) {
    $(selector).hide();
}

function hideAlertSuccess() {
    hideAlert('.alert-success');
}

function hideAlertError() {
    hideAlert('.alert-danger');
}

function hideAlerts() {
    hideAlertSuccess();
    hideAlertError();
}

function hideModalAlerts() {
    hideAlertSuccess();
    hideAlert('.modal .alert-danger');
}

function showModal(name) {
    hideAlerts();
    $('#' + name + 'View').modal('show');
}

function hideModal(name) {
    $('#' + name + 'View').modal('hide');
}

function resetForm(name) {
    $('#' + name + 'Form')[0].reset();
}

function reloadWindow() {
    location.reload();
}

function signedIn(data, targetUrl, withCookie) {
    const token = data['token'];

    if (!token) {
        throw "Invalid auth token!";
    }

    saveToken(token, withCookie);

    if (targetUrl) {
        location.href = targetUrl;
    }
    else {
        reloadWindow();
    }
}

function signedOut(data) {
    deleteToken();
    reloadWindow();
}

var authTokenKey = 'auth_token';
var authTokenCookieTtl = 365; // days

function getAuthTokenKey() {
    return authTokenKey;
}

function saveToken(token, cookie = false) {
    let key = getAuthTokenKey();

    localStorage.setItem(key, token);

    if (cookie) {
        saveCookie(key, token, authTokenCookieTtl);
    }
}

function loadToken() {
    let key = getAuthTokenKey();
    let item = localStorage.getItem(key);

    return item;
}

function deleteToken() {
    let key = getAuthTokenKey();

    localStorage.removeItem(key);

    deleteCookie(key);
}

function hasToken() {
    return loadToken();
}

function saveCookie(name, value, days) {
    let expires = "";

    if (days) {
        let date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toGMTString();
    }

    document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + expires + "; path=/";
}

function loadCookie(name) {
    let nameEq = encodeURIComponent(name) + "=";
    let ca = document.cookie.split(';');

    for (var i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEq) === 0) {
            return decodeURIComponent(c.substring(nameEq.length, c.length));
        }
    }

    return null;
}

function deleteCookie(name) {
    saveCookie(name, "", -1);
}

function getHeaders() {
    return {
        Authorization: 'Bearer ' + loadToken(),
    };
}

function getById(items, id) {
    if (items) {
        let results = $.grep(items, function(e) {
            return e.id === id;
        });

        if (results.length > 0) {
            return results[0];
        }
    }

    return null;
}

function parseDate(input) {
    if (input == null) {
        return null;
    }

    let mainParts = input.split(' ');
    let datePart = mainParts[0];
    let timePart = mainParts[1];

    let dateSubparts = datePart.split('-');
    let timeSubparts = timePart.split(':');

    return new Date(dateSubparts[0], dateSubparts[1] - 1, dateSubparts[2], timeSubparts[0], timeSubparts[1], timeSubparts[2]);
}

function dateToString(date, withTime = false) {
    if (date === null) {
        return null;
    }
    
    let dateStr = date.getFullYear() + '-' + ('0' + (date.getMonth() + 1)).slice(-2) + '-' + ('0' + date.getDate()).slice(-2);
    
    if (withTime) {
        dateStr += 'T' + ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2);
    }
    
    return dateStr;
}

function escapeHtml(html) {
    var text = document.createTextNode(html);
    var div = document.createElement('div');
    div.appendChild(text);
    return div.innerHTML;
}

function addClass(selector, className) {
    selector.attr('class', function(index, classNames) {
        return classNames + ' ' + className;
    });
}

function removeClass(selector, className) {
    selector.attr('class', function(index, classNames) {
        return classNames.replace(className, '');
    });
}

function momentDiff(start, end, unknownEnd, format = null, shortFormat = null) {
    format = format || 'D MMMM Y';
    shortFormat = shortFormat || 'D MMMM';
    let startMoment = moment(start);
    let startStr = startMoment.format(format);
    
    let result = '';

    if (!end) {
        result = unknownEnd
            ? startStr + ' — ?'
            : startStr;
    } else {
        let endMoment = moment(end);
        let endStr = endMoment.format(format);

        result = (startMoment.year() == endMoment.year() && startMoment.month() == endMoment.month())
            ? (startMoment.date() == endMoment.date() ? startStr : startMoment.date() + '–' + endStr) 
            : (startMoment.year() == endMoment.year() ? startMoment.format(shortFormat) : startStr) + ' — ' + endStr;
    }

    return result;
}

function getLastChild(id) {
    const el = document.getElementById(id);
    return el
        ? el.children[el.children.length - 1]
        : null;
}

function getRelLinks() {
    const elems = document.getElementsByTagName("link");
    const links = {};

    for (var i = 0; i < elems.length; i++) { // filter link elements
        const elem = elems[i];
        
        if (elem.rel === "prev") {
            links.prev = elem;
        }
        else if (elem.rel === "next") {
            links.next = elem;
        }
    }
    
    return links;
}

let fullscreenMode = false;

function toggleFullscreen() {
    if (fullscreenMode === true) {
        exitFullscreen();
    }
    else {
        openFullscreen();
    }
}

function openFullscreen() {
    if (fullscreenMode !== true) {
        setQueryParam('full', '');
        fullscreenMode = true;
    }
}

function exitFullscreen() {
    if (fullscreenMode === true) {
        removeQueryParam('full');
        fullscreenMode = false;
    }
}

function mutateUrlToFullscreen(url) {
    if (fullscreenMode) {
        url += '?full';
    }

    return url;
}

function navigateToPrev() {
    const links = getRelLinks();

    if (links.prev) {
        location.href = mutateUrlToFullscreen(links.prev.href);
    }
}

function navigateToNext() {
    const links = getRelLinks();

    if (links.next) {
        location.href = mutateUrlToFullscreen(links.next.href);
    }
}

function getUrl() {
    return new URL(window.location);
}

function getQueryParams() {
    return getUrl().searchParams;
}

function getQueryParam(name) {
    return getQueryParams().get(name);
}

function setUrl(path = null, params = null, hash = null) {
    path = path ? path : getUrl().pathname;

    const paramsStr = paramsToString(params);

    if (paramsStr.length > 0) {
        path += '?' + paramsStr;
    }

    if (hash) {
        path += '#' + hash.replace(/^#/, '');
    }

    window.history.replaceState({}, '', path);
}

function paramsToString(params) {
    let str = '';

    for (var p of params) {
        const name = p[0];
        const value = p[1];

        if (str.length > 0) {
            str += '&';
        }

        str += name;

        if (value !== '') {
            str += '=' + value;
        }
    }

    return str;
}

function objParamsToString(objParams) {
    let params = [];

    for (var prop in objParams) {
        params.push([prop, objParams[prop]]);
    }

    return paramsToString(params);
}

function setQueryParams(params) {
    const url = getUrl();
    setUrl(url.pathname, params, url.hash);
}

function setQueryParam(name, value) {
    const params = getQueryParams();
    params.set(name, value);

    setQueryParams(params);
}

function removeQueryParam(name) {
    const params = getQueryParams();
    params.delete(name);

    setQueryParams(params);
}

// link rel=prev, link rel=next keyboard navigation

document.addEventListener('keyup', e => {
    // abort if focusing input box
    if (document.activeElement.nodeName === "INPUT" || document.activeElement.nodeName === "TEXTAREA") {
        return;
    }

    if (e.keyCode === 37) { // left key
        navigateToPrev();
    }
    else if (e.keyCode === 39) { // right key
        navigateToNext();
    }
});
