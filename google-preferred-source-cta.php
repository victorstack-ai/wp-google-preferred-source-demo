<?php
/**
 * Plugin Name: Google Preferred Source CTA
 * Plugin URI:  https://github.com/victorstack-ai/google-preferred-source-cta
 * Description: Promotes Google News follow actions and highlights publisher-selected preferred sources.
 * Version:     1.0.0
 * Author:      VictorStack AI
 * License:     GPL-2.0-or-later
 * Text Domain: google-preferred-source-cta
 *
 * @package WP_Google_Preferred_Source
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP_Google_Preferred_Source
 *
 * Handles the Google Preferred Source CTA functionality.
 */
class WP_Google_Preferred_Source {

	/**
	 * Settings option name.
	 *
	 * @var string
	 */
	private $option_name = 'wpgps_settings';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_shortcode( 'google_preferred_source', array( $this, 'render_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_filter( 'the_content', array( $this, 'auto_append_to_content' ) );
	}

	/**
	 * Add admin menu.
	 */
	public function add_admin_menu() {
		add_options_page(
			'Google Preferred Source',
			'Google Preferred Source',
			'manage_options',
			'google-preferred-source-cta',
			array( $this, 'options_page_html' )
		);
	}

	/**
	 * Register settings.
	 */
	public function register_settings() {
		register_setting( $this->option_name, $this->option_name, array( 'sanitize_callback' => array( $this, 'sanitize_options' ) ) );

		add_settings_section(
			'wpgps_main_section',
			'Settings',
			null,
			'google-preferred-source-cta'
		);

		add_settings_field(
			'google_news_url',
			'Google News Publication URL',
			array( $this, 'google_news_url_callback' ),
			'google-preferred-source-cta',
			'wpgps_main_section'
		);

		add_settings_field(
			'auto_append',
			'Auto-append to Posts',
			array( $this, 'auto_append_callback' ),
			'google-preferred-source-cta',
			'wpgps_main_section'
		);

		add_settings_field(
			'preferred_sources',
			'Preferred Source URLs',
			array( $this, 'preferred_sources_callback' ),
			'google-preferred-source-cta',
			'wpgps_main_section'
		);
	}

	/**
	 * Sanitize options.
	 *
	 * @param array $input Input options.
	 * @return array Sanitized options.
	 */
	public function sanitize_options( $input ) {
		$new_input = array();

		if ( isset( $input['google_news_url'] ) ) {
			$new_input['google_news_url'] = esc_url_raw( $input['google_news_url'] );
		}

		$new_input['auto_append'] = isset( $input['auto_append'] ) ? '1' : '0';

		if ( isset( $input['preferred_sources'] ) ) {
			$new_input['preferred_sources'] = $this->sanitize_sources( $input['preferred_sources'] );
		}

		return $new_input;
	}

	/**
	 * Sanitize preferred source URLs from textarea input.
	 *
	 * @param string $raw_sources Newline-separated source URLs.
	 * @return array<string> Clean source URLs.
	 */
	public function sanitize_sources( $raw_sources ) {
		$raw_sources = is_string( $raw_sources ) ? $raw_sources : '';
		$lines       = array_map( 'trim', explode( PHP_EOL, $raw_sources ) );
		$clean       = array();

		foreach ( $lines as $line ) {
			if ( '' === $line ) {
				continue;
			}

			$sanitized = esc_url_raw( $line );
			if ( '' !== $sanitized ) {
				$clean[] = $sanitized;
			}
		}

		return array_values( array_unique( $clean ) );
	}

	/**
	 * Google News URL callback.
	 */
	public function google_news_url_callback() {
		$options = get_option( $this->option_name );
		$value   = isset( $options['google_news_url'] ) ? esc_attr( $options['google_news_url'] ) : '';
		echo '<input type="text" name="' . esc_attr( $this->option_name ) . '[google_news_url]" value="' . esc_attr( $value ) . '" class="regular-text" placeholder="https://news.google.com/publications/..." />';
		echo '<p class="description">Enter your Google News Publication URL. Users will be directed here to "Follow".</p>';
	}

	/**
	 * Auto append callback.
	 */
	public function auto_append_callback() {
		$options = get_option( $this->option_name );
		$checked = ( isset( $options['auto_append'] ) && '1' === $options['auto_append'] ) ? 'checked="checked"' : '';
		echo '<input type="checkbox" name="' . esc_attr( $this->option_name ) . '[auto_append]" value="1" ' . esc_attr( $checked ) . ' /> Append CTA to the bottom of all posts';
	}

	/**
	 * Preferred sources field callback.
	 */
	public function preferred_sources_callback() {
		$options = get_option( $this->option_name );
		$stored  = isset( $options['preferred_sources'] ) && is_array( $options['preferred_sources'] ) ? $options['preferred_sources'] : array();
		$value   = implode( PHP_EOL, $stored );

		echo '<textarea name="' . esc_attr( $this->option_name ) . '[preferred_sources]" rows="6" class="large-text code" placeholder="https://example.com/newsroom">' . esc_textarea( $value ) . '</textarea>';
		echo '<p class="description">One URL per line. These links will be shown as trusted sources below the follow button.</p>';
	}

	/**
	 * Options page HTML.
	 */
	public function options_page_html() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1>Google Preferred Source CTA</h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( $this->option_name );
				do_settings_sections( 'google-preferred-source-cta' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Enqueue styles.
	 */
	public function enqueue_styles() {
		wp_enqueue_style( 'wpgps-styles', plugins_url( 'assets/css/style.css', __FILE__ ), array(), '1.0.0' );
	}

	/**
	 * Render shortcode.
	 *
	 * @return string Shortcode output.
	 */
	public function render_shortcode() {
		$options = get_option( $this->option_name );
		$url     = isset( $options['google_news_url'] ) ? $options['google_news_url'] : '';

		if ( empty( $url ) ) {
			return '';
		}

		$preferred_sources = isset( $options['preferred_sources'] ) && is_array( $options['preferred_sources'] ) ? $options['preferred_sources'] : array();

		$output  = '<div class="wpgps-container">';
		$output .= '<div class="wpgps-box">';
		$output .= '<span class="wpgps-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.84z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg></span>';
		$output .= '<div class="wpgps-text">';
		$output .= '<strong>Never miss an update!</strong><br>';
		$output .= 'Follow us on Google News & set as Preferred Source.';
		$output .= '</div>';
		$output .= '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer" class="wpgps-button">Follow</a>';
		$output .= '</div>';

		if ( ! empty( $preferred_sources ) ) {
			$output .= '<div class="wpgps-preferred-sources"><strong>Preferred Sources</strong><ul>';

			foreach ( $preferred_sources as $source_url ) {
				$output .= '<li><a href="' . esc_url( $source_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $source_url ) . '</a></li>';
			}

			$output .= '</ul></div>';
		}

		$output .= '</div>';

		return $output;
	}

	/**
	 * Auto append to content.
	 *
	 * @param string $content Post content.
	 * @return string Modified content.
	 */
	public function auto_append_to_content( $content ) {
		if ( ! is_single() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		$options = get_option( $this->option_name );
		if ( isset( $options['auto_append'] ) && '1' === $options['auto_append'] ) {
			$content .= $this->render_shortcode();
		}

		return $content;
	}
}

new WP_Google_Preferred_Source();
