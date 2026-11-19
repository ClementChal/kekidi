<?php
// Studio WP-CLI : eval-file <ce fichier> --user=admin après le test navigateur.
$check = static function ( bool $condition, string $message ): void {
    if ( ! $condition ) {
        throw new RuntimeException( $message );
    }
};
$id = (int) get_option( 'kekidi_contact_form_id' );
$check( $id > 0, 'Formulaire créé automatiquement' );
$check( $id === kekidi_contact_ensure_form(), 'Activation idempotente : le formulaire conserve son ID' );
$form = Forminator_API::get_form( $id );
$check( ! is_wp_error( $form ), 'Forminator disponible sans Jetpack' );
$check( array() === $form->notifications, 'Aucune notification e-mail' );
$check( '1' === $form->settings['store_submissions'], 'Stockage local activé' );
$check( '1' === $form->settings['honeypot'], 'Protection honeypot active' );
$check( 0 === (int) get_option( 'kekidi_test_mail_calls' ), 'Aucun appel à wp_mail pendant les envois réels' );
$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'kekidi/contact' );
$check( is_array( $pattern ) && true === $pattern['inserter'], 'Composition disponible' );
$check( has_block( 'forminator/forms', $pattern['content'] ), 'Bloc Forminator natif dans la composition' );
$check( false === strpos( $pattern['content'], 'jetpack/' ), 'Composition indépendante de Jetpack' );
$filtered = apply_filters( 'forminator_custom_form_submit_field_data', array( array( 'name' => '_forminator_user_ip', 'value' => '192.0.2.1' ), array( 'name' => 'email-1', 'value' => 'test@example.invalid' ) ), $id );
$check( 1 === count( $filtered ) && 'email-1' === $filtered[0]['name'], 'IP retirée, message conservé' );
$unrelated = apply_filters( 'forminator_custom_form_submit_field_data', array( array( 'name' => '_forminator_user_ip', 'value' => '192.0.2.1' ) ), $id + 99999 );
$check( 1 === count( $unrelated ), 'Les autres formulaires ne sont pas modifiés' );
$entries = Forminator_API::get_entries( $id );
$check( is_array( $entries ) && count( $entries ) > 0, 'Envoi réel enregistré en base' );
$test_count = 0;
foreach ( $entries as $entry ) {
    $text = $entry->meta_data['textarea-1']['value'] ?? '';
    if ( 0 !== strpos( $text, 'KEKIDI-FORMINATOR-TEST :' ) ) {
        continue;
    }
    $test_count++;
    $check( ! isset( $entry->meta_data['_forminator_user_ip'] ), 'L’entrée réelle ne contient aucune IP' );
    $check( 'kekidi-test@example.invalid' === $entry->meta_data['email-1']['value'], 'E-mail enregistré' );
    $check( 'Poser une question' === $entry->meta_data['select-1']['value'], 'Objet par défaut enregistré' );
}
$check( $test_count > 0, 'Au moins un message issu du test navigateur' );
$check( ! get_post_type_object( 'forminator_forms' )->public, 'Formulaires non publics en tant que contenus WordPress' );
$previous = get_current_user_id();
wp_set_current_user( 0 );
$check( ! forminator_is_user_allowed( 'forminator-entries' ), 'Visiteur privé d’accès aux messages' );
wp_set_current_user( $previous );
$check( forminator_is_user_allowed( 'forminator-entries' ), 'Admin autorisé à consulter les messages' );
echo "Contact : formulaire stable, composition, stockage réel, absence d’e-mail et d’IP, isolation et droits vérifiés.\n";
