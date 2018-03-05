/*
 * Set the main status to the specified type, returning a promise
 * for when it is visible to the user
 *
 * @param type the type of message (one of primary, secondary, success, danger, warning, info, light, or dark)
 * @param html the html for the status text
 */
function set_status(type, html) {
  return set_status_text($("#stats-status"), html, type, true);   
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
 *   sum_unpaid_cents: number,
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
  tr.append("<th>Sum Unpaid</th>");
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
    td.text( "$" + (row.sum_unpaid_cents / 100).toFixed(2) );
    td.attr("data-th", "Sum Unpaid Principal");
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
  tabl.addClass("w-100");
  tabl.slideDown('fast');
}

/*
 * Sets up the most active recent table. This replaces the "Recent" in the
 * header for the section with "Since xx/xx/xx" to reduce ambiguity. It also
 * uses the given data to apply it to the table, ensuring that the table is 
 * not visible while being modified.
 *
 * @param since a Date for after when we were focusing on
 * @param data an array of objects, ordered from most active to least active, where each
 *        object is of the form
 *        {
 *          username: string,
 *          number_new_loans: number,
 *          new_principal_cents: number,
 *          amount_outstanding_cents: number,
 *          amount_outstanding_loans: number
 *        }
 */
function setup_most_active_recent(since, data) {
  var pretty_since = since.getUTCFullYear() + "-" + (since.getUTCMonth() + 1);

  var since_span = $("#most-active-lenders-recent-since");
  since_span.fadeOut('fast', function() {
    since_span.empty();
    since_span.text("(Since " + pretty_since + ")");
    since_span.fadeIn('fast');
  });

  var tabl = $("#most-active-lenders-recent");
  tabl.attr("style", "display: none");
  
  var thead = $("<thead>");
  var tr = $("<tr>");
  tr.append("<th>Username</th>");
  tr.append("<th>New Loans</th>");
  tr.append("<th>Sum New Principal</th>");
  tr.append("<th>Principal Outstanding</th>");
  tr.append("<th>Loans Outstanding</th>");
  thead.append(tr);
  tabl.append(thead);

  var tbody = $("<tbody>");
  for(var ind = 0, len = data.length; ind < len; ind++) {
    var row = data[ind];
    tr = $("<tr>");

    var td = $("<td>");
    td.text(row.username);
    td.attr("data-th", "Username");
    tr.append(td);

    td = $("<td>");
    td.text(row.number_new_loans);
    td.attr("data-th", "New Loans");
    tr.append(td);

    td = $("<td>");
    td.text( "$" + (row.new_principal_cents / 100).toFixed(2) );
    td.attr("data-th", "New Principal");
    tr.append(td);

    td = $("<td>");
    td.text( "$" + (row.amount_outstanding_cents / 100).toFixed(2) );
    td.attr("data-th", "Principal Outstanding");
    tr.append(td);

    td = $("<td>");
    td.text(row.amount_outstanding_loans);
    td.attr("data-th", "Loans Outstanding");
    tr.append(td);

    tbody.append(tr);
  }

  tabl.append(tbody);
  tabl.basictable({
    tableWrap: true,
    breakpoint: 991
  });
  tabl.addClass("w-100");
  tabl.slideDown("fast");
}

/*
 * Sets up the percent requests fulfilled table and related graphics using 
 * the specified information which has been parsed in a convenient format.
 *
 * @param start - Date for when loans started being parsed at
 * @param stop  - Date for when loans stopped being parsed at 
 * @param data  - an array of objects, ordered from most requests fulfilled to least
 *                where each object is of the form 
 *                {
 *                  username: string,
 *                  number_loans: number,
 *                  perc_loans: number
 *                  principal: number,
 *                  perc_principal: number
 *                }
 */
function setup_perc_recent_requests(start, stop, data) {
  var tabl = $("#percent-requests-fulfilled");
  tabl.attr('style', 'display: none;');
  
  var thead = $("<thead>");
  var tr = $("<tr>"); 
  tr.append("<th>Username</th>");
  tr.append("<th>Loans over Period</th>");
  tr.append("<th>Percent loans over Period</th>");
  tr.append("<th>Principal over Period</th>");
  tr.append("<th>Percent Principal over Period</th>");
  thead.append(tr);
  tabl.append(thead);

  var tbody = $("<tbody>");
  for(var row of data) {
    var tr = $("<tr>");
    var td = $("<td>");
    td.text(row.username);
    td.attr('data-th', 'Username');
    tr.append(td);

    td = $("<td>");
    td.text(row.number_loans);
    td.attr('data-th', '#Loans');
    tr.append(td);

    td = $("<td>");
    td.text((row.perc_loans * 100).toFixed(2) + '%');
    td.attr('data-th', '%Loans');
    tr.append(td);

    td = $("<td>");
    td.text('$' + (row.principal / 100.0).toFixed(2));
    td.attr('data-th', 'Principal');
    tr.append(td);

    td = $("<td>");
    td.text((row.perc_principal * 100).toFixed(2) + '%');
    td.attr('data-th', '%Principal');
    tr.append(td);
    tbody.append(tr);
  }
  tabl.append(tbody);

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
 *
 * cache.recent_activity_summary
 *   An object, the keys are user ids as strings, the values are objects of the form
 *     { number_new_loans: number, new_principal_cents: number, amount_outstanding_cents: number, amount_outstanding_loans: number }
 */

/*
 * Fetch the activity summary for the specified user ids, as an object with
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
    var sum_unpaid = 0;
    var sum_unique_borrowers = 0;
    var unique_borrowers = {}; // user ids as strings for keys, the boolean 'true' as values.

    for(var i = 0, len = loans.length; i < len; i++) {
      var loan = loans[i];

      if(loan[1] === user_id) {
        num_loans++;
        sum_principal += loan[3];
        sum_repayment += loan[4];

        if(loan[5]) {
          sum_unpaid += (loan[3] - loan[4]);
        }
        
        var borrower_id = loan[2];
        if(!unique_borrowers.hasOwnProperty(borrower_id.toString())) {
          unique_borrowers[borrower_id.toString()] = true;
          sum_unique_borrowers++;
        }
      }
    }

    return { 
      number_loans: num_loans, 
      sum_loan_principal_cents: sum_principal, 
      sum_loan_principal_repayment_cents: sum_repayment, 
      sum_unpaid_cents: sum_unpaid,
      sum_unique_borrowers: sum_unique_borrowers 
    };
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
 * Fetch the recetn activity summary for the specified user ids, as an object
 * with user ids as strings for keys and recent activity summaries for values, 
 * from the cache where possible. Otherwise, calculate them and place them in 
 * the cache, as well as return them.
 *
 * @param loans the loan information
 * @param cache the cache (search cache + ctrlf no spaces no +)
 * @param user_ids an array of user ids to fetch information on
 * @param since the date to get loans after
 * @return a promise for the recent activity summaries for the specified user ids
 */
function cfetch_or_calculate_recent_activity_summaries(loans, cache, user_ids, since) {
  if(!cache.hasOwnProperty("recent_activity_summary")) {
    cache.recent_activity_summary = {};
  }

  function calculate_recent_activity_summary(user_id, since) {
    var since_n = since.getTime();
    var num_loans = 0;
    var new_principal = 0;
    var outstanding_cents = 0;
    var outstanding_loans = 0;

    for(var i = 0, len = loans.length; i < len; i++) {
      var loan = loans[i];

      if(loan[1] === user_id) {
        if(loan[6] !== 1 && loan[3] != loan[4]) {
          outstanding_cents += (loan[3] - loan[4]);
          outstanding_loans++;
        }
        if(loan[6] > since_n) {
          num_loans++;
          new_principal += loan[3];
        }
      }
    }

    return { 
      number_new_loans: num_loans,
      new_principal_cents: new_principal,
      amount_outstanding_cents: outstanding_cents,
      amount_outstanding_loans: outstanding_loans
    };
  }

  return new Promise(function(resolve, reject) { 
    var result = {};
    var promises = [];
    for(var ind = 0, len = user_ids.length; ind < len; ind++) {
      var id = user_ids[ind];
      var id_str = id.toString();
      if(!cache.recent_activity_summary.hasOwnProperty(id_str)) {
        cache.recent_activity_summary[id_str] = calculate_recent_activity_summary(id, since);
      }

      var cache_val = cache.recent_activity_summary[id_str];

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

function calculate_most_active_recent(loans, cache, since) {
  return new Promise(function(resolve, reject) {
    // user ids to loan count
    var loan_count = {};
    var since_n = since.getTime();

    // group on count(loans)
    for(var i = 0, len = loans.length; i < len; i++) {
      var loan = loans[i];
      if(loan[6] < since_n)
        continue;

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
    var recent_activity_summaries_promise = cfetch_or_calculate_recent_activity_summaries(loans, cache, top_five_as_user_id_array, since).then(function(recent_activity_summaries) {
      for(var ind = 0, len = top_five.length; ind < len; ind++) {
        var obj = top_five[ind];
        var rec_activity_summ = recent_activity_summaries[obj.user_id.toString()];

        for(var key in rec_activity_summ) {
          if(rec_activity_summ.hasOwnProperty(key)) {
            obj[key] = rec_activity_summ[key];
          }
        }
      }
    }).then(function(){});

    Promise.all([ usernames_promise, recent_activity_summaries_promise ]).then(function() {
      resolve(top_five);
    }, function(reject_reason) {
      reject(reject_reason);
    });
  });
}

/**
 * A promise to calculate the percent-requests-fulfilled table, which is an array of
 * 
 *        {
 *          username: string,
 *          number_loans: number,
 *          perc_loans: number
 *          principal: number,
 *          perc_principal: number
 *        }
 *
 * In sorted order where the earlier elements have a higher portion of new loans
 * than later entries.
 *
 * Only considers loans between the start and stop Date
 *
 * @param loans Array of loans
 * @param cache the information cache
 * @param topn the number of top loans-lent to include
 * @param users an array of user ids to include
 * @param start Date (inclusive)
 * @param stop Date (exclusive)
 * @return promise for array
 */
function calculate_perc_requests_fulfilled(loans, cache, topn, users, start, stop) {
  return new Promise(function (resolve, reject) {
    var info_by_user_id = new Map(); // id -> result except username
    var loan, info;
    var sum_loans = 0;
    var sum_princ = 0;
    for(loan of loans) {
      var lcreated_at = loan[6];
      if(lcreated_at >= start && lcreated_at < stop) {
        sum_princ += loan[3];
        sum_loans++;
        info = info_by_user_id.get(loan[1]);
        if(info !== undefined) {
          info.number_loans += 1;
          info.principal += loan[3]; 
        }else {
          info_by_user_id.set(loan[1], { number_loans: 1, principal: loan[3] });
        }
      }
    }

    var result = []; // array of [ user_id, info ]
    var user_id, ins_ind, kv;
    if(topn > 0) {
      for(kv of info_by_user_id.entries()) {
        user_id = kv[0];
        info = kv[1];
        
        if(result.length === topn && info.number_loans < result[topn - 1][1].number_loans)
          continue;

        for(ins_ind = 0; ins_ind < result.length && result[ins_ind][1].number_loans > info.number_loans; ins_ind++){}
        
        result.splice(ins_ind, 0, [user_id, info]);
        if(result.length > topn) {
          result.pop();
        }
      }
    }

    var row, found;
    for(user_id of users) {
      found = false;
      for(row of result) {
        if(row[0] === user_id) {
          found = true;
          break;
        }
      }

      if(!found) {
        for(kv of result) {
          if(kv[0] === user_id) {
            found = kv[1];
            break;
          }
        }
        if(!found) {
          found = { number_loans: 0, principal: 0 };
        }
        for(ins_ind = 0; ins_ind < result.length && result[ins_ind][1].number_loans > found.number_loans; ins_ind++){}
        result.splice(ins_ind, 0, [ user_id, found ]);
      }
    }

    var i, len;
    var user_ids = new Array(result.length);
    for(i = 0, len = result.length; i < len; i++) {
      user_ids[i] = result[i][0];
    }

    var usernames_promise = cfetch_or_fetch_usernames(loans, cache, user_ids).then(function(usernames) {
      var result_reformatted = new Array(result.length);
      for(i = 0, len = result.length; i < len; i++) {
        result_reformatted[i] = { 
          username: usernames[result[i][0]], 
          number_loans: result[i][1].number_loans, 
          perc_loans: result[i][1].number_loans / sum_loans,
          principal: result[i][1].principal,
          perc_principal: result[i][1].principal / sum_princ
        };
      }
      resolve(result_reformatted);
    }).then(function(){});
  });
}

/*
 * This function glues all the other functions together
 */
function do_everything() {
  set_status('info', LOADING_GLYPHICON + " Fetching bulk data...").then(function() {
    fetch_all_loans().then(function(loans) {
      window.last_loans = loans;
      console.log("fetch_all_loans succeeded");
      set_status('info', LOADING_GLYPHICON + " Calculating statistics...").then(function() {
        var cache = {}
        window.cache = cache;
        var promises = [];
        promises.push(calculate_most_active_overall(loans, cache).then(function(data) {
          console.log("calculate_most_active_overall succeeded");
          setup_most_active_overall(data);
        }, function(reject_reason) {
          console.log("calculate_most_active_overall failed with reason " + reject_reason);
          set_status('danger', FAILURE_GLYPHICON + " " + reject_reason); 
        }));

        var now = new Date();
        var since = new Date(now.getUTCFullYear() + "-" + (now.getUTCMonth() + 1));
        promises.push(calculate_most_active_recent(loans, cache, since).then(function(data) {
          console.log("calculate_most_active_recent succeeded");
          setup_most_active_recent(since, data);
        }, function(reject_reason) {
          console.log("calculate_most_active_recent failed with reason " + reject_reason);
          set_status("danger", FAILURE_GLYPHICON + " " + reject_reason);
        }));

        Promise.all(promises).then(function() {
          set_status('success', SUCCESS_GLYPHICON + " Success!");
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
  var now = moment().toDate();
  var amonthAgo = moment().subtract(1, 'months').toDate();
  $("#perc-req-fulfilled-start-date")[0].valueAsDate = now;
  $("#perc-req-fulfilled-end-date")[0].valueAsDate = amonthAgo;
  $("#perc-req-fulfilled-add-person-button").click(function(e) {
    e.preventDefault();

    var v = $("#perc-req-fulfilled-add-person").val().trim();
    if(v.length === 0)
      return;
    
    var opt = $("<option>");
    opt.attr('value', v);
    opt.text(v);
    opt.prop("selected", true);
    $("#perc-req-fulfilled-who-select").append(opt);
  });

  $("#perc-req-fulfilled-search-btn").click(function(e) {
    e.preventDefault();

    var topn = 0;
    var usernames = [];

    var opts = $("#perc-req-fulfilled-who-select option:selected");
    for(var opt of opts) {
      opt = $(opt);

      var v = opt.val();
      if(v.startsWith('top')) {
        topn = Math.max(topn, parseInt(v.substring(3)));
      }else {
        usernames.push(v);
      }
    }

    var user_id_promises = [];
    for(var username in usernames) {
      user_id_promises.append(new Promise(function(resolve, reject) {
        $.get("https://redditloans.com/api/users.php", { username: username }, function(data) {
          resolve(data.users);
        }).fail(function(xhr) {
          console.log(xhr);
          reject(xhr);
        });
      }));
    }

    Promise.all(user_id_promises).then(function(user_ids) {
      new Promise(function(resolve, reject) {
        if(window.last_loans !== undefined && window.last_loans !== null) {
          resolve(window.last_loans);
        }else {
          fetch_all_loans().then(function(loans) {
            window.last_loans = loans;
            window.cache = {};
            resolve(loans);
          });
        }
      }).then(function(loans) {
        var start = $("#perc-req-fulfilled-start-date")[0].valueAsDate;
        var stop = $("#perc-req-fulfilled-end-date")[0].valueAsDate;
        calculate_perc_requests_fulfilled(loans, window.cache, topn, user_ids, start, stop).then(function(data) {
          setup_perc_recent_requests(start, stop, data);
        });
      });
    });
  });
});
