var SUCCESS_GLYPHICON = '<i class=\"far fa-check\"></i>';
var FAILURE_GLYPHICON = '<i class=\"far fa-exclamation-triangle\"></i>';
var LOADING_GLYPHICON = '<i class=\"far fa-sync fa-spin\"></i>';

/*
 * This is used to differentiate which handler is handling
 * the auto-fold on some status text
 */
var status_text_id_counter = 1;

/* 
 * the jquery wrapped div we're controlling.
 */
var __status_text_div = null;

/*
 * Contains objects that look like
 *
 * {
 *    text: string,
 *    alert_type: string,
 *    auto_fold: boolean,
 *    min_visible_duration: number,
 *    resolve_start_promise: function
 * }
 */
var __status_text_queue = [];

/*
 * The currently active status text
 * {
 *   auto_fold: boolean
 *   finish_time: Date
 *   resolve_finish_promise: function,
 *   reject_finish_promise: function
 * }
 */
var __status_text_active = null;

/*
 * If we are currently actively ticking
 */
var __status_text_started = false;

/*
 * If we can modify the status text yet
 */
var __status_text_ready = true;

function __status_text_tick() {
  __status_text_started = false;

  if(!__status_text_ready) {
    __status_text_started = true;
    setTimeout(__status_text_tick, 100);
    return;
  }


  var time = new Date();

  var active = __status_text_active;
  var queue = __status_text_queue;

  if(active !== null) {
    if(active.auto_fold) {
      if(active.finish_time > time) {
        __status_text_started = true;
        setTimeout(__status_text_tick, active.finish_time - time);
        return;
      }

      if(queue.length === 0) {
        __status_text_div.slideUp();
        active.resolve_finish_promise();
        __status_text_active = null;
        active = null;
      }
    }
  }

  if(queue.length > 0) {
    if(active) {
      active.reject_finish_promise();
      active = null;
      __status_text_active = null;
    }

    var next = queue.shift();
    var outer_resolve = null;
    var outer_reject = null;
    var promise = new Promise(function(resolve, reject) {
      outer_resolve = resolve;
      outer_reject = reject;
    }).catch((e) => {}); // chrome spams console otherwise :/

    next.resolve_start_promise({ promise: promise });
    __status_text_ready = false;
    (function(typ, tex) {
      __status_text_div.slideUp('fast', function() {
        __status_text_div.attr('class', 'container-fluid alert alert-' + typ);
        __status_text_div.html(tex);
        // chrome needs a bit to render
        setTimeout(function() {
          __status_text_div.slideDown('fast', function() {
            __status_text_ready = true;
          });
        }, 10);
      });
    })(next.alert_type, next.text);
    

    if(next.auto_fold) {
      active = {
        auto_fold: true,
        finish_time: new Date(Date.now() + next.min_visible_duration + 800), // 800 is animation time
        resolve_finish_promise: outer_resolve,
        reject_finish_promise: outer_reject
      };
      __status_text_active = active;
      __status_text_started = true;
      setTimeout(__status_text_tick, next.min_visible_duration);
      return;
    }

    active = {
      auto_fold: false,
      resolve_finish_promise: outer_resolve,
      reject_finish_promise: outer_reject
    }
    __status_text_active = active;
    __status_text_started = true;
    setTimeout(__status_text_tick, 400);
    return;
  }
}

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
* @param min_visible_duration minimum time to keep it visible
* @return a promise resolving after visible, before auto fold, with a promise for after auto fold
*/
function set_status_text(st_div, new_text, new_alert_type, auto_fold, min_visible_duration = 2000) {
  if(__status_text_div === null) {
    __status_text_div = st_div;
  }

  if(new_alert_type !== 'info' && new_alert_type !== 'danger' && new_alert_type !== 'success') {
    console.log('weird alert type: ' + new_alert_type);
  }

  var outer_resolve = null;
  var promise = new Promise(function(resolve, reject) {
    outer_resolve = resolve;
  });

  __status_text_queue.push({ 
   text: new_text,
   alert_type: new_alert_type,
   auto_fold: auto_fold,
   min_visible_duration: min_visible_duration,
   resolve_start_promise: outer_resolve
 });

 if(!__status_text_started) {
   __status_text_tick();
 }

 return promise;
}
