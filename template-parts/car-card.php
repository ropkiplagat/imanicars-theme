<?php
/**
 * Template Part: Car Card
 * Used in archive.php, front-page.php, search.php
 * Expects the WP loop to be active (have_posts / the_post).
 */

defined( 'ABSPATH' ) || exit;

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
$suburb      = get_post_meta( $post_id, '_ic_suburb', true );
$state_code  = get_post_meta( $post_id, '_ic_state', true );
$dealer_name = get_post_meta( $post_id, '_ic_dealer_name', true );
$badge_type  = get_post_meta( $post_id, '_ic_badge_type', true );
$finance_mo  = get_post_meta( $post_id, '_ic_finance_mo', true );
$is_featured = get_post_meta( $post_id, '_ic_is_featured', true );

$car_img  = ic_get_car_image( $post_id, 'ic-card' );
$title    = trim( $year . ' ' . $make . ' ' . $model . ' ' . $badge_v );
if ( ! $title ) $title = get_the_title();
$car_url  = get_permalink();

$badge_labels = [
    'featured'  => 'FEATURED',
    'new'       => 'NEW',
    'certified' => 'CERTIFIED',
    'demo'      => 'DEMO',
    'used'      => 'USED',
];
$bl = ( $badge_type && isset( $badge_labels[ $badge_type ] ) ) ? $badge_labels[ $badge_type ] : '';
if ( $is_featured === '1' && ! $badge_type ) {
    $badge_type = 'featured';
    $bl         = 'FEATURED';
}
?>

<article class="ic-car-card" id="vehicle-<?php echo esc_attr( $post_id ); ?>">
  <div class="ic-car-card__img-wrap">
    <a href="<?php echo esc_url( $car_url ); ?>" tabindex="-1" aria-hidden="true">
      <img src="<?php echo esc_url( $car_img ); ?>"
           alt="<?php echo esc_attr( $title ); ?>"
           width="400" height="267" loading="lazy" class="ic-car-card__img">
    </a>
    <?php if ( $bl ) : ?>
    <span class="ic-car-card__badge ic-car-card__badge--<?php echo esc_attr( $badge_type ); ?>"><?php echo esc_html( $bl ); ?></span>
    <?php endif; ?>
    <button class="ic-car-card__fav" aria-label="<?php esc_attr_e( 'Save to favourites', 'imanicars' ); ?>" data-id="<?php echo esc_attr( $post_id ); ?>">
      <span aria-hidden="true">&#9825;</span>
    </button>
  </div>

  <div class="ic-car-card__body">
    <div class="ic-car-card__price-row">
      <span class="ic-car-card__price"><?php echo esc_html( ic_format_price( $price ) ); ?></span>
      <?php if ( $price_label ) : ?>
      <span class="ic-car-card__price-label"><?php echo esc_html( $price_label ); ?></span>
      <?php elseif ( $price ) : ?>
      <span class="ic-car-card__price-label"><?php esc_html_e( 'Drive Away', 'imanicars' ); ?></span>
      <?php endif; ?>
    </div>

    <?php if ( $finance_mo ) : ?>
    <div class="ic-car-card__finance">
      <?php
      /* translators: %s: monthly payment */
      printf( esc_html__( '~%s/mo', 'imanicars' ), esc_html( ic_format_price( $finance_mo ) ) );
      ?>
    </div>
    <?php endif; ?>

    <h3 class="ic-car-card__title">
      <a href="<?php echo esc_url( $car_url ); ?>"><?php echo esc_html( $title ); ?></a>
    </h3>

    <ul class="ic-car-card__specs">
      <?php if ( $km )    echo '<li class="ic-spec-pill">' . esc_html( number_format( (int) $km ) ) . ' km</li>'; ?>
      <?php if ( $trans ) echo '<li class="ic-spec-pill">' . esc_html( $trans ) . '</li>'; ?>
      <?php if ( $fuel )  echo '<li class="ic-spec-pill">' . esc_html( $fuel ) . '</li>'; ?>
      <?php if ( $body )  echo '<li class="ic-spec-pill">' . esc_html( $body ) . '</li>'; ?>
    </ul>

    <div class="ic-car-card__seller">
      <?php if ( $suburb || $state_code ) : ?>
      <span class="ic-car-card__location"><?php echo esc_html( trim( $suburb . ', ' . $state_code, ', ' ) ); ?></span>
      <?php endif; ?>
      <?php if ( $dealer_name ) : ?>
      <span class="ic-car-card__dealer"><?php echo esc_html( $dealer_name ); ?></span>
      <?php endif; ?>
    </div>
  </div>

  <div class="ic-car-card__actions">
    <a href="<?php echo esc_url( $car_url ); ?>" class="btn btn-primary btn-sm ic-car-card__enquire">
      <?php esc_html_e( 'Enquire', 'imanicars' ); ?>
    </a>
    <button class="btn btn-outline btn-sm ic-car-card__save" data-id="<?php echo esc_attr( $post_id ); ?>">
      <?php esc_html_e( 'Save', 'imanicars' ); ?>
    </button>
  </div>
</article>
