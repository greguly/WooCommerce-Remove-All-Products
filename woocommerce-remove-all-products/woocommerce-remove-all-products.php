<?php
/**
 *
 * Plugin Name: WooCommerce Remove All Products
 * Plugin URI: https://wordpress.org/plugins/woocommerce-remove-all-products/

 * Version: 1.0.6
 * Description: This plugin will remove all products from a WooCommerce store.
 
 * Author: Gabriel Reguly
 * Author URI: http://omniwp.com.br/

 * Text Domain: woocommerce-remove-all-products
 * Domain Path: /languages

 * WC requires at least: 2.0
 * WC tested up to: 3.3.5

 * Copyright: 2014-18 Gabriel Reguly
 * License: GNU General Public License v2.0 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
	
**/

add_action( 'init',  'wc_remove_all_products_init' );
add_action( 'plugins_loaded', 'wc_remove_all_products_plugins_loaded' );

function wc_remove_all_products_init() {
	load_plugin_textdomain( 'woocommerce-remove-all-products', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

function wc_remove_all_products_plugins_loaded() {
	add_action( 'admin_menu', 'wc_remove_all_products_admin_menu' );
	add_filter( 'admin_footer_text', 'wc_remove_all_products_admin_footer_text', 1, 2 );
}

function wc_remove_all_products_admin_menu() {
	if ( current_user_can( 'manage_woocommerce' ) ) {
		add_submenu_page('woocommerce',
			 __('WooCommerce Remove All Products', 'woocommerce-remove-all-products'),  
			 __('Remove All Products', 'woocommerce-remove-all-products') , 
			 'manage_woocommerce', 
			 'wc_remove_all_products_page', 
			 'wc_remove_all_products_show_page');
	}
}	
	
function wc_remove_all_products_show_page() {
	global $woocommerce;
	
	// scripts & styles
	wp_register_script( 'woocommerce_admin', $woocommerce->plugin_url() . '/assets/js/admin/woocommerce_admin.min.js', array ('jquery', 'jquery-ui-widget', 'jquery-ui-core' ), '1.0' );
	wp_enqueue_script( 'woocommerce_admin' );
	wp_enqueue_style( 'woocommerce_admin_styles', $woocommerce->plugin_url() . '/assets/css/admin.css' );
		
	if ( ! isset( $_REQUEST['settings-updated'] ) ) {
		$_REQUEST['settings-updated'] = false; 
	} 
	
	$tab = 'default';
	if ( isset( $_GET['page'] ) && $_GET['page'] == 'wc_remove_all_products_page' ) {
		if ( isset( $_GET['tab'] ) ) {
			if ( in_array( $_GET['tab'], array( 
				'orders', 
				'log' ) ) )
				$tab = esc_attr( $_GET['tab'] );
		}
	}
?>

<div class="wrap woocommerce">
  <div id="icon-woocommerce" class="icon32 icon32-woocommerce-settings"></div>
  <h1 class="nav-tab-wrapper"> <a href="<?php echo admin_url('admin.php?page=wc_remove_all_products_page'); ?>" class="nav-tab <?php if ( $tab == 'default' ) echo 'nav-tab-active'; ?>">
    <?php _e('WooCommerce Remove All Products', 'woocommerce-remove-all-products')?>
    </a> <a href="<?php echo admin_url('admin.php?page=wc_remove_all_products_page&tab=log'); ?>" class="nav-tab <?php if ( $tab == 'log' ) echo 'nav-tab-active'; ?>">
    <?php _e('Log', 'woocommerce-remove-all-products')?>
    </a> </h1>
  <?php
	if ( false !== $_REQUEST['settings-updated'] ) { 
?>
  <div id="message" class="updated fade">
    
      <?php echo '<p><strong>' . __( 'Your settings have been saved.', 'woocommerce-remove-all-products') . '</strong></p>'; ?>
      
  </div>
  <?php
	}
	switch ( $tab ) {
		case 'default':		
			wc_remove_all_products_display_default_tab();
		break;
		case 'log':
			wc_remove_all_products_display_log_tab();
		break;
	}
?>
</div>
<?php
}

function wc_remove_all_products_display_default_tab() {

	$maximum_time   = ini_get('max_execution_time');
	$timestamp      = time();
	$timeout_passed = false;
	$removed        = 0;

?>
<div id="wc_remove_all_products_options" class="woocommerce_options_panel">
  <?php
  
  	$products_count = 0;
  	foreach ( wp_count_posts( 'product' ) as $product )
		$products_count += $product;
  	foreach ( wp_count_posts( 'product_variation' ) as $variation )
		$products_count += $variation;
  

	if ( ! $products_count ) {
		echo '<h2>' . __( 'No products found.', 'woocommerce-remove-all-products') . '</h2>';
	} else {
		echo '<h2>' . sprintf(__( 'Found %s products.', 'woocommerce-remove-all-products'), $products_count ) . '</h2>';
		
		if (  empty( $_POST ) ) {
?>
	  <form method="post">
		<input type="submit" class="button button-primary" value="<?php _e('Delete all products, no confirmations will be asked!!', 'woocommerce-remove-all-products') ?>" />
		<?php wp_nonce_field( 'delete_action', 'delete_security_nonce'); ?>
	  </form>
<?php
		} elseif ( check_admin_referer( 'delete_action', 'delete_security_nonce' ) ) {
			
			$args = array( 
				'post_type'   => array( 'product', 'product_variation' ),
				'post_status' => get_post_stati(),
				'numberposts' => 150, 
				);
			$products = get_posts( $args );

			$msg = sprintf(__( 'Trying to remove %s products.', 'woocommerce-remove-all-products'), sizeof( $products ) );
			printf( '<p>%s</p><ol>', $msg );
			wc_remove_all_products_omniwp_log( $msg );
			foreach( $products as $product ) {
				$_product = wc_get_product( $product->ID );
				if ( $_product ) {
					printf( '<li>%s</li>', $_product->get_formatted_name() );
					wp_delete_post( $product->ID, $force_delete = true );
					$removed++;
				}
				if ( ( time() - $timestamp ) > ( $maximum_time - 2 ) ) {
					$timeout_passed	= true;
					break;
				}
			}
			$msg =  sprintf(__( 'Removed %s products.', 'woocommerce-remove-all-products'), $removed ) ;		

			printf( '</ol><p>%s</p>', $msg );
			wc_remove_all_products_omniwp_log( $msg );
?>
	  <form method="post" id="autopostform">
		<input id="continue" type="submit" class="button button-primary" value="<?php _e('Continue deleting all products, again no confirmations will be asked!!', 'woocommerce-remove-all-products') ?>" />
		<?php wp_nonce_field( 'delete_action', 'delete_security_nonce'); ?>
	  </form>
<script>
jQuery(function($) {
  $( '#continue' ).addClass( 'disabled'  );
  $( '#autopostform'  ).submit();
});
</script>
<?php				
		}
	}
?>
</div>
<?php 
}

function wc_remove_all_products_display_log_tab() {
	$continue = false;
?>
<div class="panel woocommerce_options_panel">
  <?php 	
	if ( isset($_GET['clear_log'] )
		&& 1 == $_GET['clear_log']  
		&& check_admin_referer() ) {

		wc_remove_all_products_delete_log();

	}
?>
  <h3>
    <?php _e('Logged events', 'woocommerce-remove-all-products');?>
    <a href="<?php echo wp_nonce_url( admin_url('admin.php?page=wc_remove_all_products_page&tab=log&clear_log=1' ) ); ?>" class="button-primary right">
    <?php _e( 'Clear Log', 'woocommerce-remove-all-products') ?>
    </a></h3>
  <table class="widefat">
    <thead>
      <tr>
        <th style="width: 150px"><?php _e( 'Timestamp', 'woocommerce-remove-all-products') ?></th>
        <th><?php _e( 'Event', 'woocommerce-remove-all-products') ?></th>
        <th><?php _e( 'User', 'woocommerce-remove-all-products') ?></th>
      </tr>
    </thead>
    <tbody>
      <?php 
$class = '';
foreach ( wc_remove_all_products_get_log() as $event ) {
	$user_data = get_userdata( $event[2] ); 
?>
      <tr <?php echo $class ?>>
        <td><?php echo wc_remove_all_products_nice_time( $event[0] ); ?></td>
        <td><?php echo $event[1]; ?></td>
        <td><?php echo $user_data->display_name; ?></td>
      </tr>
      <?php 
	if ( empty( $class ) )  {
		$class = ' class="alternate"';
	} else {
		$class = '';
	}

}
?>
    </tbody>
  </table>
</div>
<?php 
}

function wc_remove_all_products_nice_time( $time, $args = false ) {

	$defaults = array( 'format' => 'date_and_time' );
	extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

	if ( ! $time)
		return false;

	if ( $format == 'date' )
		return date( get_option( 'date_format' ), $time );
		
	if ( $format == 'time' )
		return date( get_option( 'time_format' ), $time );
		
	if ( $format == 'date_and_time' ) //get_option( 'time_format' )
		return date( get_option( 'date_format' ), $time ) . " " . date( 'H:i:s', $time );

	return false;
}

function wc_remove_all_products_omniwp_log( $event ) {
	$current_user = wp_get_current_user();
	$current_user_id = $current_user->ID;

	$log = get_option( 'wc_remove_all_products_omniwp_log' );

	$time_difference = get_option('gmt_offset') * 3600;
	$time            = time() + $time_difference;
	
	if ( ! is_array( $log ) ) {
		$log = array();
		array_push( $log, array( $time, __( 'Log Started.', 'woocommerce-remove-all-products' ), $current_user_id ) );
	}

	array_push( $log, array( $time, $event, $current_user_id ) );
	return update_option( 'wc_remove_all_products_omniwp_log', $log );
}

function wc_remove_all_products_get_log() {
	$log = get_option( 'wc_remove_all_products_omniwp_log' );
	// If no log created yet, create one
	if ( ! is_array( $log ) ) {
		$current_user    = wp_get_current_user();
		$current_user_id = $current_user->ID;
		$log             = array();
		$time_difference = get_option( 'gmt_offset' ) * 3600;
		$time            = time() + $time_difference;
		array_push( $log, array( $time, __( 'Log Started.', 'woocommerce-remove-all-products' ), $current_user_id ) );
		update_option( 'wc_remove_all_products_omniwp_log', $log );
	}
	return array_reverse( get_option( 'wc_remove_all_products_omniwp_log' ) );
}

function wc_remove_all_products_delete_log() {
	$current_user    = wp_get_current_user();
	$current_user_id = $current_user->ID;
	$log             = array();
	$time_difference = get_option( 'gmt_offset' ) * 3600;
	$time            = time() + $time_difference;
	array_push( $log, array( $time, __( 'Log cleared.', 'woocommerce-remove-all-products' ), $current_user_id ) );
	update_option( 'wc_remove_all_products_omniwp_log', $log );
}

function wc_remove_all_products_admin_footer_text( $footer_text ) {
	global $current_screen;

	// list of admin pages we want this to appear on
	$pages = array(
		'woocommerce_page_wc_remove_all_products_page',
	);

	if ( isset( $current_screen->id ) && in_array( $current_screen->id, $pages ) ) {
		$footer_text = sprintf( __( 'Please rate <a href="%1$s" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a> for <strong>WooCommerce Remove All Products</strong> on <a href="%1$s" target="_blank">WordPress.org</a> and make the developer happy :-)', 'woocommerce-remove-all-products' ), 'https://wordpress.org/support/view/plugin-reviews/woocommerce-remove-all-products?filter=5#postform' );
	}

	return $footer_text;
}

