<?php
/**
 * The three destination books.
 *
 * Bands set by Rop on 10 Sep 2026 and deliberately DISJOINT — three different
 * buyers, and an overlap means two of them bid against each other and inflate a
 * price they both pay:
 *
 *   Imani Car Rentals  2008 - 2010   WOVR N/A only
 *   Uganda             2011 - 2018
 *   Kenya              2019 +
 *
 * Every window is tested across MULTIPLE calendar years. A test written in the
 * year a literal was hardcoded cannot tell a formula from a literal.
 */

ic_test( 'WOVR coding: INSP is tested before REP, or it is lost' );
ic_is( IC_Salvage_Books::wovr_code( 'Inspection Passed Repairable Write-Off' ), 'INSP', 'the string contains "Repairable" and must still code INSP' );
ic_is( IC_Salvage_Books::wovr_code( 'Repairable Write-Off' ), 'REP', 'plain repairable' );
ic_is( IC_Salvage_Books::wovr_code( 'Statutory Write-Off' ), 'STAT', 'statutory' );
ic_is( IC_Salvage_Books::wovr_code( 'Statutory Write-off' ), 'STAT', 'case varies between houses' );
ic_is( IC_Salvage_Books::wovr_code( 'No WOVR Record' ), 'NONE', 'IAA wording' );
ic_is( IC_Salvage_Books::wovr_code( 'WOVR N/A' ), 'NONE', 'Pickles wording for the same thing' );
ic_is( IC_Salvage_Books::wovr_code( '' ), null, 'nothing published is null, not NONE' );
ic_is( IC_Salvage_Books::wovr_code( null ), null, 'null in, null out' );

ic_test( 'an unpublished WOVR is NOT the same as "No WOVR Record"' );
ic_ok( IC_Salvage_Books::wovr_code( '' ) !== IC_Salvage_Books::NONE, 'blank must never collapse into NONE — NONE is the whole of book 3' );

/* ---------------- Band arithmetic ---------------- */

ic_test( 'all six band edges are formulas that move with the calendar' );
ic_is( IC_Salvage_Books::rental_floor( 2026 ), 2008, 'rental opens 2008 in 2026' );
ic_is( IC_Salvage_Books::rental_ceiling( 2026 ), 2010, 'rental closes 2010 in 2026' );
ic_is( IC_Salvage_Books::uganda_floor( 2026 ), 2011, 'Uganda opens 2011 in 2026' );
ic_is( IC_Salvage_Books::uganda_ceiling( 2026 ), 2018, 'Uganda closes 2018 in 2026' );
ic_is( IC_Salvage_Books::kenya_floor( 2026 ), 2019, 'Kenya opens 2019 in 2026' );

ic_is( IC_Salvage_Books::rental_floor( 2027 ), 2009, 'and every edge moves in 2027' );
ic_is( IC_Salvage_Books::rental_ceiling( 2027 ), 2011, 'rental ceiling' );
ic_is( IC_Salvage_Books::uganda_floor( 2027 ), 2012, 'Uganda floor' );
ic_is( IC_Salvage_Books::uganda_ceiling( 2027 ), 2019, 'Uganda ceiling' );
ic_is( IC_Salvage_Books::kenya_floor( 2027 ), 2020, 'Kenya floor' );

ic_test( 'the three bands are DISJOINT and leave no gap, in any year' );
foreach ( array( 2026, 2027, 2030 ) as $cy ) {
	$rc = IC_Salvage_Books::rental_ceiling( $cy );
	$uf = IC_Salvage_Books::uganda_floor( $cy );
	$uc = IC_Salvage_Books::uganda_ceiling( $cy );
	$kf = IC_Salvage_Books::kenya_floor( $cy );
	ic_is( $uf, $rc + 1, sprintf( '%d: Uganda starts the year after rental ends', $cy ) );
	ic_is( $kf, $uc + 1, sprintf( '%d: Kenya starts the year after Uganda ends', $cy ) );
}

ic_test( 'NONE-informative floors match the published state thresholds' );
ic_is( IC_Salvage_Books::none_informative_floor( 'QLD', 2026 ), 2010, 'QLD records 16 years or younger' );
ic_is( IC_Salvage_Books::none_informative_floor( 'NSW', 2026 ), 2011, 'NSW up to 15 years' );
ic_is( IC_Salvage_Books::none_informative_floor( 'WA', 2026 ), 2011, 'WA within last 15 years' );
ic_is( IC_Salvage_Books::none_informative_floor( 'VIC', 2026 ), 2012, 'VIC less than 15 years — one year tighter than NSW' );
ic_is( IC_Salvage_Books::none_informative_floor( 'SA', 2026 ), null, 'no published threshold for SA' );

/* ---------------- Book 1 — Kenya ---------------- */

ic_test( 'Kenya: age window, both sides, across years' );
$dry = array( 'year' => 2019, 'wovr' => 'Repairable Write-Off', 'state' => 'NSW', 'damage' => 'Front' );
ic_is( IC_Salvage_Books::kenya( $dry, 2026 )['ok'], true, '2019 is inside in 2026 — the boundary case' );
ic_is( IC_Salvage_Books::kenya( $dry, 2027 )['ok'], false, 'the same car is outside in 2027' );
ic_is( IC_Salvage_Books::kenya( array( 'year' => 2018, 'wovr' => 'Repairable Write-Off', 'damage' => 'Front' ), 2026 )['ok'], false, '2018 is not' );

ic_test( 'Kenya: statutory ships like repairable' );
ic_is( IC_Salvage_Books::kenya( array( 'year' => 2022, 'wovr' => 'Statutory Write-Off', 'state' => 'NSW', 'damage' => 'Front' ), 2026 )['ok'], true, 'statutory is not a Kenyan disqualifier' );

ic_test( 'Kenya: VIC statutory raises the EPA flag without changing eligibility' );
$r = IC_Salvage_Books::kenya( array( 'year' => 2022, 'wovr' => 'Statutory Write-Off', 'state' => 'VIC', 'damage' => 'Front' ), 2026 );
ic_is( $r['ok'], true, 'still eligible' );
ic_ok( in_array( 'vic_epa_licence', $r['flags'], true ), 'and flagged for the licence' );
ic_ok( false !== stripos( implode( ' ', $r['reasons'] ), 'UNCONFIRMED' ), 'export-vs-dismantling stays marked unconfirmed' );

ic_test( 'Kenya: water beats everything, at any age' );
$r = IC_Salvage_Books::kenya( array( 'year' => 2025, 'wovr' => 'Statutory Write-Off', 'state' => 'VIC', 'damage' => 'All Over + Water' ), 2026 );
ic_is( $r['ok'], false, 'a brand-new flood car is rejected' );
ic_ok( in_array( 'water_reject', $r['flags'], true ), 'water flag raised' );

ic_test( 'Kenya: unknowns stay unknown' );
ic_is( IC_Salvage_Books::kenya( array( 'year' => null, 'wovr' => 'Repairable Write-Off', 'damage' => 'Front' ), 2026 )['ok'], null, 'no year' );
ic_is( IC_Salvage_Books::kenya( array( 'year' => 2025, 'wovr' => 'Repairable Write-Off' ), 2026 )['ok'], null, 'no damage published — the Pickles case' );

/* ---------------- Book 2 — Uganda ---------------- */

ic_test( 'Uganda: the band is closed at BOTH ends' );
ic_is( IC_Salvage_Books::uganda( array( 'year' => 2010, 'damage' => 'Front' ), 2026 )['ok'], false, '2010 is below the floor' );
ic_is( IC_Salvage_Books::uganda( array( 'year' => 2011, 'damage' => 'Front' ), 2026 )['ok'], true, '2011 opens the band' );
ic_is( IC_Salvage_Books::uganda( array( 'year' => 2018, 'damage' => 'Front' ), 2026 )['ok'], true, '2018 closes it' );
ic_is( IC_Salvage_Books::uganda( array( 'year' => 2019, 'damage' => 'Front' ), 2026 )['ok'], false, '2019 belongs to Kenya, not Uganda' );

ic_test( 'Uganda: rejecting a 2019 says WHY, so it is not read as ineligible for export' );
$r = IC_Salvage_Books::uganda( array( 'year' => 2020, 'damage' => 'Front' ), 2026 );
ic_ok( false !== stripos( implode( ' ', $r['reasons'] ), 'Kenya-book' ), 'the reason names the other book rather than implying the car is unimportable' );

ic_test( 'Uganda: 2011 is eligible by decision but stays flagged as contested' );
$b = IC_Salvage_Books::uganda( array( 'year' => 2011, 'damage' => 'Front' ), 2026 );
ic_is( $b['ok'], true, 'admitted — Rop assigned 2011 to Uganda' );
ic_ok( in_array( 'uganda_boundary_contested_2011', $b['flags'], true ), 'but the contested flag survives the decision' );
$txt = implode( ' ', $b['reasons'] );
ic_ok( false !== stripos( $txt, 'under 15 years' ), 'the reason cites the guidance that excludes it' );
ic_ok( false !== stripos( $txt, 'levy band' ), 'and the levy band that admits it' );
ic_ok( false !== stripos( $txt, 'by decision, not by resolution' ), 'and is explicit that a choice was made, not a finding' );

ic_test( 'Uganda: the band moves with the calendar' );
ic_is( IC_Salvage_Books::uganda( array( 'year' => 2011, 'damage' => 'Front' ), 2027 )['ok'], false, '2011 drops out in 2027' );
ic_is( IC_Salvage_Books::uganda( array( 'year' => 2019, 'damage' => 'Front' ), 2027 )['ok'], true, 'and 2019 becomes the ceiling' );

ic_test( 'Uganda: the levy band is what actually matters' );
foreach ( range( 2011, 2017 ) as $y ) {
	ic_ok( in_array( 'uganda_levy_50pct', IC_Salvage_Books::uganda( array( 'year' => $y, 'damage' => 'Front' ), 2026 )['flags'], true ),
		sprintf( '%d carries the 50%% levy', $y ) );
}
ic_ok( in_array( 'uganda_levy_20pct', IC_Salvage_Books::uganda( array( 'year' => 2018, 'damage' => 'Front' ), 2026 )['flags'], true ),
	'2018 is the only year in the band at 20% rather than 50% — the one worth buying' );

ic_test( 'Uganda: water is rejected here too, and duty is on URA\'s database' );
ic_is( IC_Salvage_Books::uganda( array( 'year' => 2015, 'damage' => 'Front + Water' ), 2026 )['ok'], false, 'PVoC applies to Uganda' );
ic_is( IC_Salvage_Books::uganda( array( 'year' => 2015, 'damage' => 'Front' ), 2026 )['duty_basis'], 'ura_database', 'not the invoice' );

/* ---------------- Book 3 — Australian rental ---------------- */

ic_test( 'AU rental: closed band 2008-2010, both edges' );
$none = function ( $y, $st = 'VIC' ) { return array( 'year' => $y, 'wovr' => 'No WOVR Record', 'state' => $st ); };
ic_is( IC_Salvage_Books::au_rental( $none( 2007 ), 2026 )['ok'], false, '2007 is below the floor' );
ic_is( IC_Salvage_Books::au_rental( $none( 2008 ), 2026 )['ok'], true, '2008 opens the band' );
ic_is( IC_Salvage_Books::au_rental( $none( 2010 ), 2026 )['ok'], true, '2010 closes it' );
ic_is( IC_Salvage_Books::au_rental( $none( 2011 ), 2026 )['ok'], false, '2011 belongs to Uganda now' );

ic_test( 'AU rental: WOVR N/A ONLY — repairable no longer qualifies' );
ic_is( IC_Salvage_Books::au_rental( array( 'year' => 2009, 'wovr' => 'Repairable Write-Off', 'state' => 'VIC' ), 2026 )['ok'], false, 'a repairable in the band is OUT' );
ic_is( IC_Salvage_Books::au_rental( array( 'year' => 2009, 'wovr' => 'Inspection Passed Repairable Write-Off', 'state' => 'QLD' ), 2026 )['ok'], false, 'so is an inspection-passed repairable' );
ic_is( IC_Salvage_Books::au_rental( array( 'year' => 2009, 'wovr' => 'Statutory Write-Off', 'state' => 'VIC' ), 2026 )['ok'], false, 'statutory never registrable' );
$r = IC_Salvage_Books::au_rental( array( 'year' => 2009, 'wovr' => 'Repairable Write-Off', 'state' => 'VIC' ), 2026 );
ic_ok( false !== stripos( implode( ' ', $r['reasons'] ), 'age never removes it' ), 'and the reason says why age does not help a register entry' );

ic_test( 'AU rental: PPSR is mandatory on EVERY lot in this band, not advisable' );
// 2008-2010 sits below every state's recording threshold, so a clean reading
// proves nothing anywhere. That is the defining property of the band.
foreach ( array( 'VIC', 'NSW', 'QLD', 'WA' ) as $st ) {
	foreach ( array( 2008, 2009, 2010 ) as $y ) {
		$f = IC_Salvage_Books::au_rental( $none( $y, $st ), 2026 )['flags'];
		ic_ok( in_array( 'history_unverified', $f, true ) && in_array( 'ppsr_mandatory', $f, true ),
			sprintf( '%s %d is eligible but unverifiable', $st, $y ) );
	}
}

ic_test( 'AU rental: statutory is never registrable, in any state' );
foreach ( array( 'VIC', 'NSW', 'QLD', 'WA', 'SA' ) as $st ) {
	ic_is( IC_Salvage_Books::au_rental( array( 'year' => 2009, 'wovr' => 'Statutory Write-Off', 'state' => $st ), 2026 )['ok'], false, 'STAT rejected in ' . $st );
}

ic_test( 'AU rental: unknowns stay unknown' );
ic_is( IC_Salvage_Books::au_rental( array( 'year' => 2009, 'wovr' => '', 'state' => 'VIC' ), 2026 )['ok'], null, 'no WOVR published' );
ic_is( IC_Salvage_Books::au_rental( array( 'year' => null, 'wovr' => 'No WOVR Record', 'state' => 'VIC' ), 2026 )['ok'], null, 'no year published' );

ic_test( 'AU rental: water is rejected on fleet-safety grounds, and says so' );
$w = IC_Salvage_Books::au_rental( array( 'year' => 2009, 'wovr' => 'No WOVR Record', 'state' => 'VIC', 'damage' => 'Front + Water' ), 2026 );
ic_is( $w['ok'], false, 'rejected' );
ic_ok( false !== stripos( implode( ' ', $w['reasons'] ), 'not on a cited regulation' ), 'flagged as a judgement, not a regulation' );

/* ---------------- Cross-book ---------------- */

ic_test( 'NO lot can be true in two books — that is the whole point of the split' );
$cases = array();
foreach ( range( 2005, 2028 ) as $y ) {
	foreach ( array( 'No WOVR Record', 'Repairable Write-Off', 'Statutory Write-Off' ) as $w ) {
		$cases[] = array( 'year' => $y, 'wovr' => $w, 'state' => 'VIC', 'damage' => 'Front' );
	}
}
$collisions = 0;
foreach ( $cases as $c ) {
	$a = IC_Salvage_Books::assess( $c, 2026 );
	$true_in = 0;
	foreach ( array( 'kenya_ok', 'uganda_ok', 'au_rental_ok' ) as $k ) {
		if ( true === $a[ $k ] ) { $true_in++; }
	}
	if ( $true_in > 1 ) { $collisions++; }
}
ic_is( $collisions, 0, sprintf( 'across %d year/WOVR combinations, no lot is eligible in two books at once', count( $cases ) ) );

ic_test( 'the 2011 that used to collide now belongs to exactly one book' );
$a = IC_Salvage_Books::assess( array( 'year' => 2011, 'wovr' => 'No WOVR Record', 'state' => 'VIC', 'damage' => 'Front' ), 2026 );
ic_is( $a['uganda_ok'], true, 'Uganda takes it' );
ic_is( $a['au_rental_ok'], false, 'rental does not' );
ic_is( $a['kenya_ok'], false, 'nor Kenya' );

ic_test( 'assess() emits the agreed row shape' );
$all = IC_Salvage_Books::assess( array( 'year' => 2020, 'wovr' => 'No WOVR Record', 'state' => 'VIC', 'damage' => 'Front' ), 2026 );
foreach ( array( 'source','stock','year','model','wovr','state','sale_date',
	'kenya_ok','uganda_ok','au_rental_ok','flags','duty_basis' ) as $k ) {
	ic_ok( array_key_exists( $k, $all ), 'row carries ' . $k );
}
ic_is( $all['wovr'], 'NONE', 'wovr is emitted as the code' );
ic_is( $all['duty_basis'], 'crsp', 'Kenya-eligible rows use CRSP' );
ic_is( IC_Salvage_Books::assess( array( 'year' => 2015, 'wovr' => 'No WOVR Record', 'state' => 'VIC', 'damage' => 'Front' ), 2026 )['duty_basis'], 'ura_database', 'Uganda rows use the URA database' );

ic_test( 'there is no V8 Prado — a row claiming one is flagged as a mis-scrape' );
ic_ok( null !== IC_Salvage_Rules::drivetrain_conflict( 'Prado', '4.5 V8 Turbo Diesel' ), 'V8 Prado flagged' );
ic_ok( null !== IC_Salvage_Rules::drivetrain_conflict( 'Prado', '4.5L' ), '4.5L Prado flagged' );
ic_is( IC_Salvage_Rules::drivetrain_conflict( 'Prado', '2.8 GXL Turbo Diesel' ), null, 'the real 2.8 Prado is fine' );
ic_is( IC_Salvage_Rules::drivetrain_conflict( 'LandCruiser', '4.5 V8 Turbo Diesel' ), null, 'the 4.5 V8 belongs to the LandCruiser' );

ic_test( 'a statutory-only lane scores out of every book that needs registration' );
// The real 10 Sep Manheim shape. Stock numbers SYNTHETIC — real ones are
// proprietary and this repository is public.
$lane = array(
	array( 'stock' => '90000101', 'year' => 2025, 'wovr' => 'Statutory Write-Off', 'state' => 'VIC' ),
	array( 'stock' => '90000102', 'year' => 2022, 'wovr' => 'Statutory Write-Off', 'state' => 'VIC' ),
	array( 'stock' => '90000103', 'year' => 2019, 'wovr' => 'Statutory Write-Off', 'state' => 'VIC' ),
	array( 'stock' => '90000104', 'year' => 2020, 'wovr' => 'Statutory Write-Off', 'state' => 'NSW' ),
);
$epa = 0;
foreach ( $lane as $l ) {
	$a = IC_Salvage_Books::assess( $l, 2026 );
	ic_is( $a['au_rental_ok'], false, $l['stock'] . ' is statutory and out of band — out of the rental book twice over' );
	ic_is( $a['kenya_ok'], null, $l['stock'] . ' Kenya is UNKNOWN — Manheim publishes no damage codes' );
	if ( in_array( 'vic_epa_licence', $a['flags'], true ) ) { $epa++; }
}
ic_is( $epa, 3, 'three of the four need a Victorian EPA licence to bid' );
