<?php
/**
 * Settings class for Location Data Collection plugin
 *
 * @package Nami_Location_Collection
 */

namespace Nami\LocationData\Admin;

use Nami\LocationData\Options\ApiSettings;

/**
 * Settings Class
 */
class SettingsPage {

	/**
	 * Admin page slug
	 *
	 * @var string
	 */
	const string ADMIN_PAGE_SLUG = 'nami-location-data-settings';

	/**
	 * Name of page hook
	 *
	 * @var string|null
	 */
	protected ?string $page_hook;

	/**
	 * Singleton instance
	 *
	 * @var SettingsPage|null
	 */
	protected static ?SettingsPage $instance = null;

	/**
	 * Get singleton instance
	 *
	 * @return SettingsPage
	 */
	public static function get_instance(): SettingsPage {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Initialize settings page
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
	}


	/**
	 * Load admin page
	 *
	 * @return void
	 */
	public function admin_menu(): void {
		$this->page_hook = add_options_page(
			esc_html__( 'Location Data Collection Settings', 'nami-location-collection' ),
			esc_html__( 'Location Data', 'nami-location-collection' ),
			'activate_plugins',
			self::ADMIN_PAGE_SLUG,
			[ $this, 'output_admin_page' ]
		);
	}


	/**
	 * Output admin page content
	 *
	 * @return void
	 */
	public function output_admin_page(): void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Location Data Collection Settings', 'nami-location-collection' ); ?></h1>
			<form method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>">
				<?php
				settings_fields( ApiSettings::OPTIONS_GROUP );
				do_settings_sections( ApiSettings::OPTIONS_GROUP );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
