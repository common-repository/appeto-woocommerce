<ion-view title="<?php echo $title;?>">

    <div ng-controller="WordpressAuth" ng-init="slug='<?php echo $slug;?>'; wpAjaxUrl='<?php echo admin_url( 'admin-ajax.php' );?>'">

        <ion-nav-bar class="bar-dark" align-title="center">

            <ion-nav-back-button ripple ripple-dark ripple-hold  class="button-icon icon ion-android-arrow-back custom-back">
            </ion-nav-back-button>

        </ion-nav-bar>

        <ion-content padding="false" class="login-theme-2 page-content <?php echo $class;?>">

            <div class="padding">
                <div class="text-box text-center">
شما به این بخش دسترسی ندارید.
                </div>
            </div>

        </ion-content>

    </div>



</ion-view>
