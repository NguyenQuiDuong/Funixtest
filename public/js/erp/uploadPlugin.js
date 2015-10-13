(function ( $ ) {
	$.fn.uploadPlugin = function(customOptions) {
		console.log('aaaa');
		var previewTemplate = '<div>' +
	       '<span class="preview"><img data-dz-thumbnail /></span>' +
	        '</div>' +
	        '<div>' +
	            '<p class="name" data-dz-name></p>' +
	            '<strong class="error text-danger" data-dz-errormessage></strong>' +
	        '</div>' +
	        '<div>' +
	            '<p class="size" data-dz-size></p>' +
	            '<div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">' +
	              '<div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress></div>' +
	            '</div>' +
	        '</div>' +
	        '<div>' +
	          '<button class="btn btn-primary start">' +
	              '<i class="glyphicon glyphicon-upload"></i>' +
	              '<span>Start</span>' +
	          '</button>' +
	          '<button data-dz-remove class="btn btn-warning cancel">' +
	              '<i class="glyphicon glyphicon-ban-circle"></i>' +
	              '<span>Cancel</span>' +
	          '</button>' +
	          '<button data-dz-remove class="btn btn-danger delete">' +
	            '<i class="glyphicon glyphicon-trash"></i>' +
	            '<span>Delete</span>' +
	          '</button>' +
	        '</div>';
		var defaultOptions = { // Make the whole body a dropzone
				paramName: 'fileUpload',
				uploadMultiple: false,
				acceptedFiles: 'image/*',
				maxFiles: 20,
				maxFilesize: 4,
				thumbnailWidth: 80,
				thumbnailHeight: 80,
				parallelUploads: 20,
				previewTemplate: previewTemplate,
				autoQueue: false, // Make sure the files aren't queued until manually added
				previewsContainer: '#preview', // Define the container to display the previews
				clickable: ".fileinput-button" // Define the element that should be used as click trigger to select files.
			};
		console.log(defaultOptions);
		var uploadOptions = $.extend(defaultOptions, customOptions);
		
		var myDropzone = new Dropzone('#dropzone', uploadOptions);
		
		myDropzone.on("addedfile", function(file) {
		  // Hookup the start button
			file.previewElement.querySelector(".start").onclick = function() { myDropzone.enqueueFile(file); };
		});
		 
		// Update the total progress bar
		myDropzone.on("totaluploadprogress", function(progress) {
			document.querySelector("#total-progress .progress-bar").style.width = progress + "%";
		});
		 
		myDropzone.on("sending", function(file) {
		  // Show the total progress bar when upload starts
			document.querySelector("#total-progress").style.opacity = "1";
		  // And disable the start button
			file.previewElement.querySelector(".start").setAttribute("disabled", "disabled");
		});
		 
		// Hide the total progress bar when nothing's uploading anymore
		myDropzone.on("queuecomplete", function(progress) {
			document.querySelector("#total-progress").style.opacity = "0";
		});
		 
		// Setup the buttons for all transfers
		// The "add files" button doesn't need to be setup because the config
		// `clickable` has already been specified.
		document.querySelector("#actions .start").onclick = function() {
			myDropzone.enqueueFiles(myDropzone.getFilesWithStatus(Dropzone.ADDED));
		};
		document.querySelector("#actions .cancel").onclick = function() {
			myDropzone.removeAllFiles(true);
		};
		return this;
	}
}( jQuery ));