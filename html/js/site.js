"use strict";
function errorMessageFromFailResponse(result) {
  var errMess = '';
  var first = true;
  for(var i = 0; i < result.errors.length; i++) {
    if(first) {
      first = false;
    }else {
      errMess = errMess + "<br>";
    }
    errMess = errMess + result.errors[i].error_message;
  }
  return errMess;
}
