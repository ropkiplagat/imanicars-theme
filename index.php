<?php
/**
 * Index — Fallback template — Imani Cars
 * Handles any request not caught by a more specific template.
 */
get_header();
?>

<div class="ic-index-page">
  <div class="container">
    <?php if ( have_posts() ) : ?>

    <h1 class="ic-index-page__title">
      <?php
      if ( is_home() && ! is_front_page() ) {
          single_post_title();
      } elseif ( is_category() ) {
          single_cat_title( esc_html__( 'Category: ', 'imanicars' ) );
      } elseif ( is_tag() ) {
          single_tag_title( esc_html__( 'Tag: ', 'imanicars' ) );
      } elseif ( is_author() ) {
          the_author();
      } elseif ( is_year() ) {
          echo get_the_date( 'Y' );
      } elseif ( is_month() ) {
          echo get_the_date( 'F Y' );
      } elseif ( is_day() ) {
          echo get_the_date( get_option( 'date_format' ) );
      } else {
          esc_html_e( 'Latest Posts', 'imanicars' );
      }
      ?>
    </h1>

    <div class="ic-index-grid">
      <?php
      while ( have_posts() ) :
          the_post();
          get_template_part( 'template-parts/content' );
      endwhile;
      ?>
    </div>

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
    <?php get_template_part( 'template-parts/content', 'none' ); ?>
    <?php endif; ?>
  </div>
</div>

<?php get_footer(); ?>
