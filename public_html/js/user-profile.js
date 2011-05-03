var isEditing = false;

// init search form
$(document).ready(function () {
	$('.editable').click(function () {
		
		if (isEditing) {
			return false;
		}
		isEditing = true;
		
		var itemText = $(this).text();
		$(this).html('<input id="inline-editor" value="' + itemText + '" />');
		$('#inline-editor').focus().blur(function(){
			var itemText = $(this).attr('value');
			var parentElem = $(this).parent();
			parentElem.after('<img id="loading-progress" src="/images/ajax-loader.gif" />');
			var elemId = parentElem.attr('id');
			parentElem.html(itemText);
			
			$.post('/' + locale + '/clads/update/info', {elem:elemId,text:itemText}, function(data) {
				$('#loading-progress').remove();
				isEditing = false;
			});

		});
	});
	
	$('.editable-area').click(function () {
		
		if (isEditing) {
			return false;
		}
		isEditing = true;
		
		var itemText = $(this).text();
		$(this).html('<textarea id="inline-editor">' + itemText + '</textarea>');
		
		$('#inline-editor').focus().blur(function(){
			var itemText = $(this).val();
			var parentElem = $(this).parent();
			parentElem.after('<img id="loading-progress" src="/images/ajax-loader.gif" />');
			var elemId = parentElem.attr('id');
			parentElem.html(itemText);

			$.post('/' + locale + '/clads/update/info', {elem:elemId,text:itemText}, function(data) {
				$('#loading-progress').remove();
				isEditing = false;
			});
		});
	});
});