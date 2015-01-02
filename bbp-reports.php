<?php
/*
Plugin Name: bbPress Reports
Plugin URI: http://example.com/
Description: Description
Version: 0.1
Author: Your Name
Author URI: http://example.com/
License: GPL2
*/

/**
 * Copyright (c) YEAR Your Name (email: Email). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * WeDevs_bbPress_Reports class
 *
 * @class WeDevs_bbPress_Reports The class that holds the entire WeDevs_bbPress_Reports plugin
 */
class WeDevs_bbPress_Reports {

    /**
     * Constructor for the WeDevs_bbPress_Reports class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses register_activation_hook()
     * @uses register_deactivation_hook()
     * @uses is_admin()
     * @uses add_action()
     */
    public function __construct() {
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        // Localize our plugin
        add_action( 'init', array( $this, 'localization_setup' ) );
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );

        // Loads frontend scripts and styles
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    /**
     * Initializes the WeDevs_bbPress_Reports() class
     *
     * Checks for an existing WeDevs_bbPress_Reports() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new WeDevs_bbPress_Reports();
        }

        return $instance;
    }

    /**
     * Placeholder for activation function
     *
     * Nothing being called here yet.
     */
    public function activate() {

    }

    /**
     * Placeholder for deactivation function
     *
     * Nothing being called here yet.
     */
    public function deactivate() {

    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup() {
        load_plugin_textdomain( 'bbp-reports', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    public function admin_menu() {
        add_submenu_page( 'edit.php?post_type=forum', __( 'bbPress Reports', 'bbp-report' ), __( 'Reports', 'bbp-reports' ), 'manage_options', 'bbp-reports', array( $this, 'report_page' ) );
    }

    public function report_page() {
        ?>
        <div class="wrap bbp-reports">

            <?php
            $active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'conversation';
            $tabs = apply_filters( 'bbp_reports_tabs', array(
                'conversation' => array(
                    'title'    => __( 'Conversation', 'bbp-reports' ),
                    'callback' => 'bbp_report_conversation'
                ),
                'productivity' => array(
                    'title'    => __( 'Productivity', 'bbp-reports' ),
                    'callback' => 'bbp_report_productivity'
                ),
                'team' => array(
                    'title'    => __( 'Team', 'bbp-reports' ),
                    'callback' => 'bbp_report_team'
                ),
            ) );
            ?>

            <h2 class="nav-tab-wrapper">
                <?php foreach ($tabs as $key => $tab) {
                    $active_class = ( $key == $active_tab ) ? ' nav-tab-active' : '';
                    ?>
                    <a href="<?php echo add_query_arg( array( 'tab' => $key ), admin_url( 'edit.php?post_type=forum&page=bbp-reports' ) ); ?>" class="nav-tab<?php echo $active_class; ?>"><?php echo $tab['title'] ?></a>
                <?php } ?>
            </h2>

            <?php
            // call the tab callback function
            if ( array_key_exists( $active_tab, $tabs ) && is_callable( $tabs[$active_tab]['callback'] ) ) {
                call_user_func( $tabs[$active_tab]['callback'] );
            }
            ?>
        </div>
        <?php
    }

    /**
     * Enqueue admin scripts
     *
     * Allows plugin assets to be loaded.
     *
     * @uses wp_enqueue_script()
     * @uses wp_localize_script()
     * @uses wp_enqueue_style
     */
    public function enqueue_scripts() {
        global $wp_scripts;

        $ui       = $wp_scripts->query('jquery-ui-core');
        $protocol = is_ssl() ? 'https' : 'http';
        $url      = "$protocol://ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css";

        wp_enqueue_style( 'bbp-report-styles', plugins_url( 'assets/css/bbp-reports.css', __FILE__ ), false, date( 'Ymd' ) );
        wp_enqueue_style('jquery-ui-smoothness', $url );

        /**
         * All scripts goes here
         */
        wp_enqueue_script( 'bbp-report-scripts', plugins_url( 'assets/js/script.js', __FILE__ ), array( 'jquery', 'jquery-ui-datepicker' ), false, true );


        /**
         * Example for setting up text strings from Javascript files for localization
         *
         * Uncomment line below and replace with proper localization variables.
         */
        // $translation_array = array( 'some_string' => __( 'Some string to translate', 'bbp-report' ), 'a_value' => '10' );
        // wp_localize_script( 'base-plugin-scripts', 'bbp-report', $translation_array ) );

    }

} // WeDevs_bbPress_Reports

$reports = WeDevs_bbPress_Reports::init();

/*
import topics
=================================
INSERT INTO home_bbp_reports( topic_id, topic_created, created_by)
SELECT ID, post_date, post_author
FROM home_posts WHERE post_type = 'topic' AND post_status != 'trash'
*/

/*
Update response time
====================================
UPDATE home_bbp_reports AS r
INNER JOIN (
    SELECT post_parent, ID, post_author, post_date
    FROM home_posts
    WHERE post_type = 'reply' AND post_author IN (3754,5053,39,3192,4347,3177,5052,39,4593,98)
    GROUP BY post_parent
    ORDER BY post_date ASC
) AS reply ON r.topic_id = reply.post_parent
SET r.first_response = reply.post_date,
r.response_by = reply.post_author
*/

/*
Update last active time
======================================
UPDATE home_bbp_reports AS r
INNER JOIN (
    SELECT post_id, meta_value
    FROM home_postmeta
    WHERE meta_key = '_bbp_last_active_time'
) AS m ON m.post_id = r.topic_id
SET r.last_active = m.meta_value
*/

/*
Update Total reply
=======================================
UPDATE home_bbp_reports AS r
INNER JOIN (
    SELECT post_id, meta_value
    FROM home_postmeta
    WHERE meta_key = '_bbp_voice_count'
) AS m ON m.post_id = r.topic_id
SET r.total_reply = m.meta_value
 */

/*
Topic resolve status
=================================
UPDATE home_bbp_reports AS r
INNER JOIN (
    SELECT post_id, meta_value
    FROM home_postmeta
    WHERE meta_key = '_bbps_topic_status'
) AS m ON m.post_id = r.topic_id
SET r.topic_status = m.meta_value
 */

add_action( 'init', function() {
    global $wpdb;

    if ( ! isset( $_GET['bbp_report_test'] ) ) {
        return;
    }

    // get mdoerators
    // $moderators = get_users( array( 'role' => 'bbp_moderator' ) );
    // $moderators = array_unique( wp_list_pluck( $moderators, 'ID' ) );
    // var_dump($moderators);
    // echo "IN (" . implode( ',', $moderators) . ") <br>";

    // exit;
    // get posts by moderators
//     $query = "SELECT post_parent as topic_id, ID as reply_id, post_author, post_date
// FROM $wpdb->posts
// WHERE post_type = 'reply' AND
//     post_author IN (" . implode( ',', $moderators) . ")

// GROUP BY post_parent
// ORDER BY post_date ASC";

//     echo '<pre>' . $query . '</pre>';
//     $replies = $wpdb->get_results( $query );
//     var_dump( $replies );

    $start_date = '2014-12-01';
    $end_date   = '2014-12-31';
    exit;
} );



function bbp_report_conversation() {
    global $wpdb;

    $timestamp  = current_time( 'timestamp' );
    $cur_year   = date( 'Y', $timestamp );
    $cur_month  = date( 'm', $timestamp );
    $no_of_days = cal_days_in_month( CAL_GREGORIAN, $cur_month, $cur_year );
    $start_date = isset( $_GET['bbp_report_start'] ) ? sanitize_text_field( $_GET['bbp_report_start'] ) : date( 'Y-m-01', $timestamp );
    $end_date   = isset( $_GET['bbp_report_end'] ) ? sanitize_text_field( $_GET['bbp_report_end'] ) : date( 'Y-m-' . $no_of_days, $timestamp );
    $table_name = $wpdb->prefix . 'bbp_reports';

    // echo '<pre>' . $start_date . ' <-> ' . $end_date . '</pre>';
    $topic_created = $wpdb->get_results( "SELECT id, topic_created, created_by, last_active FROM $table_name WHERE topic_created >= '$start_date' AND topic_created <= '$end_date' ORDER BY topic_id ASC");
    $active_conversation = $wpdb->get_results( "SELECT id, topic_created, created_by, last_active FROM $table_name WHERE ( last_active >= '$start_date' AND last_active <= '$end_date' ) OR ( topic_created >= '$start_date' AND topic_created <= '$end_date' ) ORDER BY last_active ASC");

    $topic_replies_query = "SELECT post_date as date, post_author
        FROM $wpdb->posts
        WHERE
            post_type IN ('topic', 'reply') AND
            post_date >= '$start_date' AND post_date <= '$end_date' AND
            post_status in ( 'publish', 'closed' )
        -- GROUP BY day
        ORDER BY post_date DESC";
    $topic_replies = $wpdb->get_results( $topic_replies_query );
    // var_dump($topic_replies);

    $user_count = array_unique( wp_list_pluck( $topic_replies, 'post_author' ) );
    $fills = array(
        '12-3am' => 0,
        '3-6am'  => 0,
        '6-9am'  => 0,
        '9-12pm' => 0,
        '12-3pm' => 0,
        '3-6pm'  => 0,
        '6-9pm'  => 0,
        '9-12am' => 0
    );
    $replies_formatted = array(
        'Sunday'    => $fills,
        'Monday'    => $fills,
        'Tuesday'   => $fills,
        'Wednesday' => $fills,
        'Thursday'  => $fills,
        'Friday'    => $fills,
        'Saturday'  => $fills
    );
    $day_count = array(
        'Sunday'    => 0,
        'Monday'    => 0,
        'Tuesday'   => 0,
        'Wednesday' => 0,
        'Thursday'  => 0,
        'Friday'    => 0,
        'Saturday'  => 0
    );

    foreach ($topic_replies as $reply) {
        $timestamp = strtotime( $reply->date );
        $key       = date( 'l', $timestamp );
        $hour      = date('G', $timestamp);

        $day_count[$key] += 1;

        if ( $hour >= 0 && $hour <= 3 ) {
            $replies_formatted[ $key ]['12-3am'] += 1;
            $fills['12-3am'] += 1;
        } elseif ( $hour > 3 && $hour <= 6 ) {
            $replies_formatted[ $key ]['3-6am'] += 1;
            $fills['3-6am'] += 1;
        } elseif ( $hour > 6 && $hour <= 9 ) {
            $replies_formatted[ $key ]['6-9am'] += 1;
            $fills['6-9am'] += 1;
        } elseif ( $hour > 9 && $hour <= 12 ) {
            $replies_formatted[ $key ]['9-12pm'] += 1;
            $fills['9-12pm'] += 1;
        } elseif ( $hour > 12 && $hour <= 15 ) {
            $replies_formatted[ $key ]['12-3pm'] += 1;
            $fills['12-3pm'] += 1;
        } elseif ( $hour > 15 && $hour <= 18 ) {
            $replies_formatted[ $key ]['3-6pm'] += 1;
            $fills['3-6pm'] += 1;
        } elseif ( $hour > 18 && $hour <= 21 ) {
            $replies_formatted[ $key ]['6-9pm'] += 1;
            $fills['6-9pm'] += 1;
        } elseif ( $hour > 21 && $hour <= 24 ) {
            $replies_formatted[ $key ]['9-12am'] += 1;
            $fills['9-12am'] += 1;
        }
    }

    // var_dump($replies_formatted);
    arsort( $day_count );
    arsort( $fills );
    $day_count_flip = array_flip( $day_count );
    $fills_flip = array_flip( $fills );
    // var_dump($day_count_flip);
    // var_dump($day_count);
    // var_dump( $fills );
    // var_dump( $fills_flip );

    // $busiest_hour_query = "SELECT hour(topic_created) AS hr, count(*) as count, topic_created
    //     FROM $table_name
    //     WHERE topic_created >= '$start_date' AND topic_created <= '$end_date'
    //     GROUP BY hr
    //     ORDER BY count(*) DESC";
    // $busiest_hour = $wpdb->get_results( $busiest_hour_query );
    // $busiest_hour = array_map( 'intval', wp_list_pluck( $busiest_hour, 'hr' ) );
    // sort( $busiest_hour );

    // var_dump( $topic_created, $total_conversation );
    // var_dump($topic_created);
    // var_dump($active_conversation);

    // $topic_created_ids       = wp_list_pluck( $topic_created, 'id' );
    // $active_conversation_ids = wp_list_pluck( $active_conversation, 'id' );
    // $total_conversation_ids  = array_unique( array_merge( $topic_created_ids, $active_conversation_ids ) );

    // var_dump($busiest_day);
    // var_dump($busiest_hour);
    ?>

    <div class="chart-container clearfix">

        <?php bbp_reports_filter_area( $start_date, $end_date, 'conversation' ); ?>

        <div class="chart-sidebar">
            <ul class="chart-legend clearfix">
                <li>
                    <strong><?php echo count( $active_conversation ); ?></strong>
                    <?php _e( 'Active Topics', 'bbp-reports' ); ?>
                </li>
                <li>
                    <strong><?php echo count( $topic_created ); ?></strong>
                    <?php _e( 'Topics Created', 'bbp-reports' ); ?>
                </li>
                <li>
                    <strong><?php echo count( $user_count ); ?></strong>
                    <?php _e( 'User Participation', 'bbp-reports' ); ?>
                </li>
                <li>
                    <strong><?php echo ceil( count( $active_conversation ) / $no_of_days ); ?></strong>
                    <?php _e( 'Avg. Topics/Day', 'bbp-reports' ); ?>
                </li>
                <li>
                    <strong><?php echo reset( $day_count_flip ); ?></strong>
                    <?php _e( 'Busiest Day', 'bbp-reports' ); ?>
                </li>
                <li>
                    <strong><?php echo reset( $fills_flip ); ?></strong>
                    <?php _e( 'Busiest Time', 'bbp-reports' ); ?>
                </li>
            </ul>
        </div><!-- .chart-sidebar -->
        <div class="chart-main">

            <div class="postbox leads-actions">
                <h3 class="hndle"><?php _e( 'Busiest Time of day', 'bbp-reports' ); ?> <span><?php _e( 'topic and replies', 'bbp-reports' ); ?></span></h3>
                <div class="inside">

                    <table class="widefat">
                        <thead>
                            <tr>
                                <th>&nbsp;</th>
                                <th><?php _e( '12-3am', 'bbp-reports' ); ?></th>
                                <th><?php _e( '3-6am', 'bbp-reports' ); ?></th>
                                <th><?php _e( '6-9am', 'bbp-reports' ); ?></th>
                                <th><?php _e( '9-12pm', 'bbp-reports' ); ?></th>
                                <th><?php _e( '12-3pm', 'bbp-reports' ); ?></th>
                                <th><?php _e( '3-6pm', 'bbp-reports' ); ?></th>
                                <th><?php _e( '6-9pm', 'bbp-reports' ); ?></th>
                                <th><?php _e( '9-12am', 'bbp-reports' ); ?></th>
                                <th><?php _e( 'Total', 'bbp-reports' ); ?></th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php $row = 0; ?>
                            <?php foreach ($replies_formatted as $day => $values) { ?>
                                <tr<?php echo ( $row % 2 ) == 0 ? ' class="alternate"' : ''; ?>>
                                    <th><?php echo substr( $day, 0, 3 ); ?></th>

                                    <?php foreach ($values as $range => $count) { ?>
                                        <td><?php echo $count; ?></td>
                                    <?php } ?>

                                    <td><?php echo $day_count[$day]; ?></td>
                                </tr>
                                <?php $row += 1; ?>
                            <?php } ?>

                            <tr>
                                <th>&nbsp;</th>
                                <td><?php echo $fills['12-3am']; ?></td>
                                <td><?php echo $fills['3-6am']; ?></td>
                                <td><?php echo $fills['6-9am']; ?></td>
                                <td><?php echo $fills['9-12pm']; ?></td>
                                <td><?php echo $fills['12-3pm']; ?></td>
                                <td><?php echo $fills['3-6pm']; ?></td>
                                <td><?php echo $fills['6-9pm']; ?></td>
                                <td><?php echo $fills['9-12am']; ?></td>
                                <td>&nbsp;</td>
                            </tr>
                        </tbody>

                    </table>
                </div>
            </div><!-- .postbox -->

        </div><!-- .chart-main -->
    </div><!-- .chart-container -->
    <?php
}

function bbp_report_team() {
    global $wpdb;

    $timestamp  = current_time( 'timestamp' );
    $cur_year   = date( 'Y', $timestamp );
    $cur_month  = date( 'm', $timestamp );
    $no_of_days = cal_days_in_month( CAL_GREGORIAN, $cur_month, $cur_year );
    $start_date = isset( $_GET['bbp_report_start'] ) ? sanitize_text_field( $_GET['bbp_report_start'] ) : date( 'Y-m-01', $timestamp );
    $end_date   = isset( $_GET['bbp_report_end'] ) ? sanitize_text_field( $_GET['bbp_report_end'] ) : date( 'Y-m-' . $no_of_days, $timestamp );
    $table_name = $wpdb->prefix . 'bbp_reports';

    // $start_date = '2014-11-01';
    // $end_date = '2014-11-30';

    // get mdoerators
    $administrators = get_users( array( 'role' => 'administrator' ) );
    $keymaster      = get_users( array( 'role' => 'bbp_keymaster' ) );
    $moderators     = get_users( array( 'role' => 'bbp_moderator' ) );

    $all_users = array_merge( $administrators, $keymaster, $moderators );
    $all_users = array_unique( wp_list_pluck( $all_users, 'ID' ) );
    // var_dump( $all_users );

    $query = "SELECT COUNT(*) as count, u.ID, p.post_author, u.display_name
        FROM $wpdb->posts AS p
        INNER JOIN $wpdb->users AS u ON u.ID = p.post_author
        WHERE post_author IN (" . implode( ',', $all_users ) . ") AND post_type = 'reply' AND
        ( post_date >= '$start_date' AND post_date <= '$end_date' ) AND post_status in ('publish', 'closed' )
        GROUP BY p.post_author
        ORDER BY count DESC";

    $users = $wpdb->get_results( $query );
    // var_dump( $active_conversation );
    ?>
    <?php bbp_reports_filter_area( $start_date, $end_date, 'team' ); ?>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php _e( 'Team', 'bbp-reports' ); ?></th>
                <th><?php _e( 'Replies', 'bbp-reports' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ( $users ) {
                foreach ($users as $key => $user) {
                    ?>
                    <tr<?php echo ( $key % 2 ) == 0 ? ' class="alternate"' : ''; ?>>
                        <td>
                            <a href="<?php echo bbp_user_replies_created_url( $user->ID ); ?>"><?php echo $user->display_name; ?></a>
                        </td>
                        <td>
                            <?php echo $user->count; ?>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="2">
                        <?php _e( 'No replies found.', 'bbp-reports' ); ?>
                    </td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>

    <?php
}


function bbp_reports_filter_area( $start_date, $end_date, $tab ) {
    ?>
    <div class="filter-area clearfix">
        <form action="<?php echo admin_url( 'edit.php' ); ?>" method="get">
            <input type="hidden" name="post_type" value="forum">
            <input type="hidden" name="page" value="bbp-reports">
            <input type="hidden" name="tab" value="<?php echo $tab; ?>">

            <input type="text" name="bbp_report_start" id="bbp-report-start" class="bbp-datepicker" value="<?php echo esc_attr( $start_date ); ?>" readonly>
            <input type="text" name="bbp_report_end" id="bbp-report-end" class="bbp-datepicker" value="<?php echo esc_attr( $end_date ); ?>" readonly>

            <?php submit_button( __( 'Filter', 'bbp-reports' ), 'secondary', 'filter', '' ); ?>
        </form>
    </div>
    <?php
}