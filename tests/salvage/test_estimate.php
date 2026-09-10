<?php
/**
 * Estimate vs auction price.
 *
 * The failure this file exists to prevent: a spreadsheet cell that could not be
 * read becoming a number on the board. Every "cannot read" path is asserted to
 * produce null and a reason, never zero and never a silent blank.
 */

ic_test( 'estimate: a single figure parses, in every format the sheet uses' );
foreach ( array( '12500', '$12,500', '$12500.00', '12,500', ' 12500 ', 'AUD 12,500' ) as $s ) {
	ic_is( IC_Salvage_Estimate::parse( $s )['low'], 1250000, sprintf( '"%s" is $12,500', $s ) );
}
ic_is( IC_Salvage_Estimate::parse( '12500' )['is_range'], false, 'a single figure is not a range' );
ic_is( IC_Salvage_Estimate::parse( '12500' )['high'], 1250000, 'low and high are the same figure' );

ic_test( 'estimate: an explicit thousands suffix is unambiguous and is accepted' );
ic_is( IC_Salvage_Estimate::parse( '12k' )['low'], 1200000, '12k' );
ic_is( IC_Salvage_Estimate::parse( '12.5k' )['low'], 1250000, '12.5k' );
ic_is( IC_Salvage_Estimate::parse( '$8K' )['low'], 800000, 'case and dollar sign' );

ic_test( 'estimate: a bare small number is NOT silently multiplied' );
// "about 12" in a sheet is twelve dollars or twelve thousand depending on who
// typed it. Reading it as $12 is wrong; reading it as $12,000 is inventing a
// price. It parses as $12 because that is literally what is written.
ic_is( IC_Salvage_Estimate::parse( '12' )['low'], 1200, '"12" is twelve dollars, not twelve thousand' );

ic_test( 'estimate: ranges parse to both ends and are labelled as ranges' );
foreach ( array( '8000-10000', '8,000 - 10,000', '$8000 – $10,000', '8000 to 10000', '8k-10k' ) as $s ) {
	$p = IC_Salvage_Estimate::parse( $s );
	ic_is( $p['low'], 800000, sprintf( '"%s" low', $s ) );
	ic_is( $p['high'], 1000000, sprintf( '"%s" high', $s ) );
	ic_is( $p['is_range'], true, sprintf( '"%s" is flagged a range', $s ) );
}

ic_test( 'estimate: a backwards range is ordered, not turned into a negative width' );
$p = IC_Salvage_Estimate::parse( '10000-8000' );
ic_is( $p['low'], 800000, 'low is the smaller end' );
ic_is( $p['high'], 1000000, 'high is the larger end' );

ic_test( 'estimate: a range of one figure is a figure, not a range' );
ic_is( IC_Salvage_Estimate::parse( '8000-8000' )['is_range'], false, 'equal ends are not a range' );

ic_test( 'estimate: unreadable text yields NOTHING, and says what it could not read' );
foreach ( array( 'TBC', 'see notes', 'ask Wilson', 'n/a', 'high' ) as $s ) {
	$p = IC_Salvage_Estimate::parse( $s );
	ic_is( $p['low'], null, sprintf( '"%s" produces no figure', $s ) );
	ic_is( $p['unparsed'], $s, sprintf( '"%s" is echoed back so the board can show it', $s ) );
}

ic_test( 'estimate: an absent cell is absent, not unparseable' );
foreach ( array( null, '', '-', '?' ) as $s ) {
	$p = IC_Salvage_Estimate::parse( $s );
	ic_is( $p['low'], null, 'no figure' );
	ic_is( $p['unparsed'], null, 'and nothing to complain about — the cell was simply empty' );
}

ic_test( 'estimate: zero is a real estimate and is not confused with absence' );
$p = IC_Salvage_Estimate::parse( '0' );
ic_is( $p['low'], 0, '"0" parses to zero cents' );
ic_ok( null !== $p['low'], 'and zero is NOT null — the sheet said nothing is what it is worth' );

/* ---------------- Comparison ---------------- */

ic_test( 'compare: a lot that made more than the estimate' );
$c = IC_Salvage_Estimate::compare( '$20,000', 2325000 ); // the real lot 163 shape
ic_is( $c['comparable'], true, 'comparable' );
ic_is( $c['direction'], 'over', 'it made more' );
ic_is( $c['delta'], 325000, '$3,250 over' );
ic_ok( abs( $c['pct'] - 16.25 ) < 0.001, '16.25% over' );

ic_test( 'compare: a lot that made less' );
$c = IC_Salvage_Estimate::compare( '$10,000', 400000 );
ic_is( $c['direction'], 'under', 'under' );
ic_is( $c['delta'], -600000, 'negative delta, so the sign carries the direction too' );
ic_ok( abs( $c['pct'] + 60.0 ) < 0.001, '-60%' );

ic_test( 'compare: exactly on the estimate is its own answer, not a rounding of over' );
$c = IC_Salvage_Estimate::compare( '$10,000', 1000000 );
ic_is( $c['direction'], 'on', 'on' );
ic_is( $c['delta'], 0, 'zero delta' );
ic_is( IC_Salvage_Estimate::format_variance( $c ), 'exactly on the estimate', 'and reads that way' );

ic_test( 'compare: a range is measured from the end that was MISSED, never a midpoint' );
$c = IC_Salvage_Estimate::compare( '8000-10000', 1200000 );
ic_is( $c['direction'], 'over', 'above the top of the range' );
ic_is( $c['delta'], 200000, '$2,000 over the TOP — not $3,000 over an invented $9,000 midpoint' );

$c = IC_Salvage_Estimate::compare( '8000-10000', 700000 );
ic_is( $c['direction'], 'under', 'below the bottom' );
ic_is( $c['delta'], -100000, '$1,000 under the BOTTOM' );

$c = IC_Salvage_Estimate::compare( '8000-10000', 900000 );
ic_is( $c['direction'], 'on', 'inside the range is not a miss' );
ic_is( $c['delta'], 0, 'and carries no variance' );
ic_is( IC_Salvage_Estimate::format_variance( $c ), 'inside the estimate', 'phrased as a range, not as an exact hit' );

ic_test( 'compare: one side unknown is NOT a variance of zero' );
$c = IC_Salvage_Estimate::compare( 'TBC', 1000000 );
ic_is( $c['comparable'], false, 'no estimate, nothing to compare' );
ic_is( $c['delta'], null, 'delta is null, never 0' );
ic_ok( false !== strpos( (string) $c['reason'], 'TBC' ), 'and the reason quotes the cell it could not read' );

$c = IC_Salvage_Estimate::compare( '$10,000', null );
ic_is( $c['comparable'], false, 'no hammer yet' );
ic_is( $c['delta'], null, 'delta is null' );
ic_is( $c['estimate_low'], 1000000, 'but the estimate itself is still known and still shown' );
ic_ok( false !== stripos( (string) $c['reason'], 'no hammer price recorded' ), 'and the reason names the missing side' );

$c = IC_Salvage_Estimate::compare( null, null );
ic_is( $c['comparable'], false, 'neither side' );
ic_ok( false !== stripos( (string) $c['reason'], 'no estimate' ), 'the absent estimate is reported first' );

ic_test( 'compare: a zero estimate does not divide by zero' );
$c = IC_Salvage_Estimate::compare( '0', 500000 );
ic_is( $c['comparable'], true, 'still comparable' );
ic_is( $c['delta'], 500000, 'the dollar delta is real' );
ic_is( $c['pct'], null, 'but the percentage is undefined and is null, not INF or 0' );
ic_ok( false === strpos( (string) IC_Salvage_Estimate::format_variance( $c ), '%' ), 'and no percentage is printed' );

ic_test( 'formatting: an estimate renders as written, a range as a range' );
ic_is( IC_Salvage_Estimate::format_estimate( IC_Salvage_Estimate::compare( '12500', null ) ), '$12,500.00', 'single' );
ic_is( IC_Salvage_Estimate::format_estimate( IC_Salvage_Estimate::compare( '8000-10000', null ) ), '$8,000.00 – $10,000.00', 'range' );
ic_is( IC_Salvage_Estimate::format_estimate( IC_Salvage_Estimate::compare( 'TBC', null ) ), null, 'nothing to render' );
ic_is( IC_Salvage_Estimate::format_variance( IC_Salvage_Estimate::compare( 'TBC', 100000 ) ), null, 'no variance to render' );

ic_test( 'formatting: the variance carries a sign AND a word, so it cannot be misread' );
$v = IC_Salvage_Estimate::format_variance( IC_Salvage_Estimate::compare( '$20,000', 2325000 ) );
ic_ok( 0 === strpos( $v, '+' ), 'leads with a plus' );
ic_ok( false !== strpos( $v, 'over' ), 'and says "over" in words' );
$v = IC_Salvage_Estimate::format_variance( IC_Salvage_Estimate::compare( '$20,000', 1500000 ) );
ic_ok( 0 === strpos( $v, '-' ), 'leads with a minus' );
ic_ok( false !== strpos( $v, 'under' ), 'and says "under"' );

ic_test( 'the real 10 Sep Pickles lane: statutory prices as parts, and the estimate proves it' );
// Estimates written before the sale against what the four actually made. The
// finding Rop needs the board to make visible is that a statutory write-off is
// not a discounted car — it is a parts list, and an estimate built from retail
// misses it by multiples.
$lane = array(
	array( 'lot' => 71,  'est' => '$25,000',        'hammer' => 2875000, 'dir' => 'over'  ),
	array( 'lot' => 275, 'est' => '$22,000-26,000', 'hammer' => 2400000, 'dir' => 'on'    ),
	array( 'lot' => 503, 'est' => '$8,000',         'hammer' => 260000,  'dir' => 'under' ),
	array( 'lot' => 551, 'est' => '$12,000',        'hammer' => 400000,  'dir' => 'under' ),
);
foreach ( $lane as $l ) {
	$c = IC_Salvage_Estimate::compare( $l['est'], $l['hammer'] );
	ic_is( $c['direction'], $l['dir'], sprintf( 'lot %d came in %s', $l['lot'], $l['dir'] ) );
}
$c = IC_Salvage_Estimate::compare( '$12,000', 400000 );
ic_ok( $c['pct'] < -60, 'the statutory Wildtrak missed a retail-derived estimate by more than 60%' );
