<?php
defined( 'ABSPATH' ) || exit;

function kekidi_scrutins_normalize_senate( string $html, int $number, string $period ): array {
	if ( ! preg_match( '/^20\d{2}$/D', $period ) ) {
		throw new InvalidArgumentException( 'Indiquez l’année de début de la session du Sénat.' ); }
	$xpath   = kekidi_scrutins_document( $html );
	$heading = kekidi_scrutins_text( $xpath->evaluate( 'string(//h1)' ) );
	if ( ! preg_match( '/^Scrutin n°\s*' . $number . ' - séance du /u', $heading ) ) {
		throw new InvalidArgumentException( 'Page du Sénat absente ou numéro incompatible.' ); }
	$date = kekidi_scrutins_date( $heading );
	if ( $date < $period . '-10-01' || $date >= ( (int) $period + 1 ) . '-10-01' ) {
		throw new InvalidArgumentException( 'La date ne correspond pas à la session.' ); }
	$title = kekidi_scrutins_text( $xpath->evaluate( 'string(//p[contains(concat(" ",normalize-space(@class)," ")," page-lead ")])' ) );
	if ( '' === $title ) {
		throw new InvalidArgumentException( 'Intitulé officiel du Sénat absent.' ); }
	$groups = array();
	$codes  = array(
		'UMP'  => 'LR',
		'SOC'  => 'SER',
		'CRC'  => 'CRCE-K',
		'LREM' => 'RDPI',
		'RTLI' => 'INDEP',
	);
	foreach ( $xpath->query( '//div[starts-with(@id,"accordion-scrutin-")]' ) as $node ) {
		$id = substr( $node->getAttribute( 'id' ), strlen( 'accordion-scrutin-' ) );
		if ( ! preg_match( '/^[A-Z0-9-]+$/D', $id ) ) {
			throw new InvalidArgumentException( 'Identifiant de groupe sénatorial invalide.' ); }
		$name = preg_replace( '/\s*:\s*\d+.*$/u', '', kekidi_scrutins_text( $xpath->evaluate( 'string(.//h3)', $node ) ) );
		$text = kekidi_scrutins_text( $xpath->evaluate( 'string(.//ul)', $node ) );
		if ( ! $name || ! $text ) {
			throw new InvalidArgumentException( 'Groupe du Sénat incomplet.' ); }
		$groups[] = array(
			'id'         => $id,
			'short'      => $codes[ $id ] ?? $id,
			'name'       => $name,
			'pour'       => kekidi_scrutins_match_count( $text, '/Pour\s*:\s*(\d+)/u' ),
			'contre'     => kekidi_scrutins_match_count( $text, '/Contre\s*:\s*(\d+)/u' ),
			'abstention' => kekidi_scrutins_match_count( $text, '/Abstentions?\s*:\s*(\d+)/u' ),
			'nonVotants' => kekidi_scrutins_match_count( $text, '/N[’\'](?:ont|a) pas pris part au vote\s*:\s*(\d+)/u' ),
		);
	}
	$summary_node = $xpath->query( '//h2[contains(concat(" ",normalize-space(@class)," ")," card-title ")]/../..' )->item( 0 );
	if ( ! $summary_node ) {
		throw new InvalidArgumentException( 'Synthèse du Sénat absente.' ); }
	$text   = kekidi_scrutins_text( $summary_node->textContent );
	$voters = kekidi_scrutins_match_count( $text, '/(\d+) votants/u' );
	$totals = array(
		'pour'       => kekidi_scrutins_match_count( $text, '/(\d+) pour/u' ),
		'contre'     => kekidi_scrutins_match_count( $text, '/(\d+) contre/u' ),
		'abstention' => kekidi_scrutins_match_count( $text, '/Abstentions?\s*:\s*(\d+)/u' ),
		'nonVotants' => kekidi_scrutins_match_count( $text, '/N[’\'](?:ont|a) pas pris part au vote\s*:\s*(\d+)/u' ),
	);
	kekidi_scrutins_validate_totals( $groups, $totals, $voters );
	return array(
		'schemaVersion' => 1,
		'chamber'       => 'senat',
		'period'        => $period,
		'number'        => $number,
		'date'          => $date,
		'title'         => $title,
		'sourceUrl'     => 'https://www.senat.fr/scrutin-public/' . $period . '/scr' . $period . '-' . $number . '.html',
		'groups'        => $groups,
		'totals'        => $totals,
		'warnings'      => array(),
	);
}

function kekidi_scrutins_fetch( string $url ): string {
	$response = wp_safe_remote_get(
		$url,
		array(
			'timeout'             => 12,
			'redirection'         => 0,
			'limit_response_size' => 2097153,
			'user-agent'          => 'KekidiScrutins/1.0 (WordPress parliamentary voting quiz)',
			'headers'             => array( 'Accept' => 'application/json,text/html' ),
		)
	);
	if ( is_wp_error( $response ) ) {
		throw new InvalidArgumentException( 'Source indisponible : ' . $response->get_error_message() . ' Réessayez ; les données précédentes sont conservées.' ); }
	$status = wp_remote_retrieve_response_code( $response );
	if ( 200 !== $status ) {
		throw new InvalidArgumentException( 'La source répond HTTP ' . $status . '. Vérifiez le numéro et la période, puis réessayez.' ); }
	$body = wp_remote_retrieve_body( $response );
	if ( '' === $body || strlen( $body ) > 2097152 ) {
		throw new InvalidArgumentException( 'Réponse vide ou trop volumineuse.' ); }
	return $body;
}

function kekidi_scrutins_import( string $chamber, string $period, int $number ): array {
	if ( $number < 1 || $number > 999999 ) {
		throw new InvalidArgumentException( 'Le numéro doit contenir de 1 à 6 chiffres et être strictement positif.' ); }
	if ( 'assemblee' === $chamber && '17' === $period ) {
		$json    = kekidi_scrutins_fetch( 'https://api.clair.vote/api/v1/scrutins/' . $number . '?chambre=assemblee&session=17' );
		$payload = json_decode( $json, true );
		if ( ! is_array( $payload ) ) {
			throw new InvalidArgumentException( 'La source n’a pas renvoyé un JSON exploitable.' ); }
		$result = kekidi_scrutins_normalize_assemblee( $payload, kekidi_scrutins_fetch( 'https://www.assemblee-nationale.fr/dyn/17/scrutins/' . $number ), $number, $period );
	} elseif ( 'senat' === $chamber && preg_match( '/^20\d{2}$/D', $period ) ) {
		$result = kekidi_scrutins_normalize_senate( kekidi_scrutins_fetch( 'https://www.senat.fr/scrutin-public/' . $period . '/scr' . $period . '-' . $number . '.html' ), $number, $period );
	} else {
		throw new InvalidArgumentException( 'Choisissez l’Assemblée (législature 17) ou le Sénat avec une année de début de session.' ); }
	$result['retrievedAt'] = gmdate( 'c' );
	return $result;
}
