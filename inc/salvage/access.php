<?php
/**
 * Salvage board — access control and search-engine exclusion.
 *
 * The board is proprietary: it shows which lots Imani is bidding on, at which
 * auctions, with valuations attached. Exposure to a rival bidder at the same
 * auction is the failure this file exists to prevent.
 *
 * Authentication is WordPress core's. No custom login form, no custom session,
 * no password store of our own — core already does all three correctly, and a
 * hand-rolled version of any of them would be strictly worse.
 *
 * Exclusion is layered, because any single mechanism can be misconfigured:
 *
 *   1. template_redirect  — unauthenticated visitors go to wp-login.php
 *   2. X-Robots-Tag       — sent on the response AND on the redirect
 *   3. <meta robots>      — noindex, nofollow, noarchive, nosnippet
 *   4. REST API           — the page is removed from collections and single reads
 *   5. Core sitemap       — wp-sitemap.xml excludes the page
 *   6. Search / menus     — the page never appears in site search or nav lists
 *
 * The page is deliberately NOT listed in robots.txt. robots.txt is world
 * readable, so a Disallow line would publish the very URL being protected. The
 * page is unreachable without a login and carries noindex; advertising its path
 * would be a net loss.
 *
 * @package imanicars
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class IC_Salvage_Access {

	const SLUG      = 'insurance';
	const CAP       = 'view_salvage_board';
	const ROLE      = 'salvage_viewer';
	const ROLE_NAME = 'Salvage Viewer';
	const OPT_PAGE  = 'ic_salvage_page_id';

	public static function init() {
		add_action( 'init',              array( __CLASS__, 'register_role' ) );
		add_action( 'admin_init',        array( __CLASS__, 'ensure_page' ) );
		add_action( 'template_redirect', array( __CLASS__, 'guard' ), 1 );
		add_action( 'wp_head',           array( __CLASS__, 'noindex_meta' ), 1 );

		// Keep the page out of every public listing surface.
		add_filter( 'rest_page_query',              array( __CLASS__, 'rest_exclude_collection' ), 10, 2 );
		add_filter( 'rest_prepare_page',            array( __CLASS__, 'rest_exclude_single' ), 10, 3 );
		add_filter( 'wp_sitemaps_posts_query_args', array( __CLASS__, 'sitemap_exclude' ), 10, 2 );
		add_filter( 'pre_get_posts',                array( __CLASS__, 'exclude_from_search' ) );
		add_filter( 'wp_list_pages_excludes',       array( __CLASS__, 'exclude_from_page_lists' ) );
		add_filter( 'get_pages',                    array( __CLASS__, 'exclude_from_get_pages' ) );
	}

	/**
	 * A dedicated role holding one capability.
	 *
	 * Single-tenant by design. No per-tenant scoping, no subscription gating, no
	 * role hierarchy — one dataset, one viewer. Keeping it that way is most of
	 * what keeps it secure.
	 */
	public static function register_role() {
		$role = get_role( self::ROLE );
		if ( ! $role ) {
			add_role( self::ROLE, self::ROLE_NAME, array(
				'read'               => true,
				'read_private_pages' => true,
				self::CAP            => true,
			) );
		} elseif ( ! $role->has_cap( self::CAP ) ) {
			$role->add_cap( self::CAP );
		}

		// Administrators can always see it, so the board cannot be locked away by
		// a role that was edited or removed by a plugin.
		$admin = get_role( 'administrator' );
		if ( $admin && ! $admin->has_cap( self::CAP ) ) {
			$admin->add_cap( self::CAP );
		}
	}

	/** The WP page backing /insurance. Created once, then remembered by ID. */
	public static function ensure_page() {
		$id = (int) get_option( self::OPT_PAGE );
		if ( $id && 'page' === get_post_type( $id ) && 'trash' !== get_post_status( $id ) ) {
			return $id;
		}

		$existing = get_page_by_path( self::SLUG, OBJECT, 'page' );
		if ( $existing ) {
			update_option( self::OPT_PAGE, (int) $existing->ID );
			return (int) $existing->ID;
		}

		$new = wp_insert_post( array(
			'post_type'      => 'page',
			'post_name'      => self::SLUG,
			'post_title'     => 'Salvage Board',
			'post_status'    => 'publish',
			'post_content'   => '',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
		) );

		if ( $new && ! is_wp_error( $new ) ) {
			update_option( self::OPT_PAGE, (int) $new );
			return (int) $new;
		}
		return 0;
	}

	public static function page_id() {
		return (int) get_option( self::OPT_PAGE );
	}

	/** Is the current request the board page? */
	public static function is_board_page() {
		if ( is_admin() ) { return false; }
		$id = self::page_id();
		if ( $id && is_page( $id ) ) { return true; }
		return is_page( self::SLUG );
	}

	public static function user_may_view( $user_id = null ) {
		if ( null === $user_id ) {
			return is_user_logged_in() && current_user_can( self::CAP );
		}
		return user_can( $user_id, self::CAP );
	}

	/**
	 * The gate.
	 *
	 * Logged out            -> wp-login.php, returning here afterwards.
	 * Logged in, no cap     -> 403, and it says so plainly.
	 * Logged in, has cap    -> through, with noindex headers attached.
	 */
	public static function guard() {
		if ( ! self::is_board_page() ) { return; }

		// Robots headers go out on every branch below, including the redirect.
		self::send_robots_header();

		if ( ! is_user_logged_in() ) {
			nocache_headers();
			wp_safe_redirect( wp_login_url( self::board_url() ), 302 );
			exit;
		}

		if ( ! current_user_can( self::CAP ) ) {
			nocache_headers();
			wp_die(
				esc_html__( 'Your account does not have access to the salvage board.', 'imanicars' ),
				esc_html__( 'Access denied', 'imanicars' ),
				array( 'response' => 403, 'back_link' => true )
			);
		}

		// The board carries commercial data; never let a proxy or browser cache it.
		nocache_headers();
	}

	public static function board_url() {
		$id = self::page_id();
		$url = $id ? get_permalink( $id ) : home_url( '/' . self::SLUG . '/' );
		return set_url_scheme( $url, 'https' );
	}

	public static function send_robots_header() {
		if ( headers_sent() ) { return; }
		header( 'X-Robots-Tag: noindex, nofollow, noarchive, nosnippet', true );
	}

	/** Emitted at wp_head priority 1, ahead of the theme's own robots tag. */
	public static function noindex_meta() {
		if ( ! self::is_board_page() ) { return; }
		echo "<meta name=\"robots\" content=\"noindex, nofollow, noarchive, nosnippet\">\n";
	}

	/* ---------------------------------------------------------------
	 * Listing-surface exclusions
	 * ------------------------------------------------------------- */

	public static function rest_exclude_collection( $args, $request ) {
		$id = self::page_id();
		if ( ! $id || self::user_may_view() ) { return $args; }
		$not = isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array();
		$not[] = $id;
		$args['post__not_in'] = $not;
		return $args;
	}

	public static function rest_exclude_single( $response, $post, $request ) {
		$id = self::page_id();
		if ( ! $id || ! $post || (int) $post->ID !== $id ) { return $response; }
		if ( self::user_may_view() ) { return $response; }
		return new WP_REST_Response( array(
			'code'    => 'rest_forbidden',
			'message' => __( 'You are not allowed to view this resource.', 'imanicars' ),
			'data'    => array( 'status' => 401 ),
		), 401 );
	}

	public static function sitemap_exclude( $args, $post_type ) {
		if ( 'page' !== $post_type ) { return $args; }
		$id = self::page_id();
		if ( ! $id ) { return $args; }
		$not = isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array();
		$not[] = $id;
		$args['post__not_in'] = $not;
		return $args;
	}

	public static function exclude_from_search( $query ) {
		if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) { return; }
		$id = self::page_id();
		if ( ! $id || self::user_may_view() ) { return; }
		$not = (array) $query->get( 'post__not_in' );
		$not[] = $id;
		$query->set( 'post__not_in', $not );
	}

	public static function exclude_from_page_lists( $excludes ) {
		$id = self::page_id();
		if ( $id ) { $excludes[] = $id; }
		return $excludes;
	}

	public static function exclude_from_get_pages( $pages ) {
		$id = self::page_id();
		if ( ! $id || self::user_may_view() ) { return $pages; }
		foreach ( $pages as $k => $p ) {
			if ( isset( $p->ID ) && (int) $p->ID === $id ) { unset( $pages[ $k ] ); }
		}
		return array_values( $pages );
	}
}

/** Used by the theme's SEO head to suppress its unconditional "index, follow". */
function ic_salvage_is_board_page() {
	return class_exists( 'IC_Salvage_Access' ) && IC_Salvage_Access::is_board_page();
}
