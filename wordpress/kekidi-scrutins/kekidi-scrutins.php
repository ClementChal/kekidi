<?php
/**
 * Plugin Name: Kekidi — Scrutins
 * Description: Quiz de votes de l’Assemblée nationale et du Sénat, avec import contrôlé et données conservées dans WordPress.
 * Version: 1.0.1
 * Requires at least: 6.6
 * Requires PHP: 7.4
 * License: GPL-2.0-or-later
 * Text Domain: kekidi-scrutins
 */
defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/includes/data.php';
require_once __DIR__ . '/includes/sources.php';
require_once __DIR__ . '/includes/storage.php';
require_once __DIR__ . '/includes/presentation.php';

add_action(
	'init',
	function (): void {
		wp_register_script( 'kekidi-scrutins-confetti', plugins_url( 'vendor/confetti-2.2.0.min.js', __FILE__ ), array(), '2.2.0', true );
		register_block_type( __DIR__ . '/block' );
		wp_localize_script( 'kekidi-quiz-scrutin-editor-script', 'KekidiScrutins', array( 'logoUrls' => kekidi_scrutins_logo_urls() ) );
		register_block_pattern(
			'kekidi/reponse-scrutin',
			array(
				'title'       => 'Nouvelle réponse scrutin',
				'description' => 'Importer un scrutin puis préparer un quiz « majoritairement pour » ou « majoritairement contre ».',
				'categories'  => array( 'kekidi' ),
				'postTypes'   => array( 'post' ),
				'content'     => '<!-- wp:kekidi/quiz-scrutin /-->',
			)
		);
	}
);

function kekidi_scrutins_has_quiz( int $post_id ): bool {
	return $post_id > 0 && has_block( 'kekidi/quiz-scrutin', get_post_field( 'post_content', $post_id ) );
}

add_filter(
	'render_block_core/group',
	function ( string $html, array $block ): string {
		$classes = explode( ' ', $block['attrs']['className'] ?? '' );
		if ( in_array( 'answer-wrap', $classes, true ) && is_singular( 'post' ) && strpos( $html, ' data-kekidi-vote ' ) !== false ) {
			$tags = new WP_HTML_Tag_Processor( $html );
			if ( $tags->next_tag( 'MAIN' ) ) {
				$tags->add_class( 'quiz-page' );
				$tags->add_class( 'vote-page' ); }
			return $tags->get_updated_html();
		}
		if ( in_array( 'question-card', $classes, true ) && kekidi_scrutins_has_quiz( (int) get_the_ID() ) ) {
			$tags = new WP_HTML_Tag_Processor( $html );
			if ( $tags->next_tag( 'A' ) ) {
				$tags->set_attribute( 'aria-label', 'Répondre au quiz : ' . get_the_title() ); }
			return $tags->get_updated_html();
		}
		return $html;
	},
	25,
	2
);

add_filter(
	'render_block_core/read-more',
	function ( string $html, array $block ): string {
		return 'read-link' === ( $block['attrs']['className'] ?? '' ) && kekidi_scrutins_has_quiz( (int) get_the_ID() ) ? str_replace( 'Lire la réponse', 'Répondre au quiz', $html ) : $html;
	},
	25,
	2
);
