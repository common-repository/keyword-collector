<?php
/*
  Plugin Name: KeyWord Collector
  Plugin URI: http://www.adsimple.at/keyword-collector-wordpress-plugin/
  Description: Collects keywords for single URLs via SISTRIX API (API key needed) and displayes them in a flexible manner on the very same single URL.
  Version: 1.4
  Author: AdSimple
  Author URI: http://www.adsimple.at/
 */

require_once(dirname(__FILE__).'/lib/WPKeyWordSettings.php');


class WPKeyWordPlugin{

    public $num;
    public $api_key;
    public $country_shortcode;
    public $custom_field_name;

    public $update_interval;
    public $delete_interval;
    
    public $auto_insert_page;
    public $auto_insert_post;
    public $auto_tag_insert;

    public function __construct() {
        $this->num = get_option('num');
        $this->api_key = get_option('api_key');
        $this->country_shortcode = get_option('country_shortcode');
        $this->custom_field_name = get_option('custom_field_name');

        $this->update_interval = get_option('update_interval');
        $this->delete_interval = get_option('delete_interval');

        $this->auto_insert_page = get_option('auto_insert_page');
        $this->auto_insert_post = get_option('auto_insert_post');
        $this->auto_insert_firma = get_option('auto_insert_firma');
        $this->auto_tag_insert = get_option('auto_tag_insert');
    }

    public function init() {
        wp_enqueue_script('WPKeyWordJS', plugin_dir_url(__FILE__) . 'lib/WPKeyWordJS.js', array('jquery'), time());
        wp_enqueue_style('WPKeyWordCSS', plugin_dir_url(__FILE__) . 'lib/WPKeyWordCSS.css', '', time());
        if (get_option('num', "-1") == "-1") {
          update_option("num", 10);
        }
        if (get_option('update_interval', "-1") == "-1") {
          update_option("update_interval", 30);
        }
        if (get_option('delete_interval', "-1") == "-1") {
          update_option("delete_interval", 10);
        }
        if (get_option('auto_insert_firma', "-1") == "-1") {
          update_option("auto_insert_firma", true);
        }
        if (get_option('before_list', "-1") == "-1") {
          update_option("before_list", "<h2>Schlagwörter zu dieser Firma</h2><div>");
        }
        if (get_option('before_items', "-1") == "-1") {
          update_option("before_items", "");
        }
        if (get_option('after_items', "-1") == "-1") {
          update_option("after_items", "<span>, </span>");
        }
        if (get_option('after_list', "-1") == "-1") {
          update_option("after_list", "</div><br><br>");
        }
        $settings = new WPKeyWordSettings();
        $settings->init();
    }
}
$WpJsonApi = new WPKeyWordPlugin();
$WpJsonApi->init();
add_filter('widget_text','do_shortcode');
add_shortcode( 'keywords_collector', 'add_keywords' );
register_deactivation_hook( __FILE__, 'delete_options' );

add_action('wp_ajax_WPKeyWordCronTest', 'check_custom_field_key_words');

function delete_options() {
  delete_option("before_list");
  delete_option("after_list");
  delete_option("before_items");
  delete_option("after_items");
  delete_option("num");
  delete_option("api_key");
  delete_option("update_interval");
  delete_option("delete_interval");
  delete_option("country_shortcode");
  delete_option("custom_field_name");
  delete_option("auto_insert_post");
  delete_option("auto_insert_page");
  delete_option("auto_insert_firma");
  delete_option("auto_tag_insert");
}

function add_keywords( $atts ){
  global $WpJsonApi;
  $post_id = get_the_ID();
  if (!unserialize(get_post_meta($post_id, 'key_words',true))) {
      $response = get_key_words_api($post_id);
      if (sizeof($response->answer[0]->result) > 0 && $response->status !== "fail") {
        foreach ($response->answer[0]->result as $k => $word) {          
          $response->answer[0]->result[$k]->date_add = date('Y-m-d H:i:s');
        }
        add_post_meta($post_id, "key_words", serialize($response->answer[0]->result), true);       
        add_post_meta($post_id, "ecpt_last_update", date('Y-m-d H:i:s'));        
      }
  }
  $post_keywords = unserialize(get_post_meta($post_id, 'key_words',true));
  if ($post_keywords) {
    if (sizeof($post_keywords) != $WpJsonApi->num && $post_keywords[0]->num_flag) {
      $response = get_key_words_api($post_id);
      if (sizeof($response->answer[0]->result) > 0 && $response->status !== "fail") {
        foreach (array_keys($response->answer[0]->result) as $k) {         
          $response->answer[0]->result[$k]->date_add = date('Y-m-d H:i:s');
        }
        if (sizeof($response->answer[0]->result) < $WpJsonApi->num) {
          $response->answer[0]->result[0]->num_flag = false;
        } else if (sizeof($response->answer[0]->result) == $WpJsonApi->num){
          $response->answer[0]->result[0]->num_flag = true;
        }
        update_post_meta($post_id, "key_words", serialize($response->answer[0]->result));        
        update_post_meta($post_id, "ecpt_last_update", date('Y-m-d H:i:s'));        
        
        $post_keywords = unserialize(get_post_meta($post_id, 'key_words',true));
      }
    }
    if (!is_array($post_keywords)) {
      $post_keywords = unserialize($post_keywords);
    }
    ob_start();
    echo "<div class='keywordcollector'>";
      echo get_option("before_list");
        foreach ($post_keywords as $k => $word) {
          echo get_option("before_items");
            echo $word->kw;           
            $post_keywords[$k]->date_use = date('Y-m-d H:i:s');
            if ($WpJsonApi->auto_tag_insert) {
              wp_set_post_tags($post_id, $word->kw, true);
            }
          echo get_option("after_items");
        }
      echo get_option("after_list");
    echo "</div>";
    update_post_meta($post_id, "key_words", serialize($post_keywords));    
    update_post_meta($post_id, "key_words_date_use", date('Y-m-d H:i:s'));
    return ob_get_clean();
  }
}

add_action('init', 'add_cron_shedule');
function add_cron_shedule() {
  // wp_clear_scheduled_hook( 'wake_up_event' );
  if ( !wp_next_scheduled( 'wake_up_event' ) ) {
    wp_schedule_event( current_time( 'timestamp' ), 'hourly', 'wake_up_event');
  }
}

add_action('wake_up_event', 'check_custom_field_key_words');

/*
function check_custom_field_key_words() {
    global $WpJsonApi;
    $args = array(
        'meta_key' => 'key_words',
        'post_type' => 'any',
    );
    $meta_posts = get_posts($args); 
    foreach ($meta_posts as $post) {
        $key_words = unserialize(get_post_meta($post->ID, "key_words", true));
        if ($key_words) {
            if (time() - strtotime($key_words[0]->date_use) > ($WpJsonApi->delete_interval * 3600) * 24 ) {
                delete_post_meta($post->ID, "key_words");
                delete_post_meta($post->ID, "ecpt_last_update");
                
            } elseif(time() - strtotime($key_words[0]->date_add) > ($WpJsonApi->update_interval * 3600) * 24 ) {
                $response = get_key_words_api($post->ID);
                if (sizeof($response->answer[0]->result) > 0 && $response->status !== "fail") {
                    foreach ($response->answer[0]->result as $k => $word) {
                        //-->//$response->answer[0]->result[$k]->date_add = date('d.m.y h:m:s', time());
                        $response->answer[0]->result[$k]->date_add = date('Y-m-d H:i:s');
                    }  

                    update_post_meta($post->ID, "key_words", serialize($response->answer[0]->result));
                    //-->//update_post_meta($post->ID, "ecpt_last_update", date('d.m.y h:m:s', time()));
                    update_post_meta($post->ID, "ecpt_last_update", date('Y-m-d H:i:s'));                    
                } else {
                    delete_post_meta($post->ID, "key_words");
                    delete_post_meta($post->ID, "ecpt_last_update");                    
                }
            }      
        }
    }
}
* 
*/

function check_custom_field_key_words() {
    // $start = microtime();
    // file_put_contents(plugin_dir_path(__FILE__) . "logs/cron_test.txt", "start date = " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
    global $WpJsonApi, $wpdb;
    $limit = 250;

    $sql = "
        SELECT 
            t1.ID
        FROM
            `".$wpdb->prefix."posts` AS t1
        INNER JOIN 
            `".$wpdb->prefix."postmeta` AS t2
            ON (
                t1.ID = t2.post_id 
                AND 
                t2.meta_key = 'key_words'
            )
        INNER JOIN 
           `".$wpdb->prefix."postmeta` AS t3
            ON (
                t1.ID = t3.post_id 
                AND 
                t3.meta_key = 'key_words_date_use'
            )
        WHERE
            t2.meta_value IS NOT NULL
            AND 
            t2.meta_value != ''
            AND
            t3.meta_value < %s
        GROUP BY 
            t1.ID
        ORDER BY 
            t1.ID ASC
        LIMIT %d
    ";
    $sql = $wpdb->prepare($sql,(date('Y-m-d H:i:s',time() - $WpJsonApi->delete_interval * 3600 * 24 )),$limit);
    $resultArr = $wpdb->get_results($sql, ARRAY_A);    
    if(!empty($resultArr)) {
        foreach ($resultArr as $post) {
            $post_id = $post['ID'];
            $key_words = unserialize(get_post_meta($post_id, "key_words", true));
            if (time() - strtotime($key_words[0]->date_use) > ($WpJsonApi->delete_interval * 3600) * 24 ) {
                delete_post_meta($post_id, "key_words");
                delete_post_meta($post_id, "ecpt_last_update");
            }
        }
    }


    $sql = "
        SELECT 
            t1.ID
        FROM
            `".$wpdb->prefix."posts` AS t1
        INNER JOIN 
            `".$wpdb->prefix."postmeta` AS t2
            ON (
                t1.ID = t2.post_id 
                AND 
                t2.meta_key = 'key_words'
            )
        INNER JOIN 
           `".$wpdb->prefix."postmeta` AS t3
            ON (
                t1.ID = t3.post_id 
                AND 
                t3.meta_key = 'ecpt_last_update'
            )
        WHERE
            t2.meta_value IS NOT NULL
            AND 
            t2.meta_value != ''
            AND
            t3.meta_value < %s
        GROUP BY 
            t1.ID
        ORDER BY 
            t1.ID ASC
        LIMIT %d
    ";
    $sql = $wpdb->prepare($sql,(date('Y-m-d H:i:s',time() - $WpJsonApi->update_interval * 3600 * 24 )),$limit);
    $resultArr = $wpdb->get_results($sql, ARRAY_A);
    if(!empty($resultArr)) {
        foreach ($resultArr as $post) {
            $post_id = $post['ID'];
            $key_words = unserialize(get_post_meta($post_id, "key_words", true));
            if ($key_words) {
                if(time() - strtotime($key_words[0]->date_add) > ($WpJsonApi->update_interval * 3600) * 24 ) {
                    $response = get_key_words_api($post_id);
                    if (sizeof($response->answer[0]->result) > 0 && $response->status !== "fail") {
                        foreach (array_keys($response->answer[0]->result) as $k) {                            
                            $response->answer[0]->result[$k]->date_add = date('Y-m-d H:i:s');
                        }
                        update_post_meta($post_id, "key_words", serialize($response->answer[0]->result));                       
                        update_post_meta($post_id, "ecpt_last_update", date('Y-m-d H:i:s'));                    
                    } else {
                        delete_post_meta($post_id, "key_words");
                        delete_post_meta($post_id, "ecpt_last_update");                    
                    }
                }      
            }
        }
    }    
  // $time = microtime() - $start;
  // file_put_contents(plugin_dir_path(__FILE__) . "logs/cron_test.txt", "limit = ". $limit .", post counts = " . sizeof($resultArr) . ", date = " . date('Y-m-d H:i:s') . " -> " . $time . "\n", FILE_APPEND);
}

add_action( 'save_post', 'update_custom_field', 10, 1);
function update_custom_field($post_ID) {  
  if (get_post_meta($post_ID, 'key_words', true)) {
    $post = get_post($post_ID);
    $response = get_key_words_api($post->ID);
    if (sizeof($response->answer[0]->result) > 0 && $response->status !== "fail") {
      foreach ($response->answer[0]->result as $k => $word) {
        $response->answer[0]->result[$k]->date_add = date('Y-m-d H:i:s');
      }
      update_post_meta($post_ID, "key_words", serialize($response->answer[0]->result));      
      update_post_meta($post_ID, "ecpt_last_update", date('Y-m-d H:i:s'));
                    
    } else {
      delete_post_meta($post_ID, "key_words");
      delete_post_meta($post_ID, "ecpt_last_update");
    }
  }
}

function get_key_words_api($post_id) {
  global $WpJsonApi;
  $path = esc_url(get_permalink($post_id));
  $domain = ""; 
  if ($WpJsonApi->custom_field_name) {
    $custom_field = get_post_meta($post_id, $WpJsonApi->custom_field_name, true);
    if ($custom_field) {
      $domain = $custom_field;
      $path = "";
    } 
  }
  $response = wp_remote_get( 'https://api.sistrix.com/keyword.domain.seo?domain=' . $domain . '&format=json&num=' . $WpJsonApi->num . '&api_key=' . $WpJsonApi->api_key . '&country=' . $WpJsonApi->country_shortcode . '&date=now&path=' . $path);
  //$response = wp_remote_get( 'https://api.sistrix.com/keyword.domain.seo?format=json&num=2&api_key=' . $WpJsonApi->api_key . '&country=' . $WpJsonApi->country_shortcode . '&date=now&path=https://www.aestomed.at/laserbehandlungen/tattooentfernung-laser-wien/');
  $response_body = json_decode(wp_remote_retrieve_body($response));
  get_log_string($post_id, $response_body);
  return $response_body;
}

function get_log_string($post_id, $response = false) {
  global $WpJsonApi;
  if (sizeof($response->answer[0]->result) > 0) {
    $post = get_post($post_id);
    $log_string = "[".date('d.m.y h:m:s')."] ";
    $log_string .= "Title: " . $post->post_title . " (ID = " . $post_id . ") ";
    $response_credits = wp_remote_get( 'https://api.sistrix.com/credits?format=json&api_key=' . $WpJsonApi->api_key);
    $response_credits = json_decode(wp_remote_retrieve_body($response_credits));
    if ($response->credits[0]->used) {
      $credits_used = $response->credits[0]->used;
    } else {
      $credits_used = 0;
    }
    $log_string .= "- The " . sizeof($response->answer[0]->result) . " keywords were received (credits used = " . $credits_used;
    if ($response_credits->answer) {
      $log_string .= ", credits left = " . $response_credits->answer[0]->credits[0]->value;
    }
    $log_string .= " ) ";
    $log_string .= "\n";
  }
  file_put_contents(plugin_dir_path(__FILE__) . "logs/activity_logs.txt", $log_string, FILE_APPEND);
}

function shortcode_add_to_content( $content ) {    
  global $WpJsonApi;
  $post = get_post(get_the_ID());
  if( is_single() && $WpJsonApi->auto_insert_post && $post->post_type == "post") {
    preg_match("(\[keywords_collector\])", $post->post_content, $matches);
    if (sizeof($matches) == 0) {
      $content .= '[keywords_collector]';
    }
  } else if( is_single() && $WpJsonApi->auto_insert_firma && $post->post_type == "firma"){
    preg_match("(\[keywords_collector\])", $post->post_content, $matches);
    if (sizeof($matches) == 0) {
      $content .= '[keywords_collector]';
    }
  } else if( is_page() && $WpJsonApi->auto_insert_page && $post->post_type == "page"){
    preg_match("(\[keywords_collector\])", $post->post_content, $matches);
    if (sizeof($matches) == 0) {
      $content .= '[keywords_collector]';
    }
  }
  return $content;
}
add_filter( 'the_content', 'shortcode_add_to_content' );
function unserializeData( $data ) {    

  return $content;
}