<?php
/**
 * Archive Template: Vehicle Listings — Imani Cars
 * Grid of car cards + sidebar filters + pagination
 */
get_header();
$current_city = sanitize_text_field( isset( $_GET['city'] ) ? $_GET['city'] : '' );
$current_make = sanitize_text_field( isset( $_GET['make'] ) ? $_GET['make'] : '' );
$condition    = sanitize_text_field( isset( $_GET['condition'] ) ? $_GET['condition'] : '' );
$seller       = sanitize_text_field( isset( $_GET['seller'] ) ? $_GET['seller'] : '' );
$body_type    = sanitize_text_field( isset( $_GET['body_type'] ) ? $_GET['body_type'] : '' );
$price_min    = absint( isset( $_GET['price_min'] ) ? $_GET['price_min'] : 0 );
$price_max    = absint( isset( $_GET['price_max'] ) ? $_GET['price_max'] : 0 );
?>

<div class="ic-archive-page">
  <div class="container">

    <!-- PAGE HEADER -->
    <div class="ic-archive-header">
      <h1 class="ic-archive-header__title">
        <?php
        $parts = [];
        if ( $current_make ) $parts[] = esc_html( $current_make );
        $parts[] = esc_html__( 'Cars for Sale', 'imanicars' );
        if ( $current_city ) $parts[] = esc_html( 'in ' . ucfirst( $current_city ) );
        echo implode( ' ', $parts );
        ?>
      </h1>
      <p class="ic-archive-header__count">
        <?php
        global $wp_query;
        $total = $wp_query->found_posts;
        /* translators: %d: number of listings */
        printf( esc_html__( '%d listings found', 'imanicars' ), (int) $total );
        ?>
      </p>
    </div>

    <!-- BREADCRUMB -->
    <nav class="ic-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'imanicars' ); ?>">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'imanicars' ); ?></a>
      <span aria-hidden="true"> &rsaquo; </span>
      <span aria-current="page"><?php esc_html_e( 'Cars for Sale', 'imanicars' ); ?></span>
    </nav>

    <div class="ic-archive-layout">

      <!-- SIDEBAR FILTERS -->
      <aside class="ic-archive-sidebar" aria-label="<?php esc_attr_e( 'Filter listings', 'imanicars' ); ?>">
        <div class="ic-filter-card">
          <h2 class="ic-filter-card__heading"><?php esc_html_e( 'Filter Results', 'imanicars' ); ?></h2>
          <form class="ic-filter-form" method="get" action="<?php echo esc_url( home_url( '/cars/' ) ); ?>">
            <div class="ic-filter-group">
              <label for="filter-make" class="ic-filter-label"><?php esc_html_e( 'Make', 'imanicars' ); ?></label>
              <select name="make" id="filter-make" class="ic-filter-select">
                <option value=""><?php esc_html_e( 'Any Make', 'imanicars' ); ?></option>
                <?php
                $makes = [ 'Toyota', 'Mazda', 'Ford', 'Holden', 'Honda', 'Nissan', 'Mitsubishi', 'Hyundai', 'Kia', 'Subaru', 'BMW', 'Mercedes-Benz', 'Audi', 'Volkswagen', 'Jeep', 'Land Rover', 'Tesla', 'Isuzu', 'LDV', 'MG' ];
                foreach ( $makes as $make ) {
                    echo '<option value="' . esc_attr( $make ) . '"' . selected( $current_make, $make, false ) . '>' . esc_html( $make ) . '</option>';
                }
                ?>
              </select>
            </div>
            <div class="ic-filter-group">
              <label for="filter-condition" class="ic-filter-label"><?php esc_html_e( 'Condition', 'imanicars' ); ?></label>
              <select name="condition" id="filter-condition" class="ic-filter-select">
                <option value=""><?php esc_html_e( 'Any', 'imanicars' ); ?></option>
                <option value="new"  <?php selected( $condition, 'new' ); ?>><?php esc_html_e( 'New', 'imanicars' ); ?></option>
                <option value="used" <?php selected( $condition, 'used' ); ?>><?php esc_html_e( 'Used', 'imanicars' ); ?></option>
                <option value="demo" <?php selected( $condition, 'demo' ); ?>><?php esc_html_e( 'Demo', 'imanicars' ); ?></option>
              </select>
            </div>
            <div class="ic-filter-group">
              <label for="filter-price-min" class="ic-filter-label"><?php esc_html_e( 'Min Price', 'imanicars' ); ?></label>
              <select name="price_min" id="filter-price-min" class="ic-filter-select">
                <option value=""><?php esc_html_e( 'No min', 'imanicars' ); ?></option>
                <?php foreach ( [ 5000, 10000, 15000, 20000, 25000, 30000, 40000, 50000, 75000 ] as $p ) echo '<option value="' . esc_attr( $p ) . '"' . selected( $price_min, $p, false ) . '>$' . esc_html( number_format( $p ) ) . '</option>'; ?>
              </select>
            </div>
            <div class="ic-filter-group">
              <label for="filter-price-max" class="ic-filter-label"><?php esc_html_e( 'Max Price', 'imanicars' ); ?></label>
              <select name="price_max" id="filter-price-max" class="ic-filter-select">
                <option value=""><?php esc_html_e( 'No max', 'imanicars' ); ?></option>
                <?php foreach ( [ 15000, 20000, 25000, 30000, 40000, 50000, 75000, 100000, 150000 ] as $p ) echo '<option value="' . esc_attr( $p ) . '"' . selected( $price_max, $p, false ) . '>$' . esc_html( number_format( $p ) ) . '</option>'; ?>
              </select>
            </div>
            <div class="ic-filter-group">
              <label for="filter-body" class="ic-filter-label"><?php esc_html_e( 'Body Type', 'imanicars' ); ?></label>
              <select name="body_type" id="filter-body" class="ic-filter-select">
                <option value=""><?php esc_html_e( 'Any', 'imanicars' ); ?></option>
                <?php foreach ( [ 'SUV', 'Sedan', 'Hatch', 'Ute', 'Wagon', 'Coupe', 'Convertible', 'Van', 'People Mover' ] as $b ) echo '<option value="' . esc_attr( $b ) . '"' . selected( $body_type, $b, false ) . '>' . esc_html( $b ) . '</option>'; ?>
              </select>
            </div>
            <div class="ic-filter-group">
              <label for="filter-city" class="ic-filter-label"><?php esc_html_e( 'City', 'imanicars' ); ?></label>
              <select name="city" id="filter-city" class="ic-filter-select">
                <option value=""><?php esc_html_e( 'Any City', 'imanicars' ); ?></option>
                <?php
                $cities = [ 'brisbane' => 'Brisbane', 'melbourne' => 'Melbourne', 'perth' => 'Perth', 'darwin' => 'Darwin' ];
                foreach ( $cities as $slug => $label ) echo '<option value="' . esc_attr( $slug ) . '"' . selected( $current_city, $slug, false ) . '>' . esc_html( $label ) . '</option>';
                ?>
              </select>
            </div>
            <div class="ic-filter-group">
              <label for="filter-seller" class="ic-filter-label"><?php esc_html_e( 'Seller Type', 'imanicars' ); ?></label>
              <select name="seller" id="filter-seller" class="ic-filter-select">
                <option value=""><?php esc_html_e( 'Any', 'imanicars' ); ?></option>
                <option value="dealer"  <?php selected( $seller, 'dealer' ); ?>><?php esc_html_e( 'Dealer', 'imanicars' ); ?></option>
                <option value="private" <?php selected( $seller, 'private' ); ?>><?php esc_html_e( 'Private Seller', 'imanicars' ); ?></option>
              </select>
            </div>
            <button type="submit" class="btn btn-primary btn-full"><?php esc_html_e( 'Apply Filters', 'imanicars' ); ?></button>
            <a href="<?php echo esc_url( home_url( '/cars/' ) ); ?>" class="ic-filter-reset"><?php esc_html_e( 'Clear all filters', 'imanicars' ); ?></a>
          </form>
        </div>

        <!-- SIDEBAR WIDGET AREA -->
        <?php if ( is_active_sidebar( 'ic-sidebar-listings' ) ) : ?>
        <div class="ic-archive-sidebar__widgets">
          <?php dynamic_sidebar( 'ic-sidebar-listings' ); ?>
        </div>
        <?php endif; ?>

        <!-- DEALER CTA SIDEBAR -->
        <div class="ic-sidebar-cta info-card">
          <h3><?php esc_html_e( 'Are You a Dealer?', 'imanicars' ); ?></h3>
          <p><?php esc_html_e( 'List up to 10 cars free. No credit card required.', 'imanicars' ); ?></p>
          <ul>
            <li>&#10003; <?php esc_html_e( 'Free forever', 'imanicars' ); ?></li>
            <li>&#10003; <?php esc_html_e( 'Live in 24 hours', 'imanicars' ); ?></li>
            <li>&#10003; <?php esc_html_e( 'Cancel anytime', 'imanicars' ); ?></li>
          </ul>
          <a href="<?php echo esc_url( home_url( '/list-your-car/' ) ); ?>" class="btn btn-primary btn-full"><?php esc_html_e( 'List Free', 'imanicars' ); ?></a>
        </div>
      </aside><!-- /.ic-archive-sidebar -->

      <!-- MAIN: SORT BAR + GRID -->
      <div class="ic-archive-content">

        <!-- SORT BAR -->
        <div class="ic-archive-sortbar">
          <form class="ic-sortbar-form" method="get" action="<?php echo esc_url( home_url( '/cars/' ) ); ?>">
            <?php
            $passthrough_fields = [ 'make', 'condition', 'price_min', 'price_max', 'body_type', 'city', 'seller' ];
            foreach ( $passthrough_fields as $f ) {
                $val = sanitize_text_field( isset( $_GET[ $f ] ) ? $_GET[ $f ] : '' );
                if ( $val ) echo '<input type="hidden" name="' . esc_attr( $f ) . '" value="' . esc_attr( $val ) . '">';
            }
            $current_sort = sanitize_text_field( isset( $_GET['sort'] ) ? $_GET['sort'] : 'latest' );
            ?>
            <label for="ic-sort" class="ic-sortbar__label"><?php esc_html_e( 'Sort by:', 'imanicars' ); ?></label>
            <select name="sort" id="ic-sort" class="ic-sortbar__select" onchange="this.form.submit()">
              <option value="latest"    <?php selected( $current_sort, 'latest' ); ?>><?php esc_html_e( 'Latest', 'imanicars' ); ?></option>
              <option value="price_asc" <?php selected( $current_sort, 'price_asc' ); ?>><?php esc_html_e( 'Price: Low to High', 'imanicars' ); ?></option>
              <option value="price_desc"<?php selected( $current_sort, 'price_desc' ); ?>><?php esc_html_e( 'Price: High to Low', 'imanicars' ); ?></option>
              <option value="year_desc" <?php selected( $current_sort, 'year_desc' ); ?>><?php esc_html_e( 'Year: Newest First', 'imanicars' ); ?></option>
              <option value="km_asc"    <?php selected( $current_sort, 'km_asc' ); ?>><?php esc_html_e( 'Odometer: Lowest', 'imanicars' ); ?></option>
            </select>
          </form>
        </div>

        <!-- LISTINGS GRID -->
        <?php if ( have_posts() ) : ?>
        <div class="ic-car-grid ic-car-grid--archive">
          <?php
          while ( have_posts() ) :
              the_post();
              get_template_part( 'template-parts/car-card' );
          endwhile;
          ?>
        </div>
        <!-- PAGINATION -->
        <div class="ic-pagination">
          <?php
          the_posts_pagination( [
              'mid_size'           => 2,
              'prev_text'          => '&larr; ' . esc_html__( 'Previous', 'imanicars' ),
              'next_text'          => esc_html__( 'Next', 'imanicars' ) . ' &rarr;',
              'screen_reader_text' => esc_html__( 'Posts navigation', 'imanicars' ),
          ] );
          ?>
        </div>
        <?php else : ?>
        <div class="ic-archive-empty">
          <?php get_template_part( 'template-parts/content', 'none' ); ?>
        </div>
        <?php endif; ?>

      </div><!-- /.ic-archive-content -->

    </div><!-- /.ic-archive-layout -->
  </div><!-- /.container -->
</div><!-- /.ic-archive-page -->

<?php get_footer(); ?>
