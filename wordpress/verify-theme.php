<?php
/** Vérification d'intégration : cartes natives et navigation cyclique. */

$posts = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'ID', 'order' => 'ASC' ) );
if ( ! $posts ) {
    WP_CLI::error( 'Publier au moins une réponse avant cette vérification.' );
}

$home_id = (int) get_option( 'page_on_front' );
$home_page = get_post( $home_id );
$front_template = file_get_contents( get_theme_file_path( 'templates/front-page.html' ) );
if ( 'page' !== get_option( 'show_on_front' ) || ! $home_page || ! str_contains( $front_template, 'wp:post-content' ) ) {
    WP_CLI::error( 'La page Accueil doit être la page statique et le modèle doit afficher son contenu.' );
}
$home_blocks = parse_blocks( $home_page->post_content );
$locked_sections = array_filter( $home_blocks, static function ( array $block ): bool {
    return 'core/group' === $block['blockName'] && 'contentOnly' === ( $block['attrs']['templateLock'] ?? null );
} );
$editor_role = get_role( 'editor' );
if ( 4 !== count( $locked_sections ) || ! $editor_role->has_cap( 'edit_pages' ) || $editor_role->has_cap( 'edit_theme_options' ) ) {
    WP_CLI::error( 'La page doit avoir quatre sections en édition de contenu seul et être modifiable par un Éditeur sans droit de modifier les modèles.' );
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
WP_CLI::success( 'Accueil modifiable (4 sections verrouillées), droits Éditeur, cartes et navigation vérifiés.' );
