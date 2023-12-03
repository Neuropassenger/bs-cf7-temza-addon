<?php
/*
 * Plugin Name:       Temza addon for Contact Form 7
 * Plugin URI:        https://github.com/Neuropassenger/bs-cf7-temza-addon
 * Description:       Implements adding UTM tags to form data, adds the ability to send data via webhook, and improves security for uploaded files.
 * Version:           1.0.0
 * Requires at least: 6.3.2
 * Requires PHP:      7.4.33
 * Author:            Oleg Sokolov
 * Author URI:        https://github.com/Neuropassenger/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 */

 // Terminate if Contact Form 7 is turned off
require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( ! is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) )
    return;

 class Bs_Cf7_Temza_Addon {
    public $plugin_slug;
    public $version;

    private $available_utm_tags;

    public function __construct() {
        $this->plugin_slug = plugin_basename( __DIR__ );
        $this->version = '1.0.0';

        $this->available_utm_tags = array( 
            'bs_utm_source', 
            'bs_utm_medium', 
            'bs_utm_term', 
            'bs_utm_content', 
            'bs_utm_campaign', 
            'bs_landing_page', 
            'bs_referer' 
        );

        // UTM
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_utm_handler' ) );
        add_filter( 'wpcf7_posted_data', array( $this, 'add_utm_to_cf7_submission' ) );
        add_action( 'wpcf7_before_send_mail', array( $this, 'add_utm_to_email' ) );

        // Webhooks
        add_action( 'wpcf7_mail_sent', array( $this, 'send_data_to_webhook' ) );

        // Uploadable files
        add_filter( 'dnd_cf7_auto_delete_files', 'dnd_set_auto_delete_files_seconds_interval' );
        add_filter( 'wpcf7_upload_file_name', 'dnd_modify_uploaded_file_name' );

        // Updates
    }

    public function enqueue_utm_handler() {
        wp_enqueue_script( 'bs-cf7-temza-addon-utm-handler', plugin_dir_url(__FILE__) . 'js/utm_handler.js',  array(), $this->version, true );
    }

    /**
     * Add UTM data to the submitted form data 
     */
    public function add_utm_to_cf7_submission( $posted_data ) {
        $utm_data = $_COOKIE['bs_utm_data'];
        $exploded_utm_data = explode( '|', $utm_data );
        $landing_page = $_COOKIE['bs_landing_page'];
        $referer = $_COOKIE['bs_referer'];

        // Subbmisson instance from CF7
        $submission = WPCF7_Submission::get_instance();

        // Make sure we have the data
        if ( ! $posted_data ) {
            $posted_data = $submission->get_posted_data();
        }

        if ( ! empty( $exploded_utm_data[0] ) && $exploded_utm_data[0] != 'false' )
            $posted_data['bs_utm_source']   = $exploded_utm_data[0];

        if ( ! empty( $exploded_utm_data[1] ) && $exploded_utm_data[1] != 'false' )
            $posted_data['bs_utm_medium']   = $exploded_utm_data[1];

        if ( ! empty( $exploded_utm_data[2] ) && $exploded_utm_data[2] != 'false' )
            $posted_data['bs_utm_term']     = $exploded_utm_data[2];

        if ( ! empty( $exploded_utm_data[3] ) && $exploded_utm_data[3] != 'false' )
            $posted_data['bs_utm_content']  = $exploded_utm_data[3];

        if ( ! empty( $exploded_utm_data[4] ) && $exploded_utm_data[4] != 'false' )
            $posted_data['bs_utm_campaign'] = $exploded_utm_data[4];

        if ( ! empty( $landing_page ) )
            $posted_data['bs_landing_page'] = $landing_page;

        if ( ! empty( $referer ) )
            $posted_data['bs_referer'] = $referer;

        return $posted_data;
    }

    /**
     * Add UTM data to an e-mail message
     */
    public function add_utm_to_email( $contact_form ) {
        $submission = WPCF7_Submission::get_instance();
        if ( $submission ) {
            $utm_data = $_COOKIE['bs_utm_data'];
            $exploded_utm_data = explode( '|', $utm_data );
            $landing_page = $_COOKIE['bs_landing_page'];
            $referer = $_COOKIE['bs_referer'];

            $email_utm = '';
            if( ! empty( $exploded_utm_data[0] ) ) {
                $email_utm .= '<style type="text/css">tr:nth-child(even) { background-color: #eff0f1; }</style><table cellpadding="10" border="1" style="border-collapse:collapse; width:50%;">';

                $email_utm .= '<tr><td><strong>UTM Parameter</strong></td><td><strong>Value</strong></td></tr>';

                if ( $exploded_utm_data[0] != 'false' )
                    $email_utm .= '<tr><td>utm_source</td><td>'. $exploded_utm_data[0] .'</td></tr>';

                if ( ! empty( $exploded_utm_data[1] ) && $exploded_utm_data[1] != 'false' )
                    $email_utm .= '<tr><td>utm_medium</td><td>'. $exploded_utm_data[1] .'</td></tr>';

                if ( ! empty( $exploded_utm_data[2] ) && $exploded_utm_data[2] != 'false' )
                    $email_utm .= '<tr><td>utm_term</td><td>'. $exploded_utm_data[2] .'</td></tr>';

                if ( ! empty( $exploded_utm_data[3] ) && $exploded_utm_data[3] != 'false' )
                    $email_utm .= '<tr><td>utm_content</td><td>'. $exploded_utm_data[3] .'</td></tr>';

                if ( ! empty( $exploded_utm_data[4] ) && $exploded_utm_data[4] != 'false' )
                    $email_utm .= '<tr><td>utm_campaign</td><td>'. $exploded_utm_data[4] .'</td></tr>';

                if ( isset( $landing_page ) )
                    $email_utm .= '<tr><td>Landing page URL</td><td>' . $landing_page .'</td></tr>';

                if ( isset( $referer ) )
                    $email_utm .= '<tr style=""><td>Page Referrer</td><td>' . $referer .'</td></tr>';

                $email_utm .='</table>';
            }

            $mail = $contact_form->prop( 'mail' );
            $mail['body'] .= $email_utm;
            $mail['use_html'] = 1;
            $contact_form->set_properties( array( 'mail' => $mail ) );
        }
    }

    public function send_data_to_webhook( $contact_form ) {
        $webhook_url = $contact_form->additional_setting( 'webhook_url' );
        // Webhook is not set
        if ( empty( $webhook_url ) )
            return;

        $webhook_url = $webhook_url[0];

        $submission = WPCF7_Submission::get_instance();
        $posted_data = $submission->get_posted_data();
        $form_tags = $contact_form->scan_form_tags();
        $form_tag_names = array();
        foreach ( $form_tags as $tag )
            if ( ! empty( $tag->name ) )
                $form_tag_names[] = $tag->name;
        $available_tag_names = array_merge( $form_tag_names, $this->available_utm_tags );
        $filtered_posted_data = array_filter(
            $posted_data,
            function ( $key ) use ( $available_tag_names ) {
                return in_array( $key, $available_tag_names );
            },
            ARRAY_FILTER_USE_KEY
        ); 

        $curl_session = curl_init( $webhook_url );
        curl_setopt( $curl_session, CURLOPT_POST, true );
        curl_setopt( $curl_session, CURLOPT_POSTFIELDS, http_build_query( $filtered_posted_data ) );
        curl_setopt( $curl_session, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $curl_session, CURLOPT_PORT, 443 );

        $verbose = fopen('php://temp', 'w+');
        curl_setopt($curl_session, CURLOPT_VERBOSE, true);
        curl_setopt($curl_session, CURLOPT_STDERR, $verbose);
        $response = curl_exec( $curl_session );
        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);
        
        $this->logit( array( 
            'webhook_url' => $webhook_url,
            'posted_data' => $posted_data,
            'filtered_posted_data' => $filtered_posted_data,
            'response' => $response,
            'verbose_log' => $verboseLog
        ), '[INFO]: send_data_to_webhook' );

        curl_close( $curl_session );
    }

    /**
     * Changes the storage time of files uploaded by users
     */
    public function set_dnd_auto_delete_files_seconds_interval( $seconds ) {
        return MONTH_IN_SECONDS;
    }

    /**
     * Changes the name of files uploaded by users for security purposes
     */
    public function dnd_modify_uploaded_file_name( $original_file_name ) {
        $salt = bin2hex( random_bytes( 16 ) ); // Salt generation
        $data = $original_file_name . time();
        $hash_with_salt = hash( 'sha256', $data . $salt ); // Data concatenation and salt before hashing

        // Splitting a file name into a filename and an extension
        $file_name = pathinfo( $original_file_name, PATHINFO_FILENAME );
        $extension = pathinfo( $original_file_name, PATHINFO_EXTENSION );

        // Shorten the file name to 30 characters if it is too long
        $file_name = ( strlen( $file_name ) > 30 ) ? substr( $file_name, 0, 30 ) : $file_name;

        return time() . '_' . $hash_with_salt . '_' . $file_name . '.' . $extension;
    }

    
    /* Auxiliary things */
    public function logit( $data, $description = '[INFO]' ) {
        $filename = WP_CONTENT_DIR . '/bs-cf7-temza-addon.log';

        $text = "===[ Temza addon for Contact Form 7 v. " . $this->version . " ]===\n";
        $text .= "===[ " . $description . " ]===\n";
        $text .= "===[ " . date( 'M d Y, G:i:s', time() ) . " ]===\n";
        $text .= print_r( $data, true ) . "\n";
        $file = fopen( $filename, 'a' );
        fwrite( $file, $text );
        fclose( $file );
    }
 }

 new Bs_Cf7_Temza_Addon();