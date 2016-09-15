<?php
/**
 * File facebook.php
 * @package atmosphere
 * @user pn
 * @version 1.0
 */

define("APP_SECRET",'4996c1beaa2b2ace5605368b96f2d1e8');

define("APP_ID",'1459162104359320');
define("PAGE_ID",'atmospherebistro');

define("GRAPH_VER",'v2.7');
define('BEFORE_WIDGET', '<aside id="%1$s" class="widget %2$s">');
define('AFTER_WIDGET', '</aside>');

define('BEFORE_TITLE', '<h3 class="wg-title"><span>');
define('AFTER_TITLE', '</span></h3>');

if ( ! defined( 'ABSPATH' ) ) { exit; }

require 'vendor/autoload.php';

/* handle the result */

class ZO_Facebook_Widget extends WP_Widget
{
    function __construct()
    {
        parent::__construct(
            'zo_facebook_widget', // Base ID
            __('ZO Facebook', 'fajar'), // Name
            array('description' => __('ZO Facebook Widget', 'fajar'),) // Args
        );
    }

    function widget($args, $instance)
    {
        extract($args);
        $title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
        $appId = empty($instance['app_id']) ? '' : $instance['app_id'];
        $appSecret = empty($instance['app_secret']) ? '' : $instance['app_secret'];
        $limit = empty($instance['number']) ? 2 : $instance['number'];
        $columns = empty($instance['columns']) ? 2 : $instance['columns'];
        $extra_class = empty($instance['extra_class']) ? '' : $instance['extra_class'];
        switch ($columns) {
            case 1:
                $span = "col-xs-12 col-sm-12 col-md-12 col-lg-12";
                break;
            case 2:
                $span = "col-xs-6 col-sm-6 col-md-6 col-lg-6";
                break;
            case 3:
                $span = "col-xs-4 col-sm-4 col-md-4 col-lg-4";
                break;
            case 4:
                $span = "col-xs-3 col-sm-3 col-md-3 col-lg-3";
                break;
            default:
                $span = "col-xs-6 col-sm-6 col-md-6 col-lg-6";
        }
        echo do_shortcode(BEFORE_WIDGET);
        if (!empty($title)) {
            echo do_shortcode(BEFORE_TITLE . $title . AFTER_TITLE);
        }
        $posts = $this->scrape_facebook($appId, $appSecret, $limit);
        ?>
        <?php if (is_wp_error($posts)) : ?>
            <?php echo do_shortcode($posts->get_error_message()); ?>
        <?php else : ?>
        <div class="zo-facebook clearfix row <?php echo esc_attr($extra_class); ?>">
            <div id="fb-root"></div>
            <script>(function (d, s, id) {
                    var js, fjs = d.getElementsByTagName(s)[0];
                    if (d.getElementById(id)) return;
                    js = d.createElement(s);
                    js.id = id;
                    js.src = "//connect.facebook.net/vi_VN/sdk.js#xfbml=1&version=v2.7&appId=" + <?php echo APP_ID; ?>;
                    fjs.parentNode.insertBefore(js, fjs);
                }(document, 'script', 'facebook-jssdk'));
            </script>
            <?php foreach ($posts as $post) : ?>
                <div class='fb-post' style="display: inline" data-href='<?php echo $post; ?>' data-width='50'></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php echo do_shortcode(AFTER_WIDGET);
    }

    function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['app_id'] = trim(strip_tags($new_instance['app_id']));
        $instance['app_secret'] = trim(strip_tags($new_instance['app_secret']));
        $instance['number'] = !absint($new_instance['number']) ? 9 : $new_instance['number'];
        $instance['columns'] = !absint($new_instance['columns']) ? 3 : $new_instance['columns'];
        $instance['extra_class'] = $new_instance['extra_class'];
        return $instance;
    }

    function form($instance)
    {
        $instance = wp_parse_args((array)$instance, array('title' => __('facebook', 'fajar'), 'username' => '', 'api' => '', 'link' => __('Follow Us', 'fajar'), 'number' => 9, 'columns' => 3, 'size' => 'thumbnail', 'target' => '_self'));
        $title = esc_attr($instance['title']);
        $appId = esc_attr($instance['app_id']);
        $appSecret = esc_attr($instance['app_secret']);
        $number = absint($instance['number']);
        $columns = absint($instance['columns']);
        $link = esc_attr($instance['link']);
        $extra_class = isset($instance['extra_class']) ? esc_attr($instance['extra_class']) : '';
        ?>
        <p><label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title', 'fajar'); ?>: <input
                    class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                    name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text"
                    value="<?php echo esc_attr($title); ?>"/></label>
        </p>
        <p><label for="<?php echo esc_attr($this->get_field_id('app_id')); ?>"><?php _e('App ID', 'fajar'); ?>: <input
                    class="widefat" id="<?php echo esc_attr($this->get_field_id('app_id')); ?>"
                    name="<?php echo esc_attr($this->get_field_name('app_id')); ?>" type="text"
                    value="<?php echo esc_attr($appId); ?>"/></label>
        </p>
        <p><label for="<?php echo esc_attr($this->get_field_id('app_secret')); ?>"><?php _e('App Secret', 'fajar'); ?>:
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('app_secret')); ?>"
                       name="<?php echo esc_attr($this->get_field_name('app_secret')); ?>" type="text"
                       value="<?php echo esc_attr($appSecret); ?>"/></label>
        </p>
        <p><label for="<?php echo esc_attr($this->get_field_id('number')); ?>"><?php _e('Number of posts', 'fajar'); ?>
                : <input class="widefat" id="<?php echo esc_attr($this->get_field_id('number')); ?>"
                         name="<?php echo esc_attr($this->get_field_name('number')); ?>" type="text"
                         value="<?php echo esc_attr($number); ?>"/></label>
        </p>
        <p><label for="<?php echo esc_attr($this->get_field_id('columns')); ?>"><?php _e('Columns', 'fajar'); ?>: <input
                    class="widefat" id="<?php echo esc_attr($this->get_field_id('columns')); ?>"
                    name="<?php echo esc_attr($this->get_field_name('columns')); ?>" type="text"
                    value="<?php echo esc_attr($columns); ?>"/></label>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('extra_class')); ?>">Extra Class:</label>
            <input class="widefat" type="text" id="<?php echo esc_attr($this->get_field_id('extra_class')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('extra_class')); ?>"
                   value="<?php echo esc_attr($extra_class); ?>"/>
        </p>
        <?php
    }

    /**
     * @param $appId
     * @param $appSecret
     * @param int $limit
     * @return array
     */
    function scrape_facebook($appId, $appSecret, $limit = 2)
    {
        $appId = empty($appId) ? APP_ID : $appId;
        $appSecret = empty($appSecret) ? APP_SECRET : $appSecret;
        $config = array(
            'app_id' => $appId,
            'app_secret' => $appSecret,
            'default_graph_version' => GRAPH_VER
        );
        $facebook = new \Facebook\Facebook($config);
        try {
            $response = $facebook->get("/oauth/access_token?grant_type=client_credentials&client_id={$appId}&client_secret={$appSecret}", $appId);
            $graphObject = $response->getGraphObject();
            $posts = $facebook->get("/" . PAGE_ID . "/posts?fields=permalink_url&limit=" . $limit, $graphObject->getField('access_token'));
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
        $feeds = $posts->getDecodedBody();
        $permanentLinks = array();
        foreach ($feeds['data'] as $feed) {
            $permanentLinks[] = $feed['permalink_url'];
        }
        return $permanentLinks;
    }
}
/**
 * Class ZO_Facebook_Widget
 */

function register_facebook_widget() {
    register_widget('ZO_Facebook_Widget');
}

add_action('widgets_init', 'register_facebook_widget');
