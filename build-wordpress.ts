import { mkdirSync, readFileSync, writeFileSync, copyFileSync, readdirSync, appendFileSync, rmSync } from 'node:fs';
import { resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import assert from 'node:assert/strict';

// Le thème est généré séparément pour conserver la maquette Open Design.
const root = new URL('.', import.meta.url);
process.chdir(fileURLToPath(root));
type Question = { id: string; category: string; question: string; answer: string; explanation: string; sources: { label: string; url: string }[] };
const questions: Question[] = JSON.parse(readFileSync('questions.json', 'utf8'));
assert(questions.length > 0);
assert.equal(new Set(questions.map(q => q.id)).size, questions.length);
for (const q of questions) {
  assert(/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(q.id));
  for (const value of [q.category, q.question, q.answer, q.explanation]) assert(typeof value === 'string' && value.trim());
  assert(Array.isArray(q.sources));
  for (const source of q.sources) {
    assert(source.label.trim());
    assert(['http:', 'https:'].includes(new URL(source.url).protocol));
  }
}

const theme = 'wordpress/kekidi';
for (const directory of ['', '/templates', '/parts', '/patterns', '/assets']) mkdirSync(theme + directory, { recursive: true });
const write = (path: string, content: string): void => writeFileSync(`${theme}/${path}`, content.trim() + '\n');
const escape = (value: string): string => value.replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
const block = (name: string, attrs: object = {}, html?: string): string => {
  const attributes = Object.keys(attrs).length ? ' ' + JSON.stringify(attrs) : '';
  return html === undefined ? `<!-- wp:${name}${attributes} /-->` : `<!-- wp:${name}${attributes} -->\n${html}\n<!-- /wp:${name} -->`;
};
const group = (className: string, content: string, tagName = 'div', anchor?: string, extraAttrs: object = {}): string => block('group', { tagName, className, ...(anchor ? { anchor } : {}), layout: { type: 'default' }, ...extraAttrs }, `<${tagName}${anchor ? ` id="${anchor}"` : ''} class="wp-block-group ${className}">${content}</${tagName}>`);
const p = (html: string, className = ''): string => block('paragraph', className ? { className } : {}, `<p${className ? ` class="${className}"` : ''}>${html}</p>`);
const heading = (html: string, level = 2, className = ''): string => block('heading', { level, ...(className ? { className } : {}) }, `<h${level} class="wp-block-heading${className ? ' ' + className : ''}">${html}</h${level}>`);
const arrow = '<svg class="arrow" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>';
const backArrow = arrow.replace('M5 12h14m-6-6 6 6-6 6', 'M19 12H5m6-6-6 6 6 6');
const button = (label: string, href: string): string => block('buttons', {}, `<div class="wp-block-buttons">${block('button', {}, `<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="${href}">${label}</a></div>`)}</div>`);
const home = `<?php echo esc_url( home_url( '/' ) ); ?>`;
const pattern = (slug: string): string => block('pattern', { slug: `kekidi/${slug}` });
const part = (slug: string, tagName: string): string => block('template-part', { slug, tagName });
const writePattern = (slug: string, title: string, content: string, inserter = true): void => write(`patterns/${slug}.php`, `<?php
/**
 * Title: ${title}
 * Slug: kekidi/${slug}
 * Categories: kekidi
 * Inserter: ${inserter ? 'true' : 'false'}
 */
defined( 'ABSPATH' ) || exit;
?>
${content}`);

write('style.css', `/*
Theme Name: Kekidi
Description: Le thème à blocs du collectif Kekidi?! — questions dans la rue, réponses ici.
Version: 1.4.0
Requires at least: 6.6
Requires PHP: 7.4
Text Domain: kekidi
License: GNU General Public License v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/`);

write('functions.php', `<?php
defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
    register_block_pattern_category( 'kekidi', array( 'label' => 'Kekidi' ) );
} );

add_action( 'after_setup_theme', function () {
    add_theme_support( 'editor-styles' );
    add_editor_style( array( 'assets/daisyui.css', 'style.css' ) );
} );

// Charger la maquette après les styles globaux évite leurs remises à zéro.
add_action( 'wp_enqueue_scripts', function () {
    $version = wp_get_theme()->get( 'Version' );
    wp_enqueue_style( 'kekidi-daisyui', get_theme_file_uri( 'assets/daisyui.css' ), array(), $version );
    wp_enqueue_style( 'kekidi', get_stylesheet_uri(), array( 'kekidi-daisyui' ), $version );
    if ( ! is_user_logged_in() ) {
        wp_enqueue_script( 'kekidi-prefetch', get_theme_file_uri( 'assets/kekidi-prefetch.js' ), array(), $version, true );
    }
}, 20 );

// Les liens du contenu éditorial s’ouvrent dans un nouvel onglet.
add_filter( 'render_block_core/group', function ( string $content, array $block ): string {
    $classes = explode( ' ', $block['attrs']['className'] ?? '' );
    if ( ! array_intersect( array( 'answer-detail', 'sources' ), $classes ) ) {
        return $content;
    }

    $processor = new WP_HTML_Tag_Processor( $content );
    while ( $processor->next_tag( 'A' ) ) {
        $rels = preg_split( '/\\s+/', strtolower( (string) $processor->get_attribute( 'rel' ) ), -1, PREG_SPLIT_NO_EMPTY );
        foreach ( array( 'noopener', 'noreferrer' ) as $rel ) {
            if ( ! in_array( $rel, $rels, true ) ) {
                $rels[] = $rel;
            }
        }
        $processor->set_attribute( 'target', '_blank' );
        $processor->set_attribute( 'rel', implode( ' ', $rels ) );
    }

    return $processor->get_updated_html();
}, 10, 2 );

// Les blocs natifs restent éditables ; la carte publiée forme un seul lien.
add_filter( 'render_block_core/group', function ( string $content, array $block ): string {
    $classes = explode( ' ', $block['attrs']['className'] ?? '' );
    if ( ! in_array( 'question-card', $classes, true ) || 'article' !== ( $block['attrs']['tagName'] ?? '' ) ) {
        return $content;
    }
    $content = trim( $content );
    $opening_end = strpos( $content, '>' ) + 1;
    $inner = substr( $content, $opening_end, -strlen( '</article>' ) );
    $inner = preg_replace( '~</?a\\b[^>]*>~i', '', $inner );
    $link = '<a class="question-card-link" href="' . esc_url( get_permalink() ) . '" aria-label="' . esc_attr( 'Lire la réponse : ' . get_the_title() ) . '">';
    return substr( $content, 0, $opening_end ) . $link . $inner . '</a></article>';
}, 10, 2 );

add_filter( 'render_block_core/read-more', function ( string $content, array $block ): string {
    if ( 'read-link' !== ( $block['attrs']['className'] ?? '' ) ) {
        return $content;
    }
    return '<span class="read-link">Lire la réponse ${arrow}</span>';
}, 10, 2 );

add_filter( 'render_block_core/post-navigation-link', function ( string $content, array $block ): string {
    if ( 'read-link kekidi-next' !== ( $block['attrs']['className'] ?? '' ) ) {
        return $content;
    }
    $ids = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids', 'orderby' => 'ID', 'order' => 'ASC' ) );
    $position = array_search( get_the_ID(), $ids, true );
    if ( count( $ids ) < 2 || false === $position ) {
        return '';
    }
    $next = $ids[ ( $position + 1 ) % count( $ids ) ];
    return '<a class="read-link kekidi-next" href="' . esc_url( get_permalink( $next ) ) . '" aria-label="' . esc_attr( 'Question suivante : ' . get_the_title( $next ) ) . '">Question suivante ${arrow}</a>';
}, 10, 2 );`);

write('theme.json', JSON.stringify({
  $schema: 'https://schemas.wp.org/wp/6.6/theme.json', version: 3,
  settings: {
    appearanceTools: true,
    layout: { contentSize: '1120px', wideSize: '1184px' },
    color: { defaultPalette: false, palette: [
      { slug: 'cream', name: 'Crème', color: '#FFF8F0' }, { slug: 'white', name: 'Blanc', color: '#FFFFFF' },
      { slug: 'ink', name: 'Encre', color: '#1F1F1F' }, { slug: 'pink', name: 'Rose', color: '#FFB5D8' },
      { slug: 'green', name: 'Menthe', color: '#B5E6D3' }, { slug: 'yellow', name: 'Jaune', color: '#FFE89A' },
      { slug: 'blue', name: 'Bleu', color: '#B5DBFF' }
    ] },
    typography: { fontFamilies: [
      { slug: 'body', name: 'DM Sans', fontFamily: '"DM Sans", Verdana, sans-serif', fontFace: [
        { fontFamily: 'DM Sans', fontWeight: '400', fontStyle: 'normal', src: ['file:./assets/dm-sans-regular.ttf'] },
        { fontFamily: 'DM Sans', fontWeight: '600', fontStyle: 'normal', src: ['file:./assets/dm-sans-semibold.ttf'] }
      ] },
      { slug: 'display', name: 'Nunito', fontFamily: 'Nunito, "Trebuchet MS", sans-serif', fontFace: [
        { fontFamily: 'Nunito', fontWeight: '800', fontStyle: 'normal', src: ['file:./assets/nunito-extra-bold.ttf'] }
      ] }
    ] }
  },
  styles: {
    color: { background: 'var:preset|color|cream', text: 'var:preset|color|ink' },
    spacing: { padding: { top: '0', right: '0', bottom: '0', left: '0' } },
    typography: { fontFamily: 'var:preset|font-family|body', fontSize: '1rem', lineHeight: '1.6' },
    elements: {
      heading: { typography: { fontFamily: 'var:preset|font-family|display', fontWeight: '800', lineHeight: '1.12' } },
      link: { color: { text: 'inherit' } },
      button: { color: { background: 'var:preset|color|green', text: 'var:preset|color|ink' }, typography: { fontFamily: 'var:preset|font-family|body', fontWeight: '600' } }
    }
  },
  templateParts: [{ name: 'header', title: 'En-tête accueil', area: 'header' }, { name: 'header-reponse', title: 'En-tête réponses', area: 'header' }, { name: 'header-simple', title: 'En-tête simple', area: 'header' }, { name: 'brand', title: 'Marque commune', area: 'uncategorized' }, { name: 'footer', title: 'Pied de page commun', area: 'footer' }],
  customTemplates: [{ name: 'reponse', title: 'Réponse Kekidi', postTypes: ['post', 'page'] }, { name: 'contact', title: 'Contact Kekidi', postTypes: ['page'] }]
}, null, 2));

writePattern('brand', 'Marque Kekidi', p(`<a class="brand" href="${home}" aria-label="Kekidi?! — accueil"><span class="brand-symbol" aria-hidden="true">?!</span>Kekidi?!</a>`), false);
write('parts/brand.html', pattern('brand'));
writePattern('header', 'En-tête Kekidi', group('site-header home-header', group('wrap header-row',
  part('brand', 'div') +
  group('navigation', p(`<a href="${home}#questions">Les questions</a>`) + p(`<a class="collective-nav" href="${home}#collectif">Le collectif</a>`) + p(`<a class="nav-pill" href="${home}#principe">Comment ça marche ?</a>`), 'nav')
)), false);
writePattern('header-reponse', 'En-tête des réponses', group('site-header answer-header', group('wrap header-row', part('brand', 'div') + group('navigation', p(`<a class="nav-pill" href="${home}#questions">${backArrow} Toutes les questions</a>`), 'nav'))), false);
writePattern('header-simple', 'En-tête simple', group('site-header', group('wrap header-row', part('brand', 'div'))), false);
writePattern('footer', 'Pied de page Kekidi', group('site-footer', group('wrap footer-row', p(`<a class="footer-brand" href="${home}">Kekidi?!</a>`) + p('Des questions dans la rue. De la curiosité partout.') + p(`<a class="read-link footer-contact" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Une question ?</a>`))), false);
write('parts/header.html', pattern('header'));
write('parts/header-reponse.html', pattern('header-reponse'));
write('parts/header-simple.html', pattern('header-simple'));
write('parts/footer.html', pattern('footer'));
writePattern('header-contact', 'En-tête contact', group('site-header answer-header', group('wrap header-row', part('brand', 'div') + group('navigation', p(`<a class="nav-pill" href="${home}">${backArrow} L’accueil</a>`), 'nav'))), false);
write('parts/header-contact.html', pattern('header-contact'));
write('templates/contact.html', part('header-contact', 'header') + group('contact-page wrap', block('post-content', { layout: { type: 'default' } }), 'main', 'contenu') + part('footer', 'footer'));


const cards = block('query', { queryId: 1, query: { perPage: 12, pages: 0, offset: 0, postType: 'post', order: 'asc', orderBy: 'ID', author: '', search: '', exclude: [], sticky: 'exclude', inherit: false } },
  `<div class="wp-block-query">${block('post-template', { className: 'questions-grid' },
    group('card question-card',
      block('post-terms', { term: 'category', className: 'badge' }) +
      block('post-title', { level: 3, isLink: true }) +
      group('card-foot', block('read-more', { content: 'Lire la réponse', className: 'read-link' }))
    , 'article'))}
${block('query-pagination', {}, `${block('query-pagination-previous', { label: 'Questions précédentes' })}${block('query-pagination-numbers')}${block('query-pagination-next', { label: 'Questions suivantes' })}`)}
${block('query-no-results', {}, p('Les premières questions arrivent bientôt.'))}</div>`);

const steps = [
  ['Une question vous arrête.', 'Vous la croisez sur une affiche, dans l’espace public.'],
  ['Vous scannez.', 'Le QR code ouvre directement la réponse à cette question.'],
  ['Vous creusez, ou pas.', 'L’essentiel d’abord. Les explications et les sources ensuite.']
].map(([title, text], i) => block('list-item', {}, `<li>${p(String(i + 1), 'step-number')}${heading(title, 3)}${p(text)}</li>`)).join('\n');
writePattern('accueil', 'Accueil Kekidi',
  group('wrap hero-layout',
    group('hero-copy', heading('Les questions sont <span class="highlight">dans la rue.</span><br>Les réponses, ici.', 1) + p('Une affiche vous a fait lever un sourcil ?<br>Bienvenue chez Kekidi?! On pose des questions dans l’espace public pour ouvrir la discussion.') + button(`Explorer les questions ${arrow}`, '#questions')) +
    group('poster-area', group('poster', group('poster-top', p('<strong>Kekidi?!</strong>') + p('On se pose la question.')) + p('À qui appartient l’espace public ?', 'poster-question') + group('poster-bottom', p('Une question croisée dans la rue.<br>Une discussion qui commence.') + p('À vous de voir.', 'poster-stamp'))) + p('Ça commence par une question. Et vous ?', 'poster-caption'))
  , 'section') +
  group('question-section', group('wrap', group('section-heading', heading('Ça mérite une question.') + p('Des questions à croiser.<br>Des réponses à découvrir.')) + cards), 'section', 'questions') +
  group('how-section', group('wrap how-layout', heading('De l’affiche<br>au déclic.') + block('list', { ordered: true, className: 'steps' }, `<ol class="wp-block-list steps">${steps}</ol>`)), 'section', 'principe') +
  group('wrap collective', heading('Un projet citoyen.<br>Une invitation à la curiosité.') + group('collective-copy', p('Kekidi?! est porté par une initiative citoyenne. Notre point de départ : afficher des questions dans l’espace public et proposer un endroit simple pour en lire les réponses.') + p('Une question, un scan, une lecture. Et peut-être une conversation qui continue hors de l’écran.') + p(`<a class="read-link" href="#questions">Revenir aux questions ${arrow}</a>`, 'collective-action')), 'section', 'collectif')
);
write('templates/front-page.html', part('header', 'header') + group('home-main', pattern('accueil'), 'main', 'contenu') + part('footer', 'footer'));

const response = (answer: string, explanation: string, sources: Question['sources']): string =>
  group('answer-panel', p(escape(answer), 'short-answer'), 'section') +
  group('answer-detail', heading('On vous explique.') + p(escape(explanation)), 'section') +
  (sources.length ? group('sources', heading('Pour aller plus loin') + block('list', {}, `<ul class="wp-block-list">${sources.map(s => block('list-item', {}, `<li><a href="${escape(s.url)}">${escape(s.label)}</a></li>`)).join('')}</ul>`), 'section') : '');
writePattern('reponse', 'Nouvelle réponse', response('Écrire ici la réponse courte.', 'Développer ici les explications.', []));
writePattern('sources', 'Sources de la réponse', group('sources', heading('Pour aller plus loin') + p('Ajouter le nom de la source, puis sélectionner ce texte pour insérer son lien.'), 'section'));
writePattern('retour', 'Retour à l’accueil', button(`${backArrow} Revenir à l’accueil`, home), false);
const responseTemplate = part('header-reponse', 'header') + group('answer-wrap',
  block('post-title', { level: 1, className: 'answer-question' }) + block('post-content', { layout: { type: 'default' } }) +
  group('answer-next', pattern('retour') + block('post-navigation-link', { type: 'next', className: 'read-link kekidi-next' }))
, 'main', 'contenu') + part('footer', 'footer');
write('templates/single.html', responseTemplate);
write('templates/reponse.html', responseTemplate);
write('templates/page.html', part('header', 'header') + group('answer-wrap', block('post-title', { level: 1, className: 'answer-question' }) + block('post-content', { layout: { type: 'default' } }), 'main', 'contenu') + part('footer', 'footer'));
write('templates/index.html', part('header', 'header') + group('wrap kekidi-archive', block('query-title', { type: 'archive' }) + cards, 'main', 'contenu') + part('footer', 'footer'));
writePattern('introuvable', 'Page introuvable', group('not-found wrap', group('not-found-code', p('404')) + group('not-found-copy', p('Oups, cette piste s’arrête ici.', 'not-found-kicker') + heading('Cette page n’a pas trouvé sa question.', 1) + p('Le lien a peut-être changé. Les questions, elles, sont toujours à l’accueil.') + button(`Retourner à l’accueil ${backArrow}`, home)), 'main', 'contenu'), false);
write('templates/404.html', part('header-simple', 'header') + pattern('introuvable') + part('footer', 'footer'));

for (const name of readdirSync('assets')) copyFileSync(`assets/${name}`, `${theme}/assets/${name}`);
const adapterCss = `/* Seuls les conteneurs ajoutés par WordPress nécessitent une adaptation. */
.wp-block-template-part { margin-block: 0; }
.wp-site-blocks { padding: 0; }
.wp-block-group, .wp-block-post-content { min-width: 0; }
.navigation p { margin: 0; }
.poster-top strong { font: 800 1.375rem var(--font-display); }
.poster-bottom p { max-width: 20ch; }
.home-main .wp-block-post-template.questions-grid { display: grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap: var(--space-3); padding: 0; margin: 0; list-style: none; }
.questions-grid > li { display: flex; min-width: 0; }
.home-main .question-card { flex: 1; width: 100%; }
.questions-grid > li:nth-child(3n+2) .badge { background: var(--surface-action); }
.questions-grid > li:nth-child(3n+3) .badge { background: var(--surface-info); }
:root .collective-action { margin-block: 0; }
body:not(.error404) .footer-brand { display: inline-flex; align-items: center; min-height: 44px; }
.answer-next > .wp-block-buttons { flex-shrink: 0; }
.answer-next .wp-element-button { display: flex; }
.answer-next > .wp-block-post-navigation-link { margin: 0; }
.answer-next > .wp-block-post-navigation-link a { display: inline-flex; align-items: center; gap: .5rem; }
.wp-block-query-pagination { display: flex; flex-wrap: wrap; gap: var(--space-3); margin-top: var(--space-4); }
.wp-block-query-pagination a { display: inline-flex; min-height: 44px; align-items: center; }
.wp-block-query-pagination-numbers { display: flex; gap: var(--space-2); align-items: center; }
.kekidi-archive { padding-block: var(--space-5); }
.kekidi-archive > h1 { margin-bottom: var(--space-4); }
/* Les blocs ajoutés hors de la composition gardent la typographie de lecture. */
:is(.answer-wrap .wp-block-post-content, .editor-styles-wrapper:has(.answer-detail)) :is(p, ul, ol, blockquote):not(:where(.answer-panel *, .sources *, .kekidi-quiz *, .kekidi-quiz-editor *, .kekidi-vote-quiz *, .kekidi-scrutins-editor *)) {
  font-family: var(--font-body); font-size: 1.125rem; line-height: 1.65; color: var(--fg); overflow-wrap: break-word;
}
:is(.answer-wrap .wp-block-post-content, .editor-styles-wrapper:has(.answer-detail)) :is(h2, h3, h4, h5, h6):not(:where(.answer-panel *, .sources *, .kekidi-quiz *, .kekidi-quiz-editor *, .kekidi-vote-quiz *, .kekidi-scrutins-editor *)) {
  font-family: var(--font-display); font-weight: 800; line-height: 1.12; letter-spacing: -.025em; margin-bottom: var(--space-2);
}
:is(.answer-wrap .wp-block-post-content, .editor-styles-wrapper:has(.answer-detail)) h2:not(:where(.answer-panel *, .sources *, .kekidi-quiz *, .kekidi-quiz-editor *, .kekidi-vote-quiz *, .kekidi-scrutins-editor *)) { font-size: 1.5rem; }
:is(.answer-wrap .wp-block-post-content, .editor-styles-wrapper:has(.answer-detail)) h3:not(:where(.answer-panel *, .sources *, .kekidi-quiz *, .kekidi-quiz-editor *, .kekidi-vote-quiz *, .kekidi-scrutins-editor *)) { font-size: 1.25rem; }
:is(.answer-wrap .wp-block-post-content, .editor-styles-wrapper:has(.answer-detail)) :is(h4, h5, h6):not(:where(.answer-panel *, .sources *, .kekidi-quiz *, .kekidi-quiz-editor *, .kekidi-vote-quiz *, .kekidi-scrutins-editor *)) { font-size: 1.125rem; }
:is(.answer-wrap .wp-block-post-content, .editor-styles-wrapper:has(.answer-detail)) :is(p, ul, ol, blockquote):not(:first-child):not(:where(.answer-panel *, .sources *, .kekidi-quiz *, .kekidi-quiz-editor *, .kekidi-vote-quiz *, .kekidi-scrutins-editor *)) { margin-top: var(--space-2); }
:is(.answer-wrap .wp-block-post-content, .editor-styles-wrapper:has(.answer-detail)) :is(h2, h3, h4, h5, h6):not(:first-child):not(:where(.answer-panel *, .sources *, .kekidi-quiz *, .kekidi-quiz-editor *, .kekidi-vote-quiz *, .kekidi-scrutins-editor *)) { margin-top: var(--space-4); }
:is(.answer-wrap .wp-block-post-content, .editor-styles-wrapper:has(.answer-detail)) .wp-block-group:not(:first-child):not(:where(.answer-panel, .answer-detail, .sources, .kekidi-quiz-editor, .answer-panel *, .sources *, .kekidi-quiz *, .kekidi-quiz-editor *, .kekidi-vote-quiz *, .kekidi-scrutins-editor *)) { margin-top: var(--space-4); }
:is(.answer-wrap .wp-block-post-content, .editor-styles-wrapper:has(.answer-detail)) :is(ul, ol):not(:where(.sources *, .kekidi-quiz *, .kekidi-quiz-editor *, .kekidi-vote-quiz *, .kekidi-scrutins-editor *)) { padding-left: 1.5em; }
:is(.answer-wrap .wp-block-post-content, .editor-styles-wrapper:has(.answer-detail)) .wp-block-image { max-width: 100%; margin-block: var(--space-3); }
:is(.answer-wrap .wp-block-post-content, .editor-styles-wrapper:has(.answer-detail)) .wp-block-image img { display: block; max-width: 100%; height: auto; }
:is(.answer-wrap .wp-block-post-content, .editor-styles-wrapper:has(.answer-detail)) .wp-block-image figcaption { font: 400 .875rem/1.5 var(--font-body); color: var(--muted); margin-top: var(--space-1); }
/* Les variables de la maquette suivent aussi les réglages Styles de WordPress. */
body, .editor-styles-wrapper {
  --bg: oklch(from var(--wp--preset--color--cream) l c h); --surface: var(--wp--preset--color--white);
  --fg: oklch(from var(--wp--preset--color--ink) l c h); --accent: oklch(from var(--wp--preset--color--pink) l c h);
  --surface-action: oklch(from var(--wp--preset--color--green) l c h); --surface-note: oklch(from var(--wp--preset--color--yellow) l c h);
  --surface-info: oklch(from var(--wp--preset--color--blue) l c h);
  --border: var(--fg); --line: 2px solid var(--border);
  --shadow: 5px 5px 0 var(--border); --shadow-large: 8px 8px 0 var(--border);
  --font-body: var(--wp--preset--font-family--body); --font-display: var(--wp--preset--font-family--display);
}
@media (max-width:800px) { .home-main .wp-block-post-template.questions-grid { grid-template-columns: minmax(0,1fr); } }
@media (max-width:600px) {
  .home-header .navigation > p:last-child { grid-column: 1 / -1; }
  .steps .step-number { grid-column: 1; }
  .wp-block-buttons, .wp-block-button { max-width: 100%; }
  .hero-copy .wp-block-button, .answer-next .wp-block-button, .answer-next .wp-block-button__link { width: 100%; }
  .not-found .wp-block-buttons, .not-found .wp-block-button { width: 100%; }
}
`;
const maquetteCss = readFileSync('assets/kekidi.css', 'utf8')
  .replaceAll("url('./", "url('./assets/")
  .replaceAll('.btn-primary', '.wp-element-button')
  .replaceAll(/\.btn\b/g, '.wp-element-button');
appendFileSync(`${theme}/style.css`, '\n/* Neutraliser les marges par défaut avant de charger celles de la maquette. */\n:root :where(.is-layout-flow, .is-layout-constrained, .wp-site-blocks) > * { margin-block: 0; }\n' + maquetteCss + '\n' + adapterCss);
rmSync(`${theme}/assets/wordpress.css`, { force: true });

const cdata = (value: string): string => `<![CDATA[${value.replaceAll(']]>', ']]]]><![CDATA[>')}]]>`;
const slug = (value: string): string => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
const categories = [...new Set(questions.map(q => q.category))];
const date = '2026-10-23 12:00:00';
const items = questions.map((q, i) => `<item>
<title>${escape(q.question)}</title><link>https://kekidi.great-site.net/reponses/${q.id}/</link>
<dc:creator>${cdata('kekidi')}</dc:creator><guid isPermaLink="false">https://kekidi.great-site.net/?p=${1001 + i}</guid>
<description></description><content:encoded>${cdata(response(q.answer, q.explanation, q.sources))}</content:encoded><excerpt:encoded>${cdata(q.answer)}</excerpt:encoded>
<wp:post_id>${1001 + i}</wp:post_id><wp:post_date>${date}</wp:post_date><wp:post_date_gmt>${date}</wp:post_date_gmt>
<wp:comment_status>closed</wp:comment_status><wp:ping_status>closed</wp:ping_status><wp:post_name>${q.id}</wp:post_name>
<wp:status>publish</wp:status><wp:post_parent>0</wp:post_parent><wp:menu_order>0</wp:menu_order><wp:post_type>post</wp:post_type><wp:post_password></wp:post_password><wp:is_sticky>0</wp:is_sticky>
<category domain="category" nicename="${slug(q.category)}">${cdata(q.category)}</category>
</item>`).join('\n');
writeFileSync('wordpress/reponses-kekidi.xml', `<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:excerpt="http://wordpress.org/export/1.2/excerpt/" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:wp="http://wordpress.org/export/1.2/">
<channel><title>Kekidi?!</title><link>https://kekidi.great-site.net</link><description>Les questions sont dans la rue. Les réponses, ici.</description><language>fr-FR</language>
<wp:wxr_version>1.2</wp:wxr_version><wp:base_site_url>https://kekidi.great-site.net</wp:base_site_url><wp:base_blog_url>https://kekidi.great-site.net</wp:base_blog_url>
<wp:author><wp:author_id>1</wp:author_id><wp:author_login>kekidi</wp:author_login><wp:author_email></wp:author_email><wp:author_display_name>Kekidi</wp:author_display_name><wp:author_first_name></wp:author_first_name><wp:author_last_name></wp:author_last_name></wp:author>
${categories.map((category, i) => `<wp:category><wp:term_id>${i + 101}</wp:term_id><wp:category_nicename>${slug(category)}</wp:category_nicename><wp:category_parent></wp:category_parent><wp:cat_name>${cdata(category)}</wp:cat_name></wp:category>`).join('\n')}
${items}</channel></rss>\n`);
console.log(`Thème créé dans ${resolve(theme)} ; ${questions.length} réponses exportées dans wordpress/reponses-kekidi.xml.`);
