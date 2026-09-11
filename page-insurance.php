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

// Bring the schema up to date from the board itself. maybe_upgrade() otherwise
// runs only on admin_init, so a deploy followed by a visit straight here — which
// is exactly how Rop reaches this page — would query columns that do not exist
// yet. It is one get_option() when there is nothing to do.
IC_Salvage_Schema::maybe_upgrade();

$filters   = IC_Salvage_View::filters_from_request();
$tables_ok = IC_Salvage_Schema::tables_exist();
// If the upgrade could not run (an older DB user without ALTER, say), the book
// columns are missing and the SQL book filters cannot work. Say so rather than
// returning an empty board that looks like "no lots match".
$books_ok  = $tables_ok && IC_Salvage_Schema::lots_column_exists( 'book_kenya' );
if ( ! $books_ok ) {
	$filters['book'] = array();
}
$rows      = $tables_ok ? IC_Salvage_Repo::query( $filters ) : array();
$facets    = $tables_ok ? IC_Salvage_Repo::facets() : array( 'source' => array(), 'model' => array(), 'wovr' => array(), 'state' => array(), 'years' => null );
$totals    = $tables_ok ? IC_Salvage_Repo::totals() : array( 'lots' => 0, 'observations' => 0, 'priced' => 0, 'scan_date' => null, 'sources' => 0 );
$summary   = $tables_ok ? IC_Salvage_Repo::summary( $filters ) : array();

$tz  = wp_timezone();
$now = ( new DateTimeImmutable( 'now', $tz ) )->format( 'Y-m-d H:i:s' );
$tzn = $tz->getName();

// The calendar year every book band is measured against. Read once, from the
// Australian clock, and used for every row — so a page rendered across midnight
// on 31 December cannot put half its rows in one year's bands and half in the
// next.
$cy = IC_Salvage_Books::calendar_year();

// Books are RECOMPUTED here rather than read from the stored columns. The stored
// columns are a filter index for SQL; they were computed against whatever year
// the import ran in, and every band moves on 1 January. What Rop reads is
// computed now.
$assessed  = array();
$book_keys = array( 'rental', 'uganda', 'kenya' );

// Counts computed from the rows on screen, so the headline cannot disagree with
// the table beneath it.
$c_48h = 0; $c_tomorrow = 0; $c_timed = 0; $c_untimed = 0; $c_kenya = 0; $c_flood = 0; $c_epa = 0;
$c_book  = array( 'kenya' => 0, 'uganda' => 0, 'rental' => 0 );
$c_stale = 0;
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

	$a = IC_Salvage_Books::assess( IC_Salvage_Books::from_row( $r ), $cy );
	$assessed[ (int) $r->id ] = $a;
	if ( true === $a['kenya_ok'] )     { $c_book['kenya']++; }
	if ( true === $a['uganda_ok'] )    { $c_book['uganda']++; }
	if ( true === $a['au_rental_ok'] ) { $c_book['rental']++; }

	// Does the stored filter index still agree with the live rule? A mismatch
	// means the SQL book checkboxes are filtering on something other than what
	// this page displays, and that must be visible rather than inferred.
	if ( $books_ok && isset( $r->book_cy ) && null !== $r->book_cy ) {
		$stored = array( (int) $r->book_kenya, (int) $r->book_uganda, (int) $r->book_rental );
		$live   = array(
			true === $a['kenya_ok'] ? 1 : 0,
			true === $a['uganda_ok'] ? 1 : 0,
			true === $a['au_rental_ok'] ? 1 : 0,
		);
		if ( $stored !== $live ) { $c_stale++; }
	}
}

// Stale when the index was built for an earlier year, or when any row's stored
// flags no longer match the live rule.
$book_index_stale = ( ! empty( $totals['book_cy_min'] ) && (int) $totals['book_cy_min'] < $cy ) || $c_stale > 0;

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

	<?php if ( $tables_ok && ! $books_ok ) : ?>
		<div class="sb-alert sb-alert--warn">
			<strong><?php esc_html_e( 'The book columns are not in the database yet.', 'imanicars' ); ?></strong>
			<?php esc_html_e( 'The three-book badges below are computed live and are correct, but the book filter CHECKBOXES are hidden because there is nothing in SQL to filter on. Open wp-admin once to let the schema upgrade run, then reload this page.', 'imanicars' ); ?>
		</div>
	<?php endif; ?>

	<?php if ( $tables_ok && $books_ok && $book_index_stale ) : ?>
		<div class="sb-alert sb-alert--warn">
			<strong><?php esc_html_e( 'The book filter index is out of date.', 'imanicars' ); ?></strong>
			<?php
			if ( ! empty( $totals['book_cy_min'] ) && (int) $totals['book_cy_min'] < $cy ) {
				printf(
					esc_html__( 'Stored book flags were computed against %1$d and it is now %2$d — every band has moved a year. ', 'imanicars' ),
					(int) $totals['book_cy_min'], (int) $cy
				);
			}
			if ( $c_stale > 0 ) {
				printf(
					esc_html( _n( '%d row on this page no longer matches its stored flags. ', '%d rows on this page no longer match their stored flags. ', (int) $c_stale, 'imanicars' ) ),
					(int) $c_stale
				);
			}
			esc_html_e( 'The badges and counts below are recomputed live and are correct. The book CHECKBOXES filter in SQL against the stored flags, so they may include or omit the wrong lots until the scan is re-imported.', 'imanicars' );
			?>
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
			<?php foreach ( $book_keys as $bk ) : ?>
				<li>
					<span class="sb-counts__n"><?php echo (int) $c_book[ $bk ]; ?></span>
					<?php echo esc_html( IC_Salvage_Books::label( $bk ) ); ?>
					<small class="sb-counts__band"><?php echo esc_html( IC_Salvage_Books::band_label( $bk, $cy ) ); ?></small>
				</li>
			<?php endforeach; ?>
			<li><span class="sb-counts__n sb-counts__n--bad"><?php echo (int) $c_flood; ?></span> <?php esc_html_e( 'flood rejects', 'imanicars' ); ?></li>
			<li><span class="sb-counts__n sb-counts__n--bad"><?php echo (int) $c_epa; ?></span> <?php esc_html_e( 'need a VIC EPA licence', 'imanicars' ); ?></li>
			<li><span class="sb-counts__n"><?php echo (int) $totals['priced']; ?></span> <?php esc_html_e( 'hammer prices recorded', 'imanicars' ); ?></li>
		</ul>
		<p class="sb-counts__foot">
			<?php
			printf(
				esc_html__( 'Book counts are computed now, against %d. They count only lots PROVEN inside a band — a lot whose year or damage was never published counts in none of the three, and is not the same as a lot that was screened out.', 'imanicars' ),
				(int) $cy
			);
			?>
		</p>

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

			<?php if ( $books_ok ) : ?>
			<div class="sb-field sb-field--checks sb-field--books">
				<span class="sb-field__legend"><?php esc_html_e( 'Destination book', 'imanicars' ); ?></span>
				<?php
				$book_hints = array(
					'rental' => __( 'WOVR N/A only. PPSR mandatory — this band is below every state\'s recording threshold.', 'imanicars' ),
					'uganda' => __( 'Levy is on URA\'s valuation, not on what you pay. Only the oldest year in the band pays 20% instead of 50%.', 'imanicars' ),
					'kenya'  => __( 'KEBS KS 1515 age window. Water fails PVoC at any age, separately.', 'imanicars' ),
				);
				foreach ( $book_keys as $bk ) :
					?>
					<label class="sb-check">
						<input type="checkbox" name="book[]" value="<?php echo esc_attr( $bk ); ?>" <?php checked( in_array( $bk, $filters['book'], true ) ); ?>>
						<span><?php echo esc_html( IC_Salvage_Books::label( $bk ) ); ?>
							<code class="sb-band"><?php echo esc_html( IC_Salvage_Books::band_label( $bk, $cy ) ); ?></code>
							<small><?php echo esc_html( $book_hints[ $bk ] ); ?></small>
						</span>
					</label>
				<?php endforeach; ?>
				<p class="sb-field__foot">
					<?php esc_html_e( 'The three bands are disjoint, so no lot is ever in two. Ticking several shows anything one of the three buyers can take. Each admits only lots proven inside its band — an unknown is not an eligible.', 'imanicars' ); ?>
				</p>
			</div>
			<?php endif; ?>

			<div class="sb-field sb-field--checks">
				<label class="sb-check">
					<input type="checkbox" name="kenya_only" value="1" <?php checked( $filters['kenya_only'] ); ?>>
					<span><?php esc_html_e( 'KEBS age window only', 'imanicars' ); ?>
						<small><?php esc_html_e( 'The imported KEBS column. Narrower than the Kenya book above, which also screens water.', 'imanicars' ); ?></small>
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
		<span class="sb-actions__status" data-sb-status role="status" aria-live="polite"></span>
	</div>

	<?php if ( $rows && IC_Salvage_View::has_active_filters( $filters ) ) : ?>
	<details class="sb-dangerbox">
		<summary class="sb-btn sb-btn--danger"><?php esc_html_e( 'Delete the lots in this view', 'imanicars' ); ?></summary>
		<form class="sb-dangerbox__form" data-sb-delete method="post">
			<p class="sb-dangerbox__lede">
				<?php
				printf(
					esc_html( _n( '%d lot is in the current view.', '%d lots are in the current view.', count( $rows ), 'imanicars' ) ),
					count( $rows )
				);
				?>
				<?php esc_html_e( 'Deleting is permanent and this board cannot undo it.', 'imanicars' ); ?>
			</p>
			<p class="sb-dangerbox__lede">
				<?php esc_html_e( 'Hiding is usually what you want instead: set a sale date and past lots drop out of view while their prices stay. A lot that sold last week is the comparison this week\'s bid gets judged against.', 'imanicars' ); ?>
			</p>
			<label class="sb-check">
				<input type="checkbox" name="include_observed" value="1" data-sb-delete-obs>
				<span><?php esc_html_e( 'Also delete lots that have recorded prices', 'imanicars' ); ?>
					<small><?php esc_html_e( 'Off by default. Leave it off and priced lots are kept even if they match the filter.', 'imanicars' ); ?></small>
				</span>
			</label>
			<div class="sb-dangerbox__actions">
				<button type="submit" class="sb-btn" data-sb-delete-check><?php esc_html_e( 'Check what would go', 'imanicars' ); ?></button>
				<button type="button" class="sb-btn sb-btn--danger" data-sb-delete-go hidden><?php esc_html_e( 'Yes, delete them', 'imanicars' ); ?></button>
			</div>
			<p class="sb-dangerbox__msg" data-sb-delete-msg role="status" aria-live="polite"></p>
		</form>
	</details>
	<?php endif; ?>

	<details class="sb-emailbox">
		<summary class="sb-btn"><?php esc_html_e( 'Email these results', 'imanicars' ); ?></summary>
		<form class="sb-emailbox__form" data-sb-email method="post">
			<label for="sb-email-to"><?php esc_html_e( 'Send to', 'imanicars' ); ?></label>
			<input type="email" id="sb-email-to" name="to" autocomplete="email" required
				value="<?php echo esc_attr( wp_get_current_user()->user_email ); ?>">
			<button type="submit" class="sb-btn sb-btn--primary">
				<?php
				printf(
					esc_html( _n( 'Send %d lot', 'Send %d lots', count( $rows ), 'imanicars' ) ),
					count( $rows )
				);
				?>
			</button>
			<p class="sb-emailbox__msg" data-sb-email-msg role="status" aria-live="polite"></p>
			<p class="sb-emailbox__foot">
				<?php esc_html_e( 'Sends the rows currently on screen, with the same CSV the Export button produces, attached. Private and proprietary — do not forward it to anyone bidding at the same auctions.', 'imanicars' ); ?>
			</p>
		</form>
	</details>

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
				<th scope="col"><?php esc_html_e( 'Book', 'imanicars' ); ?></th>
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
			// Recomputed above, against today's calendar year — never read from
			// the stored filter index.
			$book_assess = $assessed[ (int) $r->id ];

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

				<td data-label="<?php esc_attr_e( 'Book', 'imanicars' ); ?>">
					<?php
					$bk_map = array(
						'rental' => $book_assess['au_rental_ok'],
						'uganda' => $book_assess['uganda_ok'],
						'kenya'  => $book_assess['kenya_ok'],
					);
					$in_a_book = false;
					foreach ( $bk_map as $bk => $ok ) :
						if ( true !== $ok ) { continue; }
						$in_a_book = true;
						$why = implode( ' ', (array) $book_assess['reasons'][ 'rental' === $bk ? 'au_rental' : $bk ] );
						?>
						<span class="sb-book sb-book--<?php echo esc_attr( $bk ); ?>" title="<?php echo esc_attr( $why ); ?>">
							<?php echo esc_html( IC_Salvage_Books::label( $bk ) ); ?>
						</span>
					<?php endforeach; ?>

					<?php if ( ! $in_a_book ) : ?>
						<?php
						// UNKNOWN and OUT are different answers and must not share a
						// rendering. "No buyer" reads as a decision; it is only a
						// decision when the fields it needs were actually published.
						$unknown_books = array();
						foreach ( $bk_map as $bk => $ok ) {
							if ( null === $ok ) { $unknown_books[] = IC_Salvage_Books::label( $bk ); }
						}
						?>
						<?php if ( $unknown_books ) : ?>
							<span class="sb-flag sb-flag--unknown" title="<?php echo esc_attr( implode( ' ', (array) $book_assess['reasons']['kenya'] ) ); ?>">&mdash;</span>
							<div class="sb-cell-note sb-cell-note--warn">
								<?php
								printf(
									esc_html__( 'undecided for %s — a field it needs was not published', 'imanicars' ),
									esc_html( implode( ', ', $unknown_books ) )
								);
								?>
							</div>
						<?php else : ?>
							<span class="sb-flag sb-flag--off"><?php esc_html_e( 'no buyer', 'imanicars' ); ?></span>
							<div class="sb-cell-note"><?php esc_html_e( 'outside all three bands', 'imanicars' ); ?></div>
						<?php endif; ?>
					<?php endif; ?>

					<?php
					// Book flags that change what a lot costs or what it needs. These
					// are the ones worth money, so they are text on the row, not a
					// tooltip.
					$flag_notes = array(
						'uganda_levy_50pct' => __( '50% URA levy', 'imanicars' ),
						'uganda_levy_20pct' => __( '20% URA levy', 'imanicars' ),
						'uganda_levy_nil'   => __( 'no URA levy', 'imanicars' ),
						'ppsr_mandatory'    => __( 'PPSR mandatory', 'imanicars' ),
					);
					foreach ( (array) $book_assess['flags'] as $bf ) :
						if ( 0 === strpos( $bf, 'uganda_boundary_contested_' ) ) : ?>
							<div class="sb-cell-warn"><?php esc_html_e( 'contested boundary year — confirm with URA before committing', 'imanicars' ); ?></div>
						<?php elseif ( isset( $flag_notes[ $bf ] ) ) : ?>
							<div class="sb-cell-note"><?php echo esc_html( $flag_notes[ $bf ] ); ?></div>
						<?php endif;
					endforeach;
					?>
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

					<?php
					// ESTIMATE vs ACTUAL — the whole point of a three-week watch.
					// Compared against the pre-bid ceiling, because that is the
					// number a bid decision was actually made against. Both sides
					// must be known; one known side is not a small variance.
					$cmp     = IC_Salvage_Estimate::compare( $r->high_pre_bid, $r->latest_hammer_cents );
					$est_str = IC_Salvage_Estimate::format_estimate( $cmp );
					$var_str = IC_Salvage_Estimate::format_variance( $cmp );
					?>
					<div class="sb-vs">
						<?php if ( null !== $est_str ) : ?>
							<div class="sb-vs__row">
								<span class="sb-vs__label"><?php esc_html_e( 'Estimate', 'imanicars' ); ?></span>
								<span class="sb-vs__est"><?php echo esc_html( $est_str ); ?></span>
							</div>
							<?php if ( null !== $var_str ) : ?>
								<div class="sb-vs__row sb-vs__row--<?php echo esc_attr( $cmp['direction'] ); ?>">
									<span class="sb-vs__label"><?php esc_html_e( 'Actual vs estimate', 'imanicars' ); ?></span>
									<span class="sb-vs__delta"><?php echo esc_html( $var_str ); ?></span>
								</div>
								<?php if ( $cmp['is_range'] && $cmp['reason'] ) : ?>
									<div class="sb-cell-note"><?php echo esc_html( $cmp['reason'] ); ?></div>
								<?php endif; ?>
							<?php else : ?>
								<div class="sb-cell-note"><?php echo esc_html( (string) $cmp['reason'] ); ?></div>
							<?php endif; ?>
						<?php elseif ( null !== $cmp['reason'] ) : ?>
							<div class="sb-vs__row">
								<span class="sb-vs__label"><?php esc_html_e( 'Estimate', 'imanicars' ); ?></span>
								<span class="sb-unknown">&mdash;</span>
							</div>
							<div class="sb-cell-note"><?php echo esc_html( $cmp['reason'] ); ?></div>
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
