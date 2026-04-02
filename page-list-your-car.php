<?php
/**
 * Template Name: List Your Car — Dealer Free Signup
 * FREE hook — "List free, get leads, no contract"
 */
get_header();
?>

<!-- HERO -->
<section class="ic-lyc-hero">
  <div class="container ic-lyc-hero__inner">
    <div class="ic-lyc-hero__text">
      <span class="ic-lyc-hero__eyebrow"><?php esc_html_e( 'For Car Dealers', 'imanicars' ); ?></span>
      <h1 class="ic-lyc-hero__title">
        <?php esc_html_e( 'List Your Cars Free.', 'imanicars' ); ?><br>
        <span class="ic-lyc-hero__accent"><?php esc_html_e( 'Reach Buyers Across 4 Cities.', 'imanicars' ); ?></span>
      </h1>
      <p class="ic-lyc-hero__sub"><?php esc_html_e( 'No credit card. No contract. Free forever for up to 10 listings. Join 50+ dealers already getting leads on Imani Cars.', 'imanicars' ); ?></p>
      <div class="ic-lyc-hero__trust">
        <span class="ic-lyc-trust-badge">&#10003; <?php esc_html_e( 'Join 50+ dealers', 'imanicars' ); ?></span>
        <span class="ic-lyc-trust-badge">&#10003; <?php esc_html_e( 'Live in 24 hours', 'imanicars' ); ?></span>
        <span class="ic-lyc-trust-badge">&#10003; <?php esc_html_e( 'Cancel anytime', 'imanicars' ); ?></span>
      </div>
      <a href="#ic-lyc-form" class="btn btn-primary btn-lg ic-lyc-hero__cta">
        <?php esc_html_e( 'List My Cars Free', 'imanicars' ); ?> &rarr;
      </a>
    </div>
    <div class="ic-lyc-hero__img-col">
      <img src="<?php echo esc_url( ic_unsplash( '1548199973-03cce0bbc87b', 560, 420 ) ); ?>"
           alt="Car dealer lot" width="560" height="420" loading="eager" class="ic-lyc-hero__img">
    </div>
  </div>
</section>

<!-- VALUE PROPS -->
<section class="ic-lyc-props section section--grey" aria-label="<?php esc_attr_e( 'Why list with us', 'imanicars' ); ?>">
  <div class="container">
    <h2 class="ic-section-title text-center"><?php esc_html_e( 'Why Dealers Choose Imani Cars', 'imanicars' ); ?></h2>
    <div class="ic-lyc-props__grid">
      <div class="ic-lyc-prop">
        <div class="ic-lyc-prop__icon feature-icon" aria-hidden="true">&#128272;</div>
        <h3 class="ic-lyc-prop__title"><?php esc_html_e( 'Free Forever Starter', 'imanicars' ); ?></h3>
        <p><?php esc_html_e( 'List up to 10 cars for free, forever. No credit card required. Upgrade only when you want more leads.', 'imanicars' ); ?></p>
      </div>
      <div class="ic-lyc-prop">
        <div class="ic-lyc-prop__icon feature-icon" aria-hidden="true">&#128200;</div>
        <h3 class="ic-lyc-prop__title"><?php esc_html_e( 'Real-Time Lead Notifications', 'imanicars' ); ?></h3>
        <p><?php esc_html_e( 'Buyer enquiries go straight to your email. Respond fast, close more deals. No middleman.', 'imanicars' ); ?></p>
      </div>
      <div class="ic-lyc-prop">
        <div class="ic-lyc-prop__icon feature-icon" aria-hidden="true">&#128205;</div>
        <h3 class="ic-lyc-prop__title"><?php esc_html_e( '4 City Reach', 'imanicars' ); ?></h3>
        <p><?php esc_html_e( 'Your listings appear in Brisbane, Melbourne, Perth and Darwin — all from one account.', 'imanicars' ); ?></p>
      </div>
      <div class="ic-lyc-prop">
        <div class="ic-lyc-prop__icon feature-icon" aria-hidden="true">&#128176;</div>
        <h3 class="ic-lyc-prop__title"><?php esc_html_e( 'Save $500+/Month', 'imanicars' ); ?></h3>
        <p><?php esc_html_e( 'carsales charges $726–$1,436/month. Imani Cars Pro is $349/month. Same audience, fraction of the cost.', 'imanicars' ); ?></p>
      </div>
      <div class="ic-lyc-prop">
        <div class="ic-lyc-prop__icon feature-icon" aria-hidden="true">&#128241;</div>
        <h3 class="ic-lyc-prop__title"><?php esc_html_e( 'Mobile-Optimised Listings', 'imanicars' ); ?></h3>
        <p><?php esc_html_e( '80% of car buyers browse on mobile. Your listings are fast and beautiful on every screen.', 'imanicars' ); ?></p>
      </div>
      <div class="ic-lyc-prop">
        <div class="ic-lyc-prop__icon feature-icon" aria-hidden="true">&#128202;</div>
        <h3 class="ic-lyc-prop__title"><?php esc_html_e( 'Analytics Dashboard', 'imanicars' ); ?></h3>
        <p><?php esc_html_e( 'See views, clicks and enquiries for every listing. Know which cars are getting attention and which need work.', 'imanicars' ); ?></p>
      </div>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="ic-lyc-steps section" aria-label="<?php esc_attr_e( 'How it works', 'imanicars' ); ?>">
  <div class="container">
    <h2 class="ic-section-title text-center"><?php esc_html_e( 'Live in 3 Simple Steps', 'imanicars' ); ?></h2>
    <div class="ic-lyc-steps__grid">
      <div class="ic-lyc-step">
        <div class="ic-lyc-step__num step-icon" aria-hidden="true">1</div>
        <h3 class="ic-lyc-step__title"><?php esc_html_e( 'Register Free', 'imanicars' ); ?></h3>
        <p><?php esc_html_e( 'Fill in the form below. No payment details needed. We\'ll verify your dealership within 24 hours.', 'imanicars' ); ?></p>
      </div>
      <div class="ic-lyc-step">
        <div class="ic-lyc-step__num step-icon" aria-hidden="true">2</div>
        <h3 class="ic-lyc-step__title"><?php esc_html_e( 'Upload Your Cars', 'imanicars' ); ?></h3>
        <p><?php esc_html_e( 'Log into your dealer dashboard and add up to 10 cars with photos, price and specs. Takes 10 minutes.', 'imanicars' ); ?></p>
      </div>
      <div class="ic-lyc-step">
        <div class="ic-lyc-step__num step-icon" aria-hidden="true">3</div>
        <h3 class="ic-lyc-step__title"><?php esc_html_e( 'Start Getting Leads', 'imanicars' ); ?></h3>
        <p><?php esc_html_e( 'Your listings go live and buyers can find and enquire about your cars. Leads hit your inbox instantly.', 'imanicars' ); ?></p>
      </div>
    </div>
  </div>
</section>

<!-- COMPARISON: IMANI VS CARSALES -->
<section class="ic-lyc-compare section section--grey" aria-label="<?php esc_attr_e( 'Imani Cars vs carsales comparison', 'imanicars' ); ?>">
  <div class="container">
    <h2 class="ic-section-title text-center"><?php esc_html_e( 'Imani Cars vs carsales.com.au', 'imanicars' ); ?></h2>
    <div class="ic-lyc-compare__wrap">
      <table class="ic-lyc-compare__table">
        <thead>
          <tr>
            <th scope="col"><?php esc_html_e( 'Feature', 'imanicars' ); ?></th>
            <th scope="col" class="ic-lyc-compare__our"><?php esc_html_e( 'Imani Cars', 'imanicars' ); ?></th>
            <th scope="col"><?php esc_html_e( 'carsales.com.au', 'imanicars' ); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php
          $rows = [
              [ 'Free starter plan',        'Yes — 10 listings free', 'No — paid only' ],
              [ 'Monthly cost (mid-tier)',   '$149/mo (Growth)',       '$726–$1,436/mo' ],
              [ 'Contract required',         'No',                    'Yes' ],
              [ 'Credit card to start',      'No',                    'Yes' ],
              [ 'Live time',                 '24 hours',              '24–48 hours' ],
              [ 'City-specific targeting',   'Brisbane, Melb, Perth, Darwin', 'National only' ],
              [ 'Lead notifications',        'Instant email alerts',  'Dashboard only' ],
              [ 'Analytics dashboard',       'Yes (Growth+)',         'Basic' ],
              [ 'Dedicated account manager', 'Yes (Premium)',         'Enterprise plans' ],
          ];
          foreach ( $rows as $row ) :
          ?>
          <tr>
            <td><?php echo esc_html( $row[0] ); ?></td>
            <td class="ic-lyc-compare__our"><span class="ic-check" aria-hidden="true">&#10003;</span> <?php echo esc_html( $row[1] ); ?></td>
            <td class="ic-lyc-compare__them"><?php echo esc_html( $row[2] ); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p class="text-center" style="margin-top:1.5rem;">
      <a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>" class="btn btn-primary">
        <?php esc_html_e( 'See Full Pricing Plans', 'imanicars' ); ?>
      </a>
    </p>
  </div>
</section>

<!-- SIGNUP FORM -->
<section class="ic-lyc-form-section section" id="ic-lyc-form" aria-label="<?php esc_attr_e( 'Dealer signup form', 'imanicars' ); ?>">
  <div class="container">
    <div class="ic-lyc-form-wrap">
      <div class="ic-lyc-form-info">
        <h2><?php esc_html_e( 'Start for Free Today', 'imanicars' ); ?></h2>
        <p><?php esc_html_e( "Fill in the form and we'll have your account ready within 24 hours. No payment, no contract.", 'imanicars' ); ?></p>
        <ul class="ic-lyc-form-perks">
          <li>&#10003; <?php esc_html_e( 'Free forever up to 10 listings', 'imanicars' ); ?></li>
          <li>&#10003; <?php esc_html_e( 'Instant email leads', 'imanicars' ); ?></li>
          <li>&#10003; <?php esc_html_e( 'Cancel anytime', 'imanicars' ); ?></li>
          <li>&#10003; <?php esc_html_e( 'No lock-in contract', 'imanicars' ); ?></li>
        </ul>
      </div>
      <form class="ic-lyc-form" id="ic-dealer-signup-form" novalidate>
        <?php wp_nonce_field( 'ic_nonce', 'ic_form_nonce' ); ?>
        <div class="ic-form-group">
          <label for="lyc-dealership" class="ic-form-label"><?php esc_html_e( 'Dealership Name', 'imanicars' ); ?> <span class="ic-form-req" aria-hidden="true">*</span></label>
          <input type="text" id="lyc-dealership" name="dealership" class="ic-form-input"
                 placeholder="<?php esc_attr_e( 'e.g. City Cars Brisbane', 'imanicars' ); ?>" required aria-required="true">
        </div>
        <div class="ic-form-group">
          <label for="lyc-contact" class="ic-form-label"><?php esc_html_e( 'Contact Name', 'imanicars' ); ?> <span class="ic-form-req" aria-hidden="true">*</span></label>
          <input type="text" id="lyc-contact" name="contact" class="ic-form-input"
                 placeholder="<?php esc_attr_e( 'Your full name', 'imanicars' ); ?>" required aria-required="true">
        </div>
        <div class="ic-form-group">
          <label for="lyc-phone" class="ic-form-label"><?php esc_html_e( 'Phone Number', 'imanicars' ); ?> <span class="ic-form-req" aria-hidden="true">*</span></label>
          <input type="tel" id="lyc-phone" name="phone" class="ic-form-input"
                 placeholder="<?php esc_attr_e( '04XX XXX XXX', 'imanicars' ); ?>" required aria-required="true">
        </div>
        <div class="ic-form-group">
          <label for="lyc-email" class="ic-form-label"><?php esc_html_e( 'Business Email', 'imanicars' ); ?> <span class="ic-form-req" aria-hidden="true">*</span></label>
          <input type="email" id="lyc-email" name="email" class="ic-form-input"
                 placeholder="<?php esc_attr_e( 'you@dealership.com.au', 'imanicars' ); ?>" required aria-required="true">
        </div>
        <div class="ic-form-group">
          <label for="lyc-city" class="ic-form-label"><?php esc_html_e( 'City', 'imanicars' ); ?> <span class="ic-form-req" aria-hidden="true">*</span></label>
          <select id="lyc-city" name="city" class="ic-form-select" required aria-required="true">
            <option value=""><?php esc_html_e( 'Select your city', 'imanicars' ); ?></option>
            <option value="Brisbane"><?php esc_html_e( 'Brisbane', 'imanicars' ); ?></option>
            <option value="Melbourne"><?php esc_html_e( 'Melbourne', 'imanicars' ); ?></option>
            <option value="Perth"><?php esc_html_e( 'Perth', 'imanicars' ); ?></option>
            <option value="Darwin"><?php esc_html_e( 'Darwin', 'imanicars' ); ?></option>
          </select>
        </div>
        <div class="ic-form-group">
          <label for="lyc-cars" class="ic-form-label"><?php esc_html_e( 'Number of Cars in Your Yard', 'imanicars' ); ?></label>
          <select id="lyc-cars" name="cars_count" class="ic-form-select">
            <option value=""><?php esc_html_e( 'Select range', 'imanicars' ); ?></option>
            <option value="1-10"><?php esc_html_e( '1–10 cars', 'imanicars' ); ?></option>
            <option value="11-25"><?php esc_html_e( '11–25 cars', 'imanicars' ); ?></option>
            <option value="26-50"><?php esc_html_e( '26–50 cars', 'imanicars' ); ?></option>
            <option value="50+"><?php esc_html_e( '50+ cars', 'imanicars' ); ?></option>
          </select>
        </div>
        <div id="ic-lyc-msg" class="ic-form-msg" role="alert" aria-live="polite" hidden></div>
        <button type="submit" class="btn btn-primary btn-lg btn-full ic-lyc-form__submit">
          <?php esc_html_e( 'Register My Dealership Free', 'imanicars' ); ?>
        </button>
        <p class="ic-form-disclaimer">
          <?php esc_html_e( 'By submitting you agree to our Terms of Service. No spam, ever.', 'imanicars' ); ?>
        </p>
      </form>
    </div>
  </div>
</section>

<!-- TESTIMONIALS STRIP -->
<section class="ic-lyc-testimonials section section--grey" aria-label="<?php esc_attr_e( 'Dealer testimonials', 'imanicars' ); ?>">
  <div class="container">
    <h2 class="ic-section-title text-center"><?php esc_html_e( 'What Dealers Are Saying', 'imanicars' ); ?></h2>
    <div class="ic-testimonials__grid">
      <article class="ic-review-card">
        <div class="ic-review-card__stars" aria-label="5 out of 5 stars">
          <span class="ic-review-card__star" aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
        </div>
        <blockquote class="ic-review-card__text">"<?php esc_html_e( "We were paying $900/month on carsales. Switched to Imani Cars Pro ($349) and our leads actually went UP. Couldn't be happier.", 'imanicars' ); ?>"</blockquote>
        <div class="ic-review-card__author">
          <img src="<?php echo esc_url( ic_unsplash( '1570295999-10bde93aefc1', 80, 80 ) ); ?>" alt="Mark Davies" width="80" height="80" loading="lazy" class="ic-review-card__avatar">
          <div>
            <strong class="ic-review-card__name"><?php esc_html_e( 'Mark Davies', 'imanicars' ); ?></strong>
            <span class="ic-review-card__role"><?php esc_html_e( 'QLD Car Centre, Brisbane', 'imanicars' ); ?></span>
          </div>
        </div>
      </article>
      <article class="ic-review-card">
        <div class="ic-review-card__stars" aria-label="5 out of 5 stars">
          <span class="ic-review-card__star" aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
        </div>
        <blockquote class="ic-review-card__text">"<?php esc_html_e( "Setup was done in an afternoon. First lead came through the same evening. The free tier is genuinely useful — not a bait and switch.", 'imanicars' ); ?>"</blockquote>
        <div class="ic-review-card__author">
          <img src="<?php echo esc_url( ic_unsplash( '1507003211-2a6f260ac0e9', 80, 80 ) ); ?>" alt="Kevin Nguyen" width="80" height="80" loading="lazy" class="ic-review-card__avatar">
          <div>
            <strong class="ic-review-card__name"><?php esc_html_e( 'Kevin Nguyen', 'imanicars' ); ?></strong>
            <span class="ic-review-card__role"><?php esc_html_e( 'West Coast Autos, Perth', 'imanicars' ); ?></span>
          </div>
        </div>
      </article>
      <article class="ic-review-card">
        <div class="ic-review-card__stars" aria-label="5 out of 5 stars">
          <span class="ic-review-card__star" aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
        </div>
        <blockquote class="ic-review-card__text">"<?php esc_html_e( "We're in Darwin — carsales never prioritised our market. Imani Cars is local-focused and our listings actually show up in the right city.", 'imanicars' ); ?>"</blockquote>
        <div class="ic-review-card__author">
          <img src="<?php echo esc_url( ic_unsplash( '1494790108-d7b21cd3cd26', 80, 80 ) ); ?>" alt="Sarah Patten" width="80" height="80" loading="lazy" class="ic-review-card__avatar">
          <div>
            <strong class="ic-review-card__name"><?php esc_html_e( 'Sarah Patten', 'imanicars' ); ?></strong>
            <span class="ic-review-card__role"><?php esc_html_e( 'NT Car Centre, Darwin', 'imanicars' ); ?></span>
          </div>
        </div>
      </article>
    </div>
  </div>
</section>

<?php get_footer(); ?>
