<?php
defined( 'ABSPATH' ) || exit;

function kekidi_scrutins_snapshot( int $post_id, string $id ): ?array {
    if ( ! preg_match( '/^[a-f0-9]{64}$/D', $id ) ) { return null; }
    $post_id = (int) ( wp_is_post_revision( $post_id ) ?: $post_id );
    $snapshot = get_post_meta( $post_id, '_kekidi_scrutin_snapshot_' . $id, true );
    if ( is_array( $snapshot ) ) { return $snapshot; }
    // Compatibilité avec les premiers exemples locaux.
    $snapshots = get_post_meta( $post_id, '_kekidi_scrutin_snapshots', true );
    return is_array( $snapshots ) && isset( $snapshots[ $id ] ) ? $snapshots[ $id ] : null;
}

function kekidi_scrutins_store_snapshot( int $post_id, array $snapshot ): string {
    $identity = $snapshot;
    unset( $identity['retrievedAt'] );
    $id = hash( 'sha256', wp_json_encode( $identity, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
    if ( ! kekidi_scrutins_snapshot( $post_id, $id ) && ! add_post_meta( $post_id, '_kekidi_scrutin_snapshot_' . $id, wp_slash( $snapshot ), true ) && ! kekidi_scrutins_snapshot( $post_id, $id ) ) {
        throw new RuntimeException( 'Impossible d’enregistrer les données. Réessayez avant de publier.' );
    }
    return $id;
}

function kekidi_scrutins_permission( WP_REST_Request $request ) {
    $path = $request->get_url_params();
    $post_id = (int) ( 'GET' === $request->get_method() ? ( $path['post_id'] ?? 0 ) : $request['postId'] );
    if ( ! is_user_logged_in() || 'post' !== get_post_type( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
        return new WP_Error( 'kekidi_scrutin_forbidden', 'Vous devez pouvoir modifier cet article pour charger ses scrutins.', array( 'status' => is_user_logged_in() ? 403 : 401 ) );
    }
    return true;
}

add_action( 'rest_api_init', function (): void {
    register_rest_route( 'kekidi/v1', '/scrutins/import', array(
        'methods' => 'POST', 'permission_callback' => 'kekidi_scrutins_permission',
        'args' => array(
            'postId' => array( 'type' => 'integer', 'minimum' => 1, 'required' => true ),
            'chamber' => array( 'type' => 'string', 'enum' => array( 'assemblee', 'senat' ), 'required' => true ),
            'period' => array( 'type' => 'string', 'pattern' => '^(17|20[0-9]{2})$', 'required' => true ),
            'number' => array( 'type' => 'integer', 'minimum' => 1, 'maximum' => 999999, 'required' => true ),
        ),
        'callback' => function ( WP_REST_Request $request ) {
            $post_id = (int) $request['postId'];
            try {
                $snapshot = kekidi_scrutins_import( $request['chamber'], $request['period'], (int) $request['number'] );
                $id = kekidi_scrutins_store_snapshot( $post_id, $snapshot );
                return array( 'snapshotId' => $id, 'snapshot' => kekidi_scrutins_snapshot( $post_id, $id ) );
            } catch ( InvalidArgumentException $error ) {
                return new WP_Error( 'kekidi_scrutin_import_failed', $error->getMessage(), array( 'status' => 422 ) );
            } catch ( RuntimeException $error ) {
                return new WP_Error( 'kekidi_scrutin_storage_failed', $error->getMessage(), array( 'status' => 500 ) );
            }
        },
    ) );
    register_rest_route( 'kekidi/v1', '/scrutins/(?P<post_id>\d+)/(?P<snapshot_id>[a-f0-9]{64})', array(
        'methods' => 'GET', 'permission_callback' => 'kekidi_scrutins_permission',
        'args' => array( 'direction' => array( 'type' => 'string', 'enum' => array( 'pour', 'contre' ), 'default' => 'pour' ) ),
        'callback' => function ( WP_REST_Request $request ) {
            $path = $request->get_url_params();
            $snapshot = kekidi_scrutins_snapshot( (int) $path['post_id'], $path['snapshot_id'] );
            if ( $snapshot ) { $snapshot['defaultExplanation'] = kekidi_scrutins_default_explanation( $snapshot, $request['direction'] ); }
            return $snapshot ? $snapshot : new WP_Error( 'kekidi_scrutin_missing', 'Données introuvables pour cet article. Rechargez le scrutin.', array( 'status' => 404 ) );
        },
    ) );
} );

function kekidi_scrutins_local_explanation( array $blocks ): bool {
    $origin = wp_parse_url( home_url(), PHP_URL_HOST );
    foreach ( $blocks as $block ) {
        if ( ! in_array( $block['blockName'], array( 'core/paragraph', 'core/heading', 'core/list', 'core/list-item', 'core/image', 'core/group', 'core/quote', 'core/details' ), true ) ) { return false; }
        $tags = new WP_HTML_Tag_Processor( $block['innerHTML'] ?? '' );
        while ( $tags->next_tag() ) {
            if ( in_array( $tags->get_tag(), array( 'IFRAME', 'EMBED', 'OBJECT', 'SCRIPT', 'STYLE', 'LINK', 'VIDEO', 'AUDIO', 'SOURCE' ), true ) ) { return false; }
            if ( 'IMG' === $tags->get_tag() ) {
                $src = (string) $tags->get_attribute( 'src' );
                if ( wp_parse_url( $src, PHP_URL_HOST ) !== $origin || ! in_array( wp_parse_url( $src, PHP_URL_SCHEME ), array( 'http', 'https' ), true ) ) { return false; }
                $srcset = (string) $tags->get_attribute( 'srcset' );
                foreach ( array_filter( array_map( 'trim', explode( ',', $srcset ) ) ) as $candidate ) {
                    $url = preg_split( '/\s+/', $candidate )[0];
                    if ( wp_parse_url( $url, PHP_URL_HOST ) !== $origin ) { return false; }
                }
            }
        }
        if ( ! kekidi_scrutins_local_explanation( $block['innerBlocks'] ?? array() ) ) { return false; }
    }
    return true;
}

// Éviter aussi les intégrations distantes dans l’aperçu d’un brouillon invalide.
add_filter( 'render_block_data', function ( array $block ): array {
    if ( 'kekidi/quiz-scrutin' === $block['blockName'] && ! kekidi_scrutins_local_explanation( $block['innerBlocks'] ?? array() ) ) {
        $block['innerBlocks'] = array();
        $block['innerContent'] = array();
        $block['innerHTML'] = '';
    }
    return $block;
} );

function kekidi_scrutins_content_error( string $content, int $post_id ): string {
    $quizzes = array();
    $visit = static function ( array $blocks ) use ( &$visit, &$quizzes ): void {
        foreach ( $blocks as $block ) {
            if ( in_array( $block['blockName'], array( 'kekidi/quiz', 'kekidi/quiz-scrutin' ), true ) ) { $quizzes[] = $block; }
            $visit( $block['innerBlocks'] ?? array() );
        }
    };
    $visit( parse_blocks( $content ) );
    $votes = array_filter( $quizzes, static function ( array $block ): bool { return 'kekidi/quiz-scrutin' === $block['blockName']; } );
    if ( ! $votes ) { return ''; }
    if ( count( $quizzes ) > 1 ) { return 'Une réponse ne peut contenir qu’un seul quiz.'; }
    $vote = reset( $votes );
    $attributes = $vote['attrs'];
    if ( ! kekidi_scrutins_local_explanation( $vote['innerBlocks'] ?? array() ) ) { return 'Utilisez des blocs texte et des images de la médiathèque pour l’explication du quiz.'; }
    if ( ! in_array( $attributes['direction'] ?? 'pour', array( 'pour', 'contre' ), true ) ) { return 'Choisissez le sens du quiz : pour ou contre.'; }
    if ( ! kekidi_scrutins_snapshot( $post_id, $attributes['snapshotId'] ?? '' ) ) { return 'Chargez un scrutin vérifié avant de publier.'; }
    if ( '' === trim( wp_strip_all_tags( $attributes['measure'] ?? '' ) ) ) { return 'Décrivez la mesure soumise au vote avant de publier.'; }
    foreach ( $attributes['logos'] ?? array() as $attachment_id ) {
        if ( ! is_int( $attachment_id ) || $attachment_id < 0 || ( $attachment_id && ! in_array( get_post_mime_type( $attachment_id ), array( 'image/png', 'image/jpeg', 'image/webp', 'image/gif', 'image/avif' ), true ) ) ) { return 'Choisissez un logo au format image dans la médiathèque.'; }
    }
    return '';
}

add_filter( 'rest_pre_insert_post', function ( $prepared, WP_REST_Request $request ) {
    if ( is_wp_error( $prepared ) ) { return $prepared; }
    $id = (int) $request['id'];
    if ( ! in_array( $prepared->post_status ?? get_post_status( $id ), array( 'publish', 'future' ), true ) ) { return $prepared; }
    $error = kekidi_scrutins_content_error( (string) ( $prepared->post_content ?? get_post_field( 'post_content', $id ) ), $id );
    return $error ? new WP_Error( 'kekidi_scrutin_invalid', $error, array( 'status' => 400 ) ) : $prepared;
}, 20, 2 );

// Les autres voies de publication restent en brouillon si le quiz est incomplet.
add_filter( 'wp_insert_post_data', function ( array $data, array $postarr ): array {
    if ( 'post' === $data['post_type'] && in_array( $data['post_status'], array( 'publish', 'future' ), true ) && kekidi_scrutins_content_error( wp_unslash( $data['post_content'] ), (int) ( $postarr['ID'] ?? 0 ) ) ) { $data['post_status'] = 'draft'; }
    return $data;
}, 20, 2 );
