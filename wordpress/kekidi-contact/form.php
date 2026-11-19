<?php
/** Le formulaire est créé une seule fois, avec un identifiant propre à chaque site. */
defined( 'ABSPATH' ) || exit;

function kekidi_contact_ensure_form(): int {
	$id = (int) get_option( 'kekidi_contact_form_id' );
	if ( $id && 'forminator_forms' === get_post_type( $id ) && 'trash' !== get_post_status( $id ) ) {
		return $id;
	}
	if ( ! class_exists( 'Forminator_API' ) || ! current_user_can( 'manage_options' ) ) {
		return 0;
	}
	$fields   = array(
		array(
			'element_id'  => 'select-1',
			'type'        => 'select',
			'field_label' => 'Je souhaite…',
			'required'    => true,
			'value_type'  => 'single',
			'options'     => array(
				array(
					'label'   => 'Poser une question',
					'value'   => 'Poser une question',
					'default' => true,
					'key'     => 'poser',
				),
				array(
					'label'   => 'Proposer une question',
					'value'   => 'Proposer une question',
					'default' => false,
					'key'     => 'proposer',
				),
			),
		),
		array(
			'element_id'       => 'email-1',
			'type'             => 'email',
			'field_label'      => 'Mon adresse mail',
			'required'         => true,
			'required_message' => 'Indiquez votre adresse mail.',
			'validation'       => true,
			'validation_text'  => 'Saisissez une adresse mail valide, comme nom@exemple.fr.',
		),
		array(
			'element_id'       => 'textarea-1',
			'type'             => 'textarea',
			'field_label'      => 'Ma question',
			'required'         => true,
			'required_message' => 'Écrivez votre question.',
			'input_type'       => 'paragraph',
			'limit'            => '5000',
			'limit_type'       => 'characters',
		),
	);
	$wrappers = array_map(
		function ( array $field ): array {
			$field['cols'] = '12';
			return array(
				'wrapper_id' => 'wrapper-' . $field['element_id'],
				'fields'     => array( $field ),
			);
		},
		$fields
	);
	$settings = array(
		'formName'             => 'Contact Kekidi',
		'form-type'            => 'default',
		'form-style'           => 'none',
		'enable-ajax'          => 'true',
		'validation-inline'    => true,
		'form-expire'          => 'no_expire',
		'submission-behaviour' => 'behaviour-thankyou',
		'thankyou-message'     => 'Merci, votre message est envoyé !',
		'submitData'           => array(
			'custom-submit-text'          => 'Valider mon message',
			'custom-invalid-form-message' => 'Vérifiez les champs indiqués.',
		),
		'store_submissions'    => '1',
		'honeypot'             => '1',
		'use-admin-ajax'       => true,
	);
	$created  = Forminator_API::add_form( 'Contact Kekidi', $wrappers, $settings );
	if ( is_wp_error( $created ) ) {
		return 0;
	}
	// L’API crée une notification d’admin par défaut : la retirer avant toute publication du formulaire.
	$saved = Forminator_API::update_form( $created, $wrappers, $settings, 'publish', array() );
	if ( is_wp_error( $saved ) ) {
		wp_update_post(
			array(
				'ID'          => $created,
				'post_status' => 'draft',
			)
		);
		return 0;
	}
	update_option( 'kekidi_contact_form_id', (int) $created, false );
	return (int) $created;
}
