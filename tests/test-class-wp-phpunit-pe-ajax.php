<?php

class WP_PHPUnit_PE_Ajax_Test extends WP_Ajax_UnitTestCase {
	private $nonce;

	public function setUp() {
		/**
		 * Always call parent::setUp to avoid policy errors.
		 */
		parent::setUp();
		$this->_setRole( 'administrator' );

		$this->nonce = wp_create_nonce( 'wp_phpunit_ajax_update_email' );
		new WP_PHPUnit_PE_AJAX();
	}

	public function test_if_sent_data_is_empty() {
		$_POST['_wpnonce'] = $this->nonce;
		$_POST['data']     = wp_json_encode( array( 'user' => array() ) );

		try {
			$this->_handleAjax( 'wp_phpunit_update_email' );
		} catch ( WPAjaxDieStopException|WPAjaxDieContinueException $e ) {}

		$this->assertTrue( isset( $e ) );

		$response = json_decode( $this->_last_response, true );

		$this->assertFalse( $response['success'] );
		$this->assertSame( 'There is no data to save.', $response['data']['message'] );
	}

	public function test_if_email_to_update_is_invalid() {
		$_POST['_wpnonce'] = $this->nonce;
		$_POST['data']     = wp_json_encode( array(
			'user' => array( 'email' => 'hola.org' ),
		) );

		try {
			$this->_handleAjax( 'wp_phpunit_update_email' );
		} catch ( WPAjaxDieStopException|WPAjaxDieContinueException $e ) {}

		$this->assertTrue( isset( $e ) );

		$response = json_decode( $this->_last_response, true );

		$this->assertFalse( $response['success'] );
		$this->assertSame( 'The inserted email is invalid.', $response['data']['message'] );
	}

	public function test_update_email_success() {
		$valid_email       = 'hola@example.org';
		$_POST['_wpnonce'] = $this->nonce;
		$_POST['data']     = wp_json_encode( array(
			'user' => array( 'email' => $valid_email ),
		) );

		try {
			$this->_handleAjax( 'wp_phpunit_update_email' );
		} catch ( WPAjaxDieStopException|WPAjaxDieContinueException $e ) {}

		$this->assertTrue( isset( $e ) );

		$response  = json_decode( $this->_last_response, true );
		$user_data = get_option( 'wp_phpunit_tests_option', array() );

		$this->assertNotEmpty( $user_data );
		$this->assertArrayHasKey( 'email', $user_data );
		$this->assertSame( $valid_email, $user_data['email'] );
		$this->assertTrue( $response['success'] );
		$this->assertSame( 'Option updated successfully.', $response['data']['message'] );
	}
}
