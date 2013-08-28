<?php
/*
Plugin Name: Support for Custom Post Templates in ACF
Plugin URI: github.com/lukechapman/custom-post-template-support-for-acf
Description: Adds a post template location rule to ACF for themes using the Custom Post Template plugin
Version: 1.2
Author: Luke Chapman
Author URI: www.iamlukechapman.com
*/

// Get post templates
function get_post_templates(){
  $theme = wp_get_theme();
  $post_templates = array();
  $files = (array) $theme->get_files( 'php', 1 );
  foreach ( $files as $file => $full_path ) {
  	$headers = get_file_data( $full_path, array( 'Template Name Posts' => 'Template Name Posts' ) );
    if ( empty( $headers['Template Name Posts'] ) )
    continue;
    $post_templates[ $file ] = $headers['Template Name Posts'];
  }
  return $post_templates;
} 

// Add custom post template rule to dropdown
add_filter('acf/location/rule_types', 'acf_location_rules_types');
function acf_location_rules_types( $choices ){
  $choices['Post']['cpt'] = 'Post Template';
  return $choices;
}

// Add custom post template names to value dropdown
add_filter('acf/location/rule_values/cpt', 'acf_location_rules_values_cpt');
function acf_location_rules_values_cpt( $choices ){
  $templates = get_post_templates();
    foreach($templates as $k => $v){
	  $choices[$k] = $v;
	}
  return $choices;
}

// Match location rule and show ACFs
add_filter('acf/location/rule_match/cpt', 'acf_location_rules_match_cpt', 10, 3);
function acf_location_rules_match_cpt( $match, $rule, $options ){
  global $post;
  if(isset($options['cpt'])){
    $current_post_template = $options['cpt'];	
  }else{
    $current_post_template = get_post_meta($post->ID,'custom_post_template',true);
  }
  $selected_post_template = $rule['value'];
  if($rule['operator'] == "=="){
  	$match = ( $current_post_template == $selected_post_template );
  }elseif($rule['operator'] == "!="){
  	$match = ( $current_post_template != $selected_post_template );
  }
  return $match;
}

// Add js to admin header to trigger ACFs
add_action('admin_head', 'acf_custom_post_template_js');
function acf_custom_post_template_js() {
  echo "<script>jQuery('#custom_post_template').live('change', function(){ acf.screen['cpt'] = jQuery(this).val(); jQuery(document).trigger('acf/update_field_groups');});</script> \n";
}

?>