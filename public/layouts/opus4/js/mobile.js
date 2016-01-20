$( document ).on( "pagecreate", "#home", function() {
    $( "#autocomplete").on( "filterablebeforefilter", function( e, data ) {
        var $ul = $( this),
            $input = $( data.input),
            value = $input.val(),
            html = "";

        $ul.html( "" );

        if ( value && value.length > 2 ) {
            $ul.html( "<li><div class='ui-loader'><span class='ui-icon ui-icon-loading'></span></div></li>");
            $ul.listview( "refresh" );
            $.ajax({
                url: "mobile/index/autocomplete",
                dataType: "json",
                crossDomain: false,
                data: {
                    q: $input.val()
                }
            })
                .then( function ( response ) {
                    $.each( response, function( i, val ) {
                        html += "<li>" + val + "</li>";
                    });
                    $ul.html( html );
                    $ul.listview( "refresh" );
                    $ul.trigger( "updatelayout" );
                });
        }
    });

    // Submit search form when enter is pressed (without separate button)
    $('#autocomplete-search').keyup(function( e ) {
        if (e.which == 13) {
            $('#searchform').submit();
            return false;
        }
    });
});


