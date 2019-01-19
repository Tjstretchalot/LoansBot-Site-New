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
    <title>RedditLoans - Promo Blacklist</title>
    <?php include('metatags.php'); ?>

    <?php include('bootstrap_css.php'); ?>
    <link rel="stylesheet" href="/css/basictable.css">
    <link rel="stylesheet" href="/css/query2.css">
  </head>
  <body>
    <?php include('navigation.php'); ?>
    <div class="container px-2 py-5">

      <h1>Promotion Blacklist</h1>
      <p>This page can be used to manage the promotion blacklist. Users on this list are never given
        additional permissions on the website and are not invited to lenderscamp.</p>

      <h2>Search</h2>
      <div class="container-fluid alert" id="status-text-search" style="display: none"></div>

      <form class="form-inline" id="search-form">
        <label class="sr-only" for="search-username">Username</label>
        <input type="text" class="form-control mr-sm-2 mb-2" id="search-username" placeholder="Username">

        <label class="sr-only" for="search-limit">Maximum number of results</label>
        <input type="number" class="form-control mr-sm-2 mb-2" id="search-limit" placeholder="Maximum # results" value="10">

        <button type="submit" class="btn btn-primary mb-2 mr-sm-2" id="search-get">Fetch Page 1</button>
        <button type="button" class="btn btn-secondary mb-2 mr-sm-2" id="search-get-prev" disabled>Prev Page</button>
        <button type="button" class="btn btn-secondary mb-2 mr-sm-2" id="search-get-next" disabled>Next Page</button>
      </form>

      <div id="results-table-wrapper" style="display: none">
        <table id="results-table">
        </table>
      </div>

      <h2>Add</h2>
      <div class="container-fluid alert" id="status-text-add" style="display: none"></div>

      <h2>Remove</h2>
      <div class="container-fluid alert" id="status-text-remove" style="display: none"></div>
    </div>
    <?php include('bootstrap_js.php') ?>
    <script src="js/jquery.basictable.min.js"></script>
    <script src="js/status_text_utils.js"></script>
    <script src="js/moment.js"></script>
    <script type="text/javascript">
      var paginate_prev = null;
      var paginate_next = null;

      function get_page(username, min_id, max_id, limit) {
        var hiddenPromise = null;
        var wrap = $("#results-table-wrapper");
        if(wrap.is(":visible")) {
          hiddenPromise = new Promise(function(resolve, reject) { wrap.fadeOut(resolve); });
        }else {
          hiddenPromise = new Promise(function(resolve, reject) { resolve() });
        }

        var st_div = $('#status-text-search');
        var tab = $("#results-table");
        hiddenPromise.then(function() {
          tab.empty();
          if(tab.parent().hasClass("bt-wrapper")) {
            tab.basictable('destroy');
          }
        });

        $.get('https://redditloans.com/api/get_promotion_blacklist.php', { username: username, min_id: min_id, max_id: max_id, limit: limit }, function(data, stat) {
          hiddenPromise.then(function() {
              var thead = $("<thead>");
              var tr = $("<tr>");
              tr.append("<th>Username</th>");
              tr.append("<th>Mod Username</th>");
              tr.append("<th>Reason</th>");
              tr.append("<th>Added At</th>");
              thead.append(tr);
              tab.append(thead);

              var tbody = $("<tbody>");

              paginate_prev = null;
              paginate_next = null;
              for(var i = 0, len = data.list.length; i < len; i++) {
                var user = data.list[i];
                if(paginate_prev === null || user.id < paginate_prev) {
                  paginate_prev = user.id;
                }
                if(paginate_next === null || user.id > paginate_next) {
                  paginate_next = user.id;
                }

                tr = $("<tr>");
                var td = $("<td>");
                td.attr("data-th", "Username");
                td.text(user.username);
                tr.append(td);

                td = $("<td>");
                td.attr("data-th", "Mod");
                td.text(user.mod_username);
                tr.append(td);

                td = $("<td>");
                td.attr("data-th", "Reason");
                td.text(user.reason);
                tr.append(td);

                td = $("<td>");
                td.attr("data-th", "Added");
                td.text(moment.unix(user.added_at).calendar());
                tr.append(td);

                tbody.append(tr);
              }

              tab.append(tbody);
              tab.basictable({
                tableWrap: true,
                breakpoint: 991
              });
              wrap.slideDown('fast');

              if(limit === null) {
                $("#search-get-prev").attr("disabled", true);
                $("#search-get-next").attr("disabled", true);
              }else {
                if(data.list.length < limit) {
                  $("#search-get-prev").attr("disabled", min_id === null);
                  $("#search-get-next").attr("disabled", max_id === null);
                }else {
                  $("#search-get-prev").attr("disabled", min_id === null && max_id === null);
                  $("#search-get-next").attr("disabled", false);
                }
              }
          });
        }).fail(function(xhr) {
          set_status_text_from_xhr(st_div, xhr);
        });
      }

      $("#search-get").click(function(e) {
        e.preventDefault();

        username = $("#search-username").val().trim();
        if(username.length === 0) { username = null; }

        limit = parseInt($("#search-limit").val().trim());
        if(isNaN(limit)) { limit == null; }

        get_page(username, null, null, limit);
      });

      $("#search-get-next").click(function(e) {
        e.preventDefault();

        username = $("#search-username").val().trim();
        if(username.length === 0) { username = null; }

        limit = parseInt($("#search-limit").val().trim());
        if(isNaN(limit)) { limit == null; }

        get_page(username, paginate_next + 1, null, limit);
      });

      $("#search-get-prev").click(function(e) {
        e.preventDefault();

        username = $("#search-username").val().trim();
        if(username.length === 0) { username = null; }

        limit = parseInt($("#search-limit").val().trim());
        if(isNaN(limit)) { limit == null; }

        get_page(username, null, paginate_prev - 1, limit);
      });
    </script>
  </body>
</html>
<?php
  $sql_conn->close();
?>
