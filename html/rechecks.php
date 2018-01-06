<?php
  include_once('api/auth.php');
  require_once('database/helper.php');

  if(!is_trusted()) {
    on_failed_auth();

    if(is_moderator()) 
      var_dump($logged_in_user);
    return;
  }
?>
<!doctype html>
<html lang="en">
  <head>
    <title>RedditLoans</title>
    <?php include('metatags.php'); ?>

    <?php include('bootstrap_css.php'); ?>
  </head>
  <body>
    <?php include('navigation.php'); ?>
    <div class="container px-2 py-5">
      <h1>Rechecks</h1>
      <p>Users who have achieved trusted access or higher (which requires either 5 loans completed as lender or approval from Tjstretchalot) have access to this page. From here you can:</p>
      <ul>
        <li>See if / when the LoansBot viewed a particular comment</li>
        <li>Make the LoansBot forget it already processed a particular comment</li>
        <li>Ask the LoansBot to process a comment you've made it forget, or a comment it simply missed</li>
      </ul>

      <p>Note that while the term "comment" is used throughout this page, note that this page works for comments and threads.</p>

      <h2>Acquiring thing fullnames</h2>
      <p>An important concept for using this page is underestanding that comments and links on reddit have "ids" attached to them, which are visible in the URL that you use to view the link. That id is combined with a custom prefix based on the type of thing to get its fullname. For comments, this is the prefix "t1_", which is most easily remembered with the idea that comments are the most important thing on reddit. For threads this is "t3_". For those curious, accounts have the prefix "t2_".</p>

      <h3>Example - Comment</h3>
      <p>Go to the permalink of a comment, and look at your URL. It should look something like:</p>
      <div style="font-family: monospace;">https://www.reddit.com/r/LoansBot/comments/2ea912/loansbot_basic_usage/<b>ck2aa1k</b>/</div>
      <p>The important part of the comment id is at the end, in bold. This is the comment <em>id</em>. We prefix with "t1_" to get the comment fullname: <strong>t1_ck2aa1k</strong></p>
      <p>You can also paste the <i>permalink</i> in this form:</p>
      <div class="container-fluid alert" id="parse-comment-fullname-status-text" style="display: none"></div>
      <form id="parse-comment-fullname-form">
        <div class="form-group row">
          <input type="text" class="form-control" id="permalink" aria-label="Thread Permalink" placeholder="Permalink" aria-describedby="permalinkHelpBlock" required>
          <small id="permalinkHelpBlock" class="form-text text-muted">This needs to be the comment permalink as shown in the example above.</small>
        </div>
        <div class="form-group row">
          <button id="comment-submit-button" type="submit" class="col-auto btn btn-primary">Get Fullname</button>
        </div>
        <div class="form-group row">
          <input type="text" class="form-control" id="comment-fullname" aria-label="Comment Fullname Output" placeholder="The fullname will be parsed and placed here" aria-describedby="commentFullnameHelpBlock" disabled>
          <small id="commentFullnameHelpBlock" class="form-text text-muted">This will be filled with the fullname of the comment when you press "Get Fullname"</small>
        </div>
      </form>

      <h3>Example - Thread</h3>
      <p>Go to the permalink of a thread, and look at your URL. It should look something like:</p>
      <div style="font-family: monospace">https://www.reddit.com/r/LoansBot/comments/<b>2ea912</b>/loansbot_basic_usage/</div>
      <p>The important of the thread is is just prior to the shortened thread title, in bold. We prefix with "t3_" to get the thread fullname: <strong>t3_2ea912</strong></p>
      <p>You can also paste the <i>thread url</i> in this form:</p>
      <div class="container-fluid alert" id="parse-thread-fullname-status-text" style="display: none"></div>
      <form id="parse-thread-fullname-form">
        <div class="form-group row">
          <input type="text" class="form-control" id="thread-link" aria-label="Thread Permalink" placeholder="Thread URL" aria-describedby="threadLinkHelpBlock" required>
          <small id="threadLinkHelpBlock" class="form-text text-muted">This needs to be the thread link as shown in the example above.</small>
        </div>
        <div class="form-group row">
          <button id="thread-submit-button" type="submit" class="col-auto btn btn-primary">Get Fullname</button>
        </div>
        <div class="form-group row">
          <input type="text" class="form-control" id="thread-fullname" aria-label="Comment Fullname Output" placeholder="The fullname will be parsed and placed here" aria-describedby="commentFullnameHelpBlock" disabled>
          <small id="commentFullnameHelpBlock" class="form-text text-muted">This will be filled with the fullname of the link when you press "Get Fullname"</small>
        </div>
      </form>

      <h2>Determine if the LoansBot missed a comment</h2>
      <p>This form can be used to determine if the LoansBot has already looked at a comment. Typically if this is true and the LoansBot did not respond, the comment was malformed at the time the LoansBot looked at it. If you have since edited the comment you will likely need to make the LoansBot forget about parsing the given comment (see below).</p>

      <div class="container-fluid alert" id="check-if-seen-status-text" style="display: none"></div>
      <form id="check-if-seen-form">
        <div class="form-group row">
          <input type="text" class="form-control" id="check-if-seen-fullname" aria-label="Thing Fullname" placeholder="Thing Fullname" aria-describedby="checkIfSeenFullnameHelpBlock" required>
          <small id="checkIfSeenFullnameHelpBlock" class="form-text text-muted">This needs to be a comment or thread fullname (such as t1_xxx or t3_xxx).</small>
        </div>
        <div class="form-group row">
          <button id="check-if-seen-submit-button" type="submit" class="col-auto btn btn-primary">Query Database</button>
        </div>
      </form>
      
      <h2>Make the LoansBot Recheck a Comment</h2>
      <p>This form serves two distinct purposes, however they are time-sensisitive. First, you can make the LoansBot forget it processed a comment. Second, you can request that
      the LoansBot recheck a comment it has not already processed. Using both of these at the same time will ensure that the LoansBot processes the most up to date version of
      the comment during its next recheck cycle.</p>

      <div class="container-fluid alert" id="make-recheck-status-text" style="display: none"></div>
      <form id="make-recheck-form">
        <div class="form-group row">
          <input type="text" class="form-control" id="make-recheck-fullname" aria-label="Thing Fullname" placeholder="Thing FUllname" aria-describedby="makeRecheckFullnameHelpBlock" required>
          <small id="makeRecheckFullnameHelpBlock" class="form-text text-muted">This needs to be a comment or thread fullname (such as t1_xxx or t3_xxx)</small>
        </div>
        <div class="form-group row">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" value="" id="make-recheck-forget-cb">
            <label class="form-check-label" for="make-recheck-forget-cb" checked>
              Make Forget
            </label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" value="" id="make-recheck-recheck-cb">
            <label class="form-check-label" for="make-recheck-recheck-cb" checked>
              Queue Recheck
            </label>
          </div>
        </div>
      </form>
    </div>
    <?php include('bootstrap_js.php') ?>
    <script src="js/status_text_utils.js"></script>
    <script src="js/rechecks.js"></script>
  </body>
</html>
<?php
  $sql_conn->close();
?>
