<?php
  include_once('connect_and_get_loggedin.php');

  if(!isset($logged_in_user) || $logged_in_user === null || $logged_in_user->auth < 5) {
    http_response_code(403);
    $sql_conn->close();
    return;
  }
?>
<!doctype html>
<html lang="en">
  <head>
    <title>RedditLoans</title>
    <?php include('metatags.php'); ?>

    <?php include('bootstrap_css.php'); ?>
    <link rel="stylesheet" href="/css/query2.css">
  </head>
  <body>
    <?php include('navigation.php'); ?>
    <div class="container px-2 py-5">
      <div class="container-fluid alert" id="statusText" style="display: none"></div>
      <section>
        <div class="container-fluid alert" id="saved-queries-status-text" style="display: none"></div>
        <form id="saved-queries-form" class="form-inline">
          <div class="form-group justify-content-between">
            <select class="form-control col-sm" id="saved-queries-select">
              <option value="loading">Loading...</option>
            </select>
            <button type="submit" class="btn btn-primary col-sm">Load Query</button>
          </div>
        </form>
      </section>
      <section>
        <form id="parameters-form">
        </form>
        <form id="add-parameter-form">
          <div class="form-group">
            <select class="form-control col-sm" id="add-parameter-select">
              <option>Loading...</option>
            </select>
            <button type="submit" class="btn btn-primary col-sm">Add Parameter</button>
          </div>
        </form>
        <div class="container-fluid alert" id="save-query-status-text" style="display: none"></div>
        <form id="save-query-form">
          <div class="form-group">
            <input type="text" class="form-control col-sm" id="save-query-name" aria-label="Name for this query" placeholder="Name for this query">
            <button type="submit" class="btn btn-primary col-sm">Save Query</button>
          </div>
        </form>
        <div class="container-fluid alert" id="get-results-status-text" style="display: none"></div>
        <form id="get-results-form">
          <div class="form-group">
            <button type="submit" class="btn btn-primary btn-block">Get Results</button>
          </div>
        </form>
      </section>
      <section>
        <table id="results-table">
        </table>
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
          select.append("<option></option>").attr("value", saved_query.str_id).text(saved_query.name);
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
          ele.data("param-name", valid_param.param_name);
          ele.text(valid_param.name);
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
          if($(this).data("param-name") === param_name) {
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
        for(var i = 0, len = existing_query_parameters.length(); i < len; i++) {
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
          if($(this).data("param-name") === param_name) {
            $(this).attr("disabled", true);
            return false;
          }
        });

        var params_form = $("#parameters-form");
        params_form.add(new_form_element);
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
        var str = "$" + math.floor(cents / 100) + ".";
        
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

      /*
       * Prepare the results table with the specified successful response
       * from /api/loans.php
       *
       * @param results this should be a LOANS_EXTENDED result
       */
      function load_results(results) {
        var tab = $("#results-table");
        tab.slideUp('fast', function() {
          var have_admin_info = false;
          var have_modified_info = false;
          if(results.loans.length > 0) {
            have_admin_info = results.loans[0].hasOwnProperty("deleted");
            have_modified_info = results.loans[0].hasOwnProperty("new_lender_id");
          }

          tab.empty();
          
          var thead = $("<thead>");
          var tr = $("<tr>");
          tr.add("<th>ID</th>");
          tr.add("<th>Lender</th>");
          tr.add("<th>Borrower</th>");
          tr.add("<th>Principal</th>");
          tr.add("<th>Repayment</th>");
          tr.add("<th>Unpaid?</th>");
          if(have_admin_info)
            tr.add("<th>Deleted?</th>");
          tr.add("<th>Created At</th>");
          if(have_admin_info) {
            tr.add("<th>Deleted At</th>");
            tr.add("<th>Deleted Reason</th>");
          }
          if(have_modified_info)
            tr.add("<th>Refresh</th>");
          
          thead.add(tr);
          tab.add(thead);

          var tbody = $("<tbody>");
          for(var i = 0, len = results.loans.length; i < len; i++) {
            var loan = results.loans[i];

            tr = $("<tr>");
            var td = $("<td>");
            td.text(loan.loan_id.toString());
            tr.add(td);
            td = $("<td>");
            td.text(loan.lender_name);
            tr.add(td);
            td = $("<td>");
            td.text(loan.borrower_name);
            tr.add(td);
            td = $("<td>");
            td.text(format_money(loan.principal_cents));
            tr.add(td);
            td = $("<td>");
            td.text(format_money(loan.principal_repayment_cents));
            tr.add(td);
            td = $("<td>");
            td.text((loan.unpaid ? "Yes" : "No"));
            tr.add(td);
            if(have_admin_info) {
              td = $("<td");
              td.text((loan.deleted ? "Yes" : "No"));
              tr.add(td);
            }
            td = $("<td>");
            td.text(format_time(loan.created_at));
            tr.add(td);
            if(have_admin_info) {
              td = $("<td>");
              td.text(format_time(loan.deleted_at));
              tr.add(td);
              td = $("<td>");
              td.text(loan.deleted_reason ? loan.deleted_reason : "null");
              tr.add(td);
            }
            if(have_modified_info) {
              td = $("<td>");
              td.text("<i><b>REFRESH REQUIRED</b></i>");
              tr.add(td);
            }

            tbody.add(tr);
          }

          tab.add(tbody);
          tab.basictable({
            tableWrapper: true
          });
          tab.slideDown('fast');
        });
      }

      /*
       * Fetches the saved queries from the server then loads them.
       *
       * This handles #saved-queries-status-text for user feedback
       */
      function fetch_and_load_saved_queries() {
        $.get("/api/my_saved_queries.php", {}, function(data, stat) {
          var queries = data.queries;
          setup_saved_queries(queries);

          for(var i = 0, len = queries.length; i < len; i++) {
            var query = queries[i];
            loaded_saved_queries[query.str_id] = query;
          }
        }).fail(function(xhr) {
          var statusText = $("#saved-queries-status-text");
          statusText.removeClass("alert-success").removeClass("alert-info");
          statusText.addClass("alert-danger");
          
          var err_mess = "Unknown error";
          if(xhr.status < 400 || xhr.status >= 500 || xhr.status === 404) {
            err_mess = "Strange response status code: " + xhr.statusText;
          }else if(xhr.responseType === "json") {
            err_mess = xhr.responseJSON.errors[0].error_message;
          }

          statusText.html("<span class=\"glyphicon glyphicon-remove\"></span> Error loading saved queries: " + err_mess);
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

        var param_name = option.attr("value");
        var query = loaded_saved_queries[param_name];
        if("undefined" === typeof(query)) {
          console.log("failed to find a loaded saved query named " + param_name + "!");
          return;
        }

        load_saved_query(query);
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

      $("#get-results-form").submit(function(e) {
        e.preventDefault();

        var params = {}
        for(var i = 0, len = existing_query_parameters.length; i < len; i++) {
          var param = existing_query_parameters[i];
          param.send_params(params);
        }

        $.get("https://redditloans.com/api/loans.php", params, function(data, stat) {
          load_results(data.loans);
        }).fail(function(xhr) {
          var errMess = 'Unknown';
          if(xhr.responseType === 'json') {
            errMess = xhr.responseJSON.errors[0].error_message;
          }else {
            errMess = xhr.statusText;
          }

          var statusText = $("#get-results-status-text");
          statusText.slideUp('fast', function() {
            statusText.removeClass('alert-info').removeClass('alert-success');
            statusText.addClass('alert-danger');
            statusText.html("<span class=\"glyphicon glyphicon-remove\"></span> " + errMess);
            statusText.slideDown('fast');
          });
        });
      });
    </script>
  </body>
</html>
<?php
  $sql_conn->close();
?>
