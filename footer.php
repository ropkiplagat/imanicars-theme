</main><!-- /#ic-main -->

<!-- =========================================================
     FOOTER
     ========================================================= -->
<footer class="ic-footer" role="contentinfo">

  <!-- FOOTER MAIN GRID -->
  <div class="ic-footer__main">
    <div class="container">
      <div class="ic-footer__grid">

        <!-- COL 1 — BRAND + SOCIAL -->
        <div class="ic-footer__col ic-footer__col--brand">
          <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ic-footer__logo" aria-label="<?php esc_attr_e( 'Imani Cars — Home', 'imanicars' ); ?>">
            <img src="<?php echo esc_url( IC_THEME_URI . '/assets/images/logo.png' ); ?>"
                 alt="Imani Cars" width="140" height="42" loading="lazy">
          </a>
          <p class="ic-footer__tagline"><?php esc_html_e( "Australia's fastest growing car marketplace. Free listings for dealers in Brisbane, Melbourne, Perth and Darwin.", 'imanicars' ); ?></p>
          <div class="ic-footer__social">
            <a href="https://facebook.com/imanicars" class="ic-footer__social-link" aria-label="<?php esc_attr_e( 'Facebook', 'imanicars' ); ?>" rel="noopener noreferrer" target="_blank">
              <span class="ic-social-icon" aria-hidden="true">f</span>
            </a>
            <a href="https://instagram.com/imanicars" class="ic-footer__social-link" aria-label="<?php esc_attr_e( 'Instagram', 'imanicars' ); ?>" rel="noopener noreferrer" target="_blank">
              <span class="ic-social-icon" aria-hidden="true">in</span>
            </a>
            <a href="https://twitter.com/imanicars" class="ic-footer__social-link" aria-label="<?php esc_attr_e( 'X / Twitter', 'imanicars' ); ?>" rel="noopener noreferrer" target="_blank">
              <span class="ic-social-icon" aria-hidden="true">X</span>
            </a>
          </div>
        </div>

        <!-- COL 2 — BUY A CAR -->
        <div class="ic-footer__col">
          <h3 class="ic-footer__heading"><?php esc_html_e( 'Buy a Car', 'imanicars' ); ?></h3>
          <ul class="ic-footer__links">
            <li><a href="<?php echo esc_url( home_url( '/cars/' ) ); ?>"><?php esc_html_e( 'All Cars for Sale', 'imanicars' ); ?></a></li>
            <li><a href="<?php echo esc_url( home_url( '/used-cars/' ) ); ?>"><?php esc_html_e( 'Used Cars', 'imanicars' ); ?></a></li>
            <li><a href="<?php echo esc_url( home_url( '/sell-your-car/' ) ); ?>"><?php esc_html_e( 'Sell Your Car', 'imanicars' ); ?></a></li>
            <li><a href="<?php echo esc_url( home_url( '/cars/?condition=new' ) ); ?>"><?php esc_html_e( 'New Cars', 'imanicars' ); ?></a></li>
            <li><a href="<?php echo esc_url( home_url( '/cars/?fuel_type=Electric' ) ); ?>"><?php esc_html_e( 'Electric Cars', 'imanicars' ); ?></a></li>
            <li><a href="<?php echo esc_url( home_url( '/brisbane/' ) ); ?>"><?php esc_html_e( 'Cars in Brisbane', 'imanicars' ); ?></a></li>
            <li><a href="<?php echo esc_url( home_url( '/melbourne/' ) ); ?>"><?php esc_html_e( 'Cars in Melbourne', 'imanicars' ); ?></a></li>
            <li><a href="<?php echo esc_url( home_url( '/perth/' ) ); ?>"><?php esc_html_e( 'Cars in Perth', 'imanicars' ); ?></a></li>
            <li><a href="<?php echo esc_url( home_url( '/darwin/' ) ); ?>"><?php esc_html_e( 'Cars in Darwin', 'imanicars' ); ?></a></li>
          </ul>
        </div>

        <!-- COL 3 — SELL / DEALERS -->
        <div class="ic-footer__col">
          <h3 class="ic-footer__heading"><?php esc_html_e( 'Sell & Dealers', 'imanicars' ); ?></h3>
          <ul class="ic-footer__links">
            <li><a href="<?php echo esc_url( home_url( '/list-your-car/' ) ); ?>"><?php esc_html_e( 'List Your Car Free', 'imanicars' ); ?></a></li>
            <li><a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>"><?php esc_html_e( 'Dealer Pricing', 'imanicars' ); ?></a></li>
            <li><a href="<?php echo esc_url( home_url( '/dealers/' ) ); ?>"><?php esc_html_e( 'Find a Dealer', 'imanicars' ); ?></a></li>
            <li><a href="<?php echo esc_url( home_url( '/dealers/brisbane/' ) ); ?>"><?php esc_html_e( 'Brisbane Dealers', 'imanicars' ); ?></a></li>
            <li><a href="<?php echo esc_url( home_url( '/dealers/melbourne/' ) ); ?>"><?php esc_html_e( 'Melbourne Dealers', 'imanicars' ); ?></a></li>
            <li><a href="<?php echo esc_url( home_url( '/dealers/perth/' ) ); ?>"><?php esc_html_e( 'Perth Dealers', 'imanicars' ); ?></a></li>
            <li><a href="<?php echo esc_url( home_url( '/dealers/darwin/' ) ); ?>"><?php esc_html_e( 'Darwin Dealers', 'imanicars' ); ?></a></li>
            <li><a href="<?php echo esc_url( home_url( '/finance/' ) ); ?>"><?php esc_html_e( 'Car Finance', 'imanicars' ); ?></a></li>
          </ul>
        </div>

        <!-- COL 4 — COMPANY -->
        <div class="ic-footer__col">
          <h3 class="ic-footer__heading"><?php esc_html_e( 'Company', 'imanicars' ); ?></h3>
          <ul class="ic-footer__links">
            <li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'About Imani Cars', 'imanicars' ); ?></a></li>
            <li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact Us', 'imanicars' ); ?></a></li>
            <li><a href="<?php echo esc_url( home_url( '/careers/' ) ); ?>"><?php esc_html_e( 'Careers', 'imanicars' ); ?></a></li>
            <li><a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'imanicars' ); ?></a></li>
            <li><a href="<?php echo esc_url( home_url( '/terms-of-service/' ) ); ?>"><?php esc_html_e( 'Terms of Service', 'imanicars' ); ?></a></li>
            <li><a href="<?php echo esc_url( home_url( '/advertise/' ) ); ?>"><?php esc_html_e( 'Advertise with Us', 'imanicars' ); ?></a></li>
            <li><a href="<?php echo esc_url( home_url( '/news/' ) ); ?>"><?php esc_html_e( 'Car News', 'imanicars' ); ?></a></li>
          </ul>
        </div>

      </div><!-- /.ic-footer__grid -->
    </div><!-- /.container -->
  </div><!-- /.ic-footer__main -->

  <!-- FOOTER BOTTOM BAR -->
  <div class="ic-footer__bottom">
    <div class="container">
      <div class="ic-footer__bottom-inner">
        <p class="ic-footer__copy">
          &copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php esc_html_e( 'Imani Cars Pty Ltd. All rights reserved.', 'imanicars' ); ?>
          <?php esc_html_e( 'ABN 00 000 000 000', 'imanicars' ); ?>
        </p>
        <nav class="ic-footer__city-nav" aria-label="<?php esc_attr_e( 'City links', 'imanicars' ); ?>">
          <a href="<?php echo esc_url( home_url( '/brisbane/' ) ); ?>"><?php esc_html_e( 'Brisbane', 'imanicars' ); ?></a>
          <a href="<?php echo esc_url( home_url( '/melbourne/' ) ); ?>"><?php esc_html_e( 'Melbourne', 'imanicars' ); ?></a>
          <a href="<?php echo esc_url( home_url( '/perth/' ) ); ?>"><?php esc_html_e( 'Perth', 'imanicars' ); ?></a>
          <a href="<?php echo esc_url( home_url( '/darwin/' ) ); ?>"><?php esc_html_e( 'Darwin', 'imanicars' ); ?></a>
        </nav>
      </div>
    </div>
  </div><!-- /.ic-footer__bottom -->

</footer><!-- /.ic-footer -->

<?php wp_footer(); ?>
</body>
</html>
