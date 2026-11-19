<?php
/**
 * Plugin Name: Kekidi — Super éditeur
 * Description: Ajoute un rôle de publication avec accès à l’Éditeur de site et aux statistiques Koko Analytics.
 * Version: 1.0.0
 * Requires at least: 6.6
 * Requires PHP: 7.4
 * License: GPL-2.0-or-later
 * Text Domain: kekidi-super-editeur
 */

defined( 'ABSPATH' ) || exit;

function kekidi_super_editeur_add_role(): void {
	$editor = get_role( 'editor' );
	if ( ! $editor ) {
		return;
	}

	$capabilities = array_merge( $editor->capabilities, array(
		'edit_theme_options'  => true,
		'view_koko_analytics' => true,
	) );
	$role = get_role( 'super_editeur' );

	if ( ! $role ) {
		add_role( 'super_editeur', 'Super éditeur', $capabilities );
		return;
	}

	foreach ( $capabilities as $capability => $grant ) {
		if ( $grant && ! $role->has_cap( $capability ) ) {
			$role->add_cap( $capability );
		}
	}
}

register_activation_hook( __FILE__, 'kekidi_super_editeur_add_role' );
