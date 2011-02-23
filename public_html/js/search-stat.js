/**
 * Collect search statistics to improve search results
 */

// init search form
$(document).ready(function () {

	// add statistics tracking functionality for search results
	$('.search-results-item a').mousedown(function () {
		$.ajax({
			   type: "GET",
			   url: "/search/results/stat/data/" + encodeURIComponent(encodeURIComponent( $(this).attr('href') )),
			   async: true,
			   cache: false
		});
	});

});