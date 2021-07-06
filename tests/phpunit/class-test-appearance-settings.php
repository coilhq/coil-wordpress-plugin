<?php
/**
 * Test.
 */
namespace Coil\Tests;

use Coil\Admin;
use Coil\Settings;
use WP_UnitTestCase;

/**
 * Testing the custom appearance settings.
 */
class Test_Appearance_Settings extends WP_UnitTestCase {

	/**
	 * Testing if the padlock icon shows next to geted post titles by default.
	 *
	 * @return void
	 */
	public function test_if_default_padlock_display_is_enabled() :  void {

		$padlock_setting = Admin\get_appearance_settings( 'coil_title_padlock' );

		$this->assertSame( true, $padlock_setting );
	}

	/**
	 * Testing if the padlock icon setting is retrieved correctly from the wp_options table.
	 *
	 * @return void
	 */
	public function test_if_the_padlock_display_setting_is_retrieved_successfully() :  void {

		$padlock_display = [ 'coil_title_padlock' => false ];
		update_option( 'coil_appearance_settings_group', $padlock_display );

		$padlock_setting = Admin\get_appearance_settings( 'coil_title_padlock' );

		$this->assertSame( false, $padlock_setting );

		$padlock_display = [ 'coil_title_padlock' => true ];
		update_option( 'coil_appearance_settings_group', $padlock_display );

		$padlock_setting = Admin\get_appearance_settings( 'coil_title_padlock' );

		$this->assertSame( true, $padlock_setting );
	}

	/**
	 * Testing if the donation bar footer shows by default.
	 *
	 * @return void
	 */
	public function test_if_default_donation_bar_display_is_enabled() :  void {

		$default_appearance_settings = Admin\get_appearance_settings( 'coil_show_donation_bar' );

		$this->assertSame( true, $default_appearance_settings );
	}

	/**
	 * Testing if the donation bar display setting is retrieved correctly from the wp_options table.
	 *
	 * @return void
	 */
	public function test_if_the_donation_bar_display_setting_is_retrieved_successfully() :  void {

		$donation_bar_display = [ 'coil_show_donation_bar' => false ];
		update_option( 'coil_appearance_settings_group', $donation_bar_display );

		$donation_bar_settings = Admin\get_appearance_settings( 'coil_show_donation_bar' );

		$this->assertSame( false, $donation_bar_settings );

		$donation_bar_display = [ 'coil_show_donation_bar' => true ];
		update_option( 'coil_appearance_settings_group', $donation_bar_display );

		$donation_bar_settings = Admin\get_appearance_settings( 'coil_show_donation_bar' );

		$this->assertSame( true, $donation_bar_settings );
	}

	/**
	 * Testing if a user has donation bar and padlock display settings which they saved in the customizer that they are migrated successfully to the wp_options table
	 * In the case where both settings had been set in the customizer and no settings had been added to the settings panel.
	 *
	 * @return void
	 */
	public function test_transfer_of_appearance_settings_from_customizer_when_settings_disabled() :  void {

		// Testing when both settings are set to false
		// Adding custom appearance settings to the theme_mod
		set_theme_mod( 'coil_show_donation_bar', false );
		set_theme_mod( 'coil_title_padlock', false );

		// Transferrng settings to the wp_options table
		Settings\transfer_customizer_appearance_settings();

		// Creating an array of the appearance settings that were retrieved from the wp_options table.
		$appearance_settings = [
			'coil_show_donation_bar' => Admin\get_appearance_settings( 'coil_show_donation_bar' ),
			'coil_title_padlock'     => Admin\get_appearance_settings( 'coil_title_padlock' ),
		];

		// Checking that all appearance settings that were retrieved are correct
		$this->assertSame( false, $appearance_settings['coil_show_donation_bar'] );
		$this->assertSame( false, $appearance_settings['coil_title_padlock'] );

		// Checking that the theme_mod appearance settings have been removed
		$this->assertFalse( get_theme_mod( 'coil_show_donation_bar' ) );
		$this->assertFalse( get_theme_mod( 'coil_title_padlock' ) );
	}

	/**
	 * Testing if a user has donation bar and padlock display settings which they saved in the customizer that they are migrated successfully to the wp_options table
	 * In the case where both settings had been set in the customizer and no settings had been added to the settings panel.
	 *
	 * @return void
	 */
	public function test_transfer_of_appearance_settings_from_customizer_when_settings_enabled() :  void {

		// Adding custom appearance settings to the theme_mod
		set_theme_mod( 'coil_show_donation_bar', true );
		set_theme_mod( 'coil_title_padlock', true );

		// Transferrng settings to the wp_options table
		Settings\transfer_customizer_appearance_settings();

		// Creating an array of the appearance settings that were retrieved from the wp_options table.
		$appearance_settings = [
			'coil_show_donation_bar' => Admin\get_appearance_settings( 'coil_show_donation_bar' ),
			'coil_title_padlock'     => Admin\get_appearance_settings( 'coil_title_padlock' ),
		];

		// Checking that all appearance settings that were retrieved are correct
		$this->assertSame( true, $appearance_settings['coil_show_donation_bar'] );
		$this->assertSame( true, $appearance_settings['coil_title_padlock'] );

		// Checking that the theme_mod appearance settings have been removed
		$this->assertFalse( get_theme_mod( 'coil_show_donation_bar' ) );
		$this->assertFalse( get_theme_mod( 'coil_title_padlock' ) );
	}

	/**
	 * Testing if a user has donation bar and padlock display settings which they saved in the customizer that they are migrated successfully to the wp_options table
	 * In the case where both settings had been set in the customizer and no settings had been added to the settings panel.
	 *
	 * @return void
	 */
	public function test_transfer_of_appearance_settings_from_customizer_when_settings_are_mixed() :  void {

		// Testing when one setting is set to true and the other to false
		// Adding custom appearance settings to the theme_mod
		set_theme_mod( 'coil_show_donation_bar', true );
		set_theme_mod( 'coil_title_padlock', false );

		// Transferrng settings to the wp_options table
		Settings\transfer_customizer_appearance_settings();

		// Creating an array of the appearance settings that were retrieved from the wp_options table.
		$appearance_settings = [
			'coil_show_donation_bar' => Admin\get_appearance_settings( 'coil_show_donation_bar' ),
			'coil_title_padlock'     => Admin\get_appearance_settings( 'coil_title_padlock' ),
		];

		// Checking that all appearance settings that were retrieved are correct
		$this->assertSame( true, $appearance_settings['coil_show_donation_bar'] );
		$this->assertSame( false, $appearance_settings['coil_title_padlock'] );

		// Checking that the theme_mod appearance settings have been removed
		$this->assertFalse( get_theme_mod( 'coil_show_donation_bar' ) );
		$this->assertFalse( get_theme_mod( 'coil_title_padlock' ) );
	}

	/**
	 * Testing if a user has donation bar and padlock display settings which they saved in the customizer that they are migrated successfully to the wp_options table
	 * In cases where some settings are saved to the customizer and others have been saved to the settings panel.
	 *
	 * @return void
	 */
	public function test_transfer_of_appearance_settings_from_customizer_where_settings_have_been_saved_in_both_locations() :  void {

		// Adding custom appearance settings to the theme_mod
		set_theme_mod( 'coil_show_donation_bar', false );
		$options                       = get_option( 'coil_appearance_settings_group', [] );
		$options['coil_title_padlock'] = false;
		update_option( 'coil_appearance_settings_group', $options );

		// Transferrng settings to the wp_options table
		Settings\transfer_customizer_appearance_settings();

		// Creating an array of the appearance settings that were retrieved from the wp_options table.
		$appearance_settings = [
			'coil_show_donation_bar' => Admin\get_appearance_settings( 'coil_show_donation_bar' ),
			'coil_title_padlock'     => Admin\get_appearance_settings( 'coil_title_padlock' ),
		];

		// Checking that all appearance settings that were retrieved are correct
		$this->assertSame( false, $appearance_settings['coil_show_donation_bar'] );
		$this->assertSame( false, $appearance_settings['coil_title_padlock'] );

		// Checking that the theme_mod appearance settings have been removed
		$this->assertFalse( get_theme_mod( 'coil_show_donation_bar' ) );
		$this->assertFalse( get_theme_mod( 'coil_title_padlock' ) );
	}

	/**
	 * Testing if the CTA box's default color theme is set to 'light'.
	 *
	 * @return void
	 */
	public function test_if_default_message_theme_is_light() {

		$theme_setting = Admin\get_appearance_settings( 'coil_message_color_theme' );

		$this->assertSame( 'light', $theme_setting );
	}

	/**
	 * Testing if the CTA box's default color theme is retrieved correctly from the wp_options table.
	 *
	 * @return void
	 */
	public function test_if_the_color_theme_setting_is_retrieved_successfully() {

		$dark_color_theme = [ 'coil_message_color_theme' => 'dark' ];
		update_option( 'coil_appearance_settings_group', $dark_color_theme );

		$retrieved_color_theme = Admin\get_appearance_settings( 'coil_message_color_theme' );

		$this->assertSame( $dark_color_theme['coil_message_color_theme'], $retrieved_color_theme );

		$light_color_theme = [ 'coil_message_color_theme' => 'light' ];
		update_option( 'coil_appearance_settings_group', $light_color_theme );

		$retrieved_color_theme = Admin\get_appearance_settings( 'coil_message_color_theme' );

		$this->assertSame( $light_color_theme['coil_message_color_theme'], $retrieved_color_theme );

	}

	/**
	 * Testing if the CTA box's default font is set to false.
	 *
	 * @return void
	 */
	public function test_if_default_theme_font_is_false() {

		$default_font = Admin\get_appearance_settings( 'coil_message_font' );

		$this->assertSame( false, $default_font );
	}

	/**
	 * Testing if the CTA box's font selection is retrieved correctly from the wp_options table.
	 *
	 * @return void
	 */
	public function test_if_the_theme_font_setting_is_retrieved_successfully() {

		$default_font = [ 'coil_message_font' => false ];
		update_option( 'coil_appearance_settings_group', $default_font );

		$retrieved_font = Admin\get_appearance_settings( 'coil_message_font' );

		$this->assertSame( $default_font['coil_message_font'], $retrieved_font );

		$theme_based_font = [ 'coil_message_font' => true ];
		update_option( 'coil_appearance_settings_group', $theme_based_font );

		$retrieved_color_theme = Admin\get_appearance_settings( 'coil_message_font' );

		$this->assertSame( $theme_based_font['coil_message_font'], $retrieved_color_theme );

	}

	/**
	 * Testing if the CTA box's Coil branding selection is set to false.
	 *
	 * @return void
	 */
	public function test_if_default_message_branding_option_is_false() {

		$branding_setting = Admin\get_appearance_settings( 'coil_message_branding' );

		$this->assertSame( false, $branding_setting );
	}

	/**
	 * Testing if the CTA box's Coil branding selection is retrieved correctly from the wp_options table.
	 *
	 * @return void
	 */
	public function test_if_the_message_branding_option_is_retrieved_successfully() {

		$branding_unchecked = [ 'coil_message_branding' => false ];
		update_option( 'coil_appearance_settings_group', $branding_unchecked );

		$retrieved_branding = Admin\get_appearance_settings( 'coil_message_branding' );

		$this->assertSame( $branding_unchecked['coil_message_branding'], $retrieved_branding );

		$branding_checked = [ 'coil_message_branding' => true ];
		update_option( 'coil_appearance_settings_group', $branding_checked );

		$retrieved_branding = Admin\get_appearance_settings( 'coil_message_branding' );

		$this->assertSame( $branding_checked['coil_message_branding'], $retrieved_branding );

	}
}
