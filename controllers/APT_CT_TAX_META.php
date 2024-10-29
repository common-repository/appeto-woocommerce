<?php
if ( ! class_exists( 'APT_CT_TAX_META' ) ) {

    class APT_CT_TAX_META {

        public function __construct() {
            add_action( 'init', array( $this, 'init' ), 999 );
            add_action('admin_enqueue_scripts', array( $this, 'apt_admin_style' ));
        }

        function apt_admin_style() {
            wp_enqueue_style('apt-fonts-css', plugin_dir_url(__DIR__).'/assets/font-awesome/font-awesome.min.css');
            wp_enqueue_style('apt-icon-picker-css', plugin_dir_url(__DIR__).'/assets/fontawesome-iconpicker/dist/css/fontawesome-iconpicker.min.css');
            wp_enqueue_script('apt-icon-picker-js', plugin_dir_url(__DIR__) . '/assets/fontawesome-iconpicker/dist/js/fontawesome-iconpicker.min.js');
            wp_enqueue_script('apt-icons-js', plugin_dir_url(__DIR__) . '/assets/fontawesome-iconpicker/dist/js/apt-icons.js');
            wp_enqueue_style( 'wp-color-picker');
            wp_enqueue_script( 'wp-color-picker');
        }

        /*
         * Initialize the class and start calling our hooks and filters
         * @since 1.0.0
        */
        public function init() {
            add_action( 'category_add_form_fields', array ( $this, 'add_category_image' ), 10, 2 );
            add_action( 'created_category', array ( $this, 'save_category_image' ), 10, 2 );
            add_action( 'category_edit_form_fields', array ( $this, 'update_category_image' ), 10, 2 );
            add_action( 'edited_category', array ( $this, 'updated_category_image' ), 10, 2 );
            add_filter('manage_edit-category_columns', array($this, 'manage_category_columns'));
            add_filter('manage_category_custom_column', array($this, 'manage_category_columns_fields'), 10, 3);
            add_action( 'woocommerce_after_edit_attribute_fields', array($this, 'product_attributes_taxonomy_apt_custom_fields'), 10, 0 );
            add_action( 'woocommerce_after_add_attribute_fields', array($this, 'product_attributes_taxonomy_apt_custom_fields'), 10, 0 );
            add_action( 'woocommerce_attribute_added', array($this, 'apt_save_wc_attribute_field') );
            add_action( 'woocommerce_attribute_updated', array($this, 'apt_save_wc_attribute_field') );
            add_action( 'woocommerce_attribute_deleted', array($this, 'apt_wc_attribute_removed') );


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
                        $taxonomy_objects = get_object_taxonomies( $post_type->name, 'array' );
                        foreach($taxonomy_objects as $key => $value) {
                            if (isset($value->public) and $value->public) {
                                if(isset($value->hierarchical) and $value->hierarchical) {
                                    add_action( $value->name.'_add_form_fields', array ( $this, 'add_category_image' ), 10, 2 );
                                    add_action( 'created_'.$value->name, array ( $this, 'save_category_image' ), 10, 2 );
                                    add_action( $value->name.'_edit_form_fields', array ( $this, 'update_category_image' ), 10, 2 );
                                    add_action( 'edited_'.$value->name, array ( $this, 'updated_category_image' ), 10, 2 );
                                    add_filter('manage_edit-'.$value->name.'_columns', array($this, 'manage_category_columns'));
                                    add_filter('manage_'.$value->name.'_custom_column', array($this, 'manage_category_columns_fields'), 10, 3);
                                }
                            }
                            else {
                                if($post_type->name == 'product') {
                                    if($value->name == 'product_type' or $value->name == 'product_visibility') continue;

                                    add_action( $value->name.'_add_form_fields', array($this, 'product_attributes_taxonomy_apt_custom_color'), 10, 2 );
                                    add_action( 'created_'.$value->name, array ( $this, 'apt_save_wc_attribute_color' ), 10, 2 );
                                    add_action( $value->name.'_edit_form_fields', array($this, 'product_attributes_taxonomy_apt_custom_color'), 10, 2 );
                                    add_action( 'edited_'.$value->name, array ( $this, 'apt_save_wc_attribute_color' ), 10, 2 );
                                    add_action( 'delete_'.$value->name, array ( $this, 'apt_wc_attribute_removed_color' ), 10, 2 );
                                }
                            }
                        }
                    }
                }
            }

            add_action( 'admin_enqueue_scripts', array( $this, 'load_media' ) );
            add_action( 'admin_footer', array ( $this, 'add_script' ) );

        }

        public function load_media() {
            wp_enqueue_media();
        }

        /*
         * Add a table column in the category page
         * @since 1.0.0
        */
        function manage_category_columns($columns)
        {
            $columns['apt_category_thumbnail'] = 'تصویر اپ';
            return $columns;
        }
        function manage_category_columns_fields($deprecated, $column_name, $term_id)
        {
            if ($column_name == 'apt_category_thumbnail') {
                $image_id = get_term_meta ( $term_id, 'apt-category-image-id', true );
                echo '<div style="text-align: center; margin: auto">';
                if($image_id) {
                    echo wp_get_attachment_image ( $image_id, 'thumbnail', true, array( "style" => "width: 50px; height: 50px;" ));
                }
                else {
                    echo '<img src="'.plugins_url('assets/img/no_image.png', dirname(__FILE__)).'" style="width: 50px; height: 50px;">';
                }
                echo '</div>';
            }
        }

        /*
         * Add a form field in the new category page
         * @since 1.0.0
        */
        public function add_category_image ( $taxonomy ) { ?>
            <div class="form-field term-group">
                <label for="apt-category-image-id">افزودن تصویر برای دسته در اپلیکیشن</label>
                <input type="hidden" id="apt-category-image-id" name="apt-category-image-id" class="custom_media_url" value="">
                <div id="apt-category-image-wrapper"></div>
                <p>
                    <input type="button" class="button button-secondary apt_ct_tax_media_button" id="apt_ct_tax_media_button" name="apt_ct_tax_media_button" value="اضافه کردن تصویر" />
                    <input type="button" class="button button-secondary apt_ct_tax_media_remove" id="apt_ct_tax_media_remove" name="apt_ct_tax_media_remove" value="حذف تصویر" />
                </p>
                <p>
                    <label>انتخاب فونت آماده اپلیکیشن (برای طراحی هایی که از فونت استفاده میکنند)</label>
                    <input class="form-control icp icp-auto iconpicker" name="apt-category-font-icon" value="fa-address-book" type="text"/>
                </p>
            </div>
            <script>
                jQuery('.iconpicker').iconpicker({
                    icons: APT_ICON_PICKER_ICONS,
                    selectedCustomClass: 'bg-purple',
                    hideOnSelect: true
                });
            </script>
            <?php
        }

        /*
         * Save the form field
         * @since 1.0.0
        */
        public function save_category_image ( $term_id, $tt_id ) {
            if( isset( $_POST['apt-category-image-id'] ) && '' !== $_POST['apt-category-image-id'] ){
                $image = $_POST['apt-category-image-id'];
                add_term_meta( $term_id, 'apt-category-image-id', $image, true );
            }
            if(isset($_POST["apt-category-font-icon"]) and $_POST["apt-category-font-icon"] !== '') {
                $icon = $_POST['apt-category-font-icon'];
                add_term_meta( $term_id, 'apt-category-image-id', $icon, true );
            }
        }

        /*
         * Edit the form field
         * @since 1.0.0
        */
        public function update_category_image ( $term, $taxonomy ) { ?>
            <tr class="form-field term-group-wrap">
                <th scope="row">
                    <label for="apt-category-image-id">ویرایش تصویر برای دسته در اپلیکیشن</label>
                </th>
                <td>
                    <?php $image_id = get_term_meta ( $term -> term_id, 'apt-category-image-id', true ); ?>
                    <input type="hidden" id="apt-category-image-id" name="apt-category-image-id" value="<?php echo $image_id; ?>">
                    <div id="apt-category-image-wrapper">
                        <?php if ( $image_id ) { ?>
                            <?php echo wp_get_attachment_image ( $image_id, 'thumbnail' ); ?>
                        <?php } ?>
                    </div>
                    <p>
                        <input type="button" class="button button-secondary apt_ct_tax_media_button" id="apt_ct_tax_media_button" name="apt_ct_tax_media_button" value="تغییر تصویر" />
                        <input type="button" class="button button-secondary apt_ct_tax_media_remove" id="apt_ct_tax_media_remove" name="apt_ct_tax_media_remove" value="حذف تصویر" />
                    </p>
                    <p>
                        <?php $icon = get_term_meta ( $term->term_id, 'apt-category-font-icon', true ); ?>
                        <label>انتخاب فونت آماده اپلیکیشن (برای طراحی هایی که از فونت استفاده میکنند)</label>
                        <input class="form-control icp icp-auto iconpicker" name="apt-category-font-icon" value="<?= ($icon == "fa-address-book") ? "" : $icon;?>" type="text"/>
                    </p>
                    <script>
                        jQuery('.iconpicker').iconpicker({
                            icons: APT_ICON_PICKER_ICONS,
                            selectedCustomClass: 'bg-purple',
                            hideOnSelect: true
                        });
                    </script>
                </td>
            </tr>
            <?php
        }

        /*
         * Update the form field value
         * @since 1.0.0
         */
        public function updated_category_image ( $term_id, $tt_id ) {
            if(!wp_doing_ajax()) {
                if( isset( $_POST['apt-category-image-id'] ) && '' !== $_POST['apt-category-image-id'] ){
                    $image = $_POST['apt-category-image-id'];
                    update_term_meta ( $term_id, 'apt-category-image-id', $image );
                } else {
                    update_term_meta ( $term_id, 'apt-category-image-id', '' );
                }
                if( isset( $_POST['apt-category-font-icon'] ) && '' !== $_POST['apt-category-font-icon'] ){
                    $icon = $_POST['apt-category-font-icon'];
                    update_term_meta ( $term_id, 'apt-category-font-icon', $icon );
                } else {
                    update_term_meta ( $term_id, 'apt-category-font-icon', '' );
                }
            }
        }

        /*
         * Add script
         * @since 1.0.0
         */
        public function add_script() { ?>
            <script>
                jQuery(document).ready( function($) {
                    function apt_ct_media_upload(button_class) {
                        var _custom_media = true,
                            _orig_send_attachment = wp.media.editor.send.attachment;
                        $('body').on('click', button_class, function(e) {
                            var button_id = '#'+$(this).attr('id');
                            var send_attachment_bkp = wp.media.editor.send.attachment;
                            var button = $(button_id);
                            _custom_media = true;
                            wp.media.editor.send.attachment = function(props, attachment){
                                if ( _custom_media ) {
                                    $('#apt-category-image-id').val(attachment.id);
                                    $('#apt-category-image-wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
                                    $('#apt-category-image-wrapper .custom_media_image').attr('src',attachment.url).css('display','block');
                                } else {
                                    return _orig_send_attachment.apply( button_id, [props, attachment] );
                                }
                            }
                            wp.media.editor.open(button);
                            return false;
                        });
                    }
                    apt_ct_media_upload('.apt_ct_tax_media_button.button');
                    $('body').on('click','.apt_ct_tax_media_remove',function(){
                        $('#apt-category-image-id').val('');
                        $('#apt-category-image-wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
                    });
                    // Thanks: http://stackoverflow.com/questions/15281995/wordpress-create-category-ajax-response
                    $(document).ajaxComplete(function(event, xhr, settings) {
                        var queryStringArr = settings.data.split('&');
                        if( $.inArray('action=add-tag', queryStringArr) !== -1 ){
                            var xml = xhr.responseXML;
                            $response = $(xml).find('term_id').text();
                            if($response!=""){
                                // Clear the thumb image
                                $('#apt-category-image-wrapper').html('');
                            }
                        }
                    });

                    jQuery('.color-field').wpColorPicker();

                });
            </script>
        <?php }


        /* woocommerce attributes */
        public function product_attributes_taxonomy_apt_custom_fields() {
            $id = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0;
            $type = 'normal';
            if($id > 0) {
                $type = get_option( "apt_wc_attribute_type-$id" );
                if($type == '' or $type == null) {
                    $type = 'normal';
                }
            }
        ?>
            <tr class="form-field">
                <th scope="row">
                    <label for="apt_attribute_type">نوع ویژگی - جهت نمایش رنگ ها در اپلیکیشن</label>
                </th>
                <td>
                    <select name="apt_attribute_type" value="<?= $type?>" id="apt_attribute_type" style="width: 100%; margin-bottom: 15px;">
                        <option value="normal" <?php if($type == 'normal') echo 'selected';?>>معمولی</option>
                        <option value="color" <?php if($type == 'color') echo 'selected';?>>رنگ</option>
                    </select>
                </td>
            </tr>
            <?php
        }

        public function apt_save_wc_attribute_field( $id ) {
            if ( is_admin() && isset( $_POST['apt_attribute_type'] ) ) {
                $option = "apt_wc_attribute_type-$id";
                update_option( $option, sanitize_text_field( $_POST['apt_attribute_type'] ) );
            }
        }

        public function apt_wc_attribute_removed( $id ) {
            delete_option( "apt_wc_attribute_type-$id" );
        }


        public function product_attributes_taxonomy_apt_custom_color($arg) {
            $id = isset( $_GET['tag_ID'] ) ? absint( $_GET['tag_ID'] ) : 0;
            $color = '#ffffff';
            if($id > 0) {
                $color = get_option( "apt_wc_attribute_color-$id" );
                if($color == '' or $color == null) {
                    $color = '#ffffff';
                }
            }
            ?>
            <tr class="form-field">
                <th scope="row">
                    <label for="apt_attribute_color">کد رنگ برای اپلیکیشن</label>
                </th>
                <td>
                    <input name="apt_attribute_color" id="apt_attribute_color" type="text" data-default-color="<?= $color?>" value="<?= $color?>" style="width: 100%; margin-bottom: 25px" class="color-field" />
                </td>
            </tr>
            <br />
            <?php
        }

        public function apt_save_wc_attribute_color( $id ) {
            if ( is_admin() && isset( $_POST['apt_attribute_color'] ) ) {
                $option = "apt_wc_attribute_color-$id";
                update_option( $option, sanitize_text_field( $_POST['apt_attribute_color'] ) );
            }
        }

        public function apt_wc_attribute_removed_color( $id ) {
            delete_option( "apt_wc_attribute_color-$id" );
        }

    }
}