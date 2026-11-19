<?php
/**
 * Title: Page Contact Kekidi
 * Slug: kekidi/contact
 * Categories: kekidi
 * Block Types: core/post-content
 *
 * @package Kekidi_Contact
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"className":"contact-intro","layout":{"type":"default"}} -->
<div class="wp-block-group contact-intro"><!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">À vous de poser<br>la question.</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Une envie de comprendre ? Une idée pour une prochaine affiche ? Écrivez-nous.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:forminator/forms {"module_id":"<?php echo (int) get_option( 'kekidi_contact_form_id' ); ?>"} -->
<div class="forminator-guttenberg">[forminator_form id="<?php echo (int) get_option( 'kekidi_contact_form_id' ); ?>"]</div>
<!-- /wp:forminator/forms -->
