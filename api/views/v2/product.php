<?php
if(isset($extra->network) and $extra->network > 0) {
    $current_blog_details = get_blog_details( array( 'blog_id' => $extra->network ) );
    $blog_id = $extra->network;
    $ck = get_option("appeto_ck_".$blog_id, "");
    $cs = get_option("appeto_cs_".$blog_id, "");
    $localCk = get_option("appeto_localCk_".$blog_id, "");
    $localCs = get_option("appeto_localCs_".$blog_id, "");
    $woocommerce_api = $current_blog_details->siteurl."/";
    $currency = get_woocommerce_currency_symbol();
    $hashKey = get_option('appeto_secure_key_woo');
    $ck = base64_encode(appeto_encode_this_key($ck, $hashKey));
    $cs = base64_encode(appeto_encode_this_key($cs, $hashKey));
}
?>
<ion-view title="<?php echo $extra->name ?>" cache-view="false">
    <ion-content padding="false" ng-controller="WoocommerceProductCtrl" class="<?php echo $extra->content_classes." ".$extra->layout;?>"
             ng-init="localUrl='<?php echo site_url();?>';
             content_classes='<?php echo $extra->content_classes;?>';
             layout='<?php echo $extra->layout?>';
             currency_symbol='<?php echo get_woocommerce_currency_symbol();?>';
             <?php
             if(isset($extra->network) and $extra->network > 0) {
                 echo " fromNetwork=true; localCk='{$localCk}'; localCs='{$localCs}'; ck='{$ck}'; cs='{$cs}'; ";
             }
             ?>
             productId='<?php echo $extra->id?>';
             "
             >
        <ion-slide-box ng-if="product.images.length > 0" class="product-thumb-slider" auto-play="true" show-pager="true">

            <ion-slide ng-repeat="image in product.images" style="background: url('{{ image.src }}') no-repeat center center;
													  -webkit-background-size: cover;
													  -moz-background-size: cover;
													  -o-background-size: cover;
													  background-size: cover;"></ion-slide>

        </ion-slide-box>

        <?php
        if(isset($extra->variations)) {
            $extra->variations = array_filter($extra->variations);
            echo '<textarea id="selectedVariations" style="display: none;">'.json_encode($extra->variations).'</textarea>';
        }
        ?>

        <div class="transparent woocommerce-product text-box no-radius">
            <div>
                <div style="text-align: center" ng-bind-html="product.title"></div>
                <hr class="style-two" />
                <p ng-bind-html="product.description"></p>
                <span class="badge woo" ng-class="!product.in_stock ? 'badge-assertive' : 'badge-balanced'" ng-bind-html="!product.in_stock ? 'ناموجود' : 'موجود'"></span>
                <span class="badge badge-balanced woo" ng-if="product.featured">ویژه</span>
            </div>
            <hr class="style-two" />
            <div ng-if="attributes.length > 0 && product.parent_id <= 0 && product.variations.length <= 0">
                <h5>ویژگی ها</h5>
                <table>
                    <tr ng-repeat="attribute in attributes" ng-show="attribute.visible">
                        <td>{{ attribute.name }}</td>
                        <td>
                            <span class="badge badge-calm woo" style="margin-left: 5px" ng-repeat="option in attribute.options track by $index">{{ option }}</span>
                        </td>
                    </tr>
                </table>
            </div>
            <div ng-if="product.variations.length > 0">
                <h5>انتخاب ویژگی
                <span ng-show="variationsLoading"> - در حال دریافت اطلاعات</span>
                </h5>
                <table>
                    <tr ng-repeat="attribute in attributes" ng-show="attribute.visible && attribute.variation">
                        <td>{{ attribute.name }}</td>
                        <td>
                            <select style="width: 100%" class="variation_selector" data-attr-name="{{ attribute.name }}" data-attr-slug="{{ attribute.slug }}" data-attr-id="{{ attribute.id }}">
                                <option value="">یک گزینه را انتخاب کنید</option>
                                <option style="direction: rtl" ng-repeat="option in attribute.options track by $index">{{ option }}</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="row">
                <div ng-if="product.reviews_allowed && product.rating_count > 0" class="col"
                     ng-init="productRate=product.average_rating">
                    <ionic-ratings class="disable" ratingsobj="ratingsObjectDisable"></ionic-ratings>
                </div>

                <div class="col" ng-if="!disableToCard">
                        <span style="text-align: justify">
                        <i class="ion-pricetag second-color"></i> <span class="second-color">{{ product.price }} {{ currency_symbol }}</span>
                        <br ng-if="product.regular_price!=product.price && product.regular_price!=''" /><i class="ion-pricetag first-color" ng-if="product.regular_price!=product.price  && product.regular_price!=''"></i> <span ng-if="product.regular_price!=product.price && product.regular_price!=''" style="text-decoration: line-through" class="first-color">{{ product.regular_price }} {{ currency_symbol }}</span>
                    </span>
                </div>
            </div>
        </div>

        <div class="transparent woocommerce-product text-box no-radius" style="padding-bottom: 50px" ng-if="!disableToCard" ng-show="!product.sold_individually">
            <div>
                <div class="list">
                    <button class="button woo-plus-btn" ng-click="quantityNegativePlus(product.id, '+')">+</button>
                    <label class="item item-input woocommerce-input" style="margin-right: 18px">
                        <input type="number" id="quantity{{ product.id }}" disabled="disabled" ng-model="quantity" ng-change="setQuantity()">
                    </label>
                    <button class="button woo-negative-btn" ng-click="quantityNegativePlus(product.id, '-')">-</button>
                </div>
            </div>
        </div>

        <div class="row" ng-if="!disableToCard">
            <button ng-if="product.in_stock" class="button button-balanced wooAddToCard button-block" ng-init="setPageChanges();" id="wooAddToCard{{ product.id }}" ng-click="addToCard('{{ product.id }}', '{{ product.title }}', '{{ product.price }}', '{{ product.regular_price }}', '{{ product.images[0].src }}');">
                <i class="ion-android-cart"></i>
                افزودن به سبد خرید
            </button>
            <button ng-if="!product.in_stock" ng-init="setPageChanges();" class="button button-balanced wooAddToCard button-block">
                در حال حاضر این محصول موجود نیست.
            </button>
        </div>

        <div class="transparent woocommerce-product text-box no-radius" ng-show="haveComments" style="padding: 10px">
            <h5 class="text-center" ng-bind-html="commentsLoading"></h5>
            <div ng-if="comments.length > 0">
                <div ng-repeat="comment in comments" class="transparent woocommerce-product padding" >
                    <div><i class="ion-android-person"></i>  {{ comment.reviewer_name }}
                        <hr class="style-two" />
                        <p ng-bind-html="comment.review"></p>
                        <br />
                        <ionic-ratings class="disable" ratingsobj="commentRateObj(comment.rating, comment.id)"></ionic-ratings>
                    </div>
                </div>
                <div class="clearfix row"></div>
            </div>
        </div>

    </ion-content>
</ion-view>