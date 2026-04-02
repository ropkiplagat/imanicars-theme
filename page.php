<?php
/**
 * Generic Page Template — Imani Cars
 * Used for Finance, About, Contact, Sell Your Car, etc.
 */
get_header();

while ( have_posts() ) :
    the_post();
?>

<div class="ic-page-content">
  <div class="container">

    <!-- BREADCRUMB -->
    <nav class="ic-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'imanicars' ); ?>">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'imanicars' ); ?></a>
      <span aria-hidden="true"> &rsaquo; </span>
      <span aria-current="page"><?php the_title(); ?></span>
    </nav>

    <article class="ic-page-article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
      <header class="ic-page-article__header">
        <h1 class="ic-page-article__title"><?php the_title(); ?></h1>
      </header>
      <div class="ic-page-article__body">
        <?php the_content(); ?>
        <?php
        wp_link_pages( [
            'before' => '<nav class="page-links" aria-label="' . esc_attr__( 'Page', 'imanicars' ) . '">',
            'after'  => '</nav>',
        ] );
        ?>
      </div>
    </article>

  </div>
</div>

<?php
endwhile;
get_footer();
?>
