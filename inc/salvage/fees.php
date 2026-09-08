<?php
/**
 * Salvage board — buyer fee engine.
 *
 * Pure logic. No WordPress dependency, so it is unit-testable in plain PHP.
 *
 * ALL money is handled as integer cents. Never floats: a float cent error in a
 * bidding ceiling is a real bid at the wrong number.
 *
 * Fee schedules are published-source-only. Where an auction house has not
 * published a schedule we return NULL and a reason — never an estimate. A
 * guessed fee looks exactly like a known one on screen, and that is the failure
 * mode this board exists to avoid.
 *
 * @package imanicars
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'IC_SALVAGE_TEST' ) ) { exit; }

class IC_Salvage_Fees {

	/**
	 * Published fee schedules.
	 *
	 * pct       — hundredths of a percent (1495 = 14.95%)
	 * flat      — flat buyer fee in cents, applied when hammer is OVER flat_over
	 * flat_over — threshold in cents; at or below it the flat component is not published
	 * export_admin, transfer — fixed cents, always applied
	 */
	public static function schedules() {
		return array(
			'IAA' => array(
				'label'        => 'IAA',
				'known'        => true,
				'pct'          => 1495,
				'flat'         => 13500,
				'flat_over'    => 100000,
				'export_admin' => 0,
				'transfer'     => 0,
				'source_note'  => 'IAA published schedule: 14.95% + $135 over $1,000. No export admin fee.',
			),
			'Pickles' => array(
				'label'        => 'Pickles',
				'known'        => true,
				'pct'          => 1500,
				'flat'         => 16000,
				'flat_over'    => 100000,
				'export_admin' => 23600,
				'transfer'     => 3600,
				'source_note'  => 'Pickles published schedule: 15% + $160 over $1,000, plus $236 export admin and $36 transfer.',
			),
			'Manheim' => array(
				'label'  => 'Manheim',
				'known'  => false,
				'reason' => 'Manheim has not published a retrievable buyer fee schedule — its fee PDF returns HTTP 403. No fee is shown because none is known.',
			),
		);
	}

	/** Percentage of an amount. $pct is hundredths of a percent. Half-up rounding. */
	public static function pct_of( $cents, $pct ) {
		$cents = (int) $cents;
		$pct   = (int) $pct;
		$num   = $cents * $pct;
		$den   = 10000;
		if ( $num >= 0 ) {
			return intdiv( $num + intdiv( $den, 2 ), $den );
		}
		return -intdiv( -$num + intdiv( $den, 2 ), $den );
	}

	/**
	 * Quote the buyer fee and landed-before-duty cost for a hammer price.
	 *
	 * @param string   $source Auction house.
	 * @param int|null $hammer Hammer price in cents.
	 * @return array
	 */
	public static function quote( $source, $hammer ) {
		$schedules = self::schedules();
		$key       = self::normalise_source( $source );

		$out = array(
			'source'             => $key,
			'known'              => false,
			'hammer'             => null,
			'buyer_fee'          => null,
			'landed_before_duty' => null,
			'components'         => array(),
			'caveats'            => array(),
			'unavailable_reason' => null,
			'partial'            => false,
		);

		if ( ! isset( $schedules[ $key ] ) ) {
			$out['unavailable_reason'] = sprintf( 'Unknown auction source "%s" — no fee schedule on file.', (string) $source );
			return $out;
		}

		$s = $schedules[ $key ];

		if ( empty( $s['known'] ) ) {
			$out['unavailable_reason'] = $s['reason'];
			return $out;
		}

		if ( null === $hammer || '' === $hammer ) {
			$out['known']              = true;
			$out['unavailable_reason'] = 'No hammer price recorded yet.';
			return $out;
		}

		$hammer = (int) $hammer;
		if ( $hammer < 0 ) {
			$out['unavailable_reason'] = 'Hammer price cannot be negative.';
			return $out;
		}

		$out['known']  = true;
		$out['hammer'] = $hammer;

		$fee  = 0;
		$prem = self::pct_of( $hammer, $s['pct'] );
		$fee += $prem;

		$out['components'][] = array(
			'label'  => sprintf( 'Buyer premium %s%%', self::pct_label( $s['pct'] ) ),
			'amount' => $prem,
		);

		// The flat component is published only ABOVE the threshold. At or below it we
		// do not know what is charged, so the quote is explicitly partial rather than
		// quietly assuming zero.
		if ( $hammer > $s['flat_over'] ) {
			if ( $s['flat'] > 0 ) {
				$fee += $s['flat'];
				$out['components'][] = array(
					'label'  => sprintf( 'Flat fee (over %s)', IC_Salvage_Money::format( $s['flat_over'] ) ),
					'amount' => $s['flat'],
				);
			}
		} elseif ( $s['flat'] > 0 ) {
			$out['partial']   = true;
			$out['caveats'][] = sprintf(
				'%s publishes its %s flat fee only for hammer prices over %s. At %s the flat component is not published, so this fee is incomplete.',
				$s['label'],
				IC_Salvage_Money::format( $s['flat'] ),
				IC_Salvage_Money::format( $s['flat_over'] ),
				IC_Salvage_Money::format( $hammer )
			);
		}

		if ( $s['export_admin'] > 0 ) {
			$fee += $s['export_admin'];
			$out['components'][] = array( 'label' => 'Export admin', 'amount' => $s['export_admin'] );
		}
		if ( $s['transfer'] > 0 ) {
			$fee += $s['transfer'];
			$out['components'][] = array( 'label' => 'Transfer', 'amount' => $s['transfer'] );
		}

		$out['buyer_fee']          = $fee;
		$out['landed_before_duty'] = $hammer + $fee;

		// Non-negotiable: never imply duty is included.
		$out['caveats'][] = 'Before duty. Kenyan duty is assessed on KRA CRSP, not on what you paid — run the KRA calculator per car.';
		$out['caveats'][] = 'Excludes freight, marine insurance, KEBS/JEVIC inspection and Kenyan clearing costs.';

		return $out;
	}

	/** 1495 => "14.95", 1500 => "15" */
	public static function pct_label( $pct ) {
		$s = number_format( $pct / 100, 2, '.', '' );
		if ( false !== strpos( $s, '.' ) ) {
			$s = rtrim( rtrim( $s, '0' ), '.' );
		}
		return $s;
	}

	/** Map a free-text source to a schedule key. */
	public static function normalise_source( $source ) {
		$s = strtolower( trim( (string) $source ) );
		if ( '' === $s ) { return ''; }
		if ( false !== strpos( $s, 'iaa' ) ) { return 'IAA'; }
		if ( false !== strpos( $s, 'pickles' ) ) { return 'Pickles'; }
		if ( false !== strpos( $s, 'manheim' ) ) { return 'Manheim'; }
		return (string) $source;
	}
}

/** Money formatting from integer cents. Never from a float. */
class IC_Salvage_Money {

	/** 140575 => "$1,405.75" */
	public static function format( $cents, $symbol = '$' ) {
		if ( null === $cents || '' === $cents ) { return ''; }
		$cents = (int) $cents;
		$neg   = $cents < 0;
		$abs   = abs( $cents );
		$whole = intdiv( $abs, 100 );
		$frac  = $abs % 100;
		$str   = $symbol . number_format( $whole ) . '.' . str_pad( (string) $frac, 2, '0', STR_PAD_LEFT );
		return $neg ? '-' . $str : $str;
	}

	/**
	 * Parse user input ("8500", "$8,500.00", "8 500") to integer cents.
	 *
	 * Returns null when the input is not a usable number — never 0, because a
	 * silent 0 would compute a real-looking fee off a typo.
	 */
	public static function parse( $input ) {
		if ( null === $input ) { return null; }
		$s = trim( (string) $input );
		if ( '' === $s ) { return null; }
		$s = str_replace( array( '$', ',', ' ', "\xc2\xa0" ), '', $s );
		if ( ! preg_match( '/^-?\d+(\.\d{1,2})?$/', $s ) ) { return null; }
		$neg = ( '-' === substr( $s, 0, 1 ) );
		if ( $neg ) { $s = substr( $s, 1 ); }
		$parts = explode( '.', $s );
		$whole = (int) $parts[0];
		$frac  = isset( $parts[1] ) ? (int) str_pad( $parts[1], 2, '0' ) : 0;
		$cents = $whole * 100 + $frac;
		return $neg ? -$cents : $cents;
	}
}
