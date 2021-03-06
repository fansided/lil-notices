<?php
/**
 * Lil Notices Plugin Class
 *
 * @since 0.1.2
 */

//avoid direct calls to this file, because now WP core and framework has been used
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( ! class_exists( 'Lil_Notices' ) ) {
	class Lil_Notices {

		/**
		 * Initialize the plugin
		 *
		 * @since   0.1
		 * @return  void
		 */
		public static function init() {
			// Disable notices UIX in network admin area or not in the admin at all
			if ( ! is_admin() || ( is_multisite() ) && ( is_network_admin() ) ) {
				return;
			}
			add_action( 'admin_bar_menu', array( get_called_class(), 'admin_bar_menu' ), 999 );
			add_action( 'admin_enqueue_scripts', array( get_called_class(), 'admin_enqueue_scripts' ), 999 );

			if ( ! empty( $_REQUEST['notice_test'] ) ) {
				add_action( 'admin_notices', function () {
					echo '<div class="notice notice-success is-dismissible">';
					echo '<p>This is a notice test. Notices created by themes and plugins will be here in the future!</p>';
					echo '</div>';
				} );
			}

		} // end public function init

		public static function plugin_action_links( $links ) {
			$links[] = '<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '?notice_test=lil-notices">' . esc_html__( 'Test Lil Notices', 'liln' ) . '</a>';

			return $links;
		}


		public static function admin_enqueue_scripts() {
			wp_enqueue_script( 'ln-scripts', plugin_dir_url( __FILE__ ) . 'assets/main.js', array(), LIL_NOTICES__VERSION, true );
			wp_enqueue_style( 'ln-styles', plugin_dir_url( __FILE__ ) . 'assets/style.css', array(), LIL_NOTICES__VERSION );
		}

		public static function admin_bar_menu() {

			global $wp_admin_bar;

			$menu_id = 'ln_menu';
			$wp_admin_bar->add_node( array(
				'href' => '#',
				'id' => $menu_id,
				'parent' => 'top-secondary',
				'title' => __( 'Notices' ),
			) );
			$wp_admin_bar->add_node( array(
				'id'     => 'ln_all_notices',
				'meta'   => array(
					'html' => static::notices_content(),
				),
				'parent' => $menu_id,

			) );

		}

		/**
		 * Returns the HTML content for the Admin Bar Menu notices dropdown
		 *
		 * @since   0.1
		 * @return  string
		 */
		public static function notices_content() {
			global $wp_filter;
			// count the number of items attached to 'admin_notices' and 'all_admin_notices'
			// might actually just pull each item from this bit as it will make more sense
			$html = '';
			if ( empty( $wp_filter['admin_notices'] ) && empty( $wp_filter['all_admin_notices']) ) {
				$html .= apply_filters( 'ln_no_notices', __( 'Nothing to see here!', 'ln_domain' ) );
			} else {
				$html .= '<ul class="ln-notice-list">';

				ob_start();
				do_action( 'admin_notices' );
				do_action( 'all_admin_notices' );
				$notices = ob_get_clean();

				// preg match & replace the classes that WP is using to move admin notices in under the page's H tag
				$notices = preg_replace(
					'/(class=[\'\"])([A-Za-z0-9\-\_\ ]*)(updated)([A-Za-z0-9\-\_\ ]*)([\"\'])/',
					'$1$2ln-updated $4$5', $notices );

				$notices = preg_replace(
					'/(class=[\'\"])([A-Za-z0-9\-\_\ ]*)(notice[^\-\_])([A-Za-z0-9\-\_\ ]*)([\"\'])/',
					'$1$2ln-notice $4$5', $notices );

				$notices = preg_replace(
					'/(class=[\'\"])([A-Za-z0-9\-\_\ ]*)(error)([A-Za-z0-9\-\_\ ]*)([\"\'])/',
					'$1$2ln-error $4$5', $notices );

				$html .= $notices;

				$html .= '</ul>';
			}

			// since we're grabbing them above, remove the core do_action via remove_all_actions
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'all_admin_notices' );

			return $html;
		}

		/**
		 * Activate the plugin
		 *
		 * @since   0.1
		 * @return  void
		 */
		public static function activate() {
		} // END public static function activate

		/**
		 * Deactivate the plugin
		 *
		 * @since   0.1
		 * @return  void
		 */
		public static function deactivate() {
		} // END public static function deactivate

	} // END class Lil_Notices
} // END if ( ! class_exists( 'Lil_Notices' ) )
