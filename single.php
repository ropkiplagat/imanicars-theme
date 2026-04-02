<?php
/**
 * Single Vehicle Template — Imani Cars
 * Gallery + specs + enquiry form + finance teaser
 */
get_header();

while ( have_posts() ) :
    the_post();
    $post_id     = get_the_ID();
    $price       = get_post_meta( $post_id, '_ic_price', true );
    $price_label = get_post_meta( $post_id, '_ic_price_label', true );
    $year        = get_post_meta( $post_id, '_ic_year', true );
    $make        = get_post_meta( $post_id, '_ic_make', true );
    $model       = get_post_meta( $post_id, '_ic_model', true );
    $badge_v     = get_post_meta( $post_id, '_ic_badge', true );
    $km          = get_post_meta( $post_id, '_ic_odometer', true );
    $trans       = get_post_meta( $post_id, '_ic_transmission', true );
    $fuel        = get_post_meta( $post_id, '_ic_fuel_type', true );
    $body        = get_post_meta( $post_id, '_ic_body_type', true );
    $engine      = get_post_meta( $post_id, '_ic_engine', true );
    $colour      = get_post_meta( $post_id, '_ic_colour', true );
    $doors       = get_post_meta( $post_id, '_ic_doors', true );
    $seats       = get_post_meta( $post_id, '_ic_seats', true );
    $condition   = get_post_meta( $post_id, '_ic_condition', true );
    $seller_type = get_post_meta( $post_id, '_ic_seller_type', true );
    $dealer_name = get_post_meta( $post_id, '_ic_dealer_name', true );
    $city        = get_post_meta( $post_id, '_ic_city', true );
    $suburb      = get_post_meta( $post_id, '_ic_suburb', true );
    $state_code  = get_post_meta( $post_id, '_ic_state', true );
    $phone       = get_post_meta( $post_id, '_ic_phone', true );
    $email       = get_post_meta( $post_id, '_ic_email', true );
    $finance_mo  = get_post_meta( $post_id, '_ic_finance_mo', true );
    $badge_type  = get_post_meta( $post_id, '_ic_badge_type', true );

    $main_img = ic_get_car_image( $post_id, 'ic-single' );
    $title_str = trim( $year . ' ' . $make . ' ' . $model . ' ' . $badge_v );
    if ( empty( $title_str ) ) $title_str = get_the_title();
?>

<div class="ic-single-page">
  <div class="container">

    <!-- BREADCRUMB -->
    <nav class="ic-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'imanicars' ); ?>">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'imanicars' ); ?></a>
      <span aria-hidden="true"> &rsaquo; </span>
      <a href="<?php echo esc_url( home_url( '/cars/' ) ); ?>"><?php esc_html_e( 'Cars for Sale', 'imanicars' ); ?></a>
      <span aria-hidden="true"> &rsaquo; </span>
      <span aria-current="page"><?php echo esc_html( $title_str ); ?></span>
    </nav>

    <div class="ic-single-layout">

      <!-- LEFT COLUMN: GALLERY + DETAILS -->
      <div class="ic-single-main">

        <!-- GALLERY -->
        <div class="ic-single-gallery">
          <div class="ic-single-gallery__main">
            <?php if ( $badge_type ) : ?>
            <span class="ic-car-card__badge ic-car-card__badge--<?php echo esc_attr( $badge_type ); ?>">
              <?php echo esc_html( strtoupper( $badge_type ) ); ?>
            </span>
            <?php endif; ?>
            <img src="<?php echo esc_url( $main_img ); ?>"
                 alt="<?php echo esc_attr( $title_str ); ?>"
                 width="800" height="534" loading="eager" class="ic-single-gallery__img" id="ic-gallery-main">
          </div>
          <div class="ic-single-gallery__thumbs">
            <?php
            $seeds = [ '1552519152-9214d16d56ab', '1503376780353-7e6692767b70', '1590362891991-f776e747a588', '1555215695-3004980ad54e' ];
            foreach ( $seeds as $i => $seed ) :
            ?>
            <button class="ic-single-gallery__thumb-btn" aria-label="<?php echo esc_attr( 'Photo ' . ( $i + 1 ) ); ?>" data-img="<?php echo esc_url( ic_unsplash( $seed, 800, 534 ) ); ?>">
              <img src="<?php echo esc_url( ic_unsplash( $seed, 200, 133 ) ); ?>"
                   alt="<?php echo esc_attr( $title_str . ' photo ' . ( $i + 1 ) ); ?>"
                   width="200" height="133" loading="lazy" class="ic-single-gallery__thumb-img">
            </button>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- VEHICLE SPECS -->
        <div class="ic-single-specs">
          <h2 class="ic-single-specs__heading"><?php esc_html_e( 'Vehicle Specifications', 'imanicars' ); ?></h2>
          <div class="ic-single-specs__grid">
            <?php
            $specs = [
                [ 'label' => 'Year',         'value' => $year ],
                [ 'label' => 'Make',         'value' => $make ],
                [ 'label' => 'Model',        'value' => $model ],
                [ 'label' => 'Badge',        'value' => $badge_v ],
                [ 'label' => 'Odometer',     'value' => $km ? number_format( (int) $km ) . ' km' : '' ],
                [ 'label' => 'Transmission', 'value' => $trans ],
                [ 'label' => 'Fuel Type',    'value' => $fuel ],
                [ 'label' => 'Body Type',    'value' => $body ],
                [ 'label' => 'Engine',       'value' => $engine ],
                [ 'label' => 'Colour',       'value' => $colour ],
                [ 'label' => 'Doors',        'value' => $doors ],
                [ 'label' => 'Seats',        'value' => $seats ],
                [ 'label' => 'Condition',    'value' => $condition ],
                [ 'label' => 'Seller Type',  'value' => $seller_type ],
            ];
            foreach ( $specs as $spec ) :
                if ( empty( $spec['value'] ) ) continue;
            ?>
            <div class="ic-single-specs__item">
              <span class="ic-single-specs__label"><?php echo esc_html( $spec['label'] ); ?></span>
              <span class="ic-single-specs__value"><?php echo esc_html( $spec['value'] ); ?></span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- DESCRIPTION -->
        <?php if ( has_excerpt() || get_the_content() ) : ?>
        <div class="ic-single-description">
          <h2 class="ic-single-description__heading"><?php esc_html_e( 'About This Vehicle', 'imanicars' ); ?></h2>
          <div class="ic-single-description__body">
            <?php the_content(); ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- FINANCE TEASER -->
        <?php if ( $price ) : ?>
        <div class="ic-single-finance">
          <div class="ic-single-finance__inner">
            <div class="ic-single-finance__text">
              <h3><?php esc_html_e( 'Finance This Car', 'imanicars' ); ?></h3>
              <?php if ( $finance_mo ) : ?>
              <p>
                <?php
                /* translators: %s: monthly payment */
                printf( esc_html__( 'From approximately %s per month with approved credit.', 'imanicars' ), '<strong>' . esc_html( ic_format_price( $finance_mo ) ) . '/mo</strong>' );
                ?>
              </p>
              <?php else : ?>
              <p><?php esc_html_e( 'Get pre-approved car finance before you buy. Competitive rates, fast approval.', 'imanicars' ); ?></p>
              <?php endif; ?>
            </div>
            <a href="<?php echo esc_url( home_url( '/finance/' ) ); ?>" class="btn btn-gold"><?php esc_html_e( 'Get Finance Quote', 'imanicars' ); ?></a>
          </div>
        </div>
        <?php endif; ?>

      </div><!-- /.ic-single-main -->

      <!-- RIGHT COLUMN: PRICE + ENQUIRY -->
      <aside class="ic-single-sidebar" aria-label="<?php esc_attr_e( 'Car summary and enquiry', 'imanicars' ); ?>">

        <!-- PRICE CARD -->
        <div class="ic-single-price-card">
          <h1 class="ic-single-price-card__title"><?php echo esc_html( $title_str ); ?></h1>
          <div class="ic-single-price-card__price">
            <span class="ic-single-price-card__amount"><?php echo esc_html( ic_format_price( $price ) ); ?></span>
            <?php if ( $price_label ) : ?>
            <span class="ic-single-price-card__label"><?php echo esc_html( $price_label ); ?></span>
            <?php endif; ?>
          </div>
          <ul class="ic-single-price-card__specs">
            <?php if ( $km )    echo '<li class="ic-spec-pill">' . esc_html( number_format( (int) $km ) ) . ' km</li>'; ?>
            <?php if ( $trans ) echo '<li class="ic-spec-pill">' . esc_html( $trans ) . '</li>'; ?>
            <?php if ( $fuel )  echo '<li class="ic-spec-pill">' . esc_html( $fuel ) . '</li>'; ?>
            <?php if ( $body )  echo '<li class="ic-spec-pill">' . esc_html( $body ) . '</li>'; ?>
          </ul>
          <?php if ( $dealer_name || $suburb ) : ?>
          <div class="ic-single-price-card__seller">
            <?php if ( $dealer_name ) echo '<span class="ic-single-price-card__dealer">' . esc_html( $dealer_name ) . '</span>'; ?>
            <?php if ( $suburb || $state_code ) echo '<span class="ic-single-price-card__location">' . esc_html( trim( $suburb . ', ' . $state_code, ', ' ) ) . '</span>'; ?>
          </div>
          <?php endif; ?>
          <?php if ( $phone ) : ?>
          <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>" class="btn btn-dark btn-full ic-single-price-card__phone">
            <?php echo esc_html( $phone ); ?>
          </a>
          <?php endif; ?>
        </div>

        <!-- ENQUIRY FORM -->
        <div class="ic-single-enquiry-card">
          <h2 class="ic-single-enquiry-card__heading"><?php esc_html_e( 'Enquire About This Car', 'imanicars' ); ?></h2>
          <form class="ic-enquiry-form" id="ic-car-enquiry-form" novalidate>
            <?php wp_nonce_field( 'ic_nonce', 'ic_enquiry_nonce' ); ?>
            <input type="hidden" name="car_id" value="<?php echo esc_attr( $post_id ); ?>">
            <div class="ic-form-group">
              <label for="enq-name" class="ic-form-label"><?php esc_html_e( 'Full Name', 'imanicars' ); ?> <span class="ic-form-req" aria-hidden="true">*</span></label>
              <input type="text" id="enq-name" name="name" class="ic-form-input" required aria-required="true" placeholder="<?php esc_attr_e( 'Your full name', 'imanicars' ); ?>">
            </div>
            <div class="ic-form-group">
              <label for="enq-email" class="ic-form-label"><?php esc_html_e( 'Email Address', 'imanicars' ); ?> <span class="ic-form-req" aria-hidden="true">*</span></label>
              <input type="email" id="enq-email" name="email" class="ic-form-input" required aria-required="true" placeholder="<?php esc_attr_e( 'you@example.com', 'imanicars' ); ?>">
            </div>
            <div class="ic-form-group">
              <label for="enq-phone" class="ic-form-label"><?php esc_html_e( 'Phone Number', 'imanicars' ); ?></label>
              <input type="tel" id="enq-phone" name="phone" class="ic-form-input" placeholder="<?php esc_attr_e( '04XX XXX XXX', 'imanicars' ); ?>">
            </div>
            <div class="ic-form-group">
              <label for="enq-message" class="ic-form-label"><?php esc_html_e( 'Message', 'imanicars' ); ?> <span class="ic-form-req" aria-hidden="true">*</span></label>
              <textarea id="enq-message" name="message" class="ic-form-textarea" rows="4" required aria-required="true" placeholder="<?php esc_attr_e( 'I am interested in this vehicle...', 'imanicars' ); ?>"></textarea>
            </div>
            <div id="ic-enquiry-msg" class="ic-form-msg" role="alert" aria-live="polite" hidden></div>
            <button type="submit" class="btn btn-primary btn-full">
              <?php esc_html_e( 'Send Enquiry', 'imanicars' ); ?>
            </button>
            <p class="ic-form-disclaimer"><?php esc_html_e( 'Your details are sent directly to the seller. We never share your info with third parties.', 'imanicars' ); ?></p>
          </form>
        </div>

        <!-- SIMILAR LISTINGS LINK -->
        <?php if ( $make ) : ?>
        <div class="ic-single-similar">
          <a href="<?php echo esc_url( home_url( '/cars/?make=' . urlencode( $make ) ) ); ?>" class="ic-single-similar__link">
            <?php
            /* translators: %s: car make */
            printf( esc_html__( 'View all %s cars for sale', 'imanicars' ), esc_html( $make ) );
            ?>
            &rarr;
          </a>
        </div>
        <?php endif; ?>

      </aside><!-- /.ic-single-sidebar -->

    </div><!-- /.ic-single-layout -->
  </div><!-- /.container -->
</div><!-- /.ic-single-page -->

<?php
endwhile;
get_footer();
?>
