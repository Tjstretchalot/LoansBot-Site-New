var query2_parameters = {};

(function() {
  function generate_input_control(param_name, type, aria_label)  {
    var result = $("<input>");
    result.attr("type", type);
    result.addClass("form-control");
    result.attr("id", param_name + "-control");
    result.attr("aria-label", aria_label);
    return result;
  }
  function generate_container(param_name) {
    var result = $("<div>");
    result.addClass("form-group");
    result.data("param-name", param_name);

    return result;
  }

  function generate_label(param_name, content) {
    var result = $("<label>");
    result.text(content);
    return result;
  }

  function generate_simple_help_block(param_name, content) {
    var result = $("<div>");
    result.addClass("form-text");
    result.addClass("help-block");
    result.attr("id", param_name + "-helpblock");
    
    var header = $("<h1>");
    header.text("Help");
    result.append(header);
    
    var body = $("<p>");
    body.text(content);
    result.append(body);

    return result;
  }

  function generate_remove_button(param_name) {
    var result = $("<button>");
    result.attr("type", "button");
    result.addClass("btn");
    result.addClass("btn-danger");
    result.attr("id", param_name + "-remove");
    result.text("Remove");
    return result;
  }

  function combine_elements(param_name, container, things) {
    if(things.label) {
      if(things.control) {
        things.label.attr("for", param_name + "-control");
      }
      container.append(things.label);
    }

    if(things.control) {
      container.append(things.control);
    }

    if(things.help_block) {
      if(things.control) {
        things.control.attr("aria-describedby", param_name + "-helpblock");
      }
      container.append(things.help_block);
    }

    if(things.remove_button) {
      container.append(things.remove_button);
    }
  }

  // If you see this and you are wondering why I didn't just follow the normal object model...
  // there is no good reason
  function default_fetch_control() {
    return $("#" + this.param_name + "-control");
  }

  function default_fetch_params() {
    return [ this.fetch_control().val() ];
  }

  function default_send_params(all_params) {
    all_params[this.param_name] = this.fetch_params()[0];
  }

  function apply_defaults(partial_param) {
    if("undefined" === typeof(partial_param.fetch_control)) {
      partial_param.fetch_control = default_fetch_control;
    }

    if("undefined" === typeof(partial_param.fetch_params)) {
      partial_param.fetch_params = default_fetch_params;
    }

    if("undefined" === typeof(partial_param.send_params)) {
      partial_param.send_params = default_send_params;
    }

    return partial_param;
  }

  query2_parameters.limit = apply_defaults({
    param_name: "limit",
    name: "Limit",
    construct_html: function(limit) {
      limit = limit || 10;
      var container = generate_container(this.param_name);
      var label = generate_label(this.param_name, "Limit");
      var help_block = generate_simple_help_block(this.param_name, "Restricts the number of results to no more than the specified amount. When not included, the server will assume a default limit of 10 to avoid accidentally making very large queries. Use '0' as the limit to remove this restriction. Note that the loans API only guarrantees sorted results when a limit is specified and it is between 1 and 99 (inclusive)");
      var control = generate_input_control(this.param_name, "number", "Limit");
      control.attr("value", limit.toString());
      control.attr("min", "0");
      control.attr("step", "1");
      var remove_button = generate_remove_button(this.param_name);

      combine_elements(this.param_name, container, { label: label, control: control, help_block: help_block, remove_button: remove_button });
      return container;
    }
  });

  query2_parameters.id = apply_defaults({
    param_name: "id",
    name: "Loan ID",
    construct_html: function(id) {
      id = id || 0;
      var container = generate_container(this.param_name);
      var label = generate_label(this.param_name, "Loan ID");
      var help_block = generate_simple_help_block(this.param_name, "Restrict the result to only the loan id with the specified ID. Since loan ids are unique, this guarantees either 0 or 1 results");
      var control = generate_input_control(this.param_name, "number", "Loan identifier");
      control.attr("value", id.toString());
      control.attr("min", "1");
      control.attr("step", "1");
      var remove_button = generate_remove_button(this.param_name);

      combine_elements(this.param_name, container, { label: label, control: control, help_block: help_block, remove_button: remove_button });
      return container;
    }
  });

  query2_parameters.after_time = apply_defaults({
    param_name: "after_time",
    name: "Created After",
    construct_html: function(time) {
      if(!time)
        time = new Date();
      else
        time = new Date(time);
      var container = generate_container(this.param_name);
      var label = generate_label(this.param_name, "Created After");
      var help_block = generate_simple_help_block(this.param_name, "Restrict the results to loans that were created on or after midnight on the specified date. The created time, for loans after 2015, is the timestamp for the comment that generated the loan. For loans prior to 2015, created_at is the time when the loan was added to the database. For loans prior to 2015, this is using PST, for other loans it matches the reddit timezone.");
      var control = generate_input_control(this.param_name, "date", "Created after date");
      control.attr('value', time.toISOString().slice(0, 10));
      var remove_button = generate_remove_button(this.param_name);

      combine_elements(this.param_name, container, { label: label, control: control, help_block: help_block, remove_button: remove_button });
      return container;
    },
    send_params: function(all_params) {
      all_params[this.param_name] = new Date(this.fetch_params()[0]).getTime();
    }
  });

  query2_parameters.before_time = apply_defaults({
    param_name: "before_time",
    name: "Created Before",
    construct_html: function(time) {
      if(!time)
        time = new Date();
      else
        time = new Date(time);
      var container = generate_container(this.param_name);
      var label = generate_label(this.param_name, "Created Before");
      var help_block = generate_simple_help_block(this.param_name, "Restrict the results to loans that were created strictly before midnight on the specified date. The created time, for loans after 2015, is the timestamp for the comment that generated the loan. For loans prior to 2015, created_at is the time when the loan was added to the database. For loans prior to 2015, this is using PST, for other loans it matches the reddit timezone.");
      var control = generate_input_control(this.param_name, "date", "Created before date");
      control.attr('value', time.toISOString().slice(0, 10));
      var remove_button = generate_remove_button(this.param_name);

      combine_elements(this.param_name, container, { label: label, control: control, help_block: help_block, remove_button: remove_button });
      return container;
    },
    send_params: function(all_params) {
      all_params[this.param_name] = new Date(this.fetch_params()[0]).getTime();
    }
  });

  query2_parameters.borrower_name = apply_defaults({
    param_name: "borrower_name",
    name: "Borrower",
    construct_html: function(borrower) {
      borrower = borrower || "";
      var container = generate_container(this.param_name);
      var label = generate_label(this.param_name, "Borrower");
      var help_block = generate_simple_help_block(this.param_name, "Restrict the results to loans where the username for the borrower fuzzily matches the specified input. This uses a LIKE operator in SQL to perform the evaluation, which means you can use percent signs (%) to match any-number of any-character, and you can use underscores (_) to match exactly 1 of any character. So, for example, %hn would match john or kahn. %a% would match any username with an a in it. _ob would match bob or cob or sob but not snob or prob.");
      var control = generate_input_control(this.param_name, "text", "Borrower");
      control.attr('value', borrower);
      var remove_button = generate_remove_button(this.param_name);

      combine_elements(this.param_name, container, { label: label, control: control, help_block: help_block, remove_button: remove_button });
      return container;
    }
  });

  query2_parameters.lender_name = apply_defaults({
    param_name: "lender_name",
    name: "Lender",
    construct_html: function(lender) {
      lender = lender || "";
      var container = generate_container(this.param_name);
      var label = generate_label(this.param_name, "Lender");
      var help_block = generate_simple_help_block(this.param_name, "Restrict the results to loans where the username for the lender fuzzily matches the specified input. This uses a LIKE operator in SQL to perform the evaluation, which means you can use percent signs (%) to match any-number of any-character, and you can use underscores (_) to match exactly 1 of any character. So, for example, %hn would match john or kahn. %a% would match any username with an a in it. _ob would match bob or cob or sob but not snob or prob.");
      var control = generate_input_control(this.param_name, "text", "Lender");
      control.attr('value', lender);
      var remove_button = generate_remove_button(this.param_name);

      combine_elements(this.param_name, container, { label: label, control: control, help_block: help_block, remove_button: remove_button });
      return container;
    }
  });

  query2_parameters.includes_user_name = apply_defaults({
    param_name: "includes_user_name",
    name: "Includes User",
    construct_html: function(includes_user_name) {
      includes_user_name = includes_user_name || "";
      var container = generate_container(this.param_name);
      var label = generate_label(this.param_name, "Includes User");
      var help_block = generate_simple_help_block(this.param_name, "Restrict the results to loans where the username for either the lender or the borrower fuzzily matches the specified input. This uses a LIKE operator in SQL to perform the evaluation, which means you can use percent signs (%) to match any-number of any-character, and you can use underscores (_) to match exactly 1 of any character. So, for example, %hn would match john or kahn. %a% would match any username with an a in it. _ob would match bob or cob or sob but not snob or prob.");
      var control = generate_input_control(this.param_name, "text", "Includes User");
      control.attr('value', includes_user_name);
      var remove_button = generate_remove_button(this.param_name);

      combine_elements(this.param_name, container, { label: label, control: control, help_block: help_block, remove_button: remove_button });
      return container;
    }
  });

  query2_parameters.principal_cents = apply_defaults({
    param_name: "principal_cents",
    name: "Principal",
    construct_html: function(amt_cents) {
      amt_cents = amt_cents || "";
      var container = generate_container(this.param_name);
      var label = generate_label(this.param_name, "Principal");
      var help_block = generate_simple_help_block(this.param_name, "Restrict the results to loans with exactly the specified principal.");
      var control = generate_input_control(this.param_name, "number", "Principal");
      control.attr('value', amt_cents / 100);
      control.attr('step', '0.01');
      control.attr('min', '0');
      var remove_button = generate_remove_button(this.param_name);

      combine_elements(this.param_name, container, { label: label, control: control, help_block: help_block, remove_button: remove_button });
      return container;
    },
    fetch_params: function() {
      return [ Math.floor(parseFloat(this.fetch_control().val()) * 100) ]
    }
  });

  query2_parameters.principal_repayment_cents = apply_defaults({
    param_name: "principal_repayment_cents",
    name: "Repayment",
    construct_html: function(amt_cents) {
      amt_cents = amt_cents || "";
      var container = generate_container(this.param_name);
      var label = generate_label(this.param_name, "Repayment");
      var help_block = generate_simple_help_block(this.param_name, "Restrict the results to loans with exactly the specified repayment.");
      var control = generate_input_control(this.param_name, "number", "Repayment");
      control.attr('value', amt_cents / 100);
      control.attr('step', '0.01');
      control.attr('min', '0');
      var remove_button = generate_remove_button(this.param_name);

      combine_elements(this.param_name, container, { label: label, control: control, help_block: help_block, remove_button: remove_button });
      return container;
    },
    fetch_params: function() {
      return [ Math.floor(parseFloat(this.fetch_control().val()) * 100) ]
    }
  });

  query2_parameters.unpaid = apply_defaults({
    param_name: "unpaid",
    name: "Unpaid",
    construct_html: function(only_unpaid) {
      only_unpaid = ("undefined" === typeof(only_unpaid)) ? true : only_unpaid == 'true';
      var container = generate_container(this.param_name);
      var label = generate_label(this.param_name, "Unpaid");
      var help_block = generate_simple_help_block(this.param_name, "Restrict the results to loans that either have been flagged as unpaid or have not been flagged unpaid.");
      var control = $("<div>");
      
      var div1 = $("<div>");
      div1.addClass("form-check");
      div1.addClass("form-check-inline");
      var lab1 = $("<label>");
      lab1.addClass("form-check-label");
      var inp1 = $("<input>");
      inp1.addClass("form-check-input");
      inp1.attr("type", "radio");
      inp1.attr("name", "unpaid-radio");
      inp1.attr("value", "only-unpaid");
      inp1.attr("id", "unpaid-radio-only-unpaid");
      if(only_unpaid)
        inp1.attr("checked", true);
      lab1.append(inp1);
      lab1.append("Only Unpaid");
      div1.append(lab1);
      control.append(div1);

      var div2 = $("<div>");
      div2.addClass("form-check");
      div2.addClass("form-check-inline");
      var lab2 = $("<label>");
      lab2.addClass("form-check-label");
      var inp2 = $("<input>");
      inp2.addClass("form-check-input");
      inp2.attr("type", "radio");
      inp2.attr("name", "unpaid-radio");
      inp2.attr("value", "no-unpaid");
      inp2.attr("id", "unpaid-radio-no-unpaid");
      if(!only_unpaid)
        inp2.attr("checked", true);
      lab2.append(inp2);
      lab2.append("No Unpaid");
      div2.append(lab2);
      control.append(div2);

      var remove_button = generate_remove_button(this.param_name);

      combine_elements(this.param_name, container, { label: label, control: control, help_block: help_block, remove_button: remove_button });
      return container;
    },
    fetch_params: function() {
      return [ $("#unpaid-radio-only-unpaid").is(":checked") ]
    },
    send_params: function(all_params) {
      all_params.unpaid = this.fetch_params()[0] ? 1 : 0;
    }
  });
})();
