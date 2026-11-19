<?php
// Prépare des brouillons et fichiers de blocs ; les valider avant toute publication.
$assemblee = kekidi_scrutins_import( 'assemblee', '17', 7987 );
$senate = kekidi_scrutins_import( 'senat', '2025', 33 );
$examples = array(
    array( 'slug' => 'scrutin-7987-pour', 'title' => 'Qui a voté pour la présomption de légitime défense ?', 'direction' => 'pour', 'snapshot' => $assemblee, 'measure' => 'Reconnaître une présomption de légitime défense pour les forces de l’ordre dans l’exercice de leurs fonctions.', 'explanation' => 'Le scrutin porte sur l’ensemble de la proposition de loi, en première lecture. Les résultats décrivent les votes enregistrés lors de ce scrutin. Une majorité au sein d’un groupe ne signifie pas que tous ses membres ont voté de la même façon.' ),
    array( 'slug' => 'scrutin-7987-contre', 'title' => 'Qui a voté contre cette proposition sur la légitime défense ?', 'direction' => 'contre', 'snapshot' => $assemblee, 'measure' => 'Reconnaître une présomption de légitime défense pour les forces de l’ordre dans l’exercice de leurs fonctions.', 'explanation' => 'Cette variante demande de retrouver les groupes qui ont voté majoritairement contre la même proposition. Les décomptes restent identiques ; seul le sens de la question change.' ),
    array( 'slug' => 'scrutin-senat-2025-33', 'title' => 'Qui a voté pour supprimer l’article 5 bis du PLFSS 2026 ?', 'direction' => 'pour', 'snapshot' => $senate, 'measure' => 'Supprimer l’article 5 bis du projet de loi de financement de la sécurité sociale pour 2026, avec l’amendement n° 591.', 'explanation' => 'Le scrutin porte sur un amendement de suppression. Voter pour signifie ici soutenir la suppression proposée. Les groupes et leurs décomptes sont ceux publiés par le Sénat pour ce scrutin.' ),
);
foreach ( $examples as $example ) {
    $post = get_page_by_path( $example['slug'], OBJECT, 'post' );
    if ( $post && ! get_post_meta( $post->ID, '_kekidi_scrutin_demo', true ) ) { throw new RuntimeException( 'Un article existant utilise déjà ce slug.' ); }
    $id = $post ? $post->ID : wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'draft', 'post_title' => $example['title'], 'post_name' => $example['slug'], 'comment_status' => 'closed' ) );
    update_post_meta( $id, '_kekidi_scrutin_demo', true );
    $snapshot_id = kekidi_scrutins_store_snapshot( $id, $example['snapshot'] );
    $attrs = wp_json_encode( array( 'snapshotId' => $snapshot_id, 'direction' => $example['direction'], 'measure' => $example['measure'] ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
    $content = '<!-- wp:kekidi/quiz-scrutin ' . $attrs . ' -->' . "\n" . '<!-- wp:paragraph -->' . "\n" . '<p>' . esc_html( $example['explanation'] ) . '</p>' . "\n" . '<!-- /wp:paragraph -->' . "\n" . '<!-- /wp:kekidi/quiz-scrutin -->';
    $file = ABSPATH . 'tmp/' . $example['slug'] . '.html';
    file_put_contents( $file, $content );
    echo wp_json_encode( array( 'postId' => $id, 'file' => $file, 'snapshotId' => $snapshot_id, 'slug' => $example['slug'] ), JSON_UNESCAPED_SLASHES ) . "\n";
}
