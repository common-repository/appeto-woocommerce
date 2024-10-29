<?php
$current_blog_details = get_blog_details( array( 'blog_id' => $extra->id ) );
$blog_id = $extra->id;
$ck = get_option("appeto_ck_".$blog_id, "");
$cs = get_option("appeto_cs_".$blog_id, "");
$localCk = get_option("appeto_localCk_".$blog_id, "");
$localCs = get_option("appeto_localCs_".$blog_id, "");
$woocommerce_api = $current_blog_details->siteurl."/";
$currency = get_woocommerce_currency_symbol();
$hashKey = get_option('appeto_secure_key_woo');
$ck = base64_encode(appeto_encode_this_key($ck, $hashKey));
$cs = base64_encode(appeto_encode_this_key($cs, $hashKey));
?>
<ion-view cache-view="false">

    <div ng-controller="WoocommerceCtrl"
         ng-init="network=true; wooCurrentNetworkSiteUrl='<?php echo $woocommerce_api;?>'; wooCurrentNetworkSiteName='<?php echo $current_blog_details->blogname?>'; wooCurrentNetworkSiteId=<?php echo $blog_id;?>; ck='<?php echo  $ck; ?>'; cs='<?php echo  $cs; ?>'; localCs='<?php echo  $localCs ?>'; localCk='<?php echo  $localCk ?>'; localUrl='<?php echo  $woocommerce_api ?>'; currency_symbol='<?php echo  $currency ?>'">

        <ion-nav-bar class="bar-dark no-shadow" align-title="center" >

            <ion-nav-back-button ripple ripple-dark ripple-hold  class="button-icon icon ion-android-arrow-back custom-back">
            </ion-nav-back-button>

            <ion-nav-buttons side="right">
                <button ripple ripple-dark ripple-hold ng-click="doSearch();" class="button button-icon " ng-class="isSearchOpen ? 'ion-android-close' : 'ion-ios-search-strong'"></button>
                <button ripple ripple-dark ripple-hold class="button button-icon ion-android-cart woocommerce-card">
                    <span class="woocommerce-card-count second-color-bg"><i>0</i></span>
                </button>
            </ion-nav-buttons>

        </ion-nav-bar>

        <div class="woo-theme-1">

            <ion-tabs class="tabs-positive tabs-icon-top no-radius tabs-top tabs-striped">

                <ion-tab title="محصولات" icon-on="ion-bag" icon-off="ion-bag" on-select="onTabSelected('p')">
                    <div class="">
                        <div class="woocommerce">

                            <ion-content padding="false" class="page-content">

                                <ion-refresher on-refresh="doRefresh()" pulling-icon="ion-refresh"></ion-refresher>
                                <div class="transparent padding-content text-center" ng-show="products.length == 0" style="text-align: center">در حال دریافت اطلاعات</div>
                                <div class="row" ng-repeat="baseProduct in products">
                                    <div class="col col-50" ng-repeat="product in baseProduct.products">
                                        <a href="#/nav/woocommerce-v2/product/{{  product.appLink }}" style="text-decoration: none">
                                            <div class="card product">
                                                <div class="imageBox" ng-if="product.images[0].src" style="background: url('{{ product.images[0].src }}') no-repeat center center;
                                                        -webkit-background-size: cover;
                                                        -moz-background-size: cover;
                                                        -o-background-size: cover;
                                                        background-size: cover;">
                                                    <p style="visibility: hidden">&nbsp;</p>
                                                </div>
                                                <div class="item item-body">
                                                    <h3 class="garage-title">
                                                        {{ product.title }}
                                                    </h3>
                                                <span style="text-align: justify">
                                                    <i class="ion-pricetag second-color"></i> <span class="second-color">{{ product.price }} {{ currency_symbol }}</span>
                                                    <br ng-if="product.regular_price!=product.price && product.regular_price!=''" /><i class="ion-pricetag first-color" ng-if="product.regular_price!=product.price && product.regular_price!=''"></i> <span ng-if="product.regular_price!=product.price && product.regular_price!=''" style="text-decoration: line-through" class="first-color">{{ product.regular_price }} {{ currency_symbol }}</span>
                                                </span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>

                                <ion-infinite-scroll
                                    on-infinite="nextPage()"
                                    distance="1%">
                                </ion-infinite-scroll>

                            </ion-content>



                        </div>
                    </div>
                </ion-tab>

                <ion-tab title="دسته‌ها" icon-on="ion-clipboard" icon-off="ion-clipboard" on-select="onTabSelected('c')">
                    <ion-content padding="false" ng-controller="WoocommerceCategoriesCtrl" ng-init="load_categories();" class="page-content">
                        <div class="transparent padding-content text-center" ng-show="categories.length <= 0" style="text-align: center">{{ loadingText }}</div>
                        <div class="padding" style="padding-top: 0">
                            <div class="list no-shadow" style="margin-top: 0" ng-show="categories.length > 0">
                                <a ng-repeat="category in categories" class="item woo-category-box cards-shadow" ng-class="category.image != '' ? 'item-thumbnail-right' : ''" href="#/nav/woocommerce-v2/category/{{ category.applink }}">
                                    <img ng-src="{{ category.image }}" ng-if="category.image != ''" />
                                    <h2>{{ category.name }}</h2>
                                    <span class="badge badge-assertive" ng-class="category.image != '' ? '' : 'no-img'">{{ category.count }}</span>
                                </a>
                            </div>
                        </div>
                    </ion-content>
                </ion-tab>


            </ion-tabs>



        </div>


    </div>

</ion-view>