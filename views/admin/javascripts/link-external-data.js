(function($) {

    $(document).ready(
        function() {

            $("#hasNoExternalData").click(
                function() {
                    $("#urlExternalDataTextInput").slideUp();
                }
            );

            $("#hasExternalData").click(
                function() {
                    $("#urlExternalDataTextInput").slideDown();
                }
            );
        }
    );

})(jQuery);