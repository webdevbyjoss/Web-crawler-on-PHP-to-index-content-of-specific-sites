$(document).ready(function () {

	/**
	 * Optimize layout for small screens
	 */
	$(window).resize(function() {

		if (700 > $(window).width()) {
			$('#header-search-form').removeClass();
			$('#header-search-form').addClass('header-search-form-narrow');
		} else {
			$('#header-search-form').removeClass();
			$('#header-search-form').addClass('header-search-form-wide');
		}

	});

	$(window).trigger('resize');

	/**
	 * prepare search form 
	 */
	$('#header-search-form-element').focus();

});