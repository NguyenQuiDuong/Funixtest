//---------------------------Preview----------------
function buildPreviewModal(){
	$("#preview").empty();
	$("#preview").html("<div class='modal-dialog modal-lg' style='width: 100%; height:0px; margin:0px; padding-top:5px;'><div class='modal-content' style='width: 100%;'><div class='modal-header'><button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button><h4 class='modal-title'>Xem trước</h4></div><div class='modal-body'></div></div></div>");
}

	$.fn.previewModal = function(){
		this.each(function(){
			 var d = window.location.origin + $(this).attr("href");
			 var f = $(this).attr("data-file");
		     var e = d.substring(d.lastIndexOf(".") + 1);
			 if (/^(xlsx|ppt|pps|doc|docx|pptx|jpg|png|gif|pdf|jpeg)$/i.test(e)) {
					 $(this).prepend('<i style="color:#E80909"  class="fa fa-eye"></i>');
			 }else{
				 $(this).css('padding-left','17px')
			 }
		$(this).click(function(){
			 if (/^(xlsx|ppt|pps|doc|docx|pptx)$/.test(e)) {
		         $(this).after(function() {
		            $("#preview").modal('show');
		         	$('#preview .modal-body').empty();
		         	$('#preview .modal-footer').empty();
		         	$('#preview .modal-body').append('<iframe src="https://view.officeapps.live.com/op/view.aspx?src=' + encodeURIComponent(d) + '" width=100%" height="550px" style="border: none;"></iframe>');
		         	$('#preview .modal-footer').append("<button type='button' class='btn btn-default' data-dismiss='modal'>Đóng</button>");
		         });
		     }else if (/^(jpg|png|jpeg|gif|pdf)$/i.test(e)){
		     	window.open(d);
		     }else{
		    	 window.open(f);
		     }
		})
		});
		  
	}


