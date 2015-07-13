<?php


/*
Plugin Name: WP Post Type Resolver
Plugin URI: https://github.com/dominickolbe/WP-Post-Type-Resolver
Description: Wordpress Plugin, which return the post type of a given post (usefull for AJAX Requests).
Author: Dominic Kolbe
Author URI: http://dominickolbe.dk
Version: 1.0
*/

class PostTypeResolver {

    function activate() {
        global $wp_rewrite;
        $this->flush_rewrite_rules();
    }

    // Took out the $wp_rewrite->rules replacement so the rewrite rules filter could handle this.
    function create_rewrite_rules($rules) {
        global $wp_rewrite;
        $newRule = array('post-type-resolver/(.+)' => 'index.php?post-type-resolver='.$wp_rewrite->preg_index(1));
        $newRules = $newRule + $rules;
        return $newRules;
    }

    function add_query_vars($qvars) {
        $qvars[] = 'post-type-resolver';
        return $qvars;
    }

    function flush_rewrite_rules() {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }

    function template_redirect_intercept() {
        global $wp_query;
        if ($wp_query->get('post-type-resolver')) {
            $this->pushoutput($wp_query->get('post-type-resolver'));
            exit;
        }
    }

    function pushoutput($message) {
        $this->output($message);
    }

    function output( $output ) {
       
        header( 'Content-type: application/json' );

        $post_id = $output;

        $array = array("post_type" => get_post_type( get_post($post_id) ));

        echo json_encode($array);

    }
}

$MyPluginCode = new PostTypeResolver();
register_activation_hook( __file__, array($MyPluginCode, 'activate') );

// Using a filter instead of an action to create the rewrite rules.
// Write rules -> Add query vars -> Recalculate rewrite rules
add_filter('rewrite_rules_array', array($MyPluginCode, 'create_rewrite_rules'));
add_filter('query_vars',array($MyPluginCode, 'add_query_vars'));

// Recalculates rewrite rules during admin init to save resourcees.
// Could probably run it once as long as it isn't going to change or check the
// $wp_rewrite rules to see if it's active.
add_filter('admin_init', array($MyPluginCode, 'flush_rewrite_rules'));
add_action( 'template_redirect', array($MyPluginCode, 'template_redirect_intercept') );