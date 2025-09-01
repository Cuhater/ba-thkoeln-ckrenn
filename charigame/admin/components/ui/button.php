<?php
/**
 * Button Component
 * Shadcn UI Button implementation for PHP
 */

if ( ! function_exists( 'shadcn_button' ) ) {
	/**
	 * Renders a button.
	 *
	 * @param array $args Array of arguments for the button.
	 *
	 * @return string
	 */
	function shadcn_button( $args = array() ) {
		$defaults = array(
			'text'       => 'Button',
			'type'       => 'button',
			'name'       => '',
			'id'         => '',
			'value'      => '',
			'variant'    => 'default',
			'size'       => 'default',
			'class'      => '',
			'attributes' => array(),
		);

		$args            = array_merge( $defaults, $args );
		$variant_classes = array(
			'default'     => 'shadcn-btn-primary',
			'destructive' => 'shadcn-btn-destructive',
			'outline'     => 'shadcn-border shadcn-border-input shadcn-bg-background hover:shadcn-bg-accent hover:shadcn-text-accent-foreground',
			'secondary'   => 'shadcn-btn-secondary',
			'ghost'       => 'hover:shadcn-bg-accent hover:shadcn-text-accent-foreground',
			'link'        => 'shadcn-text-primary shadcn-underline-offset-4 hover:shadcn-underline',
		);

		$size_classes = array(
			'default' => 'shadcn-h-10 shadcn-px-4 shadcn-py-2',
			'sm'      => 'shadcn-h-9 shadcn-rounded-md shadcn-px-3',
			'lg'      => 'shadcn-h-11 shadcn-rounded-md shadcn-px-8',
			'icon'    => 'shadcn-h-10 shadcn-w-10',
		);

		$base_classes = 'shadcn-btn shadcn-ring-offset-background focus-visible:shadcn-outline-none focus-visible:shadcn-ring-2 focus-visible:shadcn-ring-ring focus-visible:shadcn-ring-offset-2 disabled:shadcn-pointer-events-none disabled:shadcn-opacity-50';

		$variant_class = isset( $variant_classes[ $args['variant'] ] ) ? $variant_classes[ $args['variant'] ] : $variant_classes['default'];
		$size_class    = isset( $size_classes[ $args['size'] ] ) ? $size_classes[ $args['size'] ] : $size_classes['default'];

		$class = $base_classes . ' ' . $variant_class . ' ' . $size_class;

		if ( ! empty( $args['class'] ) ) {
			$class .= ' ' . $args['class'];
		}

		$attributes = '';
		if ( ! empty( $args['id'] ) ) {
			$attributes .= ' id="' . esc_attr( $args['id'] ) . '"';
		}

		if ( ! empty( $args['name'] ) ) {
			$attributes .= ' name="' . esc_attr( $args['name'] ) . '"';
		}

		if ( ! empty( $args['value'] ) ) {
			$attributes .= ' value="' . esc_attr( $args['value'] ) . '"';
		}

		foreach ( $args['attributes'] as $attr => $value ) {
			$attributes .= ' ' . esc_attr( $attr ) . '="' . esc_attr( $value ) . '"';
		}

		return '<button type="' . esc_attr( $args['type'] ) . '" class="' . esc_attr( $class ) . '"' . $attributes . '>' . esc_html( $args['text'] ) . '</button>';
	}
}
