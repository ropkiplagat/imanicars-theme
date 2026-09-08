<?php
/**
 * Sale-date parsing and the "sells soon" flags.
 *
 * The published strings carry a weekday but no year. The weekday is the only
 * cross-check available, so it is used as one: a string whose weekday does not
 * match any nearby year is refused rather than resolved to a guess.
 */

$ref = '2026-09-07'; // the scan date
$tz  = 'Australia/Sydney';

ic_test( 'sale date: real published formats parse to the right instant' );
$p = IC_Salvage_SaleDate::parse( 'Wed 16/09 12:00PM', $ref, $tz );
ic_is( $p['datetime'], '2026-09-16 12:00:00', 'noon stays noon — 12PM is not midnight' );
ic_is( $p['weekday_ok'], true, 'weekday cross-check passed' );

ic_is( IC_Salvage_SaleDate::parse( 'Tue 08/09 10:30AM', $ref, $tz )['datetime'], '2026-09-08 10:30:00', 'morning sale' );
ic_is( IC_Salvage_SaleDate::parse( 'Thu 10/09 01:00PM', $ref, $tz )['datetime'], '2026-09-10 13:00:00', '1PM becomes 13:00' );
ic_is( IC_Salvage_SaleDate::parse( 'Mon 21/09 09:30AM', $ref, $tz )['datetime'], '2026-09-21 09:30:00', 'next-week sale' );
ic_is( IC_Salvage_SaleDate::parse( 'Fri 18/09 10:00AM', $ref, $tz )['datetime'], '2026-09-18 10:00:00', 'Friday sale' );

ic_test( 'sale date: midnight and noon do not collapse into each other' );
ic_is( IC_Salvage_SaleDate::parse( '16/09 12:00AM', $ref, $tz )['datetime'], '2026-09-16 00:00:00', '12AM is midnight' );
ic_is( IC_Salvage_SaleDate::parse( '16/09 12:00PM', $ref, $tz )['datetime'], '2026-09-16 12:00:00', '12PM is noon' );

ic_test( 'sale date: absent times stay absent' );
foreach ( array( '-', '?', '', 'n/a' ) as $blank ) {
	$p = IC_Salvage_SaleDate::parse( $blank, $ref, $tz );
	ic_is( $p['datetime'], null, sprintf( '"%s" yields no datetime', $blank ) );
	ic_ok( null !== $p['unknown_reason'], sprintf( '"%s" explains why', $blank ) );
}
ic_is( IC_Salvage_SaleDate::parse( null, $ref, $tz )['datetime'], null, 'null yields no datetime' );

ic_test( 'sale date: a weekday that does not match is refused, not resolved' );
$p = IC_Salvage_SaleDate::parse( 'Mon 16/09 12:00PM', $ref, $tz ); // 16/09/2026 is a Wednesday
ic_is( $p['datetime'], null, 'no datetime is produced from a contradictory string' );
ic_is( $p['weekday_ok'], false, 'the mismatch is recorded' );
ic_ok( null !== $p['unknown_reason'], 'and explained' );

ic_test( 'sale date: junk is refused' );
ic_is( IC_Salvage_SaleDate::parse( 'sometime next week', $ref, $tz )['datetime'], null, 'free text' );
ic_is( IC_Salvage_SaleDate::parse( '32/09 10:00AM', $ref, $tz )['datetime'], null, 'impossible day' );
ic_is( IC_Salvage_SaleDate::parse( '16/13 10:00AM', $ref, $tz )['datetime'], null, 'impossible month' );

ic_test( 'sale date: the year is chosen by weekday, across a year boundary' );
// 04/01 is a Monday in 2027; scanning on 30 Dec 2026 must resolve it forward.
$p = IC_Salvage_SaleDate::parse( 'Mon 04/01 10:00AM', '2026-12-30', $tz );
ic_is( $p['datetime'], '2027-01-04 10:00:00', 'a January sale seen in December resolves to next year' );

ic_test( 'sells within 48h' );
$now = '2026-09-08 07:00:00';
ic_is( IC_Salvage_SaleDate::sells_within_hours( '2026-09-09 09:30:00', $now, 48, $tz ), true, 'tomorrow morning is inside 48h' );
ic_is( IC_Salvage_SaleDate::sells_within_hours( '2026-09-10 06:00:00', $now, 48, $tz ), true, 'just inside 48h' );
ic_is( IC_Salvage_SaleDate::sells_within_hours( '2026-09-11 09:30:00', $now, 48, $tz ), false, 'three days out is outside' );
ic_is( IC_Salvage_SaleDate::sells_within_hours( '2026-09-07 09:30:00', $now, 48, $tz ), false, 'already sold is not upcoming' );

ic_test( 'an unknown sale time is UNKNOWN, not "no"' );
ic_is( IC_Salvage_SaleDate::sells_within_hours( null, $now, 48, $tz ), null, 'null sale time gives null, not false' );
ic_is( IC_Salvage_SaleDate::sells_tomorrow( null, $now, $tz ), null, 'and the same for sells-tomorrow' );

ic_test( 'sells tomorrow is a local CALENDAR day, not a 24-hour offset' );
ic_is( IC_Salvage_SaleDate::sells_tomorrow( '2026-09-09 09:30:00', $now, $tz ), true, 'next calendar day' );
ic_is( IC_Salvage_SaleDate::sells_tomorrow( '2026-09-10 09:30:00', $now, $tz ), false, 'two days out' );
ic_is( IC_Salvage_SaleDate::sells_tomorrow( '2026-09-08 09:30:00', $now, $tz ), false, 'today is not tomorrow' );

// 23:30 today to 00:30 the next day is one hour apart, but it IS tomorrow.
ic_is( IC_Salvage_SaleDate::sells_tomorrow( '2026-09-09 00:30:00', '2026-09-08 23:30:00', $tz ), true, 'one hour later across midnight is tomorrow' );
// 25 hours apart, but both fall on the same calendar day boundary check.
ic_is( IC_Salvage_SaleDate::sells_tomorrow( '2026-09-09 23:00:00', '2026-09-08 00:30:00', $tz ), true, '46 hours out is still tomorrow by calendar day' );

ic_test( 'day boundaries are resolved on the Australian clock, not UTC' );
// 2026-09-09 09:00 Sydney is 2026-09-08 23:00 UTC. Computed in UTC this would
// read as "today"; on the local clock it is correctly tomorrow.
ic_is( IC_Salvage_SaleDate::sells_tomorrow( '2026-09-09 09:00:00', '2026-09-08 08:00:00', $tz ), true, 'a morning sale that is the previous day in UTC is still tomorrow locally' );

ic_test( 'past sales are identified' );
ic_is( IC_Salvage_SaleDate::is_past( '2026-09-07 09:30:00', $now, $tz ), true, 'yesterday has passed' );
ic_is( IC_Salvage_SaleDate::is_past( '2026-09-09 09:30:00', $now, $tz ), false, 'tomorrow has not' );
ic_is( IC_Salvage_SaleDate::is_past( null, $now, $tz ), null, 'unknown stays unknown' );
