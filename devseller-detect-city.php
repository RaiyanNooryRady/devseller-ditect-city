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

    // Your paid IP-API API key (replace with your actual API key)
    $access_key = 'C0tVbJx7gkIY0Jo';  // Replace with your API key from IP-API

    // Use the IP-API API to get location data
    $geo_url = "http://api.ip-api.com/{$user_ip}?access_key={$access_key}";  // API request with the API key
    
    
    //test
    $user_ip= "192.168.1.1";
    $geo_url = "http://api.ip-api.com/{$user_ip}?access_key={$access_key}";


    // Fetch the location data
    $response = wp_remote_get($geo_url);

    if (is_wp_error($response)) {
        return __('City not found', 'devseller-detect-city');  // Return a default message if the request fails
    }

    // Decode the response body
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // Check if city data is available
    if (!empty($data['city'])) {
        return $data['city'];  // Return the city name
    }

    return __('City not found', 'devseller-detect-city');  // Return a default message if city is not found
}
