function updateUI() {
	// colorbox
	if (typeof colorbox !== 'undefined' && colorbox instanceof HTMLDivElement) {
	    //console.log('colorbox detected');
        $('.colorbox').colorbox({rel:'colorbox', maxWidth: '90%', scalePhotos: 'true'});
	}
    
    $('[data-toggle="tooltip"]').tooltip();
    $('.carousel').carousel();
}

$(function() {
	focusOnModals();
    hideAlerts();

    $('[data-hide]').on('click', function() {
        $(this).closest('.' + $(this).attr('data-hide')).hide();
    });
    
    updateUI();
});

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

function signedIn(data, targetUrl, withCookie) {
	saveToken(data['token'], withCookie);

	if (targetUrl) {
		location.href = targetUrl;
	}
	else {
		location.reload();
	}
}

function signedOut(data) {
	deleteToken();
	location.reload();
}

var authTokenKey = 'auth_token';

function getAuthTokenKey() {
	return authTokenKey;
}

function saveToken(token, cookie = false) {
	let key = getAuthTokenKey();
	
	localStorage.setItem(key, token);
	
	if (cookie) {
		saveCookie(key, token);
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
		Authorization: 'Basic ' + loadToken(),
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
	if (input === null) {
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

var delay = (function() {
    var timer = 0;
    return function(callback, ms) {
        clearTimeout(timer);
        timer = setTimeout(callback, ms);
    };
})();

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
