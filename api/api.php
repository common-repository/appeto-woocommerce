<?php
class appeto_AddRulesWoo
{
    function __construct()
    {
        add_filter('rewrite_rules_array','appeto_AddRulesWoo::insertRules');
        add_filter('query_vars','appeto_AddRulesWoo::insertQueryVars');
        add_action('parse_query','appeto_AddRulesWoo::insertParseQuery');
    }

    static function insertRules($rules)
    {
        $newrules = array();
        $newrules['redirect/url/(.+)$']='index.php?appeto_api';
        return $newrules+$rules;
    }
    static function insertQueryVars($vars)
    {
        array_push($vars, 'appeto_api');
        return $vars;
    }
    static function insertParseQuery($query)
    {
        if(!empty($query->query_vars['appeto_api']) and $query->query_vars['appeto_api'] == "woocommerce")
        {
            if(isset($_GET["check_api"]) and $_GET["check_api"] == "info") {
                $result = array(
                    "status" => "no"
                );
                $plugins = get_option('active_plugins');
                if(in_array("woocommerce/woocommerce.php", $plugins)) {
                    $is_hard = false;
                    if(function_exists("mcrypt_encrypt")) {
                        $is_hard = true;
                    }
                    $result = array(
                        "status" => "ok",
                        "currency_symbol" => get_woocommerce_currency_symbol(),
                        "is_hard" => $is_hard
                    );
                }
                if(function_exists("is_plugin_active_for_network")) {
                    if ( is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) {
                        $is_hard = false;
                        if(function_exists("mcrypt_encrypt")) {
                            $is_hard = true;
                        }
                        $result = array(
                            "status" => "ok",
                            "currency_symbol" => get_woocommerce_currency_symbol(),
                            "is_hard" => $is_hard
                        );
                    }
                }
                self::jsonView($result);
            }

            if(isset($_GET["check_api"]) and $_GET["check_api"] == "network") {
                $result = appeto_get_network_sites();
                self::jsonView($result);
            }

            if(isset($_GET["check_api"]) and $_GET["check_api"] == "networkSearch" and isset($_GET["s"]) and $_GET["s"] != "") {
                global $wpdb;
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
                $q = esc_sql($_GET["s"]);
                foreach($sites as $site) {
                    if ($site->path == "/") continue;
                    if($site->deleted == 1) continue;
                    $bimage = get_option("appeto_ntsite_img_".$site->blog_id, "");
                    switch_to_blog($site->blog_id);
                    $posts = $wpdb->get_blog_prefix($site->blog_id)."posts";
                    $wp_postmeta = $wpdb->get_blog_prefix($site->blog_id)."postmeta";
                    $select = "SELECT p.id, p.post_title AS title, m.meta_value AS regular_price, pt.meta_value AS price FROM {$posts} p
                            INNER JOIN {$wp_postmeta} m ON (
                                 p.id = m.post_id
                            )
                            INNER JOIN {$wp_postmeta} pt ON (
                                 p.id = pt.post_id
                            )
                            WHERE
                             p.post_type = 'product'
                             AND
                            p.post_title LIKE '%".$q."%'
                            AND
                             p.post_status = 'publish'
                            AND
                             m.meta_key = '_regular_price'
                            AND
                             pt.meta_key = '_price'";
                    $_r = $wpdb->get_results($select);
                    if(!empty($_r)) {
                        $checker = array();
                        foreach ( $_r as $k => $post )
                        {
                            if(in_array($post->id, $checker)) {
                                unset($_r[$k]);
                                continue;
                            }
                            $image_url = get_the_post_thumbnail_url( $post->id );
                            if($image_url == "") {
                                $image_url = wc_placeholder_img_src();
                            }
                            $_r[$k]->img = $image_url;
                            $_r[$k]->site = $site->blog_id;
                            array_push($checker, $post->id);
                        }
                        $result[$site->blog_id]["result"] = $_r;
                        $blog_details = get_blog_details( array( 'blog_id' => $site->blog_id ) );
                        $r["id"] = $site->blog_id;
                        $r["applink"] = base64_encode(json_encode(array("id" => $site->blog_id, "path" => $blog_details->path, "siteurl" => $blog_details->siteurl)));
                        $r["name"] = $blog_details->blogname;
                        $r["path"] = $blog_details->path;
                        $r["siteurl"] = $blog_details->siteurl;
                        $r["image"] = $bimage;
                        $result[$site->blog_id]["info"] = $r;
                    }
                    restore_current_blog();
                }
                self::jsonView($result);
                exit;
            }

            if(isset($_GET["viewAppCart"]) and $_GET["viewAppCart"] == "true"
                and isset($_GET["layout"]) and isset($_GET["content_class"])
                and isset($_GET["ck"]) and isset($_GET["cs"]) and isset($_GET["slug"])) {
                $states = new WC_Countries();
                $iranStates = $states->get_states('IR');
                self::appView('cart', array(
                    "layout" => $_GET["layout"],
                    "content_class" => $_GET["content_class"],
                    "ck" => $_GET["ck"],
                    "cs" => $_GET["cs"],
                    "slug" => $_GET["slug"],
                    "states" => $iranStates
                ));
            }

            $result = array(
                "status" => "false",
                "msg" => "invalid command",
                "WC_MSG" => "",
                "response" => "",
                "currency_symbol" => get_woocommerce_currency_symbol()
            );
            $id = null;
            $args = array();
            $ck_link = "";
            $cs_link = "";
            if(isset($_GET["ck"]) and $_GET["ck"] != "" and
                isset($_GET["cs"]) and $_GET["cs"] != "" and
                isset($_GET["needed"]) and $_GET["needed"] != "") {
                $hashKey = get_option('appeto_secure_key_woo');
                $ck_link = $_GET["ck"];
                $cs_link = $_GET["cs"];
                $_GET["ck"] = self::decode_this_key(base64_decode($_GET["ck"]), $hashKey);
                $_GET["cs"] = self::decode_this_key(base64_decode($_GET["cs"]), $hashKey);
                $command = $_GET["needed"];
                $obj = "";
                $fnc = "";
                $createCustomer = false;
                $createOrder = false;
                $update_args = array();
                $user_email = "";

                self::parseAppCommand($command, $obj, $fnc);

                if(isset($_GET["arg1"]) and $_GET["arg1"] != "") {
                    $id = $_GET["arg1"];
                    if($id == "null" or $id == 0) {
                        $id = null;
                    }
                }
                if(isset($_GET["arg2"]) and $_GET["arg2"] != "") {
                    $_GET["arg2"] = str_replace("'", "\"", $_GET["arg2"]);
                    $args = json_decode(stripslashes(urldecode($_GET["arg2"])), true);
                    if($args == null) {
                        $args = array();
                    }
                }

                if($obj == "customers" and $fnc == "create") {
                    if(email_exists($args["email"])) {
                        $result["msg"] = "emailExists";
                        self::jsonView($result);
                    }

                    $username = explode("@", $args["email"]);
                    $username = $username[0];
                    if(username_exists($username)) {
                        $username = $username."_".time();
                    }
                    $new_args = array(
                        'email' => $args["email"],
                        'first_name' => $args["first_name"],
                        'last_name' => $args["last_name"],
                        'username' => $username,
                        'password' => $args["password"],
                        'billing' => $args,
                        'shipping' => array(
                            'first_name' => $args["first_name"],
                            'last_name' => $args["last_name"],
                            'company' => $args["company"],
                            'address_1' => $args["address_1"],
                            'address_2' => $args["address_2"],
                            'city' => $args["city"],
                            'state' => $args["state"],
                            'postcode' => $args["postcode"],
                            'country' => $args["country"]
                        )
                    );
                    $update_args = array(
                        'billing' => $args,
                        'shipping' => array(
                            'first_name' => $args["first_name"],
                            'last_name' => $args["last_name"],
                            'company' => $args["company"],
                            'address_1' => $args["address_1"],
                            'address_2' => $args["address_2"],
                            'city' => $args["city"],
                            'state' => $args["state"],
                            'postcode' => $args["postcode"],
                            'country' => $args["country"]
                        )
                    );
                    $args = $new_args;
                    $createCustomer = true;
                }

                if($obj == "customers" and $fnc == "get_by_email") {
                    if(wp_login($args["email"], $args["password"])) {
                        $user_email = $args["email"];
                        $args = $user_email;
                    }
                    else {
                        self::jsonView($result);
                    }
                }

                if($obj == "orders" and $fnc == "create") {
                    $checkKey = get_user_meta($args["customer_id"], 'appeto_woo_order_key', true);
                    if(isset($_GET["appeto_key"]) and $checkKey != "" and $checkKey == $_GET["appeto_key"]) {
                        $user_id = $args["customer_id"];
                        $new_args = array(
                            "customer_id" => $args["customer_id"],
                            "line_items" => $args["line_items"],
                            'billing_address' => array(
                                'first_name' => get_user_meta($args["customer_id"], "billing_first_name", true),
                                'last_name' => get_user_meta($args["customer_id"], "billing_last_name", true),
                                'address_1' => get_user_meta($args["customer_id"], "billing_address_1", true),
                                'address_2' => get_user_meta($args["customer_id"], "billing_address_2", true),
                                'city' => get_user_meta($args["customer_id"], "billing_city", true),
                                'state' => get_user_meta($args["customer_id"], "billing_state", true),
                                'postcode' => get_user_meta($args["customer_id"], "billing_postcode", true),
                                'country' => get_user_meta($args["customer_id"], "billing_country", true),
                                'email' => get_user_meta($args["customer_id"], "billing_email", true),
                                'phone' => get_user_meta($args["customer_id"], "billing_phone", true)
                            ),
                            'shipping_address' => array(
                                'first_name' => get_user_meta($args["customer_id"], "shipping_first_name", true),
                                'last_name' => get_user_meta($args["customer_id"], "shipping_last_name", true),
                                'address_1' => get_user_meta($args["customer_id"], "shipping_address_1", true),
                                'address_2' => get_user_meta($args["customer_id"], "shipping_address_2", true),
                                'city' => get_user_meta($args["customer_id"], "shipping_city", true),
                                'state' => get_user_meta($args["customer_id"], "shipping_state", true),
                                'postcode' => get_user_meta($args["customer_id"], "shipping_postcode", true),
                                'country' => get_user_meta($args["customer_id"], "shipping_country", true)
                            )
                        );
                        $args = $new_args;
                        $createOrder = true;
                    }
                    else {
                        self::jsonView($result);
                    }
                }


                /* RUN WOOCOMMERCE API */
                require_once "lib/woocommerce-api.php";
                $options = array(
                    'debug'           => false,
                    'return_as_array' => false,
                    'validate_url'    => false,
                    'timeout'         => 30,
                    'ssl_verify'      => false,
                );
                try {
                    $client = new WC_API_Client(
                        site_url(),
                        $_GET["ck"],
                        $_GET["cs"],
                        $options
                    );
                    $result["status"] = "true";
                    $result["msg"] = "valid command";
                    $result["comments"] = "";
                    $result["app_order_key"] = "";
                    if($id == -1) {
                        if($obj == "customers" and $fnc == "get_by_email" and $user_email != '') {
                            $user = get_user_by( 'email', $args );
                            if(isset($user->ID) and $user->ID > 0) {
                                $result["response"] = (object) array(
                                    "customer" => (object) array(
                                        "email" => $args,
                                        "id" => $user->ID
                                    )
                                );
                            }
                        }
                        else {
                            $result["response"] = $client->$obj->$fnc($args);
                        }
                        if($createCustomer and isset($result["response"]->customer->id) and $result["response"]->customer->id > 0) {
                            $user_id = $result["response"]->customer->id;
                            foreach($update_args as $key => $value) {
                                if(is_array($value)) {
                                    foreach($value as $mk => $mv) {
                                        update_user_meta($user_id, $key."_".$mk, $mv);
                                    }
                                }
                            }
                            $appeto_woo_order_key = md5($user_id.time().rand());
                            update_user_meta($user_id, "appeto_woo_order_key", $appeto_woo_order_key);
                            $result["app_order_key"] = $appeto_woo_order_key;
                        }
                        if($user_email != "" and isset($result["response"]->customer->id) and $result["response"]->customer->id > 0) {
                            $appeto_woo_order_key = md5($result["response"]->customer->id.time().rand());
                            update_user_meta($result["response"]->customer->id, "appeto_woo_order_key", $appeto_woo_order_key);
                            $result["app_order_key"] = $appeto_woo_order_key;
                        }
                        if($createOrder and isset($result["response"]->order->id) and $result["response"]->order->id > 0) {
                            delete_user_meta($user_id, 'appeto_woo_order_key');
                            $result["app_order_key"] = wc_get_checkout_url()."/".get_option('woocommerce_checkout_pay_endpoint')."/".$result["response"]->order->id."/?pay_for_order=true&key=".get_post_meta($result["response"]->order->id, "_order_key", true);
                            if(isset($_GET['e']) and isset($_GET['g']) and $_GET['e'] != '' and $_GET['g'] != '') {
                                $result["app_order_key"] = site_url().'/?appCardToSite='.$result["response"]->order->id."&e=".base64_encode($_GET['e'])."&g=".base64_encode($_GET['g']);
                            }
                        }
                    }
                    else {
                        $result["response"] = $client->$obj->$fnc($id, $args);
                    }
                    if($id != null and $obj == "products" and $fnc == "get") {
                        $result["comments"] = $client->products->get_reviews($id);
                    }
                } catch (WC_API_Client_Exception $e) {
                    $result["status"] = "false";
                    $result["WC_MSG"] = $e->getMessage();
                }
            }

            if(isset($_GET["view"]) and $_GET["view"] != "" and isset($_GET["extra"])) {
               $_GET["extra"] = stripslashes($_GET["extra"]);
               $_GET["extra"] = str_replace("'", "\"", $_GET["extra"]);
                $extra = json_decode($_GET["extra"]);
                if($extra == null) {
                    $extra = new stdClass();
                }
                $data = array(
                    'site_url' => site_url(),
                    'ck' => $ck_link,
                    'cs' => $cs_link,
                    'args1' => $id,
                    'args2' => $args,
                    'needed' => $_GET["needed"],
                    'result' => $result,
                    'extra' => $extra,
                );
                $page = str_replace("..", "", $_GET["view"]);
                $page = str_replace(".", "", $page);
                $page = str_replace("/", "", $page);
                self::appView($page, $data);
            }
            else {
                self::jsonView($result);
            }
        }
        else if(!empty($query->query_vars['appeto_api']) and $query->query_vars['appeto_api'] == "woocommerce-v2") {
            if(isset($_GET['view']) and $_GET['view'] != '' and isset($_GET["extra"])) {
                $_GET["extra"] = str_replace("::", "/", $_GET["extra"]);
                $_GET["extra"] = str_replace("^", "+", $_GET["extra"]);
                $_GET["extra"] = base64_decode($_GET["extra"]);
                $_GET["extra"] = str_replace("'", "\"", $_GET["extra"]);
                if(!class_exists("Services_JSON")) {
                    require_once 'JSON.php';
                    $json = new Services_JSON();
                }
                else {
                    $json = new Services_JSON();
                }

                $extra = $json->decode($_GET["extra"]);
                if($extra == null) {
                    $extra = new stdClass();
                }
                $page = str_replace("..", "", $_GET["view"]);
                $page = str_replace(".", "", $page);
                $page = str_replace("/", "", $page);
                $page = "/v2/".$page;

                self::appView($page, array(
                    'site_url' => site_url(),
                    'extra' => $extra
                ));
            }
        }
        else {
            /* DO NOTHING LIKE PATRIC :) */
        }
    }


    private static function parseAppCommand($command, &$obj, &$fnc) {
        $command = explode("/", $command);
        if(count($command) < 2) {
            self::jsonView();
        }
        $_obj = @$command[0];
        $_fnc = @$command[1];
        $allowed = self::getAllowCommands();
        if(isset($allowed[$_obj]) and in_array($_fnc, $allowed[$_obj])) {
            $obj = $_obj;
            $fnc = $_fnc;
        }
        else {
            self::jsonView();
        }
    }

    private static function getAllowCommands() {
        return array(
            'products' => array(
                'get',
                'get_reviews',
                'get_categories'
            ),
            'customers' => array(
                'create',
                'get_by_email'
            ),
            'orders' => array(
                'create'
            )
        );
    }

    public static function appView($page, $args = array()) {
        if(!empty($args)) {
            extract($args);
        }
        if(!is_file(__DIR__.'/views/'.$page.'.php')) {
            $page = "categories";
        }
        require_once 'views/'.$page.'.php';
        exit;
    }

    public static function jsonView($data = array(
            "status" => "false",
            "msg" => "invalid command",
            "WC_MSG" => "",
            "response" => ""
    )) {
        if(!is_array($data)) {
            $data = array(
                'status' => 'false'
            );
        }

        if(!class_exists("Services_JSON")) {
            require_once 'JSON.php';
            $json = new Services_JSON();
        }
        else {
            $json = new Services_JSON();
        }

        if(preg_match('/(?i)msie [1-8]/',$_SERVER['HTTP_USER_AGENT']) or  strpos( $_SERVER['HTTP_USER_AGENT'], 'Trident/7.0; rv:11.0' ) !== false )
        {
            if(!headers_sent()) {
                header("HTTP/1.0 200 OK");
                header('Content-type: text/json; charset=utf-8');
                header("Cache-Control: no-cache, must-revalidate");
                header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
                header("Pragma: no-cache");
            }
        }
        else
        {
            if(!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }
        }

        //echo $json->_encode($data);
		echo $json->encode($data);
        exit;
    }

    private static function decode_this_key( $txt, $hashKey )
    {
        if(function_exists("mcrypt_encrypt")) {
            return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($hashKey), base64_decode( $txt ), MCRYPT_MODE_CBC, md5(md5($hashKey))), "\0");
        }
        return base64_decode( $txt );
    }

}

/* don't remove this class for new version */
class appeto_browserAddToCard {
    function __construct()
    {
        add_filter('rewrite_rules_array','appeto_browserAddToCard::insertRules');
        add_filter('query_vars','appeto_browserAddToCard::insertQueryVars');
        add_action('parse_query','appeto_browserAddToCard::insertParseQuery');
    }

    static function insertRules($rules)
    {
        $newrules = array();
        $newrules['redirect/url/(.+)$']='index.php?appCardToSite';
        return $newrules+$rules;
    }
    static function insertQueryVars($vars)
    {
        array_push($vars, 'appCardToSite');
        return $vars;
    }
    static function insertParseQuery($query)
    {
        if (!empty($query->query_vars['appCardToSite']) and $query->query_vars['appCardToSite'] != "") {
            if(isset($_GET['e']) and isset($_GET['g']) and $_GET['e'] != '' and $_GET['g'] != '') {
                wp_logout();
                $_GET['e'] = str_replace("::", "/", $_GET['e']);
                $_GET["e"] = str_replace("^", "+", $_GET["e"]);
                $_GET['g'] = str_replace("::", "/", $_GET['g']);
                $_GET["g"] = str_replace("^", "+", $_GET["g"]);
                $email = base64_decode($_GET['e']);
                $pass = base64_decode($_GET['g']);
                if(wp_login($email, $pass)) {
                    $user = get_user_by( 'email', $email );
                    wp_set_current_user( $user->ID, $user->user_login );
                    wp_set_auth_cookie( $user->ID );
                    //do_action( 'wp_login', $user->user_login );
                }
                $link = wc_get_checkout_url()."/".get_option('woocommerce_checkout_pay_endpoint')."/".$query->query_vars['appCardToSite']."/?pay_for_order=true&key=".get_post_meta($query->query_vars['appCardToSite'], "_order_key", true);
                wp_redirect($link);
                exit;
            }
            else {

                /* new version */
                if(isset($_GET['apt_card'])) {
                    $url = site_url();
                    WC()->cart->empty_cart();
                    $card = base64_decode($_GET['apt_card']);
                    $card = json_decode($card, true);
                    if(!empty($card)) {
                        foreach ($card as $product_id => $info) {
                            $variations = [];
                            if(!is_array($info['variations'])) {
                                $info['variations'] = [];
                            }
                            foreach ($info['variations'] as $variation) {
                                $arr = explode("||", $variation);
                                $variations[$arr[1]] = $arr[0];
                            }
                            WC()->cart->add_to_cart( (int) $info['id'], (int) $info['quantity'], (int) $info['variation'], $variations);
                        }
                        $url = wc_get_cart_url();
                    }
                    wp_redirect($url);
                    exit;
                }

                /* old version */
                WC()->cart->empty_cart();
                $query->query_vars['appCardToSite'] = str_replace("::", "/", $query->query_vars['appCardToSite']);
                $query->query_vars['appCardToSite'] = str_replace("^", "+", $query->query_vars['appCardToSite']);
                $card = base64_decode($query->query_vars['appCardToSite']);
                $card = json_decode($card, true);
                if(!empty($card)) {
                    foreach($card as $val) {
                        if(!isset($val['v'])) {
                            $val['v'] = array();
                        }
                        self::appetoAddToCard($val['p'], $val['q'], $val['v']);
                    }
                    wp_redirect(WC()->cart->get_cart_url());
                    exit;
                }
            }
        }
    }

    static private function appetoAddToCard($product_id, $quantity, $variation = array()) {
        WC()->cart->add_to_cart( $product_id, $quantity, 0, $variation );
    }
}

class appeto_auth {
    function __construct()
    {
        add_filter('rewrite_rules_array','appeto_Auth::insertRules');
        add_filter('query_vars','appeto_Auth::insertQueryVars');
        add_action('parse_query','appeto_Auth::insertParseQuery');

        add_action( 'wp_ajax_appeto_user_login_callback', 'appeto_Auth::appeto_user_login_callback' );
        add_action( 'wp_ajax_nopriv_appeto_user_login_callback', 'appeto_Auth::appeto_user_login_callback' );

        add_action( 'wp_ajax_appeto_user_register_callback', 'appeto_Auth::appeto_user_register_callback' );
        add_action( 'wp_ajax_nopriv_appeto_user_register_callback', 'appeto_Auth::appeto_user_register_callback' );

        add_action( 'wp_ajax_appeto_user_logout_callback', 'appeto_Auth::appeto_user_logout_callback' );
        add_action( 'wp_ajax_nopriv_appeto_user_logout_callback', 'appeto_Auth::appeto_user_logout_callback' );
    }

    static function insertRules($rules)
    {
        $newrules = array();
        $newrules['redirect/url/(.+)$']='index.php?appetoAuth';
        return $newrules+$rules;
    }
    static function insertQueryVars($vars)
    {
        array_push($vars, 'appetoAuth');
        return $vars;
    }
    static function insertParseQuery($query)
    {
        if (!empty($query->query_vars['appetoAuth']) and $query->query_vars['appetoAuth'] != "") {
            $args = array();
            if(isset($_GET['appetoAuthArgs'])) {
                $args = json_decode(base64_decode($_GET['appetoAuthArgs']), true);
            }
            if(isset($_GET['u']) and isset($_GET['i'])) {
                $args['user'] = (int) $_GET['u'];
                if($args['user'] == "" or $args['user'] == 0) {
                    $query->query_vars['appetoAuth'] = "access";
                }
                else {
                    if(get_user_meta($args['user'], "appetoAuthKey", true) != $_GET['i']) {
                        $query->query_vars['appetoAuth'] = "access";
                    }
                }
            }
            if($query->query_vars['appetoAuth'] == "checkUrl") {
                global $wp_roles;
                if ( ! isset( $wp_roles ) )
                    $wp_roles = new WP_Roles();
                $roles = $wp_roles->get_names();
                $wpAjax = admin_url( 'admin-ajax.php' );
                $appetoLoginNonce = get_option('appetoLoginNonce');
                if($appetoLoginNonce == "") {
                    update_option("appetoLoginNonce", md5(rand()));
                    $appetoLoginNonce = get_option('appetoLoginNonce');
                }
                self::jsonView(array(
                    "status" => "true",
                    "roles" => $roles,
                    "wpAjaxUrl" => $wpAjax,
                    "appetoNonce" => $appetoLoginNonce,
                    "lostPasswordUrl" => wp_lostpassword_url(),
                    "users_can_register" => get_option( 'users_can_register' )
                ));
            }
            else {
                self::appView($query->query_vars['appetoAuth'], $args);
            }
            exit;
        }
    }

    public static function appeto_check_nonce($nonce) {
        if($nonce == '') return false;
        $appetoLoginNonce = get_option('appetoLoginNonce');
        if($appetoLoginNonce == $nonce) {
            return true;
        }
        return false;
    }

    public static function appeto_user_login_callback() {
        if ( !self::appeto_check_nonce( $_REQUEST['nonce'] ))
        {
            exit("No naughty business please 1");
        }
        global $wpdb;
        $json = array();
        $json['success'] = 0;
        $json['userId'] = 0;
        $json['msg'] = 'args';
        if ( !isset($_POST['log']) or !isset($_POST['pwd'])) {
            echo json_encode( $json );
            die();
        }
        $username = $wpdb->escape($_POST['log']);
        $password = $wpdb->escape($_POST['pwd']);
        if( empty( $username ) ) {
            $json['success'] = 0;
            $json['msg'] = 'username';
        } else if( empty( $password ) ) {
            $json['msg'] = 'password';
            $json['success'] = 0;
        } else {
            $user_data = array();
            $user_data['user_login'] = $username;
            $user_data['user_password'] = $password;
            $user_data['remember'] = true;
            wp_logout();
            $user = wp_signon( $user_data, false );
            if ( is_wp_error($user) ) {
                $json['msg'] = 'username';
                //$json['msg'] = $user->get_error_message();
            } else {
                wp_set_current_user( $user->ID, $username );
                wp_set_auth_cookie( $user->ID );
                do_action('set_current_user');
                $key = md5(rand());
                update_user_meta($user->ID, "appetoAuthKey", $key);
                $json['msg'] = 'done';
                $json['success'] = 1;
                $json['id'] = $user->ID;
                $json['display_name'] = $user->data->display_name;
                $json['role'] = $user->roles[0];
                $json['key'] = $key;
            }
        }
        echo json_encode( $json );
        die();
    }

    public static function appeto_user_register_callback() {
        if ( !wp_verify_nonce( $_REQUEST['nonce'], "appeto_auth_register" ))
        {
            exit("No naughty business please 1");
        }
        global $wpdb;
        $json = array();
        $json['success'] = 0;
        $json['msg'] = 'args';
        if ( !isset($_POST['username']) or !isset($_POST['email'])) {
            echo json_encode( $json );
            die();
        }
        $username = $wpdb->escape($_POST['username']);
        $email = $wpdb->escape($_POST['email']);
        if( empty( $username ) ) {
            $json['success'] = 0;
            $json['msg'] = 'username';
        } else if( empty( $email ) ) {
            $json['msg'] = 'email';
            $json['success'] = 0;
        } else {
            $errors = register_new_user($username, $email);
            if ( is_wp_error($errors) ) {
                $json['msg'] = $errors->get_error_message();
            }
            else {
                $json['msg'] = 'done';
                $json['success'] = 1;
            }
        }
        echo json_encode( $json );
        die();
    }

    public static function appeto_user_logout_callback() {
        if ( !wp_verify_nonce( $_REQUEST['nonce'], "appeto_auth_logout" ))
        {
            exit("No naughty business please 1");
        }
        if(isset($_POST["wpUserId"])) {
            delete_user_meta($_POST["wpUserId"], "appetoAuthKey");
        }
        wp_logout();
    }

    public static function appView($page, $args = array()) {
        if(!empty($args)) {
            extract($args);
        }
        if(!is_file(__DIR__.'/views/auth/'.$page.'.php')) {
            $page = "register";
        }
        require_once 'views/auth/'.$page.'.php';
        exit;
    }

    public static function jsonView($data = array(
        'status' => 'false'
    )) {
        if(!is_array($data)) {
            $data = array(
                'status' => 'false'
            );
        }

        if(!class_exists("Services_JSON")) {
            require_once 'JSON.php';
            $json = new Services_JSON();
        }
        else {
            $json = new Services_JSON();
        }

        if(preg_match('/(?i)msie [1-8]/',$_SERVER['HTTP_USER_AGENT']) or  strpos( $_SERVER['HTTP_USER_AGENT'], 'Trident/7.0; rv:11.0' ) !== false )
        {
            if(!headers_sent()) {
                header("HTTP/1.0 200 OK");
                header('Content-type: text/json; charset=utf-8');
                header("Cache-Control: no-cache, must-revalidate");
                header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
                header("Pragma: no-cache");
            }
        }
        else
        {
            if(!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }
        }

        //echo $json->_encode($data);
        echo $json->encode($data);
        exit;
    }
}