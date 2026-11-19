<?php
// À exécuter avec Studio WP-CLI eval-file ; aucun accès réseau.
require_once dirname( __DIR__ ) . '/includes/data.php';
require_once dirname( __DIR__ ) . '/includes/sources.php';
$assert = static function ( bool $condition, string $message ): void {
    if ( ! $condition ) { throw new RuntimeException( $message ); }
};
$fixtures = __DIR__ . '/fixtures/';
$assert( kekidi_scrutins_date( 'jeudi 1er octobre 2026' ) === '2026-10-01', 'Premier jour écrit 1er' );
$payload = json_decode( file_get_contents( $fixtures . 'assemblee-7987.json' ), true );
$quiz = kekidi_scrutins_normalize_assemblee( $payload, file_get_contents( $fixtures . 'assemblee-7987.html' ), 7987, '17' );
$assert( $quiz['totals'] === array( 'pour' => 313, 'contre' => 199, 'abstention' => 5, 'nonVotants' => 1 ), 'Totaux du scrutin 7987' );
$assert( $quiz['date'] === '2026-07-07' && count( $quiz['groups'] ) === 12, 'Identité et groupes historiques' );
$expected = kekidi_scrutins_expected( $quiz['groups'], 'pour' );
$assert( count( $expected ) === 7 && in_array( 'PO845454', $expected, true ), 'Les Démocrates majoritairement pour, malgré le champ contradictoire' );
$wrong_title = $payload;
$wrong_title['data']['sourceData']['titre'] = 'Autre mesure';
$checked_title = kekidi_scrutins_normalize_assemblee( $wrong_title, file_get_contents( $fixtures . 'assemblee-7987.html' ), 7987, '17' );
$assert( $checked_title['title'] === $quiz['title'], 'Le titre vient de la page officielle' );
$assert( count( $quiz['warnings'] ) === 1, 'Anomalie explicitée sans inventer le corrigé' );
$assert( count( kekidi_scrutins_expected( $quiz['groups'], 'contre' ) ) === 4, 'Quatre groupes majoritairement contre' );
foreach ( array( 8435, 9785 ) as $number ) {
    $extra = kekidi_scrutins_normalize_assemblee( json_decode( file_get_contents( $fixtures . 'assemblee-' . $number . '.json' ), true ), file_get_contents( $fixtures . 'assemblee-' . $number . '.html' ), $number, '17' );
    $assert( count( $extra['groups'] ) === 12, 'Groupes conservés, même sans aucun votant' );
    if ( 8435 === $number ) { $assert( '2026-10-01' === $extra['date'], 'Import d’une date officielle écrite 1er' ); }
}
$senate = kekidi_scrutins_normalize_senate( file_get_contents( $fixtures . 'senat-2025-33.html' ), 33, '2025' );
$assert( $senate['date'] === '2025-11-20' && count( $senate['groups'] ) === 9, 'Sénat et session' );
$assert( $senate['totals']['pour'] + $senate['totals']['contre'] + $senate['totals']['abstention'] === 308, 'Sénat : total de 308 participants' );
$lr = array_values( array_filter( $senate['groups'], static function ( array $g ): bool { return 'UMP' === $g['id']; } ) )[0];
$assert( $lr['contre'] === 129, 'Les Républicains : 129 contre officiels' );
$assert( kekidi_scrutins_expected( array( array( 'id' => 'a', 'pour' => 1, 'contre' => 1, 'abstention' => 0 ) ), 'pour' ) === array(), '50 % ne suffit pas' );
$assert( kekidi_scrutins_expected( array( array( 'id' => 'a', 'pour' => 0, 'contre' => 0, 'abstention' => 0 ) ), 'pour' ) === array(), 'Zéro participant' );
$assert( kekidi_scrutins_expected( array( array( 'id' => 'a', 'pour' => 4, 'contre' => 3, 'abstention' => 2 ) ), 'pour' ) === array(), 'Position dominante ne signifie pas majorité stricte' );
$assert( kekidi_scrutins_expected( $senate['groups'], 'pour' ) !== kekidi_scrutins_expected( $senate['groups'], 'contre' ), 'Sens du quiz pris en compte' );
$failures = array(
    static function () use ( $payload, $fixtures ): void { kekidi_scrutins_normalize_assemblee( $payload, file_get_contents( $fixtures . 'assemblee-7987.html' ), 4042, '17' ); },
    static function () use ( $payload ): void { kekidi_scrutins_normalize_assemblee( $payload, '<html></html>', 7987, '17' ); },
    static function () use ( $payload, $fixtures ): void { $payload['data']['sourceData']['ventilationVotes']['organe']['groupes']['groupe'][0]['vote']['decompteVoix']['pour'] = '121'; kekidi_scrutins_normalize_assemblee( $payload, file_get_contents( $fixtures . 'assemblee-7987.html' ), 7987, '17' ); },
    static function () use ( $fixtures ): void { kekidi_scrutins_normalize_senate( file_get_contents( $fixtures . 'senat-2025-33.html' ), 33, '2026' ); },
    static function (): void { kekidi_scrutins_integer( '-1' ); },
    static function (): void { kekidi_scrutins_integer( '1.5' ); },
    static function (): void { kekidi_scrutins_expected( array(), 'autre' ); },
);
foreach ( $failures as $case ) {
    $failed = false;
    try { $case(); } catch ( InvalidArgumentException $error ) { $failed = true; }
    $assert( $failed, 'Une donnée incohérente doit être refusée' );
}
echo "Données : Assemblée/Sénat, sources officielles, anomalie DEM, pour/contre, majorité stricte et refus vérifiés.\n";
