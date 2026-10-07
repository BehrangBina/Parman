<?php
/**
 * Theme bootstrap: registers every module with WordPress.
 *
 * @package IMP
 */

namespace IMP\Core;

defined( 'ABSPATH' ) || exit;

/**
 * One list of modules, so it is obvious what the theme does and in which order it hooks in.
 */
final class Theme {

	/**
	 * Modules with a static register() method.
	 *
	 * @var string[]
	 */
	const MODULES = array(
		Assets::class,
		Layout::class,
		\IMP\Routing\Page_Router::class,
		\IMP\Admin\Issue_Post_Type::class,
		\IMP\Admin\Pdf_Meta_Field::class,
		\IMP\Services\Pdf_Download::class,
		\IMP\Services\Contact_Mailer::class,
		\IMP\Integrations\Fluent_Forms::class,
	);

	/**
	 * Register all modules. A module that fails to load is logged and skipped, so one broken
	 * module cannot take the whole site down.
	 */
	public static function boot() {
		foreach ( self::MODULES as $module ) {
			try {
				$module::register();
			} catch ( \Throwable $error ) {
				Logger::error( 'Theme::boot', 'Module failed to register', array( 'module' => $module, 'error' => $error->getMessage() ) );
			}
		}
	}
}
