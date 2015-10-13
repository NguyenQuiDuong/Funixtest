$(function () {
  function setModalSubmit(lesson_id, url, is_new, from_mentor) {
    lesson_review.lesson_id = lesson_id, lesson_review.url = url, lesson_review.is_new = is_new, lesson_review.from_mentor = from_mentor
  }

  function showAddOfflineHelpFeedbackModal(offlineHelpId) {
    $("#modal-content").find(".modal-body").scrollTop(0), $("#submit-offline-help-feedback").attr("data-offline-help", offlineHelpId), $("#submit-offline-help-feedback").attr("disabled", !1), $("#submit-offline-help-feedback").html("Submit"), $("#modal-offline-help-feedback input[type='radio']").prop("checked", !1), $("#modal-offline-help-feedback textarea").val(""), $("#modal-offline-help-feedback").modal("show")
  }

  function prepareOfflineHelpFeedbackSubmit(form_id, $submitBtn) {
    var url, obj = $(form_id).serializeObject(), offlineHelpId = $submitBtn.attr("data-offline-help"), from_mentor = $submitBtn.attr("data-from-mentor");
    return url = "true" == from_mentor ? "/api/offline-helps/" + offlineHelpId.toString() + "/mentor_feedback" : "/api/offline-helps/" + offlineHelpId.toString() + "/feedback", $submitBtn.attr("disabled", !0), $submitBtn.html("Processing..."), {
      obj: obj,
      url: url,
      offlineHelpId: offlineHelpId
    }
  }

  function updateOfflineHelpFeedback(offlineHelpId, review) {
    var $rating, $offlineHelp = $("#offline-help-" + offlineHelpId), $review = $offlineHelp.find(".review-box .review"), $ratingBox = $offlineHelp.find(".rating-box");
    if (review.rating)for (0 == $ratingBox.find(".rating").length && ($ratingBox.find(".no-rating").remove(), $ratingBox.append("<strong></strong>"), $ratingBox.append('<div class="rating"></div>')), $rating = $ratingBox.find(".rating"), $rating.html(""), i = 0; i < 5; i++)i < parseInt(review.rating) ? $rating.append("<span> \u2605 </span>") : $rating.append('<span class="empty"> \u2605 </span>');
    review.content && ($review.text(review.content), $review.html($review.html().replace(/\r\n/g, "<br />")))
  }

  $("#what_is_profile_completeness").popover({
    toggle: "popover",
    title: "Mentor Profile Completeness",
    content: "Please provide more details about each of your expertise.  All fields need to be filled to reach 100% mentor profile completeness.",
    trigger: "click",
    placement: "right",
    container: "small.profile_completeness_popover"
  }), $("#datepicker").datepicker({format: "yyyy-mm-dd"}), $(document).on("click", "a.more", function () {
    return $(this).parents(".truncated-body").hide(), $(this).parents(".truncated-body").siblings(".full-body").show(), !1
  }), $(document).on("click", "a.less", function () {
    return $(this).parents(".full-body").hide(), $(this).parents(".full-body").siblings(".truncated-body").show(), !1
  }), $(document).on("click", "a.more-comment", function () {
    return $(this).parents(".truncated-comment").hide(), $(this).parents(".truncated-comment").siblings(".full-comment").show(), !1
  }), $(document).on("click", "a.less-comment", function () {
    return $(this).parents(".full-comment").hide(), $(this).parents(".full-comment").siblings(".truncated-comment").show(), !1
  }), $(document).on("click", ".btnDecline", function () {
    return $("#decline_schedule").attr("action", "/schedules/" + $(this).data("schedule") + "/decline"), $(".userName").text($(this).data("user-name")), $("#modalScheduleDecline").modal(), !1
  });
  var _send_form = function (form, btn, e) {
    e.preventDefault();
    var $form = $(form), submit_btn = $form.find(btn);
    return submit_btn.data("clicked") ? !1 : $form.parsley("validate") ? (submit_btn.data("clicked", !0), void $form.submit()) : (submit_btn.data("clicked", !1), !1)
  };
  $("#btnDeclineSubmit").click(function (e) {
    _send_form("#decline_schedule", "#btnDeclineSubmit", e)
  }), $("#btnConfirmSubmit").click(function (e) {
    _send_form("#confirm_schedule", "#btnConfirmSubmit", e)
  }), $("#btnRescheduleSubmit").click(function (e) {
    _send_form("#reschedule", "#btnRescheduleSubmit", e)
  }), $("#btnMessageSubmit").click(function (e) {
    _send_form("#message_box", "#btnMessageSubmit", e)
  }), $(document).on("click", ".btnReschedule", function (e) {
    $("#reschedule").attr("action", "/schedules/" + $(this).data("schedule") + "/reschedule"), $(".userName").text($(this).data("user-name"));
    var current_user_timezone = $(this).data("currentuser-timezone"), other_user_timezone = $(this).data("user-timezone"), current_user_timezone_offset = 1e3 * current_user_timezone, other_user_timezone_offset = 1e3 * other_user_timezone;
    return $("#modalReschedule").on("show", function () {
      $rootScope = angular.element("#modalReschedule").scope().$root, $rootScope.$broadcast("timezoneInit", {
        current_user_timezone_offset: current_user_timezone_offset,
        other_user_timezone_offset: other_user_timezone_offset
      }), $rootScope.$on("timeChanged", function () {
        $("#modalReschedule").find("#btnRescheduleSubmitDisabled").hide(), $("#modalReschedule").find("#btnRescheduleSubmit").show()
      }), $rootScope.$on("timeChangedToPast", function () {
        $("#modalReschedule").find("#btnRescheduleSubmitDisabled").show(), $("#modalReschedule").find("#btnRescheduleSubmit").hide()
      })
    }), $("#modalReschedule").modal(), !1
  }), $(document).on("click", ".btnConfirm", function () {
    return $("#confirm_schedule").attr("action", "/schedules/" + $(this).data("schedule") + "/confirm"), $(".userName").text($(this).data("user-name")), $("#modalConfirm").modal(), !1
  }), $(document).on("click", ".btnMessage", function () {
    var $btn = $(this);
    return "block" != $btn.siblings(".question_messages").css("display") && $btn.siblings(".question_messages").toggle(), $btn.siblings(".question_messages").find("textarea").focus(), !1
  }), $(document).on("click", ".goQuestionPage", function (e) {
    return confirm("This schedule will be canceled if you end up scheduling with another mentor.") ? void 0 : (e.preventDefault(), !1)
  }), $(document).on("click", ".doCancel", function (e) {
    if ($(this).data("clicked"))return !1;
    var msg = $(this).data("msg") || "The question will be canceled. Are you sure?";
    return confirm(msg) ? (console.log("1"), void $(this).data("clicked", !0)) : (console.log("2"), $(this).data("clicked", !1), e.preventDefault(), e.stopImmediatePropagation(), !1)
  }), $(document).on("click", ".doCancelSchedule", function (e) {
    return $("#decline_schedule").attr("action", "/schedules/" + $(this).data("schedule") + "/cancel"), $(".userName").text($(this).data("user-name")), $("#decline_schedule textarea").attr("name", "cancel_reason"), $("#modalScheduleDecline").modal(), !1
  }), $(document).on("click", ".show_message_set", function () {
    var $btn = $(this);
    return $btn.toggleClass("show"), $btn.siblings(".question_messages").toggle(), !1
  }), $(".promo-message a").click(function () {
    mixpanel.track("/user/click/referral_url")
  }), $("[data-toggle=tooltip]").tooltip("hide");
  var lesson_review = {lesson_id: "", url: "", is_new: !1, from_mentor: !1};
  $(document).on("click", ".edit-feedback", function () {
    var url, lesson_id = $(this).data("lesson"), from_mentor = $(this).data("from_mentor"), is_new = $(this).data("is_new");
    url = 1 == from_mentor ? "/api/lessons/" + lesson_id.toString() + "/mentor_feedback" : "/api/lessons/" + lesson_id.toString() + "/feedback", setModalSubmit(lesson_id, url, is_new, from_mentor), $("#submit_edit_feedback").attr("disabled", !1), $("#submit_edit_feedback").html("Submit"), 1 == from_mentor ? $.get(url).done(function (lesson) {
      lesson.review_from_mentor = lesson.review_from_mentor || {content: ""}, $("#modal-edit-feedback input#lessons_rating_" + lesson.review_from_mentor.rating).prop("checked", !0), $("#modal-edit-feedback textarea#review").val(lesson.review_from_mentor.content), $("#modal-edit-feedback").modal("show")
    }) : $.get(url).done(function (lesson) {
      lesson.review = lesson.review || {content: ""}, $("#modal-edit-feedback input#lessons_rating_" + lesson.review.rating).prop("checked", !0), $("#modal-edit-feedback textarea#review").val(lesson.review.content), $("#modal-edit-feedback").modal("show")
    })
  }), $("#submit_edit_feedback").on("click", function () {
    var $submit_btn = $(this), formType = lesson_review.is_new ? "POST" : "PUT";
    $submit_btn.attr("disabled", !0), $submit_btn.html("Processing..."), $.ajax({
      url: lesson_review.url,
      type: formType,
      data: $("#edit_feedback_form").serializeObject()
    }).done(function (data) {
      var $comment = $('<div class="full-comment"></div>'), $commentBox = $("li[question-panel=" + lesson_review.lesson_id + "] div.review-box"), $ratingBox = $("li[question-panel=" + lesson_review.lesson_id + "] div.rating-box"), $lessonBlock = $("li[question-panel=" + lesson_review.lesson_id + "] div.confirm");
      lesson_review.from_mentor ? $comment.text(data.review_from_mentor.content) : $comment.text(data.review.content), $comment.html($comment.html().replace(/\r\n/g, "<br />")), $commentBox.html("<h6>Your Review</h6>"), $commentBox.append($comment), lesson_review.from_mentor ? $ratingBox.find("strong").text(parseInt(data.review_from_mentor.rating)) : $ratingBox.find("strong").text(parseInt(data.review.rating)), $ratingBox.find("div.rating").html("");
      var rating = lesson_review.from_mentor ? data.review_from_mentor.rating : data.review.rating;
      for (i = 0; i < 5; i++)i < parseInt(rating) ? $ratingBox.find("div.rating").append("<span> \u2605 </span> ") : $ratingBox.find("div.rating").append('<span class="empty"> \u2605 </span> ');
      $("#modal-edit-feedback").modal("hide"), $lessonBlock.find(".edit-feedback").data("is_new", !1), $lessonBlock.find(".edit-feedback").html("Edit Review")
    }.bind(this))
  });
  if (gon.open_add_offline_help_feedback_modal) {
    var offlineHelpId = gon.open_add_offline_help_feedback_modal;
    showAddOfflineHelpFeedbackModal(offlineHelpId)
  }
  $(document).on("click", ".add-offline-help-feedback", function () {
    var offlineHelpId = $(this).attr("data-offline-help"), from_mentor = $(this).attr("data-from_mentor");
    $("#submit-offline-help-feedback").attr("data-from-mentor", from_mentor), showAddOfflineHelpFeedbackModal(offlineHelpId)
  }), $(document).on("click", ".edit-offline-help-feedback", function () {
    var url, offlineHelpId = $(this).attr("data-offline-help"), from_mentor = $(this).attr("data-from_mentor");
    url = "true" == from_mentor ? "/api/offline-helps/" + offlineHelpId + "/mentor_feedback" : "/api/offline-helps/" + offlineHelpId + "/feedback", $("#submit-edit-offline-help-feedback").attr("data-offline-help", offlineHelpId), $("#submit-edit-offline-help-feedback").attr("disabled", !1), $("#submit-edit-offline-help-feedback").attr("data-from-mentor", from_mentor), $("#submit-edit-offline-help-feedback").html("Submit"), $.get(url).done(function (review) {
      $("#modal-edit-offline-help-feedback input#offline_help_rating_" + review.rating).prop("checked", !0), $("#modal-edit-offline-help-feedback textarea#review").val(review.content), $("#modal-edit-offline-help-feedback").modal("show")
    })
  }), $("#submit-offline-help-feedback").on("click", function () {
    var $submitBtn = $(this), modalData = prepareOfflineHelpFeedbackSubmit("#offline-help-feedback-form", $submitBtn);
    $.post(modalData.url, modalData.obj).done(function (review) {
      var offlineHelpId = $submitBtn.attr("data-offline-help"), $offlineHelp = $("#offline-help-" + offlineHelpId), $addFeedbackBtn = $offlineHelp.find(".add-offline-help-feedback"), $editFeedbackBtn = $offlineHelp.find(".edit-offline-help-feedback");
      $addFeedbackBtn.hide(), $editFeedbackBtn.show(), updateOfflineHelpFeedback(offlineHelpId, review), $("#modal-offline-help-feedback").modal("hide")
    }.bind(this))
  }), $("#submit-edit-offline-help-feedback").on("click", function () {
    var $submitBtn = $(this), modalData = prepareOfflineHelpFeedbackSubmit("#edit-offline-help-feedback-form", $submitBtn);
    $.ajax({url: modalData.url, type: "PUT", data: modalData.obj}).done(function (review) {
      var offlineHelpId = $submitBtn.attr("data-offline-help");
      updateOfflineHelpFeedback(offlineHelpId, review), $("#modal-edit-offline-help-feedback").modal("hide")
    }.bind(this))
  })
});
