<?php
  include_once('connect_and_get_loggedin.php');
?>
<!doctype html>
<html lang="en">
  <head>
    <title>RedditLoans</title>
    <?php include('metatags.php'); ?>

    <?php include('bootstrap_css.php'); ?>
    <link rel="stylesheet" href="/css/basictable.css">
    <link rel="stylesheet" href="/css/query2.css">
  </head>
  <body>
    <?php include('navigation.php'); ?>
    <div class="container px-2 py-5">
      <div class="container-fluid alert" id="statusText" style="display: none"></div>
      <section>
        <div class="container-fluid alert" id="saved-queries-status-text" style="display: none"></div>
        <form id="saved-queries-form" class="form-inline">
          <div class="form-group justify-content-between w-100">
            <select class="form-control col-sm" id="saved-queries-select">
              <option value="loading">Loading...</option>
            </select>
            <button type="submit" class="btn btn-primary col-sm" value="Load">Load Query</button>
            <button type="button" id="run-query-button" class="btn btn-secondary col-sm" value="Run">Run Query</button>
            <button type="button" id="delete-query-button" class="btn btn-danger col-sm" value="Delete">Delete Query</button>
          </div>
        </form>
      </section>
      <section>
        <form id="parameters-form">
        </form>
        <form id="add-parameter-form" class="form-inline">
          <div class="form-group w-100">
            <select class="form-control col-sm" id="add-parameter-select">
              <option>Loading...</option>
            </select>
            <button type="submit" class="btn btn-primary col-sm">Add Parameter</button>
          </div>
        </form>
        <div class="container-fluid alert" id="save-query-status-text" style="display: none"></div>
        <form id="save-query-form" class="form-inline">
          <div class="form-group w-100">
            <input type="text" class="form-control col-sm" id="save-query-name" aria-label="Name for this query" placeholder="Name for this query" required>
            <button type="submit" class="btn btn-primary col-sm">Save Query</button>
          </div>
        </form>
        <div class="container-fluid alert" id="get-results-status-text" style="display: none"></div>
        <form id="get-results-form" class="form-inline">
          <div class="form-group w-100">
            <button type="submit" class="btn btn-primary btn-block">Get Results</button>
          </div>
        </form>
      </section>
      <section>
        <div id="results-table-wrapper">
          <table id="results-table">
          </table>
        </div>
      </section>
    </div>
    <?php include('bootstrap_js.php') ?>
    <script src="js/jquery.basictable.min.js"></script>
    <script src="js/query2_query_parameters.js"></script>
    <script type="text/javascript">
      var existing_query_parameters = [];
      var loaded_saved_queries = {};

      /*
       * Accepts a list of saved queries (at least sufficient to include the
       * query name and query id), and then populates the saved-queries-select
       * with them.
       *
       * @param saved_queries something of the form [ { str_id: "unique_string123", name: "Query 1" } ]
       */
      function setup_saved_queries(saved_queries) {
        var select = $("#saved-queries-select");

        select.empty();
        for(var i = 0, len = saved_queries.length; i < len; i++) {
          var saved_query = saved_queries[i];
          var opt = $("<option>");
          opt.attr("value", saved_query.str_id);
          opt.text(saved_query.name);
          select.append(opt);
        }
      }

      /*
       * Sets up the "add-parameter-select" selection box with the list of valid
       * parameters, which must be an array of elements at least of the form
       *
       * { param_name: "unique_paramname", name: "Param Name" }
       *
       * This will use the data attribute "param-name" to save the param name for
       * each <option>
       *
       * @param valid_parameters an array of parameters
       */
      function setup_add_parameters(valid_parameters) {
        var select = $("#add-parameter-select");
        select.empty();

        for(var i = 0, len = valid_parameters.length; i < len; i++) {
          var valid_param = valid_parameters[i];
          var ele = $("<option>");
          ele.attr("value", valid_param.param_name);
          ele.text(valid_param.name);
          select.append(ele);
        }
      }

      /*
       * Removes the specified parameter by the parameter name from the visible
       * list of parameters in the parameters-form.
       *
       * @param param_name the unique name of the parameter
       */
      function remove_query_parameter(param_name) {
        var form = $("#parameters-form");
        form.children().each(function(idx) {
          if($(this).data("param-name") === param_name) {
            $(this).remove();
            return false;
          }
        });

        var params_select = $("#add-parameter-select");
        params_select.children().each(function(idx) {
          if($(this).attr("value") === param_name) {
            $(this).attr("disabled", false);
            return false;
          }
        });

        for(var i = 0, len = existing_query_parameters.length; i < len; i++) {
          var exist_param = existing_query_parameters[i];
          if(exist_param.param_name === param_name) {
            existing_query_parameters.splice(i, 1);
            break;
          }
        }
      }

      /*
       * Append a query parameter to the parameters form.
       *
       * The param_name must match a param name that is specified in
       * /js/query2_query_parameters.js
       *
       * The options vary based on the parameter. It should be an array.
       *
       * @param param_name a unique string corresponding with the query parameter
       * @param options the options to be passed to the constructor
       */
      function append_query_parameter(param_name, options) {
        for(var i = 0, len = existing_query_parameters.length; i < len; i++) {
          var existing_param = existing_query_parameters[i];
          if(existing_param.param_name === param_name)
            throw Error("Duplicate param name " + param_name + " (matches with existing param at index " + i + ")");
        }

        var new_param = query2_parameters[param_name];
        if(!new_param)
          throw Error("Unknown param name " + param_name);

        existing_query_parameters[existing_query_parameters.length] = new_param;
        new_form_element = new_param.construct_html.apply(new_param, options);

        var remove_button = new_form_element.find("#" + param_name + "-remove");
        remove_button.click(function(e) {
          e.preventDefault();
          remove_query_parameter(param_name);
        });

        var add_params_select = $("#add-parameter-select");
        add_params_select.children().each(function(idx) {
          if($(this).attr("value") === param_name) {
            $(this).attr("disabled", true);
            return false;
          }
        });

        var params_form = $("#parameters-form");
        params_form.append(new_form_element);
      }

      /*
       * Empty the parameters form and clear and side-effects
       */
      function clear_query_parameters() {
        existing_query_parameters = []
        $("#parameters-form").empty();
        $("#add-parameter-select").children().each(function(idx) {
          $(this).attr("disabled", false);
        });
      }

      /*
       * Loads a saved query into the parameter list. Assumes that the
       * query has already been fetched from the server.
       *
       * The saved query should be at least of the form
       *
       * { parameters: [ { param_name: "unique_param_name", options: [] } ] }
       */
      function load_saved_query(saved_query) {
        clear_query_parameters();

        var params = saved_query.parameters;
        for(var i = 0, len = params.length; i < len; i++) {
          var param = params[i];

          append_query_parameter(param.param_name, param.options);
        }
      }

      function format_money(cents) {
        var str = "$" + Math.floor(cents / 100) + ".";

        var pennies = cents % 100;
        if(pennies !== 0) {
          if(pennies < 10)
            str += "0" + pennies;
          else
            str += pennies;
        }

        return str;
      }

      function format_time(time) {
        return new Date(time).toLocaleDateString();
      }

      function resolve_thread(callingA, loan_id) {
        callingA = $(callingA);
        if(callingA.data("resolving") == 1)
          return true;
        if(callingA.data("resolved") == 1)
          return true;
        callingA.data("resolving", 1);

        $.get('https://redditloans.com/api/get_creation_info.php', { loan_id: loan_id }, function(data, status) {
          console.log("retrieved loan creation info: ");
          console.log(JSON.stringify(data));

          callingA.data("resolved", 1);
          callingA.removeData("resolving");

          if(!data.results[loan_id]) {
            callingA.html("No thread found (code 0)");
          }else {
            var info = data.results[loan_id];
            if(info.type == 0) {
              callingA.attr("href", info.thread);
              callingA.html("Thread");
              window.open(info.thread, '_blank');
            }else {
              callingA.html("No thread found (code " + info.type.toString() + ")");
            }
          }
        }).fail(function(xhr) {
          var err_mess = 'Unknown Error';
          if("undefined" === typeof(xhr.responseJSON))
            err_mess = xhr.statusText;
          else
            err_mess = xhr.responseJSON.errors[0].error_message;
          callingA.html(errMess)
        });
        return false;
      }

      /*
       * Prepare the results table with the specified successful response
       * from /api/loans.php
       *
       * @param results this should be a LOANS_EXTENDED result
       */
      function load_results(results) {
        var wrap = $("#results-table-wrapper");
        var tab = $("#results-table");

        wrap.slideUp('fast', function() {
          var have_admin_info = false;
          var have_modified_info = false;
          if(results.loans.length > 0) {
            have_admin_info = results.loans[0].hasOwnProperty("deleted");
            have_modified_info = results.loans[0].hasOwnProperty("new_lender_id");
          }

          tab.empty();
          if(tab.parent().hasClass("bt-wrapper")) {
            tab.basictable('destroy');
          }

          var thead = $("<thead>");
          var tr = $("<tr>");
          tr.append("<th>ID</th>");
          tr.append("<th>Lender</th>");
          tr.append("<th>Borrower</th>");
          tr.append("<th>Principal</th>");
          tr.append("<th>Repayment</th>");
          tr.append("<th>Thread</th>");
          tr.append("<th>Unpaid?</th>");
          if(have_admin_info)
            tr.append("<th>Deleted?</th>");
          tr.append("<th>Created At</th>");
          if(have_admin_info) {
            tr.append("<th>Deleted At</th>");
            tr.append("<th>Deleted Reason</th>");
          }
          if(have_modified_info)
            tr.append("<th>Refresh</th>");

          thead.append(tr);
          tab.append(thead);

          var tbody = $("<tbody>");
          for(var i = 0, len = results.loans.length; i < len; i++) {
            var loan = results.loans[i];

            tr = $("<tr>");
            var td = $("<td>");
            td.attr("data-th", "ID");
            td.text(loan.loan_id.toString());
            tr.append(td);
            td = $("<td>");
            td.attr("data-th", "Lender");
            td.text(loan.lender_name);
            tr.append(td);
            td = $("<td>");
            td.attr("data-th", "Borrower");
            td.text(loan.borrower_name);
            tr.append(td);
            td = $("<td>");
            td.attr("data-th", "Principal");
            td.text(format_money(loan.principal_cents));
            tr.append(td);
            td = $("<td>");
            td.attr("data-th", "Repayment");
            td.text(format_money(loan.principal_repayment_cents));
            tr.append(td);
            td = $("<td>");
            var a = $("<a>");
            a.attr("href", "#");
            a.attr("onclick", "return resolve_thread(this, " + loan.loan_id + ");");
            a.text("Fetch thread");
            td.append(a);
            tr.append(td);
            td = $("<td>");
            td.attr("data-th", "Unpaid?");
            td.text((loan.unpaid ? "Yes" : "No"));
            tr.append(td);
            if(have_admin_info) {
              td = $("<td>");
              td.attr("data-th", "Deleted?");
              td.text((loan.deleted ? "Yes" : "No"));
              tr.append(td);
            }
            td = $("<td>");
            td.attr("data-th", "Created At");
            td.text(format_time(loan.created_at));
            tr.append(td);
            if(have_admin_info) {
              td = $("<td>");
              td.attr("data-th", "Deleted At");
              td.text(format_time(loan.deleted_at));
              tr.append(td);
              td = $("<td>");
              td.attr("data-th", "Deleted Reason");
              td.text(loan.deleted_reason ? loan.deleted_reason : "null");
              tr.append(td);
            }
            if(have_modified_info) {
              td = $("<td>");
              td.attr("data-th", "Refresh?");
              td.text("<i><b>REFRESH REQUIRED</b></i>");
              tr.append(td);
            }

            tbody.append(tr);
          }

          tab.append(tbody);
          tab.basictable({
            tableWrap: true,
            breakpoint: 991
          });
          wrap.slideDown('fast');
        });
      }

      /*
       * Fetches the saved queries from the server then loads them.
       *
       * This handles #saved-queries-status-text for user feedback
       */
      function fetch_and_load_saved_queries(succ_callback, fail_callback) {
        $.get("/api/my_saved_queries.php", {}, function(data, stat) {
          var queries = data.queries;
          setup_saved_queries(queries);

          for(var i = 0, len = queries.length; i < len; i++) {
            var query = queries[i];
            loaded_saved_queries[query.str_id] = query;
          }

          if(succ_callback)
            succ_callback();
        }).fail(function(xhr) {
          var statusText = $("#saved-queries-status-text");
          statusText.removeClass("alert-success").removeClass("alert-info");
          statusText.addClass("alert-danger");

          var err_mess = "Unknown error";
          if(xhr.status < 400 || xhr.status >= 500 || xhr.status === 404) {
            err_mess = "Strange response status code: " + xhr.statusText;
          }else if("object" === typeof(xhr.responseJSON)) {
            err_mess = xhr.responseJSON.errors[0].error_message;
          }

          statusText.html("<span class=\"glyphicon glyphicon-remove\"></span> Error loading saved queries: " + err_mess);

          if(fail_callback)
            fail_callback(err_mess);
        });
      }

      fetch_and_load_saved_queries();

      /*
       * Collects the query2_parameters into a format thats usable for
       * setup_add_parameters and calls it
       */
      function load_add_parameters() {
        params = [];

        for(var key in query2_parameters) {
          if(query2_parameters.hasOwnProperty(key)) {
            params[params.length] = query2_parameters[key];
          }
        }

        setup_add_parameters(params);
      }

      load_add_parameters();

      /// EVENT LISTENERS

      $("#saved-queries-form").submit(function(e) {
        e.preventDefault();

        var option = $("#saved-queries-select :selected");
        if(option.length === 0)
          return;

        var str_id = option.attr("value");
        var query = loaded_saved_queries[str_id];
        if("undefined" === typeof(query)) {
          console.log("failed to find a loaded saved query with identifier " + str_id + "!");
          return;
        }

        load_saved_query(query);
      });

      $("#delete-query-button").click(function(e) {
        e.preventDefault();

        var option = $("#saved-queries-select :selected");
        if(option.length === 0)
          return;

        var str_id = option.attr("value");

        var statusText = $("#saved-queries-status-text");
        statusText.slideUp('fast', function() {
          statusText.removeClass("alert-success").removeClass("alert-danger");
          statusText.addClass("alert-info");
          statusText.html("<span class=\"glyphicon glyphicon-refresh glyphicon-refresh-animate\"></span> Deleting..");

          statusText.slideDown('fast', function() {
            var params = { str_id: str_id };
            console.log(params);
            $.post("/api/delete_saved_query.php", params, function(data, stat) {
              statusText.fadeOut('fast', function() {
                statusText.html("<span class=\"glyphicon glyphicon-refresh glyphicon-refresh-animate\"></span> Reloading saved queries..");
                statusText.fadeIn('fast', function() {
                  fetch_and_load_saved_queries(function() {
                    statusText.fadeOut('fast', function() {
                      statusText.removeClass('alert-info');
                      statusText.addClass('alert-success');
                      statusText.html("<span class=\"glyphicon glyphicon-ok\"></span> Success!");
                      statusText.fadeIn('fast');
                      setTimeout(function() {
                        statusText.slideUp('fast');
                      }, 2000);
                    });
                  }, function(err_mess) {
                    statusText.fadeOut('fast', function() {
                      statusText.removeClass('alert-info');
                      statusText.addClass('alert-danger');
                      statusText.html("<span class=\"glyphicon glyphicon-remove\"></span> Failed to reload queries: " + err_mess);
                      statusText.fadeIn('fast');
                    });
                  });
                });
              });
            }).fail(function(xhr) {
              statusText.fadeOut('fast', function() {
                statusText.removeClass('alert-info');
                statusText.addClass('alert-danger');

                var err_mess = 'Unknown';
                if('object' === typeof(xhr.responseJSON))
                  err_mess = xhr.responseJSON.errors[0].error_message;
                else
                  err_mess = xhr.statusText;

                statusText.html("<span class=\"glyphicon glyphicon-remove\"></span> Failed to delete query: " + err_mess);
                statusText.fadeIn('fast');
              });
            });
          });
        });
      });

      $("#add-parameter-form").submit(function(e) {
        e.preventDefault();

        var option = $("#add-parameter-select :selected");
        if(option.length === 0)
          return;

        if(option.attr('disabled'))
          return;

        var param_name = option.attr('value');
        append_query_parameter(param_name, []);
      });

      $("#run-query-button").click(function(e) {
        e.preventDefault();

        var params = { format: 3 };
        var option = $("#saved-queries-select :selected");
        if(option.length === 0)
          return;

        var str_id = option.attr("value");
        var query = loaded_saved_queries[str_id];
        if("undefined" === typeof(query)) {
          console.log("failed to find a loaded saved query with identifier " + str_id + "!");
          return;
        }

        clear_query_parameters();
        for(var i = 0; i < query.parameters.length; i++) {
          var as_param = query2_parameters[query.parameters[i].param_name];
          as_param.send_params(params, as_param.options);
        }
        var statusText = $("#get-results-status-text");
        $.get("https://redditloans.com/api/loans.php", params, function(data, stat) {
          statusText.slideUp('fast');
          load_results(data);
        }).fail(function(xhr) {
          var errMess = 'Unknown';
          if("object" === typeof(xhr.responseJSON)) {
            errMess = xhr.responseJSON.errors[0].error_message;
          }else {
            errMess = xhr.statusText;
          }

          statusText.slideUp('fast', function() {
            statusText.removeClass('alert-info').removeClass('alert-success');
            statusText.addClass('alert-danger');
            statusText.html("<span class=\"glyphicon glyphicon-remove\"></span> " + errMess);
            statusText.slideDown('fast');
          });
        });
      });

      $("#get-results-form").submit(function(e) {
        e.preventDefault();

        var params = { format: 3 };
        for(var i = 0, len = existing_query_parameters.length; i < len; i++) {
          var param = existing_query_parameters[i];
          param.send_params(params);
        }
        console.log(params);
        var statusText = $("#get-results-status-text");
        $.get("https://redditloans.com/api/loans.php", params, function(data, stat) {
          statusText.slideUp('fast');
          load_results(data);
        }).fail(function(xhr) {
          var errMess = 'Unknown';
          if("object" === typeof(xhr.responseJSON)) {
            errMess = xhr.responseJSON.errors[0].error_message;
          }else {
            errMess = xhr.statusText;
          }

          statusText.slideUp('fast', function() {
            statusText.removeClass('alert-info').removeClass('alert-success');
            statusText.addClass('alert-danger');
            statusText.html("<span class=\"glyphicon glyphicon-remove\"></span> " + errMess);
            statusText.slideDown('fast');
          });
        });
      });

      $("#save-query-form").submit(function(e) {
        e.preventDefault();

        var statusText = $("#save-query-status-text");
        statusText.slideUp('fast', function(e) {
          statusText.removeClass('alert-success').removeClass('alert-danger');
          statusText.addClass('alert-info');
          statusText.html("<span class=\"glyphicon glyphicon-refresh glyphicon-refresh-animate\"></span> Saving...");
          statusText.slideDown('fast', function(e) {
            var nm = $("#save-query-name").val();
            var params = { name: nm };
            var options = [];

            for(var i = 0, len = existing_query_parameters.length; i < len; i++) {
              var param = existing_query_parameters[i];

              var tmp = [];
              tmp.push(param.param_name);
              tmp = tmp.concat(param.fetch_params());

              options.push(tmp);
            }
            params.params = options;

            console.log(params);
            $.post("/api/save_query.php", params, function(data, stat) {
              statusText.fadeOut('fast', function() {
                statusText.html("<span class=\"glyphicon glyphicon-refresh glyphicon-refresh-animate\"></span> Reloading queries..");
                statusText.fadeIn('fast', function() {
                  fetch_and_load_saved_queries(function() {
                    statusText.fadeOut('fast', function() {
                      statusText.removeClass('alert-info');
                      statusText.addClass('alert-success');
                      statusText.html("<span class=\"glyphicon glyphicon-ok\"></span> Success!");
                      statusText.fadeIn('fast');
                      setTimeout(function() {
                        statusText.slideUp('fast');
                      }, 2000);
                    });
                  }, function(err_mess) {
                    statusText.fadeOut('fast', function() {
                      statusText.removeClass('alert-info');
                      statusText.addClass('alert-danger');
                      statusText.html("<span class=\"glyphicon glyphicon-remove\"></span> Failed to reload queries: " + err_mess);
                      statusText.fadeIn('fast');
                    });
                  });
                });
              })
            }).fail(function(xhr) {
              statusText.fadeOut('fast', function() {
                var err_mess = "Unknown";
                if("object" === typeof(xhr.responseJSON)) {
                  err_mess = xhr.responseJSON.errors[0].error_message;
                }else {
                  err_mess = xhr.statusText;
                }

                statusText.removeClass('alert-info');
                statusText.addClass('alert-danger');
                statusText.html("<span class=\"glyphicon glyphicon-remove\"></span> Failed to save query: " + err_mess);
                statusText.fadeIn('fast');
              });
            });
          });
        });
      });
    </script>
  </body>
</html>
<?php
  $sql_conn->close();
?>
