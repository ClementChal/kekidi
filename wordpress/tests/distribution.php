<?php
/** Vérifie les sources et les ZIP versionnés via WP-CLI eval-file. */

$root = dirname( __DIR__, 2 );
$check = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		WP_CLI::error( $message );
	}
};
$check( class_exists( Inmarelibero\GitIgnoreChecker\GitIgnoreChecker::class ), 'Installer le package WP-CLI dist-archive avant de vérifier la distribution.' );
$check_blocks = static function ( string $content, string $label ) use ( $check ): void {
	$stack = array();
	preg_match_all( '/<!--\s*(\/?)wp:([\w\/-]+)(.*?)-->/s', $content, $matches, PREG_SET_ORDER );
	$check( count( $matches ) > 0, "Aucun bloc : $label" );
	$check( false === strpos( $content, 'wp:html' ), "Bloc HTML brut : $label" );
	foreach ( $matches as $match ) {
		$attributes = trim( $match[3] );
		if ( $match[1] ) {
			$check( array_pop( $stack ) === $match[2], "Bloc mal fermé : $label" );
			continue;
		}
		$standalone = '/' === substr( $attributes, -1 );
		$attributes = $standalone ? rtrim( substr( $attributes, 0, -1 ) ) : $attributes;
		if ( '' !== $attributes ) {
			json_decode( $attributes, true, 512, JSON_THROW_ON_ERROR );
		}
		if ( ! $standalone ) {
			$stack[] = $match[2];
		}
	}
	$check( array() === $stack, "Blocs ouverts : $label" );
};

$theme = $root . '/wordpress/kekidi';
foreach ( array( 'style.css', 'functions.php', 'theme.json', 'templates/index.html' ) as $file ) {
	$check( is_file( "$theme/$file" ), "Fichier du thème absent : $file" );
}
$config = json_decode( file_get_contents( "$theme/theme.json" ), true, 512, JSON_THROW_ON_ERROR );
$check( 3 === $config['version'], 'Version de theme.json attendue : 3' );
foreach ( $config['settings']['typography']['fontFamilies'] as $font ) {
	foreach ( $font['fontFace'] as $face ) {
		foreach ( $face['src'] as $src ) {
			$check( 0 === strpos( $src, 'file:./' ) && is_file( $theme . '/' . substr( $src, 7 ) ), "Police absente : $src" );
		}
	}
}
foreach ( array_merge( glob( "$theme/templates/*.html" ), glob( "$theme/parts/*.html" ), glob( "$theme/patterns/*.php" ) ) as $path ) {
	$content = file_get_contents( $path );
	$check_blocks( $content, $path );
	preg_match_all( '/"slug":"kekidi\/([\w-]+)"/', $content, $patterns );
	foreach ( $patterns[1] as $slug ) {
		$check( is_file( "$theme/patterns/$slug.php" ), "Composition absente : $slug" );
	}
	preg_match_all( '/wp:template-part\s+\{"slug":"([\w-]+)"/', $content, $parts );
	foreach ( $parts[1] as $slug ) {
		$check( is_file( "$theme/parts/$slug.html" ), "Partie absente : $slug" );
	}
}
$prefetch = file_get_contents( "$theme/assets/kekidi-prefetch.js" );
foreach ( array( 'IntersectionObserver', 'entry.intersectionRatio <= 0', "rootMargin: '0px'" ) as $fragment ) {
	$check( false !== strpos( $prefetch, $fragment ), "Préchargement modifié : $fragment" );
}
$check( false !== strpos( file_get_contents( "$theme/functions.php" ), 'kekidi-prefetch' ), 'Préchargement non enregistré' );
$check( false !== strpos( file_get_contents( "$theme/templates/front-page.html" ), '"slug":"kekidi/accueil"' ), 'Composition de l’accueil absente' );
$check( false === strpos( file_get_contents( "$theme/patterns/accueil.php" ), '"templateLock":"contentOnly"' ), 'Accueil verrouillé de manière incompatible' );
$contact = get_file_data( $root . '/wordpress/kekidi-contact/kekidi-contact.php', array( 'requires' => 'Requires Plugins' ) );
$check( 'forminator' === $contact['requires'], 'Dépendance Forminator absente' );
$role = file_get_contents( $root . '/wordpress/kekidi-super-editeur/kekidi-super-editeur.php' );
foreach ( array( 'view_koko_analytics', 'edit_theme_options' ) as $capability ) {
	$check( false !== strpos( $role, $capability ), "Capacité absente : $capability" );
}

$questions = json_decode( file_get_contents( __DIR__ . '/fixtures/reponses.json' ), true, 512, JSON_THROW_ON_ERROR );
$xml = simplexml_load_file( $root . '/wordpress/reponses-kekidi.xml' );
$check( false !== $xml && count( $xml->channel->item ) === count( $questions ), 'Export initial incomplet' );
$index = 0;
foreach ( $xml->channel->item as $item ) {
	$question = $questions[ $index++ ];
	$wp = $item->children( 'http://wordpress.org/export/1.2/' );
	$check( (string) $item->title === $question['question'] && (string) $wp->post_name === $question['id'], 'Titre ou slug initial modifié' );
	$check( 'post' === (string) $wp->post_type && 'publish' === (string) $wp->status && 'closed' === (string) $wp->comment_status, 'État initial invalide' );
	$content = (string) $item->children( 'http://purl.org/rss/1.0/modules/content/' )->encoded;
	$check_blocks( $content, $question['id'] );
	$check( ( false !== strpos( $content, '"className":"sources"' ) ) === ( count( $question['sources'] ) > 0 ), 'Sources initiales incohérentes' );
	$check( false === strpos( $content, 'Écrire ici' ), 'Contenu initial non renseigné' );
}

foreach ( array( 'kekidi', 'kekidi-contact', 'kekidi-quiz', 'kekidi-scrutins', 'kekidi-super-editeur' ) as $slug ) {
	$directory = "$root/wordpress/$slug";
	$main = 'kekidi' === $slug ? 'style.css' : "$slug.php";
	$required = array( $main );
	if ( 'kekidi' === $slug ) {
		$required = array_merge( $required, array( 'functions.php', 'theme.json', 'templates/index.html', 'assets/daisyui.css', 'assets/kekidi-prefetch.js', 'assets/dm-sans-LICENSE.txt', 'assets/nunito-LICENSE.txt' ) );
		foreach ( $config['settings']['typography']['fontFamilies'] as $font ) {
			foreach ( $font['fontFace'] as $face ) {
				foreach ( $face['src'] as $src ) {
					$required[] = substr( $src, 7 );
				}
			}
		}
	}
	if ( in_array( $slug, array( 'kekidi-quiz', 'kekidi-scrutins' ), true ) ) {
		$required = array_merge( $required, array( 'block/block.json', 'block/render.php', 'block/editor.js', 'block/editor.asset.php', 'block/view.js', 'block/view.asset.php', 'vendor/confetti-2.2.0.min.js', 'vendor/confetti-LICENSE' ) );
	}
	if ( 'kekidi-scrutins' === $slug ) {
		$required = array_merge( $required, array( 'includes/storage.php', 'assets/logos/SOURCES.md' ) );
	}
	if ( 'kekidi-contact' === $slug ) {
		$required = array_merge( $required, array( 'form.php', 'patterns/contact.php', 'view.js' ) );
	}
	foreach ( $required as $file ) {
		$check( is_file( "$directory/$file" ), "Fichier requis absent : $slug/$file" );
	}
	$header = get_file_data( "$directory/$main", array( 'version' => 'Version' ) );
	$check( 1 === preg_match( '/^\d+\.\d+\.\d+$/D', $header['version'] ), "Version invalide : $slug" );
	$archive = "$root/dist/$slug-{$header['version']}.zip";
	$check( is_file( $archive ), "Archive versionnée absente : $archive" );
	$zip = new ZipArchive();
	$check( true === $zip->open( $archive, ZipArchive::CHECKCONS ), "Archive invalide : $archive" );
	for ( $entry = 0; $entry < $zip->numFiles; $entry++ ) {
		$name = $zip->getNameIndex( $entry );
		$check( 0 === strpos( $name, "$slug/" ) && false === strpos( $name, '..' ), "Racine ZIP incorrecte : $name" );
		$check( ! preg_match( '~(?:^|/)(?:tests|node_modules|backups|\.git)(?:/|$)|(?:^|/)(?:\.env(?:\.[^/]*)?|\.distignore|README\.md)$~', $name ), "Fichier de développement distribué : $name" );
	}
	$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $directory, FilesystemIterator::SKIP_DOTS ) );
	$ignore = new Inmarelibero\GitIgnoreChecker\GitIgnoreChecker( $directory, '.distignore' );
	foreach ( $files as $file ) {
		if ( ! $file->isFile() ) {
			continue;
		}
		$relative = substr( $file->getPathname(), strlen( $directory ) + 1 );
		if ( $ignore->isPathIgnored( '/' . $relative ) && ! in_array( $relative, $required, true ) ) {
			continue;
		}
		$stored = $zip->getFromName( "$slug/$relative" );
		$check( false !== $stored && hash( 'sha256', $stored ) === hash_file( 'sha256', $file->getPathname() ), "Fichier absent ou différent dans le ZIP : $slug/$relative" );
	}
	$zip->close();
}
WP_CLI::success( 'Sources, blocs, XML, dépendances et cinq archives versionnées vérifiés.' );
