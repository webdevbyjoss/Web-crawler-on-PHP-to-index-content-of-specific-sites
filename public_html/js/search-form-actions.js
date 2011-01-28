// key codes from keydown event handler
const KEY_CODE_ENTER = '13';
const KEY_CODE_ESCAPE = '27';
const KEY_CODE_LEFT_ARROW = '37';
const KEY_CODE_RIGHT_ARROW = '39';
const KEY_CODE_SHIFT = '16';
const KEY_CODE_CTRL = '17';
const KEY_CODE_INSERT = '45';
const KEY_CODE_DELETE = '46';
const KEY_CODE_HOME = '36';
const KEY_CODE_END = '35';
const KEY_CODE_PAGEUP = '33';
const KEY_CODE_PAGEDOWN = '34';

// the minimum string lenght for AJAX
const MIN_SEARCH_LENGHT = 4;

// delay in miliseconds after typing stops and we can load results via AJAX
const TYPING_DELAY_TO_STOP = 1000; 

// flag that will hols the last key that was pressed and will be used on "typing" plugin to 
// avoid typing "stop" event in case "Esc" was pressed
var lastKeyCode = 0;

//flag that will indicate the ajax call
var SearchFormIsLoading = false;


// init search form
$(document).ready(function () {
	
	/**
	 * Optimize layout for small screens
	 */
	$(window).resize(function() {

		if (700 > $(window).width()) {
			$('#header-search-form')
				.removeClass()
				.addClass('header-search-form-narrow');
		} else {
			$('#header-search-form')
				.removeClass()
				.addClass('header-search-form-wide');
		}

	});

	$(window).trigger('resize');

	/**
	 * prepare search form 
	 */
	$('#header-search-form-element').focus().typing({
		
	    start: function (event, $elem) {
	    	// stop loading
	    	stopAnalyzeSearchData($elem);
	    },
	    
	    stop: function (event, $elem) {

	    	// lets be a little bit smart and with typing
	    	// so do not triget ajax loading if these key were pressed
	    	if (lastKeyCode == KEY_CODE_ESCAPE
	    		|| lastKeyCode == KEY_CODE_ENTER
	    		|| lastKeyCode == KEY_CODE_LEFT_ARROW
	    		|| lastKeyCode == KEY_CODE_RIGHT_ARROW
	    		|| lastKeyCode == KEY_CODE_SHIFT
	    		|| lastKeyCode == KEY_CODE_CTRL
	    		|| lastKeyCode == KEY_CODE_INSERT
	    		|| lastKeyCode == KEY_CODE_DELETE
	    		|| lastKeyCode == KEY_CODE_HOME
	    		|| lastKeyCode == KEY_CODE_END
	    		|| lastKeyCode == KEY_CODE_PAGEUP
	    		|| lastKeyCode == KEY_CODE_PAGEDOWN
	    		) {
	    		return;
	    	}
	    	analyzeSearchData($elem);

	    },
	    
	    delay: TYPING_DELAY_TO_STOP
	    
	}).keydown(function(event) {

			lastKeyCode = event.keyCode;
			
			if (event.keyCode == KEY_CODE_ENTER) {
				event.preventDefault();
				analyzeSearchData($(this));
			}
			if (event.keyCode == KEY_CODE_ESCAPE) {
				event.preventDefault();
				stopAnalyzeSearchData($(this));
			}
			
		}
	);
	
	$('#feedbackLink').click(function(){
		$('#feedback-form').removeClass('feedback-invisible');
		$('#feedback-form-text').autoResize({
		    // On resize:
		    onResize : function() {
		        $(this).css({opacity:0.8});
		    },
		    // After resize:
		    animateCallback : function() {
		        $(this).css({opacity:1});
		    },
		    // Quite slow animation:
		    animateDuration : 500,
		    // More extra space:
		    extraSpace : 40
		});
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
		$("#feedback-form-errors").empty();
		$("form[name=feedback-form-data] input[name=email]").css({"border": "1px solid silver"});
		$("form[name=feedback-form-data] input[name=telephone]").css({"border": "1px solid silver"});
		$("form[name=feedback-form-data] #feedback-form-text").css({"border": "1px solid silver"});
	}
	
	function FeedbackFormclearResult()
	{
		$("#feedback-send-result").empty();
	}
	
	$('#feedback-button-submit').click(function(){
		//clean errors messages
		FeedbackFormclearErrors();
		FeedbackFormclearResult();
		
		//check input data
		var email = $("form[name=feedback-form-data] input[name=email]").val();
		var telephone = $("form[name=feedback-form-data] input[name=telephone]").val();
		var message = $("form[name=feedback-form-data] #feedback-form-text").val();
		if ((email || telephone) && message) {
			//everything ok, send form data to server
			$("form[name=feedback-form-data]").ajaxSubmit({
				"method": "POST",
				"success": function(data) {
					$("#feedback-send-result").html(data);
				}
			});
		}
		else {
			// some errors
			var error_style = {
				"border": " 2px solid red", 
			};
			if (!email && !telephone) {
				$("#feedback-form-errors").append("<li>Please provide email or telephone<li>");
				$("form[name=feedback-form-data] input[name=email]").css(error_style);
				$("form[name=feedback-form-data] input[name=telephone]").css(error_style);
			}
			if (!message) {
				$("#feedback-form-errors").append("<li>Please provide message of feedback<li>");
				$("form[name=feedback-form-data] #feedback-form-text").css(error_style);
			}
		}
	
		return false;
	});
	
	/*
	ajaxForm({
		"success": function(data) {
			if (!data.success) {
				//display errors
				for (control in data.errors) {
					for (er in data.errors[control]) {
						$("#feedback-"+control+"-error").text(data.errors[control][er]);
						$("#"+control+"-element").css("margin-bottom", "0px");
					}
				}
			}
			else {
				//clear errors 
				$(".feedback-element-error").empty();
				$("#feedback-form").hide();
				$("#feedback-success-message").text(data.message);
			}
		},
		"dataType": 'json'
	})
	*/

});


// analyze search data an deside whether we need to redirect to search page or load data from AJAX
function analyzeSearchData($elem)
{
	if (true == SearchFormIsLoading) {
		return;
	}
	
	var keywords = $elem.attr('value');
	
	// we don't need to load anything in case the form field is empty
	if ("" == keywords) {
		return;
	}

	if (keywords.length < MIN_SEARCH_LENGHT) {
		return;
	}

	// OK. we are ready to load pre-search data via AJAX
	// BUT we need to make sure that page we are typing search query is a "Search" page 
	// and not the Articles, Catalogue, Help, etc..
	// so if we are not on "Search" page we should redirect first to the search page 
	// and only then we can process search queries
	//if (window.location.pathname)
	//return;
	var hashSeach = '#q=' + encodeURIComponent(keywords);

	var urlpath = location.pathname;
	
	if (urlpath == '/' || urlpath == '/uk/' || urlpath == '/ru/') {

		// on search page we should process the pagenation
		if (currentPage > 1) {
			hashSeach = hashSeach + '&page=' + currentPage;  
		}
		
		location.hash = hashSeach;
		loadPreSearchData($elem);
	}
	
	// redirect to search page
	location = '/' + locale + '/' + hashSeach;
}

// call function only if it exists
// else redirect to search form with the query
function stopAnalyzeSearchData($elem) {
	
	if (typeof stopLoadingPreSearchData == 'function') {
		stopLoadingPreSearchData($elem);
	}
	
	// FIXME: commented this due to bug with control keys (arrays, home,end,page up/down)
	// analyzeSearchData($elem);
}