<?php
/**
 * Mail delivery status — make a failed send VISIBLE.
 *
 * The problem this solves is not a mail problem, it is an honesty problem.
 *
 * WordPress's lost-password flow prints "Check your email for the confirmation
 * link" whenever retrieve_password() returns, and retrieve_password() returns
 * successfully even when wp_mail() failed outright. On a host with no SMTP —
 * which this one is — Rop asks for a reset, is told to check his email, and
 * waits for something that was never sent. The screen reports a success that
 * did not happen, which is the exact failure mode the salvage board itself is
 * built to prevent.
 *
 * So: record every wp_mail failure with its reason, and say so on the screens
 * where someone is waiting on an email.
 *
 * Two rules govern this file:
 *
 * 1. NO PERMANENT WARNING. A banner that is always on is camouflage — it stops
 *    being read, and then hides the one time it mattered. Nothing here shows
 *    unless an actual send actually failed, and it clears itself once a send
 *    succeeds.
 * 2. NO SECRETS, EVER. The recorded reason is a transport error string. It is
 *    truncated and it is never allowed to carry a reset key: the whole point of
 *    a reset link is that only the mailbox owner sees it.
 *
 * @package imanicars
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'IC_SALVAGE_TEST' ) ) { exit; }

class IC_Salvage_Mail {

	const OPT_LAST_FAIL = 'ic_salvage_last_mail_failure';
	const OPT_LAST_OK   = 'ic_salvage_last_mail_success';

	/** How long a recorded failure stays worth showing. */
	const STALE_DAYS = 30;

	public static function init() {
		add_action( 'wp_mail_failed',  array( __CLASS__, 'record_failure' ) );
		add_action( 'wp_mail_succeeded', array( __CLASS__, 'record_success' ) );
		add_filter( 'login_message',   array( __CLASS__, 'login_notice' ) );
		add_action( 'admin_notices',   array( __CLASS__, 'admin_notice' ) );
	}

	/**
	 * @param WP_Error $error
	 */
	public static function record_failure( $error ) {
		if ( ! is_wp_error( $error ) ) { return; }

		update_option( self::OPT_LAST_FAIL, array(
			'time'   => time(),
			'reason' => self::scrub( $error->get_error_message() ),
		), false );
	}

	/** A success clears the standing failure, so the warning cannot outlive the fault. */
	public static function record_success() {
		update_option( self::OPT_LAST_OK, time(), false );
		delete_option( self::OPT_LAST_FAIL );
	}

	/**
	 * Strip anything that could be a credential or a reset key.
	 *
	 * PHPMailer errors quote the SMTP conversation, and that conversation can
	 * contain an AUTH line or a message body. A reset key printed on a login
	 * screen would hand the account to whoever is looking at it.
	 */
	public static function scrub( $msg ) {
		$msg = (string) $msg;
		// Anything that looks like a key, token, password or auth blob.
		//
		// The lookbehind, not \b, is what makes this work: underscore is a word
		// character, so \bkey\b does NOT match inside "api_key" — the exact
		// spelling an SMTP library uses. Rejecting only a preceding LETTER
		// catches api_key, smtp.password and X-Auth-Token while still leaving
		// "monkey" alone.
		$msg = preg_replace( '/(?<![a-z])(key|token|pass(?:word)?|auth|secret|bearer)\b\s*[:=]?\s*\S+/i', '$1 [redacted]', $msg );
		// Reset links carry the key in the query string.
		$msg = preg_replace( '#https?://\S+#i', '[link redacted]', $msg );
		// Long opaque strings.
		$msg = preg_replace( '/\b[A-Za-z0-9]{20,}\b/', '[redacted]', $msg );

		// Anything mixing letters AND digits over ten characters: a key, a token,
		// a nonce, a message id.
		//
		// This is the rule that actually catches secrets, and the keyword rule
		// above is only a helper. A header like "X-Auth-Token: abc123def456" lets
		// the keyword rule match "Auth" and consume "-Token:" as its value, so the
		// token itself walks straight past it. Shape, not label, is what a
		// credential can be recognised by.
		//
		// Mixed alnum is the discriminator that keeps ordinary diagnostics
		// readable: "535", "port 25" and "authentication" are digits-only or
		// letters-only and all survive.
		$msg = preg_replace(
			'/\b(?=[A-Za-z0-9]{10,}\b)(?=[A-Za-z0-9]*[A-Za-z])(?=[A-Za-z0-9]*\d)[A-Za-z0-9]+\b/',
			'[redacted]',
			$msg
		);
		$msg = trim( preg_replace( '/\s+/', ' ', $msg ) );
		return ( strlen( $msg ) > 300 ) ? substr( $msg, 0, 300 ) . '…' : $msg;
	}

	/** The standing failure, or null when there is nothing to report. */
	public static function last_failure() {
		$f = get_option( self::OPT_LAST_FAIL );
		if ( ! is_array( $f ) || empty( $f['time'] ) ) { return null; }
		if ( ( time() - (int) $f['time'] ) > ( self::STALE_DAYS * DAY_IN_SECONDS ) ) { return null; }
		return $f;
	}

	private static function message( $f ) {
		return sprintf(
			/* translators: 1: how long ago, 2: the transport's reason */
			__( 'This site last failed to send an email %1$s ago. The reason given was: %2$s', 'imanicars' ),
			human_time_diff( (int) $f['time'] ),
			$f['reason'] ? $f['reason'] : __( 'none given', 'imanicars' )
		);
	}

	/**
	 * On the login screens where someone is waiting for an email.
	 *
	 * Shown on lostpassword (before asking) and on checkemail=confirm (right
	 * after WordPress has said "check your email"), because that reassurance is
	 * the specific thing that needs contradicting.
	 */
	public static function login_notice( $message ) {
		$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$check  = isset( $_GET['checkemail'] ) ? sanitize_key( wp_unslash( $_GET['checkemail'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

		if ( ! in_array( $action, array( 'lostpassword', 'retrievepassword' ), true ) && 'confirm' !== $check ) {
			return $message;
		}

		$f = self::last_failure();
		if ( ! $f ) { return $message; }

		$notice  = '<div id="login_error" style="border-left-color:#a86400">';
		$notice .= '<strong>' . esc_html__( 'The reset email may not arrive.', 'imanicars' ) . '</strong><br>';
		$notice .= esc_html( self::message( $f ) ) . '<br><br>';
		$notice .= esc_html__( 'This host has no SMTP configured, so WordPress is using PHP mail() and the host is refusing it. A reset link cannot reach you until SMTP is set up. Ask whoever administers the site to reset the password directly instead of waiting for this email.', 'imanicars' );
		$notice .= '</div>';

		return $notice . $message;
	}

	/** And in wp-admin, for the person who can actually fix it. */
	public static function admin_notice() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$f = self::last_failure();
		if ( ! $f ) { return; }

		echo '<div class="notice notice-warning"><p><strong>'
			. esc_html__( 'Email is not being delivered from this site.', 'imanicars' )
			. '</strong> ' . esc_html( self::message( $f ) ) . '</p><p>'
			. esc_html__( 'Until SMTP is configured, password resets, the salvage board\'s Email button and every other outgoing message will fail. Install an SMTP plugin and give it a SendGrid API key — the key goes into the plugin, never into the theme or the repository.', 'imanicars' )
			. '</p></div>';
	}
}
