<?php
/*
Plugin Name: bbPress Reports
Plugin URI: http://wedevs.com/plugins/
Description: A simple reporting plugin for bbPress
Version: 0.1
Author: Tareq Hasan
Author URI: http://tareq.wedevs.com/
License: GPL2
*/

/**
 * Copyright (c) 2015 Tareq Hasan (email: tareq@wedevs.com). All rights reserved.
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
        $this->includes();
        $this->init_actions();
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
     * Include the required files
     *
     * @return void
     */
    public function includes() {
        require_once dirname( __FILE__ ) . '/includes/class-report.php';
    }

    /**
     * Initialize the action hooks
     *
     * @return void
     */
    public function init_actions() {
        // Localize our plugin
        add_action( 'init', array( $this, 'localization_setup' ) );

        add_action( 'admin_menu', array( $this, 'admin_menu' ) );

        // Loads frontend scripts and styles
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup() {
        load_plugin_textdomain( 'bbp-reports', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Register the admin menu
     *
     * @return void
     */
    public function admin_menu() {
        add_submenu_page( 'edit.php?post_type=forum', __( 'bbPress Reports', 'bbp-report' ), __( 'Reports', 'bbp-reports' ), 'manage_options', 'bbp-reports', array( $this, 'report_page' ) );
    }

    /**
     * Render the reports page
     *
     * @return void
     */
    public function report_page() {
        ?>
        <div class="wrap bbp-reports">

            <?php
            $active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'conversation';
            $tabs = apply_filters( 'bbp_reports_tabs', array(
                'conversation' => array(
                    'title'    => __( 'Conversation', 'bbp-reports' ),
                    'callback' => array( 'WeDevs_bbPress_Reporting', 'conversation' )
                ),
                // 'productivity' => array(
                //     'title'    => __( 'Productivity', 'bbp-reports' ),
                //     'callback' => 'bbp_report_productivity'
                // ),
                'team' => array(
                    'title'    => __( 'Team', 'bbp-reports' ),
                    'callback' => array( 'WeDevs_bbPress_Reporting', 'report_team' )
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
     * @uses wp_enqueue_style
     */
    public function enqueue_scripts() {
        global $wp_scripts;

        $suffix   = ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) ? '' : '.min';

        wp_enqueue_style( 'bbp-report-styles', plugins_url( 'assets/css/bbp-reports.css', __FILE__ ) );
        wp_enqueue_style( 'jquery-ui', plugins_url( 'assets/css/jquery-ui.min.css', __FILE__ ) );

        /**
         * All scripts goes here
         */
        wp_enqueue_script( 'bbp-report-scripts', plugins_url( "assets/js/script$suffix.js", __FILE__ ), array( 'jquery', 'jquery-ui-datepicker' ), false, true );
    }

} // WeDevs_bbPress_Reports

$reports = WeDevs_bbPress_Reports::init();
