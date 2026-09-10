<?php
/**
 * Salvage board — estimate vs auction price.
 *
 * Rop's question for the three-week watch is not "what did it cost", it is
 * "were we right". That needs two numbers side by side: what we said the lot was
 * worth before the sale, and what it actually made.
 *
 * The estimate columns arrive as free text from a valuation spreadsheet, so this
 * file's whole job is refusing to guess:
 *
 * 1. A single figure parses. A range parses to its two ends and is LABELLED as a
 *    range — the midpoint is never invented and never shown as if it were the
 *    estimate.
 * 2. Anything else ("TBC", "see notes", an empty cell) returns null with a
 *    reason. Null is never rendered as zero, and zero is never rendered as
 *    "we estimated nothing".
 * 3. A variance is only computed when BOTH sides are known. One known side and
 *    one unknown is not a small variance, it is no variance.
 *
 * @package imanicars
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'IC_SALVAGE_TEST' ) ) { exit; }

class IC_Salvage_Estimate {

	/**
	 * Parse a spreadsheet estimate cell.
	 *
	 * @param string|null $raw
	 * @return array{low:int|null, high:int|null, is_range:bool, unparsed:string|null}
	 *         low/high in integer cents. unparsed carries the original text when
	 *         nothing could be read from it, so the board can show what it could
	 *         not understand rather than an empty cell.
	 */
	public static function parse( $raw ) {
		$none = array( 'low' => null, 'high' => null, 'is_range' => false, 'unparsed' => null );

		if ( null === $raw ) { return $none; }
		$s = trim( (string) $raw );
		if ( '' === $s || '-' === $s || '?' === $s ) { return $none; }

		// A range: "8,000-10,000", "$8000 – $10,000", "8000 to 10000".
		$parts = preg_split( '/\s*(?:-|–|—|to)\s*/i', $s, 2 );
		if ( 2 === count( $parts ) && '' !== trim( $parts[0] ) && '' !== trim( $parts[1] ) ) {
			$lo = self::one( $parts[0] );
			$hi = self::one( $parts[1] );
			if ( null !== $lo && null !== $hi ) {
				// A backwards range is a typo, not a range. Order it rather than
				// producing a negative width, but do not silently accept equality
				// as a range — that is a single figure written oddly.
				if ( $lo > $hi ) { $t = $lo; $lo = $hi; $hi = $t; }
				return array( 'low' => $lo, 'high' => $hi, 'is_range' => ( $lo !== $hi ), 'unparsed' => null );
			}
		}

		$one = self::one( $s );
		if ( null !== $one ) {
			return array( 'low' => $one, 'high' => $one, 'is_range' => false, 'unparsed' => null );
		}

		return array( 'low' => null, 'high' => null, 'is_range' => false, 'unparsed' => $s );
	}

	/**
	 * One figure to cents. Accepts "$12,500", "12500", "12,500.50", "12.5k".
	 *
	 * Deliberately NOT tolerant of anything else. A cell reading "about 12" is
	 * twelve dollars or twelve thousand depending on who typed it, and a board
	 * that picks one is inventing a price.
	 */
	private static function one( $v ) {
		$s = strtolower( trim( (string) $v ) );
		if ( '' === $s ) { return null; }
		$s = str_replace( array( '$', ',', ' ', "\xc2\xa0", 'aud' ), '', $s );

		// "12.5k" / "12k" — an explicit thousands suffix is unambiguous.
		if ( preg_match( '/^(\d+(?:\.\d+)?)k$/', $s, $m ) ) {
			return (int) round( (float) $m[1] * 100000 );
		}
		if ( ! preg_match( '/^\d+(\.\d{1,2})?$/', $s ) ) { return null; }
		return IC_Salvage_Money::parse( $s );
	}

	/**
	 * Compare an estimate against what the lot actually made.
	 *
	 * @param string|null $raw_estimate The spreadsheet cell.
	 * @param int|null    $actual_cents Hammer, or landed — the caller decides
	 *                                  which, and says so in the label.
	 * @return array{
	 *   comparable:bool, estimate_low:int|null, estimate_high:int|null,
	 *   is_range:bool, actual:int|null, delta:int|null, pct:float|null,
	 *   direction:string|null, reason:string|null
	 * }
	 *   direction is 'over' when the lot made MORE than estimated, 'under' when
	 *   less, 'on' when inside a range or exactly on a single figure.
	 */
	public static function compare( $raw_estimate, $actual_cents ) {
		$e   = self::parse( $raw_estimate );
		$out = array(
			'comparable'    => false,
			'estimate_low'  => $e['low'],
			'estimate_high' => $e['high'],
			'is_range'      => $e['is_range'],
			'actual'        => ( null === $actual_cents ? null : (int) $actual_cents ),
			'delta'         => null,
			'pct'           => null,
			'direction'     => null,
			'reason'        => null,
		);

		if ( null === $e['low'] ) {
			$out['reason'] = ( null !== $e['unparsed'] )
				? sprintf( 'Estimate "%s" is not a figure this board can read.', $e['unparsed'] )
				: 'No estimate recorded for this lot.';
			return $out;
		}
		if ( null === $actual_cents ) {
			$out['reason'] = 'No hammer price recorded yet — nothing to compare the estimate against.';
			return $out;
		}

		$actual = (int) $actual_cents;
		$out['comparable'] = true;

		if ( $actual > $e['high'] ) {
			$out['direction'] = 'over';
			$out['delta']     = $actual - $e['high'];
			$ref              = $e['high'];
		} elseif ( $actual < $e['low'] ) {
			$out['direction'] = 'under';
			$out['delta']     = $actual - $e['low'];
			$ref              = $e['low'];
		} else {
			$out['direction'] = 'on';
			$out['delta']     = 0;
			$ref              = $e['low'];
		}

		// Percentage against the end of the estimate that was missed, so a range
		// is not reported as a miss from a midpoint nobody wrote down.
		$out['pct'] = ( $ref > 0 ) ? ( $out['delta'] / $ref ) * 100 : null;

		if ( $e['is_range'] ) {
			$out['reason'] = 'Measured from the end of the range the price missed, not from a midpoint.';
		}
		return $out;
	}

	/** "$8,000.00 – $10,000.00" or "$8,000.00", or null when there is no estimate. */
	public static function format_estimate( array $cmp ) {
		if ( null === $cmp['estimate_low'] ) { return null; }
		if ( $cmp['is_range'] ) {
			return IC_Salvage_Money::format( $cmp['estimate_low'] ) . ' – ' . IC_Salvage_Money::format( $cmp['estimate_high'] );
		}
		return IC_Salvage_Money::format( $cmp['estimate_low'] );
	}

	/** "+$1,250.00 (12.5% over)" — or null when there is nothing to say. */
	public static function format_variance( array $cmp ) {
		if ( ! $cmp['comparable'] ) { return null; }
		if ( 'on' === $cmp['direction'] ) {
			return $cmp['is_range'] ? 'inside the estimate' : 'exactly on the estimate';
		}
		$sign = ( $cmp['delta'] > 0 ) ? '+' : '';
		$s    = $sign . IC_Salvage_Money::format( $cmp['delta'] );
		if ( null !== $cmp['pct'] ) {
			$s .= sprintf( ' (%.1f%% %s)', abs( $cmp['pct'] ), $cmp['direction'] );
		}
		return $s;
	}
}
