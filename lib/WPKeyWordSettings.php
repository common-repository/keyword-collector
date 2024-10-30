<?php

class WPKeyWordSettings
{
    protected static $content = '';
    public function __construct() {

    }

    /**
     * Initialize actions
     */
    public function init() {
        add_action('admin_menu', array($this, 'WPKeyWord'));
        add_action('admin_print_footer_scripts', array($this, 'WPKeyWordSettingsJs'), 99);
        add_action('wp_ajax_WPKeyWordSettings', array($this, 'WPKeyWordSettings'));
        add_action('wp_ajax_nopriv_WPKeyWordSettings', array($this, 'WPKeyWordSettings'));

        add_action('wp_ajax_WPKeyWordClearLogs', array($this, 'WPKeyWordClearLogs'));

        add_action('wp_ajax_WPKeyWordUpdateLogs', array($this, 'WPKeyWordUpdateLogs'));
        add_action('wp_ajax_WPKeyWordGetCredits', array($this, 'WPKeyWordGetCredits'));
    }

    /**
     *  Create link in admin panel Settings tab
     */
    public function WPKeyWord() {
        add_menu_page( 'KeyWord Collector Activity', 'KeyWord Collector Activity', 'manage_options', 'keyword_collector', array($this, 'WPKeyWordAdminPageLogs') );
        
        add_options_page('KeyWord Collector', 'KeyWord Collector', 'manage_options', 'wp-keyword', array($this, 'WPKeyWordOptions'));
    }

    /**
     * Create options page fields / send ajax and save field values in wp_options table
     */
    public function WPKeyWordOptions() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        ?>
            <form role="form" id="WPKeyWordSettingsForm">
                <h1><?php echo __("Plugin Configuration"); ?></h1>
                <div class="keyword-activity-wrapper">
                    <div class="keyword-tabs-btn-wrapper">
                      <button type="button" class="keyword-tabs-btn active" data-keyword-tab-item="keyword-collector-wrapper"><?php echo __("Wrapper"); ?></button>
                      <button type="button" class="keyword-tabs-btn" data-keyword-tab-item="keyword-collector-api"><?php echo __("API Settings"); ?></button>
                      <button type="button" class="keyword-tabs-btn" data-keyword-tab-item="keyword-collector-cron"><?php echo __("Cron"); ?></button>
                      <button type="button" class="keyword-tabs-btn" data-keyword-tab-item="keyword-collector-features"><?php echo __("Features"); ?></button>
                    </div>
                    <div class="wrapper keyword-tabs active" data-keyword-tab-target="keyword-collector-wrapper">
                        <h1><?php echo __("Wrapper"); ?></h1>
                        <div class="key-row">
                            <div class="label" for="before-list"><?php echo __("HTML before the list"); ?></div>
                            <input type="text" class="form-control" id="before-list" value="<?php echo get_option('before_list'); ?>" placeholder="<?php echo __("HTML before the list"); ?>">
                        </div>
                        <div class="key-row">
                        <div class="label" for="after-list"><?php echo __("HTML after the list"); ?></div>
                            <input type="text" class="form-control" id="after-list" value="<?php echo get_option('after_list'); ?>" placeholder="<?php echo __("HTML after the list"); ?>">
                        </div>
                        <div class="key-row">
                            <div class="label" for="before-items"><?php echo __("HTML before each item"); ?></div>
                            <input type="text" class="form-control" id="before-items" value="<?php echo get_option('before_items'); ?>" placeholder="<?php echo __("HTML before each item"); ?>">
                        </div>
                        <div class="key-row">
                            <div class="label" for="after-items"><?php echo __("HTML after items"); ?></div>
                            <input type="text" class="form-control" id="after-items" value="<?php echo get_option('after_items'); ?>" placeholder="<?php echo __("HTML after each item"); ?>">
                        </div>
                    </div>
                    <div class="api keyword-tabs" data-keyword-tab-target="keyword-collector-api">
                        <h1><?php echo __("API Settings"); ?></h1>
                        <div class="key-row">
                            <div class="label" for="item-num"><?php echo __("Item count"); ?></div>
                            <input type="number" class="form-control" id="item-num" value="<?php echo get_option('num'); ?>" placeholder="<?php echo __("Item count"); ?>">        
                        </div>
                        <div class="key-row">
                            <div class="label" for="sistrix-api-key"><?php echo __("SISTRIX API Key"); ?></div>
                            <input type="text" class="form-control" id="sistrix-api-key" value="<?php echo get_option('api_key'); ?>" placeholder="<?php echo __("SISTRIX API Key"); ?>">
                        </div>
                        <div class="key-row">
                            <div class="label" for="country-shortcode"><?php echo __("Country shortcode"); ?></div>
                            <input type="text" class="form-control" id="country-shortcode" value="<?php echo get_option('country_shortcode'); ?>" placeholder="<?php echo __("Country shortcode"); ?>">           
                        </div>
                    </div>
                    <div class="cron keyword-tabs" data-keyword-tab-target="keyword-collector-cron">
                        <h1><?php echo __("Cron"); ?></h1>
                        <div class="key-row">
                            <div class="label" for="update-interval"><?php echo __("Update interval"); ?> (<?php echo __("days"); ?>)</div></th>
                            <input type="number" class="form-control" id="update-interval" value="<?php echo get_option('update_interval'); ?>" placeholder="<?php echo __("Update interval"); ?>">                     
                        </div>
                        <div class="key-row">
                            <div class="label" for="delete-interval"><?php echo __("Delete interval"); ?> (<?php echo __("days"); ?>)</div>
                            <input type="number" class="form-control" id="delete-interval" value="<?php echo get_option('delete_interval'); ?>" placeholder="<?php echo __("Delete interval"); ?>">
                        </div>
                    </div>
                    <div class="features keyword-tabs" data-keyword-tab-target="keyword-collector-features">
                        <h1><?php echo __("Features"); ?></h1>
                        <div class="key-row">
                            <div class="label"><?php echo __("Auto Insert"); ?></div>
                            <span class="label" for="autho-insert-post"><?php echo __("Post"); ?></span>
                            <input type="checkbox" class="form-control" id="auto-insert-post" 
                            value=""<?php echo (get_option('auto_insert_post'))? ' checked' : ''; ?>>
                            <span class="label" for="autho-insert-page"><?php echo __("Page"); ?></span>
                            <input type="checkbox" class="form-control" id="auto-insert-page"
                            value=""<?php echo (get_option('auto_insert_page'))? ' checked' : ''; ?>>
                            <span class="label" for="autho-insert-firma"><?php echo __("Firma"); ?></span>
                            <input type="checkbox" class="form-control" id="auto-insert-firma"
                            value=""<?php echo (get_option('auto_insert_firma'))? ' checked' : ''; ?>>
                        </div>
                        <div class="key-row">
                            <span class="label"><?php echo __("Add keywords to post/page tags"); ?></span> 
                            <input type="checkbox" class="form-control" id="auto-tag-insert" 
                            value=""<?php echo (get_option('auto_tag_insert'))? ' checked' : ''; ?>>
                        </div>
                        <div class="key-row">
                            <div class="label"><?php echo __("Custom Field Name"); ?></div>
                            <input type="text" class="form-control" id="custom-field-name"
                             value="<?php echo get_option('custom_field_name'); ?>" placeholder="<?php echo __("Custom Field Name"); ?>">
                        </div>
                    </div>
                </div>
                <p class="submit">
                    <button type="button" id="wpKeyWord-settings-save" class="button button-primary" ><?php echo __("Save Changes"); ?></button>
                    <span id="wpKeyWord-setings-success" style="display: none;"><?php echo __("Successfully saved"); ?></span>
                </p>
            </form>
        <?php
    }

    public function WPKeyWordAdminPageLogs() {
        $page_num = 1;
        if(isset($_GET['paged'])) {
            $page_num = (int)$_GET['paged'];
        }
        $limit = 10; 
        $offset = ($page_num - 1)*$limit;
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        //$logs = file_get_contents(plugin_dir_path(__FILE__) . "../logs/activity_logs.txt");
        $logs = file(plugin_dir_path(__FILE__) . "../logs/activity_logs.txt");
        $logs = array_reverse($logs);
        $logs = implode("", $logs);
        if (!$logs) {
            file_put_contents(plugin_dir_path(__FILE__) . "../logs/activity_logs.txt", "", FILE_APPEND);
            // $logs = file_get_contents(plugin_dir_path(__FILE__) . "../logs/activity_logs.txt");
            $logs = file(plugin_dir_path(__FILE__) . "../logs/activity_logs.txt");
            $logs = array_reverse($logs);
            $logs = implode("", $logs);
        }
        $response_credits = wp_remote_get( 'https://api.sistrix.com/credits?format=json&api_key=' . get_option('api_key'));
        $response_credits = json_decode(wp_remote_retrieve_body($response_credits));
        $args = array(
            'count_all' => true, //magic 
            'no_found_rows' => false,
            'numberposts' => $limit,
            'offset' => $offset,
            'post_type' => 'any',
            'meta_query' => array(
                array(
                    'key' => 'key_words',
                    'value' => '0',
                    'compare' => '>'
                )
             ) 
        );
        $obj = new WP_Query( $args );
        $all = $obj->found_posts;
        $last_page_num = ceil($all/$limit);
        $posts_meta = $obj->posts;
        $next_page_num = $page_num + 1;
        if($next_page_num > $last_page_num) {
            $next_page_num = $last_page_num;
        }
        $prev_page_num = $page_num - 1;
        if($prev_page_num<1) {
            $prev_page_num = 1;
        }        
        ?>
        <div class="keyword-activity-wrapper">
            <div class="keyword-tabs-btn-wrapper">
              <button class="keyword-tabs-btn active" data-keyword-tab-item="keyword-collector-logs"><?php echo __("Logs"); ?></button>
              <button class="keyword-tabs-btn" data-keyword-tab-item="keyword-collector-statistic"><?php echo __("Statistic"); ?></button>
            </div>
            <div class="logs keyword-tabs active" data-keyword-tab-target="keyword-collector-logs">
              <h1><?php echo __("Logs"); ?></h1>
              <textarea readonly id="keyword-logs-textarea" class="logs-input"><?php echo $logs?></textarea>
              <button id="keyword-logs-clear" class="keyword-clear-btn button button-primary button-large"><?php echo __("Clear Logs"); ?></button>
              <button id="keyword-logs-update" class="keyword-update-btn button button-primary button-large"><?php echo __("Update Logs"); ?></button>
            </div>
            <div class="statistic keyword-tabs" data-keyword-tab-target="keyword-collector-statistic">
              <h1><?php echo __("Statistic"); ?></h1>
              <div class="credits-wrapper">
                <h3><?php echo __("Credits Left"); ?>:&nbsp&nbsp<div class="credits-label"><?php echo $response_credits->answer[0]->credits[0]->value; ?></div></h3>
              </div>   
              <table class="stat-table" cellspacing="0">
                <thead>
                    <th><?php echo __("№/Post ID"); ?></th>
                    <th><?php echo __("Post Type"); ?></th>
                    <th><?php echo __("Shortcode"); ?></th>
                    <th><?php echo __("Tags"); ?></th>
                    <th><?php echo __("Post/Page Title"); ?></th>
                    <th><?php echo __("Saved Keywords"); ?></th>
                    <th><?php echo __("Keyword Save Date"); ?></th>
                    <th><?php echo __("Keyword Use Date"); ?></th>
                </thead>
                <tbody>
                    <?php foreach ($posts_meta as $k => $post) { 
                        $post_words = unserialize(get_post_meta($post->ID, 'key_words',true));
                        if (!is_array($post_words)) {
                            $post_words = unserialize($post_words);
                        } 
                        if ($post_words) {
                            $shortcode_flag = __("No");
                            if (strpos($post->post_content, "[keywords_collector]")) {
                                $shortcode_flag = __("Yes");
                            }
                            $words_string = "";
                            $tag_flag = __("No");
                            foreach ($post_words as $l => $word) {
                                $words_string .= $word->kw;
                                if ($l != sizeof($post_words)-1) {
                                    $words_string .= ", ";    
                                }
                                if (has_tag($word->kw, $post)) {
                                    $tag_flag = __("Yes");
                                }
                            } ?>
                            <tr>
                                <td><?php echo $offset+$k+1 . "/" . $post->ID; ?></td>
                                <td><?php echo $post->post_type; ?></td>
                                <td><?php echo $shortcode_flag; ?></td>
                                <td><?php echo $tag_flag; ?></td>
                                <td><a class="post-link" href="<?php echo get_permalink($post->ID); ?>"><?php echo $post->post_title; ?></a></td>
                                <td><?php echo $words_string; ?></td>
                                <td><?php echo date("d.m.y", strtotime($post_words[0]->date_add)); ?></td>
                                <td><?php echo date("d.m.y", strtotime($post_words[0]->date_use)); ?></td>
                            </tr>    
                        <?php }        
                    } ?>
                </tbody>
              </table>
              
              <script type="text/javascript">
                jQuery(function($){
                   if(location.hash!='') {
                       $('[data-keyword-tab-item="'+location.hash.replace('#','')+'"]').click();
                   } 
                });
              </script>
              <?php if($all>$limit) { ?>
              <div class="keyword-tabs__paginator"> 
                  <div class="tablenav">
                    <div class="tablenav-pages">
                          <span class="pagination-links">
                                <?php if($page_num>1) { ?>
                                <a class="tablenav-pages-navspan" href="<?php echo admin_url('admin.php?page=keyword_collector&paged=1#keyword-collector-statistic'); ?>">«</a>
                                <?php } else { ?>
                                <span class="tablenav-pages-navspan">«</span>
                                <?php } ?>
                                
                                <?php if($page_num != 1) { ?>
                                <a class="tablenav-pages-navspan" href="<?php echo admin_url('admin.php?page=keyword_collector&paged='.$prev_page_num.'#keyword-collector-statistic'); ?>">‹</a>
                                <?php } else { ?>
                                <span class="tablenav-pages-navspan">‹</span>
                                <?php } ?>
                                
                                <span class="screen-reader-text">Current Page</span>
                                <span id="table-paging" class="paging-input">
                                    <span class="tablenav-paging-text"><?php echo $page_num; ?> of <span class="total-pages"><?php echo  $last_page_num; ?></span>
                                    </span>
                                </span>
                                
                                
                                <?php if($page_num != $last_page_num) { ?>
                                <a class="next-page" href="<?php echo admin_url('admin.php?page=keyword_collector&paged='.$next_page_num.'#keyword-collector-statistic'); ?>">
                                    <span class="screen-reader-text">Next page</span><span aria-hidden="true">›</span>
                                </a>
                                <?php } else { ?>
                                <span class="tablenav-pages-navspan">›</span>
                                <?php } ?>
                                
                                
                                <?php if($page_num != $last_page_num) { ?>
                                <a class="last-page" href="<?php echo admin_url('admin.php?page=keyword_collector&paged='.$last_page_num.'#keyword-collector-statistic'); ?>">
                                    <span class="screen-reader-text">Last page</span><span aria-hidden="true">»</span>
                                </a>
                                <?php } else { ?>
                                <span class="tablenav-pages-navspan">»</span>
                                <?php } ?>
                          </span>
                    </div>
                  </div>
                
              </div>
              <?php } ?>
            </div>
        </div>
        <?php
    }

    public function WPKeyWordSettingsJs() {
        ?>
        <script type="text/javascript">
            if (!window.jQuery) {
                var script = document.createElement("SCRIPT");
                script.src = 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js';
                script.type = 'text/javascript';
                document.getElementsByTagName("head")[0].appendChild(script);
                var checkReady = function(callback) {
                    if (window.jQuery) {
                        callback(jQuery);
                    }
                    else {
                        window.setTimeout(function() { checkReady(callback); }, 20);
                    }
                };
                checkReady(function(jQuery) {
                    jQuery(function() {
                        attachHandlers()
                    });
                });
            }else{
                attachHandlers()
            }
            function attachHandlers() {
                jQuery(document).ready(function () {
                    jQuery(document).on('change', '#WPKeyWordSettingsForm', function () {
                        jQuery('#wpKeyWord-settings-save').prop('disabled', false);
                    });
                    jQuery(document).on('click', '#wpKeyWord-settings-save', function () {
                        var before_list = jQuery('#before-list').val();
                        var after_list = jQuery('#after-list').val();
                        var before_items = jQuery('#before-items').val();
                        var after_items = jQuery('#after-items').val();
                        var num = jQuery('#item-num').val();
                        var api_key = jQuery('#sistrix-api-key').val();
                        var update_interval = jQuery('#update-interval').val();
                        var delete_interval = jQuery('#delete-interval').val();
                        var country_shortcode = jQuery('#country-shortcode').val();
                        var custom_field_name = jQuery('#custom-field-name').val();
                        var auto_insert_post = jQuery('#auto-insert-post').is(':checked');
                        var auto_insert_page = jQuery('#auto-insert-page').is(':checked');
                        var auto_insert_firma = jQuery('#auto-insert-firma').is(':checked');
                        var auto_tag_insert = jQuery('#auto-tag-insert').is(':checked');
                        var data = {
                            action: 'WPKeyWordSettings',
                            WpKeyWordSettings: {
                                before_list: before_list,
                                after_list: after_list,
                                before_items: before_items,
                                after_items: after_items,
                                num: num,
                                api_key: api_key,
                                update_interval: update_interval,
                                delete_interval: delete_interval,
                                country_shortcode: country_shortcode,
                                custom_field_name: custom_field_name,
                                auto_insert_post: auto_insert_post,
                                auto_insert_page: auto_insert_page,
                                auto_insert_firma: auto_insert_firma,
                                auto_tag_insert: auto_tag_insert,
                            }
                        };
                        jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function (response) {
                            if (response) {
                                jQuery('#wpKeyWord-setings-success').fadeIn();
                                setTimeout(function () {
                                    jQuery('#wpKeyWord-setings-success').fadeOut();
                                }, 1000);
                            }
                        });
                    })
                    .on('click', '#keyword-logs-clear', function () {
                        var data = {
                            action: 'WPKeyWordClearLogs',
                        };
                        jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function (response) {
                            if (response) {
                                jQuery('#keyword-logs-textarea').val("");
                            }
                        });
                    })
                    .on('click', '#keyword-logs-update', function () {
                        var data = {
                            action: 'WPKeyWordUpdateLogs',
                        };
                        jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function (response) {
                            if (response) {

                                jQuery('#keyword-logs-textarea').val(response);
                            }
                        });
                    });
                });
            }
        </script>
        <?php
    }

    public function WPKeyWordSettings() {

        if (!empty($_POST['WpKeyWordSettings'])) {          
            update_option("before_list", $_POST['WpKeyWordSettings']['before_list']);
            update_option("after_list", $_POST['WpKeyWordSettings']['after_list']);
            update_option("before_items", $_POST['WpKeyWordSettings']['before_items']);
            update_option("after_items", $_POST['WpKeyWordSettings']['after_items']);
            update_option("num", $_POST['WpKeyWordSettings']['num']);
            update_option("api_key", $_POST['WpKeyWordSettings']['api_key']);
            update_option("update_interval", $_POST['WpKeyWordSettings']['update_interval']);
            update_option("delete_interval", $_POST['WpKeyWordSettings']['delete_interval']);
            update_option("country_shortcode", $_POST['WpKeyWordSettings']['country_shortcode']);
            update_option("custom_field_name", $_POST['WpKeyWordSettings']['custom_field_name']);
            $auto_insert_post_bool = $_POST['WpKeyWordSettings']['auto_insert_post'] === 'true'? true: false;
            $auto_insert_page_bool = $_POST['WpKeyWordSettings']['auto_insert_page'] === 'true'? true: false;
            $auto_insert_firma_bool = $_POST['WpKeyWordSettings']['auto_insert_firma'] === 'true'? true: false;
            $auto_tag_insert_bool = $_POST['WpKeyWordSettings']['auto_tag_insert'] === 'true'? true: false;
            update_option("auto_insert_post", $auto_insert_post_bool);
            update_option("auto_insert_page", $auto_insert_page_bool);
            update_option("auto_insert_firma", $auto_insert_firma_bool);
            update_option("auto_tag_insert", $auto_tag_insert_bool);
            //$this->check_auto_insert();
            echo true;
        } else
            echo false;
        wp_die();
    }
    public function WPKeyWordClearLogs() {
        file_put_contents(plugin_dir_path(__FILE__) . "../logs/activity_logs.txt", " ");
        echo true;
        wp_die();
    }
    public function WPKeyWordUpdateLogs() {
        $logs = file(plugin_dir_path(__FILE__) . "../logs/activity_logs.txt");
        $logs = array_reverse($logs);
        $logs = implode("", $logs);
        echo $logs;
        wp_die();
    }
    // public function check_auto_insert() {
    //     $args = array(
    //       'post_type' => "any",
    //     );
    //     $all_posts = get_posts($args);
    //     foreach ($all_posts as $k => $post) {
    //         if (!get_option("auto_tag_insert")) {
    //             $keywords = unserialize(get_post_meta($post->ID, 'key_words',true));
    //             if ($keywords) {
    //                 $key_word_id = array();
    //                 foreach ($keywords as $l => $word) {
    //                     if (has_tag($word->kw, $post)) {
    //                         $key_word_id[] = str_replace(" ", "-", $word->kw);
    //                     }  
    //                 }
    //                 wp_remove_object_terms( $post->ID, $key_word_id, 'post_tag' );
    //             }
    //         } else if(get_option("auto_tag_insert")){
    //             $keywords = unserialize(get_post_meta($post->ID, 'key_words',true));
    //             if ($keywords) {
    //                 foreach ($keywords as $l => $word) {
    //                     wp_set_post_tags($post->ID, $word->kw, true);
    //                 }
    //             }
    //         }   
    //         if (get_option("auto_insert_page") && $post->post_type == "page") {
    //           $this->edit_short_code($post, "add");
    //         } else if (!get_option("auto_insert_page") && $post->post_type == "page"){
    //           $this->edit_short_code($post, "delete");
    //         }
    //         if (get_option("auto_insert_post") && $post->post_type == "post") {
    //           $this->edit_short_code($post, "add");
    //         } else if (!get_option("auto_insert_post") && $post->post_type == "post") {
    //           $this->edit_short_code($post, "delete");
    //         }
    //         if (get_option("auto_insert_firma") && $post->post_type == "firma") {
    //           $this->edit_short_code($post, "add");
    //         } else if (!get_option("auto_insert_firma") && $post->post_type == "firma") {
    //           $this->edit_short_code($post, "delete");
    //         }
    //     }
    // }
    // public function edit_short_code($post, $type) {
    //     var_dump($post->post_title);
    //   switch ($type) {
    //     case 'add':
    //         if ($post->post_content != "") {
    //             preg_match("(\[keywords_collector\])", $post->post_content, $matches);
    //             if (sizeof($matches) == 0) {
    //               $post->post_content .= "[keywords_collector]";
    //               return wp_update_post( $post );
    //             } else {
    //               return false;
    //             }
    //         } else {
    //             var_dump($post->post_title);
    //             $post->post_content .= "[keywords_collector]";
    //             return wp_update_post( $post );
    //         }
    //         break;
        
    //     case 'delete':
    //       preg_match("(\[keywords_collector\])", $post->post_content, $matches);
    //       if (sizeof($matches) > 0) {
    //         $post->post_content = str_replace("[keywords_collector]", "", $post->post_content);
    //         return wp_update_post( $post );
    //       } else {
    //         return false;
    //       }
    //       break;

    //     default:
    //       return false;
    //       break;
    //   }
    // }
}