<?php

// If this file is called directly, abort.
defined( 'ABSPATH' ) || die;

class StackonetSiteColorsCustomizer {

	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			add_action( 'customize_register', [ self::$instance, 'customize_register' ] );
		}

		return self::$instance;
	}

	/**
	 * @param WP_Customize_Manager $wp_customize
	 */
	public function customize_register( $wp_customize ) {
		$wp_customize->add_section( 'stackonet_colors', [
			'title'    => __( 'Stackonet Colors', 'stackonet-site-colors' ),
			'priority' => 20,
		] );

		$fields = [
			[
				'settings'    => 'shapla_primary_color',
				'label'       => __( 'Primary Color', 'stackonet-site-colors' ),
				'description' => __( 'A primary color is the color displayed most frequently across your site.', 'stackonet-site-colors' ),
				'default'     => '#00d1b2',
				'priority'    => 10,
			],
			[
				'settings'    => 'shapla_secondary_color',
				'label'       => __( 'Secondary Color', 'shapla' ),
				'description' => __( 'Color for Links, Actions buttons, Highlighting text', 'shapla' ),
				'default'     => '#6200ee',
				'priority'    => 20,
			],
			[
				'settings'    => 'shapla_surface_color',
				'label'       => __( 'Surface Color', 'shapla' ),
				'description' => __( 'Color for surfaces of components such as cards.', 'shapla' ),
				'default'     => '#ffffff',
				'priority'    => 30,
			],
			[
				'settings'    => 'shapla_error_color',
				'label'       => __( 'Error Color', 'shapla' ),
				'description' => __( 'Color for error in components.', 'shapla' ),
				'default'     => '#b00020',
				'priority'    => 40,
			]
		];

		foreach ( $fields as $field ) {
			$this->add_field( $wp_customize, $field );
		}
	}

	/**
	 * @param WP_Customize_Manager $wp_customize
	 * @param array $args
	 */
	public function add_field( $wp_customize, array $args ) {
		$wp_customize->add_setting( $args['settings'], array(
			'default'           => isset( $args['default'] ) ? $args['default'] : '',
			'type'              => 'theme_mod',
			'capability'        => 'edit_theme_options',
			'transport'         => 'refresh',
			'sanitize_callback' => 'sanitize_text_field',
		) );

		$wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, $args['settings'], array(
			'settings'    => $args['settings'],
			'label'       => $args['label'],
			'description' => isset( $args['description'] ) ? $args['description'] : '',
			'priority'    => isset( $args['priority'] ) ? $args['priority'] : 10,
			'section'     => 'stackonet_colors',
		) ) );
	}
}