jQuery(document).ready(function($) {
    // Create the loading spinner element
    var loadingSpinner = $('<div id="loading-spinner"><br><div class="loading-text">The current digital impact measurement is taken in Switzerland. Measure of the API can last a few minutes. Please continue your writing...</div><br><div class="loader"></div></div>');

    // Button click event handler
    $('#digital_impact_extract_btn').click(function() {
        // Get the post ID
        var post_id = $('#post_ID').val();

        // Get the latest post content
        var post_content = $('#content').val();

        // Make an AJAX request to send the latest post ID for analysis
        $.ajax({
            url: digital_impact_ajax_object.ajaxurl,
            type: 'POST',
            dataType: 'html',
            data: {
                action: 'digital_impact_start_measure',
                post_id: post_id,
                post_content: post_content
            },
            beforeSend: function() {
                // Display the loading spinner
                $('#digital_impact_results_container').html(loadingSpinner);

                // Show the loading spinner
                loadingSpinner.show();
            },
            success: function(response) {
                // Hide the loading spinner
                loadingSpinner.hide();

                // Display the keyword results in the container
                $('#digital_impact_results_container').html(response);
            },
            error: function(xhr, status, error) {
                // Hide the loading spinner
                loadingSpinner.hide();
                
                // Handle the error
                console.log(xhr.responseText);
            }
        });
    });
});
