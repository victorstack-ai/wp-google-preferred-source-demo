<?php
use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

class Test_WP_Google_Preferred_Source extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Functions\when( 'add_action' )->justReturn( true );
		Functions\when( 'add_shortcode' )->justReturn( true );
		Functions\when( 'add_filter' )->justReturn( true );
		Functions\when( 'plugins_url' )->justReturn( 'https://example.com/assets/css/style.css' );
		Functions\when( 'wp_enqueue_style' )->justReturn( true );
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

		Functions\expect( 'esc_url_raw' )
			->once()
			->with( 'https://source-1.example/news' )
			->andReturn( 'https://source-1.example/news' );

		Functions\expect( 'esc_url_raw' )
			->once()
			->with( 'https://source-2.example/home' )
			->andReturn( 'https://source-2.example/home' );

		$input = array(
			'google_news_url' => 'https://news.google.com/publications/test',
			'auto_append'     => '1',
			'preferred_sources' => "https://source-1.example/news\nhttps://source-2.example/home",
		);

		$sanitized = $plugin->sanitize_options( $input );

		$this->assertEquals( 'https://news.google.com/publications/test', $sanitized['google_news_url'] );
		$this->assertEquals( '1', $sanitized['auto_append'] );
		$this->assertEquals(
			array(
				'https://source-1.example/news',
				'https://source-2.example/home',
			),
			$sanitized['preferred_sources']
		);
	}

	public function test_sanitize_sources_removes_empty_and_duplicates() {
		$plugin = new WP_Google_Preferred_Source();

		Functions\expect( 'esc_url_raw' )
			->times( 3 )
			->andReturnUsing(
				static function ( $url ) {
					return trim( $url );
				}
			);

		$clean = $plugin->sanitize_sources( "https://source.example/a\n\nhttps://source.example/a\nhttps://source.example/b" );

		$this->assertSame(
			array(
				'https://source.example/a',
				'https://source.example/b',
			),
			$clean
		);
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
			->andReturn(
				array(
					'google_news_url'    => 'https://news.google.com/publications/test',
					'preferred_sources' => array(
						'https://source-1.example/news',
						'https://source-2.example/home',
					),
				)
			);

		Functions\expect( 'esc_url' )
			->times( 3 )
			->andReturnUsing(
				static function ( $value ) {
					return $value;
				}
			);

		Functions\expect( 'esc_html' )
			->times( 2 )
			->andReturnUsing(
				static function ( $value ) {
					return $value;
				}
			);

		$output = $plugin->render_shortcode();
		$this->assertStringContainsString( 'wpgps-container', $output );
		$this->assertStringContainsString( 'https://news.google.com/publications/test', $output );
		$this->assertStringContainsString( 'Preferred Sources', $output );
		$this->assertStringContainsString( 'https://source-1.example/news', $output );
		$this->assertStringContainsString( 'https://source-2.example/home', $output );
	}
}
