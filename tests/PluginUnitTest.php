<?php
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

class Test_WP_Google_Preferred_Source extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function test_sanitize_options() {
		$plugin = new WP_Google_Preferred_Source();
		
		Functions\expect( 'esc_url_raw' )
			->once()
			->with( 'https://news.google.com/publications/test' )
			->andReturn( 'https://news.google.com/publications/test' );

		$input = array(
			'google_news_url' => 'https://news.google.com/publications/test',
			'auto_append'     => '1',
		);

		$sanitized = $plugin->sanitize_options( $input );

		$this->assertEquals( 'https://news.google.com/publications/test', $sanitized['google_news_url'] );
		$this->assertEquals( '1', $sanitized['auto_append'] );
	}

	public function test_render_shortcode_empty_url() {
		$plugin = new WP_Google_Preferred_Source();

		Functions\expect( 'get_option' )
			->once()
			->with( 'wpgps_settings' )
			->andReturn( array() );

		$output = $plugin->render_shortcode( array() );
		$this->assertEquals( '', $output );
	}

	public function test_render_shortcode_with_url() {
		$plugin = new WP_Google_Preferred_Source();

		Functions\expect( 'get_option' )
			->once()
			->with( 'wpgps_settings' )
			->andReturn( array( 'google_news_url' => 'https://news.google.com/publications/test' ) );

		Functions\expect( 'esc_url' )
			->once()
			->andReturn( 'https://news.google.com/publications/test' );

		$output = $plugin->render_shortcode( array() );
		$this->assertStringContainsString( 'wpgps-container', $output );
		$this->assertStringContainsString( 'https://news.google.com/publications/test', $output );
	}
}
