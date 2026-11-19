<?php
/**
 * Normalisation des données de scrutin.
 *
 * @package Kekidi_Scrutins
 */

defined( 'ABSPATH' ) || exit;

/**
 * Valide un décompte entier.
 *
 * @param mixed $value Décompte à valider.
 *
 * @return int
 * @throws InvalidArgumentException Si les données ou la réponse ne satisfont pas les contrôles.
 */
function kekidi_scrutins_integer( $value ): int {
	if ( ( ! is_int( $value ) && ! is_string( $value ) ) || ! preg_match( '/^\d+$/D', (string) $value ) || (float) $value > 1000000 ) {
		throw new InvalidArgumentException( 'Décompte absent ou invalide.' );
	}
	return (int) $value;
}

/**
 * Calcule les groupes majoritaires.
 *
 * @param array  $groups Groupes parlementaires.
 * @param string $direction Sens du vote.
 *
 * @return array
 * @throws InvalidArgumentException Si le sens du vote est invalide.
 */
function kekidi_scrutins_expected( array $groups, string $direction ): array {
	if ( ! in_array( $direction, array( 'pour', 'contre' ), true ) ) {
		throw new InvalidArgumentException( 'Choisissez « majoritairement pour » ou « majoritairement contre ».' );
	}
	return array_values(
		array_map(
			static function ( array $group ): string {
				return $group['id']; },
			array_filter(
				$groups,
				static function ( array $group ) use ( $direction ): bool {
					if ( in_array( $group['id'], array( 'PO840056', 'NI' ), true ) ) {
						return false; }
					$total = $group['pour'] + $group['contre'] + $group['abstention'];
					return $total > 0 && $group[ $direction ] * 2 > $total;
				}
			)
		)
	);
}

/**
 * Normalise le texte extrait de la source.
 *
 * @param string $text Texte extrait.
 *
 * @return string
 */
function kekidi_scrutins_text( string $text ): string {
	return trim( preg_replace( '/\s+/u', ' ', str_replace( "\xc2\xa0", ' ', $text ) ) );
}

/**
 * Prépare les requêtes XPath sur la source.
 *
 * @param string $html Page officielle.
 *
 * @return DOMXPath
 * @throws InvalidArgumentException Si les données ou la réponse ne satisfont pas les contrôles.
 */
function kekidi_scrutins_document( string $html ): DOMXPath {
	if ( ! class_exists( 'DOMDocument' ) ) {
		throw new InvalidArgumentException( 'L’extension PHP DOM est nécessaire pour vérifier la page officielle.' ); }
	$document = new DOMDocument();
	$previous = libxml_use_internal_errors( true );
	try {
		$loaded = $document->loadHTML( '<?xml encoding="UTF-8">' . $html, LIBXML_NONET );
	} finally {
		libxml_clear_errors();
		libxml_use_internal_errors( $previous );
	}
	if ( ! $loaded ) {
		throw new InvalidArgumentException( 'Page officielle illisible.' ); }
	return new DOMXPath( $document );
}

/**
 * Normalise la date officielle du scrutin.
 *
 * @param string $text Texte extrait.
 *
 * @return string
 * @throws InvalidArgumentException Si les données ou la réponse ne satisfont pas les contrôles.
 */
function kekidi_scrutins_date( string $text ): string {
	$months = array( 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre' );
	if ( ! preg_match( '/\b(\d{1,2})(?:er)? (' . implode( '|', $months ) . ') (20\d{2})\b/u', $text, $matches ) ) {
		throw new InvalidArgumentException( 'Date officielle absente ou incompatible.' );
	}
	$month = array_search( $matches[2], $months, true ) + 1;
	if ( ! checkdate( $month, (int) $matches[1], (int) $matches[3] ) ) {
		throw new InvalidArgumentException( 'Date officielle invalide.' ); }
	return sprintf( '%04d-%02d-%02d', $matches[3], $month, $matches[1] );
}

/**
 * Extrait un décompte à partir d’un motif.
 *
 * @param string $text Texte extrait.
 * @param string $pattern Motif de recherche.
 *
 * @return int
 * @throws InvalidArgumentException Si les données ou la réponse ne satisfont pas les contrôles.
 */
function kekidi_scrutins_match_count( string $text, string $pattern ): int {
	if ( ! preg_match( $pattern, $text, $matches ) ) {
		throw new InvalidArgumentException( 'Synthèse officielle absente ou incompatible.' ); }
	return kekidi_scrutins_integer( $matches[1] );
}

/**
 * Vérifie la cohérence des décomptes.
 *
 * @param array $groups Groupes parlementaires.
 * @param array $totals Décomptes globaux.
 * @param int   $voters Nombre de votants.
 * @throws InvalidArgumentException Si les données ou la réponse ne satisfont pas les contrôles.
 */
function kekidi_scrutins_validate_totals( array $groups, array $totals, int $voters ): void {
	$ids = array_column( $groups, 'id' );
	if ( ! $groups || count( $groups ) > 80 || count( array_unique( $ids ) ) !== count( $ids ) ) {
		throw new InvalidArgumentException( 'Groupes absents, dupliqués ou incompatibles.' );
	}
	foreach ( $totals as $key => $value ) {
		if ( array_sum( array_column( $groups, $key ) ) !== $value ) {
			throw new InvalidArgumentException( 'Les décomptes des groupes ne correspondent pas à la synthèse officielle.' );
		}
	}
	if ( $totals['pour'] + $totals['contre'] + $totals['abstention'] !== $voters ) {
		throw new InvalidArgumentException( 'Nombre de votants incohérent.' );
	}
}

/**
 * Recoupe les décomptes de l’Assemblée avec la page officielle.
 *
 * @param array  $payload Données structurées de l’Assemblée.
 * @param string $html Page officielle.
 * @param int    $number Numéro du scrutin.
 * @param string $period Législature ou session.
 *
 * @return array
 * @throws InvalidArgumentException Si les données ou la réponse ne satisfont pas les contrôles.
 */
function kekidi_scrutins_normalize_assemblee( array $payload, string $html, int $number, string $period ): array {
	$source = $payload['data']['sourceData'] ?? array();
	if ( '17' !== $period || (string) ( $source['legislature'] ?? '' ) !== $period || kekidi_scrutins_integer( $source['numero'] ?? null ) !== $number || empty( $source['titre'] ) ) {
		throw new InvalidArgumentException( 'Identité du scrutin de l’Assemblée absente ou incompatible.' );
	}
	$date    = (string) ( $source['dateScrutin'] ?? '' );
	$xpath   = kekidi_scrutins_document( $html );
	$heading = kekidi_scrutins_text( $xpath->evaluate( 'string(//h1)' ) );
	if ( ! preg_match( '/scrutin n°\s*' . $number . '\b/u', $heading ) ) {
		throw new InvalidArgumentException( 'Le numéro de la page officielle ne correspond pas.' ); }
	$description = kekidi_scrutins_text( $xpath->evaluate( 'string(//meta[@name="description"]/@content)' ) );
	$title       = preg_replace( '/^Scrutin public n°\s*' . $number . '\s+sur\s+/u', '', $description, 1, $title_matches );
	if ( ! $title_matches || ! $title ) {
		throw new InvalidArgumentException( 'Intitulé officiel absent ou incompatible.' ); }
	$official_date = '';
	foreach ( $xpath->query( '//h2' ) as $node ) {
		if ( preg_match( '/séance du/u', $node->textContent ) ) {
			$official_date = kekidi_scrutins_date( kekidi_scrutins_text( $node->textContent ) );
			break; }
	}
	if ( $date !== $official_date || ! preg_match( '/^20\d{2}-\d{2}-\d{2}$/D', $date ) ) {
		throw new InvalidArgumentException( 'La date ne correspond pas à la page officielle.' ); }
	$official_groups = array();
	foreach ( $xpath->query( '//h2[normalize-space(.)="Votes des groupes"]/parent::div/following-sibling::div[1]//li[@data-organe-id]' ) as $node ) {
		$id     = $node->getAttribute( 'data-organe-id' );
		$counts = array(
			'pour'       => 0,
			'contre'     => 0,
			'abstention' => 0,
			'nonVotants' => 0,
		);
		$found  = false;
		foreach ( $xpath->query( './ul/li/span', $node ) as $span ) {
			$text = kekidi_scrutins_text( $span->textContent );
			if ( preg_match( '/^(Pour|Contre|Abstention|Non votant)\s*:\s*(\d+)$/u', $text, $matches ) ) {
				$key            = array(
					'Pour'       => 'pour',
					'Contre'     => 'contre',
					'Abstention' => 'abstention',
					'Non votant' => 'nonVotants',
				)[ $matches[1] ];
				$counts[ $key ] = kekidi_scrutins_integer( $matches[2] );
				$found          = true;
			}
		}
		if ( $found || $xpath->query( './ul', $node )->length ) {
			$official_groups[ $id ] = array(
				'name'   => kekidi_scrutins_text( $xpath->evaluate( 'string(.//h3)', $node ) ),
				'counts' => $counts,
			);
		}
	}
	$raw = $source['ventilationVotes']['organe']['groupes']['groupe'] ?? array();
	if ( isset( $raw['organeRef'] ) ) {
		$raw = array( $raw ); }
	if ( ! is_array( $raw ) || ! $raw ) {
		throw new InvalidArgumentException( 'Répartition par groupe absente.' ); }
	$codes    = array(
		'PO845401' => 'RN',
		'PO845407' => 'EPR',
		'PO845413' => 'LFI-NFP',
		'PO845419' => 'SOC',
		'PO845425' => 'DR',
		'PO845439' => 'ECOS',
		'PO845454' => 'DEM',
		'PO845470' => 'HOR',
		'PO845485' => 'LIOT',
		'PO845514' => 'GDR',
		'PO872880' => 'UDR',
		'PO840056' => 'NI',
	);
	$warnings = array();
	$groups   = array();
	foreach ( $raw as $item ) {
		$id = $item['organeRef'] ?? '';
		if ( ! is_string( $id ) || ! preg_match( '/^PO\d+$/D', $id ) || empty( $official_groups[ $id ]['name'] ) ) {
			throw new InvalidArgumentException( 'Groupe introuvable sur la page officielle.' ); }
		$raw_counts = $item['vote']['decompteVoix'] ?? array();
		$counts     = array(
			'pour'       => kekidi_scrutins_integer( $raw_counts['pour'] ?? null ),
			'contre'     => kekidi_scrutins_integer( $raw_counts['contre'] ?? null ),
			'abstention' => kekidi_scrutins_integer( $raw_counts['abstentions'] ?? null ),
			'nonVotants' => kekidi_scrutins_integer( $raw_counts['nonVotants'] ?? null ),
		);
		if ( $counts !== $official_groups[ $id ]['counts'] ) {
			throw new InvalidArgumentException( 'Les décomptes de ' . $official_groups[ $id ]['name'] . ' ne correspondent pas à la page officielle.' ); }
		$votes   = array_intersect_key( $counts, array_flip( array( 'pour', 'contre', 'abstention' ) ) );
		$leaders = array_keys( $votes, max( $votes ), true );
		if ( array_sum( $votes ) && count( $leaders ) === 1 && ( $item['vote']['positionMajoritaire'] ?? '' ) !== $leaders[0] ) {
			$warnings[] = $official_groups[ $id ]['name'] . ' : positionMajoritaire contradictoire. Les décomptes concordent avec la page officielle et sont utilisés pour le corrigé.';
		}
		if ( ! isset( $codes[ $id ] ) ) {
			$warnings[] = $official_groups[ $id ]['name'] . ' : logo non référencé ; choisir une image ou conserver le sigle.'; }
		$groups[] = array_merge(
			array(
				'id'    => $id,
				'short' => $codes[ $id ] ?? $id,
				'name'  => $official_groups[ $id ]['name'],
			),
			$counts
		);
	}
	if ( count( $official_groups ) !== count( $groups ) ) {
		throw new InvalidArgumentException( 'La liste des groupes diffère de la page officielle.' ); }
	$summary = $source['syntheseVote'] ?? array();
	$totals  = array(
		'pour'       => kekidi_scrutins_integer( $summary['decompte']['pour'] ?? null ),
		'contre'     => kekidi_scrutins_integer( $summary['decompte']['contre'] ?? null ),
		'abstention' => kekidi_scrutins_integer( $summary['decompte']['abstentions'] ?? null ),
		'nonVotants' => kekidi_scrutins_integer( $summary['decompte']['nonVotants'] ?? null ),
	);
	$voters  = kekidi_scrutins_integer( $summary['nombreVotants'] ?? null );
	kekidi_scrutins_validate_totals( $groups, $totals, $voters );
	$text            = kekidi_scrutins_text( $xpath->evaluate( 'string(//body)' ) );
	$official_totals = array(
		'pour'       => kekidi_scrutins_match_count( $text, '/Pour l[’\']adoption\s*:\s*(\d+)/u' ),
		'contre'     => kekidi_scrutins_match_count( $text, '/Contre\s*:\s*(\d+)/u' ),
		'abstention' => kekidi_scrutins_match_count( $text, '/Abstention\s*:\s*(\d+)/u' ),
	);
	if ( array_intersect_key( $totals, $official_totals ) !== $official_totals || kekidi_scrutins_match_count( $text, '/Nombre de votants\s*:\s*(\d+)/u' ) !== $voters ) {
		throw new InvalidArgumentException( 'La synthèse diffère de la page officielle.' ); }
	return array(
		'schemaVersion' => 1,
		'chamber'       => 'assemblee',
		'period'        => $period,
		'number'        => $number,
		'date'          => $date,
		'title'         => $title,
		'sourceUrl'     => 'https://www.assemblee-nationale.fr/dyn/' . $period . '/scrutins/' . $number,
		'dataUrl'       => 'https://api.clair.vote/api/v1/scrutins/' . $number . '?chambre=assemblee&session=' . $period,
		'groups'        => $groups,
		'totals'        => $totals,
		'warnings'      => $warnings,
	);
}
