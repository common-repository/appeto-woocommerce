<?php
/*
 * Plugin Name: Appeto
 * Plugin URI: https://wordpress.org/plugins/appeto-woocommerce/
 * Description: اتصال وردپرس به اپلیکیشن و امکانات ویژه رابط بین اپلیکیشن و فروشگاه ساز ووکامرس - اپتو
 * Author: APPETO TM
 * Version: 4.0.3
 * Author URI: http://appeto.ir
 * License: appeto.ir users
 */

/* woocommerce */
require_once 'api/api.php';
new appeto_AddRulesWoo();
new appeto_browserAddToCard();
function appeto_add_cors_http_header() {
    if(!headers_sent()) {
        header("Access-Control-Allow-Origin: *");
    }
}
add_action('init','appeto_add_cors_http_header');

register_activation_hook(__FILE__, "appeto_woo_active");
register_uninstall_hook(__FILE__, "appeto_woo_remove");

function appeto_woo_active() {
    $length = 10;
    $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_*!@"), 0, $length);
    $secure_key = get_option('appeto_secure_key_woo');
    if($secure_key == '') {
        update_option('appeto_secure_key_woo', $randomString);
    }
}

function appeto_woo_remove() {
    delete_option('appeto_secure_key_woo');
    /*v2*/
    delete_option('appeto_woo_signup_form_app');
    delete_option('appeto_woo_signup_form_mobile');
    delete_option('appeto_woo_signup_form_company');
    delete_option('appeto_woo_signup_form_state');
    delete_option('appeto_woo_signup_form_city');
    delete_option('appeto_woo_signup_form_address');
    delete_option('appeto_woo_signup_form_address2');
    delete_option('appeto_woo_signup_form_postalcode');
    delete_option('appetoLoginNonce');
}

add_filter( 'woocommerce_general_settings', 'appeto_add_secure_key_setting' );
function appeto_add_secure_key_setting( $settings ) {
    $updated_settings = array();
    foreach ( $settings as $section ) {
        // at the bottom of the General Options section
        if ( isset( $section['id'] ) && 'general_options' == $section['id'] &&
            isset( $section['type'] ) && 'sectionend' == $section['type']
        ) {
            $secure_key = get_option('appeto_secure_key_woo');
            $updated_settings[] = array(
                'name'     => __( 'کلید امنیتی اپلیکیشن', 'wc_appeto_secure' ),
                'desc_tip' => '',
                'id'       => 'appeto_secure_key_woo',
                'type'     => 'text',
                'css'      => 'min-width:300px; direction: ltr; text-align: right;"',
                'std'      => $secure_key,  // WC < 2.0
                'default'  => $secure_key,  // WC >= 2.0
                'desc'     => __( 'این کلید را در پنل اپتو برای افزونه ووکامرس وارد کنید', 'wc_appeto_secure_desc' ),
            );
        }
        $updated_settings[] = $section;
    }
    return $updated_settings;
}

add_action( 'admin_head', 'appeto_woo_admin_js' );
function appeto_woo_admin_js(){
    if( is_admin() and isset($_GET["page"]) and $_GET["page"] == "wc-settings")
    {
?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('appeto_secure_key_woo').setAttribute('readonly', 'readonly');
        }, false);
    </script>
<?php
    }
}

function appeto_woo_review_ago_time($time_ago){
    $cur_time     = time();
    $time_elapsed     = $cur_time - $time_ago;
    $seconds     = $time_elapsed ;
    $minutes     = round($time_elapsed / 60 );
    $hours         = round($time_elapsed / 3600);
    $days         = round($time_elapsed / 86400 );
    $weeks         = round($time_elapsed / 604800);
    $months     = round($time_elapsed / 2600640 );
    $years         = round($time_elapsed / 31207680 );
    // Seconds
    if($seconds <= 60) {
        return "$seconds ثانیه قبل";
    }
    //Minutes
    else if($minutes <=60) {
        if($minutes==1){
            return "یک ماه پیش";
        }else{
            return "$minutes دقیقه قبل";
        }
    }
    //Hours
    else if($hours <= 24) {
        if($hours==1){
            return "یک ساعت قبل";
        }else{
            return "$hours ساعت قبل";
        }
    }
    //Days
    else if($days <= 7) {
        if($days==1){
            return "دیروز";
        }else{
            return "$days روز قبل";
        }
    }
    //Weeks
    else if($weeks <= 4.3) {
        if($weeks==1){
            return "یک هفته پیش";
        }else{
            return "$weeks هفته پیش";
        }
    }
    //Months
    else if($months <=12) {
        if($months==1){
            return "یک ماه پیش";
        }else{
            return "$months ماه پیش";
        }
    }
    //Years
    else{
        if($years==1){
            return "یک سال پیش";
        }else{
            return "$years سال پیش";
        }
    }
}

/* V2 */
if( is_admin() ){
    $appeto_woo_signup_form_mobile = get_option('appeto_woo_signup_form_mobile');
    if(!$appeto_woo_signup_form_mobile || $appeto_woo_signup_form_mobile == '') {
        update_option('appeto_woo_signup_form_mobile', 'yes');
        update_option('appeto_woo_signup_form_company', 'yes');
        update_option('appeto_woo_signup_form_state', 'yes');
        update_option('appeto_woo_signup_form_city', 'yes');
        update_option('appeto_woo_signup_form_address', 'yes');
        update_option('appeto_woo_signup_form_address2', 'yes');
        update_option('appeto_woo_signup_form_postalcode', 'yes');
    }
    require_once 'api/lib/woocommerce-settings-tab-appeto.php';
    WC_Settings_Tab_Appeto::init();
}

/* wordpress */

$active_plugins = get_option('active_plugins', array());

define('APT_IS_WOO_ACTIVE', in_array('woocommerce/woocommerce.php', $active_plugins));

if(!in_array('json-api/json-api.php', $active_plugins)) {
    require_once 'json-api/json-api.php';
}
if(!in_array('wp-cors/wp-cors.php', $active_plugins)) {
    require_once 'wp-cors/wp-cors.php';
}

if(!in_array('appeto/index.php', $active_plugins)) {
    add_action( 'json_api', function( $controller, $method )
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST');
        header("Access-Control-Allow-Headers: X-Requested-With");
    }, 10, 2 );
    function appeto_add_custom_post_type_categories_controller($controllers) {
        $controllers[] = 'appeto';
        return $controllers;
    }
    add_filter('json_api_controllers', 'appeto_add_custom_post_type_categories_controller');

    function appeto_set_custom_post_type_categories_controller_path() {
        return plugin_dir_path(__FILE__)."controllers/custom_post_type_categories.php";
    }
    add_filter('json_api_appeto_controller_path', 'appeto_set_custom_post_type_categories_controller_path');
    require_once 'controllers/menu.php';
    require_once 'controllers/notifications.php';
    add_action('admin_menu', 'appeto_admin_menu::appeto_add_menu_item');


    function appeto_register_meta_boxes() {
        add_meta_box( 'appeto-send-notification ', 'ارسال نوتیفیکیشن - اپتو', 'appeto_display_notification_callback', 'post' );
        $deny_types = array();
        $args = array(
            'public'   => true,
            '_builtin' => false
        );
        $output = 'array';
        $operator = 'and';
        $post_types = get_post_types( $args, $output, $operator );
        if( is_array( $post_types ) ) {
            if (!empty($post_types)) {
                foreach ($post_types as $post_type) {
                    if (!in_array($post_type->name, $deny_types)) {
                        add_meta_box( 'appeto-send-notification ', 'ارسال نوتیفیکیشن - اپتو', 'appeto_display_notification_callback',  $post_type->name, 'side', 'high' );
                    }
                }
            }
        }
    }
    add_action( 'add_meta_boxes', 'appeto_register_meta_boxes' );

    function appeto_display_notification_callback( $post ) {
        ?>
        <p style="background-color: #206ea1; padding: 5px; border: 1px solid #154161; color: #ffffff; text-align: center">
            در این بخش میتوانید همزمان با انتشار/ویرایش این پست به کاربرانتان نوتیفیکیشن ارسال کنید.
        </p>
        <fieldset>
            <label for="appeto_push_active_after_post">
                <input name="appeto_push_active_after_post" onchange="if(document.getElementById('appeto_post_push_settings').style.display == 'none') { document.getElementById('appeto_post_push_settings').style.display='block'} else { document.getElementById('appeto_post_push_settings').style.display='none'}" type="checkbox" id="appeto_push_active_after_post" >
                بعد از انتشار/ویرایش این مطلب نوتیفیکیشن را ارسال کن
            </label>
        </fieldset>

        <div id="appeto_post_push_settings" style="display: none">
            <hr />
            <label for="appeto_push_devices">نوع دستگاه</label>
            <br/>
            <select name="appeto_push_devices" style="width: 100%">
                <option value="all">برای کاربران اندروید و iOS</option>
                <option value="android">فقط کاربران اندروید</option>
                <option value="ios">فقط کاربران iOS</option>
            </select>
            <hr />
            <fieldset>
                <label for="appeto_push_url">
                    <input name="appeto_push_url" type="checkbox" id="appeto_push_url" >
                    لینک به این پست
                </label>
            </fieldset>
            <small class="help">در صورت انتخاب این گزینه بعد از باز شدن نوتیفیکیشن این پست در مرورگر باز خواهد شد.</small>
            <hr />
            <label for="appeto_push_title">عنوان - حداکثر 100 کاراکتر</label>
            <br/>
            <input type="text" id="appeto_push_title" name="appeto_push_title" style="width: 100%">
            <small class="help">در صورت خالی گذاشتن این بخش عنوان مطلب انتخاب خواهد شد.</small>
            <hr />
            <label for="appeto_push_desc">توضیحات - حداکثر 100 کاراکتر</label>
            <br/>
            <input type="text" id="appeto_push_desc" name="appeto_push_desc" style="width: 100%">
            <small class="help">در صورت خالی گذاشتن این بخش، از 100 کاراکتر اول توضیحات استفاده خواهد شد.</small>
        </div>

    <?php
    }

    function appeto_save_meta_box( $post_id, $post ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return;
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
            return;
        if ( ! current_user_can( 'edit_post', $post_id ) )
            return;
        if ( false !== wp_is_post_revision( $post_id ) )
            return;

        if(isset($_POST['appeto_push_active_after_post']) and $_POST['appeto_push_active_after_post'] != null) {
            $url = "";
            $title = substr($post->post_title, 0, 100);
            $desc = substr(strip_tags($post->post_content), 0, 100);
            $device = "all";
            if(isset($_POST['appeto_push_url']) and $_POST['appeto_push_url'] != null) {
                $url = $post->guid;
            }
            if(isset($_POST['appeto_push_title']) and $_POST['appeto_push_title'] != "") {
                $title = $_POST['appeto_push_title'];
            }
            if(isset($_POST['appeto_push_desc']) and $_POST['appeto_push_desc'] != "") {
                $desc = $_POST['appeto_push_desc'];
            }
            if(isset($_POST['appeto_push_devices'])) {
                $device = $_POST['appeto_push_devices'];
            }
            $notifications = new appeto_push_notification();
            $notifications->send($device, $url, $title, $desc);
        }
    }
    add_action( 'save_post', 'appeto_save_meta_box', 1, 2);

    function appeto_custom_get_terms($term) {
        global $wpdb;
        $out = array();
        $a = $wpdb->get_results($wpdb->prepare("SELECT t.name,t.slug,t.term_group,x.term_taxonomy_id,x.term_id,x.taxonomy,x.description,x.parent,x.count FROM {$wpdb->prefix}term_taxonomy x LEFT JOIN {$wpdb->prefix}terms t ON (t.term_id = x.term_id) WHERE x.taxonomy=%s;",$term));
        foreach ($a as $b) {
            $obj = new stdClass();
            $obj->term_id = $b->term_id;
            $obj->name = $b->name;
            $obj->slug = $b->slug;
            $obj->term_group = $b->term_group;
            $obj->term_taxonomy_id = $b->term_taxonomy_id;
            $obj->taxonomy = $b->taxonomy;
            $obj->description = $b->description;
            $obj->parent = $b->parent;
            $obj->count = $b->count;
            $out[] = $obj;
        }
        return $out;
    }
}

function appeto_change_location($url) {
    if(!headers_sent()) {
        header("Location: ".$url);
    }
    else {
        echo '<script>document.location.href = "'.$url.'";</script>';
    }
    exit;
}

/* Login/register */
new appeto_auth();
/* woocommrece network */
function appeto_get_network_sites() {
    $result = array();
    if(function_exists('get_sites')) {
        $sites = get_sites();
    }
    elseif(function_exists('wp_get_sites')) {
        $sites = wp_get_sites();
    }
    else {
        $sites = array();
    }
    foreach($sites as $site) {
        if($site->path == "/") continue;
        if($site->deleted == 1) continue;
        $blog_details = get_blog_details( array( 'blog_id' => $site->blog_id ) );
        $r["id"] = $site->blog_id;
        $r["applink"] = base64_encode(json_encode(array("id" => $site->blog_id, "path" => $blog_details->path, "siteurl" => $blog_details->siteurl)));
        $r["name"] = $blog_details->blogname;
        $r["path"] = $blog_details->path;
        $r["siteurl"] = $blog_details->siteurl;
        $r["image"] = get_option("appeto_ntsite_img_".$site->blog_id, "");
        array_push($result, $r);
    }
    return $result;
}

function appeto_encode_this_key( $txt, $hashKey )
{
    if(function_exists("mcrypt_encrypt")) {
        return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($hashKey), $txt, MCRYPT_MODE_CBC, md5(md5($hashKey))));
    }
    return base64_encode($txt);
}


/* Version 4.0.0 */
function aptConvertIcon($icon) {
    if($icon == "") return $icon; //$icon = "fa-address-book";
    $icon = str_replace("-", "_", $icon);
    return $icon;
}
function aptIntegrationColors($color) {

    if( $color == "" )
        return "#FFFFFFFF";

    if(strpos($color, "rgba") !== FALSE) {
        $color = str_replace("rgba(", "", $color);
        $color = str_replace(")", "", $color);
        $color = explode(", ", $color);
        $r = aptToHex($color[0]);
        $g = aptToHex($color[1]);
        $b = aptToHex($color[2]);
        if(count($color) > 3) {
            $a = $color[3];
            $hex = "FF";
            for($i = 1; $i >= 0; $i -= 0.01) {
                $i = round($i * 100) / 100;
                if($i == $a) {
                    $alpha = round($i * 255);
                    $hex = dechex($alpha);
                    break;
                }
            }
            $color = "#".$hex.$r.$g.$b;
        }
        else {
            $color = "#FF".$r.$g.$b;
        }
    }
    else if(strpos($color, "rgb") !== FALSE) {
        $color = str_replace("rgb(", "", $color);
        $color = str_replace(")", "", $color);
        $color = explode(", ", $color);
        $r = aptToHex($color[0]);
        $g = aptToHex($color[1]);
        $b = aptToHex($color[2]);
        $color = "#FF".$r.$g.$b;
    }
    else if($color == "transparent") {
        $color = "#00000000";
    }
    else {
        if ($color[0] == '#' ) {
            $color = substr( $color, 1 );
        }
        $color = "#FF".$color;
    }

    return $color;
}
/* hex to rgb */
function aptHexRgb($hex) {
    list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
    return array($r, $g, $b);
}

/* rgb to hex */
function aptToHex($n) {
    $n = intval($n);
    if (!$n)
        return '00';

    $n = max(0, min($n, 255)); // make sure the $n is not bigger than 255 and not less than 0
    $index1 = (int) ($n - ($n % 16)) / 16;
    $index2 = (int) $n % 16;

    return substr("0123456789ABCDEF", $index1, 1)
        . substr("0123456789ABCDEF", $index2, 1);
}

if(is_admin()) {
    require_once 'controllers/APT_CT_TAX_META.php';
    new APT_CT_TAX_META();
}
require_once  'controllers/apt_api.php';
$aptApi = new AptApi();
