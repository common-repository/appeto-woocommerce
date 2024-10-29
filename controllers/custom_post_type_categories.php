<?php
/*
Controller name: Appeto
Controller description: فعال کردن امکانات اپتو مثل نمایش custom post type بر اساس دسته و ...
*/
class json_api_appeto_controller {

    public function appeto_show_custom_post_type_of_custom_taxonomy() {
        global $json_api;
        if (!$json_api->query->post_type or !$json_api->query->taxonomy or !$json_api->query->termslug) {
            $json_api->error("Include a 'post_type' and 'taxonomy' and 'termslug' query var.");
        }
        $url = parse_url($_SERVER['REQUEST_URI']);
        $defaults = array(
            'ignore_sticky_posts' => true
        );
        $query = wp_parse_args($url['query']);
        unset($query['json']);
        unset($query['post_status']);
        $query[$json_api->query->taxonomy] = $json_api->query->termslug;
        unset($query['taxonomy']);
        unset($query['termslug']);
        $query = array_merge($defaults, $query);
        $posts = $json_api->introspector->get_posts($query);
        $result = $this->posts_result($posts);
        return $result;
    }
    protected function posts_result($posts) {
        global $wp_query;
        return array(
            'count' => count($posts),
            'count_total' => (int) $wp_query->found_posts,
            'pages' => $wp_query->max_num_pages,
            'posts' => $posts
        );
    }


    public function appeto_get_categories_of_custom_post_type() {
        global $json_api;
        if (!$json_api->query->post_type) {
            $json_api->error("Include a 'post_type' query var.");
        }
        $post_type = $json_api->query->post_type;
        $taxonomy_objects = get_object_taxonomies( $post_type, 'array' );
        $result = array();
        if(!empty($taxonomy_objects)) {
            $i = 0;
            foreach($taxonomy_objects as $key => $value) {
                if(isset($value->public) and $value->public) {
                    $result[$i]['name'] = $key;
                    $result[$i]['label'] = $value->label;
                    /*$terms = get_terms( array(
                        'taxonomy' => $key,
                        'hide_empty' => false,
                    ));*/
                    $terms = appeto_custom_get_terms($key);
                    if(!empty($terms)) {
                        foreach($terms as $_key => $_value) {
                            $result[$i]['terms'][] = array(
                                'term_id' => $_value->term_id,
                                'name' => $_value->name,
                                'slug' => $_value->slug
                            );
                        }
                    }
                    $i++;
                }
            }
        }
        return array(
            "categories" => $result
        );
    }
    public function appeto_get_custom_post_types() {
        $deny_types = array();
        $args = array(
            'public'   => true,
            '_builtin' => false
        );
        $output = 'array';
        $operator = 'and';
        $post_types = get_post_types( $args, $output, $operator );

        $result[0]["name"] = "post";
        $result[0]["label"] = "نوشته‌ها";
        if( is_array( $post_types ) ) {
            if (!empty($post_types)) {
                $i = 1;
                foreach ($post_types as $post_type) {
                    if (!in_array($post_type->name, $deny_types)) {
                        $result[$i]["name"] = $post_type->name;
                        $result[$i]["label"] = $post_type->label;
                        $i++;
                    }
                }
            }
        }

        return array(
            "post_types" => $result
        );
    }
}