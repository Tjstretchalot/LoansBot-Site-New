/*
 * This form performs the heavy lifting behind rechecks.php
 *
 * It assumes the following elements:
 * 
 * #parse-comment-fullname-form with input #permalink and alert container #parse-comment-fullname-status-text and submit button #comment-submit-button 
 *
 * #parse-thread-fullname-form with input #thread-link and alert container #parse-thread-fullname-status-text
 *
 * #check-if-seen-form with input #check-if-seen-fullname and alert container #check-if-seen-status-text 
 *
 * #make-recheck-form with input #make-recheck-fullname, checkboxes #make-recheck-forget-cb and #make-recheck-recheck-cb, and alert container #make-recheck-status-text
 */

 $(function() {
   $("#parse-comment-fullname-form").on("submit", function(e) {
     e.preventDefault();

     var status_text = $("#parse-comment-fullname-status-text");
     var permalink_div = $("#permalink");
     var output_div = $("comment-fullname");
     var comment_submit_button = $("#comment-submit-button");

     if(comment_submit_button.is(":disabled"))
       return;

     var permalink = permalink_div.val();

     permalink_div.removeClass("is-invalid").removeClass("is-valid");
     comment_submit_button.attr("disabled", true);

     function finish_up(success, text) {
       permalink_div.addClass(success ? "is-valid" : "is-invalid");
       var cleanup = function() {
         comment_submit_button.attr("disabled", false);
         permalink_div.removeClass(success ? "is-valid" : "is-invalid");
       };
         
       set_status_text(status_text, text, success ? "success" : "danger", true).then(function(af_prom) {
         af_prom.then(cleanup, cleanup);
       });
     }

     function fail_with_error(error_mes) {
       finish_up(false, FAILURE_GLYPHICON + " " + error_mes);
     }

     function succeed_with_message(mess) {
       finish_up(true, SUCCESS_GLYPHICON + " " + mess);
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
     output_div.val(fullname);
     succeed_with_message("Success!");
   });
});
