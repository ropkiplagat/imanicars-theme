<?php
/**
 * Template Name: Sell Your Car
 * Landing page for private sellers — /sell-your-car/
 */
get_header();
?>

<!-- SELL YOUR CAR HERO -->
<section class="ic-syc-hero section" aria-label="<?php esc_attr_e( 'Sell your car', 'imanicars' ); ?>">
  <div class="container">
    <div class="ic-syc-hero__inner">
      <div class="ic-syc-hero__text">
        <h1 class="ic-syc-hero__title">
          <?php esc_html_e( 'Sell Your Car Fast.', 'imanicars' ); ?><br>
          <span style="color:#fa3232;"><?php esc_html_e( 'List Free on Imani Cars.', 'imanicars' ); ?></span>
        </h1>
        <p class="ic-syc-hero__sub">
          <?php esc_html_e( 'Reach 8,000+ active buyers across Brisbane, Melbourne, Perth and Darwin. List in minutes — no jargon, no hidden fees.', 'imanicars' ); ?>
        </p>
        <div class="ic-syc-hero__stats" style="display:flex;gap:2rem;flex-wrap:wrap;margin:1.5rem 0;">
          <div class="ic-syc-stat">
            <span class="ic-stats-bar__number">8,000+</span>
            <span class="ic-stats-bar__label"><?php esc_html_e( 'Active Buyers', 'imanicars' ); ?></span>
          </div>
          <div class="ic-syc-stat">
            <span class="ic-stats-bar__number">4</span>
            <span class="ic-stats-bar__label"><?php esc_html_e( 'Cities', 'imanicars' ); ?></span>
          </div>
          <div class="ic-syc-stat">
            <span class="ic-stats-bar__number ic-stats-bar__number--free">FREE</span>
            <span class="ic-stats-bar__label"><?php esc_html_e( 'Dealer Listings', 'imanicars' ); ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- TWO OPTIONS -->
<section class="ic-syc-options section section--grey" aria-label="<?php esc_attr_e( 'Listing options', 'imanicars' ); ?>">
  <div class="container">
    <h2 class="ic-section-title text-center"><?php esc_html_e( 'Choose How You Want to Sell', 'imanicars' ); ?></h2>
    <div class="ic-lyc-props__grid" style="max-width:800px;margin:0 auto;">

      <!-- OPTION 1: Dealer -->
      <div class="ic-lyc-prop" style="border:2px solid #fa3232;">
        <div class="ic-lyc-prop__icon feature-icon" aria-hidden="true">&#127970;</div>
        <h3 class="ic-lyc-prop__title"><?php esc_html_e( 'Dealer Listing', 'imanicars' ); ?></h3>
        <p><?php esc_html_e( 'Are you a car dealer? List your full inventory for FREE. Up to 10 cars on the Starter plan — no credit card, no contract. Upgrade to Growth or Pro when you need more reach.', 'imanicars' ); ?></p>
        <ul style="list-style:none;padding:0;margin:1rem 0;">
          <li>&#10003; <?php esc_html_e( 'Free forever for up to 10 listings', 'imanicars' ); ?></li>
          <li>&#10003; <?php esc_html_e( 'Instant email lead notifications', 'imanicars' ); ?></li>
          <li>&#10003; <?php esc_html_e( 'Live in 24 hours', 'imanicars' ); ?></li>
        </ul>
        <a href="<?php echo esc_url( home_url( '/list-your-car/' ) ); ?>" class="btn btn-primary">
          <?php esc_html_e( 'List as Dealer — Free', 'imanicars' ); ?> &rarr;
        </a>
      </div>

      <!-- OPTION 2: Private Seller -->
      <div class="ic-lyc-prop">
        <div class="ic-lyc-prop__icon feature-icon" aria-hidden="true">&#128100;</div>
        <h3 class="ic-lyc-prop__title"><?php esc_html_e( 'Private Seller', 'imanicars' ); ?></h3>
        <p><?php esc_html_e( 'Selling your own car? Fill in the form below and we\'ll list it for you. Private listings from $29. Reach thousands of buyers across 4 cities — usually sells within 7 days.', 'imanicars' ); ?></p>
        <ul style="list-style:none;padding:0;margin:1rem 0;">
          <li>&#10003; <?php esc_html_e( 'From $29 for a 30-day listing', 'imanicars' ); ?></li>
          <li>&#10003; <?php esc_html_e( 'Buyer enquiries direct to your phone', 'imanicars' ); ?></li>
          <li>&#10003; <?php esc_html_e( 'Listing live within 24 hours', 'imanicars' ); ?></li>
        </ul>
        <a href="#ic-private-form" class="btn btn-outline">
          <?php esc_html_e( 'Sell as Private Seller', 'imanicars' ); ?> &rarr;
        </a>
      </div>

    </div>
  </div>
</section>

<!-- PRIVATE SELLER FORM -->
<section class="ic-lyc-form-section section" id="ic-private-form" aria-label="<?php esc_attr_e( 'Private seller enquiry form', 'imanicars' ); ?>">
  <div class="container">
    <div class="ic-lyc-form-wrap">
      <div class="ic-lyc-form-info">
        <h2><?php esc_html_e( 'List Your Car Today', 'imanicars' ); ?></h2>
        <p><?php esc_html_e( 'Tell us about your car and we\'ll get it listed fast. One of our team will contact you within 24 hours to confirm your listing.', 'imanicars' ); ?></p>
        <ul class="ic-lyc-form-perks">
          <li>&#10003; <?php esc_html_e( '8,000+ active buyers across 4 cities', 'imanicars' ); ?></li>
          <li>&#10003; <?php esc_html_e( 'Enquiries direct to you — no middleman', 'imanicars' ); ?></li>
          <li>&#10003; <?php esc_html_e( 'From $29 for a full 30-day listing', 'imanicars' ); ?></li>
          <li>&#10003; <?php esc_html_e( 'Live in under 24 hours', 'imanicars' ); ?></li>
        </ul>
        <div style="margin-top:1.5rem;">
          <a href="<?php echo esc_url( home_url( '/list-your-car/' ) ); ?>" class="btn btn-primary">
            <?php esc_html_e( 'I\'m a Dealer — List Free', 'imanicars' ); ?>
          </a>
        </div>
      </div>
      <form class="ic-lyc-form" id="ic-private-seller-form" novalidate>
        <?php wp_nonce_field( 'ic_nonce', 'ic_form_nonce' ); ?>
        <div class="ic-form-group">
          <label for="syc-name" class="ic-form-label"><?php esc_html_e( 'Your Name', 'imanicars' ); ?> <span class="ic-form-req" aria-hidden="true">*</span></label>
          <input type="text" id="syc-name" name="name" class="ic-form-input"
                 placeholder="<?php esc_attr_e( 'Full name', 'imanicars' ); ?>" required aria-required="true">
        </div>
        <div class="ic-form-group">
          <label for="syc-phone" class="ic-form-label"><?php esc_html_e( 'Phone Number', 'imanicars' ); ?> <span class="ic-form-req" aria-hidden="true">*</span></label>
          <input type="tel" id="syc-phone" name="phone" class="ic-form-input"
                 placeholder="<?php esc_attr_e( '04XX XXX XXX', 'imanicars' ); ?>" required aria-required="true">
        </div>
        <div class="ic-form-group">
          <label for="syc-email" class="ic-form-label"><?php esc_html_e( 'Email Address', 'imanicars' ); ?> <span class="ic-form-req" aria-hidden="true">*</span></label>
          <input type="email" id="syc-email" name="email" class="ic-form-input"
                 placeholder="<?php esc_attr_e( 'you@example.com', 'imanicars' ); ?>" required aria-required="true">
        </div>
        <div class="ic-form-group">
          <label for="syc-make" class="ic-form-label"><?php esc_html_e( 'Car Make & Model', 'imanicars' ); ?> <span class="ic-form-req" aria-hidden="true">*</span></label>
          <input type="text" id="syc-make" name="car_make_model" class="ic-form-input"
                 placeholder="<?php esc_attr_e( 'e.g. 2020 Toyota RAV4 GX', 'imanicars' ); ?>" required aria-required="true">
        </div>
        <div class="ic-form-group">
          <label for="syc-price" class="ic-form-label"><?php esc_html_e( 'Asking Price ($)', 'imanicars' ); ?> <span class="ic-form-req" aria-hidden="true">*</span></label>
          <input type="number" id="syc-price" name="asking_price" class="ic-form-input"
                 placeholder="<?php esc_attr_e( 'e.g. 24990', 'imanicars' ); ?>" min="1" required aria-required="true">
        </div>
        <div class="ic-form-group">
          <label for="syc-city" class="ic-form-label"><?php esc_html_e( 'City', 'imanicars' ); ?> <span class="ic-form-req" aria-hidden="true">*</span></label>
          <select id="syc-city" name="city" class="ic-form-select" required aria-required="true">
            <option value=""><?php esc_html_e( 'Select your city', 'imanicars' ); ?></option>
            <option value="Brisbane"><?php esc_html_e( 'Brisbane', 'imanicars' ); ?></option>
            <option value="Melbourne"><?php esc_html_e( 'Melbourne', 'imanicars' ); ?></option>
            <option value="Perth"><?php esc_html_e( 'Perth', 'imanicars' ); ?></option>
            <option value="Darwin"><?php esc_html_e( 'Darwin', 'imanicars' ); ?></option>
          </select>
        </div>
        <div id="ic-syc-msg" class="ic-form-msg" role="alert" aria-live="polite" hidden></div>
        <button type="submit" class="btn btn-primary btn-lg btn-full">
          <?php esc_html_e( 'Submit My Car for Listing', 'imanicars' ); ?>
        </button>
        <p class="ic-form-disclaimer">
          <?php esc_html_e( 'By submitting you agree to our Terms of Service. Our team will contact you within 24 hours.', 'imanicars' ); ?>
        </p>
      </form>
    </div>
  </div>
</section>

<?php get_footer(); ?>
