<?php
/**
 * Présentation des données de scrutin.
 *
 * @package Kekidi_Scrutins
 */

defined( 'ABSPATH' ) || exit;

/**
 * Retourne les URL des logos embarqués.
 *
 * @return array
 */
function kekidi_scrutins_logo_urls(): array {
	$files = array(
		'PO845401' => 'rn',
		'PO845407' => 'epr',
		'PO845413' => 'lfi',
		'PO845419' => 'soc',
		'PO845425' => 'lr',
		'PO845439' => 'ecolo',
		'PO845454' => 'dem',
		'PO845470' => 'hor',
		'PO845485' => 'liot',
		'PO845514' => 'gdr',
		'PO872880' => 'udr',
		'UMP'      => 'lr',
		'SOC'      => 'soc',
		'CRC'      => 'gdr',
		'GEST'     => 'ecolo',
		'LREM'     => 'epr',
	);
	return array_map(
		static function ( string $file ): string {
			return plugins_url( 'assets/logos/' . $file . '.png', dirname( __DIR__ ) . '/kekidi-scrutins.php' );
		},
		$files
	);
}

/**
 * Construit le logo et son libellé de secours.
 *
 * @param array $group Données du groupe.
 * @param array $logos Logos personnalisés.
 *
 * @return string
 */
function kekidi_scrutins_logo_html( array $group, array $logos ): string {
	$defaults = kekidi_scrutins_logo_urls();
	$custom   = array_key_exists( $group['id'], $logos );
	$id       = $custom && is_int( $logos[ $group['id'] ] ) ? $logos[ $group['id'] ] : 0;
	$url      = $custom ? ( $id > 0 && wp_attachment_is_image( $id ) ? wp_get_attachment_image_url( $id, 'thumbnail' ) : '' ) : ( $defaults[ $group['id'] ] ?? '' );
	$fallback = '<span class="vote-party-logo vote-logo-fallback" aria-hidden="true"' . ( $url ? ' hidden' : '' ) . '>' . esc_html( $group['short'] ) . '</span>';
	return '<span class="vote-emblem">' . ( $url ? '<img class="vote-party-logo" src="' . esc_url( $url ) . '" width="40" height="40" alt="" decoding="async">' : '' ) . $fallback . '</span>';
}

/**
 * Formate la part d’un groupe.
 *
 * @param int $count Nombre de votes.
 * @param int $total Nombre de participants.
 *
 * @return string
 */
function kekidi_scrutins_percentage( int $count, int $total ): string {
	if ( ! $total ) {
		return '—'; }
	$percentage = round( $count * 100 / $total, 1 );
	return number_format_i18n( $percentage, floor( $percentage ) === $percentage ? 0 : 1 ) . ' %';
}


/**
 * Construit les blocs explicatifs initiaux.
 *
 * @param array  $snapshot Données du scrutin.
 * @param string $direction Sens du vote.
 *
 * @return string
 */
function kekidi_scrutins_default_explanation( array $snapshot, string $direction ): string {
	$groups       = array_values(
		array_filter(
			$snapshot['groups'],
			static function ( array $group ): bool {
				return ! in_array( $group['id'], array( 'NI', 'PO840056' ), true );
			}
		)
	);
	$expected     = kekidi_scrutins_expected( $snapshot['groups'], $direction );
	$names        = array_column(
		array_filter(
			$groups,
			static function ( array $group ) use ( $expected ): bool {
				return in_array( $group['id'], $expected, true );
			}
		),
		'name'
	);
	$summary      = $names ? implode( ', ', $names ) . ( count( $names ) > 1 ? ' ont voté' : ' a voté' ) . ' majoritairement ' . $direction . '.' : 'Aucun groupe n’a voté majoritairement ' . $direction . '.';
	$totals       = $snapshot['totals'];
	$non_inscrits = array_values(
		array_filter(
			$snapshot['groups'],
			static function ( array $group ): bool {
				return in_array( $group['id'], array( 'NI', 'PO840056' ), true );
			}
		)
	);
	$paragraph    = static function ( string $html ): string {
		return '<!-- wp:paragraph --><p>' . $html . '</p><!-- /wp:paragraph -->';
	};
	$blocks       = '<!-- wp:heading --><h2 class="wp-block-heading">Explication et sources</h2><!-- /wp:heading -->';
	$blocks      .= $paragraph( esc_html( $summary . ' Résultat du scrutin : ' . $totals['pour'] . ' votes pour, ' . $totals['contre'] . ' contre et ' . $totals['abstention'] . ' abstentions.' ) );
	$note         = count( $groups ) . ' groupes sont représentés.';
	if ( $non_inscrits ) {
		$note .= ' Les non-inscrits ne constituent pas un groupe ; leurs ' . $non_inscrits[0]['pour'] . ' votes pour, ' . $non_inscrits[0]['contre'] . ' contre et ' . $non_inscrits[0]['abstention'] . ' abstentions sont inclus dans le résultat du scrutin.'; }
	$blocks .= $paragraph( esc_html( $note ) );
	$chamber = 'senat' === $snapshot['chamber'] ? 'Sénat' : 'Assemblée nationale';
	$date    = wp_date( 'j F Y', strtotime( $snapshot['retrievedAt'] ), new DateTimeZone( 'Europe/Paris' ) );
	$blocks .= $paragraph( 'Source : <a href="' . esc_url( $snapshot['sourceUrl'] ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $chamber . ' · scrutin n° ' . $snapshot['number'] . ' (nouvel onglet)' ) . '</a>. Données vérifiées sur la page officielle et enregistrées le ' . esc_html( $date ) . '.' );
	$blocks .= '<!-- wp:details --><details class="wp-block-details"><summary>Intitulé officiel du scrutin</summary>' . $paragraph( esc_html( $snapshot['title'] ) ) . '</details><!-- /wp:details -->';
	$blocks .= $paragraph( 'Majorité strictement supérieure à 50 % des pour, contre et abstentions. Les pourcentages sont arrondis à une décimale ; leur somme affichée peut différer de 100 %. Certains emblèmes représentent un parti associé au groupe.' );
	return $blocks;
}
