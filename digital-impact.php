<?php
/**
 * Plugin Name: ArgYou Digital Impact API
 * Description: Edit your own online texts with what users already search on 14’103 channels. The colours indicate to you what is most used and understood.
 * Version: 1.0.3
 * Author: Argyou.com
 * Author URI: https://find.argyou.com
 * License: GPL2
 */

// Function to get the path of the current PHP file
function digital_impact_get_php_path() {
    return plugin_dir_path( __FILE__ );
}

// Enqueue the custom JavaScript file
add_action('admin_enqueue_scripts', 'digital_impact_enqueue_scripts');
function digital_impact_enqueue_scripts($hook) {
    if ($hook === 'post.php') {
        // Enqueue the JavaScript file
        wp_enqueue_script('digital-impact-custom-script', plugins_url('js/start_button.js', __FILE__), array('jquery'), '1.0', true);

        // Enqueue the CSS file
        wp_enqueue_style('digital-impact-custom-style', plugins_url('css/digital-impact.css', __FILE__), array(), '1.0');

        // Localize the script to make the AJAX URL available
        wp_localize_script('digital-impact-custom-script', 'digital_impact_ajax_object', array(
            'ajaxurl' => admin_url('admin-ajax.php')
        ));
    }
}

// Add meta box to display the results
add_action('add_meta_boxes', 'digital_impact_add_meta_box');
function digital_impact_add_meta_box() {
    add_meta_box('digital_impact_meta_box', 'Keywords which users search in your text above', 'digital_impact_meta_box_callback', 'post', 'side');
}

// Callback function to display the meta box and the results inside
function digital_impact_meta_box_callback($post) {
    // Display the button to trigger the keyword extraction
    echo '<button id="digital_impact_extract_btn">Measure Now</button>';

    // Display the container for the results
    echo '<div id="digital_impact_results_container"></div>';
}

// AJAX callback function for keyword extraction
add_action('wp_ajax_digital_impact_start_measure', 'digital_impact_start_measure');
function digital_impact_start_measure() {

    $post_id = $_POST['post_id'];

    // Get the latest post object
    $post = get_post($post_id);

    // Call the digital_impact_keyword_extraction function
    digital_impact_keyword_extraction($post->post_content);

    // Always exit to avoid further processing
    wp_die();
}

function sendRequestToFlaskServer($url, $file_path, $post_data) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);

    $post_data['file'] = curl_file_create($file_path);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($curl);
    curl_close($curl);

    echo $response;
}


// Run the keyword extraction Python script and get the output words
function digital_impact_keyword_extraction( $content ) {
    // Remove HTML tags and <br> tags from the content
    $clean_content = strip_tags($content);
    $clean_content = str_replace(['<br>', '<strong>', '&nbsp;','“', '”'], ' ', $clean_content);

    // Combine all the content into one paragraph
    $clean_content = preg_replace("/[\r\n]+/", " ", $clean_content);

    // Create a temporary file to store the post content
    $tmp_file = tempnam(digital_impact_get_php_path(), 'post_content_');
    file_put_contents($tmp_file, $clean_content);

    if (file_exists($tmp_file)) {
       
    } else {
       echo 'Failed to create temporary file.';
    }

    // Flask server URL: app.py
    $url = 'http://46.140.198.153:8080/run-test';  
    $url_2 = 'http://46.140.198.153:8080/get-ranking-data';

    // Set the path of the file to be uploaded
    $file_path = $tmp_file;

    // Call the function without any additional POST data
    sendRequestToFlaskServer($url, $file_path, array());

    // Get data back from the Flask server
    $response_from_Flask = file_get_contents($url_2);
    // Convert the JSON response to an associative array
    $kw_from_Flask = json_decode($response_from_Flask, true);

    // Show final results
    printKeywordsWithColors($kw_from_Flask, $content);

    // Remove the temporary file
    unlink($tmp_file);

}

function printKeywordsWithColors($data, $text, $fontSize = '18px') {
    if (!is_array($data)) {
        echo 'Invalid data format: ' . print_r($data, true);
        return;
    }

    $text = str_replace("'", "", $text);

    // Loop through the data and replace keywords with colored spans
    foreach ($data as $row) {
        if (!is_array($row) || count($row) < 2) {
            echo 'Invalid row format: ' . print_r($row, true);
            continue;
        }

        $keyword = $row[0];
        $value = $row[1];

        // Assign color based on value
        $color = '';
        if ($value == 1) {
            $color = 'green';
        } elseif ($value == -1) {
            $color = 'red';
        } elseif ($value == 0) {
            $color = 'orange';
        }

        // Create the colored span with the keyword
        $coloredKeyword = '<span style="color: ' . $color. '; font-size: ' . $fontSize . ';">$0</span>';


        // Create the regex pattern to match the whole word
        $pattern = '/\b' . preg_quote($keyword, '/') . '\b/iu';
                                                                                                                                               
        // Replace the keyword in the text with the colored span
        $text = preg_replace($pattern, $coloredKeyword, $text);
        
    }
        // Output the modified text with colored keywords
        echo $text;

}


// Add uninstall hook
register_uninstall_hook( __FILE__, 'digital_impact_uninstall' );
function digital_impact_uninstall() {
    // TODO: Implement uninstallation cleanup
    error_log( 'Digital Impact plugin has been uninstalled.' );
}
