//jQuery("#hideUrl").addClass("hide")


jQuery("#hasNoExternalData").click(
	function() {
 		alert( "Handler for .click() called." );
		jQuery("#urlExternalDataTextInput").slideUp();
	}
);

jQuery("#hasExternalData").click(
	function() {
		alert( "Handler for .click() called." );
		jQuery("#urlExternalDataTextInput").slideDown();
	}
);
