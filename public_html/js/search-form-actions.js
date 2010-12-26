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

// we will hold all ajax calls from search form in global array 
// to be able to cancel all ajax calls in case user starts typing
var SearchFormAjaxCalls = Array();

// flag that will indicate the ajax call
var SearchFormIsLoading = false;

// flag that will hols the last key that was pressed and will be used on "typing" plugin to 
// avoid typing "stop" event in case "Esc" was pressed
var lastKeyCode = 0;

// search form tempaltes
var searchFormPostersTemplate = 
	  '<a tabindex="3" id="header-selector-regions" href="#">${Regions}</a>'
	+ '<a tabindex="2" id="header-selector-services" href="#">${Services}</a>';

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
	    	stopLoadingPreSearchData($elem);
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
	    		|| lastKeyCode == KEY_CODE_HOME
	    		|| lastKeyCode == KEY_CODE_END
	    		|| lastKeyCode == KEY_CODE_PAGEUP
	    		|| lastKeyCode == KEY_CODE_PAGEDOWN
	    		) {
	    		return;
	    	}
	    	
	    	loadPreSearchData($elem);

	    },
	    
	    delay: TYPING_DELAY_TO_STOP
	    
	}).keydown(function(event) {

			lastKeyCode = event.keyCode;
			
			if (event.keyCode == KEY_CODE_ENTER) {
				event.preventDefault();
				loadPreSearchData($(this));
			}
			if (event.keyCode == KEY_CODE_ESCAPE) {
				event.preventDefault();
				stopLoadingPreSearchData($(this));
			}
			
		}
	);

	/*

	$('#header-selector').html("");
	var templateData = $.tmpl(searchFormPostersTemplate, 
		{ Regions : "Заліщики", Services: "Укладка кафельної плитки" }
	).appendTo('#header-selector');

	// OMFG: it looks like this plugin was donated with Microsoft because .html() produced total crap
	// so we should emulate .html() function by clearing element content and .appendTo() function 
	$('#header-selector').html("");
	var templateData = $.tmpl(searchFormPostersTemplate, 
		{ Regions : "Тернопіль, Чернівці", Services: "&nbsp;" }
	).appendTo('#header-selector');

	// alert(templateData);
	// $('#header-selector').html("");
	
	*/

});


function loadPreSearchData($elem) {

	if (true == SearchFormIsLoading) {
		return;
	}
	
	// we don't need to load anything in case the form field is empty
	if (("" == $elem.attr('value'))) {
		return;
	}

	if ($elem.attr('value').length < MIN_SEARCH_LENGHT) {
		return;
	}
	
	// start loading 
	$elem.addClass('is-loading');
	SearchFormIsLoading = true;
	
	
	// alert($elem.attr('value'));
}

function stopLoadingPreSearchData($elem) {
	SearchFormIsLoading = false;
	$elem.removeClass('is-loading');
	
	
}