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
	
	analyzeSearchData($elem);
}