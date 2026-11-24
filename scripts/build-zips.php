<?php
/** Génère et vérifie la livraison avant de ranger les anciennes archives. */

$root = dirname( __DIR__ );
$dist = "$root/dist";
$backup = "$dist/backup";
$staging = "$dist/.build-" . wp_generate_uuid4();

if ( ! wp_mkdir_p( $dist ) ) {
	WP_CLI::error( "Impossible de créer $dist." );
}
$lock = fopen( "$dist/.build.lock", 'c' );
if ( ! $lock || ! flock( $lock, LOCK_EX ) ) {
	WP_CLI::error( "Impossible de verrouiller $dist." );
}

$run = static function ( string $command ): void {
	$result = WP_CLI::runcommand( $command, array( 'launch' => false, 'return' => 'all', 'exit_error' => false ) );
	if ( 0 !== $result->return_code ) {
		throw new RuntimeException( trim( $result->stderr ?: $result->stdout ) );
	}
	WP_CLI::log( rtrim( $result->stdout ) );
};

$archives = array();
$failure = null;
try {
	if ( ! wp_mkdir_p( $staging ) ) {
		throw new RuntimeException( "Impossible de créer $staging." );
	}
	foreach ( array( 'kekidi', 'kekidi-contact', 'kekidi-quiz', 'kekidi-scrutins', 'kekidi-super-editeur' ) as $slug ) {
		$source = "$root/wordpress/$slug";
		$main = 'kekidi' === $slug ? 'style.css' : "$slug.php";
		$version = get_file_data( "$source/$main", array( 'version' => 'Version' ) )['version'];
		if ( ! preg_match( '/^\d+\.\d+\.\d+$/D', $version ) ) {
			throw new RuntimeException( "Version invalide : $slug." );
		}
		$archives[ $slug ] = $version;
		$filename = "$slug-$version.zip";
		if ( is_file( "$dist/$filename" ) ) {
			if ( ! copy( "$dist/$filename", "$staging/$filename" ) ) {
				throw new RuntimeException( "Impossible de vérifier $filename." );
			}
			continue;
		}
		$run( 'dist-archive ' . escapeshellarg( $source ) . ' ' . escapeshellarg( "$staging/$filename" ) );
	}

	$run( 'eval-file ' . escapeshellarg( "$root/wordpress/tests/distribution.php" ) . ' ' . escapeshellarg( $staging ) );
	foreach ( $archives as $slug => $version ) {
		$filename = "$slug-$version.zip";
		if ( ! file_exists( "$dist/$filename" ) && ! rename( "$staging/$filename", "$dist/$filename" ) ) {
			throw new RuntimeException( "Impossible de livrer $filename." );
		}
	}

	foreach ( $archives as $slug => $version ) {
		foreach ( glob( "$dist/$slug-*.zip" ) as $path ) {
			$filename = basename( $path );
			if ( ! is_file( $path ) || ! preg_match( '/^' . preg_quote( $slug, '/' ) . '-(\d+\.\d+\.\d+)\.zip$/D', $filename, $match ) || ! version_compare( $match[1], $version, '<' ) ) {
				continue;
			}
			if ( ! wp_mkdir_p( $backup ) ) {
				throw new RuntimeException( "Impossible de créer $backup." );
			}
			$backup_name = wp_unique_filename( $backup, $filename );
			if ( ! rename( $path, "$backup/$backup_name" ) ) {
				throw new RuntimeException( "Impossible de sauvegarder $filename." );
			}
			WP_CLI::log( "Sauvegardé : backup/$backup_name" );
		}
	}
} catch ( RuntimeException $error ) {
	$failure = $error->getMessage();
} finally {
	if ( is_dir( $staging ) ) {
		foreach ( glob( "$staging/*.zip" ) as $path ) {
			unlink( $path );
		}
		rmdir( $staging );
	}
	fclose( $lock );
}

if ( null !== $failure ) {
	WP_CLI::error( $failure );
}
