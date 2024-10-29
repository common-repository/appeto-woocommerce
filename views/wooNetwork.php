<?php
if(isset($_GET["appeto_bid"]) and $_GET["appeto_bid"] != "") {
    $blog_id = (int) base64_decode($_GET["appeto_bid"]);
    $current_blog_details = get_blog_details( array( 'blog_id' => $blog_id ) );
    if(empty($current_blog_details) or $blog_id == 0) {
        echo '<div class="error">
                    <p>
                        <strong>
                        در شبکه سایتی یافت نشد.
                        </strong>
                    </p>
                 </div>';
    }
    else {

        if(isset($_POST["save_appeto_blog_api"]) and isset($_GET['_nonce'])) {
            if(wp_verify_nonce($_GET['_nonce'], 'appeto_save_blog_api')) {
                update_option('appeto_ck_'.$blog_id, sanitize_text_field($_POST['appeto_ck']));
                update_option('appeto_cs_'.$blog_id, sanitize_text_field($_POST['appeto_cs']));
                update_option('appeto_localCk_'.$blog_id, sanitize_text_field($_POST['appeto_localCk']));
                update_option('appeto_localCs_'.$blog_id, sanitize_text_field($_POST['appeto_localCs']));
                update_option('appeto_ntsite_img_'.$blog_id, sanitize_text_field($_POST['appeto_site_logo']));
                echo '<div class="updated">
                        <strong>
                        تغییرات ذخیره شد.
                        </strong>
                    </div>';
            }
        }
        $appeto_ck = get_option("appeto_ck_".$blog_id, "");
        $appeto_cs = get_option("appeto_cs_".$blog_id, "");
        $appeto_localCk = get_option("appeto_localCk_".$blog_id, "");
        $appeto_localCs = get_option("appeto_localCs_".$blog_id, "");
        $appeto_site_logo = get_option("appeto_ntsite_img_".$blog_id, "");
?>
    <div class='wrap'>
        <h2>تنظیمات اپ برای سایت
        <?php echo $current_blog_details->blogname;?>
        </h2>
        <hr />
        <form action="admin.php?page=appeto-network-woo&appeto_bid=<?php echo base64_encode($blog_id);?>&_nonce=<?php echo wp_create_nonce('appeto_save_blog_api')?>" method="post">
            <p>آدرس تصویر به عنوان لوگو سایت</p>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row">
                        <label for="appeto_site_logo">لینک لوگو این سایت</label>
                    </th>
                    <td>
                        <input type="text" value="<?php echo $appeto_site_logo;?>" id="appeto_site_logo" name="appeto_site_logo" class="regular-text">
                    </td>
                </tr>
                </tbody>
            </table>
            <p>
                کلیدهای ساخته شده برای این بخش با دسترسی "خواندن و نوشتن" باشد.
                ( دقت کنید این کلیدها در سایت مورد نظر ساخته شده باشند )
            </p>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row">
                        <label for="appeto_ck">کلید مصرف کننده</label>
                    </th>
                    <td>
                        <input type="text" value="<?php echo $appeto_ck;?>" id="appeto_ck" name="appeto_ck" class="regular-text">
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="appeto_cs">رمز مصرف کننده</label>
                    </th>
                    <td>
                        <input type="text" value="<?php echo $appeto_cs;?>" id="appeto_cs" name="appeto_cs" class="regular-text">
                    </td>
                </tr>
                </tbody>
            </table>
            <p>
                کلیدهای ساخته شده برای این بخش فقط با دسترسی "اطلاعات بیشتر" باشد.
                ( دقت کنید این کلیدها در سایت مورد نظر ساخته شده باشند )
            </p>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row">
                        <label for="appeto_ck">کلید مصرف کننده</label>
                    </th>
                    <td>
                        <input type="text" value="<?php echo $appeto_localCk;?>" id="appeto_localCk" name="appeto_localCk" class="regular-text">
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="appeto_cs">رمز مصرف کننده</label>
                    </th>
                    <td>
                        <input type="text" value="<?php echo $appeto_localCs;?>" id="appeto_localCs" name="appeto_localCs" class="regular-text">
                    </td>
                </tr>
                </tbody>
            </table>
            <p class="submit">
                <input type="submit" name="save_appeto_blog_api" id="submit" class="button button-primary" value="ذخیره‌ی تغییرات">
            </p>
        </form>

    </div>
<?php
    }
?>

<?php
}
else {
?>
    <div class='wrap'>
        <h2>تنظیمات وردپرس شبکه ای برای ووکامرس - اپتو</h2>
        <hr />
        <?php
        if(function_exists('get_sites')) {
            $sites = get_sites();
        }
        elseif(function_exists('wp_get_sites')) {
            $sites = wp_get_sites();
        }
        else {
            $sites = array();
        }
        if(empty($sites)) {
            echo '<div class="error">
                    <p>
                        <strong>
                        در شبکه سایتی یافت نشد.
                        </strong>
                    </p>
                 </div>';
        }
        else {
        ?>
            <table class="wp-list-table widefat plugins">
                <thead>
                <tr>
                    <th scope="col" id="name" class="manage-column column-name column-primary">#</th>
                    <th scope="col" id="description" class="manage-column column-description">پسوند</th>
                    <th scope="col" id="api" class="manage-column column-api">تنظیمات api</th>
                </tr>
                </thead>
                <tbody id="the-list">
                <?php
                foreach($sites as $site) {
                    if($site->path == "/") continue;
                    if($site->deleted == 1) continue;
                    echo '<tr>
                            <td>'.$site->blog_id.'</td>
                            <td>'.$site->path.'</td>
                            <td><a href="admin.php?page=appeto-network-woo&appeto_bid='.base64_encode($site->blog_id).'">تنظیمات برای اپ</a></td>
                          </tr>';
                }
                ?>
                </tbody>

            </table>
        <?php
        }
        ?>
    </div>
<?php
}
?>