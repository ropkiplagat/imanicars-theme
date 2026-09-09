<?php
/**
 * Salvage board test runner.
 *
 * Usage:  php tests/salvage/run.php
 *
 * The exit code is computed from the assertions that actually ran. A run that
 * loads no test files, or executes no assertions, FAILS — a green result must be
 * evidence that the checks executed, not merely that nothing threw.
 */

require_once __DIR__ . '/bootstrap.php';

$files = glob( __DIR__ . '/test_*.php' );
sort( $files );

if ( ! $files ) {
	fwrite( STDERR, "FAIL: no test files found in " . __DIR__ . "\n" );
	exit( 1 );
}

/**
 * Test files are loaded inside a closure so their local variables cannot reach
 * the runner's. Requiring them at top level shares scope, and a `$f` in a test
 * silently overwrites this loop's `$f` — which is how a passing suite turned
 * into a fatal in basename().
 */
$load = static function ( $ic_test_file ) {
	require $ic_test_file;
};

foreach ( $files as $f ) {
	$before = $GLOBALS['ic_t']['pass'] + $GLOBALS['ic_t']['fail'];
	$load( $f );
	$after = $GLOBALS['ic_t']['pass'] + $GLOBALS['ic_t']['fail'];
	if ( $after === $before ) {
		$GLOBALS['ic_t']['fail']++;
		$GLOBALS['ic_t']['fails'][] = basename( $f ) . ' :: file ran but asserted nothing';
	}
	printf( "  %-24s %3d assertions\n", basename( $f ), $after - $before );
}

$t     = $GLOBALS['ic_t'];
$total = $t['pass'] + $t['fail'];

echo str_repeat( '-', 62 ) . "\n";

if ( 0 === $total ) {
	fwrite( STDERR, "FAIL: zero assertions executed — the suite did not run.\n" );
	exit( 1 );
}

if ( $t['fail'] > 0 ) {
	echo "FAILURES:\n";
	foreach ( $t['fails'] as $msg ) {
		echo '  x ' . $msg . "\n";
	}
	echo str_repeat( '-', 62 ) . "\n";
	printf( "FAIL  %d passed, %d failed, %d total\n", $t['pass'], $t['fail'], $total );
	exit( 1 );
}

printf( "PASS  %d assertions, %d files\n", $t['pass'], count( $files ) );
exit( 0 );
