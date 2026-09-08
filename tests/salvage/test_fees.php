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

ic_test( 'fees: Manheim is not known, and is never guessed' );
$q = IC_Salvage_Fees::quote( 'Manheim', 850000 );
ic_is( $q['buyer_fee'], null, 'Manheim buyer fee is NULL, not an estimate' );
ic_is( $q['landed_before_duty'], null, 'no landed cost without a fee' );
ic_is( $q['known'], false, 'Manheim schedule is flagged unknown' );
ic_ok( null !== $q['unavailable_reason'], 'a reason is given instead of a number' );
ic_ok( false !== strpos( $q['unavailable_reason'], '403' ), 'the reason names the 403 that blocked retrieval' );

ic_test( 'fees: the flat component below its published threshold' );
$q = IC_Salvage_Fees::quote( 'IAA', 100000 ); // exactly $1,000 — not OVER $1,000
ic_is( $q['partial'], true, 'quote is marked partial at exactly the threshold' );
ic_is( $q['buyer_fee'], 14950, 'only the premium is charged, flat is not assumed' );
ic_ok( ! empty( $q['caveats'] ), 'a caveat explains what is missing' );

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
