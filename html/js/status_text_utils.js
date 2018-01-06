var SUCCESS_GLYPHICON = '<i class=\"far fa-check\"></i>';
var FAILURE_GLYPHICON = '<i class=\"far fa-exclamation-triangle\"></i>';
var LOADING_GLYPHICON = '<i class=\"far fa-sync fa-spin\"></i>';

/*
 * This is used to differentiate which handler is handling
 * the auto-fold on some status text
 */
var status_text_id_counter = 1;

/*
* Set the status text of st_div to the specified text. Returns
* a promise for when the status text is completely visible. The
* the returned promise will resolve PRIOR to
* auto folding, however it will pass in a promise for auto folding.
* Note the auto folding promise is much more likely to be rejected,
* since auto folding is always suppressed by future calls to 
* set_status_text and will also always be rejected if auto_fold
* is false
*
* **NOTE**
* We cannot actually return a promise in this way, because doing so
* would automatically have the promise resolved to true (this allows
* fancy promise chaining). To avoid this behavior, the promise returned
* from the promise is an object of the form {promise: Promise}
*
* Thus, if you want to do something when the status text is visible and
* then something else when the status text is hidden:
*
*
* function on_status_visible() { ... }
* function on_status_hidden() { ... }
* function on_status_hidden_fail() { ... } // another message was displayed instead of hiding this one
*
* set_status_text(st_div, "test", "primary", true).then(function(fake_prom) {
*   var real_prom = fake_prom.promise;
*   on_status_visible();
*   real_prom.then(on_status_hidden, on_status_hidden_fail);
* })
*
* @param st_div the div to insert the status text
* @param new_text the new text for the div
* @param new_alert_type the new alert type (primary, secondary, danger, info, etc)
* @param auto_fold if the div should auto minimize after some time
* @return a promise resolving after visible, before auto fold, with a promise for after auto fold
*/
function set_status_text(st_div, new_text, new_alert_type, auto_fold) {
 var my_id = (status_text_id_counter++);
 st_div.data("st-handled-by", my_id);

 function actually_set_status_text() {
   st_div.html(new_text);
   st_div.removeClass();
   st_div.addClass("container-fluid").addClass("alert").addClass("alert-" + new_alert_type);
 }

 function setup_auto_fold() {
   var me = new Promise(function(resolve, reject) {
     if(st_div.data("st-handled-by") !== my_id) {
       reject("there was a future call to set_status_text prior to auto folding");
       return;
     }
     st_div.data("shown", false);
     st_div.data("hiding", true);
     st_div.slideUp('fast', function() {
       st_div.data("hiding", false);
       st_div.data("hidden", true);
       st_div.data("current-promise", null);
       resolve(true);
     });
   });
   st_div.data("current-promise", me);
   return me;
 }

 function resolve_with_auto_fold(resolve, reject) {
   var prom2 = new Promise(function(resolve2, reject2) {
     if(auto_fold)
       resolve2(setup_auto_fold());
     else
       reject2("auto-folding not requested");
   });
   resolve({ promise: prom2 });
 }

 var was_hiding = st_div.data("hiding") || false;
 var was_hidden = st_div.data("hidden") || false;
 var was_showing = st_div.data("showing") || false;
 var was_shown = st_div.data("shown") || false;

 if(!was_hiding && !was_hidden && !was_showing && !was_shown) {
   // this hasnt been modified yet
   was_hidden = st_div.is(":hidden");
   was_shown = !was_hidden;
 }

 var latest_promise = st_div.data("current-promise");
 
 if(was_hiding || was_showing) {
   console.assert(latest_promise !== null, 'If status text was hiding or was showing we should have a promise for that to complete!');
   
   if(was_showing) {
     // After the promise is down were visible, so this is still under "showing"
     var me = new Promise(function(resolve, reject) {
       latest_promise.then(function(b) {
         st_div.data("showing", true); // after a "showing" promise finishes its now "shown", we undo that
         st_div.data("shown", false);
         st_div.fadeOut('fast', function() {
           actually_set_status_text();
           st_div.fadeIn('fast', function() {
             st_div.data("shown", true);
             st_div.data("showing", false);
             st_div.data("current-promise", null);
             resolve_with_auto_fold(resolve, reject);
           });
         });
       }, function(reject_reason) {
         reject(reject_reason);
       });
     });
     st_div.data("current-promise", me);
     return me;
   }else if(was_hiding) {
     var me = new Promise(function(resolve, reject) {
       st_div.data("hidden", false); // after a "hiding" promise finishes its now "hidden" and we are going to show
       st_div.data("showing", true);
       latest_promise.then(function(b) {
         actually_set_status_text();
         st_div.slideDown('fast', function() {
           st_div.data("showing", false);
           st_div.data("shown", true);
           st_div.data("current-promise", null);
           resolve_with_auto_fold(resolve, reject);
         });
       }, function(reject_reason) {
         reject(reject_reason)
       });
     });
     st_div.data("current-promise", me);
     return me;
   }
 }
 if(was_hidden) {
   var me = new Promise(function(resolve, reject) {
     actually_set_status_text();
     st_div.data("hidden", false);
     st_div.data("showing", true);
     st_div.slideDown('fast', function() {
       st_div.data("showing", false);
       st_div.data("shown", true);
       st_div.data("current-promise", null);
       resolve_with_auto_fold(resolve, reject);
     });
   });
   st_div.data("current-promise", me);
   return me;
 }else if(was_shown) {
   var me = new Promise(function(resolve, reject) {
     st_div.data("shown", false);
     st_div.data("showing", true);
     st_div.fadeOut('fast', function() {
       actually_set_status_text();
       st_div.fadeIn('fast', function() {
         st_div.data("showing", false);
         st_div.data("shown", true);
         st_div.data("current-promise", null);
         resolve_with_auto_fold(resolve, reject);
       });
     });
   });
   st_div.data("current-promise", me);
   return me;
 }
}
