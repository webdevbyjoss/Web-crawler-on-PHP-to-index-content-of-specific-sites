// cache regions and cities data retrieved via ajax 
// to eliminate the amount of calls to server
// TODO: if browser supports client side storage then use that storage 
//       to eliminate the amount of AJAX calls to the server
var regionsCache = Array();
var citiesCache = Array();

$(document).ready(function () {
	
	// load services & regions containers via AJAX
	$('#selector-service-bar').load('/searchdata/index/services');
	$('#selector-region-bar').load('/searchdata/index/regiones');
	
	// bind click event to display services selection box
	// this will show the services selection box that will 
	// allow for user to select from available services
	$("#selector-service-container").click(function() {
		
		if ($(this).hasClass('selector-service-opened')) {
			closeServicesBox();
		} else {
			openServicesBox();
		}
		
		return false;
	});

	// this will show the region container that will allow user 
	// to select regions for to search for items
	$("#selector-region-container").click(function() {
		
		if ($(this).hasClass('selector-region-opened')) {
			closeRegionBox();
		} else {
			openRegionBox();
		}
		
		return false;
	});
	
});


function openRegionBox()
{
	$("#selector-region-container").text('клик для сохранения изменений');
	$("#selector-region-container").addClass('selector-region-opened');
	closeServicesBox();
	
	$('#selector-region-control-country').unbind('change').change(function() {
		loadRegions($(this).val());
		$('#selector-region-control-button').attr('disabled', true);
	});
	
	$('#selector-region-control-region').unbind('change').change(function() {
		loadCities($(this).val());
		$('#selector-region-control-button').attr('disabled', true);
	});
	
	$('#selector-region-control-city').unbind('change').change(function(){

		if ($(this).val() > 0) {
			enableRegionAddButton();
		} else {
			$('#selector-region-control-button').attr('disabled', true);
		}
		
	});
	
	$('#selector-region-bar').show();
	$('#selector-region-control-button').attr('disabled', true);
	
	// bind adding event that will collect all selected information
	// and add new item to selected list
	$('#selector-region-control-button').unbind('click').click(function(){
		
		$(this).attr('disabled', true);
		
		countryElem = $('#selector-region-control-country').find('option:selected');
		country = countryElem.text();
		country_id = countryElem.attr('value');
		
		regionElem = $('#selector-region-control-region').find('option:selected');
		region = regionElem.text();
		region_id = regionElem.attr('value');
		
		cityElem = $('#selector-region-control-city').find('option:selected');
		city = cityElem.text();
		city_id = cityElem.attr('value');
		
		selectedBox = $('#selector-region-bar-selected');
		htmlData = selectedBox.html();
		
		if (country == region) {
			regionData = '';
		} else {
			regionData = region + ', ';
		}
		
		newItem = '<a href="#" id="city-' + city_id + '" class="selector-region-bar-selected-item"> '
            + '<span id="remove-city-' + city_id + '" class="selector-region-bar-selected-remove">удалить</span> '
            + '<span class="selector-region-bar-selected-city">' + city + ' </span> '
            + '<span class="selector-region-bar-selected-region"> ' + regionData + country + '</span> '
            + '</a>';
		
		selectedBox.html(htmlData + newItem);
		
		cityElem.remove();
		$('#selector-region-control-city').val(0);
		
		// we need to assign remove action processing 
		// to allow user to remove selected cities one by one
		// after item will be removed we need to reload 
		// the list of available cities from the server
		// to allow user to select the appropriate city one more time
		$('.selector-region-bar-selected-item').unbind('click').click(function(){
			$(this).remove();
			loadCities($('#selector-region-control-region').find('option:selected').attr('value'));
		});
		
		return false;
	});
	
}


// if user successfully selected the country, region and city
// the we need to enable the "add" button
function enableRegionAddButton()
{
	$('#selector-region-control-button').attr('disabled', false);
}

function closeRegionBox()
{
	$("#selector-region-container").text('клик для выбора региона');
	$("#selector-region-container").removeClass('selector-region-opened');
	
	$('#selector-region-bar').hide();
}


function closeServicesBox()
{
	$("#selector-service-container").text('клик для выбора вида услуг');
	$("#selector-service-container").removeClass('selector-service-opened');
	
	// show the regions box
	$('#selector-service-bar').hide();
}


// this functon will show the services box
// and bind all event handlers
function openServicesBox()
{
	$("#selector-service-container").text('клик для сохранения изменений');
	$("#selector-service-container").addClass('selector-service-opened');
	closeRegionBox();
	
	// bind services switch click event
	// if user clicks this link then detailed subitems will be showed up
	// and then after second click they will be hidden
	$('.selector-service-switch').unbind('click').click(function() {
		
		elem = $(this).parent().parent().find('.selector-service-details');
		
		// on open details event we will open the detailed block
		// and remove hilighting class from the possible parent block 
		// tо allow user to move its attention to the selected subitems
		// TODO: move to user classes to determine the menu state instead of comparing text labels
		if (!$(this).hasClass('selector-service-switch-opened')) {
			
			elem.show();
			$(this).addClass('selector-service-switch-opened');
			$(this).text('спрятать детали');

		// hide details block. In case some subitems were selected then 
		// we hilighting the parent block with color to indicate that 
		// there are selected items inside
		// note that parent block checkbox will not me checked as 
		// as checking the checkbox will select all child checkboxes
		// and is equivalent to the "select all" command
		} else {
			
			elem.hide();
			$(this).removeClass('selector-service-switch-opened');
			$(this).text('показать детали');
			
			if (elem.find('input:checked').size() > 0) {
				$(this).parent().parent().find('.selector-service-item-element').addClass('service-active');
			} else {
				inpBox = $(this).parent().parent().find('.selector-service-item-element > input');
				if (inpBox.attr('checked') == false) {
					$(this).parent().parent().find('.selector-service-item-element').removeClass('service-active');
				}
			}
			
		}
		
		return false;
	});
	
	
	
	// bind selection event
	// if user clicks on CHECKBOX or LABEL to select the checkbox then we need to 
	// select the active item and hilight it with color
	$('.selector-service-item-element input, .selector-service-item-element label, .selector-service-subitem input, .selector-service-subitem label').unbind('click').click(function() {
		
		elem = $(this).parent().find('input');
		
		if (elem.attr('checked') == true) {
			elem.attr('checked', false);
			elem.parent().removeClass('service-active');
		} else {
			elem.attr('checked', true);
			elem.parent().addClass('service-active');
		}
		
	});
	
	
	
	// bind selection event
	// if user clicks on BLOCK to select the checkbox then we need to 
	// select the active item and hilight it with color
	$('.selector-service-item-element, .selector-service-subitem').unbind('click').click(function() {
		
		elem = $(this).find('input');
		
		if (elem.attr('checked') == true) {
			elem.attr('checked', false);
			$(this).removeClass('service-active');
		} else {
			elem.attr('checked', true);
			$(this).addClass('service-active');
		}
		
	});
	
	
	// show the regions box
	$('#selector-service-bar').show();
}


//this will fill the regions selectbox with the available entries
//if entries are available locally we will use that data otherwise 
//the AJAX call will be made and data will be cached
function loadRegions(countryId)
{
	elem = $('#selector-region-control-region');
	elem.attr('disabled', true);
	
	if (countryId == "0") {
		elem.html('<option value="0">[ выберите регион ]</option>');
	} else {
		
		// get data from the cache if available
		if (regionsCache[countryId]) {
			elem.html(regionsCache[countryId]);
			elem.attr('disabled', false);
		} else {
			elem.load('/searchdata/list/regiones/countryid/' + countryId, null, function(){
				regionsCache[countryId] = $(this).html();
				$(this).attr('disabled', false);
			});
		}
		
	}
	loadCities(0);
}

// this will fill the cities selectbox with the available entries
// if entries are available locally we will use that data otherwise 
// the AJAX call will be made and data will be cached
function loadCities(regionId)
{
	elem = $('#selector-region-control-city');
	elem.attr('disabled', true);
	
	if (regionId == "0") {
		elem.html('<option value="0">[ выберите город ]</option>');
	} else {
		
		// get data from the cache if available
		if (citiesCache[regionId]) {
			
			elem.html(citiesCache[regionId]);
			elem.attr('disabled', false);
			
			// we need to exclude selected items from the list
			// in order to disallow user to add same city twice
			excludeSelectedItems();
			
		} else {
			elem.load('/searchdata/list/cities/regionid/' + regionId, null, function(){
				
				citiesCache[regionId] = $(this).html();
				$(this).attr('disabled', false);
				
				// we need to exclude selected items from the list
				// in order to disallow user to add same city twice
				excludeSelectedItems();
				
			});
		}
	}
}


function excludeSelectedItems()
{
	$('.selector-region-bar-selected-item').each(function(){
		cityId = $(this).attr('id').substring(5);
		$('#selector-region-control-city').find('option[value="' + cityId + '"]').remove();
	});
}