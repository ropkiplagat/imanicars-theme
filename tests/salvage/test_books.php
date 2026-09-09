<?php
/**
 * The three destination books.
 *
 * Every year window is tested across MULTIPLE calendar years. The brief states
 * Kenya as ">= 2019" and Uganda as ">= 2012"; both are right for 2026 and both
 * would be silently wrong in 2027. A test that only ever passes 2026 cannot
 * tell a formula from a literal.
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

ic_test( 'year windows are formulas that move with the calendar' );
ic_is( IC_Salvage_Books::kenya_floor( 2026 ), 2019, 'Kenya 2019 in 2026' );
ic_is( IC_Salvage_Books::kenya_floor( 2027 ), 2020, 'and 2020 in 2027' );
ic_is( IC_Salvage_Books::uganda_floor( 2026 ), 2012, 'Uganda clean floor 2012 in 2026' );
ic_is( IC_Salvage_Books::uganda_floor( 2027 ), 2013, 'and 2013 in 2027' );
ic_is( IC_Salvage_Books::uganda_boundary_year( 2026 ), 2011, 'contested year 2011 in 2026' );
ic_is( IC_Salvage_Books::uganda_boundary_year( 2027 ), 2012, 'and 2012 in 2027' );

ic_test( 'NONE-informative floors match the published state thresholds' );
ic_is( IC_Salvage_Books::none_informative_floor( 'QLD', 2026 ), 2010, 'QLD records 16 years or younger — uninformative 2009 and older' );
ic_is( IC_Salvage_Books::none_informative_floor( 'NSW', 2026 ), 2011, 'NSW up to 15 years' );
ic_is( IC_Salvage_Books::none_informative_floor( 'WA', 2026 ), 2011, 'WA within last 15 years' );
ic_is( IC_Salvage_Books::none_informative_floor( 'VIC', 2026 ), 2012, 'VIC less than 15 years — one year tighter than NSW' );
ic_is( IC_Salvage_Books::none_informative_floor( 'SA', 2026 ), null, 'no published threshold for SA' );
ic_ok( IC_Salvage_Books::none_informative_floor( 'NSW', 2026 ) !== IC_Salvage_Books::none_informative_floor( 'VIC', 2026 ), 'NSW and VIC differ by one because one is inclusive and the other is not' );

/* ---------------- Book 1 — Kenya ---------------- */

ic_test( 'Kenya: age window, both sides, across years' );
$dry = array( 'year' => 2019, 'wovr' => 'Repairable Write-Off', 'state' => 'NSW', 'damage' => 'Front' );
ic_is( IC_Salvage_Books::kenya( $dry, 2026 )['ok'], true, '2019 is inside in 2026' );
ic_is( IC_Salvage_Books::kenya( $dry, 2027 )['ok'], false, 'the same car is outside in 2027' );
$old = array( 'year' => 2018, 'wovr' => 'Repairable Write-Off', 'state' => 'NSW', 'damage' => 'Front' );
ic_is( IC_Salvage_Books::kenya( $old, 2026 )['ok'], false, '2018 is outside in 2026' );

ic_test( 'Kenya: statutory ships like repairable' );
$stat = array( 'year' => 2022, 'wovr' => 'Statutory Write-Off', 'state' => 'NSW', 'damage' => 'Front' );
ic_is( IC_Salvage_Books::kenya( $stat, 2026 )['ok'], true, 'statutory is not a Kenyan disqualifier' );

ic_test( 'Kenya: VIC statutory raises the EPA flag without changing eligibility' );
$vic = array( 'year' => 2022, 'wovr' => 'Statutory Write-Off', 'state' => 'VIC', 'damage' => 'Front' );
$r   = IC_Salvage_Books::kenya( $vic, 2026 );
ic_is( $r['ok'], true, 'still eligible' );
ic_ok( in_array( 'vic_epa_licence', $r['flags'], true ), 'and flagged for the licence' );
ic_ok( false !== stripos( implode( ' ', $r['reasons'] ), 'UNCONFIRMED' ), 'export-vs-dismantling stays marked unconfirmed' );

ic_test( 'Kenya: water beats everything, at any age' );
$flood = array( 'year' => 2025, 'wovr' => 'Statutory Write-Off', 'state' => 'VIC', 'damage' => 'All Over + Water' );
$r = IC_Salvage_Books::kenya( $flood, 2026 );
ic_is( $r['ok'], false, 'a brand-new flood car is rejected' );
ic_ok( in_array( 'water_reject', $r['flags'], true ), 'water flag raised' );

ic_test( 'Kenya: unknowns stay unknown' );
ic_is( IC_Salvage_Books::kenya( array( 'year' => null, 'wovr' => 'Repairable Write-Off', 'damage' => 'Front' ), 2026 )['ok'], null, 'no year' );
ic_is( IC_Salvage_Books::kenya( array( 'year' => 2025, 'wovr' => 'Repairable Write-Off' ), 2026 )['ok'], null, 'no damage published — Pickles case' );

/* ---------------- Book 2 — Uganda ---------------- */

ic_test( 'Uganda: the contested year is flagged, never auto-included' );
$b = IC_Salvage_Books::uganda( array( 'year' => 2011, 'damage' => 'Front' ), 2026 );
ic_is( $b['ok'], null, '2011 is neither admitted nor refused' );
ic_ok( in_array( 'uganda_boundary_2011', $b['flags'], true ), 'boundary flag names the year' );
$txt = implode( ' ', $b['reasons'] );
ic_ok( false !== stripos( $txt, 'under 15 years' ), 'the reason cites the guidance that excludes it' );
ic_ok( false !== stripos( $txt, 'levy band' ), 'and the levy band that admits it' );

ic_test( 'Uganda: either side of the boundary is decided' );
ic_is( IC_Salvage_Books::uganda( array( 'year' => 2012, 'damage' => 'Front' ), 2026 )['ok'], true, '2012 is clean' );
ic_is( IC_Salvage_Books::uganda( array( 'year' => 2010, 'damage' => 'Front' ), 2026 )['ok'], false, '2010 is refused' );

ic_test( 'Uganda: the boundary moves with the calendar' );
ic_is( IC_Salvage_Books::uganda( array( 'year' => 2012, 'damage' => 'Front' ), 2027 )['ok'], null, '2012 becomes the contested year in 2027' );
ic_is( IC_Salvage_Books::uganda( array( 'year' => 2011, 'damage' => 'Front' ), 2027 )['ok'], false, 'and 2011 is refused outright' );

ic_test( 'Uganda: the levy band is what actually matters' );
$mid = IC_Salvage_Books::uganda( array( 'year' => 2013, 'damage' => 'Front' ), 2026 );
ic_ok( in_array( 'uganda_levy_50pct', $mid['flags'], true ), '2013 is 13 years old — 50% levy' );
$eight = IC_Salvage_Books::uganda( array( 'year' => 2018, 'damage' => 'Front' ), 2026 );
ic_ok( in_array( 'uganda_levy_20pct', $eight['flags'], true ), '2018 is exactly 8 — 20% levy' );
$young = IC_Salvage_Books::uganda( array( 'year' => 2020, 'damage' => 'Front' ), 2026 );
ic_ok( in_array( 'uganda_levy_nil', $young['flags'], true ), '2020 is 6 — no levy' );
ic_ok( false !== stripos( implode( ' ', $mid['reasons'] ), 'not on what you paid' ), 'the levy is stated as on URA valuation, not the invoice' );

ic_test( 'Uganda: 2018 is the only unpunished year in the legal band' );
for ( $y = 2012; $y <= 2017; $y++ ) {
	$f = IC_Salvage_Books::uganda( array( 'year' => $y, 'damage' => 'Front' ), 2026 )['flags'];
	ic_ok( in_array( 'uganda_levy_50pct', $f, true ), sprintf( '%d carries the 50%% levy', $y ) );
}
ic_ok( in_array( 'uganda_levy_20pct', IC_Salvage_Books::uganda( array( 'year' => 2018, 'damage' => 'Front' ), 2026 )['flags'], true ), '2018 is the exception' );

ic_test( 'Uganda: water is rejected here too' );
ic_is( IC_Salvage_Books::uganda( array( 'year' => 2020, 'damage' => 'Front + Water' ), 2026 )['ok'], false, 'PVoC applies to Uganda' );

ic_test( 'Uganda: duty basis is URA\'s database, not the hammer' );
ic_is( IC_Salvage_Books::uganda( array( 'year' => 2020, 'damage' => 'Front' ), 2026 )['duty_basis'], 'ura_database', 'duty basis' );

/* ---------------- Book 3 — Australian rental ---------------- */

ic_test( 'AU rental: statutory is never registrable, in any state' );
foreach ( array( 'VIC', 'NSW', 'QLD', 'WA', 'SA' ) as $st ) {
	ic_is( IC_Salvage_Books::au_rental( array( 'year' => 2022, 'wovr' => 'Statutory Write-Off', 'state' => $st ), 2026 )['ok'], false, 'STAT rejected in ' . $st );
}

ic_test( 'AU rental: the expensive correction — old REP is still on the register' );
$old_rep = array( 'year' => 2008, 'wovr' => 'Repairable Write-Off', 'state' => 'QLD' );
$r = IC_Salvage_Books::au_rental( $old_rep, 2026 );
ic_is( $r['ok'], true, 'a 2008 QLD repairable is buyable' );
ic_ok( in_array( 'needs_wovi', $r['flags'], true ), 'but it STILL needs the WOVI — age never removes it' );
ic_ok( ! in_array( 'history_unverified', $r['flags'], true ), 'and it is not treated as an unrecorded car' );

ic_test( 'AU rental: NONE is the book, and it needs no inspection' );
$none = IC_Salvage_Books::au_rental( array( 'year' => 2015, 'wovr' => 'No WOVR Record', 'state' => 'VIC' ), 2026 );
ic_is( $none['ok'], true, 'eligible' );
ic_ok( ! in_array( 'needs_viv', $none['flags'], true ), 'no VIV' );
ic_ok( ! in_array( 'needs_wovi', $none['flags'], true ), 'no WOVI' );

ic_test( 'AU rental: year is a confidence flag on NONE only' );
$stale = IC_Salvage_Books::au_rental( array( 'year' => 2009, 'wovr' => 'No WOVR Record', 'state' => 'VIC' ), 2026 );
ic_is( $stale['ok'], true, 'still eligible — the flag does not disqualify' );
ic_ok( in_array( 'history_unverified', $stale['flags'], true ), 'VIC stopped recording above this age, so a clean reading proves nothing' );
$fresh = IC_Salvage_Books::au_rental( array( 'year' => 2013, 'wovr' => 'No WOVR Record', 'state' => 'VIC' ), 2026 );
ic_ok( ! in_array( 'history_unverified', $fresh['flags'], true ), '2013 VIC is inside the recording window, so the reading is trustworthy' );

ic_test( 'AU rental: QLD and VIC thresholds are one year apart and both bite' );
ic_ok( in_array( 'history_unverified', IC_Salvage_Books::au_rental( array( 'year' => 2009, 'wovr' => 'No WOVR Record', 'state' => 'QLD' ), 2026 )['flags'], true ), 'QLD 2009 is uninformative' );
ic_ok( ! in_array( 'history_unverified', IC_Salvage_Books::au_rental( array( 'year' => 2010, 'wovr' => 'No WOVR Record', 'state' => 'QLD' ), 2026 )['flags'], true ), 'QLD 2010 is informative' );
ic_ok( in_array( 'history_unverified', IC_Salvage_Books::au_rental( array( 'year' => 2011, 'wovr' => 'No WOVR Record', 'state' => 'VIC' ), 2026 )['flags'], true ), 'VIC 2011 is uninformative' );

ic_test( 'AU rental: NSW repairable is out for a trade buyer' );
$nsw = IC_Salvage_Books::au_rental( array( 'year' => 2022, 'wovr' => 'Repairable Write-Off', 'state' => 'NSW' ), 2026 );
ic_is( $nsw['ok'], false, 'rejected' );
ic_ok( in_array( 'nsw_not_registrable', $nsw['flags'], true ), 'flagged' );
ic_is( IC_Salvage_Books::au_rental( array( 'year' => 2022, 'wovr' => 'Inspection Passed Repairable Write-Off', 'state' => 'NSW' ), 2026 )['ok'], false, 'INSP does not rescue NSW' );

ic_test( 'AU rental: INSP elsewhere is the best buy in the pool' );
$insp = IC_Salvage_Books::au_rental( array( 'year' => 2020, 'wovr' => 'Inspection Passed Repairable Write-Off', 'state' => 'VIC' ), 2026 );
ic_is( $insp['ok'], true, 'eligible' );
ic_ok( in_array( 'inspection_passed', $insp['flags'], true ), 'marked as already inspected' );

ic_test( 'AU rental: states with no published rule are unknown, not guessed' );
$sa = IC_Salvage_Books::au_rental( array( 'year' => 2020, 'wovr' => 'Repairable Write-Off', 'state' => 'SA' ), 2026 );
ic_is( $sa['ok'], null, 'SA is not screened' );
ic_ok( in_array( 'state_rule_unknown', $sa['flags'], true ), 'and says so' );

ic_test( 'AU rental: an unpublished WOVR cannot be screened' );
ic_is( IC_Salvage_Books::au_rental( array( 'year' => 2020, 'wovr' => '', 'state' => 'VIC' ), 2026 )['ok'], null, 'null WOVR is unknown, never eligible' );

/* ---------------- Cross-book ---------------- */

ic_test( 'books 2 and 3 are NOT disjoint below 2019' );
$overlap = IC_Salvage_Books::assess( array( 'source' => 'Pickles', 'stock' => '90000001', 'year' => 2015,
	'wovr' => 'Repairable Write-Off', 'state' => 'VIC', 'damage' => 'Front' ), 2026 );
ic_is( $overlap['kenya_ok'], false, '2015 is outside Kenya' );
ic_is( $overlap['uganda_ok'], true, 'but inside Uganda' );
ic_is( $overlap['au_rental_ok'], true, 'and buyable for the AU rental fleet' );
ic_ok( true === $overlap['uganda_ok'] && true === $overlap['au_rental_ok'],
	'a 2015 VIC repairable is true in TWO books below 2019 — the brief claims the books are disjoint there, and they are not' );

ic_test( 'all three books can be true together at 2019+' );
$all = IC_Salvage_Books::assess( array( 'year' => 2020, 'wovr' => 'No WOVR Record', 'state' => 'VIC', 'damage' => 'Front' ), 2026 );
ic_is( $all['kenya_ok'], true, 'Kenya' );
ic_is( $all['uganda_ok'], true, 'Uganda' );
ic_is( $all['au_rental_ok'], true, 'AU rental' );

ic_test( 'assess() emits the agreed row shape' );
foreach ( array( 'source','stock','year','model','wovr','state','sale_date',
	'kenya_ok','uganda_ok','au_rental_ok','flags','duty_basis' ) as $k ) {
	ic_ok( array_key_exists( $k, $all ), 'row carries ' . $k );
}
ic_is( $all['wovr'], 'NONE', 'wovr is emitted as the code' );
ic_is( $all['duty_basis'], 'crsp', 'Kenya-eligible rows use CRSP' );
ic_is( IC_Salvage_Books::assess( array( 'year' => 2015, 'wovr' => 'No WOVR Record', 'state' => 'VIC', 'damage' => 'Front' ), 2026 )['duty_basis'], 'ura_database', 'Uganda-only rows use the URA database' );

ic_test( 'a statutory-only Manheim lane, scored' );
// The shape of a real Manheim sale day: three VIC statutory and one NSW
// statutory, no damage codes published. Stock numbers are SYNTHETIC — real
// ones are proprietary and this repository is public.
$lane = array(
	array( 'stock' => '90000101', 'year' => 2025, 'wovr' => 'Statutory Write-Off', 'state' => 'VIC' ),
	array( 'stock' => '90000102', 'year' => 2022, 'wovr' => 'Statutory Write-Off', 'state' => 'VIC' ),
	array( 'stock' => '90000103', 'year' => 2019, 'wovr' => 'Statutory Write-Off', 'state' => 'VIC' ),
	array( 'stock' => '90000104', 'year' => 2020, 'wovr' => 'Statutory Write-Off', 'state' => 'NSW' ),
);
$epa = 0;
foreach ( $lane as $l ) {
	$a = IC_Salvage_Books::assess( $l, 2026 );
	ic_is( $a['au_rental_ok'], false, $l['stock'] . ' is statutory — out of the rental book' );
	ic_is( $a['kenya_ok'], null, $l['stock'] . ' Kenya is UNKNOWN — Manheim publishes no damage codes' );
	if ( in_array( 'vic_epa_licence', $a['flags'], true ) ) { $epa++; }
}
ic_is( $epa, 3, 'three of the four need a Victorian EPA licence to bid' );
