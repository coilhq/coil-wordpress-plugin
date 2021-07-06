<?php
/**
 * Test.
 */
namespace Coil\Tests;

use Coil\Gating;
use WP_UnitTestCase;
use WP_UnitTest_Factory;

/**
 * Testing the custom monetization settings.
 */
class Test_Monetization_Settings extends WP_UnitTestCase {

	/**
	 * Basic posts for testing with.
	 *
	 * These have:
	 *
	 * - No post type-specific gating.
	 * - No taxonomy-specific gating.
	 *
	 * @var \WP_Post[] Standard post objects.
	 */
	protected static $basic_posts = [];

	/**
	 * Create fake data before tests run.
	 *
	 * @param WP_UnitTest_Factory $factory Helper that creates fake data.
	 *
	 * @return void
	 */
	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) : void {

		self::$basic_posts = [
			'no'                 => $factory->post->create_and_get(),
			'no-gating'          => $factory->post->create_and_get(),
			'gate-all'           => $factory->post->create_and_get(),
			'gate-tagged-blocks' => $factory->post->create_and_get(),
		];

		foreach ( self::$basic_posts as $gating_type => $post_obj ) {
			Gating\set_post_gating( $post_obj->ID, $gating_type );
		}
	}

	/**
	 * Delete fake data after tests run.
	 *
	 * @return void
	 */
	public static function wpTearDownAfterClass() : void {

		foreach ( self::$basic_posts as $_ => $post_obj ) {
			wp_delete_post( $post_obj->ID, true );
		}

		self::$basic_posts = [];
	}

	/**
	 * Test that gating provides the correct gating for basic posts.
	 *
	 * @return void
	 */
	public function test_that_basic_posts_have_correct_gating() :  void {

		foreach ( self::$basic_posts as $expected_gating_type => $post_obj ) {
			$retrieved_gating = Gating\get_post_gating( $post_obj->ID );

			$this->assertSame( $expected_gating_type, $retrieved_gating );
		}
	}

	/**
	 * Test that gating provides the correct gating for posts.
	 *
	 * @dataProvider get_post_meta_data_provider
	 *
	 * @param string $content_type         Type of gating to test. Either "basic", "taxonomy", "posttype".
	 * @param string $expected_gating_type See return values from Gating\get_post_gating().
	 *
	 * @return void
	 */
	public function test_that_posts_have_the_correct_gating( string $content_type, string $expected_gating_type ) :  void {

		$content_objs = "{$content_type}_posts";

		foreach ( self::$$content_objs as $expected_gating_type => $post_obj ) {
			$this->assertSame(
				$expected_gating_type,
				Gating\get_post_gating( $post_obj->ID ),
				"For {$content_type}, expected: {$expected_gating_type}/"
			);
		}
	}

	/**
	 * Testing if the content setings are retrieved correctly from the database.
	 *
	 * @return void
	 */
	public function test_if_content_settings_are_retrieved_successfully() :  void {

		foreach ( self::$basic_posts as $gating_type => $post_obj ) {
			// Accessing the database directly
			update_post_meta( $post_obj->ID, '_coil_monetize_post_status', $gating_type );
			if ( update_post_meta( $post_obj->ID, '_coil_monetize_post_status', $gating_type ) ) {
				add_post_meta( $post_obj->ID, '_coil_monetize_post_status', $gating_type );
			}

			$content_gating = Gating\get_post_gating( $post_obj->ID );

			$this->assertSame( $gating_type, $content_gating );
		}
	}

	/**
	 * Get data for testing post gating and titles.
	 *
	 * @link https://phpunit.readthedocs.io/en/8.4/writing-tests-for-phpunit.html#data-providers
	 *
	 * @return array
	 */
	public function get_post_meta_data_provider() : array {

		return [
			[
				'basic',
				'default',
			],
			[
				'basic',
				'no',
			],
			[
				'basic',
				'no-gating',
			],
			[
				'basic',
				'gate-all',
			],
			[
				'basic',
				'gate-tagged-blocks',
			],
		];
	}
}
