<?php
/**
 * 404 Template — Imani Cars
 */
get_header();
?>

<div class="ic-404-page">
  <div class="container ic-404-page__inner">
    <div class="ic-404-page__content">
      <span class="ic-404-page__code" aria-hidden="true">404</span>
      <h1 class="ic-404-page__title"><?php esc_html_e( "Oops! This car has driven off.", 'imanicars' ); ?></h1>
      <p class="ic-404-page__sub"><?php esc_html_e( "The page you're looking for doesn't exist or has been moved. Let's get you back on the road.", 'imanicars' ); ?></p>
      <div class="ic-404-page__btns">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary btn-lg"><?php esc_html_e( 'Go to Homepage', 'imanicars' ); ?></a>
        <a href="<?php echo esc_url( home_url( '/cars/' ) ); ?>" class="btn btn-outline"><?php esc_html_e( 'Browse All Cars', 'imanicars' ); ?></a>
      </div>
    </div>
    <div class="ic-404-page__img-col">
      <img src="<?php echo esc_url( ic_unsplash( '1494905060-a01de8f7b4c1', 600, 300 ) ); ?>"
           alt="Car on road" width="600" height="300" loading="lazy" class="ic-404-page__img">
    </div>
  </div>

  <!-- QUICK LINKS -->
  <div class="container ic-404-page__links">
    <h2><?php esc_html_e( 'You might be looking for:', 'imanicars' ); ?></h2>
    <ul class="ic-404-page__link-list">
      <li><a href="<?php echo esc_url( home_url( '/cars/' ) ); ?>"><?php esc_html_e( 'All Cars for Sale', 'imanicars' ); ?></a></li>
      <li><a href="<?php echo esc_url( home_url( '/cars/?city=brisbane' ) ); ?>"><?php esc_html_e( 'Cars in Brisbane', 'imanicars' ); ?></a></li>
      <li><a href="<?php echo esc_url( home_url( '/cars/?city=melbourne' ) ); ?>"><?php esc_html_e( 'Cars in Melbourne', 'imanicars' ); ?></a></li>
      <li><a href="<?php echo esc_url( home_url( '/cars/?city=perth' ) ); ?>"><?php esc_html_e( 'Cars in Perth', 'imanicars' ); ?></a></li>
      <li><a href="<?php echo esc_url( home_url( '/cars/?city=darwin' ) ); ?>"><?php esc_html_e( 'Cars in Darwin', 'imanicars' ); ?></a></li>
      <li><a href="<?php echo esc_url( home_url( '/list-your-car/' ) ); ?>"><?php esc_html_e( 'List Your Car Free', 'imanicars' ); ?></a></li>
      <li><a href="<?php echo esc_url( home_url( '/dealers/' ) ); ?>"><?php esc_html_e( 'Find a Dealer', 'imanicars' ); ?></a></li>
      <li><a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>"><?php esc_html_e( 'Pricing Plans', 'imanicars' ); ?></a></li>
    </ul>
  </div>

  <!-- SEARCH BOX -->
  <div class="container ic-404-page__search">
    <form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
      <label for="ic-404-search" class="ic-form-label"><?php esc_html_e( 'Or search for a car:', 'imanicars' ); ?></label>
      <div class="ic-404-page__search-row">
        <input type="search" id="ic-404-search" name="s" class="ic-form-input"
               placeholder="<?php esc_attr_e( 'e.g. Toyota RAV4 Brisbane', 'imanicars' ); ?>">
        <button type="submit" class="btn btn-primary"><?php esc_html_e( 'Search', 'imanicars' ); ?></button>
      </div>
    </form>
  </div>
</div>

<?php get_footer(); ?>
