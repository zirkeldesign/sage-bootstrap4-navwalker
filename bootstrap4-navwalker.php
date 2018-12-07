<?php
/**
 * Class Name: Walker_Nav_Menu_BS4
 * GitHub URI: https://github.com/zirkeldesign/zd-wp-sage-bootstrap4-navwalker
 * Description: A custom WordPress nav walker class to implement the Bootstrap 4 navigation style in a custom theme using the WordPress built in menu manager.
 * Version: 1.4.0
 * Author: Daniel Sturm @dsturm
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

namespace Zirkeldesign\Bootstrap4NavWalker;

if ( ! class_exists( '\Walker_Nav_Menu' ) ) {
	return;
}

/**
 * Class: Walker_Nav_Menu
 */
class Walker_Nav_Menu extends \Walker_Nav_Menu {

	/**
	 * Start the level output.
	 *
	 * @see Walker::start_lvl()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of page. Used for padding.
	 * @param array  $args   Array with arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = [] ) {
		$indent  = str_repeat( "\t", $depth );
		$output .= PHP_EOL . "$indent<div role=\"menu\" class=\" dropdown-menu\">" . PHP_EOL;
	}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @see Walker::end_lvl()
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   An array of arguments (@see wp_nav_menu()).
	 */
	public function end_lvl( &$output, $depth = 0, $args = [] ) {
		$indent  = str_repeat( "\t", $depth );
		$output .= "$indent</div>" . PHP_EOL;
	}

	/**
	 * Start the element output.
	 *
	 * @see Walker::start_el()
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $object Menu item data object.
	 * @param int    $depth Depth of menu item. Used for padding.
	 * @param array  $args An array of arguments (@see wp_nav_menu()).
	 * @param int    $current_object_id Current item ID.
	 */
	public function start_el( &$output, $object, $depth = 0, $args = [], $current_object_id = 0 ) {
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		/**
		 * Dividers, Headers or Disabled
		 * =============================
		 * Determine whether the item is a Divider, Header, Disabled or regular
		 * menu item. To prevent errors we use the strcasecmp() function to so a
		 * comparison that is not case sensitive. The strcasecmp() function returns
		 * a 0 if the strings are equal.
		 */
		if ( 1 === $depth
			&& (
				0 === strcasecmp( $object->attr_title, 'divider' )
				|| 0 === strcasecmp( $object->title, 'divider' )
			)
		) {
			$output .= $indent . '<div class="dropdown-divider">';
		} elseif ( 1 === $depth
			&& false !== stripos( $object->attr_title, 'header' )
		) {
			$output .= $indent . '<h6 class="dropdown-header">' . esc_attr( $object->title );
		} else {
			$class_names = '';
			$value = '';
			$classes = empty( $object->classes ) ? [] : (array) $object->classes;

			$atts = [];

			$atts['title']  = ! empty( $object->title ) ? $object->title : '';
			$atts['target'] = ! empty( $object->target ) ? $object->target : '';
			$atts['rel']    = ! empty( $object->xfn ) ? $object->xfn : '';
			$atts['href']   = ! empty( $object->url ) ? $object->url : '';
			$id = apply_filters( 'nav_menu_item_id', 'menu-item-' . $object->ID, $object, $args );

			if ( 0 === $depth ) {
				$classes[] = 'nav-item';
				$classes[] = 'nav-item-' . $object->ID;

				$atts['class'] = 'nav-link';

				if ( $args->has_children ) {
					$classes[] = ' dropdown';
					$atts['href']          = ! empty( $object->url ) ? $object->url : '';
					$atts['data-hover']    = 'show';
					$atts['data-target']   = '.dropdown';
					$atts['class']         = 'dropdown-toggle nav-link';
					$atts['role']          = 'button';
					$atts['aria-haspopup'] = 'true';
				}
				$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $object, $args ) );
				if ( false !== strpos( $class_names, 'active' ) ) {
					$class_names = preg_replace( '/\bactive\b/', '', $class_names );

					if ( $object->current_item_ancestor
						|| $object->current_item_parent
					) {
						$class_names = trim( $class_names . ' has-active' );
					}
				}
				$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';
				$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';
				$output .= $indent . '<li' . $id . $value . $class_names . '>';
			} else {
				$classes[]     = 'dropdown-item';
				$class_names   = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $object, $args ) );
				$atts['class'] = $class_names;
				$atts['id']    = $id;
			}

			if ( $object->current ) {
				$atts['class'] = trim( ( $atts['class'] ?? '' ) . ' active' );
			}

			$atts = apply_filters( 'nav_menu_link_attributes', $atts, $object, $args );
			$attributes = '';
			foreach ( $atts as $attr => $value ) {
				if ( ! empty( $value ) ) {
					$value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
					$attributes .= ' ' . $attr . '="' . $value . '"';
				}
			}

			$classes = array_unique( $classes );
			$classes = explode(
				' ',
				preg_replace(
					'!\b((menu|nav)-item-\S+|(current[_-])?(page|menu)[_-]item(-\d+)?)\b!i',
					'',
					implode( ' ', $classes )
				)
			);
			$classes = array_unique( array_filter( $classes ) );

			$object_output  = $args->before;
			$object_output .= '<a' . $attributes . '>';

			/**
			 * Icons
			 * ===========
			 * Since the the menu item is NOT a Divider or Header we check the see
			 * if there is a value in the attr_title property. If the attr_title
			 * property is NOT null we apply it as the class name for the icon
			 */
			if ( ! empty( $object->attr_title ) ) {
				$object_output .= '<span class="' . esc_attr( $object->attr_title ) . '"></span>&nbsp;';
			}
			$object_output .= $args->link_before . apply_filters( 'the_title', $object->title, $object->ID ) . $args->link_after;
			$object_output .= '</a>';
			$object_output .= $args->after;

			$output .= apply_filters( 'walker_nav_menu_start_el', $object_output, $object, $depth, $args );
		}
	}

	/**
	 * Ends the element output, if needed.
	 *
	 * @see Walker::end_el()
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $object Menu item data object.
	 * @param int    $depth Depth of menu item. Used for padding.
	 * @param array  $args An array of arguments (@see wp_nav_menu()).
	 */
	public function end_el( &$output, $object, $depth, $args = [] ) {
		if ( 1 === $depth ) {
			if ( 0 === strcasecmp( $object->attr_title, 'divider' )
				|| 0 === strcasecmp( $object->title, 'divider' )
			) {
				$output .= '</div>';
			} elseif ( false !== stripos( $object->attr_title, 'header' ) ) {
				$output .= '</h6>';
			}
		} else {
			$output .= '</li>';
		}
	}

	/**
	 * Traverse elements to create list from elements.
	 *
	 * Display one element if the element doesn't have any children otherwise,
	 * display the element and its children. Will only traverse up to the max
	 * depth and no ignore elements under that depth.
	 *
	 * This method shouldn't be called directly, use the walk() method instead.
	 *
	 * @see Walker::start_el()
	 * @since 2.5.0
	 *
	 * @param object $element Data object.
	 * @param array  $children_elements List of elements to continue traversing.
	 * @param int    $max_depth Max depth to traverse.
	 * @param int    $depth Depth of current element.
	 * @param array  $args An array of arguments.
	 * @param string $output Passed by reference. Used to append additional content.
	 */
	public function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {
		if ( ! $element ) {
			return;
		}
		$id_field = $this->db_fields['id'];
		// Display this element.
		if ( is_object( $args[0] ) ) {
			$args[0]->has_children = ! empty( $children_elements[ $element->$id_field ] );
		}
		parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
	}

	/**
	 * Menu Fallback
	 * =============
	 * If this function is assigned to the wp_nav_menu's fallback_cb variable
	 * and a menu has not been assigned to the theme location in the WordPress
	 * menu manager the function with display nothing to a non-logged in user,
	 * and will add a link to the WordPress menu manager if logged in as an admin.
	 *
	 * @param array $args passed from the wp_nav_menu function.
	 */
	public static function fallback( $args ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$output = null;

		if ( $args['container'] ) {
			$output = '<' . $args['container'];

			if ( $args['container_id'] ) {
				$output .= ' id="' . $args['container_id'] . '"';
			}

			if ( $args['container_class'] ) {
				$output .= ' class="' . $args['container_class'] . '"';
			}

			$output .= '>';
		}

		$output .= '<ul';

		if ( $args['menu_id'] ) {
			$output .= ' id="' . $args['menu_id'] . '"';
		}
		if ( $args['menu_class'] ) {
			$output .= ' class="' . $args['menu_class'] . '"';
		}

		$output .= '>';
		$output .= '<li><a href="' . admin_url( 'nav-menus.php' ) . '">' . __( 'Add a menu' ) . '</a></li>';
		$output .= '</ul>';
		if ( $args['container'] ) {
			$output .= '</' . $args['container'] . '>';
		}

		echo $output;
	}
}
