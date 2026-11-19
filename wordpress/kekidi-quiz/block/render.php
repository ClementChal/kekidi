<?php
/**
 * Rendu du bloc de quiz.
 *
 * @package Kekidi_Quiz
 */

defined( 'ABSPATH' ) || exit;

if ( kekidi_quiz_error( $attributes ) ) {
	echo '<p class="quiz-error">Ce quiz est en préparation.</p>';
	return;
}
$choices      = $attributes['choices'];
$count        = count(
	array_filter(
		$choices,
		static function ( array $choice ): bool {
			return $choice['correct'];
		}
	)
);
$multiple     = $count > 1;
$uid          = wp_unique_id( 'kekidi-quiz-' );
$quiz_post_id = (int) ( $block->context['postId'] ?? get_the_ID() );
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'kekidi-quiz' ) ); ?> data-kekidi-quiz data-quiz-key="<?php echo esc_attr( (string) $quiz_post_id ); ?>">
	<form class="quiz-form" data-quiz>
		<fieldset aria-describedby="<?php echo esc_attr( $uid . 'instructions ' . $uid . 'error' ); ?>" aria-label="Choix de réponse">
			<p class="quiz-instructions" id="<?php echo esc_attr( $uid . 'instructions' ); ?>"><?php echo $multiple ? 'Plusieurs réponses possibles.' : 'Une seule réponse possible.'; ?></p>
			<div class="quiz-options">
				<?php foreach ( $choices as $index => $choice ) : ?>
				<label class="quiz-option">
					<input class="quiz-checkbox" data-choice type="<?php echo $multiple ? 'checkbox' : 'radio'; ?>" name="<?php echo esc_attr( $uid . 'answer' ); ?>" value="<?php echo esc_attr( (string) $index ); ?>" data-correct="<?php echo $choice['correct'] ? 'true' : 'false'; ?>" aria-describedby="<?php echo esc_attr( $uid . 'instructions ' . $uid . 'error' ); ?>">
					<span class="quiz-option-copy"><span class="quiz-letter"><?php echo esc_html( chr( 65 + $index ) . '.' ); ?></span><span class="quiz-option-label"><?php echo esc_html( $choice['label'] ); ?></span><span class="quiz-option-status"></span></span>
				</label>
				<?php endforeach; ?>
			</div>
		</fieldset>
		<p id="<?php echo esc_attr( $uid . 'error' ); ?>" class="quiz-error" role="alert" hidden>Sélectionnez au moins une réponse avant de valider.</p>
		<button class="wp-element-button" type="button" data-validate-quiz aria-controls="<?php echo esc_attr( $uid . 'result ' . $uid . 'explanation' ); ?>">Valider mes réponses</button>
	</form>
	<section id="<?php echo esc_attr( $uid . 'result' ); ?>" class="quiz-result" data-quiz-result tabindex="-1" role="status" aria-live="polite" aria-atomic="true" hidden><h2></h2><p></p></section>
	<div class="quiz-explanation" id="<?php echo esc_attr( $uid . 'explanation' ); ?>" data-quiz-explanation aria-hidden="true" inert><div class="quiz-explanation-inner"><?php echo $content; ?></div></div>
	<noscript><p>Activez JavaScript pour répondre à ce quiz et afficher son explication.</p></noscript>
</div>
