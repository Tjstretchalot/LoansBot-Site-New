var SUCCESS_GLYPHICON = '<i class=\"far fa-check\"></i>';
var FAILURE_GLYPHICON = '<i class=\"far fa-exclamation-triangle\"></i>';
var LOADING_GLYPHICON = '<i class=\"far fa-sync fa-spin\"></i>';
/*
 * Set the main status to the specified type, returning a promise
 * for when it is visible to the user
 *
 * @param type the type of message (one of primary, secondary, success, danger, warning, info, light, or dark)
 * @param html the html for the status text
 */
function set_status(type, html) {
  console.log("set_status('" + type + "', '" + html + "')");
  var stat_text = $("#stats-status");

  return new Promise(function(resolve, reject) {
    var was_hidden = stat_text.is(":hidden");
    stat_text.fadeOut('fast', function() {
      stat_text.empty();
      stat_text.removeClass();
      stat_text.addClass("container-fluid").addClass("alert");
      stat_text.addClass("alert-" + type);
      stat_text.html(html);
      if(!was_hidden) {
        stat_text.fadeIn('fast', function() {
          resolve();
        });
      }else {
        stat_text.slideDown('fast', function() {
          resolve();
        });
      }
    });
  });
}

/*
 * Hide the main status alert
 */
function hide_status() {
  console.log("hide_status()");
  var stat_text = $("#stats-status");

  return new Promise(function(resolve, reject) {
    stat_text.slideUp(function() {
      resolve();
    });
  });
}

/*
 * Get a human-readable error message based on the response from the server
 * for an ajax query.
 *
 * @param xhr the thing passed to the failure callback
 * @return human-readable string
 */
function get_error_message(xhr) {
  var err_mess = "Unknown";
  if(xhr.hasOwnProperty('responseJSON')) {
    err_mess = xhr.responseJSON.errors[0].error_message;
  }else {
    err_mess = xhr.status + " " + xhr.statusText;
  }
  return err_mess;
}

/*
 * Fetches all loans from api/loans.php using format 2
 *
 * @return promise of list (of loans/compact)
 */
function fetch_all_loans() {
  return new Promise(function(resolve, reject) {
    $.get('https://redditloans.com/api/loans.php', { format: 1, limit: 0 }, function(data, stat) {
      resolve(data.loans);
    }).fail(function(xhr) {
      var err_mess = get_error_message(xhr);
      reject(err_mess);
    });
  });
}

/*
 * Fetches the username for the specified user id
 *
 * @ return promise of string
 */
function fetch_username(user_id) {
  return new Promise(function(resolve, reject) {
    $.get('https://redditloans.com/api/users.php', { format: 3, limit: 1, id: user_id }, function(data, stat) {
      if(data.users.length === 0) {
        reject("Empty result");
        return;
      }

      resolve(data.users[0].username);
    }).fail(function(xhr) {
      var err_mess = get_error_message(xhr);
      set_status("danger", FAILURE_GLYPHICON + " Failed to find username for user id=" + user_id + ": " + err_mess);
      reject(err_mess);
    });
  });
}

/*
 * Sets the most-active-lenders-overall table according to the specified information.
 *
 * Should contain an array of objects, where each object is of the form:
 *
 * {
 *   username: "reddit_username",
 *   number_loans: number,
 *   sum_loan_principal_cents: number,
 *   sum_loan_principal_repayment_cents: number,
 *   sum_unique_borrowers: number
 * }
 */
function setup_most_active_overall(info) {
  var tabl = $("#most-active-lenders-overall");
  tabl.attr("style", "display: none");

  var thead = $("<thead>");
  var tr = $("<tr>");
  tr.append("<th>Username</th>");
  tr.append("<th>Num. Loans</th>");
  tr.append("<th>Sum Principal</th>");
  tr.append("<th>Sum Repayment</th>");
  tr.append("<th>Num. Unique Borr.</th>");
  thead.append(tr);
  tabl.append(thead);

  var tbody = $("<tbody>");
  for(var i = 0, len = info.length; i < len; i++) {
    var row = info[i];

    tr = $("<tr>");
    var td = $("<td>");
    td.text(row.username);
    td.attr("data-th", "Username");
    tr.append(td);

    td = $("<td>");
    td.text(row.number_loans.toString());
    td.attr("data-th", "Number of Loans");
    tr.append(td);

    td = $("<td>");
    td.text( "$" + (row.sum_loan_principal_cents / 100).toFixed(2) );
    td.attr("data-th", "Sum of Loan Principal");
    tr.append(td);

    td = $("<td>");
    td.text( "$" + (row.sum_loan_principal_repayment_cents / 100).toFixed(2) );
    td.attr("data-th", "Sum Loan Princ. Repayment");
    tr.append(td);

    td = $("<td>");
    td.text(row.sum_unique_borrowers.toString());
    td.attr("data-th", "Number of Unique Borrowers");
    tr.append(td);

    tbody.append(tr);
  }
  tabl.append(tbody);
  tabl.basictable({
    tableWrap: true,
    breakpoint: 991
  });
  tabl.slideDown('fast');
}

/*
 * Cache information ( cachectrlf )
 *
 * At any point, any "object" in the cache may be a promise for that object.
 *
 * cache.users
 *   An object, the keys are user ids as strings, the values are usernames
 *
 * cache.activity_summary
 *   An object, the keys are user ids as strings, the values are objects of the form
 *     { number_loans: number, sum_loan_principal_cents: number, sum_loan_principal_repayment_cents: number, sum_unique_borrowers: number }
 */

/*
 * Fetch the activity summary for the specified user ids, as a object with
 * user ids as strings for keys and activity summaries as values, from the
 * cache where possible. Otherwise, calculate them and place them in the cache,
 * as well as return them.
 *
 * @param loans the loan information
 * @param cache the cache (search cache + ctrlf no spaces no +)
 * @param user_ids an array of user ids to fetch information on
 * @return a promise for the activity summaries for the specified user ids 
 */
function cfetch_or_calculate_activity_summaries(loans, cache, user_ids) {
  if(!cache.hasOwnProperty("activity_summary")) {
    cache.activity_summary = {};
  }

  function calculate_activity_summary(user_id) {
    var num_loans = 0;
    var sum_principal = 0;
    var sum_repayment = 0;
    var sum_unique_borrowers = 0;
    var unique_borrowers = {}; // user ids as strings for keys, the boolean 'true' as values.

    for(var i = 0, len = loans.length; i < len; i++) {
      var loan = loans[i];

      if(loan[1] === user_id) {
        num_loans++;
        sum_principal += loan[3];
        sum_repayment += loan[4];
        
        var borrower_id = loan[2];
        if(!unique_borrowers.hasOwnProperty(borrower_id.toString())) {
          unique_borrowers[borrower_id.toString()] = true;
          sum_unique_borrowers++;
        }
      }
    }

    return { number_loans: num_loans, sum_loan_principal_cents: sum_principal, sum_loan_principal_repayment_cents: sum_repayment, sum_unique_borrowers: sum_unique_borrowers };
  }


  return new Promise(function(resolve, reject) { 
    var result = {};
    var promises = [];
    for(var ind = 0, len = user_ids.length; ind < len; ind++) {
      var id = user_ids[ind];
      var id_str = id.toString();
      if(!cache.activity_summary.hasOwnProperty(id_str)) {
        cache.activity_summary[id_str] = calculate_activity_summary(id);
      }

      var cache_val = cache.activity_summary[id_str];

      if(typeof(cache_val.then) === 'function') {
        promises.push(cache_val.then(function(true_cache_val) {
          result[id_str] = true_cache_val;
        }, function(reject_reason) {
          reject(reject_reason);
        }));
      }else {
        result[id_str] = cache_val;
      }
    }

    Promise.all(promises).then(function() {
      resolve(result); 
    }, function(reject_reason) {
      reject(reject_reason);
    });
  });
}

/*
 * Fetch the usernames for the specified user ids, as an object with
 * user ids as strings for keys and usernames for values, from the 
 * cache where possible. Otherwise, fetch them from the server and 
 * place them in the cache, then return them.
 *
 * @param loans loans/compact
 * @param cache the cache
 * @param user_ids an array of user ids to fetch usernames for
 * @return a promise for usernames for the specified user ids
 */
function cfetch_or_fetch_usernames(loans, cache, user_ids) {
  if(!cache.hasOwnProperty("users"))
    cache.users = {};

  return new Promise(function(resolve, reject) {
    var promises = [];
    for(var ind = 0, len = user_ids.length; ind < len; ind++) {
      var id = user_ids[ind];
      if(!cache.users.hasOwnProperty(id.toString()))  {
        var tmp = null;
        tmp = cache.users[id.toString()] = fetch_username(id).then(function(username) {
          cache.users[id.toString()] = username;
          return username;
        });
        promises.push(tmp);
      }else {
        var tmp = cache.users[id.toString()];
        promises.push(tmp);
      }
    }

    Promise.all(promises).then(function(data) {
      var result = {};
      for(var ind = 0, len = user_ids.length; ind < len; ind++) {
        var id = user_ids[ind];
        result[id] = data[ind];
      }
      resolve(result);
    }, function(reject_reason) {
      reject(reject_reason);
    });
  });
}

/*
 * Calculates the lenders who are most active overall by 
 * number of loans
 *
 * @param loans a list of loans/compact
 * @param cache this is the cache of data (search for cache followed by ctrlf, without spaces)
 * @return a promise for something that can be passed to setup_most_active_overall
 */
function calculate_most_active_overall(loans, cache) {
  return new Promise(function(resolve, reject) {
    // user ids to loan count
    var loan_count = {};

    // group on count(loans)
    for(var i = 0, len = loans.length; i < len; i++) {
      var loan = loans[i];
      var lender_id = loan[1];
      var key = lender_id.toString();
      if(loan_count.hasOwnProperty(key)) {
        loan_count[key] = loan_count[key] + 1;
      }else {
        loan_count[key] = 1;
      }
    }

    // finding the top 5 
    var top_five = [];
    for(key in loan_count) {
      var num_loans = loan_count[key];

      if(top_five.length < 5) {
        //insertion insert
        var index = 0;
        while(top_five.length > index && top_five[index].num_loans > num_loans) {
          index++;
        }
        if(index === top_five.length) {
          top_five.push({ user_id: parseInt(key), num_loans: num_loans });
        }else {
          top_five.splice(index, 0, { user_id: parseInt(key), num_loans: num_loans });
        }
      }else {
        var index = 0;
        while(top_five.length > index && top_five[index].num_loans > num_loans) {
          index++;
        }

        if(index < 5) {
          top_five.splice(index, 0, { user_id: parseInt(key), num_loans: num_loans });
          top_five.pop()
        }
      }
    }

    var top_five_as_user_id_array = []
    for(key in top_five) {
      top_five_as_user_id_array.push(top_five[key].user_id);
    }

    var usernames_promise = cfetch_or_fetch_usernames(loans, cache, top_five_as_user_id_array).then(function(usernames) {
      for(var ind = 0, len = top_five.length; ind < len; ind++) {
        var obj = top_five[ind];
        obj.username = usernames[obj.user_id.toString()];
      }
    }).then(function(){});
    var activity_summaries_promise = cfetch_or_calculate_activity_summaries(loans, cache, top_five_as_user_id_array).then(function(activity_summaries) {
      for(var ind = 0, len = top_five.length; ind < len; ind++) {
        var obj = top_five[ind];
        var activity_summ = activity_summaries[obj.user_id.toString()];

        for(var key in activity_summ) {
          if(activity_summ.hasOwnProperty(key)) {
            obj[key] = activity_summ[key];
          }
        }
      }
    }).then(function(){});

    Promise.all([ usernames_promise, activity_summaries_promise ]).then(function() {
      resolve(top_five);
    }, function(reject_reason) {
      reject(reject_reason);
    });
  });
}

/*
 * This function glues all the other functions together
 */
function do_everything() {
  set_status('info', LOADING_GLYPHICON + " Fetching bulk data...").then(function() {
    fetch_all_loans().then(function(loans) {
      console.log("fetch_all_loans succeeded");
      set_status('info', LOADING_GLYPHICON + " Calculating statistics...").then(function() {
        var cache = {}
        var promises = [];
        promises.push(calculate_most_active_overall(loans, cache).then(function(data) {
          console.log("calculate_most_active_overall succeeded");
          setup_most_active_overall(data);
        }, function(reject_reason) {
          console.log("calculate_most_active_overall failed with reason " + reject_reason);
          set_status('danger', FAILURE_GLYPHICON + " " + reject_reason); 
        }));

        Promise.all(promises).then(function() {
          set_status('success', SUCCESS_GLYPHICON + " Success!").then(function() {
            setTimeout(function() {
              hide_status();
            }, 3000);
          }); 
        });
      });
    }, function(reject_reason) {
      console.log("fetch_all_loans failed with reason " + reject_reason);
      set_status('danger', FAILURE_GLYPHICON + " " + reject_reason); 
    });
  });
};

$(function() {
  do_everything();
});
