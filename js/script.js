jQuery(function($){
	$('#accordion_tables .accordion_container').accordion({
		active: false,
		collapsible: true,
		heightStyle: "content",
		navigation: true,
		beforeActivate: function( event, ui ) {
			$('#accordion_tables .fa-minus-circle').each(function(){
				$(this).removeClass('fa-minus-circle').addClass('fa-plus-circle');
			});
			$(ui.newHeader.context).find('i.fa-plus-circle').removeClass('fa-plus-circle').addClass('fa-minus-circle');
			$('#accordion_tables .fa-angle-up').each(function(){
				$(this).removeClass('fa-angle-up').addClass('fa-angle-down');
			});
			$(ui.newHeader.context).find('i.fa-angle-down').removeClass('fa-angle-down').addClass('fa-angle-up');
		}

	});
});
