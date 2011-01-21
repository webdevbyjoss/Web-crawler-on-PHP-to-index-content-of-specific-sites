// We will load search results here
// this script will be loaded only on search results page


// search form tempaltes
var templateSearchFormBlocks = 
	  '<a tabindex="3" id="header-selector-regions" href="#">{{html Regions}}&nbsp;</a>'
	+ '<a tabindex="2" id="header-selector-services" href="#">{{html Services}}&nbsp;</a>';


//this will allow us to store the local cache
//TODO: its better to save data using persistent storage like "jquery-offline"
var Storage = Array();


//we will hold all ajax calls from search form in global array 
//to be able to cancel all ajax calls in case user starts typing
var SearchFormAjaxCalls = Array();

// global variable that will hold the current page value
var currentPage = 1;

// init search form
$(document).ready(function () {
	
	/**
	 * We should preload progress bar images
	 */
	(function($) {
		  var cache = [];
		  // Arguments are image paths relative to the current page.
		  $.preLoadImages = function() {
		    var args_len = arguments.length;
		    for (var i = args_len; i--;) {
		      var cacheImage = document.createElement('img');
		      cacheImage.src = arguments[i];
		      cache.push(cacheImage);
		    }
		  }
		})(jQuery);
	
	jQuery.preLoadImages("/images/ajax-data-loader-progress.gif", "/images/ajax-loader.gif");
	

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

	/**
	 * We should process search results in case search params were passed
	 */
	var queryParams = getHashVars(location.hash);
	var $elem = $('#header-search-form-element');
	$elem.attr('value', queryParams['q']);
	currentPage = parseInt(queryParams['page']);
	analyzeSearchData($elem);
});




// Detects hash params and bilds an array of values
// 
// We have the following URL: "http://www.domain.com/path/page.html#param1=value1&param2=value2"
// this function will extract the part: "param1=value1&param2=value2" and build an array:
// param1 = value1
// param2 = value2
// 
// inspired by: http://jquery-howto.blogspot.com/2009/09/get-url-parameters-values-with-jquery.html
// 
// @returns {Array}
function getHashVars() 
{
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('#') + 1).split('&');
    for(var i = 0; i < hashes.length; i++)
    {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = decodeURIComponent(hash[1]);
    }
    return vars;
}




//loads pre-fetch data
function loadPreSearchData($elem)
{
	var keywords = $elem.attr('value');
	
	// start loading 
	stopLoadingPreSearchData($elem);
	
	// in case some data for this particular request was already loaded some time ago
	// we can get that data fromt the local cache
	// FIXME: this is commented out as I was not able to make this 
	// operational withour bugs for some cyrillyc letters 
	//if (data = Storage[keywords]) {
	//	updateForm(data);
	//	return;
	//}
	
	$elem.addClass('is-loading');
	SearchFormIsLoading = true;
	
	ajaxCallHandler = $.getJSON('/' + locale +'/search/presearch/query/data/' + encodeURIComponent(keywords), null, function(data) {
		// load data and store it into local cache
		updateForm(data);
		stopLoadingPreSearchData($('#header-search-form-element'));
	});
	SearchFormAjaxCalls.push(ajaxCallHandler); 
	
}



//stops all AJAX calls and changes form loading indication
function stopLoadingPreSearchData($elem) {
	SearchFormIsLoading = false;
	$elem.removeClass('is-loading');

	for (var i in SearchFormAjaxCalls) {
		SearchFormAjaxCalls[i].abort();
	}
}

//updates search form with the data received from the pre-search process
function updateForm(data) {
	
	// temporrary solution
	var regionIds = null;
	var serviceIds = null;
	
	var regionsHTML = "";
	if (data.regions) {
		$.each(data.regions, function(i, val) {
			if ("" == regionsHTML) {
				regionsHTML = '<span id="city-' + i + '">' + val + '</span>';
			} else {
				regionsHTML += ', <span id="city-' + i + '">' + val + '</span>';
			}
			
			if (null == regionIds) {
				regionIds = i;
			} else {
				regionIds = regionIds + ',' + i;
			}
			
		});
	}
	
	var servicesHTML = "";
	if (data.services) {
		$.each(data.services, function(i, val) {
			
			if ("" == servicesHTML) {
				servicesHTML = '<span id="service-' + i + '">' + val + '</span>';
			} else {
				servicesHTML += ', <span id="service-' + i + '">' + val + '</span>';
			}
			
			if (null == serviceIds) {
				serviceIds = i;
			} else {
				serviceIds = serviceIds + ',' + i;
			}
			
		});
	}

	$('#header-selector').html("");
	var templateData = $.tmpl(templateSearchFormBlocks, 
		{ Regions : regionsHTML, Services: servicesHTML }
	).appendTo('#header-selector');
	
	// and now we can call for search results
	loadSearchResults(serviceIds, regionIds, currentPage);
}

//loads data from the backend
function loadSearchResults(serviceIds, regionIds, page) {
	
	if (null == page) {
		page = 1;
	}
	
	var url = '/' + locale +'/search/results/get/service/' + encodeURIComponent(serviceIds) + '/region/' + encodeURIComponent(regionIds) + '/page/' + page;
	loadSearchResultsByUrl(url);
}

//load data from specified URL
function loadSearchResultsByUrl(url) {
	$('#main').html('<div id="data-loading"><img src="/images/ajax-data-loader-progress.gif" /></div>');
	
	$('#main').load(url, function() {
		
		highlightResults();
		
		// lets bind AJAX calls to new pagenation links that was just loaded
		$('.searchPaginationControl a').click(function(){
			// update hash
			var keywords = $('#header-search-form-element').attr('value');
			currentPage = parseInt($(this).attr('title'));
			location.hash = '#q=' + encodeURIComponent(keywords) + '&page=' + currentPage;
			// load new data
			loadSearchResultsByUrl($(this).attr('href'));
			return false;
		});
	});
	
}

// lets highlight search results
function highlightResults()
{
	var searchQuery = $('#header-search-form-element').attr('value');
	var keywords = searchQuery.split(' '); 
	
	// lets hilight search query
	for (var i in keywords) {
		
		if (keywords[i].length > MIN_SEARCH_LENGHT) {
			$(".search-results-item").highlight(keywords[i]);
		}

	}
}