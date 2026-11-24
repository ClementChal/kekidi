<?php
/**
 * Title: Accueil Kekidi
 * Slug: kekidi/accueil
 * Categories: kekidi
 * Inserter: true
 *
 * @package Kekidi
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"tagName":"section","className":"wrap hero-layout","layout":{"type":"default"}} -->
<section class="wp-block-group wrap hero-layout"><!-- wp:group {"tagName":"div","className":"hero-copy","layout":{"type":"default"}} -->
<div class="wp-block-group hero-copy"><!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">Les questions sont <span class="highlight">dans la rue.</span><br>Les réponses, ici.</h1>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>Une affiche vous a fait lever un sourcil ?<br>Bienvenue chez Kekidi?! On pose des questions dans l’espace public pour ouvrir la discussion.</p>
<!-- /wp:paragraph --><!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#questions">Explorer les questions <svg class="arrow" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --><!-- wp:group {"tagName":"div","className":"poster-area","layout":{"type":"default"}} -->
<div class="wp-block-group poster-area"><!-- wp:group {"tagName":"div","className":"poster","layout":{"type":"default"}} -->
<div class="wp-block-group poster"><!-- wp:group {"tagName":"div","className":"poster-top","layout":{"type":"default"}} -->
<div class="wp-block-group poster-top"><!-- wp:paragraph {"className":"brand-symbol poster-logo"} -->
<p class="brand-symbol poster-logo">?!</p>
<!-- /wp:paragraph --><!-- wp:paragraph {"className":"poster-tagline"} -->
<p class="poster-tagline">On se pose la question</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --><!-- wp:paragraph {"className":"poster-question"} -->
<p class="poster-question">Quelles sont les missions de la Police ?</p>
<!-- /wp:paragraph --><!-- wp:list {"ordered":true,"className":"poster-choices"} -->
<ol class="wp-block-list poster-choices"><!-- wp:list-item -->
<li>Garantir la sécurité publique</li>
<!-- /wp:list-item --><!-- wp:list-item -->
<li>Défendre les institutions</li>
<!-- /wp:list-item --><!-- wp:list-item -->
<li>Protéger les biens des possédants privés</li>
<!-- /wp:list-item --><!-- wp:list-item -->
<li>Entretenir une relation empreinte de courtoisie avec la population</li>
<!-- /wp:list-item --><!-- wp:list-item -->
<li>Découvrir les auteurs de crimes</li>
<!-- /wp:list-item --></ol>
<!-- /wp:list --><!-- wp:group {"tagName":"div","className":"poster-bottom","layout":{"type":"default"}} -->
<div class="wp-block-group poster-bottom"><!-- wp:paragraph -->
<p>Les réponses<br>et les sources.</p>
<!-- /wp:paragraph --><!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"poster-qr"} -->
<figure class="wp-block-image size-full poster-qr"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/affiche-qr-illustratif.svg' ) ); ?>" alt=""/></figure>
<!-- /wp:image --></div>
<!-- /wp:group --><!-- wp:paragraph {"className":"poster-signature"} -->
<p class="poster-signature">Une initiative citoyenne</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group --><!-- wp:group {"tagName":"section","className":"question-section","anchor":"questions","layout":{"type":"default"}} -->
<section id="questions" class="wp-block-group question-section"><!-- wp:group {"tagName":"div","className":"wrap","layout":{"type":"default"}} -->
<div class="wp-block-group wrap"><!-- wp:group {"tagName":"div","className":"section-heading","layout":{"type":"default"}} -->
<div class="wp-block-group section-heading"><!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Ça mérite une question.</h2>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>Des questions à croiser.<br>Des réponses à découvrir.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --><!-- wp:query {"queryId":1,"query":{"perPage":12,"pages":0,"offset":0,"postType":"post","order":"asc","orderBy":"ID","author":"","search":"","exclude":[],"sticky":"exclude","inherit":false}} -->
<div class="wp-block-query"><!-- wp:post-template {"className":"questions-grid"} -->
<!-- wp:group {"tagName":"article","className":"card question-card","layout":{"type":"default"}} -->
<article class="wp-block-group card question-card"><!-- wp:post-terms {"term":"category","className":"badge"} /--><!-- wp:post-title {"level":3,"isLink":true} /--><!-- wp:group {"tagName":"div","className":"card-foot","layout":{"type":"default"}} -->
<div class="wp-block-group card-foot"><!-- wp:read-more {"content":"Lire la réponse","className":"read-link"} /--></div>
<!-- /wp:group --></article>
<!-- /wp:group -->
<!-- /wp:post-template -->
<!-- wp:query-pagination -->
<!-- wp:query-pagination-previous {"label":"Questions précédentes"} /--><!-- wp:query-pagination-numbers /--><!-- wp:query-pagination-next {"label":"Questions suivantes"} /-->
<!-- /wp:query-pagination -->
<!-- wp:query-no-results -->
<!-- wp:paragraph -->
<p>Les premières questions arrivent bientôt.</p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results --></div>
<!-- /wp:query --></div>
<!-- /wp:group --></section>
<!-- /wp:group --><!-- wp:group {"tagName":"section","className":"how-section","anchor":"principe","layout":{"type":"default"}} -->
<section id="principe" class="wp-block-group how-section"><!-- wp:group {"tagName":"div","className":"wrap how-layout","layout":{"type":"default"}} -->
<div class="wp-block-group wrap how-layout"><!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">De l’affiche<br>au déclic.</h2>
<!-- /wp:heading --><!-- wp:list {"ordered":true,"className":"steps"} -->
<ol class="wp-block-list steps"><!-- wp:list-item -->
<li><!-- wp:paragraph {"className":"step-number"} -->
<p class="step-number">1</p>
<!-- /wp:paragraph --><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Une question vous arrête.</h3>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>Vous la croisez sur une affiche, dans l’espace public.</p>
<!-- /wp:paragraph --></li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li><!-- wp:paragraph {"className":"step-number"} -->
<p class="step-number">2</p>
<!-- /wp:paragraph --><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Vous scannez.</h3>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>Le QR code ouvre directement la réponse à cette question.</p>
<!-- /wp:paragraph --></li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li><!-- wp:paragraph {"className":"step-number"} -->
<p class="step-number">3</p>
<!-- /wp:paragraph --><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Vous creusez, ou pas.</h3>
<!-- /wp:heading --><!-- wp:paragraph -->
<p>L’essentiel d’abord. Les explications et les sources ensuite.</p>
<!-- /wp:paragraph --></li>
<!-- /wp:list-item --></ol>
<!-- /wp:list --></div>
<!-- /wp:group --></section>
<!-- /wp:group --><!-- wp:group {"tagName":"section","className":"wrap collective","anchor":"collectif","layout":{"type":"default"}} -->
<section id="collectif" class="wp-block-group wrap collective"><!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Un projet citoyen.<br>Une invitation à la curiosité.</h2>
<!-- /wp:heading --><!-- wp:group {"tagName":"div","className":"collective-copy","layout":{"type":"default"}} -->
<div class="wp-block-group collective-copy"><!-- wp:paragraph -->
<p>Kekidi?! est porté par une initiative citoyenne. Notre point de départ : afficher des questions dans l’espace public et proposer un endroit simple pour en lire les réponses.</p>
<!-- /wp:paragraph --><!-- wp:paragraph -->
<p>Une question, un scan, une lecture. Et peut-être une conversation qui continue hors de l’écran.</p>
<!-- /wp:paragraph --><!-- wp:paragraph {"className":"collective-action"} -->
<p class="collective-action"><a class="read-link" href="#questions">Revenir aux questions <svg class="arrow" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
