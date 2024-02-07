<?php
use Elementor\Controls_Manager;
use Elementor\Element_Base;
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Plugin class.
 *
 * The main class that initiates and runs the addon.
 *
 * @since 1.0.0
 */
final class CCPD_Main {
	/**
	 * Minimum PHP Version
	 *
	 * @since 1.0.0
	 * @var string Minimum PHP version required to run the addon.
	 */

	static $ccpd_should_script_enqueue = false;
	/**
	 * Instance
	 *
	 * @since 1.0.0
	 * @access private
	 * @static
	 * @var \Elementor_Test_Addon\Plugin The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 * @return \Elementor_Test_Addon\Plugin An instance of the class.
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;

	}

	/**
	 * Constructor
	 *
	 * Perform some compatibility checks to make sure basic requirements are meet.
	 * If all compatibility checks pass, initialize the functionality.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		add_action( 'elementor/element/common/_section_style/after_section_end', array( $this, 'ccpd_add_controls_sections' ), 1 );
		add_action( 'wp_footer', array( $this, 'ccpd_enqueue_scripts' ) );
		add_action( 'wp_ajax_ccpd_get_section_data', array( $this, 'ccpd_get_section_data' ) );
		add_action( 'wp_ajax_nopriv_ccpd_get_section_data', array( $this, 'ccpd_get_section_data' ) );
		add_action( 'elementor/frontend/widget/before_render', array( $this, 'ccpd_should_script_enqueue' ) );
		
	}
	public function ccpd_add_controls_sections( $element) {
		
		$element->start_controls_section(
			'_section_cool_pro_features',
			array(
				'label' => __( 'Cool Copy Paste Features', 'ccpd' ),
				'tab'   => Controls_Manager::TAB_ADVANCED,
			)
		);
		$element->add_control(
			'_ccpd_enable_live_copy',
			array(
				'label'              => __( 'Enable Live Copy', 'happy-addons-pro' ),
				'type'               => Controls_Manager::SWITCHER,
				'label_on'           => __( 'On', 'ccpd' ),
				'label_off'          => __( 'Off', 'ccpd' ),
				'return_value'       => 'enable',
				'default'            => 'enable',
				'frontend_available' => true,
			)
		);
		$element->end_controls_section();
	}
	public function ccpd_should_script_enqueue( Element_Base $section ) {
		if ( 'enable' == $section->get_settings_for_display( '_ccpd_enable_live_copy' ) ) {
			self::$ccpd_should_script_enqueue = true;
			remove_action( 'elementor/frontend/section/before_render', array( __CLASS__, 'ccpd_should_script_enqueue' ) );
		 }
	}
	public function ccpd_get_section_data() {
		check_ajax_referer( 'ccpd_get_section_data', 'nonce' );
		$post_id     = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;
		 $section_id = isset( $_GET['section_id'] ) ? $_GET['section_id'] : 0;
		 $main_id    = isset( $_GET['main_id'] ) ? $_GET['main_id'] : '';
		if ( empty( $post_id ) || empty( $section_id ) ) {
			wp_send_json_error( 'Incomplete request' );
		}
		$is_built_with_elementor = \Elementor\Plugin::$instance->documents->get( $post_id )->is_built_with_elementor();
		if ( ! $is_built_with_elementor ) {
			wp_send_json_error( 'Not built with elementor' );
		}
		$document       = \Elementor\Plugin::instance()->documents->get( $post_id );
		$elementor_data = $document ? $document->get_elements_data() : array();
		$widget_data    = $this->ccpd_find_element_recursive( $elementor_data, $section_id );
		if ( empty( $widget_data ) ) {
			wp_send_json_error( 'Container not found' );
		}

		wp_send_json_success( $widget_data );
	}
	public function ccpd_find_element_recursive( $elements, $form_id ) {
		foreach ( $elements as $element ) {
			if ( $form_id === $element['id'] ) {
				return $element;
			}
			if ( ! empty( $element['elements'] ) ) {
				$element = $this->ccpd_find_element_recursive( $element['elements'], $form_id );
				if ( $element ) {
					return $element;
				}
			}
		}
	}
	public function ccpd_enqueue_scripts() {
		if(self::$ccpd_should_script_enqueue==false){
		 return;
		}
		if ( self::$ccpd_should_script_enqueue ) {
			self::ccpd_add_inline_style();
			self::ccpd_add_button();
			wp_enqueue_script(
				'ccpd-marvin-ls',
				CCPD_DIR_URL . 'assets/js/marvin-ls.min.js',
				null,
				CCPD_VERSION,
				true
			);
			wp_enqueue_script(
				'ccpd-live-copy',
				CCPD_DIR_URL . 'assets/js/live-copy.min.js',
				array(
					'jquery',
					'ccpd-marvin-ls',
				),
				CCPD_VERSION,
				true
			);
			wp_localize_script(
				'ccpd-live-copy',
				'livecopy',
				array(
					'storagekey' => md5( 'Twae LICENSE KEY' ),
					'ajax_url'   => admin_url( 'admin-ajax.php' ),
					'nonce'      => wp_create_nonce( 'ccpd_get_section_data' ),
				)
			);

		 }
	}
	protected static function ccpd_add_button() {
		?>
		<div id="ccpd-live-copy-base" class="ccpd-live-copy-wrap" style="display: none">
			<a class="ccpd-live-copy-btn" href="#" class="" target="_blank"><?php echo esc_html( 'Live Copy / Paste', 'ccpd' ); ?></a>
			<div class="ccpd-text">Copy timeline inside elementor page.</div>
		</div>
		<?php
	}
	protected static function ccpd_add_inline_style() {
		?>
		<style>
			.elementor-widget.elementor-widget-timeline-widget-addon .ccpd-live-copy-wrap,
			.elementor-widget.elementor-widget-timeline-widget-addon .ccpd-live-copy-wrap {
				position: absolute;
				bottom: -90px;
				right: calc(50% - 110px);
    			width: 220px;
    			text-align: center;
				z-index: 99999;
				display: block;
				text-decoration: none;
				font-size: 15px;
				-webkit-transform: translateY(-50%);
				-ms-transform: translateY(-50%);
				transform: translateY(-50%)
			}
			.ccpd-live-copy-wrap .ccpd-live-copy-btn {
				display: block;
				padding: 12px 5px;
				border-radius: 0px;
				background: var(--e-global-color-primary);
				color: #fff;
				line-height: 1;
				-webkit-transition: all 0.2s;
				transition: all 0.2s
			}
			.ccpd-live-copy-wrap .ccpd-live-copy-btn:hover {
				background: var(--e-global-color-42e4f77);
			}
			.ccpd-live-copy-wrap .ccpd-text {
				font-size: 12px;
				line-height:1.2em;
			}
			.elementor-widget-twae-post-timeline-widget .ccpd-live-copy-wrap .ccpd-text {
    			display: none !important;
			}
			.elementor-widget.elementor-widget-timeline-widget-addon>.elementor-widget.live-copy-preview .ccpd-live-copy-wrap,
			.elementor-widget.elementor-widget-timeline-widget-addon.live-copy-preview .ccpd-live-copy-wrap,
			.elementor-widget.elementor-widget-timeline-widget-addon>.elementor-widget:not(.elementor-element-edit-mode):hover .ccpd-live-copy-wrap,
			.elementor-widget.elementor-widget-timeline-widget-addon:not(.elementor-element-edit-mode):hover .ccpd-live-copy-wrap {
				display: block
			}
		</style>
		<?php
	}
}
