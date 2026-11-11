<?php
/*
Plugin Name: KO: Custom Functions (Divi 5)
Author: <a href="mailto:6822858@kevinoneill.us">KO</a>
Version: 2.9.4
Author URI: https://kevinoneill.us
Description: Add custom functions and filters by editing ko-custom-functions.php.
Requires at least: 6.7
Requires PHP: 7.4
*/
/* Start Adding Functions Below this Line */

/* Administrator-only account email changes */
require_once __DIR__ . '/includes/email-restriction.php';
/* END Administrator-only account email changes */

/////////////// Add typeface to login screen /////////////////
function ko_cf_enqueue_login_font() {
	wp_enqueue_style(
		'ko-cf-login-font',
		'https://fonts.googleapis.com/css?family=Montserrat&display=swap',
		array(),
		null
	);
}
add_action( 'login_enqueue_scripts', 'ko_cf_enqueue_login_font' );

/////////////// Customize login page (logo, form, & background) ///////////////
function ko_cf_customize_login() { ?>
<style type="text/css">
	
	.itsec-pwls-login__submit {
    margin-top: 20px !important;
    padding: 25px 1px !important;
}
	
    body { background: #fff; }

	#login { text-align:center; }
    body.login div#login h1 a { background-image:url(https://kevinoneill.us/wp-content/uploads/2026/01/OA-Logo-2025.svg) !important; background-size: 70% !important; background-position: center; width:100% !important; height:150px !important; background-color: #fff; margin-bottom:0px !important;}
    .login #nav a, .login #nav a:hover { color:#000000 !important; text-shadow: none; }
    #backtoblog { display:none !important; }
    .login form { padding: 1px 24px 1px !important;  margin-top:0px !important; background:#fff !important; border: none !important; box-shadow: none !important; }
    .login form .input, .login input[type=text] { border-radius:0px !important; }
    .login { background-color:#fff !important; background-position: center; background-size: cover; }
    .wp-core-ui .button {
    background: #000000 !important;
    border-color: #000000 !important;
    box-shadow: none !important;
    color: #fff !important;
    text-decoration: none !important;
    text-shadow: none !important;
    border-radius: 5px !important;
    }
    .login label { color:#000000 !important; width:100% !important; text-align:center; margin-top:20px; }
    
    .login #login_error, .login .message, .login .success { border-left: 4px solid #000 !important; }
    
	#itsec-pwls-login-user-form__username { margin-top:10px; }
	
    .itsec-pwls-login__link, .itsec-pwls-login__submit, .itsec-pwls-login__link, .itsec-pwls-login__submit:hover {
    background: #000000 !important; }
    
    .login form .input, .login form input[type=checkbox], .login input[type=text] {
  -webkit-transition: all 0.30s ease-in-out !important;
  -moz-transition: all 0.30s ease-in-out !important;
  -ms-transition: all 0.30s ease-in-out !important;
  -o-transition: all 0.30s ease-in-out !important;
  outline: none !important;
  border: 0px solid #f2f2f2 !important;
  box-shadow: 0 0 1px #000000 !important;
}
 
.login form .input:focus, .login form input[type=checkbox]:focus, .login input[type=text]:focus {
  box-shadow: 0 0 2px #000000 !important;
  border: 0px solid #000000 !important;
    }
    
.wp-core-ui p .button {
    vertical-align: middle;
    width: 100%;
    margin-top: 20px;
    margin-bottom: 69px !important;
    padding: 10px 20px 20px !important;
    height: 50px !important;
    border-radius:5px !important;
}
    
</style>
<?php }
                         
add_action( 'login_enqueue_scripts', 'ko_cf_customize_login' );

/////////////// Change Login Logo Link /////////////////

function ko_cf_login_logo_url() {
	    return home_url();
}
add_filter( 'login_headerurl', 'ko_cf_login_logo_url' );

function ko_cf_login_logo_text() {
	    return get_bloginfo( 'name' );
}
add_filter( 'login_headertext', 'ko_cf_login_logo_text' );

/////////////////// Add Hide Label to Gravity Forms ///////////////////

add_filter( 'gform_enable_field_label_visibility_settings', '__return_true' );

/////////////////// Hide Divi Project Post Type ////////////////

 function ko_cf_remove_divi_projects(){
 unregister_post_type( 'project' );
 }
 add_action( 'init', 'ko_cf_remove_divi_projects' );

/////////////////// END Hide Divi Project Post Type ////////////////


// Remove Yoast SEO user roles
 /* Credit: Yoast Team
 /* Last Tested: Oct 25 2017 using Yoast SEO 5.7.1 on WordPress 4.8.2
 /*/
// Remove Yoast `SEO Manager` role
if ( get_role('wpseo_manager') ) {
    remove_role( 'wpseo_manager' );
}
// Remove Yoast `SEO Editor` role
if ( get_role('wpseo_editor') ) {
    remove_role( 'wpseo_editor' );
}

/******* Change WP Logo in Dashboard ********/
function ko_cf_dashboard_custom_logo() {
echo '
<style type="text/css">
#wpadminbar #wp-admin-bar-wp-logo > .ab-item .ab-icon:before {
background-image: url(https://kevinoneill.us/wp-content/uploads/2021/07/OA-Logo-REV-2018.svg) !important;
background-position: 0 0;
background-repeat:no-repeat;
background-size:100%;
color:rgba(0, 0, 0, 0);
}
#wpadminbar #wp-admin-bar-wp-logo.hover > .ab-item .ab-icon {
background-position: 0 0;
}
</style>
';
}

//hook into the administrative header output
add_action( 'wp_before_admin_bar_render', 'ko_cf_dashboard_custom_logo' );
/******* END Change WP Logo in Dashboard ********/


##################################
//programmatically set default role for new users
##################################
add_filter('pre_option_default_role', function($default_role){
	    return 'subscriber'; 
});
 
##################################
//untick the send the new user and email 
##################################
add_action( 'user_new_form', 'ko_cf_uncheck_new_user_notification' );
	 
function ko_cf_uncheck_new_user_notification() { 
	    echo '<script>jQuery(document).ready(function($) { 
	        $("#send_user_notification").prop("checked", false); 
	    } ); </script>';
}
 
##################################
//remove messy profile section items
##################################
	if( is_admin() ){
	    remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' );
	    add_action( 'personal_options', 'ko_cf_hide_personal_options' );
	}
	 
function ko_cf_hide_personal_options() {
  ?>
    <script type="text/javascript">
        jQuery( document ).ready(function( $ ){
	            $( '.user-rich-editing-wrap,.user-syntax-highlighting-wrap,#your-profile h3:first, .yoast, .user-description-wrap, .user-pinterest-wrap, .user-myspace-wrap, .user-soundcloud-wrap, .user-tumblr-wrap, .user-wikipedia-wrap, .user-url-wrap, .user-facebook-wrap, .user-instagram-wrap, .user-linkedin-wrap, .user-twitter-wrap, .user-youtube-wrap,#email-description,.user-comment-shortcuts-wrap' ).remove();
        } );
    </script>
  <?php
}

/**************** Clean Up WP Dashboard **************/

function ko_cf_remove_dashboard_meta() {
    remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal'); //Removes the 'incoming links' widget
    remove_meta_box('dashboard_plugins', 'dashboard', 'normal'); //Removes the 'plugins' widget
    remove_meta_box('dashboard_primary', 'dashboard', 'normal'); //Removes the 'WordPress News' widget
    remove_meta_box('dashboard_secondary', 'dashboard', 'normal'); //Removes the secondary widget
    remove_meta_box('dashboard_quick_press', 'dashboard', 'side'); //Removes the 'Quick Draft' widget
    remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side'); //Removes the 'Recent Drafts' widget
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal'); //Removes the 'Activity' widget
    remove_meta_box('dashboard_right_now', 'dashboard', 'normal'); //Removes the 'At a Glance' widget
    remove_meta_box('dashboard_activity', 'dashboard', 'normal'); //Removes the 'Activity' widget (since 3.8)

}
add_action( 'admin_init', 'ko_cf_remove_dashboard_meta' );

/*** Security against Click Jacking ***/

function ko_cf_add_custom_headers( $headers ) {
	$headers['X-Frame-Options'] = 'SAMEORIGIN';
	$headers['Content-Security-Policy'] = "frame-ancestors 'self'";

	return $headers;
}
add_filter( 'wp_headers', 'ko_cf_add_custom_headers' );

/*** END Security against Click Jacking ***/


/********* Hide admin toolbar and restrict dashboard access for Subscribers ********/
function ko_cf_current_user_is_subscriber() {
	$user = wp_get_current_user();

	return in_array( 'subscriber', (array) $user->roles, true );
}

function ko_cf_restrict_subscriber_dashboard_access() {
	    if ( is_admin() && ko_cf_current_user_is_subscriber() && ! wp_doing_ajax() ) {
	        wp_safe_redirect( home_url() );
	        exit;
	    }
}
add_action( 'admin_init', 'ko_cf_restrict_subscriber_dashboard_access' );

function ko_cf_hide_admin_bar_for_subscribers() {
	    if ( ko_cf_current_user_is_subscriber() ) {
	        show_admin_bar( false );
	    }
}
add_action( 'after_setup_theme', 'ko_cf_hide_admin_bar_for_subscribers' );

/********* Completely disable WordPress comments and pingbacks ********/

// Close comments and pingbacks everywhere, including existing content.
add_filter( 'comments_open', '__return_false', 20, 2 );
add_filter( 'pings_open', '__return_false', 20, 2 );

// Prevent existing comments from being displayed on the front end.
add_filter( 'comments_array', '__return_empty_array', 10, 2 );

// Remove comment and trackback support from every registered post type.
function ko_cf_remove_comment_support() {
	foreach ( get_post_types() as $post_type ) {
		if ( post_type_supports( $post_type, 'comments' ) ) {
			remove_post_type_support( $post_type, 'comments' );
		}

		if ( post_type_supports( $post_type, 'trackbacks' ) ) {
			remove_post_type_support( $post_type, 'trackbacks' );
		}
	}
}
add_action( 'init', 'ko_cf_remove_comment_support', 100 );

// Remove Comments from the admin menu and redirect direct access.
function ko_cf_remove_comments_admin_menu() {
	remove_menu_page( 'edit-comments.php' );
}
add_action( 'admin_menu', 'ko_cf_remove_comments_admin_menu' );

function ko_cf_redirect_comments_admin_page() {
	global $pagenow;

	if ( 'edit-comments.php' === $pagenow ) {
		wp_safe_redirect( admin_url() );
		exit;
	}
}
add_action( 'admin_init', 'ko_cf_redirect_comments_admin_page' );

// Remove the Comments item from the admin toolbar.
function ko_cf_remove_comments_admin_bar( $wp_admin_bar ) {
	$wp_admin_bar->remove_node( 'comments' );
}
add_action( 'admin_bar_menu', 'ko_cf_remove_comments_admin_bar', 999 );

// Remove comment-count columns from post and page list tables.
function ko_cf_remove_comments_column( $columns ) {
	unset( $columns['comments'] );

	return $columns;
}
add_filter( 'manage_posts_columns', 'ko_cf_remove_comments_column' );
add_filter( 'manage_pages_columns', 'ko_cf_remove_comments_column' );

// Remove comment feeds, feed links, and the X-Pingback response header.
remove_action( 'wp_head', 'feed_links_extra', 3 );

function ko_cf_remove_pingback_header( $headers ) {
	unset( $headers['X-Pingback'] );

	return $headers;
}
add_filter( 'wp_headers', 'ko_cf_remove_pingback_header' );

function ko_cf_disable_comment_feed() {
	wp_die(
		esc_html__( 'Comments are disabled.', 'ko-custom-functions' ),
		'',
		array( 'response' => 404 )
	);
}
add_action( 'do_feed_rss2_comments', 'ko_cf_disable_comment_feed', 1 );
add_action( 'do_feed_atom_comments', 'ko_cf_disable_comment_feed', 1 );

// Remove pingback methods from XML-RPC.
function ko_cf_remove_xmlrpc_pingback_methods( $methods ) {
	unset( $methods['pingback.ping'], $methods['pingback.extensions.getPingbacks'] );

	return $methods;
}
add_filter( 'xmlrpc_methods', 'ko_cf_remove_xmlrpc_pingback_methods' );

// Remove public comment routes from the WordPress REST API.
function ko_cf_remove_comment_rest_routes( $endpoints ) {
	foreach ( array_keys( $endpoints ) as $route ) {
		if ( 0 === strpos( $route, '/wp/v2/comments' ) ) {
			unset( $endpoints[ $route ] );
		}
	}

	return $endpoints;
}
add_filter( 'rest_endpoints', 'ko_cf_remove_comment_rest_routes' );


/* Stop Adding Functions Below this Line */
