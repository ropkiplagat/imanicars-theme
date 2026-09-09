<?php
/**
 * Salvage board test harness.
 *
 * Deliberately tiny and dependency-free: it runs on a bare `php:8.2-cli`
 * container with no composer install, so the gate cannot be skipped for want of
 * tooling.
 *
 * The runner's exit code is derived from the assertion counters below. There is
 * no separate "tests passed" artefact that could report success on a run that
 * never executed an assertion — a zero-assertion run FAILS.
 */

define( 'IC_SALVAGE_TEST', true );

$GLOBALS['ic_t'] = array(
	'pass'    => 0,
	'fail'    => 0,
	'fails'   => array(),
	'current' => '',
);

$root = dirname( dirname( __DIR__ ) );
require_once $root . '/inc/salvage/fees.php';
require_once $root . '/inc/salvage/rules.php';
require_once $root . '/inc/salvage/saledate.php';
require_once $root . '/inc/salvage/normalise.php';
require_once $root . '/inc/salvage/books.php';

function ic_test( $name ) {
	$GLOBALS['ic_t']['current'] = $name;
}

function ic_ok( $cond, $msg ) {
	if ( $cond ) {
		$GLOBALS['ic_t']['pass']++;
		return true;
	}
	$GLOBALS['ic_t']['fail']++;
	$GLOBALS['ic_t']['fails'][] = $GLOBALS['ic_t']['current'] . ' :: ' . $msg;
	return false;
}

function ic_is( $actual, $expected, $msg ) {
	$same = ( $actual === $expected );
	if ( ! $same ) {
		$msg .= sprintf( ' (expected %s, got %s)', ic_dump( $expected ), ic_dump( $actual ) );
	}
	return ic_ok( $same, $msg );
}

function ic_dump( $v ) {
	if ( null === $v ) { return 'NULL'; }
	if ( true === $v ) { return 'TRUE'; }
	if ( false === $v ) { return 'FALSE'; }
	if ( is_array( $v ) ) { return 'array(' . count( $v ) . ')'; }
	if ( is_string( $v ) ) { return '"' . ( strlen( $v ) > 90 ? substr( $v, 0, 90 ) . '…' : $v ) . '"'; }
	return (string) $v;
}
