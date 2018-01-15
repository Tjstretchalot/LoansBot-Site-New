/*
 * Organized from called first -> called last (roughly)
 *
 * index.js - Layout:
 *   Constants
 *   "Unwrapped" Code
 *   General UI Code
 *
 *   ConfidenceInterval(c, min, max):
 *     confidence ; number
 *     min        ; number
 *     max        ; number
 *    
 *     confidenceToString()      ; string
 *     intervalToStringPercent() ; string
 *     intervalToStringDollar()  ; string
 *     intervalToString()        ; string
 *     toString()                ; string
 *
 *   Loan(loanId, lenderId, borrowerId, principalCents, principalRepaymentCents, unpaid, createdAt, updatedAt):
 *     loanId                  ; number
 *     lenderId                ; number
 *     borrowerId              ; number
 *     principalCents          ; number
 *     principalRepaymentCents ; number
 *     unpaid                  ; boolean
 *     createdAt               ; number
 *     updatedAt               ; number
 *
 *     isComplete() ; boolean
 *     
 *   LBData():
 *     loans ; array (of Loans)

 *     fetch(successCallback, failCallback) ; void
 *     getLoans(predicate)                  ; list
 *     ... WIP
 *
 *   LBDataAnalysis(LBData)
 *     getAllLoans()                               ; array (of Loans)
 *     getCompletedLoans()                         ; array (of Loans)
 *     getOutstandingLoans()                       ; array (of Loans)
 *     getUnpaidLoans()                            ; array (of Loans)
 *
 *     getFromLastXDays(loans, days, useUpdated)   ; array (of Loans)
 *
 *     centValue(loans)                            ; number
 *     getDistinctBorrowerIds(loans)               ; array (of numbers)
 *     getIndistinctBorrowerIds(loans)             ; array (of numbers)
 *
 *     estimateSkewness(arr, dataFn)               ; number
 *     getBinSizeDoane(arr, dataFn, skewness)      ; number
 *     groupBy(arr, dataFn, binSize, startAtFirst) ; array of [array (of object), number]
 *     groupByPrincipal(loan, binSizeCents)        ; array of [array (of Loans), number]
 *     groupByCreatedAt(loan, binSizeMS)           ; array of [array (of Loans), number]
 *
 *     getLoanDefaultRateCI()                    ; ConfidenceInterval
 *     getUserDefaultRateCI()                    ; ConfidenceInterval
 *     getRecurringUserRateCI()                  ; ConfidenceInterval
 *     getRecurringUserDefaultRateCI()           ; ConfidenceInterval
 *     getProjectedLoanValueCI()                 ; ConfidenceInterval
 *     ... WIP
 */

"use strict";

/*
 * Constants
 */
var CONTACT_ME_REDDIT_LINK = "https://www.reddit.com/message/compose?to=Tjstretchalot";
var CONTACT_ME_EMAIL_LINK  = "mailto:mtimothy984@gmail.com";

/*
 * "Unwrapped" Code
 */
$(document).ready(function() {
  implementToggleButtons();
  implementLoadDataButton();
});

/*
 * General UI Code
 */
function implementToggleButtons() {
  /**
   * Allows for easy toggle buttons of the form
   * <a href="#" class="toggle" for="#selector">Button Text</a>
   *
   * Calls slideToggle on the selector when the button is clicked
   **/
  $(".toggle").click(function(e) {
    e.preventDefault(); 
    $($(this).attr("for")).slideToggle();
  });
}

function implementLoadDataButton() {


  $("#fetch-data-button").click(function(e) {
    e.preventDefault();
  });
};

/*
 * ConfidenceInterval
 */
var ConfidenceInterval = function(c, min, max) {
  /**
   * Constructs a confidence interval with confidence c (between 0 and 1, inclusive), with 
   * a lower interval of min (inclusive) and an upper interval of max (inclusive)
   **/
  this.confidence = c;
  this.min = min;
  this.max = max;
};

ConfidenceInterval.prototype.confidenceToString = function() {
  var res = (Math.floor(this.confidence * 10000) / 100.).toLocaleString() + "%";
  if(this.confidence < 0.8) {
    res = "<b>" + res + "</b>";
  }
  return res;
};

ConfidenceInterval.prototype.intervalToStringPercent = function() {
  return (Math.floor(this.min * 10000) / 100.).toLocaleString() + "% &#8210; " + (Math.floor(this.max * 10000) / 100.).toLocaleString() + "%";
}

ConfidenceInterval.prototype.intervalToStringDollar = function() {
  return "$" + (Math.floor(this.min) / 100.).toLocaleString() + " &#8210; $" + (Math.floor(this.max) / 100.).toLocaleString();
};

ConfidenceInterval.prototype.intervalToString = function() {
  return (Math.floor(this.min * 100) / 100.).toLocaleString() + " &#8210; " + (Math.floor(this.max * 100) / 100.).toLocaleString();
}

ConfidenceInterval.prototype.toString = function() {
  return this.confidenceToString() + " confident that real value is in " + this.intervalToString();
};

/*
 * Loan
 */
var Loan = function(loanId, lenderId, borrowerId, principalCents, principalRepaymentCents, unpaid, createdAt, updatedAt) {
  this.loanId = loanId;
  this.lenderId = lenderId;
  this.borrowerId = borrowerId;
  this.principalCents = principalCents;
  this.principalRepaymentCents = principalRepaymentCents;
  this.unpaid = unpaid;
  this.createdAt = createdAt;
  this.updatedAt = updatedAt;
};

Loan.prototype.isComplete = function() {
  /**
   * Checks if this loan has the same principal as principal repayment
   * @return boolean
   **/
  return this.principalCents === this.principalRepaymentCents;
};

/*
 * LBData
 */
var LBData = function() { /** Prepares an LBData instance with no data */ }

LBData.prototype.fetch = function(successCallback, failCallback) {
  /**
   * Fetches the raw data from the server using the API exposed 
   * in https://github.com/Tjstretchalot/LoansBot-Site, and calls
   * success callback once the data is ready for use, or the fail
   * callback if the data couldn't be loaded.
   */
  var me = this;
  $.get("https://redditloans.com/api/loans.php", {limit: 0, format: 1}, function(data, stat) {
    me.loans = [];
    data.loans.forEach(function(ele, ind, arr) {
      me.loans[ind] = new Loan(ele[0], ele[1], ele[2], ele[3], ele[4], ele[5] === 1 ? true : false, ele[6], ele[7]);
    });
    successCallback();
  }).fail(function($xhr) {
    console.log($xhr.responseJSON);
    failCallback();
  });
};

LBData.prototype.getLoans = function(predicate) {
  /**
   * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/filter
   **/
  return this.loans.filter(predicate);
};

/*
 * LBDataAnalysis
 */
var LBDataAnalysis = function(lbData) {
  /** 
   * Constructs the analysis object with the specified data. The data
   * should be ready for use by the time any functions are called
   **/
  this.lbData = lbData;
};

LBDataAnalysis.prototype.getAllLoans = function() {
  /**
   * Gets every loan
   * @return array (of Loans)
   **/
  return this.lbData.loans.slice();
};

LBDataAnalysis.prototype.getCompletedLoans = function() {
  /**
   * Gets every completed loan
   * @return array (of Loans)
   **/
  return this.lbData.getLoans(function(ele, ind, arr) { return ele.isComplete(); });
};

LBDataAnalysis.prototype.getOutstandingLoans = function() {
  /**
   * Gets every outstanding loan (not unpaid, not complete)
   * @return array (of Loans)
   **/
  return this.lbData.getLoans(function(ele, ind, arr) { return !ele.isComplete() && !ele.unpaid; });
};

LBDataAnalysis.prototype.getUnpaidLoans = function() {
  /**
   * Gets every unpaid loan
   * @return array (of Loans)
   **/
  return this.lbData.getLoans(function(ele, ind, arr) { return ele.unpaid; });
};

LBDataAnalysis.prototype.getFromLastXDays = function(loans, days, useUpdatedInsteadOfCreated) {
  /**
   * Gets the loans from the last x days
   * @return array (of Loans)
   */
  
  var currentServerTime = Date.now()
  var filterBefore = currentServerTime - 86400000 * days
  return loans.filter(function(ele, ind, arr) {
    if(!useUpdatedInsteadOfCreated) {
      return ele.createdAt >= filterBefore; 
    }else {
      return ele.updatedAt >= filterBefore;
    }
  });
}

LBDataAnalysis.prototype.centValue = function(loans) {
  /**
   * Calculates the sum of the principal of the specified
   * loans.
   *
   * @param loans array (of Loans)
   * @return number
   **/
  var result = 0;
  loans.forEach(function(ele, ind, arr) {
    result += ele.principalCents;
  });
  return result;
};

LBDataAnalysis.prototype.getDistinctBorrowerIds = function(loans) {
  /**
   * Finds each borrower ids in the list of loans as an array
   * without duplicates
   *
   * @param loans array (of Loans)
   * @return array (of numbers)
   **/
   var result = [];
   loans.forEach(function(ele, ind, arr) {
     if($.inArray(ele.borrowerId, result) === -1) {
       result.push(ele.borrowerId);
     }
   });
   return result;
};

LBDataAnalysis.prototype.getIndistinctBorrowerIds = function(loans) {
  /**
   * Finds the list of borrower ids in the lists of loans such that
   * each id in the result shows up as a borrower in the loans at least
   * twice.
   *
   * @param loans array (of Loans)
   * @return array (of numbers)
   **/
  var idsToCount = [];
  loans.forEach(function(ele, ind, arr) {
    if(idsToCount[ele.borrowerId]) {
      idsToCount[ele.borrowerId]++;
    }else {
      idsToCount[ele.borrowerId] = 1;
    }
  });

  var result = [];
  idsToCount.forEach(function(ele, ind, arr) {
    if(ele > 1) {
      result.push(ind);
    }
  });

  return result;
}

LBDataAnalysis.prototype.estimateSkewness = function(arr, dataFn) {
  /**
   * Estimates the skewness of the data using a natural method
   * of moments estimator.
   * 
   * @param arr an array of things, such that
   * @param dataFn dataFn(arr[i]) returns a number
   * @see https://en.wikipedia.org/wiki/Skewness
   */

   // step one: mean
   var total = new Decimal(0);
   arr.forEach(function(ele, ind, arr) {
     var data = dataFn(ele);

     if(typeof(data) === 'number') {
       total = total.plus(data);
     }
   });
   var mean = total.dividedBy(arr.length);
   
   // step 2: sum of squared and cubed distance from mean
   var squaredDistToMeanTotal = new Decimal(0);
   var cubedDistToMeanTotal = new Decimal(0);
   arr.forEach(function(ele, ind, arr) {
     var data = dataFn(ele);
     
     if(typeof(data) === 'number') {
       var dist = new Decimal(data).minus(mean);
       squaredDistToMeanTotal = squaredDistToMeanTotal.plus(dist.toPower(2));
       cubedDistToMeanTotal = cubedDistToMeanTotal.plus(dist.toPower(3));
     }
   })
   
   // step 3: numerator
   var numerator = (new Decimal(1).dividedBy(arr.length)).times(cubedDistToMeanTotal);
   
   // step 4: denominator
   var denominator = ((new Decimal(1).dividedBy(arr.length - 1)).times(squaredDistToMeanTotal)).toPower(1.5);

   // step 5: done
   return numerator.dividedBy(denominator).toNumber();
};

LBDataAnalysis.prototype.getBinSizeDoane = function(arr, dataFn, skewness) {
  /**
   * Approximates a reasonable bin size for the data using Doane's formula
   *
   * @param arr an array of things, such that
   * @param dataFn dataFn(arr[i]) returns a number
   * @param skewness the skewness of the data
   * @see https://en.wikipedia.org/wiki/Histogram
   */
   
   // step 1: min and max
   var minVal = Number.MAX_VALUE;
   var maxVal = Number.MIN_VALUE;

   arr.forEach(function(ele, ind, arr) {
     var val = dataFn(ele);
     if(typeof(val) === 'number') {
       minVal = Math.min(minVal, val);
       maxVal = Math.max(maxVal, val);
     }
   });

   // step 2: sigma 
   var sigma = new Decimal(6).times(new Decimal(arr.length).minus(2)).dividedBy(new Decimal(arr.length).plus(1).times(new Decimal(arr.length).plus(3))).squareRoot();

   // step 3: get k 
   var k = new Decimal(1).plus(new Decimal(arr.length).log(2)).plus(new Decimal(1).plus(new Decimal(skewness).absoluteValue().dividedBy(sigma)).log(2));
   
   // step 4: done
   return new Decimal(maxVal).minus(minVal).dividedBy(k).toNumber();
};

LBDataAnalysis.prototype.groupBy = function(arr, dataFn, binSize, startAtFirst) {
  /** 
   * Generic function to group an array that can fetch
   * a value from each element with dataFn, into bins of
   * binSize
   **/

  var start = 0;
  if(startAtFirst) {
    var smallest = dataFn(arr[0]);
    arr.forEach(function(ele, ind, arr) {
      smallest = Math.min(smallest, dataFn(ele));
    });
    start = smallest;
  }

  var result = [];
  var largest = -1;
  arr.forEach(function(ele, ind, arr) {
    var index = Math.floor((dataFn(ele) - start) / binSize);
    if(index > largest) { largest = index; }
    if(typeof(result[index]) === 'undefined') { result[index] = []; }
    result[index].push(ele)
  });
  for(var i = 0; i < largest; i++) {
    if(typeof(result[i]) === 'undefined') {
      result[i] = [];
    }
  }
  
  return [result, start];
};

LBDataAnalysis.prototype.groupByPrincipal = function(loans, binSizeCents) {
  /**
   * Groups the specified loans by their principal. Returns 
   * an array of array of loans. The first index has loans 
   * with a principal between 0 and binSizeCents.
   *
   * @param loans array (of Loans)
   * @param binSizeCents desired size of each bin
   * @return array of [array of loans, start];
   **/
  return this.groupBy(loans, (ele) => ele.principalCents, binSizeCents, false);
};

LBDataAnalysis.prototype.groupByCreatedAt = function(loans, binSizeMS) {
  /**
   * Groups the specified loans by their created at timestamp. Returns 
   * an array of array of loans. THe first index has loans with a created
   * at between the first loan the binSizeMS + first loan time
   *
   * @param loans array (of Loans)
   * @param binSizeMS desired size of each bin
   * @return array of [array of loans, start]
   **/

   return this.groupBy(loans, (ele) => ele.createdAt, binSizeMS, true);
};

LBDataAnalysis.prototype.getLoanDefaultRateCI = function() {
  /**
   * Calculates the loan default rate confidence interval using Fisher information.
   * @return ConfidenceInterval loan default rate
   **/
  var L = this.getAllLoans().length;
  var P = this.getOutstandingLoans().length;
  var D = this.getUnpaidLoans().length;
  
  var Ls = L - P;

  var r = 1.96 * Math.sqrt( ((D / Ls) * (1 - (D / Ls))) / (Ls) );
  return new ConfidenceInterval(0.95, (D / Ls) - r, (D / Ls) + r); 
};

LBDataAnalysis.prototype.getUserDefaultRateCI = function() {
  /**
   * Calculates the user default rate confidence interval using Fisher information.
   * @return ConfidenceInterval user default rate
   **/
  var UB = this.getDistinctBorrowerIds(this.getAllLoans()).length;
  var UD = this.getDistinctBorrowerIds(this.getUnpaidLoans()).length;
  var r = 1.96 * Math.sqrt( ((UD / UB) * (1 - UD / UB)) / UB );
  return new ConfidenceInterval(0.95, (UD / UB) - r, (UD / UB) + r);
};

LBDataAnalysis.prototype.getRecurringUserRateCI = function() {
  /**
   * Calculates the recurring user rate confidence interval using Fisher information
   * @return ConfidenceInterval recurring user rate
   **/
  var LRB = this.getIndistinctBorrowerIds(this.getCompletedLoans()).length;
  var LC = this.getCompletedLoans().length;
 
  var r = 1.96 * Math.sqrt( ((LRB / LC) * (1 - (LRB / LC))) / LC );
  return new ConfidenceInterval(0.95, (LRB / LC) - r, (LRB / LC) + r);
};

LBDataAnalysis.prototype.getRecurringUserDefaultRateCI = function() {
  /**
   * Calculates the recurring user default rate confidence interval using Fisher information
   * @return ConfidenceInterval recurring user default rate
   **/
   // Finding LRB
   var completedLoans = this.getCompletedLoans();
   var borrowersWithMultipleCompletedLoans = this.getIndistinctBorrowerIds(completedLoans);
   var completedLoansWithBorrowersWithMultipleCompletedLoans = completedLoans.filter(function(ele, ind, arr) {
      return $.inArray(ele.borrowerId, borrowersWithMultipleCompletedLoans) !== -1;
   });
   
   // Finding LDRB
   var loansRepaidInFull = this.lbData.getLoans(function(ele, ind, arr) {
     return ele.principalCents === ele.principalRepaymentCents;
   });
   var borrowersWithAtleastOneRepaidLoan = this.getDistinctBorrowerIds(loansRepaidInFull);
   var loansUnpaidWithBorrowerWithAtleastOneRepaidLoan = this.lbData.getLoans(function(ele, ind, arr) {
     return ele.unpaid && $.inArray(ele.borrowerId, borrowersWithAtleastOneRepaidLoan) !== -1;
   });

   var LRB = completedLoansWithBorrowersWithMultipleCompletedLoans.length;
   var LDRB = loansUnpaidWithBorrowerWithAtleastOneRepaidLoan.length;

   var r = 1.96 * Math.sqrt( ((LDRB / LRB) * (1 - (LDRB / LRB))) / LRB );
   return new ConfidenceInterval(0.95, (LDRB / LRB) - r, (LDRB / LRB) + r);
}

LBDataAnalysis.prototype.getProjectedLoanValueCI = function() {
  /**
   * Calculates the projected loan value confidence interval using standard error
   * @return ConfidenceInterval projected loan value in cents
   **/
  // Step 1: Finding the mean
  var allLoans = this.getAllLoans();
  var meanCentsD = new Decimal(0);
  allLoans.forEach(function(ele, ind, arr) {
    meanCentsD = meanCentsD.plus(ele.principalCents);
  });
  meanCentsD = meanCentsD.div(allLoans.length);
  
  // Step 2: Finding S^2
  var stdErrorSqD = new Decimal(0);
  allLoans.forEach(function(ele, ind, arr) {
    var diff = new Decimal(ele.principalCents).minus(meanCentsD);
    var diffSq = diff.times(diff);
    var diffSqDivN = diffSq.div(allLoans.length);
    stdErrorSqD = stdErrorSqD.plus(diffSqDivN);
  });
 
  // Step 3: Finding r
  var r = Math.ceil(1 * stdErrorSqD.squareRoot().toNumber());
  return new ConfidenceInterval(0.68, meanCentsD.toNumber() - r, meanCentsD.toNumber() + r);
};

// Misc functions
function analyzeData(lbData) {
  /**
   * This is called if the data is successfully loaded below. It needs to go and and
   * update the following selectors:
   *
   * #loans-lent-total       : # ($)
   * #loans-lent-month       : # ($)
   * #loans-lent-week        : # ($)
   * #loans-outstanding-total: # ($)
   * #loans-outstanding-month: # ($)
   * #loans-outstanding-week : # ($)
   * #loans-unpaid-total     : # ($)
   * #loans-unpaid-month     : # ($)
   * #loans-unpaid-week      : # ($)
   * 
   * #loans-default-rate-confidence:          %  ;  #loans-default-rate-interval:          % - %
   * #users-default-rate-confidence:          %  ;  #users-default-rate-interval:          % - %
   * #recurring-user-rate-confidence:         %  ;  #recurring-user-rate-interval:         % - %
   * #recurring-user-default-rate-confidence: %  ;  #recurring-user-default-rate-interval: % - %
   * #projected-loan-value-confidence:        %  ;  #projected-loan-value-interval:        $ - $
   * 
   * As well as the following graphs:
   * #loan-quantity-vs-principal
   * #loans-fulfilled-over-time
   *
   * ...WIP
   */
   var analysis = new LBDataAnalysis(lbData);

   // #loans-lent-*
   var allLoans = analysis.getAllLoans();
   var loansLastMonth = analysis.getFromLastXDays(allLoans, 30);
   var loansLastWeek = analysis.getFromLastXDays(allLoans, 7);
   $("#loans-lent-total").html(allLoans.length + " ($" + (analysis.centValue(allLoans) / 100.).toLocaleString() + ")");
   $("#loans-lent-month").html(loansLastMonth.length + " ($" + (analysis.centValue(loansLastMonth) / 100.).toLocaleString() + ")");
   $("#loans-lent-week").html(loansLastWeek.length + " ($" + (analysis.centValue(loansLastWeek) / 100.).toLocaleString() + ")");
   
   // #loans-outstanding-*
   var outstandingLoans = analysis.getOutstandingLoans();
   var outstandingLastMonth = analysis.getFromLastXDays(outstandingLoans, 30);
   var outstandingLastWeek = analysis.getFromLastXDays(outstandingLoans, 7);
   $("#loans-outstanding-total").html(outstandingLoans.length + " ($" + (analysis.centValue(outstandingLoans) / 100.).toLocaleString() + ")"); 
   $("#loans-outstanding-month").html(outstandingLastMonth.length + " ($" + (analysis.centValue(outstandingLastMonth) / 100.).toLocaleString() + ")");
   $("#loans-outstanding-week").html(outstandingLastWeek.length + " ($" + (analysis.centValue(outstandingLastWeek) / 100.).toLocaleString() + ")");

   // #loans-unpaid-*
   var unpaidLoans = analysis.getUnpaidLoans();
   var unpaidLastMonth = analysis.getFromLastXDays(unpaidLoans, 30, true);
   var unpaidLastWeek = analysis.getFromLastXDays(unpaidLoans, 7, true);
   $("#loans-unpaid-total").html(unpaidLoans.length + " ($" + (analysis.centValue(unpaidLoans) / 100.).toLocaleString() + ")");
   $("#loans-unpaid-month").html(unpaidLastMonth.length + " ($" + (analysis.centValue(unpaidLastMonth) / 100.).toLocaleString() + ")");
   $("#loans-unpaid-week").html(unpaidLastWeek.length + " ($" + (analysis.centValue(unpaidLastWeek) / 100.).toLocaleString() + ")");

     
   // #loans-default-rate-confidence, #loans-default-rate-interval
   var ldrCI = analysis.getLoanDefaultRateCI();
   $("#loans-default-rate-confidence").html(ldrCI.confidenceToString());
   $("#loans-default-rate-interval").html(ldrCI.intervalToStringPercent());

   // #users-default-rate-confidence, #users-default-rate-interval
   var udrCI = analysis.getUserDefaultRateCI();
   $("#users-default-rate-confidence").html(udrCI.confidenceToString());
   $("#users-default-rate-interval").html(udrCI.intervalToStringPercent());

   // #recurring-user-rate-confidence, #recurring-user-rate-interval
   var rcurCI = analysis.getRecurringUserRateCI();
   $("#recurring-user-rate-confidence").html(rcurCI.confidenceToString());
   $("#recurring-user-rate-interval").html(rcurCI.intervalToStringPercent());

   // #recurring-user-default-rate-confidence, #recurring-user-default-rate-interval
   var rcudrCI = analysis.getRecurringUserDefaultRateCI();
   $("#recurring-user-default-rate-confidence").html(rcudrCI.confidenceToString());
   $("#recurring-user-default-rate-interval").html(rcudrCI.intervalToStringPercent());

   // #projected-loan-value-confidence, #projected-loan-value-interval
   var prlCI = analysis.getProjectedLoanValueCI();
   $("#projected-loan-value-confidence").html(prlCI.confidenceToString());
   $("#projected-loan-value-interval").html(prlCI.intervalToStringDollar());


   // *************************************
   // *    #loan-quantity-vs-principal    *
   // *************************************

   var loansNoOutliers = analysis.lbData.getLoans(function(ele, ind, arr) { return ele.principalCents < 1050 * 100; });

   var getPrincipalOfLoan = function(loan) {
     return loan.principalCents;
   };
 
   var skewnessPrincipal = analysis.estimateSkewness(loansNoOutliers, getPrincipalOfLoan);
   var binSizeCents = analysis.getBinSizeDoane(loansNoOutliers, getPrincipalOfLoan, skewnessPrincipal);
   var binSizeDollars = Math.round(binSizeCents / 100);
   if(binSizeCents < 1000) {
     binSizeCents = 1000;
     binSizeDollars = 10;
   }
   var groupedByPrincipal = analysis.groupByPrincipal(loansNoOutliers, binSizeCents)[0];
   var labels = [];
   var data = [];
   var i = 0;
   for(; i < groupedByPrincipal.length; i++) {
     labels[i] = "$" + (i*binSizeDollars) + "-$" + (i*binSizeDollars + binSizeDollars);
     if(groupedByPrincipal[i]) {
       data[i] = groupedByPrincipal[i].length;
     }else {
       data[i] = 0;
     }
   }
   
   new Chart($("#loan-quantity-vs-principal"), {
     type: 'bar',
     data: {
       labels: labels,
       datasets: [{
         label: "# of Loans",
         data: data,
         fill: false,
         borderColor: "rgba(100, 100, 255, 1)",
         backgroundColor: "rgba(100, 100, 255, 1)",
         pointBorderColor: "transparent",
         pointBackgroundColor: "transparent"
       }]
     }
   });

   // *************************************
   // *    #loans-fulfilled-over-time     *
   // *************************************

   loansNoOutliers = analysis.lbData.getLoans(function(ele, ind, arr) { return ele.createdAt > 1000; });
   var getCreatedAtOfLoan = function(loan) { return loan.createdAt; };

   skewnessPrincipal = analysis.estimateSkewness(loansNoOutliers, getCreatedAtOfLoan);
   var binSizeMs = analysis.getBinSizeDoane(loansNoOutliers, getCreatedAtOfLoan, skewnessPrincipal);

   var groupedByInfo = analysis.groupByCreatedAt(loansNoOutliers, binSizeMs);
   var groupedByCreatedAt = groupedByInfo[0];
   var groupedByCreatedAtStart = groupedByInfo[1];

   labels = [];
   data = [];
   i = 0;
   for(; i < groupedByCreatedAt.length - 1; i++) {
     var dateStart = (groupedByCreatedAtStart + i*binSizeMs)
     var dateStartStr = new Date(dateStart).toLocaleDateString();
     var dateEnd = (groupedByCreatedAtStart + (i + 1)*binSizeMs);
     var dateEndStr = new Date(dateEnd).toLocaleDateString();

     labels[i] = dateStartStr + "-" + dateEndStr;
     data[i] = groupedByCreatedAt[i].length;
   }

   new Chart($("#loans-fulfilled-over-time"), {
     type: 'line',
     data: {
       labels: labels,
       datasets: [{
         label: "# of Loans",
         data: data,
         borderColor: "rgba(100, 100, 255, 1)"
       }]
     }
   });
};

function loadData() {
  /**
   * The data should be fetched and analyzed.
   *
   * In case this function takes a while, there is also a div that looks like:
   * <div id="fetch-data-status-text" hidden></div>
   *
   * This div can be further styled using any of the bootstrap backgrounds. See 
   * status_text_utils for information on how that works.
   *
   * Finally, in the event of failure, the user should be directed to contact me via
   * reddit (CONTACT_ME_REDDIT_LINK) or through email (CONTACT_ME_EMAIL_LINK)
   **/
  var statusText = $("#fetch-data-status-text");

  // Configuration
  var fetchingDataText = LOADING_GLYPHICON + ' Fetching data...';
  var fetchingDataBackground = 'info';

  var fetchDataFailedText = FAILURE_GLYPHICON + ' Failed to fetch data! If this keeps happening, contact me through '
                          + '<a href="' + CONTACT_ME_REDDIT_LINK + '">reddit</a> or <a href="' + CONTACT_ME_EMAIL_LINK + '">email</a>.';
  var fetchDataFailedBackground = 'danger';

  var analyzingDataText = LOADING_GLYPHICON + ' Analyzing data...';
  var analyzingDataBackground = 'info';

  var analyzingDataFailedText = FAILURE_GLYPHICON + ' Failed to analyze data! This is definitely <b>not</b> your fault - '
                              + 'this page may be undergoing maintanence, so wait a few minutes and try again. If it still doesn\'t work, contact me '
                              + 'through <a href="' + CONTACT_ME_REDDIT_LINK + '">reddit</a> or <a href="' + CONTACT_ME_EMAIL_LINK + '">email</a>.';
  var analyzingDataFailedBackground = 'danger';

  var successText = SUCCESS_GLYPHICON + ' Success!';
  var successBackground = 'success';

  // Implementation
  set_status_text(statusText, fetchingDataText, fetchingDataBackground, true); 

  var lbData = new LBData();
  lbData.fetch(function() {
    set_status_text(statusText, analyzingDataText, analyzingDataBackground, true).then(function() {
      // Delay processing to allow status text update
      setTimeout(function() {
        try {
          analyzeData(lbData);

          set_status_text(statusText, successText, successBackground, true);
        }catch(err) {
          console.log(err);
          if(typeof(err.stack) !== 'undefined') {
            console.log(err.stack);
          }
          if(typeof(err.line) !== 'undefined') {
            console.log(err.line);
          }
          set_status_text(statusText, analyzingDataFailedText, analyzingDataFailedBackground, false);
        }
      }, 100);
    });
  }, function() {
    set_status_text(statusText, fetchDataFailedText, fetchDataFailedBackground, false);
  });
}

$(window).ready(function() {
  loadData();
});
