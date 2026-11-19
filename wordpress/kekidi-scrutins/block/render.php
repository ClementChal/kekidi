<?php
defined( 'ABSPATH' ) || exit;
$post_id = (int) ( $block->context['postId'] ?? get_the_ID() );
$snapshot = kekidi_scrutins_snapshot( $post_id, $attributes['snapshotId'] ?? '' );
$direction = $attributes['direction'] ?? 'pour';
if ( ! $snapshot || ! in_array( $direction, array( 'pour', 'contre' ), true ) ) {
    echo '<p class="quiz-error">Ce quiz est en préparation.</p>';
    return;
}
$logos = is_array( $attributes['logos'] ?? null ) ? $attributes['logos'] : array();
$groups = array_values( array_filter( $snapshot['groups'], static function ( array $group ): bool { return ! in_array( $group['id'], array( 'NI', 'PO840056' ), true ); } ) );
if ( 'assemblee' === $snapshot['chamber'] ) {
    $order = array_flip( array( 'PO845514', 'PO845413', 'PO845439', 'PO845419', 'PO845485', 'PO845454', 'PO845407', 'PO845470', 'PO845425', 'PO872880', 'PO845401' ) );
    usort( $groups, static function ( array $a, array $b ) use ( $order ): int { return ( $order[ $a['id'] ] ?? 99 ) <=> ( $order[ $b['id'] ] ?? 99 ); } );
}
$expected = kekidi_scrutins_expected( $snapshot['groups'], $direction );
$chamber = 'senat' === $snapshot['chamber'] ? 'Sénat' : 'Assemblée nationale';
$date = wp_date( 'j F Y', strtotime( $snapshot['date'] . 'T12:00:00+00:00' ), new DateTimeZone( 'Europe/Paris' ) );
$uid = wp_unique_id( 'kekidi-vote-' );
$sorted = $groups;
usort( $sorted, static function ( array $a, array $b ) use ( $direction ): int {
    $a_total = $a['pour'] + $a['contre'] + $a['abstention'];
    $b_total = $b['pour'] + $b['contre'] + $b['abstention'];
    return ( $b_total ? $b[ $direction ] / $b_total : 0 ) <=> ( $a_total ? $a[ $direction ] / $a_total : 0 );
} );
$majority_names = array_column( array_filter( $groups, static function ( array $group ) use ( $expected ): bool { return in_array( $group['id'], $expected, true ); } ), 'name' );
$summary = $majority_names ? implode( ', ', $majority_names ) . ( count( $majority_names ) > 1 ? ' ont voté' : ' a voté' ) . ' majoritairement ' . $direction . '.' : 'Aucun groupe n’a voté majoritairement ' . $direction . '.';
$totals = $snapshot['totals'];
$non_inscrits = array_values( array_filter( $snapshot['groups'], static function ( array $group ): bool { return in_array( $group['id'], array( 'NI', 'PO840056' ), true ); } ) );
$links = new WP_HTML_Tag_Processor( $content );
while ( $links->next_tag( 'A' ) ) {
    $rels = preg_split( '/\s+/', (string) $links->get_attribute( 'rel' ), -1, PREG_SPLIT_NO_EMPTY );
    $links->set_attribute( 'target', '_blank' );
    $links->set_attribute( 'rel', implode( ' ', array_unique( array_merge( $rels, array( 'noopener', 'noreferrer' ) ) ) ) );
}
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'kekidi-vote-quiz' ) ); ?> data-kekidi-vote data-direction="<?php echo esc_attr( $direction ); ?>" data-quiz-key="<?php echo esc_attr( (string) $post_id ); ?>">
    <p class="vote-context"><?php echo esc_html( $chamber . ' · ' . $date ); ?></p>
    <p class="vote-measure"><?php echo esc_html( $attributes['measure'] ?? '' ); ?></p>
    <form class="quiz-form vote-form" data-vote-form>
        <fieldset aria-label="<?php echo esc_attr( 'Groupes parlementaires ayant voté majoritairement ' . $direction ); ?>">
            <legend class="vote-legend"><?php echo esc_html( 'Quels groupes ont voté majoritairement ' . $direction . ' ?' ); ?></legend>
            <div class="quiz-options vote-options">
                <?php foreach ( $groups as $group ) : ?>
                    <label class="quiz-option vote-option">
                        <span class="vote-party-name"><?php echo esc_html( $group['name'] ); ?></span>
                        <?php echo kekidi_scrutins_logo_html( $group, $logos ); ?>
                        <input class="checkbox quiz-checkbox" type="checkbox" name="party" value="<?php echo esc_attr( $group['id'] ); ?>" data-correct="<?php echo in_array( $group['id'], $expected, true ) ? 'true' : 'false'; ?>">
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>
        <div class="vote-validation">
            <p class="vote-selection-count" role="status" aria-live="polite">Aucun groupe sélectionné</p>
            <button class="wp-element-button" type="submit" data-validate-vote aria-controls="<?php echo esc_attr( $uid . 'result ' . $uid . 'chart' ); ?>">Valider mes réponses</button>
        </div>
    </form>
    <section class="quiz-result vote-result" id="<?php echo esc_attr( $uid . 'result' ); ?>" tabindex="-1" role="status" aria-live="polite" hidden><h2></h2><p></p></section>
    <section class="vote-chart" id="<?php echo esc_attr( $uid . 'chart' ); ?>" aria-labelledby="<?php echo esc_attr( $uid . 'chart-title' ); ?>" hidden>
        <h2 id="<?php echo esc_attr( $uid . 'chart-title' ); ?>">Et dans l’hémicycle ?</h2>
        <div class="vote-key" aria-label="Légende"><span><i class="vote-segment-0"></i>Pour</span><span><i class="vote-segment-1"></i>Contre</span><span><i class="vote-segment-2"></i>Abstention</span></div>
        <ol class="vote-chart-list">
            <?php foreach ( $sorted as $group ) :
                $total = $group['pour'] + $group['contre'] + $group['abstention'];
                $values = 'Pour ' . kekidi_scrutins_percentage( $group['pour'], $total ) . ' · Contre ' . kekidi_scrutins_percentage( $group['contre'], $total ) . ' · Abstention ' . kekidi_scrutins_percentage( $group['abstention'], $total );
                ?>
                <li class="vote-chart-row" data-chart-party="<?php echo esc_attr( $group['id'] ); ?>">
                    <div class="vote-chart-label"><?php echo kekidi_scrutins_logo_html( $group, $logos ); ?><div><h3><?php echo esc_html( $group['name'] ); ?></h3><p class="vote-comparison" data-comparison></p></div></div>
                    <div class="vote-bar" role="img" aria-label="<?php echo esc_attr( $group['name'] . ' : ' . $values . '. ' . $total . ' participants.' ); ?>">
                        <?php foreach ( array( 'pour', 'contre', 'abstention' ) as $index => $category ) : ?>
                            <span class="vote-segment vote-segment-<?php echo (int) $index; ?>" style="width:<?php echo esc_attr( (string) ( $total ? $group[ $category ] * 100 / $total : 0 ) ); ?>%"></span>
                        <?php endforeach; ?>
                    </div>
                    <p class="vote-values"><?php echo esc_html( $values ); ?></p>
                    <details class="vote-counts"><summary>Voir les effectifs · <?php echo (int) $total; ?> participant<?php echo 1 === $total ? '' : 's'; ?></summary><p><?php echo esc_html( $group['pour'] . ' pour · ' . $group['contre'] . ' contre · ' . $group['abstention'] . ' abstention' . ( $group['abstention'] > 1 ? 's' : '' ) . '. Non-votants recensés : ' . $group['nonVotants'] . ', exclus des pourcentages.' ); ?></p></details>
                </li>
            <?php endforeach; ?>
        </ol>
        <section class="answer-detail vote-explanation">
            <?php if ( empty( $attributes['editableExplanation'] ) ) : ?>
            <h2>On vous explique.</h2>
            <p><?php echo esc_html( $summary . ' Résultat du scrutin : ' . $totals['pour'] . ' votes pour, ' . $totals['contre'] . ' contre et ' . $totals['abstention'] . ' abstentions.' ); ?></p>
            <?php endif; ?>
            <div class="vote-editorial"><?php echo $links->get_updated_html(); ?></div>
            <?php if ( empty( $attributes['editableExplanation'] ) ) : ?>
            <p><?php echo (int) count( $groups ); ?> groupes sont représentés. <?php if ( $non_inscrits ) : ?>Les non-inscrits ne constituent pas un groupe ; leurs <?php echo (int) $non_inscrits[0]['pour']; ?> votes pour, <?php echo (int) $non_inscrits[0]['contre']; ?> contre et <?php echo (int) $non_inscrits[0]['abstention']; ?> abstentions sont inclus dans le résultat du scrutin.<?php endif; ?></p>
            <p>Source : <a href="<?php echo esc_url( $snapshot['sourceUrl'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $chamber . ' · scrutin n° ' . $snapshot['number'] . ' (nouvel onglet)' ); ?></a>. Données vérifiées sur la page officielle et enregistrées le <?php echo esc_html( wp_date( 'j F Y', strtotime( $snapshot['retrievedAt'] ), new DateTimeZone( 'Europe/Paris' ) ) ); ?>.</p>
            <details class="vote-source-details"><summary>Intitulé officiel du scrutin</summary><p><?php echo esc_html( $snapshot['title'] ); ?></p></details>
            <p>Majorité strictement supérieure à 50 % des pour, contre et abstentions. Les pourcentages sont arrondis à une décimale ; leur somme affichée peut différer de 100 %. Certains emblèmes représentent un parti associé au groupe.</p>
            <?php endif; ?>
        </section>
    </section>
    <noscript><p>Activez JavaScript pour répondre au quiz. <a href="<?php echo esc_url( $snapshot['sourceUrl'] ); ?>">Consulter directement le scrutin.</a></p></noscript>
</div>
