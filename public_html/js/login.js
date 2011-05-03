/**
 * Signup example
 */

// init search form
$(document).ready(function () {
	
	/*
	var checkEmail = function () {
		analyzeEmail($(this));
	};
	*/
	
	// place focus in search field
	// $('#email').focus().keydown(checkEmail).change(checkEmail).mousemove(checkEmail).bind('tick', checkEmail);
	
});


function analyzeEmail($elem) {

	var email = $elem.attr('value');
	
	if (email == "") {
		return;
	}

	var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;

	if(reg.test(email) == false) {
		
		$('#email-submit').remove();
		$elem.removeClass('signup-email-ok');
		$elem.addClass('signup-email-fail');

	} else {
		
		if (locale == 'uk') {
			subscribeMessage = 'підписатися';
		} else {
			subscribeMessage = 'подписаться';
		}

		$elem.addClass('signup-email-ok');
		$elem.removeClass('signup-email-fail');

		$('#email-submit').remove();
		$elem.after(' <input id="email-submit" class="signup-email" type="submit" value="' + subscribeMessage + '" />');
		
		$('#email-submit').click(function(){
			var email = $('#email').attr('value');
			Subscribe(email);
		});
		
	}
}


function Subscribe(subscriptionEmail)
{
	$('#email-subscription').html('<img src="/images/ajax-data-loader-progress.gif" />');
	
	// stop periodical form check
	$.metronome.stop();
	
	$.post('/' + locale + '/login/subscribe/', { email: subscriptionEmail }, function(data) {
		$('#email-subscription').html(data);
	});
}