<?php
  include_once('api/auth.php');

  if (!is_trusted()) {
    on_failed_auth();
    return;
  }
?>
<!doctype html>
<html lang="en">
  <head>
    <title>RedditLoans</title>
    <?php include('metatags.php'); ?>

    <?php include('bootstrap_css.php'); ?>
    <link rel="stylesheet" href="/css/basictable.css">

    <style>
      .report {
        padding-top: 9px;
        padding-bottom: 9px;
      }

      .red_flag_reports {
        padding-top: 9px;
      }

      .report_flags_header {
        padding-top: 9px;
      }
    </style>
  </head>
  <body>
    <?php include('navigation.php'); ?>
    <div class="container px-2 py-5">
      <section>
        <h1>Red Flag Report - Search</h1>
        <p>Not sure why someone isn't showing up? Check the <a href="red_flag_queue.php">queue</a> to see if its being processed</p>

        <div class="container-fluid alert" id="statusText" style="display: none"></div>
        <form id="rfrs-username-form">
          <div class="form-group row">
            <input type="text" class="form-control" id="username" aria-label="Username" placeholder="Username" aria-describedby="usernameHelpBlock">
            <small id="usernameHelpBlock" class="form-text text-muted">The username you want to fetch reports for.</small>
          </div>
          
          <div class="form-group row">
            <button id="submit-button" type="submit" class="col-auto btn btn-primary">Search</button>
            <button id="request-button" type="button" class="col-auto btn btn-danger">Request Report</button>
          </div>
        </form> 
      </section>
      <section>
        <div id="red_flag_reports">
        </div>
      </section>
    </div>
    <?php include('bootstrap_js.php') ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.21.0/moment-with-locales.min.js"></script>
    <script src="js/jquery.basictable.min.js"></script>
    <script src="js/status_text_utils.js"></script>
    <script type="text/javascript">
      $(function () {
        $('[data-toggle="tooltip"]').tooltip();
      });
      
      /**
       * Clear any reports that are visible
       */
      function clear_reports() {
        $("#red_flag_reports").empty();
      }

      /** 
       * Adds a header that indicates reports past this point are old. It wasn't sufficiently
       * obvious with just the hline. 
       *
       * The sort order is provided by the get API
       */
      function add_older_header() {
        var my_div = $("<h2>Older Reports</h2>");
        my_div.attr('style', 'display: none');
        $("#red_flag_reports").append(my_div);
        my_div.slideDown('fast');
      }
      
      /**
       * Add the report to the list of reports.
       * @param report the result from api/red_flag_report
       */
      function add_report(report) {
        var my_div = $("<div>");
        my_div.attr('style', 'display: none');
        my_div.addClass('report');

        if(!report.success) {
          my_div.addClass('report-fail');
          my_div.text('Failed to fetch report: ' + report.errors[0].error_type + " - " + report.errors[0].error_message);
          $("#red_flag_reports").append(my_div);
          my_div.slideDown('fast');
          return;
        }
        
        var header = $("<h3>");
        header.text("Summary");
        header.addClass('report_summary_header');
        my_div.append(header);;
        var my_tabl = $("<table>");

        var thead = $("<thead>");
        var tr = $("<tr>");
        tr.append("<th>Username</th>");
        tr.append("<th>Created At</th>");
        tr.append("<th>Started At</th>");
        tr.append("<th>Completed At</th>");
        thead.append(tr);
        my_tabl.append(thead);

        var tbody = $("<tbody>");
        tr = $("<tr>");
        var td = $("<td>");
        td.text(report.username);
        td.attr('data-th', 'Username');
        tr.append(td);

        td = $("<td>");
        td.text(moment(report.created_at).format('LLLL'));
        td.attr('data-th', 'Created At');
        tr.append(td);

        td = $("<td>");
        td.text(moment(report.started_at).format('LLLL'));
        td.attr('data-th', 'Started At');
        tr.append(td);

        td = $("<td>");
        td.text(moment(report.completed_at).format('LLLL'));
        td.attr('data-th', 'Completed At');
        tr.append(td);
        tbody.append(tr);

        my_tabl.append(tbody);
        my_tabl.basictable({
          tableWrap: true,
          breakpoint: 991
        });
        my_tabl.addClass('w-100');

        my_div.append(my_tabl);
        
        
        var header = $("<h3>");
        header.text("Flags");
        header.addClass('report_flags_header');
        my_div.append(header);;

        my_tabl = $("<table>");
        
        thead = $("<thead>");
        tr = $("<tr>");
        tr.append("<th>Type</th>");
        tr.append("<th>Identifier</th>");
        tr.append("<th>Description</th>");
        tr.append("<th>Count</th>");
        thead.append(tr);
        my_tabl.append(thead);

        tbody = $("<tbody>");
        for(var i = 0; i < report.flags.length; i++) {
          var row = report.flags[i];
          tr = $("<tr>");

          td = $("<td>");
          td.text(row.type);
          td.attr('data-th', 'Type');
          tr.append(td);

          td = $("<td>");
          td.text(row.identifier);
          td.attr('data-th', 'Identifier');
          tr.append(td);

          td = $("<td>");
          td.text(row.description);
          td.attr('data-th', 'Description');
          tr.append(td);

          td = $("<td>");
          td.text(row.count);
          td.attr('data-th', 'Count');
          tr.append(td);

          tbody.append(tr);
        }

        my_tabl.append(tbody);
        my_tabl.basictable({
          tableWrap: true,
          breakpoint: 991
        });
        my_tabl.addClass('w-100');
        
        my_div.append(my_tabl);
        my_div.append("<hr>");
        $("#red_flag_reports").append(my_div);
        my_div.slideDown('fast');
      }

      $("#rfrs-username-form").on('submit', function(e) {
        e.preventDefault();
        clear_reports(); 
        var status_text = $("#statusText");
        var username = $("#username").val();
        set_status_text(status_text, LOADING_GLYPHICON + ' Fetching list of reports...', 'info', true, 200); 
        $.post("/api/red_flag_reports.php", { username: username }, function(data, stat) {
          for(var i = 0; i < data.reports.length; i++) {
            (function(i) {
              $.post('/api/red_flag_report.php', { id: data.reports[i].id }, function(data, stat) {
                if(i == 1) {
                  add_older_header();
                }
                add_report(data);
              }).fail(function(xhr) {
                console.log(xhr.responseJSON);
                var json_resp = xhr.responseJSON;
                var err_type = json_resp.errors[0].error_type;
                var err_mess = json_resp.errors[0].error_message;
                console.log(err_type + ": " + err_mess);
                set_status_text(status_text, FAILURE_GLYPHICON + ' Failed fetching report ' + (i + 1) + ': ' + err_mess, 'danger', true, 200);  
                add_report(xhr.responseJSON);
              });
            })(i);
          }
        }).fail(function(xhr) {
          console.log(xhr.responseJSON);
          var json_resp = xhr.responseJSON;
          var err_type = json_resp.errors[0].error_type;
          var err_mess = json_resp.errors[0].error_message;
          console.log(err_type + ": " + err_mess);
          set_status_text(status_text, FAILURE_GLYPHICON + " " + err_mess, 'danger', true, 20200);  
        });
      });

      $("#request-button").on('click', function(e) {
        e.preventDefault();
        clear_reports();
        var status_text = $("#statusText");
        var username = $("#username").val();

        set_status_text(status_text, LOADING_GLYPHICON + ' Requesting report...', 'info', true, 200);
        $.post('/api/request_red_flag_report.php', { username: username }, function(data, stat) {
          set_status_text(status_text, SUCCESS_GLYPHICON + ' Requested report. See the queue for progress. Recall that it will only generate reports at most once per month per user.', 'success', true, 20200); 
        }).fail(function(xhr) {
          console.log(xhr.responseJSON);
          var json_resp = xhr.responseJSON;
          var err_type = json_resp.errors[0].error_type;
          var err_mess = json_resp.errors[0].error_message;
          console.log(err_type + ": " + err_mess);
          set_status_text(status_text, FAILURE_GLYPHICON + " " + err_mess, 'danger', true, 20200);  
        });
      });
    </script>
  </body>
</html>
<?php
  $sql_conn->close();
?>
