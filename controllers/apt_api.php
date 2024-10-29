<?php
class AptApi {

    public function __construct()
    {
        add_action( 'rest_api_init', function () {
            register_rest_route( 'apt/v1', '/post-types/', array(
                'methods' => 'GET',
                'callback' => array($this, "getPostTypes"),
            ) );

            register_rest_route( 'apt/v1', '/categories-of-post-type/', array(
                'methods' => 'GET',
                'callback' => array($this, "getCategoriesOfPostType"),
            ) );

            register_rest_route( 'apt/v1', '/get-all-authors/', array(
                'methods' => 'GET',
                'callback' => array($this, "getAllAuthors"),
            ) );

            register_rest_route( 'apt/v1', '/category-thumbnail/(?P<id>\d+)', array(
                'methods' => 'GET',
                'callback' => array($this, "getCategoryThumbnail"),
                'args' => array(
                    'id' => array(
                        'required' => true,
                        'validate_callback' => function($param, $request, $key) {
                            return is_numeric( $param );
                        }
                    )
                )
            ) );

            register_rest_route( 'apt/v1', '/woo-settings/', array(
                'methods' => 'GET',
                'callback' => array($this, "getWooSettings"),
            ) );

            if(function_exists('jdate')) {
                register_rest_field(
                    array('post', 'comment'),
                    'date',
                    array(
                        'get_callback'    => function() {
                            return jdate( get_option( 'date_format' ), strtotime(get_post_time( get_option( 'date_format' ))));
                        },
                        'update_callback' => null,
                        'schema'          => null,
                    )
                );
            }


        } );

        add_action( 'init', array( $this, 'init' ), 99 );

        add_filter('rest_allow_anonymous_comments', array($this, 'filter_rest_allow_anonymous_comments'));

    }

    public function filter_rest_allow_anonymous_comments() {
        return true;
    }

    public function init() {
        add_filter( 'rest_prepare_category', array($this, 'apt_rest_prepare_category'), 10, 3 );
        $args = array(
            'public'   => true,
            '_builtin' => false
        );
        $output = 'array';
        $operator = 'and';
        $post_types = get_post_types( $args, $output, $operator );
        $is_jalali = function_exists('jdate');
        if( is_array( $post_types ) ) {
            if (!empty($post_types)) {
                $posts = [];
                foreach ($post_types as $post_type) {
                    array_push($posts, $post_type->name);
                    $taxonomy_objects = get_object_taxonomies( $post_type->name, 'array' );
                    foreach($taxonomy_objects as $key => $value) {
                        if (isset($value->public) and $value->public) {
                            if(isset($value->hierarchical) and $value->hierarchical) {
                                add_filter( 'rest_prepare_'.$value->name, array($this, 'apt_rest_prepare_category'), 10, 3 );
                            }
                        }
                    }
                }
                if($is_jalali) {
                    register_rest_field(
                        $posts,
                        'date',
                        array(
                            'get_callback'    => function() {
                                return jdate( get_option( 'date_format' ), strtotime(get_post_time( get_option( 'date_format' ))));
                            },
                            'update_callback' => null,
                            'schema'          => null,
                        )
                    );
                }

                //woo attributes
                if(in_array('product', $posts)) {
                    add_action('woocommerce_product_options_advanced', array($this, 'apt_woocommerce_product_custom_fields'));
                    add_action( 'woocommerce_process_product_meta', array($this, 'apt_woocommerce_product_custom_fields_save') );
                    register_rest_field(
                        'product',
                        'attributes',
                        array(
                            'get_callback' => function($data) {
                                $attributes = [];
                                if(isset($data['attributes'])) {
                                    $product = wc_get_product($data['id']);
                                    $wc_attr_objs = $product->get_attributes();
                                    $attribute_terms = [];
                                    foreach ($wc_attr_objs as $slug => $attr) {
                                        $attribute_terms[$attr['data']['id']] = get_terms($slug);
                                    }
                                    foreach ($data['attributes'] as $key => $attribute) {
                                        $data['attributes'][$key]['apt_attr_colors'] = [];
                                        $id = $attribute['id'];
                                        $type = get_option( "apt_wc_attribute_type-$id" );
                                        if($type == '' or $type == null) {
                                            $type = 'normal';
                                        }
                                        if($type == 'color' and isset($attribute_terms[$id])) {
                                            foreach ($attribute_terms[$id] as $attribute_term) {
                                                $color = get_option( "apt_wc_attribute_color-" . $attribute_term->term_id );
                                                if($color == '' or $color == null) {
                                                    $color = '#ffffff';
                                                }
                                                $data['attributes'][$key]['apt_attr_colors'][$attribute_term->name] = aptIntegrationColors($color);
                                            }
                                        }
                                        $data['attributes'][$key]['apt_attr_type'] = $type;
                                        $data['attributes'][$key]['apt_attr_colors'] = (object) $data['attributes'][$key]['apt_attr_colors'];
                                    }
                                    $attributes = $data['attributes'];
                                }
                                return $attributes;
                            },
                            'update_callback' => null,
                            'schema'          => null,
                        )
                    );
                    register_rest_field(
                        'product',
                        'apt_quantity_limit',
                        array(
                            'get_callback' => function($data) {
                                return (int) get_post_meta($data['id'], 'woocommerce_apt_card_limit', true);
                            },
                            'update_callback' => null,
                            'schema'          => null,
                        )
                    );
                }
            }
        }

    }

    public function apt_woocommerce_product_custom_fields() {
        global $post;
        $value = '';
        if(isset($post->ID)) {
            $value = (int) get_post_meta($post->ID, 'woocommerce_apt_card_limit', true);
        }
        $args = array(
            'id' => 'woocommerce_apt_card_limit',
            'label' => 'محدودیت تعداد سفارش در اپلیکیشن',
            'type' => 'number',
            'description' => 'در صورت نداشتن محدودیت این فیلد را خالی یا 0 بگذارید',
            'desc_tip' => true,
            'value' => $value
        );
        woocommerce_wp_text_input($args);
    }

    public function apt_woocommerce_product_custom_fields_save($post_id) {
        if(isset($_POST['woocommerce_apt_card_limit'])) {
            $woocommerce_apt_card_limit = $_POST['woocommerce_apt_card_limit'];
            update_post_meta($post_id, 'woocommerce_apt_card_limit', esc_attr($woocommerce_apt_card_limit));
        }
    }

    public function apt_rest_prepare_category( $data, $category, $request ) {
        $_data = $data->data;
        $image_id = get_term_meta ( $category->term_id, 'apt-category-image-id', true );
        $size = "full";
        if(isset($_GET["app_thumbnail_size"])) {
            $size = $_GET["app_thumbnail_size"];
        }
        if($image_id) {
            $_data["app_thumbnail"] = wp_get_attachment_image_url( $image_id, $size );
        }
        else {
            $_data["app_thumbnail"] = plugins_url('assets/img/no_image.png', dirname(__FILE__));
        }
        if($_data["app_thumbnail"] === FALSE) {
            $_data["app_thumbnail"] = '';
        }
        $children = get_term_children($category->term_id, $category->taxonomy);
        if(!empty($children)) {
            $_data["app_has_child"] = true;
        }
        else {
            $_data["app_has_child"] = false;
        }
        $icon = get_term_meta ( $category->term_id, 'apt-category-font-icon', true );
        if($icon === FALSE) {
            $icon = '';
        }
        $_data["app_icon"] = aptConvertIcon($icon);
        $data->data = $_data;
        return $data;
    }

    public function getPostTypes() {

        $deny_types = array();

        $args = array(
            'public'   => true,
            '_builtin' => false
        );

        $output = 'array';
        $operator = 'and';
        $post_types = get_post_types( $args, $output, $operator );

        $result[0]["name"] = "post";
        $result[0]["label"] = __("Posts");
        $taxonomy_objects = get_object_taxonomies( "post", 'array' );
        $result[0]["taxonomy"] = [];
        foreach($taxonomy_objects as $key => $value) {
            if (isset($value->public) and $value->public) {
                if(isset($value->hierarchical) and $value->hierarchical) {
                    array_push($result[0]["taxonomy"], $value);
                }
            }
        }

        /*$result[1]["name"] = "page";
        $result[1]["label"] = __("Pages");*/

        if( is_array( $post_types ) ) {
            if (!empty($post_types)) {
                $i = 1;
                foreach ($post_types as $post_type) {
                    if (!in_array($post_type->name, $deny_types)) {
                        $result[$i]["name"] = $post_type->name;
                        $result[$i]["label"] = $post_type->label;
                        $taxonomy_objects = get_object_taxonomies( $post_type->name, 'array' );
                        $result[$i]["taxonomy"] = [];
                        foreach($taxonomy_objects as $key => $value) {
                            if (isset($value->public) and $value->public) {
                                if(isset($value->hierarchical) and $value->hierarchical) {
                                    array_push($result[$i]["taxonomy"], $value);
                                }
                            }
                        }
                        $i++;
                    }
                }
            }
        }

        return array(
            "post_types" => $result,
            "have_woocommerce" => APT_IS_WOO_ACTIVE
        );
    }

    public function getCategoryThumbnail(WP_REST_Request $request) {
        $result = [
            "app_cover" => plugins_url('assets/img/no_image.png', dirname(__FILE__))
        ];
        $parameters = $request->get_params();
        if(isset($parameters["id"])) {
            $term_id = $parameters["id"];
            $image_id = get_term_meta ( $term_id, 'apt-category-image-id', true );
            $size = "full";
            if(isset($_GET["app_thumbnail_size"])) {
                $size = $_GET["app_thumbnail_size"];
            }
            if($image_id) {
                $result["app_cover"] = wp_get_attachment_image_url( $image_id, $size );
            }
        }
        if($result["app_cover"] === FALSE) {
            $result["app_cover"] = '';
        }
        return $result;
    }

    public function getWooSettings() {
        $gateways = WC()->payment_gateways->get_available_payment_gateways();
        $enabled_gateways = [];
        if( $gateways ) {
            foreach( $gateways as $gateway ) {
                if( $gateway->enabled == 'yes' ) {
                    $obj = [
                        'id' => $gateway->id,
                        'title' => $gateway->title,
                        'description' => $gateway->description,
                        'icon' => $gateway->icon
                    ];
                    $enabled_gateways[] = $obj;
                }
            }
        }
        return [
            'aptSettings' => [
                'paymentOnSite' => $this->get_woo_option( 'appeto_woo_signup_form_app'),
                'getMobile' => $this->get_woo_option('appeto_woo_signup_form_mobile'),
                'getCompany' => $this->get_woo_option('appeto_woo_signup_form_company'),
                'getState' => $this->get_woo_option('appeto_woo_signup_form_state'),
                'getCity' => $this->get_woo_option('appeto_woo_signup_form_city'),
                'getAddress' => $this->get_woo_option('appeto_woo_signup_form_address'),
                'getAddress2' => $this->get_woo_option('appeto_woo_signup_form_address2'),
                'getPostalCode' => $this->get_woo_option('appeto_woo_signup_form_postalcode'),
                'checkoutLink' => wc_get_checkout_url()."/".get_option('woocommerce_checkout_pay_endpoint')."/",
            ],
            'gatewaySettings' => $enabled_gateways
        ];
    }

    public function getCategoriesOfPostType() {
        $post_type = isset($_GET['post_type']) ? esc_sql($_GET['post_type']) : 'post';
        $taxonomy_objects = get_object_taxonomies( $post_type, 'array' );
        $result = array();
        if(!empty($taxonomy_objects)) {
            foreach($taxonomy_objects as $key => $value) {
                if(isset($value->public) and $value->public) {
                    if(isset($value->hierarchical) and $value->hierarchical) {
                        $result['name'] = $key;
                        $result['label'] = $value->label;
                        $terms = appeto_custom_get_terms($key);
                        if(!empty($terms)) {
                            foreach($terms as $_key => $_value) {
                                $result['terms'][] = array(
                                    'term_id' => $_value->term_id,
                                    'name' => $_value->name,
                                    'slug' => $_value->slug
                                );
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }

    public function getAllAuthors() {
        $result = [];
        $users = get_users();
        foreach ($users as $user)
        {
            array_push($result, [
                'id' => $user->ID,
                'name' => $user->display_name
            ]);
        }
        return $result;
    }

    private function get_woo_option($name) {
        $option = get_option( $name, true );
        if($option === TRUE) {
            $option = 'no';
        }
        if($option == 'yes') {
            return true;
        }
        return false;
    }

}