<?php
  include_once('api/auth.php');

  if(!is_moderator()) {
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
    <link rel="stylesheet" href="css/logs.css">
  </head>
  <body>
    <?php include('navigation.php'); ?>
    <div class="container px-2 py-5">
      <section>
        <div class="container-fluid alert" id="status-text" style="display: none"></div>

        <form id="controls-form">
          <div class="form-group row">
            <button id="download-button" type="button" class="col-auto btn btn-primary">Download</button>
            <button id="fetch-latest-button" type="button" class="col-auto btn btn-secondary">Fetch latest</button>
          </div>
        </form>
        <ul id="log-list">
        </ul>
      </section>
    </div>
    <?php include('bootstrap_js.php') ?>
    <script src="js/moment.js"></script>
    <script src="js/status_text_utils.js"></script>
    <script type="text/javascript">
      var latest_raw = null;

      // Return promise for raw log file
      function fetch_raw() {
        return new Promise(function(resolve, reject) {
          $.get( "https://redditloans.com/api/logs.php", {}, function(data, stat) {
            resolve(data);
          }).fail(function(xhr) {
            var err_mess = 'Unknown';
            if(typeof(xhr.responseJSON) !== 'undefined') {
              var json_resp = xhr.responseJSON;
              var err_type = json_resp.errors[0].error_type;
              var err_mess = json_resp.errors[0].error_message;
            }else {
              err_mess = xhr.status + ": " + xhr.statusText;
            }
            reject(err_mess);
          });
        });
      }

      // Return promise for raw log file
      function fetch_raw_with_status_text() {
        return new Promise(function(resolve, reject) {
          var st_div = $("#status-text");
          set_status_text(st_div, LOADING_GLYPHICON + ' Fetching raw log file..', 'info', true).then(function() {
            fetch_raw().then(function(data) {
              set_status_text(st_div, SUCCESS_GLYPHICON + ' Success!', 'success', true).then(function() {
                resolve(data);
              });
            }, function(err_mess) {
              set_status_text(st_div, FAILURE_GLYPHICON + ' Error: ' + err_mess, 'danger', false).finally(function() {
                reject(err_mess);
              });
            });
          });
        });
      }

      // parses one raw line into
      // { timestamp: Date, type: text, level: string, file: string, text: string }
      function parse_raw_line(line) {
        // starts with something like 2018-Jan-13 16:36:12 PM
        // always 23 characters
        if(line.length < 24)
          return null; // malformed
        var timestamp_str = line.slice(0, 23);
        var timestamp = moment(timestamp_str + " +00:00", "YYYY-MMM-DD hh:mm:ss A ZZ").toDate();
        if(isNaN(timestamp.getTime()))
          return null; // malformed

        var ch_ind = 25;
        while(line[ch_ind] != ']') {
          if(ch_ind >= line.length)
            return null; // malformed
          ch_ind++;
        }
        var type = line.slice(25, ch_ind);

    
        ch_ind += 2; // skip space
        var level_start = ch_ind;
        while(line[ch_ind] != ' ') {
          if(ch_ind >= line.length)
            return null; // malformed

          ch_ind++;
        }

        var level = line.slice(level_start, ch_ind);
        
        ch_ind += 1;
        var file_start = ch_ind;
        while(line[ch_ind] != ' ') {
          if(ch_ind >= line.length)
            return null; // malformed
          
          ch_ind++;
        }

        var file = line.slice(file_start, ch_ind);
        ch_ind++; // first character past the space
        var text = line.slice(ch_ind);
        return { timestamp: timestamp, type: type, level: level, file: file, text: text };
      }

      // returns a promise to set ul to raw
      function set_ul_to_raw(raw) {
        return new Promise(function(resolve, reject) {
          var ul = $("#log-list");
          ul.slideUp('fast', function() {
            ul.empty();
            var spl_on_line = raw.split("\n");
            for(var i = 0, len = spl_on_line.length; i < len; i++) {
              var raw_line = spl_on_line[i];
              var parsed = parse_raw_line(raw_line);
              if(parsed === null)
                continue;
              var li = $("<li>");
              li.addClass("level-" + parsed.level.toLowerCase());
              var time = $("<span>");
              time.addClass("short-timestamp");
              time.attr("data-toggle", "tooltip");
              time.attr("title", parsed.timestamp.toLocaleString());
              time.html(parsed.timestamp.toLocaleTimeString());
              li.append(time);
              var text = $("<span>");
              text.addClass("log-message");
              text.html(parsed.text);
              text.attr("data-toggle", "tooltip");
              text.attr("title", raw_line);
              li.append(text);
              ul.append(li);
              time.tooltip();
              text.tooltip();
            }
            ul.slideDown('fast', function() {
              resolve(true);
            });
          });
        });
      }

      // returns a promise to set ul to raw
      function set_ul_to_raw_with_status_text(raw) {
        return new Promise(function(resolve, reject) {
          var st_div = $("#status-text");
          set_status_text(st_div, LOADING_GLYPHICON + ' Parsing raw log file..', 'info', true).then(function() {
            set_ul_to_raw(raw).then(function() {
              set_status_text(st_div, SUCCESS_GLYPHICON + ' Success!', 'success', true).then(function() {
                resolve(true);
              });
            }, function(reject_reason) {
              reject(reject_reason)
            });
          });
        });
      }

      $("#fetch-latest-button").on('click', function(e) {
        e.preventDefault();

        var b = $("#controls-form div button");
        b.attr("disabled", true);
        fetch_raw_with_status_text().then(function(raw) {
          latest_raw = raw;
          set_ul_to_raw_with_status_text(latest_raw).finally(function() {
            b.attr("disabled", false);
          });
        });
      });

      $("#download-button").on('click', function(e) {
        e.preventDefault();

        var st_div = $("#status-text");
        if(typeof(latest_raw !== 'string')) {
          set_status_text(st_div, FAILURE_GLYPHICON + ' No logs loaded! Press fetch latest', 'danger', true);
        }

        set_status_text(st_div, LOADING_GLYPHICON + ' Downloading..', 'info', true).then(function() {
          // modified from https://stackoverflow.com/questions/3665115/create-a-file-in-memory-for-user-to-download-not-through-server
          var element = document.createElement('a');
          element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(latest_raw));
          element.setAttribute('download', 'logs.txt');

          element.style.display = 'none';
          document.body.appendChild(element);

          element.click();

          document.body.removeChild(element);
          return set_status_text(st_div, SUCCESS_GLYPHICON + ' Success!', 'success', true);
        }); 
      });
      
    </script>
  </body>
</html>
<?php
  $sql_conn->close();
?>