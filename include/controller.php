<?php
if ( function_exists( 'acf_add_local_field_group' ) ):

	acf_add_local_field_group( array(
		'key'                   => 'group_5c751447e8c22',
		'title'                 => 'Calendar',
		'fields'                => array(
			array(
				'key'               => 'field_5c75147e53881',
				'label'             => 'Date of the Event',
				'name'              => 'avec_date',
				'type'              => 'date_picker',
				'instructions'      => '',
				'required'          => 0,
				'conditional_logic' => 0,
				'wrapper'           => array(
					'width' => '50',
					'class' => '',
					'id'    => '',
				),
				'display_format'    => 'd.m.Y',
				'return_format'     => 'd.m.Y',
				'first_day'         => 1,
			),
			array(
				'key'               => 'field_5c7534c5649fd',
				'label'             => 'Location',
				'name'              => 'avec_location',
				'type'              => 'text',
				'instructions'      => '',
				'required'          => 0,
				'conditional_logic' => 0,
				'wrapper'           => array(
					'width' => '50',
					'class' => '',
					'id'    => '',
				),
				'default_value'     => '',
				'placeholder'       => '',
				'prepend'           => '',
				'append'            => '',
				'maxlength'         => '',
			),
			array(
				'key'               => 'field_5c7515bf37d87',
				'label'             => 'Decription of the Event',
				'name'              => 'avec_decription',
				'type'              => 'textarea',
				'instructions'      => '',
				'required'          => 0,
				'conditional_logic' => 0,
				'wrapper'           => array(
					'width' => '100',
					'class' => '',
					'id'    => '',
				),
				'default_value'     => '',
				'placeholder'       => '',
				'maxlength'         => '',
				'rows'              => 5,
				'new_lines'         => 'br',
			),
			array(
				'key'               => 'field_5c7516ef04fb4',
				'label'             => 'Link to the event review',
				'name'              => 'avec_summary',
				'type'              => 'url',
				'instructions'      => '',
				'required'          => 0,
				'conditional_logic' => 0,
				'wrapper'           => array(
					'width' => '100',
					'class' => '',
					'id'    => '',
				),
				'default_value'     => '',
				'placeholder'       => '',
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'event',
				),
			),
		),
		'menu_order'            => 0,
		'position'              => 'normal',
		'style'                 => 'default',
		'label_placement'       => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen'        => '',
		'active'                => 1,
		'description'           => '',
	) );

endif;