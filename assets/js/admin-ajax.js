jQuery(document).ready(function($) {
    $('#roofer_area_id').on('change', function() {
        var area_id = $(this).val();
        if (!area_id) {
            $('#roofer_zip_codes').val('');
            $('#roofer_county').val('');
            $('#roofer_state').val('');
            return;
        }

        $.ajax({
            url: rooferAdmin.ajax_url,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'roofer_get_location_data',
                nonce: rooferAdmin.nonce,
                area_id: area_id
            },
            success: function(response) {
                if (response.success) {
                    $('#roofer_zip_codes').val(response.data.zip_codes.join(', '));
                    $('#roofer_county').val(response.data.county);
                    $('#roofer_state').val(response.data.state);
                } else {
                    $('#roofer_zip_codes').val('');
                    $('#roofer_county').val('');
                    $('#roofer_state').val('');
                    alert(response.data.message || 'Error fetching location data.');
                }
            },
            error: function() {
                $('#roofer_zip_codes').val('');
                $('#roofer_county').val('');
                $('#roofer_state').val('');
                alert('AJAX error occurred.');
            }
        });
    });
});
