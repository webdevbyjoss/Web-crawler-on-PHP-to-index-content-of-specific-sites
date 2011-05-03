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

	$('#selector-service').hide();
	$('#selector-description').hide();
	
	// bind change action for region select control
	$('#city-finder-regions').focus().change(function(){
		
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

		var cityId = $(this).val();
		
		if (cityId != "") {
			loadCityInfo(cityId);
			return false;
			// location.href = cityUrl;
		}

	});

	initWizardNavigation();
	
});


function initWizardNavigation()
{
	
	$('.step-city').unbind().click(function(){
		goToCity();
		return false;
	});
	
	$('.step-services').unbind().click(function(){
		goToServices();
		return false;
	});
	
	$('.step-description').unbind().click(function(){
		goToDescription();
		return false;
	});
	
	$('.step-submit').unbind().click(function(){
		
		if (validateForm() != true) {
			return false;
		}
		
		$(this).after('<img id="loading-progress" src="/images/ajax-loader.gif" />');

		$.post('/' + locale + '/clads/new/add', $("#add-ad-form").serialize(), function(data) {
			/*
			$("#selector-city").hide();
			$("#city-selected").hide();
			$("#selector-service").hide();
			$("#selector-description").hide();
			*/
			// redirect TODO: add some message
			location.href = "/";
			return false;
		});
		
		return false;
	});
}

function goToCity() {
	$("#selector-service").hide();
	$("#selector-description").hide();
	
	if ($("#city-selected").html().length > 10) {
		$("#city-selected").show();
	} else {
		$("#selector-city").show();
	}
}

function goToServices() {
	$("#selector-city").hide();
	$("#city-selected").hide();
	$("#selector-description").hide();
	$("#selector-service").show();
}

function goToDescription() {
	$("#selector-city").hide();
	$("#city-selected").hide();
	$("#selector-service").hide();
	$("#selector-description").show();
}

function validateForm() {
	
	// check if city is selected
	if ($("#city-selected").html().length < 10) {
		goToCity();
		alert('Виберіть місто!');
		return false;
	}
	
	// check if at least 1 service in selected
	var fields = $(".services-list-items input:checked").size();
	if (fields == 0) {
		goToServices();
		alert('Виберіть хоча б один вид послуг!');
		return false;
	} 
	
	// validate phone number
	var phone = $('#phone').attr('value');
	if (phone == "+380") {
		goToDescription();
		alert('Введіть номер телефону!')
		return false;
	}
	
	// validate name
	if ($('#name').attr('value') == "") {
		goToDescription();
		alert('Введіть ім’я!');
		return false;
	}
	
	return true;
}


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
		elem.load('/' + locale + '/searchdata/list/idcities/regionid/' + regionId, null, function(){
			$('#loading-progress').remove();
			elem.removeClass('hidden-element');
			citiesCache[regionId] = $(this).html();
		});
		$('#city-finder-regions').after('<img id="loading-progress" src="/images/ajax-loader.gif" />');
	}
}


function loadCityInfo(cityId)
{
	$('#city-finder-city').after('<img id="loading-progress" src="/images/ajax-loader.gif" />');
	elem = $('#city-selected');
	elem.load('/' + locale + '/searchdata/list/relatedcities/cityid/' + cityId, null, function(){
		$("#city-selected").show();
		$('#loading-progress').remove();
		$("#selector-city").hide();
		
		$('#change-city-button').click(function () {
			$("#selector-city").show();
			$("#city-selected").hide();
		});
		
		initWizardNavigation();
		return false;
	});
}