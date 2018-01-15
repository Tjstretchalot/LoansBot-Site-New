<?php
  include_once('connect_and_get_loggedin.php');
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
      <section>
        <div id="loansbot-pulse">
          <h1>Statistics<!-- and Graphs <small>WIP</small> <button type="btn btn-default" role="button" id="fetch-data-button">Load Statistics</button>--></h1> 
          <div class="container-fluid alert" id="fetch-data-status-text" style="display: none"></div>
          <div class="statistics-text">
            <h2>Overview</h2>
            <div class="data-table">
              <big>Table 1 - Measured Data</big>
              <table class="table table-sm table-hover">
                <thead><th>Metric</th><th>Total</th><th>Last 30 days</th><th>Last 7 days</th></thead>
                <tbody>
                  <tr><td>Loans Lent</td><td id="loans-lent-total"># ($)</td><td id="loans-lent-month"># ($)</td><td id="loans-lent-week"># ($)</td></tr>
                  <tr><td>Outstanding Loans</td><td id="loans-outstanding-total"># ($)</td><td id="loans-outstanding-month"># ($)</td><td id="loans-outstanding-week"># ($)</td></tr>
                  <tr><td>Unpaid Loans</td><td id="loans-unpaid-total"># ($)</td><td id="loans-unpaid-month"># ($)</td><td id="loans-unpaid-week"># ($)</td></tr>
                </tbody>
              </table>
            </div>
            <button class="btn btn-default my-toggle-button" type="button" data-toggle="collapse" data-target="#definitions-1" aria-expanded="false" aria-controls="definitions-1">Toggle Definitions</button>
            <div id="definitions-1" class="collapse">
              <p>Loans Lent is the total number of loans that have been lent that have not been flagged as deleted by a moderator, as well as the dollar value
              of the principal of the corresponding loans.</p>
              <p>Outstanding loans is the number of loans that have a principal repayment that is less than their principal and have not been flagged as
              deleted or unpaid, as well as the dollar value of the principal of the corresponding loans.</p>
              <p>Unpaid loans is the number of loans flagged as unpaid, as well as the dollar value of the principal of those loans.</p>
              <p>Time since the first loan is the interval of time since the first loan was created in the subreddit and was picked up by the bot.</p>
            </div>
            <br>
            <div class="statistics-table">
              <big>Table 2 - Derived Data</big>
              <table class="table table-sm table-hover">
                <thead><th>Metric</th><th>Confidence</th><th>Value</th></thead>
                <tbody>
                  <tr><td>Loan Default Rate</td><td id="loans-default-rate-confidence">%</td><td id="loans-default-rate-interval">% - %</td></tr>
                  <tr><td>User Default Rate</td><td id="users-default-rate-confidence">%</td><td id="users-default-rate-interval">% - %</td></tr>
                  <tr><td>Recurring User Rate</td><td id="recurring-user-rate-confidence">%</td><td id="recurring-user-rate-interval">% - %</td></tr>
                  <tr><td>Recurring User Default Rate</td><td id="recurring-user-default-rate-confidence">%</td><td id="recurring-user-default-rate-interval">% - %</td></tr>
                  <tr><td>Projected Loan Value</td><td id="projected-loan-value-confidence">%</td><td id="projected-loan-value-interval">$ - $</td></tr>
                </tbody>
              </table>
            </div>
            <button class="btn btn-default my-toggle-button" type="button" data-toggle="collapse" data-target="#statistics-text-more" aria-expanded="false" aria-controls="statistics-text-more">Toggle Definitions</button>
            <div id="statistics-text-more" class="collapse">
              <h3>Loan Default Rate</h3>
              <p>Measures the likelihood that the next loan will default.</p>
              <button class="btn btn-default my-toggle-button" type="button" data-toggle="collapse" data-target="#loan-default-rate-calculations" aria-expanded="false" aria-controls="loan-default-rate-calculations">Toggle Calculations</button>
              <div id="loan-default-rate-calculations" class="collapse">
                <h4>Calculations</h4>
                <h5>Known</h5>
                <p>Let \(L\) be the number of loans total</p>
                <p>Let \(D\) be the number of defaulted loans</p>
                <p>Let \(P\) be the number of outstanding (pending) loans</p>
                <p>Let \(c\) be the confidence that the true default rate lies within \(i\); \(c = 0.95\)</p>
                <h5>Assume</h5>
                <p>The number of loans so far is a representative sample of loans in the future, and the sample size is large enough for Fisher information to be a good estimate of \(-l''\).</p>
                <h5>Want</h5>
                <p>Let \(i\) be the Wald interval of the default rate with confidence \(c\). \(i = (i_0, i_1)\)</p>
                <p>Let \(r = \frac{i_1-i_0}{2}\)</p>

                <table class="table table-sm table-hover">
                  <thead>
                    <th>Math</th><th>Description</th>
                  </thead>
                  <tbody>
                    <tr>
                      <td>\(L^* = L - P\)</td>
                      <td>Outstanding loans do not provide information on default rates</td>
                    </tr>
                    <tr>
                      <td>\(r = 1.96 \frac{1}{\sqrt{I(\hat{\pi}})}\)</td>
                      <td>Definition of standard error, where \(I(\hat{\pi})\) is the Fisher information</td>
                    </tr>
                    <tr>
                      <td>\(r = 1.96 \frac{1}{\sqrt{\frac{L^*}{\hat{\pi}(1-\hat{\pi})}}}\)</td>
                      <td>Substitute Fisher Information \(I(p; n) = \frac{n}{p(1 - p)}\)</td>
                    </tr>
                    <tr>
                      <td>\(r = 1.96 \sqrt{\frac{\frac{D}{L^*}(1 - \frac{D}{L^*})}{L^*}}\)</td>
                      <td>Algebra, substitute \(\hat{\pi} = \frac{D}{L^*}\)</td>
                    </tr>
                    <tr>
                      <td>\(i = (\hat{\pi} - r, \hat{\pi} + r)\)</td>
                      <td>Definition</td>
                    </tr>
                    <tr>
                      <td>\(i = \left(\frac{D}{L^*} - 1.96 \sqrt{\frac{\frac{D}{L^*}(1 - \frac{D}{L^*})}{L^*}}, \frac{D}{L^*} + 1.96 \sqrt{\frac{\frac{D}{L^*}(1 - \frac{D}{L^*})}{L^*}}\right)\)</td>
                      <td>Substitution</td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <h3>User Default Rate</h3>
              <p>Measures the likelihood that the next new user will default.</p>
              <button class="btn btn-default my-toggle-button" type="button" data-toggle="collapse" data-target="#user-default-rate-calculations" aria-expanded="false" aria-controls="user-default-rate-calculations">Toggle Calculations</button>
              <div id="user-default-rate-calculations" class="collapse">
                <h4>Calculations</h4>
                <h5>Known</h5>
                <p>Let \(U_B\) be the number of users with atleast one loan as borrower</p>
                <p>Let \(U_D\) be the number of users with atleast one loan defaulted as borrower</p>
                <p>Let \(c\) be the confidence that the true user default rate lies within \(i\); \(c = 0.95\)</p>
                <h5>Assume</h5>
                <p>The users so far is a representative sample of users in the future, and the sample is large enough for Fisher information to be a good estimate of \(-l''\).</p>
                <h5>Want</h5>
                <p>Let \(i\) be the Wald interval that the true user default rate lies within with confidence \(c\). \(i = (i_0, i_1)\)</p>
                <p>Let \(r = \frac{i_1 - i_0}{2}\)</p>

                <table class="table table-sm table-hover">
                  <thead>
                    <th>Math</th><th>Description</th>
                  </thead>
                  <tbody>
                    <tr>
                      <td>\(r = 1.96 \frac{1}{\sqrt{I(\hat{\pi})}}\)</td>
                      <td>Definition of standard error, where \(I(\hat{\pi})\) is the Fisher information</td>
                    </tr>
                    <tr>
                      <td>\(r = 1.96 \frac{1}{\sqrt{\frac{U_B}{\hat{\pi}(1 - \hat{\pi})}}}\)</td>
                      <td>Substitute \(I(p; n) = \frac{n}{p(1 - p)}\) for binomial distributions, where \(U_B = n\)</td>
                    </tr>
                    <tr>
                      <td>\(r = 1.96 \sqrt{\frac{\hat{\pi}(1 - \hat{\pi})}{U_B}}\)</td>
                      <td>Algebra</td>
                    </tr>
                    <tr>
                      <td>\(r = 1.96 \sqrt{\frac{\frac{U_D}{U_B}(1 - \frac{U_D}{U_B})}{U_B}}\)</td>
                      <td>Substitute \(\hat{\pi} = \frac{U_D}{U_B}\) for binomial distributions</td>
                    </tr>
                    <tr>
                      <td>\(i = \left(\frac{U_D}{U_B} - 1.96 \sqrt{\frac{\frac{U_D}{U_B}(1 - \frac{U_D}{U_B})}{U_B}}, \frac{U_D}{U_B} + 1.96 \sqrt{\frac{\frac{U_D}{U_B}(1 - \frac{U_D}{U_B})}{U_B}}\right)\)</td>
                      <td>Definition of \(i\) substituting \(\hat{\pi} = \frac{U_D}{U_B}\) and \(r\)</td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <h3>Recurring User Rate</h3>
              <p>Measures the likelihood that the next loan will be done by a user who has already <i>completed</i> a loan. A loan is considered completed if it is not deleted, has the same principal and principal repayment, and is not
              marked as unpaid.</p>
              <button class="btn btn-default my-toggle-button" type="button" data-toggle="collapse" data-target="#recurring-user-rate-calculations" aria-expanded="false" aria-controls="recurring-user-rate-calculations">Toggle Calculations</button>
              <div id="recurring-user-rate-calculations" class="collapse">
                <h4>Calculations</h4>
                <h5>Know</h5>
                <p>Let \(L_{RB}\) be the number of loans with a repeated borrower</p>
                <p>Let \(L\) be the total number of loans</p>
                <p>Let \(P\) be the number of outstanding loans</p>
                <p>Let \(c\) be the confidence that the true recurring user rate lies within \(i\); \(c = 0.95\)</p>
                <h5>Assume</h5>
                <p>Loans and users so far are a representative sample of future users/loans, and the sample size is large enough for Fisher information to be a good estimate of \(-l''\)</p>
                <h5>Want</h5>
                <p>Let \(i\) be the interval the true recurring user rate lies within with \(c\) confidence; \(i = (i_0, i_1)\)</p>
                <p>Let \(r = \frac{i_1 - i_0}{2}\)</p>

                <table class="table table-sm table-hover">
                  <thead>
                    <th>Math</th><th>Description</th>
                  </thead>
                  <tbody>
                    <tr>
                      <td>\(r = 1.96 \frac{1}{\sqrt{I(\hat{\pi})}}\)</td>
                      <td>Definition of standard error, where \(I(\hat{\pi})\) is the Fisher information</td>
                    </tr>
                    <tr>
                      <td>\(r = 1.96 \frac{1}{\sqrt{\frac{L - P}{\frac{L_{RB}}{L - P}\left(1 - \frac{L_{RB}}{L - P}\right)}}}\)</td>
                      <td>Substitute \(I(p; n) = \frac{n}{p(1-p)}\) for binomial distributions, where \(n=L_{RB}, p = \frac{L_{RB}}{L - P}\)</td>
                    </tr>
                    <tr>
                      <td>\(r = 1.96 \sqrt{\frac{\frac{L_{RB}}{L - P}\left(1 - \frac{L_{RB}}{L - P}\right)}{L - P}}\)</td>
                      <td>Algebra</td>
                    </tr>
                    <tr>
                      <td>\(i = \left(\frac{L_{RB}}{L - P} - 1.96 \sqrt{\frac{\frac{L_{RB}}{L - P}\left(1 - \frac{L_{RB}}{L - P}\right)}{L - P}}, \frac{L_{RB}}{L - P} + 1.96 \sqrt{\frac{\frac{L_{RB}}{L - P}\left(1 - \frac{L_{RB}}{L - P}\right)}{L - P}}\right)\)</td>
                      <td>Definition of \(i\) and \(r\)</td>
                  </tbody>
                </table>
              </div>
              <h3>Recurring User Default Rate</h3>
              <p>Measures the likelihood that the next loan by a recurring user will default.</p>
              <button class="btn btn-default my-toggle-button" type="button" data-toggle="collapse" data-target="#rec-user-default-rate-calc" aria-expanded="false" aria-controls="rec-user-default-rate-calc">Toggle Calculations</button>
              <div id="rec-user-default-rate-calc" class="collapse">
                <h4>Calculations</h4>
                <h5>Know</h5>
                <p>Let \(L_{RB}\) be the number of completed (same principal as principal repayment or unpaid) loans with a repeated borrower</p>
                <p>Let \(L_{D_{RB}}\) be the number of loans defaulted on by a repeated borrower</p>
                <p>Let \(c\) be the confidence that the true recurring user default rate is within \(i\); \(c = 0.95\)</p>
                <h5>Assume</h5>
                <p>Loans by recurring users is representative of future loans by recurring users, and the sample size is large enough for Fisher information to be a good estimate of \(-l''\)</p>
                <h5>Want</h5>
                <p>Let \(i\) be the interval the true recurring user default rate lies within with \(c\) confidence; \(i = (i_0, i_1)\)</p>
                <p>Let \(r = \frac{i_1 - i_0}{2}\)</p>

                <table class="table table-sm table-hover">
                  <thead>
                    <th>Math</th><th>Description</th>
                  </thead>
                  <tbody>
                    <tr>
                      <td>\(r = 1.96 \frac{1}{\sqrt{I(\hat{\pi})}}\)</td>
                      <td>Definition of standard error, where \(I(\hat{\pi})\) is the Fisher information</td>
                    </tr>
                    <tr>
                      <td>\(r = 1.96 \frac{1}{\sqrt{\frac{L_{RB}}{\frac{L_{D_{RB}}}{L_{RB}}(1 - \frac{L_{D_{RB}}}{L_{RB}})}}}\)</td>
                      <td>Substitute \(I(p; n) = \frac{n}{p(1-p)}\), \(p = \frac{L_{D_{RB}}}{L_{RB}}, n = L_{RB}\)</td>
                    </tr>
                    <tr>
                      <td>\(r = 1.96 \sqrt{\frac{\frac{L_{D_{RB}}}{L_{RB}}(1 - \frac{L_{D_{RB}}}{L_{RB}})}{L_{RB}}}\)</td>
                      <td>Algebra</td>
                    </tr>
                    <tr>
                      <td>\(i = \left(\frac{L_{D_{RB}}}{L_{RB}} - 1.96 \sqrt{\frac{\frac{L_{D_{RB}}}{L_{RB}}(1 - \frac{L_{D_{RB}}}{L_{RB}})}{L_{RB}}}, \frac{L_{D_{RB}}}{L_{RB}} + 1.96 \sqrt{\frac{\frac{L_{D_{RB}}}{L_{RB}}(1 - \frac{L_{D_{RB}}}{L_{RB}})}{L_{RB}}}\right)\)</td>
                      <td>Definition of \(i\) and \(r\)</td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <h3>Projected Loan Value</h3>
              <p>Measures the projected value of the next loan in the subreddit. Alternatively, measures the projected average value of all future loans. Since this range is large even
              with very low confidence (68%), the principal of loan requests is probably <b>not</b> normally distributed, and warrants further investigation.</p>
              <button class="btn btn-default my-toggle-button" type="button" data-toggle="collapse" data-target="#proj-loan-value-calc" aria-expanded="false" aria-controls="proj-loan-value-calc">Toggle Calculations</button>
              <div id="proj-loan-value-calc" class="collapse">
                <h4>Calculations</h4>
                <h5>Known</h5>
                <p>Let \(L\) be the number of loans</p>
                <p>Let \(m_i\) be the principal of loan \(i\)</p>
                <p>Let \(c\) be the confidence that the true average principal lies within \(i\); \(c = 0.68\)</p>
                <h5>Assume</h5>
                <p>Current loans are representative of future loans</p>
                <h5>Want</h5>
                <p>Let \(i\) be the interval the true average principal lies within with confidence \(c\); \(i = (i_0, i_1)\)</p>

                <table class="table table-sm table-hover">
                  <thead>
                    <th>Math</th><th>Description</th>
                  </thead>
                  <tbody>
                    <tr>
                      <td>\(\bar{m} = \sum\limits_{i=1}^{L} \frac{m_i}{L}\)</td>
                      <td>Definition of mean</td>
                    </tr>
                    <tr>
                      <td>\(S^2 = \sum\limits_{i=1}^{L} \frac{(m_i - \bar{m})^2}{L - 1}\)</td>
                      <td>Variance adjusted for sample rather than population</td>
                    </tr>
                    <tr>
                      <td>\(i = (\bar{m} - 1 \sqrt{S^2}, \bar{m} + 1 \sqrt{S^2})\)</td>
                      <td>Definition of standard error</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div style="clear: both;"></div>
          </div>
          <div class="graph-container">
            <h2>Loan Quantity vs Principal</h2>
            <p>As Table 2 shows, the projected loan value did not settle into a reasonable interval. At this time, it may be desirable to do a histogram of loan quantity vs principal.</p>
            <button class="btn btn-default my-toggle-button" type="button" data-toggle="collapse" data-target="#loan-qvp-binsize-text" aria-expanded="false" aria-controls="loan-qvp-binsize-text">Toggle Calculations</button>
            <div id="loan-qvp-binsize-text" class="collapse">
              <h3>Determining Bin Size</h3>
              <p>There are several methods for determining bin size. In this case Doane's formula is a reasonable choice, since a non-normal distribution is suspected.</p>
              <h5>Known</h5>
              <p>Let \(L\) be the number of loans</p>
              <p>Let \(k\) be the number of bins</p>
              <p>Let \(m_i\) be the principal of loan \(i\)</p>
              <p>Let \(m_{\text{min}}\) be the minimum loan principal, and be equal to 0</p>
              <p>Let \(m_{\text{max}}\) be the maximum loan principal, (from data)</p>
              <h5>Assume</h5>
              <p>Doane's Formula: \(k = 1 + \log_2(n) + \log_2(1 + \frac{\left|g_1\right|}{\sigma_{g_1}})\) where</p>
              <ul>
                <li>k - number of bins</li>
                <li>n - number of data points (\(n \equiv L\))</li>
                <li>\(g_1\) - estimated 3rd-moment-skewness \(\gamma_1\)</li>
                <li>\(\sigma_{g_1} = \sqrt{\frac{6(n-2)}{(n+1)(n+3)}}\)</li>
              </ul>
              
              <p>Sample Skewness Estimation: \(\gamma_1 \approx g_1 = \frac{\frac{1}{n} \sum\limits_{i=1}^{n}(x_i - \bar{x})^3}{\left(\frac{1}{n-1}\sum\limits_{i=1}^{n}(x_i-\bar{x})^2\right)^{\frac{3}{2}}}\)</p>
              <ul>
                <li>\(\bar{x}\) - sample mean (\(\bar{x} \equiv \bar{m}\))</li>
                <li>\(x_i\) - sample value \(i\) (\(x_i \equiv m_i\))</li>
              </ul>
              <h5>Want</h5>
              <p>Let \(h\) be the bin width</p>
              
              <table class="table table-sm table-hover">
                <thead>
                  <th>Math</th><th>Description</th>
                </thead>
                <tbody>
                  <tr>
                    <td>\(\bar{m} = \sum\limits_{i=1}^{L} \frac{m_i}{L}\)</td>
                    <td>Definition of mean</td>
                  </tr>
                  <tr>
                    <td>\(g_1 = \frac{\frac{1}{L}\sum\limits_{i=1}^{L}(m_i - \bar{m})^3}{\left(\frac{1}{L-1}\sum\limits_{i=1}^L(m_i-\bar{m})^{2}\right)^{\frac{3}{2}}}\)</td>
                    <td>Sample Skewness Estimation</td>
                  </tr>
                  <tr>
                    <td>\(\sigma_{g_1} = \sqrt{\frac{6(L-2)}{(L+1)(L+3)}}\)</td>
                    <td>Definition</td>
                  </tr>
                  <tr>
                    <td>\(k = 1 + \log_2(L) + \log_2(1 + \frac{\left|g_1\right|}{\sigma_{g_1}})\)</td>
                    <td>Doane's Formula</td>
                  </tr>
                  <tr>
                    <td>\(k = \text{ceil}\left(\frac{m_{\text{max}} - m_{\text{min}}}{h}\right) \approx \frac{m_{\text{max}} - m_{\text{min}}}{h}\)</td>
                    <td>Definition of bin size vs bin width over an interval \((m_{\text{min}},m_{\text{max}})\)</td>
                  </tr>
                  <tr>
                    <td>\(h = \frac{m_{\text{max}} - m_{\text{min}}}{k}\)</td>
                    <td>Algebra</td>
                  </tr>
                </tbody>
              </table>
              
            </div>
            <div class="graph-wrapper">
              <canvas id="loan-quantity-vs-principal" width="400" height="200"></canvas>
            </div>
            <div class="graph-description">
              <p>This graph shows how many loans are created at each dollar amount. Loans over $1050 are ignored, as they stretch the graph out beyond what is useful. The graph
              helps determine what average loan looks like, as well as the distribution of loans - clearly right-skewed (the majority of the loans are on the left). This confirms
              that the reason the projected loan value did not work is because the distribution of loans is not normal.</p>
            </div>
          </div>
          <div class="graph-container">
            <h2>Loans Fulfilled Over Time</h2>
            <div class="graph-wrapper">
              <canvas id="loans-fulfilled-over-time" width="400" height="200"></canvas>
            </div>
            <div class="graph-description">
              <p>This graph shows the number of loans that were fulfilled over time, and is another metric to measure
              the healthiness of the subreddit. A loan is fulfilled as soon as it is marked in the LoansBot database.
              If the number of loans fulfilled over time is increasing, more users are coming into the subreddit and/or
              existing users are making loans more often.</p>
            </div>
          </div>
        </div>
        <div id="todo" hidden>
          <h2>Todo</h2>
          <p>Besides everything labeled WIP, these haven't even been started:</p>
          <ul>
            <li>Large loans vs small loans default rate</li>
            <li>Long loans vs short loans default rate</li>
            <li>More average loan information, Median loan information</li>
          </ul>
        </div>
      </section>
    </div>
    <?php include('bootstrap_js.php') ?>
    <script src="/js/moment.js"></script>    
    <script src="/js/Chart.min.js"></script>
    <script src="/js/decimal.min.js"></script>
    <script src="js/status_text_utils.js"></script>
    <script src="/js/index.js"></script>
    <script type="text/x-mathjax-config">
      MathJax.Hub.Config({
        extensions: ['tex2jax.js'],
        jax: ['input/TeX', 'output/HTML-CSS'],
        tex2jax: {inlineMath: [['\\(', '\\)']]}
      });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.2/MathJax.js"></script>
  </body>
</html>
<?php
  $sql_conn->close();
?>
