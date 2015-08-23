(function () {
    jQuery(document).on(
        'click',
        'a.ntdelbutton',
        function()
        {
            var tags = [];
            var $select = jQuery('select[name="featured_tag"]');
            var selected_value = jQuery('select[name="featured_tag"]').find('option:checked').text();

            jQuery('.tagsdiv .tagchecklist span').each( function() {
                tags.push(jQuery(this).text().replace("X", '').trim());
            });

            $select.find('option').each(
                function () {
                    var val = jQuery(this).text();

                    if( tags.indexOf(val) == -1 && jQuery(this).val() != "" )
                    {
                        jQuery(this).remove(); // this is no longer an allowed tag
                        if( selected_value == val )
                        {
                            alert("You are deleting your primary tag for this post.");
                        }
                    }
            });
        }
    );


}());