<?php
/**

Plugin Name: WooCommerce Remove All Products
Description: This plugin will remove all products from a WooCommerce store.
Version: 1.0.1
Author: Gabriel Reguly
Author URI: http://omniwp.com.br/

Copyright: 2014 Gabriel Reguly
License: GNU General Public License v2.0 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
	
**/

add_action( 'plugins_loaded', 'wc_remove_all_products_init' );

function wc_remove_all_products_init() {
	add_action( 'admin_menu', 'wc_remove_all_products_admin_menu' );
}

function wc_remove_all_products_admin_menu() {
	if ( current_user_can( 'manage_woocommerce' ) ) {
		add_submenu_page('woocommerce',
			 __('WooCommerce Remove All Products', 'wc_remove_all_products_omniwp'),  
			 __('Remove All Products', 'wc_remove_all_products_omniwp') , 
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
  <h2 class="nav-tab-wrapper"> <a href="<?php echo admin_url('admin.php?page=wc_remove_all_products_page'); ?>" class="nav-tab <?php if ( $tab == 'default' ) echo 'nav-tab-active'; ?>">
    <?php _e('WooCommerce remove all products', 'wc_remove_all_products_omniwp')?>
    </a> <a href="<?php echo admin_url('admin.php?page=wc_remove_all_products_page&tab=log'); ?>" class="nav-tab <?php if ( $tab == 'log' ) echo 'nav-tab-active'; ?>">
    <?php _e('Log', 'wc_remove_all_products_omniwp')?>
    </a> </h2>
  <?php
	if ( false !== $_REQUEST['settings-updated'] ) { 
?>
  <div id="message" class="updated fade">
    
      <?php echo '<p><strong>' . __( 'Your settings have been saved.', 'wc_remove_all_products_omniwp') . '</strong></p>'; ?>
      
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
<div id="wc_remove_all_products_options" class="panel woocommerce_options_panel">
  <?php
	$args = array( 
		'post_type'   => array( 'product', 'product_variation'),
		'post_status' => get_post_stati(),
		'numberposts' => -1, 
		);
	$products = get_posts( $args );
	if ( ! $products ) {
		echo '<h3>' . __( 'No products found.', 'wc_remove_all_products_omniwp') . '</h3>';
	} else {
		echo '<h3>' . sprintf(__( 'Found %s products.', 'wc_remove_all_products_omniwp'), sizeof( $products ) ) . '</h3>';
	
		if (  empty( $_POST ) ) {
?>
	  <form method="post">
		<input type="submit" class="button button-primary" value="<?php _e('Delete all products, no confirmations will be asked!!', 'wc_remove_all_products_omniwp') ?>" />
		<?php wp_nonce_field( 'delete_action', 'delete_security_nonce'); ?>
	  </form>
<?php
		} elseif ( check_admin_referer( 'delete_action', 'delete_security_nonce' ) ) {
			$msg = sprintf(__( 'Removing %s products.', 'wc_remove_all_products_omniwp'), sizeof( $products ) );
			printf( '<p>%s</p><ol>', $msg );
			wc_remove_all_products_omniwp_log( $msg );
			foreach( $products as $product ) {
				printf( '<li>%s</li>', $product->post_title );
				wp_delete_post( $product->ID, $force_delete = true );
				$removed++;
				if ( ( time() - $timestamp ) > ( $maximum_time - 2 ) ) {
					$timeout_passed	= true;
					break;
				}
			}
			$msg =  sprintf(__( 'Removed %s products.', 'wc_remove_all_products_omniwp'), $removed ) ;		
			printf( '</ol><p>%s</p>', $msg );
			wc_remove_all_products_omniwp_log( $msg );
			if ( $timeout_passed ) {
				$msg =  sprintf(__( 'Stopped processing due to imminent timeout.', 'wc_remove_all_products_omniwp') ) ;		
				printf( '<h2>%s</h2>', $msg );
				wc_remove_all_products_omniwp_log( $msg );
?>
	  <form method="post">
		<input type="submit" class="button button-primary" value="<?php _e('Continue deleting all products, again no confirmations will be asked!!', 'wc_remove_all_products_omniwp') ?>" />
		<?php wp_nonce_field( 'delete_action', 'delete_security_nonce'); ?>
	  </form>
<?php				
			}
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
    <?php _e('Logged events', 'wc_remove_all_products_omniwp');?>
    <a href="<?php echo wp_nonce_url( admin_url('admin.php?page=wc_remove_all_products_page&tab=log&clear_log=1' ) ); ?>" class="button-primary right">
    <?php _e( 'Clear Log', 'wc_remove_all_products_omniwp') ?>
    </a></h3>
  <table class="widefat">
    <thead>
      <tr>
        <th style="width: 150px"><?php _e( 'Timestamp', 'wc_remove_all_products_omniwp') ?></th>
        <th><?php _e( 'Event', 'wc_remove_all_products_omniwp') ?></th>
        <th><?php _e( 'User', 'wc_remove_all_products_omniwp') ?></th>
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
		array_push( $log, array( $time, __( 'Log Started.', 'wc_remove_all_products_omniwp' ), $current_user_id ) );
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
		array_push( $log, array( $time, __( 'Log Started.', 'wc_remove_all_products_omniwp' ), $current_user_id ) );
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
	array_push( $log, array( $time, __( 'Log cleared.', 'wc_remove_all_products_omniwp' ), $current_user_id ) );
	update_option( 'wc_remove_all_products_omniwp_log', $log );
}
?>
