$(function () {

    $('#userName').autocomplete({
        source: function (request, response) {
            $.post(
                '/system/user/suggest',
                {
                    'q': request.term,
                },
                function (rs) {
                    if (rs.code) {
                        response(rs.data);
                        console.log(rs.data);
                    } else {
                        response([]);
                    }
                }
            );
        },
        minLength: 2,
        select: function (event, ui) {
            $('#userId').val(ui.item.id);
        }
    });
    //
    //$('#userName').on('change', function () {
    //    if (!$('#userName').val()) {
    //        $('#userId').val('');
    //    }
    //});
    ////-------an hiện chỗ chọn có tài khoản hệ thống, với tạo mới tài khoản hệ thống
    //if($('input[name=selectUser]:checked').val() == 1){
		//$('fieldset:last').css('display','none');
    //}
    //if($('input[name=selectUser]:checked').val() == 2){
		//$('.col-md-6:eq(3)').css('display','none');
    //}
    //$('input[name=selectUser').click(function(){
    //	if($('input[name=selectUser]:checked').val() == 1){
    //		$('.col-md-6:eq(3)').fadeIn('slow');
    //		$('fieldset:last').fadeOut('slow');
    //	}
    //	if($('input[name=selectUser]:checked').val() == 2){
    //		$('fieldset:last').fadeIn('slow');
    //		$('.col-md-6:eq(3)').fadeOut('slow');
    //	}
    //});

    $('#subject').autocomplete({
        source: function(request, response){
            $.post(
                '/subject/subject/suggest',
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
            event.preventDefault();
            $('#subject').val('');
            addAttendanceTag(ui.item);

        },
    });

    $('.bootstrap-tagsinput').on('click', '.removeTag', function(){
        $(this).parent().remove();
        refillAttendanceId()
    })

    function addAttendanceTag(item){
        text = '<span class="tag label label-info">' +
            '<input type="hidden" class="subjectIdtag" value="' + item.id +'"/>' +
            item.name +
            '<span data-role="remove" class="removeTag"></span></span>';
        $('#subject').parent().prepend(text);
        refillAttendanceId()
    };

    function refillAttendanceId(){
        var attendanceIds = [];
        $('#subject').parent().find('.subjectIdtag').each(function(){
            attendanceIds.push($(this).val());
        })
        $('#subjectId').val(attendanceIds.join(','))
    };
});

