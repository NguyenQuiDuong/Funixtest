$(document).ready(function() {
	
	$('#parentName').autocomplete({
		
		source: function(request, response){
			$.post(
				'/accounting/account/suggest',
				{
					'q' : request.term
				},
				function(rs){
					if(rs.code){
						response(rs.data);
					} else {
						response([]);
					}
				}
			);
		},
		minLength: 2,
		select: function(event, ui){
			$('#parentId').val(ui.item.id);
		},
	});

	$('.modal-footer').on('click', '.reload', function() {
		window.location.reload();
	});
});