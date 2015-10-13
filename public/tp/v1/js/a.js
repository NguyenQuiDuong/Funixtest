var nfsReg = new RegExp('\\.','g');
$(function(){
	$('.intAutoNumeric').autoNumeric("init", {'mDec':0});

	// simplify url (disable element has no value: ?param1=abc&param2= => ?param1=abc)
	$('.filterContainer form').each(function() {
		$(this).submit(function(){
			var fArr=this.elements;
			for(var i=0; i<fArr.length; i++){
			    if(fArr[i].value===''){
			    	fArr[i].disabled=true;
			    }
			}
		});
	});
	$(".topMenuToggle").clickToggle(function() {
        $(".topMenu").slideDown(200);
    }, function() {
    	$(".topMenu").slideUp(200);
    });
	
	$('.datepicker').each(function(){
		  var option = { 
		       format: 'DD/MM/YYYY',
		       showDropdowns: true,
		       timePicker: false,
		       timePickerIncrement: 15,
		       opens:'left',
		       singleDatePicker: true
		       
		  };
		$(this).daterangepicker(option);
	});
	
	$('.datetimepicker').each(function(){
		  var option = { 
		       format: 'DD/MM/YYYY HH:mm:ss',
		       startDate: moment(),
		       endDate: moment(),
		       showDropdowns: true,
		       timePicker: true,
		       timePickerIncrement: 15,
		       opens:'left',
		       singleDatePicker: true
		  };
		$(this).daterangepicker(option);
	});
	$('input.date-range-picker').each(function(){
		var option = { 
			    format: 'DD/MM/YYYY',
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                 },
                 showDropdowns: true,
		};
		if($(this).attr('timePicker')){
			option['timePicker'] = true;
		} else {
			option['timePicker'] = false;
		}
		$(this).daterangepicker(option);
	});

	try {
		$('textarea.basicEditor').each(function(){
			$(this).ckeditor({
				language: usrCnf.lang,
				uiColor: '#6EA6D1',
				height: $(this).css('height') ? $(this).css('height') : '180px',
				extraPlugins: 'autolink',
				toolbar: [
					['FontSize','TextColor','BGColor','Bold','Italic','Underline','Link','Unlink','RemoveFormat','Maximize']
				]
			});
		});
		$('textarea.fullEditor').each(function(){
			$(this).ckeditor({
				language: usrCnf.lang,
				uiColor: '#f9f9f9',
				height: $(this).css('height') ? $(this).css('height') : '400px',
				extraPlugins: 'autolink'
//				filebrowserImageBrowseUrl : '/media/manage/uploadframe?'+getParamForFileBrowser(),
			});
		});
	} catch (err) {
		console.log(err);
	}

    //Border column in table
    $('th').click(function(){
        var elementClick = $('th').index(this) + 1;

        $(".focusColumn").removeClass("focusColumn");
        $( "tr td:nth-child(" + elementClick + ")" ).addClass("focusColumn");
        $( "tr th:nth-child(" + elementClick + ")" ).addClass("focusColumn");
        $( "tr:nth-last-child(1) td:nth-child(" + elementClick + ")").removeClass("focusColumn");
    });
	$('.combodate').combodate({
		  firstItem: 'name',
		  customClass: 'form-control form-control-inline'
	});

	
	$('.lolify .lolify-showextend').click(function(){
		$(this).closest('.lolify').find('.lolify-extend').toggle();
	});
	if($('.lolify-left-menu').children().length){
		$('.left-sidebar .quickLinkNav').html($('.lolify-left-menu').html());
		if($(".left-sidebar .quickLinkNav .main-menu .js-sub-menu-toggle").click(function(e) {
	        e.preventDefault(), $li = $(this).parents("li"), 
	        $li.hasClass("active") ? ($li.find(".toggle-icon").removeClass("fa-angle-down").addClass("fa-angle-left"), 
	        		$li.removeClass("active")) : ($li.find(".toggle-icon").removeClass("fa-angle-left").addClass("fa-angle-down"), 
	        				$li.addClass("active")), $li.find(".sub-menu").slideToggle(300)
	    })){
			// Không biết là js có thể viết như thế này đấy
		}
	}
	
	// catcomplete
	$.widget( "custom.catcomplete", $.ui.autocomplete, {
	    _create: function() {
	      this._super();
	      this.widget().menu( "option", "items", "> :not(.ui-autocomplete-category)" );
	    },
	    _renderMenu: function( ul, items ) {
	      var that = this,
	        currentCategory = "";
	      $.each( items, function( index, item ) {
	        var li;
	        if ( item.category != currentCategory ) {
	          ul.append( "<li class='ui-autocomplete-category'>-- " + item.category + " --</li>" );
	          currentCategory = item.category;
	        }
	        li = that._renderItemData( ul, item );
	        if ( item.category ) {
	          li.attr( "aria-label", item.category + " : " + item.label );
	        }
	      });
	    }
	  });
});
function addOverLay(){
	$("body").append("<div id='overlay'></div>");
}
function removeOverLay(){
	$('#overlay').remove();
}
function dump(obj) {
	if(typeof obj == "object") {
	    var out = '';
	    for (var i in obj) {
	        out += i + ": " + obj[i] + "\n";
	    }
	    return out;
	} else { return obj; }
}
function formatDecimal(n){
	n += '';
	if(!$.trim(n)){
		return '';
	}
	// /^\d+$/
    if(isInt(n)){
        if(/^-{0,1}\d*\.{0,1}\d+$/.test(n)){
            var result = '';
            while(n.length > 3){
                result = '.' + n.substr(n.length-3, 3) + result;
                n = n.substring(0, n.length-3);
            }
            return (n + result).replace('-' + '.', '-');
        } else {
            return '';
        }
    }else{
        return n;
    }
}
function isInt(i) {
	return /^\d+$/.test(i);
	//return (i.toString().search(/^-?[0-9]+$/) == 0);
}