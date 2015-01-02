;(function($) {
    $(function() {
        var dates = $('.bbp-datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            onSelect: function(selectedDate) {
                var option = $(this).is( '#bbp-report-start' ) ? 'minDate' : 'maxDate';
                var instance = $( this ).data( "datepicker" );

                var date = $.datepicker.parseDate(
                    instance.settings.dateFormat ||
                    $.datepicker._defaults.dateFormat,
                    selectedDate, instance.settings );

                dates.not( this ).datepicker( "option", option, date );
            }
        });
    });
})(jQuery);