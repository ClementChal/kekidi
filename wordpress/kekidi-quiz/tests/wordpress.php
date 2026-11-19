<?php
// Exécuter avec Studio : wp eval-file <chemin de ce fichier>.
$check = static function ( bool $condition, string $message ): void {
    if ( ! $condition ) {
        throw new RuntimeException( $message );
    }
};
$original_user = get_current_user_id();
$before = array_map( 'get_post', array( 1001, 1002, 1003 ) );
$user_id = wp_insert_user( array( 'user_login' => 'quiz-test-' . wp_generate_password( 8, false ), 'user_pass' => wp_generate_password(), 'role' => 'editor' ) );
$check( ! is_wp_error( $user_id ), 'Créer le compte de test' );
$post_id = 0;
try {
    wp_set_current_user( $user_id );
    $request = new WP_REST_Request( 'POST', '/wp/v2/posts' );
    $request->set_body_params( array( 'title' => 'Test temporaire quiz', 'status' => 'draft', 'content' => '<!-- wp:kekidi/quiz /-->' ) );
    $response = rest_do_request( $request );
    $check( 201 === $response->get_status(), 'Un éditeur peut enregistrer un brouillon incomplet' );
    $post_id = $response->get_data()['id'];
    $request = new WP_REST_Request( 'POST', '/wp/v2/posts/' . $post_id );
    $request->set_body_params( array( 'status' => 'publish' ) );
    $check( 400 === rest_do_request( $request )->get_status(), 'Bloquer la publication sans renvoyer le contenu' );
    $choices = array( array( 'label' => 'Oui', 'correct' => true ), array( 'label' => 'Non', 'correct' => false ) );
    $content = '<!-- wp:kekidi/quiz ' . wp_json_encode( array( 'choices' => $choices ) ) . ' /-->';
    $request->set_body_params( array( 'status' => 'publish', 'content' => $content ) );
    $check( 200 === rest_do_request( $request )->get_status(), 'Un éditeur peut publier un quiz valide' );
    $url = get_permalink( $post_id );
    $rendered = do_blocks( $content );
    $check( false !== strpos( $rendered, 'Une seule réponse possible.' ) && 2 === substr_count( $rendered, 'type="radio"' ), 'Consigne et contrôles simples' );
    $choices[1]['correct'] = true;
    $multiple = do_blocks( '<!-- wp:kekidi/quiz ' . wp_json_encode( array( 'choices' => $choices ) ) . ' /-->' );
    $check( false !== strpos( $multiple, 'Plusieurs réponses possibles.' ) && 2 === substr_count( $multiple, 'type="checkbox"' ), 'Consigne multiple uniquement avec plusieurs bonnes réponses' );
    $request->set_body_params( array( 'title' => 'Titre modifié', 'content' => '<!-- wp:paragraph --><p>Réponse classique</p><!-- /wp:paragraph -->' ) );
    $check( 200 === rest_do_request( $request )->get_status(), 'Conversion vers une réponse classique' );
    $check( $url === get_permalink( $post_id ), 'Permalien stable après changement de titre et de format' );
    $check( $before == array_map( 'get_post', array( 1001, 1002, 1003 ) ), 'Les anciens articles restent intacts' );
    echo "Éditeur : brouillon, publication, validation, réponse unique, conversion et URL stable vérifiés.\n";
} finally {
    if ( $post_id ) {
        wp_delete_post( $post_id, true );
    }
    wp_set_current_user( $original_user );
    require_once ABSPATH . 'wp-admin/includes/user.php';
    wp_delete_user( $user_id );
}
