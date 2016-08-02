(function($){
    var $link;

    var makerspaceCheckout = function() {
        $.ajax({
            url: MakerspaceCheckinSettings.root + 'makerspace/checkout',
            type: 'post',
            headers: {
                'X-WP-Nonce': MakerspaceCheckinSettings.nonce
            },
            success: function (data) {
                if (data) {
                    $link.text('Checkin');
                    $link.attr('href', '#checkin');
                } else {
                    alert( 'There was an error checking you out' );
                }
            }
        });
    };

    var makerspaceCheckin = function() {
        $.ajax({
            url: MakerspaceCheckinSettings.root + 'makerspace/checkin',
            type: 'post',
            headers: {
                'X-WP-Nonce': MakerspaceCheckinSettings.nonce
            },
            success: function (data) {
                if (data) {
                    $link.text('Checkout');
                    $link.attr('href', '#checkout');
                } else {
                    alert( 'There was an error checking you in' );
                }
            }
        });
    };

    $(document.body).on('click', '#wp-admin-bar-makerspace-checkinout a', function(e){
        e.preventDefault();
        $link = $(this);
        var href = $link.attr('href');
        if (href == '#checkout') {
            makerspaceCheckout();
        } else {
            makerspaceCheckin();
        }
    });
})(jQuery);
