/**
 * This script will hold all dinamic layout related functionality
 */


// init search form
$(document).ready(function () {

	$('#feedbackLink').click(function(){
		$('#feedback-form').removeClass('feedback-invisible');
		$('html, body').animate({scrollTop:0}, 'slow');
		$('#feedback-form-text').autoResize({
		    // Quite slow animation:
		    animateDuration : 100,
		    // More extra space:
		    extraSpace : 40
		});
		$('#feedback-form-text').focus();
		return false;
	});

	$('#feedback-button-hide').click(function(){
		FeedbackFormclearErrors();
		FeedbackFormclearResult();
		$('#feedback-form').addClass('feedback-invisible');
		return false;
	});
	
	function FeedbackFormclearErrors()
	{
		// $("#feedback-form-errors").empty();
		$("form[name=feedback-form-data] input[name=email]").css({"border": "1px solid silver"});
		$("form[name=feedback-form-data] input[name=telephone]").css({"border": "1px solid silver"});
		$("form[name=feedback-form-data] #feedback-form-text").css({"border": "1px solid silver"});
	}
	
	function FeedbackFormclearResult()
	{
		$("#feedback-send-result").empty();
	}
	
	$('#feedback-button-submit').click(function(){
		
		// check for selected radio
		/*
		if (!$("input[@name='category']:checked").val()) {
			// TODO: add message to explain user to about selecting the apropriate radios
			return false;
		}
		*/
		
		//clean errors messages
		FeedbackFormclearErrors();
		FeedbackFormclearResult();
		
		//check input data
		var email = $("form[name=feedback-form-data] input[name=email]").val();
		var telephone = $("form[name=feedback-form-data] input[name=telephone]").val();
		var message = $("form[name=feedback-form-data] #feedback-form-text").val();
		
		// TODO: do a regexp email validation
		// TODO: message should be more than 100 characters long
		
		if (message) {
			//everything ok, send form data to server
			$("form[name=feedback-form-data]").ajaxSubmit({
				"method": "POST",
				"success": function(data) {
					
					// TODO: change this functionality to make it more accurate
					FeedbackFormclearErrors();
					FeedbackFormclearResult();
					
					// TODO: clear form more accurate
					$("form[name=feedback-form-data] input[name=email]").val('');
					$("form[name=feedback-form-data] input[name=telephone]").val('');
					$("form[name=feedback-form-data] #feedback-form-text").val('');
					$("input[@name='category']:radio").attr("checked", false);
					$('#feedback-form').addClass('feedback-invisible');
					
					// TODO: show a beautifull message to user
					// $("#feedback-send-result").html(data);
					// $("#feedback-form").html('<h1>' + data + '</h1>');
					alert(data);
				}
			});
		}
		else {
			// some errors
			var error_style = {
				"border": " 2px solid red"
			};
			
			/*if (!email && !telephone) {
				$("form[name=feedback-form-data] input[name=email]").css(error_style);
			}*/
			if (!message) {
				$("form[name=feedback-form-data] #feedback-form-text").css(error_style);
			}
		}
	});
});