/*
 * This form performs the heavy lifting behind rechecks.php
 *
 * It assumes the following elements:
 * 
 * #parse-comment-fullname-form with input #permalink and alert container #parse-comment-fullname-status-text and submit button #comment-submit-button 
 *
 * #parse-thread-fullname-form with input #thread-link and alert container #parse-thread-fullname-status-text and submit button #thread-submit-button
 *
 * #check-if-seen-form with input #check-if-seen-fullname and alert container #check-if-seen-status-text and submit button #check-if-seen-submit-button 
 *
 * #make-recheck-form with input #make-recheck-fullname, checkboxes #make-recheck-forget-cb and #make-recheck-recheck-cb, and alert container #make-recheck-status-text
 */

$(function() {
  $("#parse-comment-fullname-form").on("submit", function(e) {
    e.preventDefault();

    var status_text = $("#parse-comment-fullname-status-text");
    var permalink_div = $("#permalink");
    var output_div = $("#comment-fullname");
    var comment_submit_button = $("#comment-submit-button");

    if(comment_submit_button.is(":disabled"))
      return;

    var permalink = permalink_div.val();

    permalink_div.removeClass("is-invalid").removeClass("is-valid");

    function finish_up(success, text, output="") {
      set_status_text(status_text, text, success ? "success" : "danger", true).then(function(af_prom) {
        permalink_div.addClass(success ? "is-valid" : "is-invalid");
        output_div.val(output);
        af_prom.promise.finally(function() {
          permalink_div.removeClass(success ? "is-valid" : "is-invalid");
        });
      });
    }

    function fail_with_error(error_mes) {
      finish_up(false, FAILURE_GLYPHICON + " " + error_mes);
    }

    function succeed_with_message(mess, fullname) {
      finish_up(true, SUCCESS_GLYPHICON + " " + mess, fullname);
    }

    if(!permalink) {
      fail_with_error("Permalink must be set!");
      return;
    }

    var parts = permalink.split('/');
    var id = parts.pop();
    if(!id) {
      if(parts.length < 1) {
        fail_with_error("Invalid URL (splitting on '/' gave one blank element)");
        return;
      }
      id = parts.pop();
    }

    var fullname = "t1_" + id;
    succeed_with_message("Success!", fullname);
  });

  $("#parse-thread-fullname-form").on('submit', function(e) {
    e.preventDefault();

    var status_text = $("#parse-thread-fullname-status-text");
    var thread_link_div = $("#thread-link");
    var output_div = $("#thread-fullname");
    var thread_submit_button = $("#thread-submit-button");

    if(thread_submit_button.is(":disabled"))
      return;

    var thread_link = thread_link_div.val();

    function finish_up(success, text, output="") {
      set_status_text(status_text, text, success ? "success" : "danger", true).then(function(fk_prom) {
        thread_link_div.addClass(success ? "is-valid" : "is-invalid");
        output_div.val(output);
        fk_prom.promise.finally(function() {
          thread_link_div.removeClass(success ? "is-valid" : "is-invalid");
        });
      });
    }

    if(!thread_link) {
      finish_up(false, FAILURE_GLYPHICON + " Thread link is required!");
      return;
    }

    var parts = thread_link.split('/');
    if(parts.length < 3) {
      finish_up(false, FAILURE_GLYPHICON + " That cannot possible be a valid thread link!");
      return;
    }
    var id = parts.pop();
    if(!id) {
      parts.pop();
    }
    id = parts.pop();

    var fullname = "t3_" + id;
    finish_up(true, SUCCESS_GLYPHICON + " Success!", fullname);
  });

  $("#check-if-seen-form").on("submit", function(e) {
    e.preventDefault();

    var status_text = $("#check-if-seen-status-text");
    var fullname_div = $("#check-if-seen-fullname");
    var submit_button = $("#check-if-seen-submit-button");

    if(submit_button.is(":disabled"))
      return;

    var fullname = fullname_div.val();

    function finish_up(success, text) {
      set_status_text(status_text, text, success ? "success" : "danger", !success).then(function(fk_prom) {
        thread_link_div.addClass(success ? "is-valid" : "is-invalid");
        fk_prom.promise.finally(function() {
          thread_link_div.removeClass(success ? "is-valid" : "is-invalid");
        });
      });
    }

    if(!fullname) {
      finish_up(false, FAILURE_GLYPHICON + " Fullname is required!");
      return;
    }

    set_status_text(status_text, LOADING_GLYPHICON + " Querying database about " + fullname + "...", "info", false).then(function(fk_prom) {
      $.get("https://redditloans.com/api/rechecks.php", { fullname: fullname }, function(data, stat) {
        var found = data.found;

        if(found) {
          finish_up(true, SUCCESS_GLYPHICON + " The LoansBot <i>has</i> viewed that comment");
        }else {
          finish_up(true, FAILURE_GLYPHICON + " The LoansBot has <i>not</i> viewed that comment");
        }
      }).fail(function(xhr) {
        var err_mess = "Unknown";
        if(typeof(xhr.responseJSON) !== 'undefined') {
          err_mess = xhr.responseJSON.errors[0].error_message;
        }else {
          err_mess = xhr.status + ": " + xhr.statusText;
        }

        set_status_text(status_text, FAILURE_GLYPHICON + " An error occurred: " + err_mess, "danger", false);
      });
    });
  });
});
