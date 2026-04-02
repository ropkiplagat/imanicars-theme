<?php
/**
 * Search Results Template — Imani Cars
 */
get_header();
$search_query = get_search_query();
?>

<div class="ic-search-results-page">
  <div class="container">

    <div class="ic-search-results-header">
      <h1 class="ic-search-results-header__title">
        <?php
        if ( $search_query ) {
            /* translators: %s: search query */
            printf( esc_html__( 'Search results for: %s', 'imanicars' ), '<span class="ic-search-results-header__term">' . esc_html( $search_query ) . '</span>' );
        } else {
            esc_html_e( 'Search Results', 'imanicars' );
        }
        ?>
      </h1>
      <?php if ( have_posts() ) : ?>
      <p class="ic-search-results-header__count">
        <?php
        global $wp_query;
        /* translators: %d: number of results */
        printf( esc_html__( '%d results found', 'imanicars' ), (int) $wp_query->found_posts );
        ?>
      </p>
      <?php endif; ?>
    </div>

    <!-- REFINE SEARCH -->
    <div class="ic-search-results-refine">
      <form class="ic-search-form ic-search-form--inline" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
        <input type="search" name="s" class="ic-search-form__input" value="<?php echo esc_attr( $search_query ); ?>" placeholder="<?php esc_attr_e( 'Search cars, makes, models...', 'imanicars' ); ?>" aria-label="<?php esc_attr_e( 'Search', 'imanicars' ); ?>">
        <button type="submit" class="btn btn-primary"><?php esc_html_e( 'Search', 'imanicars' ); ?></button>
      </form>
    </div>

    <?php if ( have_posts() ) : ?>
    <div class="ic-car-grid ic-car-grid--search">
      <?php
      while ( have_posts() ) :
          the_post();
          if ( get_post_type() === 'vehicle' ) {
              get_template_part( 'template-parts/car-card' );
          } else {
              get_template_part( 'template-parts/content' );
          }
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
