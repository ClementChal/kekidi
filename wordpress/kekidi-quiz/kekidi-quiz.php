<?php
/**
 * Plugin Name: Kekidi — Quiz
 * Description: Réponses quiz éditables dans Gutenberg, avec conservation du dernier résultat dans le navigateur.
 * Version: 1.1.1
 * Requires at least: 6.6
 * Requires PHP: 7.4
 * License: GPL-2.0-or-later
 * Text Domain: kekidi-quiz
 */

defined( 'ABSPATH' ) || exit;

function kekidi_quiz_error( array $attributes ): string {
	$choices = $attributes['choices'] ?? array();
	if ( ! is_array( $choices ) || count( $choices ) < 1 || count( $choices ) > 5 ) {
		return 'Le quiz doit contenir de 1 à 5 propositions.';
	}
	$correct = false;
	foreach ( $choices as $choice ) {
		if ( ! is_array( $choice ) || ! isset( $choice['label'], $choice['correct'] ) || ! is_string( $choice['label'] ) || '' === trim( $choice['label'] ) || ! is_bool( $choice['correct'] ) ) {
			return 'Renseignez le texte et le corrigé de chaque proposition.';
		}
		$correct = $correct || $choice['correct'];
	}
	return $correct ? '' : 'Indiquez au moins une bonne réponse.';
}

function kekidi_quiz_has_quiz( int $post_id ): bool {
	return $post_id > 0 && has_block( 'kekidi/quiz', get_post_field( 'post_content', $post_id ) );
}

add_action(
	'init',
	function () {
		wp_register_script( 'kekidi-quiz-confetti', plugins_url( 'vendor/confetti-2.2.0.min.js', __FILE__ ), array(), '2.2.0', true );
		register_block_type( __DIR__ . '/block' );
		register_block_pattern(
			'kekidi/reponse-quiz',
			array(
				'title'       => 'Nouvelle réponse quiz',
				'description' => 'Propositions, bonnes réponses et explication à renseigner.',
				'categories'  => array( 'kekidi' ),
				'postTypes'   => array( 'post' ),
				'content'     => '<!-- wp:kekidi/quiz /-->',
			)
		);
	}
);

function kekidi_quiz_content_error( string $content ): string {
	$error   = '';
	$count   = 0;
	$inspect = function ( array $blocks ) use ( &$inspect, &$error, &$count ): void {
		foreach ( $blocks as $item ) {
			if ( 'kekidi/quiz' === $item['blockName'] ) {
				++$count;
				$error = $error ?: kekidi_quiz_error( $item['attrs'] );
			}
			$inspect( $item['innerBlocks'] ?? array() );
		}
	};
	$inspect( parse_blocks( $content ) );
	if ( $count > 1 ) {
		$error = 'Une réponse ne peut contenir qu’un seul quiz.';
	}
	return $error;
}

add_filter(
	'rest_pre_insert_post',
	function ( $prepared, WP_REST_Request $request ) {
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}
		$post_id = (int) $request['id'];
		$status  = $prepared->post_status ?? get_post_status( $post_id );
		if ( ! in_array( $status, array( 'publish', 'future' ), true ) ) {
			return $prepared;
		}
		$content = $prepared->post_content ?? get_post_field( 'post_content', $post_id );
		$error   = kekidi_quiz_content_error( (string) $content );
		return $error ? new WP_Error( 'kekidi_quiz_invalid', $error, array( 'status' => 400 ) ) : $prepared;
	},
	10,
	2
);

add_action(
	'wp_ajax_inline-save',
	function () {
		$post_id = isset( $_POST['post_ID'] ) ? absint( $_POST['post_ID'] ) : 0;
		$status  = sanitize_key( wp_unslash( $_POST['_status'] ?? $_POST['post_status'] ?? '' ) );
		if ( $post_id && current_user_can( 'edit_post', $post_id ) && in_array( $status, array( 'publish', 'future' ), true ) ) {
			$error = kekidi_quiz_content_error( (string) get_post_field( 'post_content', $post_id ) );
			if ( $error ) {
				check_ajax_referer( 'inlineeditnonce', '_inline_edit' );
				wp_die( esc_html( $error ) );
			}
		}
	},
	0
);

// Le format suit le contenu de l’article, sans modifier son permalien ni son modèle enregistré.
add_filter(
	'render_block_core/group',
	function ( string $html, array $block ): string {
		$classes = explode( ' ', $block['attrs']['className'] ?? '' );
		if ( in_array( 'answer-wrap', $classes, true ) && is_singular( 'post' ) && false !== strpos( $html, ' data-kekidi-quiz ' ) ) {
			$tags = new WP_HTML_Tag_Processor( $html );
			if ( $tags->next_tag( 'MAIN' ) ) {
				$tags->add_class( 'quiz-page' );
			}
			return $tags->get_updated_html();
		}
		if ( in_array( 'question-card', $classes, true ) && kekidi_quiz_has_quiz( (int) get_the_ID() ) ) {
			$tags = new WP_HTML_Tag_Processor( $html );
			if ( $tags->next_tag( 'A' ) ) {
				$tags->set_attribute( 'aria-label', 'Répondre au quiz : ' . get_the_title() );
			}
			return $tags->get_updated_html();
		}
		return $html;
	},
	20,
	2
);

add_filter(
	'render_block_core/read-more',
	function ( string $html, array $block ): string {
		if ( 'read-link' === ( $block['attrs']['className'] ?? '' ) && kekidi_quiz_has_quiz( (int) get_the_ID() ) ) {
			return str_replace( 'Lire la réponse', 'Répondre au quiz', $html );
		}
		return $html;
	},
	20,
	2
);
