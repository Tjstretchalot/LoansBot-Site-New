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

  query2_parameters.limit = {
    param_name: "limit",
    name: "Limit",
    construct_html: function(limit) {
      limit = limit || 10;
      var container = generate_container(this.param_name);
      var label = generate_label(this.param_name, "Limit");
      var help_block = generate_simple_help_block(this.param_name, "Restricts the number of results to no more than the specified amount. When not included, the server will assume a default limit of 10 to avoid accidentally making very large queries. Use '0' as the limit to remove this restriction.");
      var control = generate_input_control(this.param_name, "number", "Limit");
      control.attr("value", limit.toString());
      control.attr("min", "0");
      control.attr("step", "1");
      var remove_button = generate_remove_button(this.param_name);

      combine_elements(this.param_name, container, { label: label, control: control, help_block: help_block, remove_button: remove_button });
      return container;
    },
    fetch_control: function() {
      return $("#" + this.param_name + "-control");
    },
    fetch_params: function() {
      return [ parseInt(this.fetch_control().val()) ];
    },
    send_params: function(all_params) {
      all_params.limit = this.fetch_params()[0];
    }
  };

  query2_parameters.id = {
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
    },
    fetch_control: function() {
      return $("#" + this.param_name + "-control");
    },
    fetch_params: function() {
      return [ parseInt(this.fetch_control().val()) ];
    },
    send_params: function(all_params) {
      all_params.id = this.fetch_params()[0];
    }
  };
})();
