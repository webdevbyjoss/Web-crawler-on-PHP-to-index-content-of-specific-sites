/**
 * Holds the functionality related to "specialists" page
 */
// cache regions and cities data retrieved via ajax 
// to eliminate the amount of calls to server
// TODO: if browser supports client side storage then use that storage 
//       to eliminate the amount of AJAX calls to the server
var citiesCache = Array();

// init search form
$(document).ready(function () {
	
	// place focus in search field
	$('#services-finder-select-control').focus();
	
	// bind change action for region select control
	$('#city-finder-regions').change(function(){
		
		var regionId = $(this).val();
		
		// if region was selected then show cities selection control for that region 
		if (regionId > 0) {
			// show city selectbox
			loadCities(regionId);
			
		} else {
			// hide city selectbox
			$('#city-finder-city').addClass('hidden-element');
		}
		
	});
	
	
	$('#city-finder-city').change(function(){

		var cityUrl = $(this).val();
		
		if (cityUrl != "") {
			$('#city-finder-city').after('<img src="/images/ajax-loader-blue.gif" />');
			location.href = cityUrl;
		}

	});
});





//this will fill the cities selectbox with the available entries
//if entries are available locally we will use that data otherwise 
//the AJAX call will be made and data will be cached
function loadCities(regionId)
{
	elem = $('#city-finder-city');
		
	// get data from the cache if available
	if (citiesCache[regionId]) {
		elem.removeClass('hidden-element');
		elem.html(citiesCache[regionId]);
	} else {
		elem.addClass('hidden-element');
		elem.load('/' + locale + '/searchdata/list/cities/regionid/' + regionId, null, function(){
			$('#loading-progress').remove();
			elem.removeClass('hidden-element');
			citiesCache[regionId] = $(this).html();
		});
		$('#city-finder-regions').after('<img id="loading-progress" src="/images/ajax-loader-blue.gif" />');
	}
}