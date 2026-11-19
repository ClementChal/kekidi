<?php
/** Vérification d'intégration : cartes natives et navigation cyclique. */

$posts = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'ID', 'order' => 'ASC' ) );
if ( ! $posts ) {
    WP_CLI::error( 'Publier au moins une réponse avant cette vérification.' );
}

$home_id = (int) get_option( 'page_on_front' );
$home_page = get_post( $home_id );
$front_template = file_get_contents( get_theme_file_path( 'templates/front-page.html' ) );
if ( 'page' !== get_option( 'show_on_front' ) || ! $home_page || ! has_block( 'core/pattern', $front_template ) || false === strpos( $front_template, '"slug":"kekidi/accueil"' ) ) {
    WP_CLI::error( 'La page Accueil doit être statique et son modèle doit afficher la composition kekidi/accueil.' );
}
$editor_role = get_role( 'editor' );
if ( ! $editor_role || ! $editor_role->has_cap( 'edit_pages' ) || $editor_role->has_cap( 'edit_theme_options' ) ) {
    WP_CLI::error( 'Le rôle Éditeur doit pouvoir modifier les pages sans droit de modifier les modèles.' );
}

$home = do_blocks( '<!-- wp:pattern {"slug":"kekidi/accueil"} /-->' );
preg_match_all( '~<a class="question-card-link" href="([^"]+)"~', $home, $links );
$expected = array_map( 'get_permalink', array_slice( $posts, 0, 12 ) );
if ( $links[1] !== $expected ) {
    WP_CLI::error( 'Les cartes ne pointent pas vers les réponses dans l’ordre attendu.' );
}
preg_match_all( '~<article\b[^>]*class="[^"]*question-card[^>]*>(.*?)</article>~s', $home, $cards );
foreach ( $cards[1] as $card ) {
    if ( 1 !== substr_count( $card, '<a ' ) ) {
        WP_CLI::error( 'Chaque carte doit contenir un seul lien couvrant sa surface.' );
    }
}

foreach ( $posts as $index => $post ) {
    $GLOBALS['post'] = $post;
    setup_postdata( $post );
    $next = render_block( array(
        'blockName' => 'core/post-navigation-link',
        'attrs' => array( 'type' => 'next', 'className' => 'read-link kekidi-next' ),
        'innerBlocks' => array(), 'innerHTML' => '', 'innerContent' => array(),
    ) );
    if ( count( $posts ) > 1 && false === strpos( $next, esc_url( get_permalink( $posts[ ( $index + 1 ) % count( $posts ) ] ) ) ) ) {
        WP_CLI::error( 'La navigation vers la question suivante est incorrecte.' );
    }
    if ( 1 === count( $posts ) && '' !== $next ) {
        WP_CLI::error( 'Une réponse unique ne doit pas avoir de lien suivant.' );
    }
}
wp_reset_postdata();
WP_CLI::success( 'Accueil statique et composition du thème, droits Éditeur, cartes et navigation vérifiés.' );
