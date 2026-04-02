<?php
/**
 * Template Name: Dealer Pricing
 * 4-tier pricing table — Free / Growth / Pro / Premium
 */
get_header();
?>

<div class="ic-pricing-page">

  <!-- PAGE HEADER -->
  <section class="ic-page-hero ic-pricing-hero section" aria-label="<?php esc_attr_e( 'Pricing hero', 'imanicars' ); ?>">
    <div class="container text-center">
      <h1 class="ic-page-hero__title"><?php esc_html_e( 'Simple, Transparent Pricing', 'imanicars' ); ?></h1>
      <p class="ic-page-hero__sub"><?php esc_html_e( 'Start free. Scale when you need more. Cancel anytime — no lock-in contracts.', 'imanicars' ); ?></p>

      <!-- ANNUAL TOGGLE -->
      <div class="ic-pricing-toggle" role="group" aria-label="<?php esc_attr_e( 'Billing period', 'imanicars' ); ?>">
        <span class="ic-pricing-toggle__label"><?php esc_html_e( 'Monthly', 'imanicars' ); ?></span>
        <button class="ic-pricing-toggle__btn" id="ic-billing-toggle" role="switch" aria-checked="false" aria-label="<?php esc_attr_e( 'Toggle annual billing', 'imanicars' ); ?>">
          <span class="ic-pricing-toggle__knob"></span>
        </button>
        <span class="ic-pricing-toggle__label"><?php esc_html_e( 'Annual', 'imanicars' ); ?></span>
        <span class="ic-pricing-toggle__save"><?php esc_html_e( 'Save 20%', 'imanicars' ); ?></span>
      </div>
    </div>
  </section>

  <!-- PRICING TABLE -->
  <section class="ic-pricing-section section" aria-label="<?php esc_attr_e( 'Pricing plans', 'imanicars' ); ?>">
    <div class="container">
      <div class="ic-pricing-grid" id="ic-pricing-grid">

        <!-- FREE TIER -->
        <div class="ic-pricing-card ic-pricing-card--free">
          <div class="ic-pricing-card__header">
            <span class="ic-pricing-card__tier ic-pricing-card__tier--free"><?php esc_html_e( 'Starter', 'imanicars' ); ?></span>
            <div class="ic-pricing-card__price">
              <span class="ic-pricing-card__amount"><?php esc_html_e( 'Free', 'imanicars' ); ?></span>
              <span class="ic-pricing-card__period"><?php esc_html_e( 'forever', 'imanicars' ); ?></span>
            </div>
            <p class="ic-pricing-card__tagline"><?php esc_html_e( 'Perfect to get started. No risk.', 'imanicars' ); ?></p>
          </div>
          <ul class="ic-pricing-card__features">
            <li class="ic-pricing-card__feature ic-pricing-card__feature--yes">&#10003; <?php esc_html_e( 'Up to 10 car listings', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--yes">&#10003; <?php esc_html_e( 'Basic dealer profile', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--yes">&#10003; <?php esc_html_e( 'Buyer email enquiries', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--yes">&#10003; <?php esc_html_e( 'Listed in all 4 cities', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--no">&#10007; <?php esc_html_e( 'Dealer logo displayed', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--no">&#10007; <?php esc_html_e( 'Priority in search results', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--no">&#10007; <?php esc_html_e( 'Analytics dashboard', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--no">&#10007; <?php esc_html_e( 'Lead notifications', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--no">&#10007; <?php esc_html_e( 'Homepage placement', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--no">&#10007; <?php esc_html_e( 'Social media promotion', 'imanicars' ); ?></li>
          </ul>
          <div class="ic-pricing-card__footer">
            <a href="<?php echo esc_url( home_url( '/list-your-car/' ) ); ?>" class="btn btn-outline btn-full ic-pricing-cta">
              <?php esc_html_e( 'Start Free', 'imanicars' ); ?>
            </a>
            <p class="ic-pricing-card__note"><?php esc_html_e( 'No credit card required', 'imanicars' ); ?></p>
          </div>
        </div>

        <!-- GROWTH TIER -->
        <div class="ic-pricing-card ic-pricing-card--growth">
          <div class="ic-pricing-card__header">
            <span class="ic-pricing-card__tier ic-pricing-card__tier--growth"><?php esc_html_e( 'Growth', 'imanicars' ); ?></span>
            <div class="ic-pricing-card__price">
              <span class="ic-pricing-card__amount ic-price-monthly">$149</span>
              <span class="ic-pricing-card__amount ic-price-annual" hidden>$119</span>
              <span class="ic-pricing-card__period"><?php esc_html_e( '/month', 'imanicars' ); ?></span>
            </div>
            <p class="ic-pricing-card__tagline"><?php esc_html_e( 'Ideal for growing dealerships.', 'imanicars' ); ?></p>
          </div>
          <ul class="ic-pricing-card__features">
            <li class="ic-pricing-card__feature ic-pricing-card__feature--yes">&#10003; <?php esc_html_e( 'Up to 50 car listings', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--yes">&#10003; <?php esc_html_e( 'Full dealer profile page', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--yes">&#10003; <?php esc_html_e( 'Dealer logo displayed', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--yes">&#10003; <?php esc_html_e( 'Priority in search results', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--yes">&#10003; <?php esc_html_e( 'Lead notifications (email)', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--yes">&#10003; <?php esc_html_e( 'Analytics dashboard', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--yes">&#10003; <?php esc_html_e( 'Listed in all 4 cities', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--no">&#10007; <?php esc_html_e( 'Featured dealer badge', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--no">&#10007; <?php esc_html_e( 'Homepage placement', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--no">&#10007; <?php esc_html_e( 'Social media promotion', 'imanicars' ); ?></li>
          </ul>
          <div class="ic-pricing-card__footer">
            <a href="<?php echo esc_url( home_url( '/list-your-car/?plan=growth' ) ); ?>" class="btn btn-dark btn-full ic-pricing-cta">
              <?php esc_html_e( 'Start Trial', 'imanicars' ); ?>
            </a>
            <p class="ic-pricing-card__note"><?php esc_html_e( '14-day free trial included', 'imanicars' ); ?></p>
          </div>
        </div>

        <!-- PRO TIER — HIGHLIGHTED -->
        <div class="ic-pricing-card ic-pricing-card--pro ic-pricing-card--featured">
          <div class="ic-pricing-card__popular"><?php esc_html_e( 'Most Popular', 'imanicars' ); ?></div>
          <div class="ic-pricing-card__header">
            <span class="ic-pricing-card__tier ic-pricing-card__tier--pro"><?php esc_html_e( 'Pro', 'imanicars' ); ?></span>
            <div class="ic-pricing-card__price">
              <span class="ic-pricing-card__amount ic-price-monthly">$349</span>
              <span class="ic-pricing-card__amount ic-price-annual" hidden>$279</span>
              <span class="ic-pricing-card__period"><?php esc_html_e( '/month', 'imanicars' ); ?></span>
            </div>
            <p class="ic-pricing-card__tagline"><?php esc_html_e( 'Best value for serious dealers.', 'imanicars' ); ?></p>
          </div>
          <ul class="ic-pricing-card__features">
            <li class="ic-pricing-card__feature ic-pricing-card__feature--yes">&#10003; <?php esc_html_e( 'Unlimited listings', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--yes">&#10003; <?php esc_html_e( 'Full dealer profile page', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--yes">&#10003; <?php esc_html_e( 'Dealer logo displayed', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--yes">&#10003; <?php esc_html_e( 'Featured dealer badge', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--yes">&#10003; <?php esc_html_e( 'Top of search results', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--yes">&#10003; <?php esc_html_e( 'Homepage placement', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--yes">&#10003; <?php esc_html_e( 'Real-time lead alerts (email + SMS)', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--yes">&#10003; <?php esc_html_e( 'Full analytics + conversion tracking', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--yes">&#10003; <?php esc_html_e( 'Listed in all 4 cities', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--no">&#10007; <?php esc_html_e( 'City homepage banner', 'imanicars' ); ?></li>
          </ul>
          <div class="ic-pricing-card__footer">
            <a href="<?php echo esc_url( home_url( '/list-your-car/?plan=pro' ) ); ?>" class="btn btn-primary btn-full ic-pricing-cta">
              <?php esc_html_e( 'Get Started', 'imanicars' ); ?>
            </a>
            <p class="ic-pricing-card__note"><?php esc_html_e( '14-day free trial included', 'imanicars' ); ?></p>
          </div>
        </div>

        <!-- PREMIUM TIER -->
        <div class="ic-pricing-card ic-pricing-card--premium">
          <div class="ic-pricing-card__header">
            <span class="ic-pricing-card__tier ic-pricing-card__tier--premium"><?php esc_html_e( 'Premium', 'imanicars' ); ?></span>
            <div class="ic-pricing-card__price">
              <span class="ic-pricing-card__amount ic-price-monthly">$699</span>
              <span class="ic-pricing-card__amount ic-price-annual" hidden>$559</span>
              <span class="ic-pricing-card__period"><?php esc_html_e( '/month', 'imanicars' ); ?></span>
            </div>
            <p class="ic-pricing-card__tagline"><?php esc_html_e( 'Maximum exposure. White-glove service.', 'imanicars' ); ?></p>
          </div>
          <ul class="ic-pricing-card__features">
            <li class="ic-pricing-card__feature ic-pricing-card__feature--yes">&#10003; <?php esc_html_e( 'Everything in Pro', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--yes">&#10003; <?php esc_html_e( 'City homepage banner ad', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--yes">&#10003; <?php esc_html_e( 'Social media promotion (weekly)', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--yes">&#10003; <?php esc_html_e( 'Dedicated account manager', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--yes">&#10003; <?php esc_html_e( 'Priority customer support', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--yes">&#10003; <?php esc_html_e( 'Custom dealer landing page', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--yes">&#10003; <?php esc_html_e( 'Monthly performance report', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--yes">&#10003; <?php esc_html_e( 'First access to new features', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--yes">&#10003; <?php esc_html_e( 'Full analytics suite', 'imanicars' ); ?></li>
            <li class="ic-pricing-card__feature ic-pricing-card__feature--yes">&#10003; <?php esc_html_e( 'Unlimited listings', 'imanicars' ); ?></li>
          </ul>
          <div class="ic-pricing-card__footer">
            <a href="<?php echo esc_url( home_url( '/contact/?plan=premium' ) ); ?>" class="btn btn-gold btn-full ic-pricing-cta">
              <?php esc_html_e( 'Contact Us', 'imanicars' ); ?>
            </a>
            <p class="ic-pricing-card__note"><?php esc_html_e( "Let's discuss your goals", 'imanicars' ); ?></p>
          </div>
        </div>

      </div><!-- /.ic-pricing-grid -->

      <p class="ic-pricing-disclaimer text-center">
        <?php esc_html_e( 'All prices in AUD. Annual billing saves 20%. Cancel anytime. No lock-in contracts.', 'imanicars' ); ?>
      </p>
    </div>
  </section>

  <!-- FEATURE COMPARISON TABLE -->
  <section class="ic-pricing-compare section section--grey" aria-label="<?php esc_attr_e( 'Full feature comparison', 'imanicars' ); ?>">
    <div class="container">
      <h2 class="ic-section-title text-center"><?php esc_html_e( 'Full Feature Comparison', 'imanicars' ); ?></h2>
      <div class="ic-pricing-compare__wrap">
        <table class="ic-pricing-compare__table">
          <thead>
            <tr>
              <th scope="col"><?php esc_html_e( 'Feature', 'imanicars' ); ?></th>
              <th scope="col"><?php esc_html_e( 'Starter', 'imanicars' ); ?></th>
              <th scope="col"><?php esc_html_e( 'Growth', 'imanicars' ); ?></th>
              <th scope="col" class="ic-pricing-compare__pro"><?php esc_html_e( 'Pro', 'imanicars' ); ?></th>
              <th scope="col"><?php esc_html_e( 'Premium', 'imanicars' ); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php
            $compare = [
                [ 'Listings',                   '10',   '50',   'Unlimited', 'Unlimited' ],
                [ 'Dealer profile page',         'Basic','Full', 'Full',      'Custom'    ],
                [ 'Dealer logo',                 '-',    'Yes',  'Yes',       'Yes'       ],
                [ 'Search priority',             '-',    'High', 'Highest',   'Highest'   ],
                [ 'Lead notifications',          '-',    'Email','Email+SMS', 'Email+SMS' ],
                [ 'Analytics dashboard',         '-',    'Yes',  'Full',      'Full'      ],
                [ 'Featured badge',              '-',    '-',    'Yes',       'Yes'       ],
                [ 'Homepage placement',          '-',    '-',    'Yes',       'Yes'       ],
                [ 'City banner ad',              '-',    '-',    '-',         'Yes'       ],
                [ 'Social media promotion',      '-',    '-',    '-',         'Weekly'    ],
                [ 'Dedicated account manager',   '-',    '-',    '-',         'Yes'       ],
                [ 'Custom landing page',         '-',    '-',    '-',         'Yes'       ],
                [ 'Monthly report',              '-',    '-',    '-',         'Yes'       ],
                [ '14-day free trial',           '-',    'Yes',  'Yes',       'Yes'       ],
                [ 'Price/month',                 'Free', '$149', '$349',      '$699'      ],
            ];
            foreach ( $compare as $row ) :
            ?>
            <tr>
              <td><?php echo esc_html( $row[0] ); ?></td>
              <td><?php echo esc_html( $row[1] ); ?></td>
              <td><?php echo esc_html( $row[2] ); ?></td>
              <td class="ic-pricing-compare__pro"><?php echo esc_html( $row[3] ); ?></td>
              <td><?php echo esc_html( $row[4] ); ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section class="ic-pricing-faq section" aria-label="<?php esc_attr_e( 'Pricing FAQ', 'imanicars' ); ?>">
    <div class="container ic-pricing-faq__inner">
      <h2 class="ic-section-title text-center"><?php esc_html_e( 'Frequently Asked Questions', 'imanicars' ); ?></h2>
      <?php
      $faqs = [
          [ 'q' => 'Is the free plan really free forever?', 'a' => 'Yes. The Starter plan (up to 10 listings) is free indefinitely. No credit card, no expiry. Upgrade only when you want more listings or features.' ],
          [ 'q' => 'Can I cancel at any time?', 'a' => 'Absolutely. Monthly plans cancel at end of the billing month. Annual plans cancel at end of the contract year with no penalties.' ],
          [ 'q' => 'Is there a setup fee?', 'a' => 'None. All plans are zero setup fee. Paid plans start after a 14-day free trial.' ],
          [ 'q' => 'How is the annual discount applied?', 'a' => 'When you switch to annual billing, you pay for 10 months upfront — 2 months free. Growth $119/mo, Pro $279/mo, Premium $559/mo (billed annually).' ],
          [ 'q' => 'What cities do my listings appear in?', 'a' => 'All listings appear in our national search. You can specifically target Brisbane, Melbourne, Perth and Darwin as location filters on your listings.' ],
      ];
      foreach ( $faqs as $i => $faq ) :
      ?>
      <div class="ic-faq-item" id="ic-faq-<?php echo esc_attr( $i ); ?>">
        <button class="ic-faq-item__q" aria-expanded="false" aria-controls="ic-faq-a-<?php echo esc_attr( $i ); ?>">
          <?php echo esc_html( $faq['q'] ); ?>
          <span class="ic-faq-item__icon" aria-hidden="true">+</span>
        </button>
        <div class="ic-faq-item__a" id="ic-faq-a-<?php echo esc_attr( $i ); ?>" hidden>
          <p><?php echo esc_html( $faq['a'] ); ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- BOTTOM CTA -->
  <section class="ic-pricing-bottom-cta section" aria-label="<?php esc_attr_e( 'Get started CTA', 'imanicars' ); ?>">
    <div class="container text-center">
      <h2><?php esc_html_e( 'Ready to Get More Car Leads?', 'imanicars' ); ?></h2>
      <p><?php esc_html_e( "Start with our free plan today. No risk, no credit card. Upgrade when you're ready.", 'imanicars' ); ?></p>
      <div class="ic-pricing-bottom-cta__btns">
        <a href="<?php echo esc_url( home_url( '/list-your-car/' ) ); ?>" class="btn btn-primary btn-lg"><?php esc_html_e( 'Start Free Today', 'imanicars' ); ?></a>
        <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn btn-outline"><?php esc_html_e( 'Talk to Sales', 'imanicars' ); ?></a>
      </div>
    </div>
  </section>

</div><!-- /.ic-pricing-page -->

<?php get_footer(); ?>
