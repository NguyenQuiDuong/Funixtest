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
        '<span class="ng-binding ng-scope subjectIdtag" value="'+item.id+'">'+item.name
        +
        '<span data-role="remove" class="removeTag remove-button ng-binding ng-scope" style="margin-left: 5px;">x</span></span>';
    $('#subject').parent().prepend(text);
    refillAttendanceId()
};

function refillAttendanceId(){
    var attendanceIds = [];
    $('#subject').parent().find('.subjectIdtag').each(function(){
        attendanceIds.push($(this).attr('value'));
    })
    $('#subjectId').val(attendanceIds.join(','))
};

$('.searchButton').click(function () {
    var uri = '/experts/index/view?id='+$('#subjectId').val();
    console.log(uri);
    location.replace(uri);
});