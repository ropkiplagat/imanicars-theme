<?php
/**
 * Buyer fee engine.
 *
 * The two IAA/Pickles figures below are the acceptance numbers from the build
 * brief. They are asserted exactly, in cents, because a fee that is nearly right
 * is a bid ceiling that is wrong.
 */

ic_test( 'fees: IAA $8,500 hammer' );
$q = IC_Salvage_Fees::quote( 'IAA', 850000 );
ic_is( $q['buyer_fee'], 140575, 'IAA buyer fee on $8,500 is $1,405.75' );
ic_is( IC_Salvage_Money::format( $q['buyer_fee'] ), '$1,405.75', 'formats as $1,405.75' );
ic_is( $q['landed_before_duty'], 990575, 'landed before duty = hammer + fee' );
ic_is( $q['partial'], false, 'not a partial quote above the flat-fee threshold' );
ic_ok( null === $q['unavailable_reason'], 'IAA fee is available' );

ic_test( 'fees: Pickles $8,500 hammer' );
$q = IC_Salvage_Fees::quote( 'Pickles', 850000 );
ic_is( $q['buyer_fee'], 170700, 'Pickles buyer fee on $8,500 is $1,707.00 (incl. export admin + transfer)' );
ic_is( IC_Salvage_Money::format( $q['buyer_fee'] ), '$1,707.00', 'formats as $1,707.00' );
ic_is( $q['landed_before_duty'], 1020700, 'landed before duty = hammer + fee' );
ic_is( count( $q['components'] ), 4, 'premium + flat + export admin + transfer' );

ic_test( 'fees: Manheim $8,500 hammer — schedule retrieved 10 Sep 2026' );
// Buyer-Auction-Fees-Aug26.pdf, salvage table, effective 1 Aug 2026.
// The PDF only ever 403'd to a bare fetch; a browser User-Agent gets HTTP 200.
$q = IC_Salvage_Fees::quote( 'Manheim', 850000 );
ic_is( $q['known'], true, 'Manheim is no longer unknown' );
ic_is( $q['buyer_fee'], 139500, 'Manheim buyer fee on $8,500 is $1,395.00 ($120 + 15%)' );
ic_is( IC_Salvage_Money::format( $q['buyer_fee'] ), '$1,395.00', 'formats as $1,395.00' );
ic_is( $q['landed_before_duty'], 989500, 'landed before duty' );
ic_ok( null === $q['unavailable_reason'], 'no reason needed — the number is known' );

ic_test( 'fees: Manheim is the cheapest of the three at $8,500' );
$iaa = IC_Salvage_Fees::quote( 'IAA', 850000 )['buyer_fee'];
$pic = IC_Salvage_Fees::quote( 'Pickles', 850000 )['buyer_fee'];
$man = IC_Salvage_Fees::quote( 'Manheim', 850000 )['buyer_fee'];
ic_ok( $man < $iaa && $iaa < $pic, sprintf( 'Manheim %d < IAA %d < Pickles %d', $man, $iaa, $pic ) );

ic_test( 'fees: banded schedules below $1,000 charge NO percentage' );
// Manheim salvage: $0-200 $121 · $201-500 $148 · $501-1,000 $220
$q = IC_Salvage_Fees::quote( 'Manheim', 20000 );
ic_is( $q['buyer_fee'], 12100, '$200 hammer pays the $121 band' );
ic_is( $q['banded'], true, 'and is marked banded' );
ic_is( $q['partial'], false, 'nothing is missing — the band IS published' );
ic_is( IC_Salvage_Fees::quote( 'Manheim', 20001 )['buyer_fee'], 14800, '$200.01 moves to the $148 band' );
ic_is( IC_Salvage_Fees::quote( 'Manheim', 50000 )['buyer_fee'], 14800, '$500 is still $148' );
ic_is( IC_Salvage_Fees::quote( 'Manheim', 50001 )['buyer_fee'], 22000, '$500.01 moves to $220' );
ic_is( IC_Salvage_Fees::quote( 'Manheim', 100000 )['buyer_fee'], 22000, '$1,000 exactly is still $220' );
ic_is( IC_Salvage_Fees::quote( 'Manheim', 100001 )['buyer_fee'], 27000, '$1,000.01 switches to $120 + 15% = $270.00' );

ic_test( 'fees: the band-to-percentage transition is a real cliff, not a ramp' );
// One cent over $1,000 costs $50 more in fees. That is the published schedule,
// not a rounding artefact — bidding $1,000 and bidding $1,000.01 are different
// decisions, and the board must not smooth it away.
$at    = IC_Salvage_Fees::quote( 'Manheim', 100000 )['buyer_fee'];
$over  = IC_Salvage_Fees::quote( 'Manheim', 100001 )['buyer_fee'];
ic_is( $over - $at, 5000, 'a one-cent bid increase raises the fee by $50.00' );
ic_ok( $over > $at, 'and the cliff goes upward, so it can never be arbitraged downward' );

ic_test( 'fees: a banded fee is never the percentage in disguise' );
// 15% of $200 would be $30. The published band is $121 — four times more.
ic_ok( IC_Salvage_Fees::quote( 'Manheim', 20000 )['buyer_fee'] !== IC_Salvage_Fees::pct_of( 20000, 1500 ), 'the band is not a percentage' );

ic_test( 'fees: Pickles bands, verified on its own fee page' );
ic_is( IC_Salvage_Fees::quote( 'Pickles', 9999 )['buyer_fee'], 3300 + 23600 + 3600, '$99.99 pays the $33 band plus admin and transfer' );
ic_is( IC_Salvage_Fees::quote( 'Pickles', 49999 )['buyer_fee'], 14000 + 23600 + 3600, '$499.99 pays $140' );
ic_is( IC_Salvage_Fees::quote( 'Pickles', 99999 )['buyer_fee'], 24500 + 23600 + 3600, '$999.99 pays $245' );
ic_is( IC_Salvage_Fees::quote( 'Pickles', 9999 )['partial'], false, 'no longer partial — the bands are published' );

ic_test( 'fees: IAA alone still publishes nothing below $1,000' );
$q = IC_Salvage_Fees::quote( 'IAA', 100000 ); // exactly $1,000 — not OVER $1,000
ic_is( $q['partial'], true, 'quote is marked partial at exactly the threshold' );
ic_is( $q['buyer_fee'], 14950, 'only the premium is charged, flat is not assumed' );
ic_is( $q['banded'], false, 'IAA has no bands to fall back on' );
ic_ok( ! empty( $q['caveats'] ), 'a caveat explains what is missing' );

ic_test( 'fees: the export admin fee says it is per invoice' );
$all = implode( ' ', IC_Salvage_Fees::quote( 'Pickles', 850000 )['caveats'] );
ic_ok( false !== stripos( $all, 'per INVOICE' ), 'Pickles names the per-invoice basis' );
$man_c = implode( ' ', IC_Salvage_Fees::quote( 'Manheim', 850000 )['caveats'] );
ic_ok( false === stripos( $man_c, 'export admin' ), 'Manheim publishes no export admin fee, so none is claimed' );

$q = IC_Salvage_Fees::quote( 'IAA', 100001 ); // one cent over
ic_is( $q['partial'], false, 'one cent over the threshold is a complete quote' );
ic_is( $q['buyer_fee'], 28450, 'premium + $135 flat' );

ic_test( 'fees: duty is never implied to be included' );
$q   = IC_Salvage_Fees::quote( 'IAA', 850000 );
$all = implode( ' ', $q['caveats'] );
ic_ok( false !== stripos( $all, 'CRSP' ), 'the CRSP duty basis is stated next to the figure' );
ic_ok( false !== stripos( $all, 'before duty' ), 'the figure is explicitly labelled before duty' );

ic_test( 'fees: bad input never produces a plausible number' );
ic_is( IC_Salvage_Fees::quote( 'IAA', null )['buyer_fee'], null, 'no hammer, no fee' );
ic_is( IC_Salvage_Fees::quote( 'IAA', -100 )['buyer_fee'], null, 'negative hammer rejected' );
ic_is( IC_Salvage_Fees::quote( 'Copart', 850000 )['buyer_fee'], null, 'unknown house rejected' );
ic_is( IC_Salvage_Fees::quote( 'IAA', 0 )['buyer_fee'], 0, 'a genuine zero hammer yields a zero premium' );

ic_test( 'fees: source names are normalised, not matched exactly' );
ic_is( IC_Salvage_Fees::normalise_source( 'iaa australia' ), 'IAA', 'IAA Australia' );
ic_is( IC_Salvage_Fees::normalise_source( 'IAAI' ), 'IAA', 'IAAI' );
ic_is( IC_Salvage_Fees::normalise_source( 'Pickles Auctions' ), 'Pickles', 'Pickles Auctions' );
ic_is( IC_Salvage_Fees::normalise_source( ' manheim ' ), 'Manheim', 'whitespace and case' );

ic_test( 'fees: percentage rounding is half-up on integer cents' );
ic_is( IC_Salvage_Fees::pct_of( 850000, 1495 ), 127075, '14.95% of $8,500 is exact' );
ic_is( IC_Salvage_Fees::pct_of( 100, 1495 ), 15, '14.95% of $1.00 rounds half-up to 15c' );
ic_is( IC_Salvage_Fees::pct_of( 0, 1495 ), 0, 'zero stays zero' );

ic_test( 'money: formatting comes from integers, never floats' );
ic_is( IC_Salvage_Money::format( 140575 ), '$1,405.75', 'thousands separator and 2dp' );
ic_is( IC_Salvage_Money::format( 170700 ), '$1,707.00', 'trailing zeros kept' );
ic_is( IC_Salvage_Money::format( 0 ), '$0.00', 'zero' );
ic_is( IC_Salvage_Money::format( 5 ), '$0.05', 'sub-dollar' );
ic_is( IC_Salvage_Money::format( -500 ), '-$5.00', 'negative sign outside the symbol' );
ic_is( IC_Salvage_Money::format( 123456789 ), '$1,234,567.89', 'millions' );

ic_test( 'money: parsing rejects junk instead of silently returning zero' );
ic_is( IC_Salvage_Money::parse( '8500' ), 850000, 'bare dollars' );
ic_is( IC_Salvage_Money::parse( '$8,500' ), 850000, 'symbol and separators' );
ic_is( IC_Salvage_Money::parse( '8500.50' ), 850050, 'cents' );
ic_is( IC_Salvage_Money::parse( '8500.5' ), 850050, 'one decimal place' );
ic_is( IC_Salvage_Money::parse( ' 8 500 ' ), 850000, 'spaces' );
ic_is( IC_Salvage_Money::parse( '' ), null, 'empty is null, not zero' );
ic_is( IC_Salvage_Money::parse( 'abc' ), null, 'text is null, not zero' );
ic_is( IC_Salvage_Money::parse( '8500.123' ), null, 'three decimals rejected rather than truncated' );
ic_is( IC_Salvage_Money::parse( null ), null, 'null in, null out' );

ic_test( 'money: a parsed typo cannot become a real fee' );
$cents = IC_Salvage_Money::parse( '85OO' ); // letter O, not zero
ic_is( $cents, null, 'a typo parses to null' );
ic_is( IC_Salvage_Fees::quote( 'IAA', $cents )['buyer_fee'], null, 'and therefore quotes no fee' );
