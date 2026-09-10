<?php
/**
 * Source row normalisation.
 *
 * Fixtures are SYNTHETIC. Real lot data is proprietary and this repository is
 * public, so no genuine stock number, valuation or verdict appears here. The
 * fixtures copy the shape of each source feed — column order, blank markers,
 * damage phrasing — which is what these tests actually exercise.
 */

$ctx = IC_Salvage_Normalise::context( '2026-09-07' );

ic_test( 'IAA scrape: every column lands in the right field' );
$row = array( 'RAV4', '2021', 'RAV4 2.5 Hybrid GXL', '90000001', 'Front + Front Window', 'Statutory Write-Off', '93364', 'Y', 'Y', 'Y', 'SOUTH KEMPSEY, NSW', 'YES', 'https://example.test/lot/90000001' );
$r   = IC_Salvage_Normalise::from_iaa_scrape( $row, $ctx );
ic_is( $r['source'], 'IAA', 'source' );
ic_is( $r['model'], 'RAV4', 'model' );
ic_is( $r['year'], 2021, 'year is an int' );
ic_is( $r['variant'], 'RAV4 2.5 Hybrid GXL', 'variant' );
ic_is( $r['stock'], '90000001', 'stock' );
ic_is( $r['primary_damage'], 'Front', 'primary damage' );
ic_is( $r['secondary_damage'], 'Front Window', 'secondary damage' );
ic_is( $r['wovr'], 'Statutory Write-Off', 'WOVR' );
ic_is( $r['odometer_km'], 93364, 'odometer' );
ic_is( $r['keys_present'], true, 'keys' );
ic_is( $r['drives'], true, 'drives' );
ic_is( $r['starts'], true, 'starts' );
ic_is( $r['location'], 'SOUTH KEMPSEY, NSW', 'location' );
ic_is( $r['state'], 'NSW', 'state derived from location' );
ic_is( $r['make'], 'Toyota', 'make derived from model' );
ic_is( $r['detail_url'], 'https://example.test/lot/90000001', 'detail URL' );

ic_test( 'IAA scrape: a blank odometer is null, and nothing shifts around it' );
$row = array( 'LandCruiser', '2016', 'LC200 Sahara', '90000002', 'Engine Fire', 'Statutory Write-Off', '', 'Y', 'N', 'N', 'Laverton North, VIC', 'NO - pre-2019', 'https://example.test/lot/90000002' );
$r   = IC_Salvage_Normalise::from_iaa_scrape( $row, $ctx );
ic_is( $r['odometer_km'], null, 'blank odometer is null, not zero' );
ic_is( $r['keys_present'], true, 'the column AFTER the blank is still keys' );
ic_is( $r['drives'], false, 'and drives is still drives' );
ic_is( $r['starts'], false, 'and starts is still starts' );
ic_is( $r['location'], 'Laverton North, VIC', 'location did not shift left' );
ic_is( $r['state'], 'VIC', 'state still resolves' );
ic_is( $r['vic_statutory_epa'], true, 'VIC statutory write-off is flagged' );

ic_test( 'IAA scrape: a zero odometer is a real zero, not an absence' );
$row = array( 'RAV4', '2016', 'RAV4 2.0 GXL', '90000003', 'Engine Fire', 'Statutory Write-Off', '0', 'Y', 'N', 'N', 'Laverton North, VIC', 'NO - pre-2019', '' );
$r   = IC_Salvage_Normalise::from_iaa_scrape( $row, $ctx );
ic_is( $r['odometer_km'], 0, 'a published 0 km stays 0' );
ic_is( $r['detail_url'], null, 'an empty URL is null' );

ic_test( 'IAA scrape: "(none)" secondary damage is an absence' );
$row = array( 'RAV4', '2025', 'RAV4 2.5 Hybrid GXL', '90000004', 'Theft', 'No WOVR Record', '8608', 'N', 'N', 'N', 'Laverton North, VIC', 'YES', '' );
$r   = IC_Salvage_Normalise::from_iaa_scrape( $row, $ctx );
ic_is( $r['primary_damage'], 'Theft', 'primary kept' );
ic_is( $r['secondary_damage'], null, 'no secondary damage' );
ic_is( $r['keys_present'], false, 'N means no keys, not unknown' );
ic_is( $r['kebs_eligible'], true, '2025 theft-recovered is eligible' );

ic_test( 'IAA scrape: no sale date is published, and that is said out loud' );
$r = IC_Salvage_Normalise::from_iaa_scrape( array( 'RAV4', '2025', 'GXL', '90000005', 'Theft', 'No WOVR Record', '100', 'Y', 'Y', 'Y', 'Laverton North, VIC', 'YES', '' ), $ctx );
ic_is( $r['sale_datetime'], null, 'IAA feed carries no sale time' );
ic_ok( null !== $r['sale_time_note'], 'and a note explains the absence rather than leaving it blank' );

ic_test( 'Pickles: columns map, and "?" odometer is unknown' );
$row = array( 'RAV4', '2025', 'AXAH54R Cruiser Hybrid', '90000010', 'Repairable Write-Off', '?', 'Bibra Lake, WA', 'Wed 16/09 12:00PM' );
$r   = IC_Salvage_Normalise::from_pickles( $row, $ctx );
ic_is( $r['source'], 'Pickles', 'source' );
ic_is( $r['stock'], '90000010', 'stock' );
ic_is( $r['odometer_km'], null, '"?" odometer is unknown' );
ic_is( $r['location'], 'Bibra Lake, WA', 'location did not shift' );
ic_is( $r['state'], 'WA', 'state' );
ic_is( $r['sale_datetime'], '2026-09-16 12:00:00', 'sale time parsed' );

ic_test( 'Pickles: damage is not published, so eligibility is UNKNOWN not YES' );
ic_is( $r['damage_published'], false, 'no damage description in the Pickles feed' );
ic_is( $r['flood_pvoc_reject'], null, 'flood status unknown' );
ic_is( $r['kebs_eligible'], null, 'a 2025 Pickles lot is UNKNOWN, because water damage cannot be ruled out' );
ic_ok( ! empty( $r['kebs_reasons'] ), 'and the reason is recorded' );

ic_test( 'Pickles: an unpublished sale time does not become a date' );
$r = IC_Salvage_Normalise::from_pickles( array( 'Prado', '2020', 'GXL', '90000011', 'Repairable Write-Off', '80,000 km', 'Tullamarine, VIC', '-' ), $ctx );
ic_is( $r['sale_datetime'], null, 'no sale time' );
ic_is( $r['odometer_km'], 80000, 'odometer with a comma and unit' );
ic_ok( null !== $r['sale_time_note'], 'absence explained' );

ic_test( 'Manheim: the extra Colour column does not shift Location' );
$row = array( 'Ranger', '2025', 'Ford Ranger PY 2.0D XL', '90000020', 'Statutory Write-off', '50,523 km', 'White', 'Altona North, Melbourne, VIC', 'Thu 10/09 01:00PM' );
$r   = IC_Salvage_Normalise::from_manheim( $row, $ctx );
ic_is( $r['source'], 'Manheim', 'source' );
ic_is( $r['colour'], 'White', 'colour read from its own column' );
ic_is( $r['location'], 'Altona North, Melbourne, VIC', 'location is the column after colour' );
ic_is( $r['state'], 'VIC', 'state' );
ic_is( $r['odometer_km'], 50523, 'odometer' );
ic_is( $r['make'], 'Ford', 'Ranger is a Ford' );
ic_is( $r['sale_datetime'], '2026-09-10 13:00:00', 'sale time' );
ic_is( $r['vic_statutory_epa'], true, 'VIC statutory flagged at Manheim too' );

ic_test( 'Manheim: a "?" odometer keeps the later columns aligned' );
$row = array( 'RAV4', '2021', 'Toyota RAV4 GX', '90000021', 'Repairable Write-off', '?', 'Red', 'Pinkenba, QLD', 'Tue 08/09 10:30AM' );
$r   = IC_Salvage_Normalise::from_manheim( $row, $ctx );
ic_is( $r['odometer_km'], null, 'unknown odometer' );
ic_is( $r['colour'], 'Red', 'colour still correct' );
ic_is( $r['location'], 'Pinkenba, QLD', 'location still correct' );
ic_is( $r['state'], 'QLD', 'state still correct' );

ic_test( 'valuation rows enrich by stock number and never blank a field' );
$row   = array_fill( 0, 23, '' );
$row[3]  = '90000001';
$row[8]  = 'Run And Drive';
$row[9]  = 'Yes';
$row[11] = 'Black';
$row[18] = 'GOOD';
$row[22] = '-';
$patch = IC_Salvage_Normalise::from_iaa_valuation( $row, $ctx );
ic_is( $patch['stock'], '90000001', 'keyed by stock' );
ic_is( $patch['source'], 'IAA', 'and by source' );
ic_is( $patch['start_code'], 'Run And Drive', 'start code carried' );
ic_is( $patch['keys_present'], true, 'keys normalised to a boolean' );
ic_is( $patch['colour'], 'Black', 'colour carried' );
ic_is( $patch['verdict'], 'GOOD', 'verdict carried' );
ic_ok( ! array_key_exists( 'est_repair', $patch ), 'an empty valuation cell is omitted, not written as blank' );
ic_ok( ! array_key_exists( 'high_pre_bid', $patch ), 'a "-" pre-bid is omitted rather than stored as "-"' );
ic_ok( ! array_key_exists( 'assessment', $patch ), 'an empty assessment is omitted' );

ic_test( 'helpers: unknown markers are absences, not values' );
ic_is( IC_Salvage_Normalise::km( '13,597 km' ), 13597, 'comma and unit' );
ic_is( IC_Salvage_Normalise::km( '?' ), null, 'question mark' );
ic_is( IC_Salvage_Normalise::km( '-' ), null, 'dash' );
ic_is( IC_Salvage_Normalise::km( 'unknown' ), null, 'free text' );
ic_is( IC_Salvage_Normalise::tri( 'Y' ), true, 'Y' );
ic_is( IC_Salvage_Normalise::tri( 'N' ), false, 'N' );
ic_is( IC_Salvage_Normalise::tri( '' ), null, 'blank is unknown, not no' );
ic_is( IC_Salvage_Normalise::tri( '?' ), null, 'question mark is unknown, not no' );
ic_is( IC_Salvage_Normalise::year( '2019' ), 2019, 'year' );
ic_is( IC_Salvage_Normalise::year( '19' ), null, 'two-digit year refused rather than expanded' );
ic_is( IC_Salvage_Normalise::year( '' ), null, 'blank year' );
ic_is( IC_Salvage_Normalise::s( '-' ), null, 'dash is an absence' );
ic_is( IC_Salvage_Normalise::s( '  x  ' ), 'x', 'trimmed' );

ic_test( 'make is derived only where the marque is actually known' );
ic_is( IC_Salvage_Normalise::make_for_model( 'RAV4' ), 'Toyota', 'RAV4' );
ic_is( IC_Salvage_Normalise::make_for_model( 'Prado' ), 'Toyota', 'Prado' );
ic_is( IC_Salvage_Normalise::make_for_model( 'LandCruiser' ), 'Toyota', 'LandCruiser' );
ic_is( IC_Salvage_Normalise::make_for_model( 'Ranger' ), 'Ford', 'Ranger' );
ic_is( IC_Salvage_Normalise::make_for_model( 'Patrol' ), null, 'an unmapped model gets no invented make' );
ic_is( IC_Salvage_Normalise::make_for_model( '' ), null, 'blank model' );

/* ============================================================
   THE THREE DESTINATION BOOKS, AS WRITTEN BY AN IMPORT

   books.php can be perfectly correct and still be dead code. These
   assertions exist because it WAS: the class was written, tested and
   never called by anything, so the board filtered on a single Kenya
   flag while three buyers' bands sat unused in a file.

   The context here is pinned to 2026 so the bands are the ones Rop
   set on 10 Sep 2026; the formulas themselves are proved to move in
   test_books.php.
   ============================================================ */

$ctx26 = IC_Salvage_Normalise::context( '2026-09-07', 2026 );

ic_test( 'import: a normalised row carries all three book columns and the year they were computed for' );
$r = IC_Salvage_Normalise::from_iaa_scrape(
	array( 'RAV4', '2021', 'RAV4 GXL', '90000201', 'Front', 'Repairable Write-Off', '93364', 'Y', 'Y', 'Y', 'SOUTH KEMPSEY, NSW', 'YES', '' ),
	$ctx26
);
foreach ( array( 'book_kenya', 'book_uganda', 'book_rental', 'book_flags', 'book_cy' ) as $k ) {
	ic_ok( array_key_exists( $k, $r ), 'record carries ' . $k );
}
ic_is( $r['book_cy'], 2026, 'book_cy records the year the bands were measured against' );

ic_test( 'import: a 2021 repairable goes to the Kenya book and to no other' );
ic_is( $r['book_kenya'], true, 'Kenya' );
ic_is( $r['book_uganda'], false, 'not Uganda' );
ic_is( $r['book_rental'], false, 'not rental' );

ic_test( 'import: a 2015 lot goes to Uganda, and carries the levy that decides the price' );
$u = IC_Salvage_Normalise::from_iaa_scrape(
	array( 'Prado', '2015', 'Prado GXL', '90000202', 'Front', 'Repairable Write-Off', '180000', 'Y', 'Y', 'Y', 'Laverton North, VIC', 'NO', '' ),
	$ctx26
);
ic_is( $u['book_uganda'], true, 'Uganda' );
ic_is( $u['book_kenya'], false, 'not Kenya' );
ic_is( $u['book_rental'], false, 'not rental' );
ic_ok( in_array( 'uganda_levy_50pct', $u['book_flags'], true ), '50% URA levy is on the row, not buried in a reason string' );

ic_test( 'import: a 2009 clean-title lot goes to the rental book, flagged unverifiable' );
$a = IC_Salvage_Normalise::from_iaa_scrape(
	array( 'Hilux', '2009', 'Hilux SR', '90000203', 'Front', 'No WOVR Record', '260000', 'Y', 'Y', 'Y', 'Laverton North, VIC', 'NO', '' ),
	$ctx26
);
ic_is( $a['book_rental'], true, 'rental' );
ic_is( $a['book_uganda'], false, 'not Uganda' );
ic_is( $a['book_kenya'], false, 'not Kenya' );
ic_ok( in_array( 'ppsr_mandatory', $a['book_flags'], true ), 'PPSR is mandatory on this band and the row says so' );

ic_test( 'import: a 2009 REPAIRABLE is NOT in the rental book — the band takes WOVR N/A only' );
$rep = IC_Salvage_Normalise::from_iaa_scrape(
	array( 'Hilux', '2009', 'Hilux SR', '90000204', 'Front', 'Repairable Write-Off', '260000', 'Y', 'Y', 'Y', 'Laverton North, VIC', 'NO', '' ),
	$ctx26
);
ic_is( $rep['book_rental'], false, 'rejected' );
ic_is( $rep['book_uganda'], false, 'and it is below the Uganda floor, so it has no buyer at all' );
ic_is( $rep['book_kenya'], false, 'nor Kenya' );

ic_test( 'import: no imported row is ever in two books' );
foreach ( range( 2005, 2027 ) as $y ) {
	foreach ( array( 'No WOVR Record', 'Repairable Write-Off', 'Statutory Write-Off' ) as $w ) {
		$row = IC_Salvage_Normalise::from_iaa_scrape(
			array( 'RAV4', (string) $y, 'RAV4 GXL', '9000' . $y, 'Front', $w, '100000', 'Y', 'Y', 'Y', 'Laverton North, VIC', 'NO', '' ),
			$ctx26
		);
		$n = 0;
		foreach ( array( 'book_kenya', 'book_uganda', 'book_rental' ) as $k ) {
			if ( true === $row[ $k ] ) { $n++; }
		}
		ic_ok( $n <= 1, sprintf( '%d %s is in at most one book', $y, $w ) );
	}
}

ic_test( 'import: Pickles publishes no damage, so its books are UNKNOWN, not "no"' );
// Pickles feeds carry no damage codes at all. Water cannot be ruled out, so
// Kenya and Uganda are undecided. Rendering that as "not eligible" would hide
// every Pickles lot from a buyer who could actually take it.
$p = IC_Salvage_Normalise::from_pickles(
	array( 'RAV4', '2021', 'GXL', '90000205', 'Repairable Write-Off', '80,000 km', 'Tullamarine, VIC', '-' ),
	$ctx26
);
ic_is( $p['book_kenya'], null, 'Kenya is unknown' );
ic_is( $p['book_uganda'], false, 'Uganda is a definite no — 2021 is above its ceiling, and that needs no damage data' );
ic_is( $p['book_rental'], false, 'rental is a definite no — 2021 is outside the band' );
