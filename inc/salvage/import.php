<?php
/**
 * Salvage board — importer.
 *
 * Data arrives as a JSON file produced by tools/xlsx_to_salvage_json.py and is
 * uploaded through an admin screen. It is deliberately NOT read from a file in
 * the theme directory: the theme rsyncs to the public web root on every deploy,
 * so any data file living there would be downloadable by anyone who guessed its
 * name. The uploaded file is consumed from PHP's temp directory and never
 * written into the theme.
 *
 * The importer never deletes. A lot missing from a newer scan keeps its row, its
 * observations and its last_seen date, and is reported as absent — because the
 * price history is the entire point of the system, and a sold lot is exactly the
 * row that disappears from the next scan.
 *
 * @package imanicars
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class IC_Salvage_Import {

	const CAP  = 'manage_options';
	const SLUG = 'ic-salvage-import';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
	}

	public static function menu() {
		add_management_page(
			__( 'Salvage board import', 'imanicars' ),
			__( 'Salvage import', 'imanicars' ),
			self::CAP,
			self::SLUG,
			array( __CLASS__, 'screen' )
		);
	}

	/**
	 * Import a decoded payload.
	 *
	 * @param array $payload Decoded JSON.
	 * @return array|WP_Error Report.
	 */
	public static function run( array $payload ) {
		if ( empty( $payload['format'] ) || 'imani-salvage/1' !== $payload['format'] ) {
			return new WP_Error( 'ic_salvage_format', __( 'Unrecognised file format. Expected imani-salvage/1.', 'imanicars' ) );
		}
		$scan_date = isset( $payload['scan_date'] ) ? (string) $payload['scan_date'] : '';
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $scan_date ) ) {
			return new WP_Error( 'ic_salvage_scan_date', __( 'The file has no valid scan_date.', 'imanicars' ) );
		}
		if ( empty( $payload['feeds'] ) || ! is_array( $payload['feeds'] ) ) {
			return new WP_Error( 'ic_salvage_feeds', __( 'The file contains no feeds.', 'imanicars' ) );
		}

		IC_Salvage_Schema::maybe_upgrade();

		$ctx = IC_Salvage_Normalise::context( $scan_date );

		$report = array(
			'scan_date'     => $scan_date,
			'inserted'      => 0,
			'updated'       => 0,
			'patched'       => 0,
			'skipped'       => 0,
			'unmatched'     => 0,
			'by_feed'       => array(),
			'anomalies'     => array(),
			'seen_lot_ids'  => array(),
		);

		$mapper = array(
			'iaa_scrape' => 'from_iaa_scrape',
			'pickles'    => 'from_pickles',
			'manheim'    => 'from_manheim',
		);

		foreach ( $payload['feeds'] as $feed ) {
			$type = isset( $feed['type'] ) ? (string) $feed['type'] : '';
			$rows = isset( $feed['rows'] ) && is_array( $feed['rows'] ) ? $feed['rows'] : array();
			$n    = array( 'rows' => count( $rows ), 'inserted' => 0, 'updated' => 0, 'patched' => 0, 'skipped' => 0, 'unmatched' => 0 );

			if ( isset( $mapper[ $type ] ) ) {
				$fn = $mapper[ $type ];
				foreach ( $rows as $i => $row ) {
					$record = IC_Salvage_Normalise::$fn( (array) $row, $ctx );
					$res    = IC_Salvage_Repo::upsert_lot( $record, IC_Salvage_Repo::scrape_fields(), $scan_date );

					if ( ! $res['id'] ) {
						$n['skipped']++;
						$report['skipped']++;
						$report['anomalies'][] = sprintf( '%s row %d: no stock number — row skipped.', $type, $i + 1 );
						continue;
					}
					$report['seen_lot_ids'][] = $res['id'];
					if ( 'inserted' === $res['action'] ) { $n['inserted']++; $report['inserted']++; }
					else { $n['updated']++; $report['updated']++; }

					self::collect_anomalies( $type, $i, $row, $record, $report );
				}
			} elseif ( 'iaa_valuation' === $type ) {
				foreach ( $rows as $i => $row ) {
					$patch = IC_Salvage_Normalise::from_iaa_valuation( (array) $row, $ctx );
					$res   = IC_Salvage_Repo::apply_patch( $patch, IC_Salvage_Repo::valuation_fields() );
					if ( 'patched' === $res['action'] ) {
						$n['patched']++;
						$report['patched']++;
					} elseif ( 'no_matching_lot' === $res['action'] ) {
						$n['unmatched']++;
						$report['unmatched']++;
						$report['anomalies'][] = sprintf(
							'Valuation row %d (stock %s) has no matching lot in the scrape feed — the valuation was not applied.',
							$i + 1,
							isset( $patch['stock'] ) ? $patch['stock'] : '?'
						);
					} else {
						$n['skipped']++;
						$report['skipped']++;
					}
				}
			} else {
				$report['anomalies'][] = sprintf( 'Unknown feed type "%s" — ignored.', $type );
			}

			$report['by_feed'][ $type ] = $n;
		}

		// Lots that exist but were not in this scan. Reported, never deleted.
		$report['absent'] = self::absent_from_scan( $report['seen_lot_ids'] );
		unset( $report['seen_lot_ids'] );

		return $report;
	}

	/**
	 * Cross-check derived flags against what the source claimed.
	 *
	 * The IAA feed publishes its own "Kenya eligible" column. We derive ours from
	 * the rules rather than trusting it — but a disagreement means either the
	 * rules or the source is wrong, and that is worth knowing before bidding.
	 */
	private static function collect_anomalies( $type, $i, $row, array $record, array &$report ) {
		if ( ! empty( $record['data_warnings'] ) ) {
			foreach ( (array) $record['data_warnings'] as $w ) {
				$report['anomalies'][] = sprintf( '%s stock %s: %s', $type, $record['stock'], $w );
			}
		}

		if ( 'iaa_scrape' === $type && isset( $row[11] ) ) {
			$claimed_raw = strtoupper( trim( (string) $row[11] ) );
			if ( '' !== $claimed_raw ) {
				$claimed = ( 0 === strpos( $claimed_raw, 'YES' ) );
				$derived = $record['kebs_eligible'];
				if ( null !== $derived && $derived !== $claimed ) {
					$report['anomalies'][] = sprintf(
						'IAA stock %s: source column says "%s" but the rules derive %s. Check the year and damage codes.',
						$record['stock'],
						$claimed_raw,
						$derived ? 'ELIGIBLE' : 'NOT eligible'
					);
				}
			}
		}
	}

	/** Lots in the table that this scan did not mention. */
	private static function absent_from_scan( array $seen_ids ) {
		global $wpdb;
		$t = IC_Salvage_Schema::lots_table();
		if ( ! $seen_ids ) {
			return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t}" );
		}
		$ids = implode( ',', array_map( 'intval', array_unique( $seen_ids ) ) );
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t} WHERE id NOT IN ({$ids})" );
	}

	/* ---------------------------------------------------------------
	 * Admin screen
	 * ------------------------------------------------------------- */

	public static function screen() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'You do not have permission to import salvage data.', 'imanicars' ) );
		}

		$report = null;
		$error  = null;

		if ( isset( $_POST['ic_salvage_import'] ) ) {
			check_admin_referer( 'ic_salvage_import' );

			if ( empty( $_FILES['ic_salvage_file']['tmp_name'] ) || ! is_uploaded_file( $_FILES['ic_salvage_file']['tmp_name'] ) ) {
				$error = __( 'No file was uploaded.', 'imanicars' );
			} else {
				$raw = file_get_contents( $_FILES['ic_salvage_file']['tmp_name'] );
				// Consume from the temp file only. Nothing is written into the theme.
				$data = json_decode( $raw, true );
				if ( null === $data ) {
					$error = __( 'That file is not valid JSON.', 'imanicars' );
				} else {
					$result = self::run( $data );
					if ( is_wp_error( $result ) ) {
						$error = $result->get_error_message();
					} else {
						$report = $result;
					}
				}
			}
		}

		$totals = IC_Salvage_Schema::tables_exist() ? IC_Salvage_Repo::totals() : null;
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Salvage board import', 'imanicars' ); ?></h1>

			<?php if ( $error ) : ?>
				<div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
			<?php endif; ?>

			<?php if ( $report ) : ?>
				<div class="notice notice-success">
					<p><strong><?php esc_html_e( 'Import finished.', 'imanicars' ); ?></strong>
					<?php
					printf(
						/* translators: 1: inserted, 2: updated, 3: patched, 4: skipped */
						esc_html__( '%1$d inserted, %2$d updated, %3$d valuations applied, %4$d skipped.', 'imanicars' ),
						(int) $report['inserted'], (int) $report['updated'], (int) $report['patched'], (int) $report['skipped']
					);
					?></p>
					<p><?php
					printf(
						esc_html__( '%d existing lots were not in this scan. They were kept, not deleted.', 'imanicars' ),
						(int) $report['absent']
					);
					?></p>
				</div>

				<h2><?php esc_html_e( 'Per feed', 'imanicars' ); ?></h2>
				<table class="widefat striped" style="max-width:760px">
					<thead><tr>
						<th><?php esc_html_e( 'Feed', 'imanicars' ); ?></th>
						<th><?php esc_html_e( 'Rows', 'imanicars' ); ?></th>
						<th><?php esc_html_e( 'Inserted', 'imanicars' ); ?></th>
						<th><?php esc_html_e( 'Updated', 'imanicars' ); ?></th>
						<th><?php esc_html_e( 'Patched', 'imanicars' ); ?></th>
						<th><?php esc_html_e( 'Skipped', 'imanicars' ); ?></th>
						<th><?php esc_html_e( 'Unmatched', 'imanicars' ); ?></th>
					</tr></thead>
					<tbody>
					<?php foreach ( $report['by_feed'] as $feed => $n ) : ?>
						<tr>
							<td><code><?php echo esc_html( $feed ); ?></code></td>
							<td><?php echo (int) $n['rows']; ?></td>
							<td><?php echo (int) $n['inserted']; ?></td>
							<td><?php echo (int) $n['updated']; ?></td>
							<td><?php echo (int) $n['patched']; ?></td>
							<td><?php echo (int) $n['skipped']; ?></td>
							<td><?php echo (int) $n['unmatched']; ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>

				<h2><?php esc_html_e( 'Anomalies', 'imanicars' ); ?></h2>
				<?php if ( empty( $report['anomalies'] ) ) : ?>
					<p><?php esc_html_e( 'None. Every row parsed, and every derived Kenya-eligibility flag agreed with the source column.', 'imanicars' ); ?></p>
				<?php else : ?>
					<ul class="ul-disc">
						<?php foreach ( $report['anomalies'] as $a ) : ?>
							<li><?php echo esc_html( $a ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ( $totals ) : ?>
				<h2><?php esc_html_e( 'Currently stored', 'imanicars' ); ?></h2>
				<p>
					<?php
					printf(
						esc_html__( '%1$d lots across %2$d auction houses, %3$d price observations (%4$d carrying a hammer price). Latest scan: %5$s', 'imanicars' ),
						(int) $totals['lots'], (int) $totals['sources'], (int) $totals['observations'], (int) $totals['priced'],
						$totals['scan_date'] ? esc_html( $totals['scan_date'] ) : esc_html__( 'none', 'imanicars' )
					);
					?>
				</p>
			<?php endif; ?>

			<h2><?php esc_html_e( 'Upload a scan', 'imanicars' ); ?></h2>
			<p class="description" style="max-width:760px">
				<?php esc_html_e( 'Produce the file locally with tools/xlsx_to_salvage_json.py, then upload it here. Existing lots are matched on auction house + stock number and updated in place; nothing is ever deleted, and a lot you have already marked Sold keeps that status.', 'imanicars' ); ?>
			</p>
			<form method="post" enctype="multipart/form-data">
				<?php wp_nonce_field( 'ic_salvage_import' ); ?>
				<input type="file" name="ic_salvage_file" accept="application/json,.json" required>
				<p><button type="submit" name="ic_salvage_import" value="1" class="button button-primary">
					<?php esc_html_e( 'Import', 'imanicars' ); ?>
				</button></p>
			</form>
		</div>
		<?php
	}
}
