<?php
/**
 * Plugin Name: DevSeller Detect City
 * Description: Detect user IP, get the city, and display it in the [Stadtname] shortcode.
 * Version: 1.0
 * Author: Devseller, Raiyan Noory Rady
 * Text Domain: devseller-detect-city
 */

// Hook to load the plugin
function devseller_detect_city_shortcode_plugin_init()
{
    // Register the shortcode
    add_shortcode('Stadtname', 'devseller_detect_city_display_city_based_on_ip');
}

// Action hook to initialize the plugin
add_action('init', 'devseller_detect_city_shortcode_plugin_init');

// Function to get the user's real IP address
function devseller_detect_city_get_user_ip()
{
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // The HTTP_X_FORWARDED_FOR header may contain a list of IPs; use the first one
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        // HTTP_CLIENT_IP header
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } else {
        // REMOTE_ADDR as a fallback
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

// Function to get city based on IP address
function devseller_detect_city_display_city_based_on_ip()
{
    // Get the user's IP address
    $user_ip = devseller_detect_city_get_user_ip();

    // Check if we're on localhost
    if ($user_ip === '127.0.0.1' || $user_ip === '::1' || strpos($user_ip, '192.168.') === 0) {
        // For localhost testing, you can choose one of these options:
        
        // Option 1: Use a test city (uncomment one of these)
        // return 'Berlin';
        // return 'Munich';
        // return 'Hamburg';
        
        // Option 2: Use a real public IP for testing (default)
        $user_ip = '8.8.8.8'; // This will return "Mountain View"
    }

    // Your paid IP-API API key
    $access_key = 'C0tVbJx7gkIY0Jo';

    // Use the IP-API API to get location data
    $geo_url = "https://pro.ip-api.com/csv/{$user_ip}?key={$access_key}&fields=city";

    // Fetch the location data
    $response = wp_remote_get($geo_url);

    if (is_wp_error($response)) {
        // Log the error for debugging
        error_log('IP-API Error: ' . $response->get_error_message());
        return __('City not found', 'devseller-detect-city');
    }

    // Get the response body
    $body = wp_remote_retrieve_body($response);
    
    // The API returns CSV format, so we'll split it
    $data = explode(',', $body);
    
    // Check if we got a valid response
    if (!empty($data[0]) && $data[0] !== 'fail') {
        return trim($data[0]); // Return the city name
    }

    // Log the invalid response for debugging
    error_log('IP-API Invalid Response: ' . $body);
    return __('City not found', 'devseller-detect-city');
}
