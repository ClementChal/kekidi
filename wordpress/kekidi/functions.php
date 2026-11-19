<?php
defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		register_block_pattern_category( 'kekidi', array( 'label' => 'Kekidi' ) );
	}
);

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'editor-styles' );
		add_editor_style( array( 'assets/daisyui.css', 'style.css' ) );
	}
);

// Charger la maquette après les styles globaux évite leurs remises à zéro.
add_action(
	'wp_enqueue_scripts',
	function () {
		$version = wp_get_theme()->get( 'Version' );
		wp_enqueue_style( 'kekidi-daisyui', get_theme_file_uri( 'assets/daisyui.css' ), array(), $version );
		wp_enqueue_style( 'kekidi', get_stylesheet_uri(), array( 'kekidi-daisyui' ), $version );
		if ( ! is_user_logged_in() ) {
			wp_enqueue_script( 'kekidi-prefetch', get_theme_file_uri( 'assets/kekidi-prefetch.js' ), array(), $version, true );
		}
	},
	20
);

// Les liens du contenu éditorial s’ouvrent dans un nouvel onglet.
add_filter(
	'render_block_core/group',
	function ( string $content, array $block ): string {
		$classes = explode( ' ', $block['attrs']['className'] ?? '' );
		if ( ! array_intersect( array( 'answer-detail', 'sources' ), $classes ) ) {
			return $content;
		}

		$processor = new WP_HTML_Tag_Processor( $content );
		while ( $processor->next_tag( 'A' ) ) {
			$rels = preg_split( '/\s+/', strtolower( (string) $processor->get_attribute( 'rel' ) ), -1, PREG_SPLIT_NO_EMPTY );
			foreach ( array( 'noopener', 'noreferrer' ) as $rel ) {
				if ( ! in_array( $rel, $rels, true ) ) {
					$rels[] = $rel;
				}
			}
			$processor->set_attribute( 'target', '_blank' );
			$processor->set_attribute( 'rel', implode( ' ', $rels ) );
		}

		return $processor->get_updated_html();
	},
	10,
	2
);

// Les blocs natifs restent éditables ; la carte publiée forme un seul lien.
add_filter(
	'render_block_core/group',
	function ( string $content, array $block ): string {
		$classes = explode( ' ', $block['attrs']['className'] ?? '' );
		if ( ! in_array( 'question-card', $classes, true ) || 'article' !== ( $block['attrs']['tagName'] ?? '' ) ) {
			return $content;
		}
		$content     = trim( $content );
		$opening_end = strpos( $content, '>' ) + 1;
		$inner       = substr( $content, $opening_end, -strlen( '</article>' ) );
		$inner       = preg_replace( '~</?a\b[^>]*>~i', '', $inner );
		$link        = '<a class="question-card-link" href="' . esc_url( get_permalink() ) . '" aria-label="' . esc_attr( 'Lire la réponse : ' . get_the_title() ) . '">';
		return substr( $content, 0, $opening_end ) . $link . $inner . '</a></article>';
	},
	10,
	2
);

add_filter(
	'render_block_core/read-more',
	function ( string $content, array $block ): string {
		if ( 'read-link' !== ( $block['attrs']['className'] ?? '' ) ) {
			return $content;
		}
		return '<span class="read-link">Lire la réponse <svg class="arrow" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></span>';
	},
	10,
	2
);

add_filter(
	'render_block_core/post-navigation-link',
	function ( string $content, array $block ): string {
		if ( 'read-link kekidi-next' !== ( $block['attrs']['className'] ?? '' ) ) {
			return $content;
		}
		$ids      = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'orderby'        => 'ID',
				'order'          => 'ASC',
			)
		);
		$position = array_search( get_the_ID(), $ids, true );
		if ( count( $ids ) < 2 || false === $position ) {
			return '';
		}
		$next = $ids[ ( $position + 1 ) % count( $ids ) ];
		return '<a class="read-link kekidi-next" href="' . esc_url( get_permalink( $next ) ) . '" aria-label="' . esc_attr( 'Question suivante : ' . get_the_title( $next ) ) . '">Question suivante <svg class="arrow" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></a>';
	},
	10,
	2
);
