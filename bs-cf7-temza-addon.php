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
 if ( ! function_exists( 'wpcf7' ) ) {
    return;
 }

 class Bs_Cf7_Temza_Addon {
    public $plugin_slug;
    public $version;

    public function __construct() {
        $this->plugin_slug = plugin_basename( __DIR__ );
        $this->version = '1.0.0';

        // UTM
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_utm_handler' ) );
        add_filter( 'wpcf7_posted_data', array( $this, 'add_utm_to_cf7_submission' ) );
        add_action( 'wpcf7_before_send_mail', array( $this, 'add_utm_to_email' ) );

        // Webhooks

        // Uploadable files

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
            $posted_data['utm_source']   = $exploded_utm_data[0];

        if ( ! empty( $exploded_utm_data[1] ) && $exploded_utm_data[1] != 'false' )
            $posted_data['utm_medium']   = $exploded_utm_data[1];

        if ( ! empty( $exploded_utm_data[2] ) && $exploded_utm_data[2] != 'false' )
            $posted_data['utm_term']     = $exploded_utm_data[2];

        if ( ! empty( $exploded_utm_data[3] ) && $exploded_utm_data[3] != 'false' )
            $posted_data['utm_content']  = $exploded_utm_data[3];

        if ( ! empty( $exploded_utm_data[4] ) && $exploded_utm_data[4] != 'false' )
            $posted_data['utm_campaign'] = $exploded_utm_data[4];

        if ( ! empty( $landing_page ) )
            $posted_data['landing_page'] = $landing_page;

        if ( ! empty( $referer ) )
            $posted_data['referer'] = $referer;

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

    public function logit( $data, $description = '[INFO]' ) {
        $filename = WP_CONTENT_DIR . '/bs-cf7-temza-addon_log.log';

        $text = "===[ Temza addon for Contact Form 7 v. " . $this->version . " ]===\n";
        $text .= "===[ " . $description . " ]===\n";
        $text .= "===[ " . date( 'M d Y, G:i:s', time() ) . " ]===\n";
        $text .= print_r( $data, true ) . "\n";
        $file = fopen( $filename, 'a' );
        fwrite( $file, $text );
        fclose( $file );
    }
 }