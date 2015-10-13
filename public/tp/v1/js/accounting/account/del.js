$(document).ready(function(){

	$('.del_Item').click(deleteIItem);	
	function deleteIItem() {
	    var val = $(this).attr('value');

	    if (val != "") {
	    	var html = '<div class="deleteId" value="' + val + '"></div>';
	        $("#delModal").modal('show');
	        $('.modal-body').append(html);
	    }
	}
	
	$('#delModal .modal-footer').on('click', '.confirm', deleteConfirm);
	function deleteConfirm() {
		
	    var id = $('.deleteId').attr('value');

	    $.get(
	        "/accounting/account/delete",
	        {
	            id: id
	        },
	        function (rs) {
	        	var html = '<div class="alert alert-success alert-dismissable">' + rs.messages + '</div>';
	            if (rs.code > 0) {	            	
	            	$("#delModal").modal('hide');
	            	$("#alertModal").modal('show');
	                $('#alertModal .modal-body').empty();
	                $('#alertModal .modal-body').append(html);
	               
	            } else {
	            	html = '<div class="alert alert-warning alert-dismissable">' + rs.messages + '</div>';
	            	$("#delModal").modal('hide');
	            	$("#alertModal").modal('show');
	                $('#alertModal .modal-body').empty();
	                $('#alertModal .modal-body').append(html);
	            }
	        }
	    )
	}
	$('.modal-footer').on('click', '.reload', function() {
	    window.location.reload();
	});
});