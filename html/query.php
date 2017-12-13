<html>
  <head>
    <title>RedditLoans - Query</title>

    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Open+Sans:400,300,400italic,700,700italic">
    <link rel="stylesheet" type="text/css" href="/css/query.css">
  </head>
  <body>
    <div class="container-fluid">
      <div class="row" id="temp-navbar">
        <div class="col-xs-12"><a href="index.php" class="btn btn-primary">Back to Index Page</a></div>
      </div>
      <div class="row" id="select-database">
        <div class="col-xs-offset-0 col-xs-12 col-lg-4" id="select-database-text">
          <h1>Database</h1>
	</div>
        <div class="col-xs-offset-0 col-xs-12 col-lg-8 btn-group" data-toggle="buttons" id="database-button-group">
          <label class="btn btn-primary active">
            <input type="radio" name="database" id="database-users" autocomplete="off" checked>Users</input>
          </label>
          <label class="btn btn-primary">
            <input type="radio" name="database" id="database-loans" autocomplete="off">Loans</input>
          </label>
          <label class="btn btn-primary">
            <input type="radio" name="database" id="database-usernames" autocomplete="off">Usernames</input>
          </label>
        </div>
      </div>
      <div class="row" id="users-restrictions">
        <div class="col-xs-offset-0 col-xs-12" id="users-restrictions-text">
          <h1>Parameters</h1>
        </div>
        <div class="col-xs-offset-0 col-xs-12">
          <div class="container-fluid">
            <div class="well">
              <div class="row">
                <div class="restrictions-label text-sm-center text-sm-vcenter">
                  <label><a href="#" data-toggle="tooltip" data-placement="top" title="The maximum number of results returned.">Limit</a></label>
                </div>
                <div class="restrictions-input input-group">
                  <span class="input-group-addon">
                    <input type="checkbox" aria-label="Enable limit?" class="checkbox-inputtoggle" for="#users-restrictions-limit" checked>
                  </span>
                  <input type="number" min="1" step="1" class="form-control" id="users-restrictions-limit" value="10">
                </div>
              </div>
              <div class="row">
                <div class="restrictions-label text-sm-center text-sm-vcenter">
                  <label><a href="#" data-toggle="tooltip" data-placement="top" title="Only return the user with the specified id.">User ID</a></label>
                </div>
                <div class="restrictions-input input-group">
                  <span class="input-group-addon">
                    <input type="checkbox" aria-label="Enable user ID filtering?" class="checkbox-inputtoggle" for="#users-restrictions-userid" id="users-restrictions-userid-checkbox">
                  </span>
                  <input type="number" min="1" step="1" class="form-control" id="users-restrictions-userid" disabled>
                </div>
              </div>
              <div class="row">
                <div class="restrictions-label text-sm-center text-sm-vcenter">
                  <label><a href="#" data-toggle="tooltip" data-placement="top" title="Only return the user with the specified username.">Username</a></label>
                </div>
                <div class="restrictions-input input-group">
                  <span class="input-group-addon">
                    <input type="checkbox" aria-label="Enable username filtering?" class="checkbox-inputtoggle" for="#users-restrictions-username" id="users-restrictions-username-checkbox">
                  </span>
                  <input type="text" class="form-control" id="users-restrictions-username" value="" disabled>
                </div>
              </div>
              <div class="row">
                <div class="restrictions-label text-sm-center text-sm-vcenter">
                  <label><a href="#" data-toggle="tooltip" data-placement="top" title="Only return users added to the loansbot after the specified date.">After</a></label>
                </div>
                <div class="restrictions-input input-group">
                  <span class="input-group-addon">
                    <input type="checkbox" aria-label="Enable created after filtering?" class="checkbox-inputtoggle" for="#users-restrictions-createdafter">
                  </span>
                  <input type="date" class="form-control" id="users-restrictions-createdafter" disabled>
                </div>
              </div>
              <div class="row">
                <div class="restrictions-label text-sm-center text-sm-vcenter">
                  <label><a href="#" data-toggle="tooltip" data-placement="top" title="Only return users added to the loansbot before the specified date.">Before</a></label>
                </div>
                <div class="restrictions-input input-group">
                  <span class="input-group-addon">
                    <input type="checkbox" aria-label="Enable created after filtering?" class="checkbox-inputtoggle" for="#users-restrictions-createdbefore">
                  </span>
                  <input type="date" class="form-control" id="users-restrictions-createdbefore" disabled>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row" id="loans-restrictions" hidden>
        <div class="col-xs-offset-0 col-xs-12" id="loans-restrictions-text">
          <h1>Parameters</h1>
        </div>
        <div class="col-xs-offset-0 col-xs-12">
          <div class="container-fluid">
            <div class="well">
              <div class="row">
                <div class="restrictions-label text-sm-center text-sm-vcenter">
                  <label><a href="#" data-toggle="tooltip" data-placement="top" title="The maximum number of results returned.">Limit</a></label>
                </div>
                <div class="restrictions-input input-group">
                  <span class="input-group-addon">
                    <input type="checkbox" aria-label="Enable limit?" class="checkbox-inputtoggle" for="#loans-restrictions-limit" checked>
                  </span>
                  <input type="number" min="1" step="1" class="form-control" id="loans-restrictions-limit" value="10">
                </div>
              </div>
              <div class="row">
                <div class="restrictions-label text-sm-center text-sm-vcenter">
                  <label><a href="#" data-toggle="tooltip" data-placement="top" title="Only return loans with the specified id.">Loan ID</a></label>
                </div>
                <div class="restrictions-input input-group">
                  <span class="input-group-addon">
                    <input type="checkbox" aria-label="Enable loan id filtering?" class="checkbox-inputtoggle" for="#loans-restrictions-loanid">
                  </span>
                  <input type="number" min="1" step="1" class="form-control" id="loans-restrictions-loanid" disabled>
                </div>
              </div>
              <div class="row">
                <div class="restrictions-label text-sm-center text-sm-vcenter">
                  <label><a href="#" data-toggle="tooltip" data-placement="top" title="Only return loans created after the specified date.">After</a></label>
                </div>
                <div class="restrictions-input input-group">
                  <span class="input-group-addon">
                    <input type="checkbox" aria-label="Enable created after filtering?" class="checkbox-inputtoggle" for="#loans-restrictions-createdafter">
                  </span>
                  <input type="date" class="form-control" id="loans-restrictions-createdafter" disabled>
                </div>
              </div>
              <div class="row">
                <div class="restrictions-label text-sm-center text-sm-vcenter">
                  <label><a href="#" data-toggle="tooltip" data-placement="top" title="Only return loans created before the specified date.">Before</a></label>
                </div>
                <div class="restrictions-input input-group">
                  <span class="input-group-addon">
                    <input type="checkbox" aria-label="Enable created before filtering?" class="checkbox-inputtoggle" for="#loans-restrictions-createdbefore">
                  </span>
                  <input type="date" class="form-control" id="loans-restrictions-createdbefore" disabled>
                </div>
              </div>
              <div class="row">
                <div class="restrictions-label text-sm-center text-sm-vcenter">
                  <label><a href="#" data-toggle="tooltip" data-placement="top" title="Only return loans with a borrower with the specified user id.">Borrower ID</a></label>
                </div>
                <div class="restrictions-input input-group">
                  <span class="input-group-addon">
                    <input type="checkbox" aria-label="Enable borrower id filtering?" class="checkbox-inputtoggle" for="#loans-restrictions-borrowerid" id="loans-restrictions-borrowerid-checkbox">
                  </span>
                  <input type="number" min="1" step="1" class="form-control" id="loans-restrictions-borrowerid" disabled>
                </div>
              </div>
              <div class="row">
                <div class="restrictions-label text-sm-center text-sm-vcenter">
                  <label><a href="#" data-toggle="tooltip" data-placement="top" title="Only return loans with a lender with the specified user id.">Lender ID</a></label>
                </div>
                <div class="restrictions-input input-group">
                  <span class="input-group-addon">
                    <input type="checkbox" aria-label="Enable lender id filtering?" class="checkbox-inputtoggle" for="#loans-restrictions-lenderid" id="loans-restrictions-lenderid-checkbox">
                  </span>
                  <input type="number" min="1" step="1" class="form-control" id="loans-restrictions-lenderid" disabled>
                </div>
              </div>
              <div class="row">
                <div class="restrictions-label text-sm-center text-sm-vcenter">
                  <label><a href="#" data-toggle="tooltip" data-placement="top" title="Only return loans that have a lender or a borrower with the specified user id.">Includes User ID</a></label>
                </div>
                <div class="restrictions-input input-group">
                  <span class="input-group-addon">
                    <input type="checkbox" aria-label="Enable generic user id filtering?" class="checkbox-inputtoggle" for="#loans-restrictions-includesuserid" id="loans-restrictions-includesuserid-checkbox">
                  </span>
                  <input type="number" min="1" step="1" class="form-control" id="loans-restrictions-includesuserid" disabled>
                </div>
              </div>
              <div class="row">
                <div class="restrictions-label text-sm-center text-sm-vcenter">
                  <label><a href="#" data-toggle="tooltip" data-placement="top" title="Only returns loans who have a borrower with the specified name.">Borrower Name</a></label>
                </div>
                <div class="restrictions-input input-group">
                  <span class="input-group-addon">
                    <input type="checkbox" aria-label="Enable borrower name filtering?" class="checkbox-inputtoggle" for="#loans-restrictions-borrowername" id="loans-restrictions-borrowername-checkbox">
                  </span>
                  <input type="text" class="form-control" id="loans-restrictions-borrowername" disabled>
                </div>
              </div>
              <div class="row">
                <div class="restrictions-label text-sm-center text-sm-vcenter">
                  <label><a href="#" data-toggle="tooltip" data-placement="top" title="Only return loans who have a lender with the specified name.">Lender Name</a></label>
                </div>
                <div class="restrictions-input input-group">
                  <span class="input-group-addon">
                    <input type="checkbox" aria-label="Enable lender name filtering?" class="checkbox-inputtoggle" for="#loans-restrictions-lendername" id="loans-restrictions-lendername-checkbox">
                  </span>
                  <input type="text" class="form-control" id="loans-restrictions-lendername" disabled>
                </div>
              </div>
              <div class="row">
                <div class="restrictions-label text-sm-center text-sm-vcenter">
                  <label><a href="#" data-toggle="tooltip" data-placement="top" title="Only return loans who have either a lender or a borrower with the specified name.">Includes User Name</a></label>
                </div>
                <div class="restrictions-input input-group"> 
                  <span class="input-group-addon">
                    <input type="checkbox" aria-label="Enable generic username filtering?" class="checkbox-inputtoggle" for="#loans-restrictions-includesusername" id="loans-restrictions-includesusername-checkbox">
                  </span>
                  <input type="text" class="form-control" id="loans-restrictions-includesusername" disabled>
                </div>
              </div>
              <div class="row">
                <div class="restrictions-label text-sm-center text-sm-vcenter">
                  <label><a href="#" data-toggle="tooltip" data-placement="top" title="Return loans who have been deleted from public view. Requires moderator access.">Include Deleted Loans</a></label>
                </div>
                <div class="restrictions-input">
                  <input type="checkbox" class="lone-checkbox" aria-label="Disable deleted loan filtering?" id="loans-restrictions-deleted">
                </div>
              </div>
              <div class="row">
                <div class="restrictions-label text-sm-center text-sm-vcenter">
                  <label><a href="#" data-toggle="tooltip" data-placement="top" title="Only return loans who have exactly the specified principal.">Principal</a></label>
                </div>
                <div class="restrictions-input input-group"> 
                  <span class="input-group-addon">
                    <input type="checkbox" aria-label="" class="checkbox-inputtoggle" for="#loans-restrictions-principal" id="loans-restrictions-principal-checkbox">
                  </span>
                  <input type="number" min="0" step="0.01" class="form-control" id="loans-restrictions-principal" disabled>
                  <span class="input-group-addon">$</span>
                </div>
              </div>
              <div class="row">
                <div class="restrictions-label text-sm-center text-sm-vcenter">
                  <label><a href="#" data-toggle="tooltip" data-placement="top" title="Only return loans who have been repaid exactly the specified amount.">Principal Repayment</a></label>
                </div>
                <div class="restrictions-input input-group"> 
                  <span class="input-group-addon">
                    <input type="checkbox" aria-label="" class="checkbox-inputtoggle" for="#loans-restrictions-principalrepayment" id="loans-restrictions-principalrepayment-checkbox">
                  </span>
                  <input type="number" min="0" step="0.01" class="form-control" id="loans-restrictions-principalrepayment" disabled>
                  <span class="input-group-addon">$</span>
                </div>
              </div>
              <div class="row">
                <div class="restrictions-label text-sm-center text-sm-vcenter">
                  <label><a href="#" data-toggle="tooltip" data-placement="top" title="Only return loans who have or do not have the same repayment as their principal.">Repaid Loans</a></label>
                </div>
                <div class="restrictions-input">
                  <input type="checkbox" aria-label="Enable repaid filtering?" class="checkbox-inputtoggle lone-checkbox" for="#loans-restrictions-repaid-yes,#loans-restrictions-repaid-no" id="loans-restrictions-repaid-checkbox">
                  <div class="btn-group radio-group" data-toggle="buttons">
                    <label>
                      <input type="radio" name="loans-restrictions-repaid" id="loans-restrictions-repaid-yes" checked disabled>
                      Only Repaid
                    </label>
                    <label>
                      <input type="radio" name="loans-restrictions-repaid" id="loans-restrictions-repaid-no" disabled>
                      No Repaid
                    </label>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="restrictions-label text-sm-center text-sm-vcenter">
                  <label><a href="#" data-toggle="tooltip" data-placement="top" title="Only return loans who have or have not been flagged as unpaid.">Unpaid Loans</a></label>
                </div>
                <div class="restrictions-input">
                  <input type="checkbox" aria-label="Enable unpaid filtering?" class="checkbox-inputtoggle lone-checkbox" for="#loans-restrictions-unpaid-yes,#loans-restrictions-unpaid-no" id="loans-restrictions-unpaid-checkbox">
                  <div class="btn-group radio-group" data-toggle="buttons">
                    <label>
                      <input type="radio" name="loans-restrictions-unpaid" id="loans-restrictions-unpaid-yes" checked disabled>
                      Only Unpaid
                    </label>
                    <label>
                      <input type="radio" name="loans-restrictions-unpaid" id="loans-restrictions-unpaid-no" disabled>
                      No Unpaid
                    </label>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="restrictions-label text-sm-center text-sm-vcenter">
                  <label><a href="#" data-toggle="tooltip" data-placement="top" title="Modify the result of the above query? Requires moderator access and a reason.">Modify Result</a><label>
                </div>
                <div class="restrictions-input input-group">
                  <span class="input-group-addon">
                    <input type="checkbox" aria-label="Modify result?" class="checkbox-inputtoggle" for="#loans-restrictions-modify-reason" id="loans-restrictions-modify-checkbox">
                  </span>
                  <input type="text" class="form-control" id="loans-restrictions-modify-reason" disabled>
                </div>
              </div>
              <div class="row" hidden>
                <div class="restrictions-label text-sm-center text-sm-vcenter">
                  <label><a href="#" data-toggle="tooltip" data-placement="top" title="Sets the ID of the borrower for all returned loans.">Set Borrower ID</a><label>
                </div>
                <div class="restrictions-input input-group">
                  <span class="input-group-addon">
                    <input type="checkbox" aria-label="Modify borrower id?" class="checkbox-inputtoggle" for="#loans-restrictions-modify-borrowerid" id="loans-restrictions-modify-borrowerid-checkbox">
                  </span>
                  <input type="number" min="1" step="1" class="form-control" id="loans-restrictions-modify-borrowerid" disabled>
                </div>
              </div>
              <div class="row" hidden>
                <div class="restrictions-label text-sm-center text-sm-vcenter">
                  <label><a href="#" data-toggle="tooltip" data-placement="top" title="Sets the ID of the lender for all returned loans.">Set Lender ID</a><label>
                </div>
                <div class="restrictions-input input-group">
                  <span class="input-group-addon">
                    <input type="checkbox" aria-label="Modify lender id?" class="checkbox-inputtoggle" for="#loans-restrictions-modify-lenderid" id="loans-restrictions-modify-lenderid-checkbox">
                  </span>
                  <input type="number" min="1" step="1" class="form-control" id="loans-restrictions-modify-lenderid" disabled>
                </div>
              </div>
              <div class="row" hidden>
                <div class="restrictions-label text-sm-center text-sm-vcenter">
                  <label><a href="#" data-toggle="tooltip" data-placement="top" title="Sets the ID of the borrower for all returned loans by fetching the user with the specified username.">Set Borrower Name</a><label>
                </div>
                <div class="restrictions-input input-group">
                  <span class="input-group-addon">
                    <input type="checkbox" aria-label="Modify borrower id by name?" class="checkbox-inputtoggle" for="#loans-restrictions-modify-borrowername" id="loans-restrictions-modify-borrowername-checkbox">
                  </span>
                  <input type="text" class="form-control" id="loans-restrictions-modify-borrowername" disabled>
                </div>
              </div>
              <div class="row" hidden>
                <div class="restrictions-label text-sm-center text-sm-vcenter">
                  <label><a href="#" data-toggle="tooltip" data-placement="top" title="Sets the ID of the lender for all returned loans by fetching the user with the specified username.">Set Lender Name</a><label>
                </div>
                <div class="restrictions-input input-group">
                  <span class="input-group-addon">
                    <input type="checkbox" aria-label="Modify lender id by name?" class="checkbox-inputtoggle" for="#loans-restrictions-modify-lendername" id="loans-restrictions-modify-lendername-checkbox">
                  </span>
                  <input type="text" class="form-control" id="loans-restrictions-modify-lendername" disabled>
                </div>
              </div>
              <div class="row" hidden>
                <div class="restrictions-label text-sm-center text-sm-vcenter">
                  <label><a href="#" data-toggle="tooltip" data-placement="top" title="Sets the principal of the returned loans to the specified amount.">Set Principal</a><label>
                </div>
                <div class="restrictions-input input-group">
                  <span class="input-group-addon">
                    <input type="checkbox" aria-label="Modify principal?" class="checkbox-inputtoggle" for="#loans-restrictions-modify-principal" id="loans-restrictions-modify-principal-checkbox">
                  </span>
                  <input type="number" min="0.01" step="0.01" class="form-control" id="loans-restrictions-modify-principal" disabled>
                  <span class="input-group-addon">$</span>
                </div>
              </div>
              <div class="row" hidden>
                <div class="restrictions-label text-sm-center text-sm-vcenter">
                  <label><a href="#" data-toggle="tooltip" data-placement="top" title="Sets the principal repayment of the returned loans to the specified amount.">Set Principal Repayment</a><label>
                </div>
                <div class="restrictions-input input-group">
                  <span class="input-group-addon">
                    <input type="checkbox" aria-label="Modify principal repayment?" class="checkbox-inputtoggle" for="#loans-restrictions-modify-principalrepayment" id="loans-restrictions-modify-principalrepayment-checkbox">
                  </span>
                  <input type="number" min="0" step="0.01" class="form-control" id="loans-restrictions-modify-principalrepayment" disabled>
                  <span class="input-group-addon">$</span>
                </div>
              </div>
              <div class="row" hidden>
                <div class="restrictions-label text-sm-center text-sm-vcenter">
                  <label><a href="#" data-toggle="tooltip" data-placement="top" title="Sets the unpaid flag of the returned loans to either yes or no.">Set Unpaid</a><label>
                </div>
                <div class="restrictions-input">
                  <input type="checkbox" aria-label="Modify unpaid?" class="checkbox-inputtoggle lone-checkbox" for="#loans-restrictions-modify-unpaid-yes,#loans-restrictions-modify-unpaid-no" id="loans-restrictions-modify-unpaid-checkbox">
                  <div class="btn-group radio-group" data-toggle="buttons">
                    <label>
                      <input type="radio" name="loans-restrictions-modify-unpaid" id="loans-restrictions-modify-unpaid-yes" checked disabled>
                      Flag Unpaid
                    </label>
                    <label>
                      <input type="radio" name="loans-restrictions-modify-unpaid" id="loans-restrictions-modify-unpaid-no" disabled>
                      Unflag Unpaid
                    </label>
                  </div>
                </div>
              </div>
              <div class="row" hidden>
                <div class="restrictions-label text-sm-center text-sm-vcenter">
                  <label><a href="#" data-toggle="tooltip" data-placement="top" title="Sets the deleted flag of the returned loans to either yes or no.">Set Deleted</a><label>
                </div>
                <div class="restrictions-input">
                  <input type="checkbox" aria-label="Modify deleted?" class="checkbox-inputtoggle lone-checkbox" for="#loans-restrictions-modify-deleted-yes, #loans-restrictions-modify-deleted-no" id="loans-restrictions-modify-deleted-checkbox">
                  <div class="btn-group radio-group" data-toggle="buttons">
                    <label>
                      <input type="radio" name="loans-restrictions-modify-deleted" id="loans-restrictions-modify-deleted-yes" checked disabled>
                      Flag Deleted
                    </label>
                    <label>
                      <input type="radio" name="loans-restrictions-modify-deleted" id="loans-restrictions-modify-deleted-no" disabled>
                      Unflag Deleted
                    </label>
                  </div>
                </div>
              </div>
              <div class="row" hidden>
                <div class="restrictions-label text-sm-center text-sm-vcenter">
                  <label><a href="#" data-toggle="tooltip" data-placement="top" title="Sets the deleted reason of the returned loans.">Set Deleted Reason</a><label>
                </div>
                <div class="restrictions-input input-group">
                  <span class="input-group-addon">
                    <input type="checkbox" aria-label="Modify deleted reason?" class="checkbox-inputtoggle" for="#loans-restrictions-modify-deletedreason" id="loans-restrictions-modify-deletedreason-checkbox">
                  </span>
                  <input type="text" class="form-control" id="loans-restrictions-modify-deletedreason" disabled>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row" id="usernames-restrictions" hidden>
      </div>
      <div class="row" id="run-search">
          <button type="button" class="btn btn-primary col-xs-12" role="button" id="run-search-button">Run search!</button>
      </div>
      <div class="row" id="status-text-row">
        <div class="col-xs-12 status-text" hidden></div>
      </div>
      <div class="row" id="results">
        <div class="col-xs-offset-0 col-xs-12" id="results-text">
          <h1>Results</h1>
        </div>
        <div class="col-xs-offset-0 col-xs-12 col-sm-offset-1 col-sm-10" id="results-users-table-container">
          <table class="table table-hover" id="results-table">
            <thead>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <script src="/js/site.js"></script>
    <script type="text/javascript">
      function layoutTheThings() {
        var dbButGrp = $("#database-button-group");
       
        dbButGrp.removeAttr("style");
        dbButGrp.removeClass("btn-group-justified").removeClass("btn-group-vertical");
        if(dbButGrp.innerWidth() < 390) {
          dbButGrp.attr("class", "btn-group-vertical " + dbButGrp.attr("class"));
          dbButGrp.css("width", "100%");
        }else {
          dbButGrp.attr("class", "btn-group-justified " + dbButGrp.attr("class"));
          if($("#select-database").width() < 1170) {
            dbButGrp.css("width", "100%");
          }else {
            dbButGrp.css("width", "66.6666%");
          }
        }

        // ----

        var textSmCenter = $(".text-sm-center");

        for(var i = 0; i < textSmCenter.length; i++) {
          var textSmCenterEle = $(textSmCenter[i])
          if($(window).width() >= 1020) {
            textSmCenterEle.addClass("text-center");
          }else {
            textSmCenterEle.removeClass("text-center");
          }
        }

        // ----

        var textSmVCenter = $(".text-sm-vcenter");

        for(var i = 0; i < textSmVCenter.length; i++) {
          var textSmVCenterEle = $(textSmVCenter[i]);
          if($(window).width() >= 1020) {
            textSmVCenterEle.addClass("text-vcenter");
          }else {
            textSmVCenterEle.removeClass("text-vcenter");
          }
        }

        // ----

        var vCenter = $(".text-vcenter");

        for(var i = 0; i < vCenter.length; i++) {
          var vCenterEle = $(vCenter[i]);
          vCenterEle.removeAttr("style");
          var vCenterEleParent = vCenterEle.parent();
          vCenterEle.css("line-height", vCenterEleParent.height() + "px");
        }

        // ---

      };

      $(window).on("resize", function() { layoutTheThings(); });

      $(function() {
        layoutTheThings();
        $('[data-toggle="tooltip"]').tooltip();
        var checkboxInputToggles = $(".checkbox-inputtoggle");
 
        for(var i = 0; i < checkboxInputToggles.length; i++) {
          var cInpToggleEle = $(checkboxInputToggles[i]);
          cInpToggleEle.change(function() {  
            var cInpToggleTarget = $($(this).attr("for"));
            cInpToggleTarget.prop("disabled", !this.checked);
          });
        }

        var showRestrics = function() {
          var usRestr = $("#users-restrictions");
          var loRestr = $("#loans-restrictions");
          var nmRestr = $("#usernames-restrictions");
          
          if($("#database-users").is(":checked")) {
            if(!usRestr.is(":visible")) {
              usRestr.slideToggle(500, layoutTheThings);
            }
          }else if(usRestr.is(":visible")) {
            usRestr.slideToggle(400);
          }
           
          if($("#database-loans").is(":checked")) {
            if(!loRestr.is(":visible")) {
              loRestr.slideToggle(500, layoutTheThings);
            }
          }else if(loRestr.is(":visible")) {
            loRestr.slideToggle(400);
          }

          if($("#database-usernames").is(":checked")) {
            if(!nmRestr.is(":visible")) {
              nmRestr.slideToggle(500, layoutTheThings);
            }
          }else if(nmRestr.is(":visible")) {
            nmRestr.slideToggle(400);
          }
        }

        $("#database-users, #database-loans, #database-usernames").change(showRestrics);
        $("#users-restrictions-userid-checkbox").change(function() {
          if(this.checked) {
            $("#users-restrictions-username-checkbox").removeAttr("checked");
            $("#users-restrictions-username").attr("disabled", true);
          }
        });
        $("#users-restrictions-username-checkbox").change(function() {
          if(this.checked) {
            $("#users-restrictions-userid-checkbox").removeAttr("checked");
            $("#users-restrictions-userid").attr("disabled", true);
          }
        });
        $("#loans-restrictions-borrowerid-checkbox").change(function() {
          if(this.checked) {
            $("#loans-restrictions-borrowername-checkbox").removeAttr("checked");
            $("#loans-restrictions-borrowername").attr("disabled", true);
          }
        });
        $("#loans-restrictions-lenderid-checkbox").change(function() {
          if(this.checked) {
            $("#loans-restrictions-lendername-checkbox").removeAttr("checked");
            $("#loans-restrictions-lendername").attr("disabled", true);
          }
        });
        $("#loans-restrictions-includesuserid-checkbox").change(function() {
          if(this.checked) {
            $("#loans-restrictions-includesusername-checkbox").removeAttr("checked");
            $("#loans-restrictions-includesusername").attr("disabled", true);
          }
        });
        $("#loans-restrictions-borrowername-checkbox").change(function() {
          if(this.checked) {
            $("#loans-restrictions-borrowerid-checkbox").removeAttr("checked");
            $("#loans-restrictions-borrowerid").attr("disabled", true);
          }
        });
        $("#loans-restrictions-lendername-checkbox").change(function() {
          if(this.checked) {
            $("#loans-restrictions-lenderid-checkbox").removeAttr("checked");
            $("#loans-restrictions-lenderid").attr("disabled", true);
          }
        });
        $("#loans-restrictions-includesusername-checkbox").change(function() {
          if(this.checked) {
            $("#loans-restrictions-includesuserid-checkbox").removeAttr("checked");
            $("#loans-restrictions-includesuserid").attr("disabled", true);
          }
        });
        $("#loans-restrictions-repaid-checkbox").change(function() {
          if(this.checked) {
            $("#loans-restrictions-principal-checkbox").removeAttr("checked");
            $("#loans-restrictions-principal").attr("disabled", true);
            $("#loans-restrictions-principalrepayment-checkbox").removeAttr("checked");
            $("#loans-restrictions-principalrepayment").attr("disabled", true);
            $("#loans-restrictions-unpaid-checkbox").removeAttr("checked");
            $($("#loans-restrictions-unpaid-checkbox").attr("for")).attr("disabled", true);
          }
        });
        $("#loans-restrictions-unpaid-checkbox").change(function() {
          if(this.checked) {
            $("#loans-restrictions-repaid-checkbox").removeAttr("checked");
            $($("#loans-restrictions-repaid-checkbox").attr("for")).attr("disabled", true);
          }
        });
        $("#loans-restrictions-principalrepayment-checkbox,#loans-restrictions-principal-checkbox").change(function() {
          if(this.checked) {
            $("#loans-restrictions-repaid-checkbox").removeAttr("checked");
            $($("#loans-restrictions-repaid-checkbox").attr("for")).attr("disabled", true);
          }
        });

        $("#loans-restrictions-modify-checkbox").change(function() {
          $("#loans-restrictions-modify-borrowerid").parent().parent().slideToggle();
          $("#loans-restrictions-modify-lenderid").parent().parent().slideToggle();
          $("#loans-restrictions-modify-borrowername").parent().parent().slideToggle();
          $("#loans-restrictions-modify-lendername").parent().parent().slideToggle();
          $("#loans-restrictions-modify-principal").parent().parent().slideToggle();
          $("#loans-restrictions-modify-principalrepayment").parent().parent().slideToggle();
          $("#loans-restrictions-modify-unpaid-yes").parent().parent().parent().parent().slideToggle();
          $("#loans-restrictions-modify-deleted-yes").parent().parent().parent().parent().slideToggle();
          $("#loans-restrictions-modify-deletedreason").parent().parent().slideToggle(400, "swing", function() { layoutTheThings(); });
        });
        $("#loans-restrictions-modify-borrowerid-checkbox").change(function() {
          if(this.checked) {
            $("#loans-restrictions-modify-borrowername-checkbox").removeAttr("checked");
            $("#loans-restrictions-modify-borrowername").attr("disabled", true);
          }
        });
        $("#loans-restrictions-modify-lenderid-checkbox").change(function() {
          if(this.checked) {
            $("#loans-restrictions-modify-lendername-checkbox").removeAttr("checked");
            $("#loans-restrictions-modify-lendername").attr("disabled", true);
          }
        });
        $("#loans-restrictions-modify-borrowername-checkbox").change(function() {
          if(this.checked) {
            $("#loans-restrictions-modify-borrowerid-checkbox").removeAttr("checked");
            $("#loans-restrictions-modify-borrowerid").attr("disabled", true);
          }
        });
        $("#loans-restrictions-modify-lendername-checkbox").change(function() {
          if(this.checked) {
            $("#loans-restrictions-modify-lenderid-checkbox").removeAttr("checked");
            $("#loans-restrictions-modify-lenderid").attr("disabled", true);
          }
        });
        $("#run-search-button").click(function() {
          if($("#database-users").is(":checked")) {
            $.get("/api/users.php", getUserParams(), function(data, status) {
              console.log(data);
              populateResults(data);
            });
          }else if($("#database-loans").is(":checked")) {
            $.get("/api/loans.php", getLoanParams(), function(data, status) {
              $(".status-text").attr("hidden", true);
              populateResults(data);
            }).fail(function(data){
              var errMess = errorMessageFromFailResponse(data.responseJSON);
              $(".status-text").addClass("bg-danger");
              $(".status-text").removeAttr("hidden");
              $(".status-text").html(errMess);
            }).always(function(data) {
              console.log(data);
            });
          }
        });
      });

      function handleParam(invalidArr, result, paramId, element) {
        if(!element.is(":disabled")) {
          result[paramId] = element.val();
          if(!!!result[paramId]) {
            invalidArr[0] = true;
            element.parent().addClass("has-error");
          }else {
            element.parent().removeClass("has-error");
          }
        }
      }
      function getUserParams() {
        var result = {};
        var invalid = [ false ];
        result['format'] = 3;
        
        handleParam(invalid, result, 'limit', $("#users-restrictions-limit"));
        handleParam(invalid, result, 'id', $("#users-restrictions-userid"));
        handleParam(invalid, result, 'username', $("#users-restrictions-username"));
        handleParam(invalid, result, 'after_time', $("#users-restrictions-createdafter"));
        if(result['after_time']) { result['after_time'] = Date.parse(result['after_time']); }
        handleParam(invalid, result, 'before_time', $("#users-restrictions-createdbefore"));
        if(result['before_time']) { result['before_time'] = Date.parse(result['before_time']); }

        if(invalid[0]) {
          return {'format': 3, 'limit': 1};
        }
        return result;
      }
      
      function getLoanParams() {
        var result = {};
        var invalid = [ false ];
        result['format'] = 3;
        
        handleParam(invalid, result, 'limit', $("#loans-restrictions-limit"));
        handleParam(invalid, result, 'id', $("#loans-restrictions-loanid"));
        handleParam(invalid, result, 'after_time', $("#loans-restrictions-createdafter"));
        if(result['after_time']) { result['after_time'] = Date.parse(result['after_time']); }
        handleParam(invalid, result, 'before_time', $("#loans-restrictions-createdbefore"));
        if(result['before_time']) { result['before_time'] = Date.parse(result['before_time']); }
        handleParam(invalid, result, 'borrower_id', $("#loans-restrictions-borrowerid"));
        handleParam(invalid, result, 'lender_id', $("#loans-restrictions-lenderid"));
        handleParam(invalid, result, 'includes_user_id', $("#loans-restrictions-includesuserid"));
        handleParam(invalid, result, 'borrower_name', $("#loans-restrictions-borrowername"));
        handleParam(invalid, result, 'lender_name', $("#loans-restrictions-lendername"));
        handleParam(invalid, result, 'includes_user_name', $("#loans-restrictions-includesusername"));
        result['include_deleted'] = $("#loans-restrictions-deleted").is(":checked") ? 1 : 0;
        handleParam(invalid, result, 'principal_cents', $("#loans-restrictions-principal"));
        if(result['principal_cents']) { result['principal_cents'] = result['principal_cents'] * 100; }
        handleParam(invalid, result, 'principal_repayment_cents', $("#loans-restrictions-principalrepayment"));
        if(result['principal_repayment_cents']) { result['principal_repayment_cents'] = result['principal_repayment_cents'] * 100; }
        
        if(!$("#loans-restrictions-unpaid-yes").is(":disabled")) {
          result['unpaid'] = $("#loans-restrictions-unpaid-yes").is(":checked") ? 1 : 0;
        }
        if(!$("#loans-restrictions-repaid-yes").is(":disabled")) {
          result['repaid'] = $("#loans-restrictions-repaid-yes").is(":checked") ? 1 : 0;
        }
        
        if($("#loans-restrictions-modify-checkbox").is(":checked")) {
          result['modify'] = 1;
          handleParam(invalid, result, 'modify_reason', $("#loans-restrictions-modify-reason"));
          handleParam(invalid, result, 'set_borrower_id', $("#loans-restrictions-modify-borrowerid"));
          handleParam(invalid, result, 'set_lender_id', $("#loans-restrictions-modify-lenderid"));
          handleParam(invalid, result, 'set_borrower_name', $("#loans-restrictions-modify-borrowername"));
          handleParam(invalid, result, 'set_lender_name', $("#loans-restrictions-modify-lendername"));
          handleParam(invalid, result, 'set_principal_cents', $("#loans-restrictions-modify-principal"));
          if(result['set_principal_cents']) { result['set_principal_cents'] = result['set_principal_cents'] * 100; }
          handleParam(invalid, result, 'set_principal_repayment_cents', $("#loans-restrictions-modify-principalrepayment"));
          if(result['set_principal_repayment_cents']) { result['set_principal_repayment_cents'] = result['set_principal_repayment_cents'] * 100; }
          
          if(!$("#loans-restrictions-modify-unpaid-yes").is(":disabled")) {
            result['set_unpaid'] = $("#loans-restrictions-modify-unpaid-yes").is(":checked") ? 1 : 0;
          }
          if(!$("#loans-restrictions-modify-deleted-yes").is(":disabled")) {
            result['set_deleted'] = $("#loans-restrictions-modify-deleted-yes").is(":checked") ? 1 : 0;
          }
          handleParam(invalid, result, 'set_deleted_reason', $("#loans-restrictions-modify-deletedreason"));
        }

        if(invalid[0]) {
          return { 'format': 3, 'limit': 1 };
        }
        return result;
      }

      function populateUsers(results, resultsTable, resultsHead, resultsBody) {
        // Step 1: Determine if we have personal information
        var havePersonalInformation = false;
        if(results.users.length > 0) {
          havePersonalInformation = typeof(results.users[0].email) !== 'undefined';
        }

        resultsHead.empty();
        resultsBody.empty();

        if(havePersonalInformation) {
          resultsHead.append("<tr><th>#</th><th>User ID</th><th>Username</th><th>Claimed</th><th>Created At</th><th>Updated At</th><th>Email</th><th>Street Address</th><th>City</th><th>State</th><th>Zip</th><th>Country</th></tr>");
        }else {
          resultsHead.append("<tr><th>#</th><th>User ID</th><th>Username</th><th>Claimed</th><th>Created At</th><th>Updated At</th></tr>");
        }

        var now = new Date();
        for(var i = 0; i < results.users.length; i++) {
          var usInfo = results.users[i];
          var createdAt = new Date(usInfo.created_at + now.getTimezoneOffset()*60000);
          var updatedAt = new Date(usInfo.updated_at + now.getTimezoneOffset()*60000);
          var toApp = "<tr>";
          toApp = toApp + "<td>" + (i+1) + "</td>";
          toApp = toApp + "<td>" + usInfo.user_id + "</td>";
          toApp = toApp + "<td>" + usInfo.username + "</td>";
          toApp = toApp + "<td>" + (usInfo.claimed ? "Yes!" : "No.") + "</td>";
          toApp = toApp + "<td>" + ((createdAt.getMonth()+1) + "/" + createdAt.getDate() + "/" + createdAt.getFullYear()) + "</td>";
          toApp = toApp + "<td>" + ((updatedAt.getMonth()+1) + "/" + updatedAt.getDate() + "/" + updatedAt.getFullYear()) + "</td>";
          if(usInfo.email) {
            toApp = toApp + "<td>" + usInfo.email + "</td>";
            toApp = toApp + "<td>" + usInfo.street_address + "</td>";
            toApp = toApp + "<td>" + usInfo.city + "</td>";
            toApp = toApp + "<td>" + usInfo.state + "</td>";
            toApp = toApp + "<td>" + usInfo.zip + "</td>";
            toApp = toApp + "<td>" + usInfo.country + "</td>";
          }
          toApp = toApp + "</tr>";
          resultsBody.append(toApp);
        }
      }
      
      function resolveThread(callingA, loan_id) {
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
            }else {
              callingA.html("No thread found (code " + info.type.toString() + ")");
            }
          }
        }).fail(function(xhr) {
          var errMess = errorMessageFromFailResponse(xhr.responseJSON);
          callingA.html(errMess)
        });
        return false;
      }

      function populateLoans(results, resultsTable, resultsHead, resultsBody) {
        var haveDeleted = false;
        var haveModified = false;

        if(results.loans.length > 0) {
          haveDeleted = typeof(results.loans[0].deleted) !== 'undefined';
          haveModified = typeof(results.loans[0].new_lender_id) !== 'undefined';
        }

        resultsHead.empty();
        var headCont = "";
        headCont += "<tr>";
        headCont += "<th>#</th><th>Loan ID</th><th>Lender Name</th><th>Borrower Name</th><th>Principal</th><th>Principal Repayment</th><th>Unpaid</th><th>Thread</th><th>Created At</th>";
        if(haveDeleted) {
          headCont += "<th>Deleted</th><th>Deleted At</th><th>Deleted Reason</th>";
        }
        if(haveModified) {
          headCont += "<th>Requires Refresh</th>";
        }
        headCont += "</tr>";
        resultsHead.html(headCont);
        resultsBody.empty();

        var now = new Date();
        for(var i = 0; i < results.loans.length; i++) {
          var loanInfo = results.loans[i];
          var createdAt = new Date(loanInfo.created_at + now.getTimezoneOffset() * 60000);
          var rowCont = "";
          rowCont += "<tr>";
          rowCont += "<td>" + (i+1) + "</td>";
          rowCont += "<td>" + loanInfo.loan_id + "</td>";
          rowCont += "<td>" + loanInfo.lender_name + "</td>";
          rowCont += "<td>" + loanInfo.borrower_name + "</td>";
          rowCont += "<td>$" + (loanInfo.principal_cents / 100.) + "</td>";
          rowCont += "<td>$" + (loanInfo.principal_repayment_cents / 100.) + "</td>";
          rowCont += "<td>" + (loanInfo.unpaid === 1 ? 'Yes' : 'No') + "</td>";
          rowCont += "<td><a href=\"#\" onclick=\"return resolveThread(this, " + loanInfo.loan_id + ");\">Fetch thread</a></td>";
          rowCont += "<td>" + ((createdAt.getMonth()+1) + "/" + createdAt.getDate() + "/" + createdAt.getFullYear()) + "</td>";
          if(haveDeleted) {
            var deletedAt = new Date(loanInfo.deleted_at + now.getTimezoneOffset() * 60000);
            rowCont += "<td>" + (loanInfo.deleted ? 'Yes' : 'No') + "</td>";
            rowCont += "<td>" + (loanInfo.deleted_at === 0 ? 'Never' : ((deletedAt.getMonth()+1) + "/" + deletedAt.getDate() + "/" + deletedAt.getFullYear())) + "</td>";
            rowCont += "<td>" + loanInfo.deleted_reason + "</td>";
          }
          if(haveModified) {
            rowCont += "<td><b><i>YES</i></b></td>";
          }
          rowCont += "</tr>";
          resultsBody.append(rowCont);
        }

        resultsTable.attr("hidden", true);
        resultsTable.removeAttr("hidden");
      }

      function populateResults(results) {
        var resultsTable = $("#results-table");
        var resultsHead = $("#results-table thead");
        var resultsBody = $("#results-table tbody");
        
        if(results.result_type === "USERS_EXTENDED") {
          populateUsers(results, resultsTable, resultsHead, resultsBody);
        }else if(results.result_type === "LOANS_EXTENDED") {
          populateLoans(results, resultsTable, resultsHead, resultsBody);
        }else {
          console.log("Invalid results type - " + results.result_type + " - USERS_EXTENDED or LOANS_EXTENDED expected");
        }
      }
    </script>
  </body>
</html>
