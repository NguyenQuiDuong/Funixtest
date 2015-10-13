$(function(){
	$('#createdByName').on('change', function(){
		if(!$('#createdByName').val()){
			$('#createdById').val('');
		}
	});
	$('#createdByName').autocomplete({
		source: function(request, response){
			$.post(
				'/system/user/suggest',
				{
					'q' : request.term,
					'companyId': $('#companyId').val()
				},
				function(rs){
					if(rs.code){
						response(rs.data);
					}
				}
			);
		},
		minLength: 2,
		select: function(event, ui){
			$('#createdById').val(ui.item.id);
		},
	});
});