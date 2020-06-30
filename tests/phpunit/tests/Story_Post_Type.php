<?php
/**
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Web_Stories\Tests;

class Story_Post_Type extends \WP_UnitTestCase {

	use Private_Access;

	/**
	 * Admin user for test.
	 *
	 * @var int
	 */
	protected static $admin_id;

	/**
	 * Subscriber user for test.
	 *
	 * @var int
	 */
	protected static $subscriber_id;

	/**
	 * Story id.
	 *
	 * @var int
	 */
	protected static $story_id;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$admin_id      = $factory->user->create(
			[ 'role' => 'administrator' ]
		);
		self::$subscriber_id = $factory->user->create(
			[ 'role' => 'subscriber' ]
		);

		self::$story_id = $factory->post->create(
			[
				'post_type'    => \Google\Web_Stories\Story_Post_Type::POST_TYPE_SLUG,
				'post_title'   => 'Example title',
				'post_status'  => 'publish',
				'post_content' => 'Example content',
				'post_author'  => self::$admin_id,
			]
		);
	}

	public function setUp() {
		parent::setUp();

		do_action( 'init' );

		// Registered during init.
		unregister_block_type( 'web-stories/embed' );
	}

	public function test_get_editor_settings_admin() {
		wp_set_current_user( self::$admin_id );
		$post_type = new \Google\Web_Stories\Story_Post_Type();
		$results   = $post_type->get_editor_settings();
		$this->assertTrue( $results['config']['capabilities']['hasUploadMediaAction'] );
	}

	public function test_get_editor_settings_sub() {
		wp_set_current_user( self::$subscriber_id );
		$post_type = new \Google\Web_Stories\Story_Post_Type();
		$results   = $post_type->get_editor_settings();
		$this->assertFalse( $results['config']['capabilities']['hasUploadMediaAction'] );
	}

	public function test_filter_rest_collection_params() {
		$query_params = [
			'foo',
			'orderby' => [
				'enum' => [],
			],
		];

		$post_type        = get_post_type_object( \Google\Web_Stories\Story_Post_Type::POST_TYPE_SLUG );
		$post_type_object = new \Google\Web_Stories\Story_Post_Type();
		$filtered_params  = $post_type_object->filter_rest_collection_params( $query_params, $post_type );
		$this->assertEquals(
			$filtered_params,
			[
				'foo',
				'orderby' => [
					'enum' => [ 'story_author' ],
				],
			]
		);
	}

	public function test_filter_rest_collection_params_incorrect_post_type() {
		$query_params = [
			'foo',
			'orderby' => [
				'enum' => [],
			],
		];

		$post_type        = new \stdClass();
		$post_type->name  = 'post';
		$post_type_object = new \Google\Web_Stories\Story_Post_Type();
		$filtered_params  = $post_type_object->filter_rest_collection_params( $query_params, $post_type );
		$this->assertEquals( $filtered_params, $query_params );
	}

	public function test_get_post_type_icon() {
		$post_type_object = new \Google\Web_Stories\Story_Post_Type();
		$valid            = $this->call_private_method( $post_type_object, 'get_post_type_icon' );
		$this->assertContains( 'data:image/svg+xml;base64', $valid );
	}

	/**
	 * @covers \Google\Web_Stories\Story_Post_Type::admin_enqueue_scripts
	 */
	public function test_admin_enqueue_scripts() {
		$post_type_object = new \Google\Web_Stories\Story_Post_Type();
		set_current_screen( 'post.php' );
		get_current_screen()->post_type = \Google\Web_Stories\Story_Post_Type::POST_TYPE_SLUG;
		get_current_screen()->base      = 'post';
		$post_type_object->admin_enqueue_scripts( 'post.php' );
		$this->assertTrue( wp_script_is( \Google\Web_Stories\Story_Post_Type::WEB_STORIES_SCRIPT_HANDLE, 'registered' ) );
		$this->assertTrue( wp_style_is( \Google\Web_Stories\Story_Post_Type::WEB_STORIES_SCRIPT_HANDLE, 'registered' ) );
	}

	/**
	 * @covers \Google\Web_Stories\Story_Post_Type::filter_site_kit_gtag_opt
	 */
	public function test_filter_site_kit_gtag_opt() {
		global $wp_query;
		$wp_query->is_singular    = true;
		$wp_query->queried_object = get_post( self::$story_id );
		$post_type_object         = new \Google\Web_Stories\Story_Post_Type();
		$gtag                     = [
			'vars'     => [
				'gtag_id' => 'hello',
			],
			'triggers' => [],
		];
		$result                   = $post_type_object->filter_site_kit_gtag_opt( $gtag );

		$this->assertArrayHasKey( 'storyProgress', $result['triggers'] );
		$this->assertArrayHasKey( 'storyEnd', $result['triggers'] );
		$this->assertSame( 'Example title', $result['triggers']['storyProgress']['vars']['event_category'] );
		$this->assertSame( 'Example title', $result['triggers']['storyEnd']['vars']['event_category'] );

		unset( $wp_query->queried_object );
	}

	/**
	 * @covers \Google\Web_Stories\Story_Post_Type::filter_use_block_editor_for_post_type
	 */
	public function test_filter_use_block_editor_for_post_type() {
		$post_type_object = new \Google\Web_Stories\Story_Post_Type();
		$use_block_editor = $post_type_object->filter_use_block_editor_for_post_type( true, $post_type_object::POST_TYPE_SLUG );
		$this->assertFalse( $use_block_editor );
	}

	/**
	 * @covers \Google\Web_Stories\Story_Post_Type::skip_amp
	 */
	public function test_skip_amp() {
		$post_type_object = new \Google\Web_Stories\Story_Post_Type();
		$skip_amp         = $post_type_object->skip_amp( true, get_post( self::$story_id ) );
		$this->assertTrue( $skip_amp );
	}
}
