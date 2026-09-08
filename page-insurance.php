<?php
/**
 * Salvage board — /insurance
 *
 * Private, login-gated. IC_Salvage_Access::guard() has already run on
 * template_redirect; reaching this file means the visitor is authenticated and
 * holds view_salvage_board.
 *
 * Design brief (frontend-design §1):
 *
 *  1. WHO / WHAT DECISION — Rop, on a sale morning, usually on his phone:
 *     which lots sell in the next 48 hours, and what is my ceiling on each.
 *
 *  2. THE ONE NUMBER — the landed-before-duty cost at the hammer price he is
 *     considering. Everything else on the row is context for that figure.
 *
 *  3. WHAT THIS SCREEN COULD MISLEAD HIM INTO BELIEVING — designed against,
 *     specifically:
 *       a. That a landed cost includes Kenyan duty. It does not; duty is on
 *          CRSP. Stated as text beside every cost, not in a tooltip.
 *       b. That a blank "sells tomorrow" means "not selling tomorrow". IAA
 *          publishes no sale times at all, so those render as an em dash with
 *          an explanation — never as "No".
 *       c. That a Manheim lot is comparable on cost. Its fee schedule is not
 *          published, so its cost cells read "not found", in red.
 *       d. That the Kenya-eligible checkbox has already excluded flood lots.
 *          Flood is a separate, age-independent rejection and has its own flag.
 *       e. That the board is live. It is a dated snapshot, and the scan date is
 *          in the header with its age in days.
 *
 * @package imanicars
 */

get_header();

$filters   = IC_Salvage_View::filters_from_request();
$tables_ok = IC_Salvage_Schema::tables_exist();
$rows      = $tables_ok ? IC_Salvage_Repo::query( $filters ) : array();
$facets    = $tables_ok ? IC_Salvage_Repo::facets() : array( 'source' => array(), 'model' => array(), 'wovr' => array(), 'state' => array(), 'years' => null );
$totals    = $tables_ok ? IC_Salvage_Repo::totals() : array( 'lots' => 0, 'observations' => 0, 'priced' => 0, 'scan_date' => null, 'sources' => 0 );
$summary   = $tables_ok ? IC_Salvage_Repo::summary( $filters ) : array();

$tz  = wp_timezone();
$now = ( new DateTimeImmutable( 'now', $tz ) )->format( 'Y-m-d H:i:s' );
$tzn = $tz->getName();

// Counts computed from the rows on screen, so the headline cannot disagree with
// the table beneath it.
$c_48h = 0; $c_tomorrow = 0; $c_timed = 0; $c_untimed = 0; $c_kenya = 0; $c_flood = 0; $c_epa = 0;
foreach ( $rows as $r ) {
	if ( $r->sale_datetime ) {
		$c_timed++;
		if ( true === IC_Salvage_SaleDate::sells_within_hours( $r->sale_datetime, $now, 48, $tzn ) ) { $c_48h++; }
		if ( true === IC_Salvage_SaleDate::sells_tomorrow( $r->sale_datetime, $now, $tzn ) ) { $c_tomorrow++; }
	} else {
		$c_untimed++;
	}
	if ( 1 === (int) $r->kebs_eligible ) { $c_kenya++; }
	if ( 1 === (int) $r->flood_pvoc_reject ) { $c_flood++; }
	if ( 1 === (int) $r->vic_statutory_epa ) { $c_epa++; }
}

$scan_age = null;
if ( ! empty( $totals['scan_date'] ) ) {
	$scan     = new DateTimeImmutable( $totals['scan_date'] . ' 00:00:00', $tz );
	$today    = new DateTimeImmutable( ( new DateTimeImmutable( 'now', $tz ) )->format( 'Y-m-d' ) . ' 00:00:00', $tz );
	$scan_age = (int) $scan->diff( $today )->days;
}

$export_url = wp_nonce_url( add_query_arg( array_merge( $_GET, array( 'ic_salvage_export' => 1 ) ), IC_Salvage_Access::board_url() ), IC_Salvage_Ajax::NONCE );
?>

<div class="sb" id="ic-main">
<div class="sb-container">

	<header class="sb-head">
		<div class="sb-head__row">
			<h1 class="sb-head__title"><?php esc_html_e( 'Salvage board', 'imanicars' ); ?></h1>
			<span class="sb-private" title="<?php esc_attr_e( 'Login required, never indexed, not linked from the public site.', 'imanicars' ); ?>">
				<?php esc_html_e( 'Private', 'imanicars' ); ?>
			</span>
		</div>
		<p class="sb-head__meta">
			<?php if ( ! empty( $totals['scan_date'] ) ) : ?>
				<?php
				printf(
					esc_html__( 'Scan of %1$s — %2$s old.', 'imanicars' ),
					esc_html( $totals['scan_date'] ),
					esc_html( 0 === $scan_age ? __( 'today', 'imanicars' ) : sprintf( _n( '%d day', '%d days', $scan_age, 'imanicars' ), $scan_age ) )
				);
				?>
				<?php if ( $scan_age > 2 ) : ?>
					<strong class="sb-stale"><?php esc_html_e( 'Sale times may have passed — re-scan before bidding.', 'imanicars' ); ?></strong>
				<?php endif; ?>
			<?php else : ?>
				<?php esc_html_e( 'No scan imported yet.', 'imanicars' ); ?>
			<?php endif; ?>
			<?php
			printf(
				esc_html__( ' %1$d lots across %2$d auction houses. %3$d price observations recorded.', 'imanicars' ),
				(int) $totals['lots'], (int) $totals['sources'], (int) $totals['observations']
			);
			?>
		</p>
	</header>

	<?php if ( ! $tables_ok ) : ?>
		<div class="sb-alert sb-alert--bad">
			<strong><?php esc_html_e( 'The salvage tables do not exist.', 'imanicars' ); ?></strong>
			<?php esc_html_e( 'Open Tools → Salvage import in wp-admin once; the tables are created there. Nothing on this page will work until they are.', 'imanicars' ); ?>
		</div>
	<?php endif; ?>

	<!-- Standing caveats. Rendered as text, always visible. These are the four
	     things the numbers below could otherwise be read as saying. -->
	<div class="sb-standing">
		<p><strong><?php esc_html_e( 'Duty is not in any figure here.', 'imanicars' ); ?></strong>
			<?php esc_html_e( 'Kenyan duty is assessed on KRA CRSP, not on what you pay at auction. Every cost on this page is before duty — run the KRA calculator per car.', 'imanicars' ); ?></p>
		<p><strong><?php esc_html_e( 'Manheim buyer fees are not published.', 'imanicars' ); ?></strong>
			<?php esc_html_e( 'Its fee schedule returns HTTP 403, so Manheim cost cells read "not found". They are not zero, and they are not comparable to IAA or Pickles.', 'imanicars' ); ?></p>
		<p><strong><?php esc_html_e( 'IAA publishes no sale times in this feed.', 'imanicars' ); ?></strong>
			<?php esc_html_e( 'IAA lots show an em dash under Sale, not "no". Check the lot page for its sale event.', 'imanicars' ); ?></p>
		<p><strong><?php esc_html_e( 'Water damage fails PVoC at any age.', 'imanicars' ); ?></strong>
			<?php esc_html_e( 'The Kenya-eligible filter and the flood flag are separate checks. A lot can be inside the age window and still be rejected for water.', 'imanicars' ); ?></p>
	</div>

	<!-- ============================================================
	     SUMMARY — leads with the one thing a sale morning is about
	     ============================================================ -->
	<section class="sb-summary" aria-label="<?php esc_attr_e( 'Summary', 'imanicars' ); ?>">
		<div class="sb-lede">
			<div class="sb-lede__num"><?php echo (int) $c_48h; ?></div>
			<div class="sb-lede__body">
				<div class="sb-lede__label"><?php esc_html_e( 'lots sell in the next 48 hours', 'imanicars' ); ?></div>
				<div class="sb-lede__sub">
					<?php
					printf(
						esc_html__( '%1$d of them tomorrow. Counted from the %2$d lots that publish a sale time.', 'imanicars' ),
						(int) $c_tomorrow, (int) $c_timed
					);
					?>
					<?php if ( $c_untimed > 0 ) : ?>
						<span class="sb-lede__caveat">
							<?php
							printf(
								esc_html__( '%d further lots publish no sale time and are not in this count.', 'imanicars' ),
								(int) $c_untimed
							);
							?>
						</span>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<ul class="sb-counts">
			<li><span class="sb-counts__n"><?php echo count( $rows ); ?></span> <?php esc_html_e( 'lots shown', 'imanicars' ); ?></li>
			<li><span class="sb-counts__n"><?php echo (int) $c_kenya; ?></span> <?php esc_html_e( 'Kenya-eligible', 'imanicars' ); ?></li>
			<li><span class="sb-counts__n sb-counts__n--bad"><?php echo (int) $c_flood; ?></span> <?php esc_html_e( 'flood rejects', 'imanicars' ); ?></li>
			<li><span class="sb-counts__n sb-counts__n--bad"><?php echo (int) $c_epa; ?></span> <?php esc_html_e( 'need a VIC EPA licence', 'imanicars' ); ?></li>
			<li><span class="sb-counts__n"><?php echo (int) $totals['priced']; ?></span> <?php esc_html_e( 'hammer prices recorded', 'imanicars' ); ?></li>
		</ul>

		<?php if ( $summary ) : ?>
			<details class="sb-bymodel">
				<summary><?php esc_html_e( 'Hammer prices seen, by model', 'imanicars' ); ?></summary>
				<div class="sb-tablewrap">
					<table class="sb-table sb-table--compact">
						<thead>
							<tr>
								<th scope="col"><?php esc_html_e( 'Model', 'imanicars' ); ?></th>
								<th scope="col" class="sb-num"><?php esc_html_e( 'Lots', 'imanicars' ); ?></th>
								<th scope="col" class="sb-num"><?php esc_html_e( 'Priced', 'imanicars' ); ?></th>
								<th scope="col" class="sb-num"><?php esc_html_e( 'Low', 'imanicars' ); ?></th>
								<th scope="col" class="sb-num"><?php esc_html_e( 'Average', 'imanicars' ); ?></th>
								<th scope="col" class="sb-num"><?php esc_html_e( 'High', 'imanicars' ); ?></th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ( $summary as $s ) : ?>
							<tr>
								<th scope="row"><?php echo IC_Salvage_View::val( $s->model ); ?></th>
								<td class="sb-num"><?php echo (int) $s->lots; ?></td>
								<td class="sb-num"><?php echo (int) $s->priced; ?></td>
								<?php if ( (int) $s->priced > 0 ) : ?>
									<td class="sb-num"><?php echo esc_html( IC_Salvage_Money::format( (int) $s->min_hammer ) ); ?></td>
									<td class="sb-num"><?php echo esc_html( IC_Salvage_Money::format( (int) round( (float) $s->avg_hammer ) ) ); ?></td>
									<td class="sb-num"><?php echo esc_html( IC_Salvage_Money::format( (int) $s->max_hammer ) ); ?></td>
								<?php else : ?>
									<td class="sb-num" colspan="3"><span class="sb-unknown"><?php esc_html_e( 'no hammer prices recorded yet', 'imanicars' ); ?></span></td>
								<?php endif; ?>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</details>
		<?php endif; ?>
	</section>

	<!-- ============================================================
	     FILTERS
	     ============================================================ -->
	<form class="sb-filters" method="get" action="<?php echo esc_url( IC_Salvage_Access::board_url() ); ?>">
		<div class="sb-filters__grid">

			<div class="sb-field">
				<label for="sb-q"><?php esc_html_e( 'Search', 'imanicars' ); ?></label>
				<input type="search" id="sb-q" name="q" value="<?php echo esc_attr( $filters['search'] ); ?>" placeholder="<?php esc_attr_e( 'stock, model, location', 'imanicars' ); ?>">
			</div>

			<?php
			$selects = array(
				'source' => array( __( 'Auction house', 'imanicars' ), $facets['source'] ),
				'model'  => array( __( 'Model', 'imanicars' ), $facets['model'] ),
				'wovr'   => array( __( 'WOVR status', 'imanicars' ), $facets['wovr'] ),
				'state'  => array( __( 'State', 'imanicars' ), $facets['state'] ),
			);
			foreach ( $selects as $key => $cfg ) :
				list( $label, $options ) = $cfg;
				?>
				<div class="sb-field">
					<label for="sb-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
					<select id="sb-<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>[]" multiple size="4">
						<?php foreach ( (array) $options as $opt ) : ?>
							<option value="<?php echo esc_attr( $opt ); ?>" <?php selected( in_array( $opt, $filters[ $key ], true ) ); ?>>
								<?php echo esc_html( $opt ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endforeach; ?>

			<div class="sb-field sb-field--pair">
				<span class="sb-field__legend"><?php esc_html_e( 'Year', 'imanicars' ); ?></span>
				<label class="sb-sr" for="sb-year-min"><?php esc_html_e( 'Earliest year', 'imanicars' ); ?></label>
				<input type="number" id="sb-year-min" name="year_min" inputmode="numeric" min="1900" max="2100"
					placeholder="<?php echo esc_attr( $facets['years'] && $facets['years']->min_year ? $facets['years']->min_year : '1990' ); ?>"
					value="<?php echo esc_attr( null === $filters['year_min'] ? '' : $filters['year_min'] ); ?>">
				<span aria-hidden="true">–</span>
				<label class="sb-sr" for="sb-year-max"><?php esc_html_e( 'Latest year', 'imanicars' ); ?></label>
				<input type="number" id="sb-year-max" name="year_max" inputmode="numeric" min="1900" max="2100"
					placeholder="<?php echo esc_attr( $facets['years'] && $facets['years']->max_year ? $facets['years']->max_year : '2026' ); ?>"
					value="<?php echo esc_attr( null === $filters['year_max'] ? '' : $filters['year_max'] ); ?>">
			</div>

			<div class="sb-field sb-field--pair">
				<span class="sb-field__legend"><?php esc_html_e( 'Sale date', 'imanicars' ); ?></span>
				<label class="sb-sr" for="sb-sale-from"><?php esc_html_e( 'Sale date from', 'imanicars' ); ?></label>
				<input type="date" id="sb-sale-from" name="sale_from" value="<?php echo esc_attr( (string) $filters['sale_from'] ); ?>">
				<span aria-hidden="true">–</span>
				<label class="sb-sr" for="sb-sale-to"><?php esc_html_e( 'Sale date to', 'imanicars' ); ?></label>
				<input type="date" id="sb-sale-to" name="sale_to" value="<?php echo esc_attr( (string) $filters['sale_to'] ); ?>">
			</div>

			<div class="sb-field sb-field--checks">
				<label class="sb-check">
					<input type="checkbox" name="kenya_only" value="1" <?php checked( $filters['kenya_only'] ); ?>>
					<span><?php esc_html_e( 'Kenya-eligible only', 'imanicars' ); ?>
						<small><?php esc_html_e( 'KEBS age window, proven — excludes lots whose eligibility is unknown', 'imanicars' ); ?></small>
					</span>
				</label>
				<label class="sb-check">
					<input type="checkbox" name="exclude_flood" value="1" <?php checked( $filters['exclude_flood'] ); ?>>
					<span><?php esc_html_e( 'Hide flood rejects', 'imanicars' ); ?>
						<small><?php esc_html_e( 'Water damage fails PVoC at any age', 'imanicars' ); ?></small>
					</span>
				</label>
			</div>
		</div>

		<div class="sb-filters__actions">
			<button type="submit" class="sb-btn sb-btn--primary"><?php esc_html_e( 'Apply filters', 'imanicars' ); ?></button>
			<a class="sb-btn" href="<?php echo esc_url( IC_Salvage_Access::board_url() ); ?>"><?php esc_html_e( 'Clear', 'imanicars' ); ?></a>
		</div>
	</form>

	<!-- ============================================================
	     ACTIONS
	     ============================================================ -->
	<div class="sb-actions">
		<a class="sb-btn" href="<?php echo esc_url( $export_url ); ?>"><?php esc_html_e( 'Export to Excel (CSV)', 'imanicars' ); ?></a>
		<button type="button" class="sb-btn" data-sb-copy><?php esc_html_e( 'Copy table', 'imanicars' ); ?></button>
		<button type="button" class="sb-btn" data-sb-print><?php esc_html_e( 'Print / Save as PDF', 'imanicars' ); ?></button>
		<a class="sb-btn" id="sb-email" href="#"><?php esc_html_e( 'Email', 'imanicars' ); ?></a>
		<span class="sb-actions__status" data-sb-status role="status" aria-live="polite"></span>
	</div>

	<!-- ============================================================
	     THE BOARD
	     ============================================================ -->
	<?php if ( ! $rows ) : ?>
		<div class="sb-empty">
			<?php if ( 0 === (int) $totals['lots'] ) : ?>
				<h2><?php esc_html_e( 'No scan has been imported yet', 'imanicars' ); ?></h2>
				<p><?php esc_html_e( 'Convert the auction workbooks with tools/xlsx_to_salvage_json.py, then upload the file at Tools → Salvage import in wp-admin.', 'imanicars' ); ?></p>
			<?php elseif ( IC_Salvage_View::has_active_filters( $filters ) ) : ?>
				<h2><?php esc_html_e( 'No lots match these filters', 'imanicars' ); ?></h2>
				<p>
					<?php
					printf(
						esc_html__( '%d lots are stored. Clear the filters to see them.', 'imanicars' ),
						(int) $totals['lots']
					);
					?>
					<?php if ( $filters['kenya_only'] ) : ?>
						<br><?php esc_html_e( 'Note: "Kenya-eligible only" admits just the lots proven inside the KEBS age window. Lots whose damage was never published count as unknown, not eligible — Pickles publishes no damage codes at all.', 'imanicars' ); ?>
					<?php endif; ?>
				</p>
				<p><a class="sb-btn" href="<?php echo esc_url( IC_Salvage_Access::board_url() ); ?>"><?php esc_html_e( 'Clear filters', 'imanicars' ); ?></a></p>
			<?php else : ?>
				<h2><?php esc_html_e( 'No lots to show', 'imanicars' ); ?></h2>
			<?php endif; ?>
		</div>
	<?php else : ?>

	<div class="sb-tablewrap">
	<table class="sb-table" id="sb-board">
		<caption class="sb-sr">
			<?php
			printf(
				esc_html__( 'Salvage lots. %d rows. Sale times in %s.', 'imanicars' ),
				count( $rows ),
				esc_html( $tzn )
			);
			?>
		</caption>
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Sale', 'imanicars' ); ?></th>
				<th scope="col"><?php esc_html_e( 'House', 'imanicars' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Stock', 'imanicars' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Vehicle', 'imanicars' ); ?></th>
				<th scope="col"><?php esc_html_e( 'WOVR', 'imanicars' ); ?></th>
				<th scope="col" class="sb-num"><?php esc_html_e( 'Odometer', 'imanicars' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Damage', 'imanicars' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Screening', 'imanicars' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Assessment', 'imanicars' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Status', 'imanicars' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Price', 'imanicars' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php
		foreach ( $rows as $r ) :
			$in48     = IC_Salvage_SaleDate::sells_within_hours( $r->sale_datetime, $now, 48, $tzn );
			$tomorrow = IC_Salvage_SaleDate::sells_tomorrow( $r->sale_datetime, $now, $tzn );
			$past     = IC_Salvage_SaleDate::is_past( $r->sale_datetime, $now, $tzn );
			$quote    = IC_Salvage_Fees::quote( $r->source, $r->latest_hammer_cents );
			$warnings = IC_Salvage_View::json_list( $r->data_warnings );
			$reasons  = IC_Salvage_View::json_list( $r->kebs_reasons );
			$rid      = 'lot-' . (int) $r->id;

			$classes = array( 'sb-row' );
			if ( 1 === (int) $r->flood_pvoc_reject ) { $classes[] = 'sb-row--rejected'; }
			if ( true === $past ) { $classes[] = 'sb-row--past'; }
			?>
			<tr class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" id="<?php echo esc_attr( $rid ); ?>">

				<td data-label="<?php esc_attr_e( 'Sale', 'imanicars' ); ?>">
					<?php if ( $r->sale_datetime ) : ?>
						<div class="sb-sale__date"><?php echo esc_html( wp_date( 'D j M, g:ia', strtotime( $r->sale_datetime . ' ' . $tzn ) ) ); ?></div>
						<div class="sb-sale__flags">
							<?php if ( true === $past ) : ?>
								<span class="sb-flag sb-flag--off"><?php esc_html_e( 'passed', 'imanicars' ); ?></span>
							<?php else : ?>
								<?php if ( true === $tomorrow ) : ?>
									<span class="sb-flag sb-flag--urgent"><?php esc_html_e( 'TOMORROW', 'imanicars' ); ?></span>
								<?php elseif ( true === $in48 ) : ?>
									<span class="sb-flag sb-flag--warn"><?php esc_html_e( '48h', 'imanicars' ); ?></span>
								<?php endif; ?>
							<?php endif; ?>
						</div>
					<?php else : ?>
						<span class="sb-unknown" title="<?php echo esc_attr( $r->sale_time_note ? $r->sale_time_note : __( 'No sale time published.', 'imanicars' ) ); ?>">&mdash;</span>
						<div class="sb-cell-note"><?php esc_html_e( 'no sale time published', 'imanicars' ); ?></div>
					<?php endif; ?>
				</td>

				<td data-label="<?php esc_attr_e( 'House', 'imanicars' ); ?>"><?php echo esc_html( $r->source ); ?></td>

				<td data-label="<?php esc_attr_e( 'Stock', 'imanicars' ); ?>">
					<?php if ( $r->detail_url ) : ?>
						<a class="sb-stock" href="<?php echo esc_url( $r->detail_url ); ?>" target="_blank" rel="noopener noreferrer nofollow">
							<?php echo esc_html( $r->stock ); ?>
							<span class="sb-sr"><?php esc_html_e( '(opens the lot page in a new tab)', 'imanicars' ); ?></span>
						</a>
						<div class="sb-cell-note"><?php esc_html_e( 'photos on the lot page', 'imanicars' ); ?></div>
					<?php else : ?>
						<span class="sb-stock"><?php echo esc_html( $r->stock ); ?></span>
						<div class="sb-cell-note"><?php esc_html_e( 'no lot link published', 'imanicars' ); ?></div>
					<?php endif; ?>
					<?php if ( $r->lot_ref ) : ?>
						<div class="sb-cell-note"><?php echo esc_html( $r->lot_ref ); ?></div>
					<?php endif; ?>
				</td>

				<td data-label="<?php esc_attr_e( 'Vehicle', 'imanicars' ); ?>">
					<div class="sb-vehicle">
						<?php echo esc_html( trim( ( $r->year ? $r->year . ' ' : '' ) . ( $r->make ? $r->make . ' ' : '' ) . (string) $r->model ) ); ?>
					</div>
					<?php if ( $r->variant ) : ?>
						<div class="sb-cell-note"><?php echo esc_html( $r->variant ); ?></div>
					<?php endif; ?>
					<?php if ( ! $r->year ) : ?>
						<div class="sb-cell-note sb-cell-note--warn"><?php esc_html_e( 'year not published', 'imanicars' ); ?></div>
					<?php endif; ?>
					<?php foreach ( $warnings as $w ) : ?>
						<div class="sb-cell-warn"><?php echo esc_html( $w ); ?></div>
					<?php endforeach; ?>
				</td>

				<td data-label="<?php esc_attr_e( 'WOVR', 'imanicars' ); ?>"><?php echo IC_Salvage_View::val( $r->wovr ); ?></td>

				<td data-label="<?php esc_attr_e( 'Odometer', 'imanicars' ); ?>" class="sb-num">
					<?php echo IC_Salvage_View::num( $r->odometer_km, ' km', __( 'Odometer not published for this lot.', 'imanicars' ) ); ?>
				</td>

				<td data-label="<?php esc_attr_e( 'Damage', 'imanicars' ); ?>">
					<?php if ( 1 === (int) $r->damage_published ) : ?>
						<?php echo IC_Salvage_View::val( $r->primary_damage ); ?>
						<?php if ( $r->secondary_damage ) : ?>
							<div class="sb-cell-note"><?php echo esc_html( $r->secondary_damage ); ?></div>
						<?php endif; ?>
					<?php else : ?>
						<span class="sb-unknown" title="<?php esc_attr_e( 'This auction house does not publish damage codes in the feed.', 'imanicars' ); ?>">&mdash;</span>
						<div class="sb-cell-note"><?php esc_html_e( 'not published', 'imanicars' ); ?></div>
					<?php endif; ?>
				</td>

				<td data-label="<?php esc_attr_e( 'Screening', 'imanicars' ); ?>">
					<div class="sb-screen">
						<?php
						echo IC_Salvage_View::flag(
							$r->kebs_eligible,
							__( 'KEBS ok', 'imanicars' ),
							__( 'Not KEBS', 'imanicars' ),
							'good',
							$reasons ? implode( ' ', $reasons ) : __( 'Eligibility could not be determined from the published fields.', 'imanicars' )
						);
						?>
						<?php if ( 1 === (int) $r->flood_pvoc_reject ) : ?>
							<span class="sb-flag sb-flag--bad" title="<?php esc_attr_e( 'Water damage fails PVoC at any age.', 'imanicars' ); ?>"><?php esc_html_e( 'FLOOD — reject', 'imanicars' ); ?></span>
						<?php endif; ?>
						<?php if ( 1 === (int) $r->vic_statutory_epa ) : ?>
							<span class="sb-flag sb-flag--bad" title="<?php echo esc_attr( IC_Salvage_Rules::VIC_EPA_NOTE ); ?>"><?php esc_html_e( 'VIC EPA licence', 'imanicars' ); ?></span>
						<?php endif; ?>
					</div>
					<?php if ( null === $r->kebs_eligible && $reasons ) : ?>
						<div class="sb-cell-note sb-cell-note--warn"><?php echo esc_html( $reasons[0] ); ?></div>
					<?php endif; ?>
				</td>

				<td data-label="<?php esc_attr_e( 'Assessment', 'imanicars' ); ?>">
					<?php if ( $r->verdict ) : ?>
						<span class="sb-verdict sb-verdict--<?php echo esc_attr( strtolower( $r->verdict ) ); ?>"><?php echo esc_html( $r->verdict ); ?></span>
						<?php if ( $r->est_repair ) : ?>
							<div class="sb-cell-note"><?php esc_html_e( 'repair', 'imanicars' ); ?> <?php echo esc_html( $r->est_repair ); ?></div>
						<?php endif; ?>
						<?php if ( $r->est_kenya_resale ) : ?>
							<div class="sb-cell-note"><?php esc_html_e( 'KE resale', 'imanicars' ); ?> <?php echo esc_html( $r->est_kenya_resale ); ?></div>
						<?php endif; ?>
						<?php if ( $r->high_pre_bid ) : ?>
							<div class="sb-cell-note"><?php esc_html_e( 'pre-bid', 'imanicars' ); ?> <?php echo esc_html( $r->high_pre_bid ); ?></div>
						<?php endif; ?>
					<?php else : ?>
						<span class="sb-unknown" title="<?php esc_attr_e( 'This lot has not been through a deep valuation.', 'imanicars' ); ?>">&mdash;</span>
						<div class="sb-cell-note"><?php esc_html_e( 'not valued', 'imanicars' ); ?></div>
					<?php endif; ?>
				</td>

				<td data-label="<?php esc_attr_e( 'Status', 'imanicars' ); ?>">
					<span class="sb-status sb-status--<?php echo esc_attr( IC_Salvage_View::status_tone( $r ) ); ?>" data-sb-status-for="<?php echo (int) $r->id; ?>">
						<?php echo esc_html( IC_Salvage_View::status_label( $r ) ); ?>
					</span>
					<?php if ( (int) $r->observation_count > 0 ) : ?>
						<div class="sb-cell-note">
							<?php
							printf(
								esc_html( _n( '%d observation', '%d observations', (int) $r->observation_count, 'imanicars' ) ),
								(int) $r->observation_count
							);
							?>
						</div>
					<?php endif; ?>
				</td>

				<td data-label="<?php esc_attr_e( 'Price', 'imanicars' ); ?>" class="sb-pricecell">
					<div class="sb-price" data-sb-price-for="<?php echo (int) $r->id; ?>">
						<?php if ( null !== $r->latest_hammer_cents ) : ?>
							<div class="sb-price__hammer"><?php echo esc_html( IC_Salvage_Money::format( (int) $r->latest_hammer_cents ) ); ?></div>
							<?php if ( null !== $quote['landed_before_duty'] ) : ?>
								<div class="sb-price__landed">
									<?php echo esc_html( IC_Salvage_Money::format( $quote['landed_before_duty'] ) ); ?>
									<span class="sb-price__qualifier"><?php esc_html_e( 'all-in, before duty', 'imanicars' ); ?></span>
								</div>
								<?php if ( $quote['partial'] ) : ?>
									<div class="sb-cell-warn"><?php echo esc_html( $quote['caveats'][0] ); ?></div>
								<?php endif; ?>
							<?php else : ?>
								<div class="sb-price__notfound"><?php esc_html_e( 'not found', 'imanicars' ); ?></div>
								<div class="sb-cell-note"><?php echo esc_html( $quote['unavailable_reason'] ); ?></div>
							<?php endif; ?>
						<?php else : ?>
							<div class="sb-price__none"><?php esc_html_e( 'no price recorded', 'imanicars' ); ?></div>
						<?php endif; ?>
					</div>

					<details class="sb-entry">
						<summary class="sb-entry__toggle"><?php esc_html_e( 'Record', 'imanicars' ); ?></summary>
						<form class="sb-entry__form" data-sb-form data-lot="<?php echo (int) $r->id; ?>" data-source="<?php echo esc_attr( $r->source ); ?>" method="post">
							<div class="sb-entry__row">
								<label for="sb-status-<?php echo (int) $r->id; ?>"><?php esc_html_e( 'Status', 'imanicars' ); ?></label>
								<select id="sb-status-<?php echo (int) $r->id; ?>" name="status" required>
									<?php foreach ( IC_Salvage_Repo::statuses() as $st ) : ?>
										<option value="<?php echo esc_attr( $st ); ?>" <?php selected( $r->current_status, $st ); ?>><?php echo esc_html( $st ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="sb-entry__row">
								<label for="sb-hammer-<?php echo (int) $r->id; ?>"><?php esc_html_e( 'Hammer (AUD)', 'imanicars' ); ?></label>
								<input type="text" id="sb-hammer-<?php echo (int) $r->id; ?>" name="hammer"
									inputmode="decimal" autocomplete="off" placeholder="8500"
									data-sb-hammer>
							</div>
							<div class="sb-entry__quote" data-sb-quote aria-live="polite">
								<?php if ( 'Manheim' === IC_Salvage_Fees::normalise_source( $r->source ) ) : ?>
									<span class="sb-price__notfound"><?php esc_html_e( 'Buyer fee: not found', 'imanicars' ); ?></span>
									<span class="sb-cell-note"><?php esc_html_e( 'Manheim does not publish a retrievable fee schedule.', 'imanicars' ); ?></span>
								<?php else : ?>
									<span class="sb-cell-note"><?php esc_html_e( 'Buyer fee and all-in cost are calculated when you type a hammer price.', 'imanicars' ); ?></span>
								<?php endif; ?>
							</div>
							<div class="sb-entry__row">
								<label for="sb-notes-<?php echo (int) $r->id; ?>"><?php esc_html_e( 'Notes', 'imanicars' ); ?></label>
								<input type="text" id="sb-notes-<?php echo (int) $r->id; ?>" name="notes" autocomplete="off">
							</div>
							<div class="sb-entry__actions">
								<button type="submit" class="sb-btn sb-btn--primary sb-btn--big"><?php esc_html_e( 'Save observation', 'imanicars' ); ?></button>
								<span class="sb-entry__msg" data-sb-msg role="status" aria-live="polite"></span>
							</div>
							<p class="sb-entry__foot"><?php esc_html_e( 'Saved as a dated observation. Earlier entries are kept — the price history is the point.', 'imanicars' ); ?></p>
						</form>
					</details>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	</div>

	<p class="sb-foot">
		<?php
		printf(
			esc_html__( 'Sale times shown in %s. %s', 'imanicars' ),
			esc_html( $tzn ),
			esc_html( IC_Salvage_SaleDate::TZ_NOTE )
		);
		?>
	</p>

	<?php endif; ?>

</div>
</div>

<?php
get_footer();
