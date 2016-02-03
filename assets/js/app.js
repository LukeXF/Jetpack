$("#wizard").steps({
    bodyTag: "fieldset",
    onFinished: function (event, currentIndex)
    {
        // Submission code
        $(this).submit();
    }
});

(function($) {

    $(document)
        .on( 'hidden.bs.modal', '.modal', function() {
            $(document.body).removeClass( 'modal-noscrollbar' );
        })
        .on( 'show.bs.modal', '.modal', function() {
            // Bootstrap adds margin-right: 15px to the body to account for a
            // scrollbar, but this causes a "shift" when the document isn't tall
            // enough to need a scrollbar; therefore, we disable the margin-right
            // when it isn't needed.
            if ( $(window).height() >= $(document).height() ) {
                $(document.body).addClass( 'modal-noscrollbar' );
            }
        });

})(window.jQuery);


$( document ).ready(function() {

    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })

});