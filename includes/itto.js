jQuery(document).ready(function($) {
    // Update the test connection button state based on the API key being present.
    function updateTestButtonState() {
        var apiKey = $('#itto_api_key').val();
        $('#itto-test-connection-button').prop('disabled', !apiKey);
    }

    // Initial state check.
    updateTestButtonState();

    // Handle test connection form submission.
    $('#itto-test-connection-form').on('submit', function(e) {
        e.preventDefault();
        
        var $button = $('#itto-test-connection-button');
        var $result = $('#itto-test-connection-result');
        
        $button.prop('disabled', true);
        $result.html('<p>Testing connection...</p>');
        
        $.ajax({
            url: ittoCompanion.ajaxUrl,
            type: 'POST',
            data: {
                action: 'itto_companion_test_connection',
                nonce: ittoCompanion.nonce
            },
            success: function(response) {
                if (response.success) {
                    $result.html('<p style="color: green;">' + response.data + '</p>');
                } else {
                    $result.html('<p style="color: red;">' + response.data + '</p>');
                }
            },
            error: function() {
                $result.html('<p style="color: red;">Connection test failed. Please try again.</p>');
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });
});
