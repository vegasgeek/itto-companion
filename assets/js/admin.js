jQuery(document).ready(function($) {
    // Test connection functionality
    $('#itto-test-connection').on('click', function() {
        const button = $(this);
        const resultDiv = $('#itto-test-result');
        
        button.prop('disabled', true);
        resultDiv.html('<div class="notice notice-info"><p>Testing connection...</p></div>');

        const apiUrl = $('#itto_companion_api_url').val().trim();
        const siteHash = $('#itto_companion_site_hash').val().trim();

        // Validate inputs
        if (!apiUrl || !siteHash) {
            resultDiv.html('<div class="notice notice-error"><p>Please configure both the API URL and Site Hash first.</p></div>');
            button.prop('disabled', false);
            return;
        }

        // Validate URL format
        try {
            new URL(apiUrl);
        } catch (e) {
            resultDiv.html('<div class="notice notice-error"><p>Invalid API URL format. Please enter a valid URL.</p></div>');
            button.prop('disabled', false);
            return;
        }

        // Validate site hash format
        if (!/^[a-zA-Z0-9]{12}$/.test(siteHash)) {
            resultDiv.html('<div class="notice notice-error"><p>Invalid Site Hash format. It should be 12 characters long and contain only letters and numbers.</p></div>');
            button.prop('disabled', false);
            return;
        }

        // Test the connection by sending a test check-in
        $.ajax({
            url: apiUrl + '/wp-json/itto/v1/test',
            type: 'POST',
            data: JSON.stringify({
                site_hash: siteHash
            }),
            contentType: 'application/json',
            timeout: 15000, // 15 second timeout
            success: function(response) {
                if (response && response.success) {
                    resultDiv.html('<div class="notice notice-success"><p>Connection successful! The ITTO service responded correctly.</p></div>');
                } else {
                    resultDiv.html('<div class="notice notice-error"><p>Connection failed: ' + 
                        (response && response.message ? response.message : 'Invalid response from server') + '</p></div>');
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = 'Connection failed';
                
                if (status === 'timeout') {
                    errorMessage = 'Connection timed out. The ITTO service took too long to respond.';
                } else if (status === 'parsererror') {
                    errorMessage = 'Invalid response from server. Please check the API URL.';
                } else if (xhr.status === 0) {
                    errorMessage = 'Could not connect to the ITTO service. Please check the API URL and your internet connection.';
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else {
                    errorMessage += ': ' + error;
                }
                
                resultDiv.html('<div class="notice notice-error"><p>' + errorMessage + '</p></div>');
            },
            complete: function() {
                button.prop('disabled', false);
            }
        });
    });
}); 