<?php
/*
Plugin Name: Instant Logout
Plugin URI: http://wordpress.org/plugins/instant-logout/
Description: Remove the "Are you sure" message when logging out.
Author: r16n
Version: 0.1.0
Author URI: https://profiles.wordpress.org/r16n
*/
if (!defined('ABSPATH'))
{
	exit;
}

class InstantLogout
{
	/**
	 * Constructor
	 */
	public function __construct ()
	{
		// Hook into logout
		add_filter('logout_url', array($this, 'removeNonce'), 10, 2);
		add_action('login_form_logout', array($this, 'logout'));

		// Add shortcode
		add_shortcode('instant-logout', array($this, 'shortcode'));
		add_action('template_redirect', array($this, 'shortcodeLogout'));
	}

	/**
	 * Hollow shortcode to trigger shortcodeLogout().
	 * 
	 * @return string Empty string
	 */
	public function shortcode ()
	{
		return '';
	}

	/**
	 * Check the page for the shortcode. If it's found log the user out and redirect.
	 * 
	 * @global WP_Post $post
	 */
	public function shortcodeLogout ()
	{
		global $post;

		if (is_singular() && !empty($post) && isset($post->ID) && !empty($post->ID) && strpos($post->post_content, '[instant-logout]') !== false)
		{
			wp_destroy_current_session();
			wp_clear_auth_cookie();

			$redirect_to = !empty($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : site_url('wp-login.php?loggedout=true');
			wp_safe_redirect($redirect_to);
			exit();
		}
	}

	/**
	 * Remove the logout nonce.
	 * 
	 * @param string $logout_url Logout URL
	 * @param string $redirect Redirect URL
	 * @return string Logout URL without nonce
	 */
	public function removeNonce ($logout_url, $redirect)
	{
		// Is the URL escaped?
		if (strpos($logout_url, '&amp;') !== false)
		{
			return htmlspecialchars(remove_query_arg('_wpnonce', htmlspecialchars_decode($logout_url)));
		}

		return remove_query_arg('_wpnonce', $logout_url);
	}

	/**
	 *	Instantly logout.
	 */
	public function logout ()
	{
		wp_logout();

		$redirect_to = !empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : 'wp-login.php?loggedout=true';
		wp_safe_redirect( $redirect_to );
		exit();
	}
}

new InstantLogout();
new InstantLogout();