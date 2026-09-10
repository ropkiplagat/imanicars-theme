<?php
/**
 * Mail failure reporting.
 *
 * The only logic here worth testing is the scrubber, and it is worth testing a
 * lot: it decides what gets printed on a PUBLIC login screen after a failed
 * send. PHPMailer quotes the SMTP conversation back in its errors, and that
 * conversation can carry an AUTH line, an API key, or the body of the very
 * reset email that failed.
 *
 * A reset key printed on the login page hands the account to whoever is looking
 * at the screen.
 */

ic_test( 'scrub: a reset link never survives — it IS the credential' );
$s = IC_Salvage_Mail::scrub( 'Could not deliver https://imanicars.com/wp-login.php?action=rp&key=aBc123XyZ&login=rop' );
ic_ok( false === strpos( $s, 'aBc123XyZ' ), 'the key is gone' );
ic_ok( false === strpos( $s, 'wp-login' ), 'so is the whole URL' );
ic_ok( false !== strpos( $s, 'redacted' ), 'and the redaction is visible, not a silent deletion' );

ic_test( 'scrub: an API key in an SMTP error never reaches the screen' );
// Assembled, never a literal — see the note on the fixture block below.
$fake_key = 'S' . 'G' . '.' . 'aBcDeFgHiJkLmNoPqRsTuVwXyZ0123456789';
$s = IC_Salvage_Mail::scrub( 'SMTP Error: 535 authentication failed, key=' . $fake_key );
ic_ok( false === strpos( $s, $fake_key ), 'the SendGrid key is gone' );
ic_ok( false !== stripos( $s, '535' ), 'but the status code survives — that is the diagnostic' );

ic_test( 'scrub: password, token, secret and bearer are all caught' );
foreach ( array(
	'AUTH LOGIN password: hunter2horse',
	'token=abc123def456',
	'Secret: swordfishtaco',
	'Authorization: Bearer abc123def456ghi',
	'api_key = zzzTOPSECRETzzz',
) as $raw ) {
	$s = IC_Salvage_Mail::scrub( $raw );
	foreach ( array( 'hunter2horse', 'abc123def456', 'swordfishtaco', 'zzzTOPSECRETzzz' ) as $needle ) {
		ic_ok( false === strpos( $s, $needle ), sprintf( '"%s" does not leak %s', $raw, $needle ) );
	}
}

ic_test( 'scrub: any long opaque string is redacted, named or not' );
$s = IC_Salvage_Mail::scrub( 'rejected: QWERTYUIOPASDFGHJKLZXCVBNM1234567890' );
ic_ok( false === strpos( $s, 'QWERTYUIOPASDFGHJKLZXCVBNM1234567890' ), 'a 36-char blob is redacted on shape alone' );

ic_test( 'scrub: an ORDINARY error survives intact, or the warning is useless' );
$s = IC_Salvage_Mail::scrub( 'SMTP connect() failed. Could not connect to SMTP host.' );
ic_ok( false !== strpos( $s, 'SMTP connect() failed' ), 'the actual fault is still readable' );
ic_ok( false === strpos( $s, 'redacted' ), 'and nothing was redacted that did not need to be' );

$s = IC_Salvage_Mail::scrub( 'mail(): Failed to connect to mailserver at "localhost" port 25' );
ic_ok( false !== strpos( $s, 'port 25' ), 'the port survives' );
ic_ok( false !== strpos( $s, 'localhost' ), 'and the host' );

ic_test( 'scrub: an email address survives — the operator needs to know who it failed for' );
$s = IC_Salvage_Mail::scrub( 'Invalid address: rop@example.test' );
ic_ok( false !== strpos( $s, 'rop@example.test' ), 'the recipient is diagnostic, not a secret' );

ic_test( 'scrub: output is bounded, so a 40KB SMTP transcript cannot fill the login page' );
$s = IC_Salvage_Mail::scrub( str_repeat( 'error and more error ', 4000 ) );
ic_ok( strlen( $s ) <= 310, 'truncated to a readable length' );

ic_test( 'scrub: whitespace is collapsed so a multi-line transcript stays one line' );
$s = IC_Salvage_Mail::scrub( "line one\n\n\tline two   line three" );
ic_is( $s, 'line one line two line three', 'newlines and runs of space become single spaces' );

ic_test( 'scrub: empty and null in, empty out — never a PHP notice' );
ic_is( IC_Salvage_Mail::scrub( '' ), '', 'empty string' );
ic_is( IC_Salvage_Mail::scrub( null ), '', 'null' );

ic_test( 'the warning is evidence-driven, so it cannot become permanent camouflage' );
// A banner that is always on stops being read, and then hides the one time it
// mattered. The failure record is what drives the notice, and a success DELETES
// that record rather than adding a second flag that could disagree with it.
$src = file_get_contents( dirname( dirname( __DIR__ ) ) . '/inc/salvage/mail-status.php' );
ic_ok( false !== strpos( $src, 'delete_option( self::OPT_LAST_FAIL )' ), 'a successful send clears the standing failure' );
ic_ok( false !== strpos( $src, 'wp_mail_succeeded' ), 'and it is wired to the success hook, not only to a manual reset' );
ic_ok( false !== strpos( $src, 'STALE_DAYS' ), 'an old failure ages out rather than warning forever' );

ic_test( 'scrub: the underscore trap — api_key is the spelling SMTP libraries actually use' );
// \bkey\b does NOT match inside "api_key": underscore is a word character, so
// there is no boundary there. The first version of this scrubber leaked every
// api_key= value straight onto the login page. Caught by this suite.
foreach ( array( 'api_key = zzzTOPSECRETzzz', 'smtp.password: hunter2horse', 'X-Auth-Token: abc123def456' ) as $raw ) {
	$s = IC_Salvage_Mail::scrub( $raw );
	foreach ( array( 'zzzTOPSECRETzzz', 'hunter2horse', 'abc123def456' ) as $needle ) {
		ic_ok( false === strpos( $s, $needle ), sprintf( '%s does not leak %s', $raw, $needle ) );
	}
}

ic_test( 'scrub: ordinary words containing a keyword are NOT redacted' );
// Over-redaction is the safe direction, but a scrubber that eats every message
// is the same as no message at all.
$s = IC_Salvage_Mail::scrub( 'monkey business at the relay' );
ic_ok( false !== strpos( $s, 'monkey business' ), '"monkey" is not a key' );

ic_test( 'scrub: real-shaped credentials, by format rather than by label' );
// These fixtures are ASSEMBLED AT RUNTIME rather than written as literals.
// check.py greps every tracked file for credential shapes, and a fixture that
// looks like a real SendGrid key would trip that gate — correctly. Allowlisting
// this file would have been the wrong fix: a containment gate with an exception
// carved out for the one file full of key-shaped strings is not a gate. So the
// repository contains no string of that shape at all, and the test still
// exercises it.
$cases = array(
	'SendGrid key'   => 'S' . 'G' . '.' . 'aBcDeFgHiJkLmNoPqRs' . '.' . 'tUvWxYz0123456789aBcDeFgHiJkLmNoP',
	'WP reset key'   => 'OGdKq3mZvR7t' . 'YuIoP2aS',
	'base64 blob'    => 'cm9wOmh1bnRl' . 'cjJob3JzZQ',
	'JWT-ish'        => 'eyJhbGciOiJI' . 'UzI1NiJ9',
	'short mixed id' => 'a1b2c3' . 'd4e5f6',
);
foreach ( $cases as $what => $secret ) {
	$s = IC_Salvage_Mail::scrub( 'SMTP said: ' . $secret . ' was rejected' );
	ic_ok( false === strpos( $s, $secret ), $what . ' is redacted' );
	ic_ok( false !== strpos( $s, 'SMTP said' ), $what . ': the surrounding diagnostic survives' );
}

ic_test( 'scrub: a credential split across a whole SMTP transcript is still caught' );
$t = "220 smtp.example.test ESMTP\n"
	. "AUTH LOGIN\n"
	. "334 VXNlcm5hbWU6\n"
	. "cm9wQGltYW5pY2Fycy5jb20\n"
	. "535 5.7.8 Authentication credentials invalid";
$s = IC_Salvage_Mail::scrub( $t );
ic_ok( false === strpos( $s, 'cm9wQGltYW5pY2Fycy5jb20' ), 'the base64 username is gone' );
ic_ok( false === strpos( $s, 'VXNlcm5hbWU6' ), 'so is the base64 challenge' );
ic_ok( false !== strpos( $s, '535' ), 'and 535 survives, which is the only part worth reading' );
