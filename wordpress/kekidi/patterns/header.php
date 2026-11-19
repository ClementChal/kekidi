<?php
/**
 * Title: En-tête Kekidi
 * Slug: kekidi/header
 * Categories: kekidi
 * Inserter: false
 *
 * @package Kekidi
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"tagName":"div","className":"site-header home-header","layout":{"type":"default"}} -->
<div class="wp-block-group site-header home-header"><!-- wp:group {"tagName":"div","className":"wrap header-row","layout":{"type":"default"}} -->
<div class="wp-block-group wrap header-row"><!-- wp:template-part {"slug":"brand","tagName":"div"} /--><!-- wp:group {"tagName":"nav","className":"navigation","layout":{"type":"default"}} -->
<nav class="wp-block-group navigation"><!-- wp:paragraph -->
<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>#questions">Les questions</a></p>
<!-- /wp:paragraph --><!-- wp:paragraph -->
<p><a class="collective-nav" href="<?php echo esc_url( home_url( '/' ) ); ?>#collectif">Le collectif</a></p>
<!-- /wp:paragraph --><!-- wp:paragraph -->
<p><a class="nav-pill" href="<?php echo esc_url( home_url( '/' ) ); ?>#principe">Comment ça marche ?</a></p>
<!-- /wp:paragraph --></nav>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
