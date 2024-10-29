<?php
if(isset($_POST["send_appeto_push"]) and isset($_GET['_nonce'])) {
    if(wp_verify_nonce($_GET['_nonce'], 'appeto_push_notification')) {
        $notifications = new appeto_push_notification();
        $sent = $notifications->send($_POST['appeto_push_devices'], $_POST['appeto_push_url'], $_POST['appeto_push_title'], $_POST['appeto_push_desc']);
        if($_POST['appeto_push_devices'] == "android" and $sent['android']) {
            echo '<div class="updated">
                    <p>
                        <strong>
                            ارسال انجام شد.
                        </strong>
                    </p>
                 </div>';
        }
        else if($_POST['appeto_push_devices'] == "ios" and $sent['ios']) {
            echo '<div class="updated">
                    <p>
                        <strong>
                            ارسال انجام شد.
                        </strong>
                    </p>
                 </div>';
        }
        else {
            if($sent['ios'] and $sent['android']) {
                echo '<div class="updated">
                    <p>
                        <strong>
                            ارسال انجام شد.
                        </strong>
                    </p>
                 </div>';
            }
            else {
                if(!$sent['ios'] and !$sent['android']) {
                    echo '<div class="error">
                        <p>
                            <strong>
                                ارسال انجام نشد.
                            </strong>
                        </p>
                     </div>';
                }
                else {
                    if(!$sent['ios']) {
                        echo '<div class="error">
                            <p>
                                <strong>
                                برای اندروید ارسال شد اما برای ios ارسال نشد.
                                </strong>
                            </p>
                         </div>';
                    }
                    if(!$sent['android']) {
                        echo '<div class="error">
                            <p>
                                <strong>
برای ios ارسال شد اما برای اندروید ارسال نشد.
                                </strong>
                            </p>
                         </div>';
                    }
                }
            }
        }
    }
}
?>
<div class='wrap'>
    <h2>ارسال نوتیفیکیشن - اپتو</h2>
    <hr />
    <form action="admin.php?page=appeto-push&_nonce=<?php echo wp_create_nonce('appeto_push_notification')?>" method="post">
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="appeto_push_devices">نوع دستگاه</label>
                    </th>
                    <td>
                        <select name="appeto_push_devices">
                            <!--<option value="all">برای کاربران اندروید و iOS</option>-->
                            <option value="android">فقط کاربران اندروید</option>
                            <!--<option value="ios">فقط کاربران iOS</option>-->
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="appeto_push_url">لینک اطلاعیه</label>
                    </th>
                    <td>
                        <input type="url" id="appeto_push_url" name="appeto_push_url" class="regular-text">
                        <br/>
                        <small class="help">پس از کلیک کردن کاربر بر روی اطلاعیه این لینک باز میشود، در صورتی که این مقدار را خالی بگذارید، اپلیکیشن باز خواهد شد.</small>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="appeto_push_title">عنوان - حداکثر 100 کاراکتر</label>
                    </th>
                    <td>
                        <input type="text" id="appeto_push_title" name="appeto_push_title" class="regular-text" required="required">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="appeto_push_desc">توضیحات - حداکثر 100 کاراکتر</label>
                    </th>
                    <td>
                        <input type="text" id="appeto_push_desc" name="appeto_push_desc" class="regular-text" required="required">
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" name="send_appeto_push" id="submit" class="button button-primary" value="ارسال نوتیفیکیشن">
        </p>
    </form>
</div>