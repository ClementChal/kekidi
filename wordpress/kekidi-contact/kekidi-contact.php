<?php
/**
 * Plugin Name: Kekidi — Contact
 * Description: Adapte Forminator à Kekidi : messages dans l’admin, sans e-mail, et états du formulaire.
 * Version: 2.0.0
 * Requires at least: 6.6
 * Requires PHP: 7.4
 * Requires Plugins: forminator
 * License: GPL-2.0-or-later
 *
 * @package Kekidi_Contact
 */

defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/form.php';

register_activation_hook( __FILE__, 'kekidi_contact_ensure_form' );
add_action(
	'init',
	function (): void {
		if ( is_admin() ) {
			kekidi_contact_ensure_form();
		}
		if ( get_option( 'kekidi_contact_form_id' ) && ! WP_Block_Patterns_Registry::get_instance()->is_registered( 'kekidi/contact' ) ) {
			register_block_pattern(
				'kekidi/contact',
				array(
					'title'      => 'Page Contact Kekidi',
					'categories' => array( 'kekidi' ),
					'postTypes'  => array( 'page' ),
					'inserter'   => true,
					'filePath'   => __DIR__ . '/patterns/contact.php',
				)
			);
		}
	},
	9
);

add_filter(
	'forminator_custom_form_submit_field_data',
	function ( array $data, int $form_id ): array {
		if ( (int) get_option( 'kekidi_contact_form_id' ) === $form_id ) {
			$data = array_values(
				array_filter(
					$data,
					function ( array $field ): bool {
						return '_forminator_user_ip' !== $field['name'];
					}
				)
			);
		}
		return $data;
	},
	10,
	2
);

add_filter(
	'do_shortcode_tag',
	function ( string $html, string $tag, $attributes ): string {
		if ( 'forminator_form' !== $tag || (int) ( $attributes['id'] ?? 0 ) !== (int) get_option( 'kekidi_contact_form_id' ) ) {
			return $html;
		}
		wp_enqueue_script( 'kekidi-contact', plugins_url( 'view.js', __FILE__ ), array( 'jquery' ), '2.0.0', true );
		$arrow = '<svg class="arrow" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>';
		$tags  = new WP_HTML_Tag_Processor( $html );
		while ( $tags->next_tag() ) {
			if ( 'FORM' === $tags->get_tag() ) {
				$tags->add_class( 'kekidi-contact' );
				$tags->add_class( 'contact-form' );
			}
			if ( $tags->has_class( 'forminator-field' ) ) {
				$tags->add_class( 'contact-field' );
			}
			if ( 'SELECT' === $tags->get_tag() ) {
				// Le menu natif conserve le comportement mobile et le dessin de la maquette.
				$tags->remove_class( 'forminator-select2' );
				$tags->remove_class( 'forminator-select2-multiple' );
				$tags->add_class( 'select' );
			}
			if ( 'INPUT' === $tags->get_tag() && 'email' === $tags->get_attribute( 'type' ) ) {
				$tags->add_class( 'input' );
				$tags->set_attribute( 'autocomplete', 'email' );
				$tags->set_attribute( 'inputmode', 'email' );
				$tags->set_attribute( 'maxlength', '254' );
			}
			if ( 'TEXTAREA' === $tags->get_tag() ) {
				$tags->add_class( 'textarea' );
				$tags->set_attribute( 'rows', '5' );
			}
			if ( $tags->has_class( 'forminator-button-submit' ) ) {
				$tags->add_class( 'wp-element-button' );
				$tags->set_attribute( 'type', 'submit' );
			}
		}
		$html    = $tags->get_updated_html();
		$html    = preg_replace( '~<option value=""[^>]*>[^<]*</option>~u', '', $html );
		$html    = preg_replace( '~(<select\b.*?</select>)~s', '<div class="contact-select">$1</div>', $html );
		$html    = preg_replace( '~(<button\b[^>]*\btype="submit"[^>]*>)(.*?)(</button>)~s', '$1$2 ' . $arrow . '$3', $html );
		$success = '<div class="contact-success" hidden tabindex="-1" role="status"><svg class="contact-success-mark" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><circle cx="24" cy="24" r="21"/><path d="m14 24 7 7 14-15" stroke-linecap="round" stroke-linejoin="round"/></svg><h2>Merci, votre message est envoyé !</h2><p>Vous pouvez poursuivre votre exploration de Kekidi?!.</p><a class="wp-element-button" href="' . esc_url( home_url( '/#questions' ) ) . '">Revenir aux questions ' . $arrow . '</a></div>';
		return '<div class="kekidi-contact-container">' . $html . $success . '</div>';
	},
	10,
	3
);
