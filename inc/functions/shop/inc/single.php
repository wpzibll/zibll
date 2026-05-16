<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-05 20:25:29
 * @LastEditTime : 2026-04-11 22:20:50
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|商城系统|商品页面函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

function zib_shop_single_header()
{
    global $post;
    // 面包屑
    $breadcrumbs = zib_shop_get_breadcrumbs();
    $is_mobile   = wp_is_mobile();

    //商品封面宣传区域
    $product_cover = zib_shop_get_product_cover($post);
    //商品选项区域
    $product_detail = zib_shop_get_product_detail($post);

    echo '<div class="single-product-header' . ($is_mobile ? ' mobile' : '') . '">';
    echo '<div class="container">';
    echo $breadcrumbs;
    echo '<div class="single-header-box">';
    echo '<div class="single-product-cover-box">' . $product_cover . '</div>';
    echo '<div class="single-product-detail-box">' . $product_detail . '</div>';
    echo '</div>';
    zib_shop_single_mobile_comment_drawer();
    echo '</div>';
    echo '</div>';
}
add_action('shop_product_page_header', 'zib_shop_single_header');

function zib_shop_single_mobile_comment_drawer()
{
    global $post;

    if (zib_shop_single_comment_is_show_tab()) {
        return;
    }

    //精选评论：评分3.5以上，评论字数大于3，按最新时间排序，取最大3条
    $comment_args = array(
        'post_id'    => $post->ID,
        'status'     => 'approve',
        'number'     => 50, //取50条，避免有些评论没有文字
        'orderby'    => 'date',
        'order'      => 'DESC',
        'parent'     => 0, //只取一级评论
        'meta_query' => array(
            array(
                'key'     => 'score',
                'value'   => 3.5,
                'compare' => '>=',
            ),
        ),
    );
    $comments          = get_comments($comment_args);
    $featured_comments = '';
    $c_i               = 0;
    if ($comments) {
        foreach ($comments as $comment) {
            $comment_content = zib_comment_filters(get_comment_text($comment));
            if (zib_new_strlen($comment_content) < 3) {
                continue;
            }

            if ($c_i >= 3) {
                break;
            }

            $c_i++;

            $user_id       = $comment->user_id;
            $author_avatar = zib_get_avatar_box($user_id, 'avatar-img avatar-mini');

            $score_data          = zib_get_comment_meta($comment->comment_ID, 'score_data', true);
            $order_data          = zib_get_comment_meta($comment->comment_ID, 'order_data', true);
            $options_active_name = $order_data['options_active_name'] ?? '';
            $options_active_name = $options_active_name ? '<span class="muted-2-color em09 ml6 text-ellipsis">' . $options_active_name . '</span>' : '';

            $score_average_badge = '';
            if ($score_data && !empty($score_data['average'])) {
                $score_average           = $score_data['average'];
                $score_average_name_data = zib_shop_get_score_average_name_data($score_average);
                $score_average_badge     = '<span class="shrink0 ml6 badg ' . $score_average_name_data[1] . ' badg-sm">' . $score_average_name_data[0] . '</span>';
            }

            $user_name = '匿名用户';
            if ($user_id) {
                $user = get_userdata($user_id);
                if (isset($user->display_name)) {
                    $user_name = $user->display_name;
                }
            }

            //图片
            $score_image      = $score_data['img_ids'] ?? [];
            $score_image_html = '';
            if ($score_image) {
                foreach ($score_image as $img_id) {
                    $img_src = zib_get_attachment_image_src($img_id);
                    if (!empty($img_src[0])) {
                        $lazy_attr = zib_get_lazy_attr('lazy_cover', $img_src[0], 'fit-cover');
                        $count     = count($score_image);

                        $count            = $count > 1 ? '<count>' . $count . '</count>' : '';
                        $score_image_html = '<div class="ml20 featured-comment-img"><img ' . $lazy_attr . ' alt="评价图片">' . $count . '</div>';
                        break;
                    }
                }
            }

            $user_name = '<div class="flex ac overflow-hidden"><div class="ml10 muted-color text-ellipsis shrink0">' . $user_name . '</div>' . $score_average_badge . $options_active_name . '</div>';

            $featured_comments .= '<div class="featured-comment-item mt20 flex ab">
                <div class="flex1">
                    <div class="flex ac mb6">' . $author_avatar . $user_name . '</div>
                    <div class="featured-comment-item-content">' . $comment_content . '</div>
                </div>
                ' . $score_image_html . '
            </div>';
        }
    }

    $comment_count = zib_shop_get_comment_count($post);
    $product_score = (float) zib_shop_get_product_score($post->ID);
    $score_badge   = '';
    if ($product_score >= 3.5) {
        $score_average_name_data = zib_shop_get_score_average_name_data($product_score);
        $score_badge             = '<span class="' . $score_average_name_data[1] . '">' . $product_score . '<span class="ml3 badg badg-sm ' . $score_average_name_data[1] . '">' . $score_average_name_data[0] . '</span></span>';
    }

    $box = '<div data-drawer="show" drawer-title="商品评价 · ' . $comment_count . '" drawer-selector=".comment-drawer" class="zib-widget mb10">
                <div class="flex ac jsb">
                    <div><b class="">商品评价</b>' . ($comment_count ? '<span class="ml6 em09 muted-2-color">' . $comment_count . '</span>' : '') . '</div>
                    <div class="flex ac">' . $score_badge . '<i class="ml6 fa fa-angle-right em12 muted-3-color"></i></div>
                </div>
                    ' . $featured_comments . '
            </div>';

    $drawer = '<div class="drawer-sm right comment-drawer"><div class="drawer-body">' . zib_shop_single_content_comment() . '</div></div>';

    echo $box;
    echo $drawer;
}

function zib_shop_single_comment_is_show_tab()
{
    return !wp_is_mobile();
}

//核心内容区域
function zib_shop_single_content()
{
    global $post;
    $is_mobile      = wp_is_mobile();
    $content_layout = zib_shop_get_product_content_layout();

    $product_id   = $post->ID;
    $tabs_args    = array();
    $article_args = array(
        'article' => array(
            'title'         => $is_mobile ? '详情' : '详情',
            'content_class' => 'product-page-article mb20',
            'route'         => true,
            'content'       => zib_shop_single_content_article(),
            'loader'        => zib_get_author_tab_loader('post'),
        ),
    );

    if (zib_shop_single_comment_is_show_tab()) {
        //商品评论，移动端不显示
        $comment_count        = zib_shop_get_comment_count($post);
        $comment_count        = $comment_count ? '<count class="opacity8 ml3 px12">' . $comment_count . '</count>' : '';
        $tabs_args['comment'] = [
            'title'         => '评价' . $comment_count,
            'content_class' => 'product-page-comment zib-widget',
            'route'         => true,
            'content'       => zib_shop_single_content_comment(),
            'loader'        => zib_get_author_tab_loader('comment'),
        ];
    }

    //更多tab栏目，可以自定义，例如：安装指导，常见问题，使用说明等
    $single_more_tab_options = zib_shop_get_single_tabs($post);

    if ($single_more_tab_options) {
        $tabs_args = array_merge($tabs_args, $single_more_tab_options);
    }

    if ($content_layout === 'full') {
        //给所有的content_class添加container
        foreach ($tabs_args as $tab_key => $tab) {
            $tabs_args[$tab_key]['content_class'] = 'container ' . ($tabs_args[$tab_key]['content_class'] ?? '');
        }
    }

    if ($tabs_args) {
        $tabs_args   = array_merge($article_args, $tabs_args);
        $tab_nav     = zib_get_main_tab_nav('nav', $tabs_args, 'product_single', false);
        $tab_content = zib_get_main_tab_nav('content', $tabs_args, 'product_single', false);
    } else {
        $tab_nav     = '<div class="mb10 mt6 muted-3-color separator">商品详情</div>';
        $tab_content = '<div class="product-page-article mb20">' . $article_args['article']['content'] . '</div>';
    }

    $content_html = '<div class="product-single-tab">';
    $content_html .= $tab_nav ? '<div class="single-tab-nav ' . ($content_layout === 'full' ? 'container' : '') . '">' . $tab_nav . '</div>' : '';
    $content_html .= $tab_content ? '<div class="single-tab-content">' . $tab_content . '</div>' : '';
    $content_html .= '</div>';

    //相关推荐
    $related_html = '';
    if (_pz('shop_single_related_s', true)) {
        $related_config = _pz('shop_single_related_opt');
        $ias_args       = array(
            'type'   => 'ias',
            'id'     => '',
            'class'  => 'product-lists-row',
            'loader' => zib_shop_get_lists_card_placeholder($related_config['list_style']), // 加载动画
            'query'  => array(
                'action'  => 'shop_single_related',
                'post_id' => $product_id,
            ),
        );

        $title = $related_config['title'] ?? '相关推荐';
        $title = $title ? '<div class="box-body notop"><div class="title-theme">' . $title . '</div></div>' : '';

        $related_html = '<div class="single-related-box mb20">' . $title . zib_get_ias_ajaxpager($ias_args) . '</div>';
    }

    $page_type = 'product';
    echo '<div class="container fluid-widget">';
    dynamic_sidebar('shop_' . $page_type . '_top_fluid');
    echo '</div>';

    if ($content_layout !== 'full') {
        echo '<div class="container">';
        echo '<div class="content-wrap">';
        echo '<div class="content-layout shop-single-content">';
        echo $content_html;
        echo $related_html;
        do_action('shop_product_page_content_after');
        echo '</div>';
        echo '</div>';
        if ($content_layout === 'side') {
            echo '<div class="sidebar">';
            dynamic_sidebar('shop_' . $page_type . '_sidebar');
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<div class="shop-single-content layout-full">';
        echo $content_html;
        echo '</div>';

        echo '<div class="container">';
        echo $related_html;
        do_action('shop_product_page_content_after');
        echo '</div>';
    }
    echo '<div class="container fluid-widget">';
    dynamic_sidebar('shop_' . $page_type . '_bottom_fluid');
    echo '</div>';

}
add_action('shop_product_page_content', 'zib_shop_single_content');

function zib_shop_single_content_article()
{

    $content_show_bg = zib_shop_get_product_in_turn_config(get_the_ID(), 'content_show_bg', 'off');
    $content         = get_the_content();
    $content         = apply_filters('the_content', $content);
    $content         = str_replace(']]>', ']]&gt;', $content);
    $content .= wp_link_pages(
        array(
            'before' => '<p class="text-center post-nav-links radius8 padding-6">',
            'after'  => '</p>',
            'echo'   => false,
        )
    );

    if (!$content) {
        $content = '<div class="text-center" style="padding:60px 0;"><img class="no-imgbox" style="width:280px;opacity: .7;" src="' . ZIB_TEMPLATE_DIRECTORY_URI . '/img/null.svg"><p style="margin-top:60px;" class="em09 muted-3-color separator">暂无详情介绍</p></div>';
    }

    $content_after = zib_shop_get_single_content_after();
    $html          = '<div class="' . ($content_show_bg === 'on' ? 'article product-article zib-widget' : 'product-article-full mb20') . '"><div class="wp-posts-content">' . $content . '</div>' . $content_after . '</div>';

    return $html;
}

function zib_shop_get_single_content_after()
{
    global $post;
    $content_after = zib_shop_get_product_cat_config($post, 'content_after');
    if (!$content_after) {
        $content_after = _pz('shop_content_after');
    }

    if (!$content_after) {
        return;
    }

    return '<div class="content-after-box">' . $content_after . '</div>';
}

// 商品评论
function zib_shop_single_content_comment()
{

    $file = ZIB_SHOP_REQUIRE_URI . 'template/comments.php';

    //将显示内内容改为返回
    ob_start();
    comments_template($file, true);
    $content = ob_get_clean();

    return $content;
}

// 商品封面宣传区域
function zib_shop_get_product_cover($post = null)
{
    if (!is_object($post)) {
        $post = get_post($post);
    }

    //商品参数
    $product_id      = $post->ID;
    $product_configs = zib_shop_get_product_config($product_id);
    $cover_images    = explode(',', ($product_configs['cover_images'] ?? ''));
    $cover_videos    = $product_configs['cover_videos'] ?? [];

    $slides = array();
    if (is_array($cover_images) && $cover_images) {
        foreach ($cover_images as $index => $cover_id) {
            if (!$cover_id || !is_numeric($cover_id)) {
                continue;
            }

            $img_url = zib_get_attachment_image_src((int) $cover_id, 'full');
            if (!empty($img_url[0])) {
                $slides[] = array(
                    'video'      => $cover_videos[$index]['url'] ?? '',
                    'background' => $img_url[0],
                );
            }
        }
    }

    if (!$slides) {
        $slides = [
            array(
                'background' => zib_shop_get_product_thumbnail_url($post, 'full') ?: ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail.svg',
                'video'      => $cover_videos[0]['url'] ?? '',
            ),
        ];
    }

    //添加选项图片
    $opt_args = $product_configs['product_options'] ?? [];
    if ($opt_args) {
        foreach ($opt_args as $opt_key => $opt) {
            if (isset($opt['opts']) && is_array($opt['opts']) && $opt['opts']) {
                foreach ($opt['opts'] as $opt_value_key => $opt_value) {
                    if (isset($opt_value['image']) && $opt_value['image']) {
                        $slides[] = array(
                            'background' => $opt_value['image'],
                            'attr'       => 'product-opt-index="' . $opt_key . '_' . $opt_value_key . '"',
                        );
                    }
                }
            }
        }
    }

    $more_btns = zib_shop_get_cover_more_btns($post);

    $slider_args = array(
        'class'           => 'product-cover-slider imgbox-container',
        'type'            => 'slider',
        'loop'            => false,
        'scale_height'    => true,
        'pagination_type' => 'fraction', //缩略图
        'gradient_bg'     => true,
        'scale'           => 100,
        'autoplay'        => false,
        'slides'          => $slides,
        'html'            => $more_btns ?: '',
    );
    $slider_content = zib_new_slider($slider_args, false);

    return $slider_content;
}

function zib_shop_get_cover_more_btns($post)
{
    if (!wp_is_mobile()) {
        return;
    }

    $share            = zib_shop_get_product_share_btn($post->ID, 'but cir mr6 c-white');
    $favorite         = '';
    $shop_author_show = _pz('shop_author_show', true);
    if ($shop_author_show) {
        $favorite = zib_shop_get_product_favorite_btn($post->ID, 'but cir mr6 c-white');
    }

    return '<div class="more-btns mt6">' . $favorite . $share . '</div>';
}

// 商品选项区域
function zib_shop_get_product_detail($post)
{
    global $zib_shop;
    if (!is_object($post)) {
        $post = get_post($post);
    }

    $v_data = zib_shop_get_product_single_vue_data($post);

    $tags_html = '<div class="product-tags-box scroll-x mini-scrollbar" v-if="!$.isEmptyObject(tags.tags)">
    <span v-for="tag in tags.tags" :key="\'tag-\' + tag.id">
        <span class="badg badge-tag badg-sm" :class="tag.class">{{ tag.name }}</span>
    </span>
</div>';

    //直接显示H1标题。有助于SEO
    $title_html = '<div class="single-product-title p-d-mb">
                    <h1 class="article-title"><span v-if="tags.important.name" :class="tags.important.class" class="badg badg-sm mr6">{{ tags.important.name }}</span><a href="' . esc_url(get_permalink($post->ID)) . '">' . get_the_title($post->ID) . '</a></h1>
                    <div class="single-product-desc muted-color mb10" v-html="desc" v-if="desc"></div>' . $tags_html . '
                </div>';

    $discount_html = '<div v-discount-badge="discount" data-hit-discount="discount_hit" class="product-discount-box scroll-x mini-scrollbar mt10" @click="discountModal(discount)"></div>';

    $price_html = '<div class="product-price-box flex jsb ac" v-if="$.isEmptyObject(important_discount)">
    <div class="flex ab" :class="pay_modo == \'points\' ? \'c-yellow\' : \'c-red\'">
        <div class="price-box mr10">
            <span class="pay-mark" v-html="show_mark"></span>
            <b class="price-str" v-price="prices.total_discount_price"></b>
        </div>
        <div class="text-left muted-2-color original-price-box flex" v-if="prices.total_price > prices.total_discount_price">
            <div class="px12-sm">优惠前：</div>
            <div class="original-price px12-sm">
                <span class="pay-mark" v-html="show_mark"></span>
                <span v-price="prices.total_price"></span>
            </div>
        </div>
    </div>
    <div class="sales-count muted-color muted-2-color px12-sm" v-if="sales_count">已售{{ sales_count }}</div>
</div>
<div v-else class="product-price-box flex ac jsb muted-box important-price-box" :class="important_discount.important_class">
        <div class="flex mr20 shrink0 xx important-price-left">
            <div class="flex ab">
                <div class="price-box mr6">
                    <span class="pay-mark" v-html="show_mark"></span>
                    <b class="price-str" v-price="prices.total_discount_price"></b>
                </div>
                    <div class="text-left original-price-box flex" v-if="prices.total_price > prices.total_discount_price">
                    <div class="px12-sm">原价:</div>
                    <div class="original-price px12-sm">
                        <span class="pay-mark" v-html="show_mark"></span>
                        <span v-price="prices.total_price"></span>
                    </div>
                </div>
            </div>
            <div class="flex ac">
                <div class="sales-count opacity8 px12-sm mt6" v-if="sales_count">已售{{ sales_count }}</div>
            </div>
       </div>
     <div class="flex ac jsb xx important-price-right overflow-hidden">
        <div class="em14 font-bold important-discount-name text-ellipsis">{{ important_discount.small_badge }}</div>
        <div class="flex jc mt6 hh" v-if="important_discount.time_limit_config && important_discount.time_limit_config.countdown">
            <span class="mr3 opacity8 px12">倒计时</span><span class="c-white badg badg-sm" int-second="auto" :data-countdown="important_discount.time_limit_config.end" data-over-text="已结束">00分00秒</span>
        </div>
        <div class="em09 opacity8 mt6 text-ellipsis" v-else-if="important_discount.desc">{{ important_discount.desc }}</div>
    </div>
</div>
';

    $price_discount_html = '<div class="product-price-discount-box p-d-mb">' . $price_html . $discount_html . '</div>';
    $title_price_html    = '<div class="product-title-price-box flex xx">' . $title_html . $price_discount_html . '</div>';

    $opts_html = '<div class="product-opts-box" v-if="product_options.length > 0 && !is_mobile">
    <div class="p-d-mb" v-for="(opt,opt_index) in product_options" :key="\'opt-\' + opt_index">
        <div class="product-opt-title-box flex ac jsb">
            <div class="product-opt-title">{{ opt.name }}</div>
            <div class="pointer muted-2-color ml10 em09" @click="switchOptView(opt)" v-if="optHasImg(opt)"><svg class="icon mr3" aria-hidden="true"><use xlink:href="#icon-img-lists"></use></svg>{{ opt.view_mode === \'img\' ? \'列表\' :  \'图片\'  }}</div>
        </div>
        <div class="product-opt-content" :class="(opt.view_mode === \'img\' ? \'mode-img\' : \'\' ) + \' count-\' + opt.opts.length">
            <div v-for="(item,item_index) in opt.opts" class="opt-item badg hollow" :class="item_index == options_active[opt_index] ? \'active\' : \'\'" @click="optChange(opt_index,item_index)" :key="\'opt-\' + opt_index + \'-\' + item_index">
                <span class="opt-item-image" v-if="item.image">
                    <img :data-src="item.image" :alt="opt.name + \'-\' + item.name" :src="lazy_src">
                    <div class="abs-center right-top pointer alone-imgbox-img flex jc" :data-src="item.image"><i class="fa fa-expand" aria-hidden="true"></i></div>
                </span>
                <span class="opt-item-name">{{ item.name }}</span>
            </div>
        </div>
    </div>
</div>';

    $params_html = '<div class="product-params-box p-d-mb" v-if="params.length > 0" @click="paramsModal(params)">
    <div class="flex">
        <div class="icon-header mr10"><i class="fa-fw fa fa-hdd-o"></i></div>
        <div class="params-items flex grow1 scroll-x no-scrollbar mr6">
            <span class="params-item text-center" v-for="(item,item_index) in params" :key="\'param-\' + item_index">
                <div class="em09 muted-3-color">{{ item.name }}</div>
                <div class="muted-color">{{ item.value }}</div>
            </span>
        </div>
        <i class="fa fa-angle-right em12  muted-3-color" v-if="is_mobile"></i>
    </div>
</div>';

    $service_html = '<div class="product-service-box p-d-mb" v-if="service.length > 0" @click="serviceModal(service)">
    <div class="flex ac">
        <div class="icon-header mr10"><i class="fa-fw fa fa-heart-o"></i></div>
        <div class="service-items muted-color grow1 scroll-x no-scrollbar mr6">
            <span class="service-item" :class="item_index !== 0 ? \'icon-spot\' : \'\'" v-for="(item,item_index) in service" :key="\'service-\' + item_index">{{ item.name }}</span>
        </div>
        <i class="fa fa-angle-right em12  muted-3-color" v-if="is_mobile"></i>
    </div>
</div>';

    $shipping_html = '<div class="product-shipping-box p-d-mb">
    <div class="flex muted-color">
        <div class="icon-header mr10"><i class="fa-fw fa fa-truck"></i></div>
        <div class="">
            <div class="flex ac shipping-title muted-color" v-html="shipping_title"></div>
            <div class="shipping-auto-desc em09 muted-3-color" v-if="shipping_desc" v-html="shipping_desc"></div>
        </div>
    </div>
</div>';

    //已经选择
    $selected_html = '<div class="product-selected-box p-d-mb" v-if="is_mobile && options_active.length > 0" @click="cartModalShow">
    <div class="flex ac">
        <div class="icon-header mr10"><svg class="icon fa-fw" aria-hidden="true"><use xlink:href="#icon-handbag"></use></svg></div>
        <div class="selected-title muted-color flex1 mr6" v-effect="$el.textContent = \'已选：\'+getOptionsActiveName(options_active)"></div>
        <i class="fa fa-angle-right em12 muted-3-color"></i>
    </div>
</div>';

    $service_combine_html = '<div class="product-service-combine-box p-d-mb">' . $params_html . $service_html . $shipping_html . $selected_html . '</div>';

    $buy_num_html = '<div class="product-buy-num-box p-d-mb flex ac" v-if="!is_mobile">
    <div v-spinner="selected_count" @change="countChange" @max-change="countMaxChange" min="1" :max="spinnerMax()" @min-change="notyf(\'最少选择一件\', \'warning\')"></div>
    <div class="ml10 flex">
        <div class="stock-desc muted-color" v-effect="$el.innerHTML = stockAllText(stock_all)"></div>
        <div class="limit-buy-desc c-yellow ml6" v-limit-buy="limit_buy"></div>
    </div>
</div>';

    $buttons_html = '<div class="product-pay-button-box" v-show="!is_mobile">
    <div class="flex jsb ac">
        <div class="but-group shop-paybtn-group">
            <button class="but c-yellow shop-add-cart-btn" @click.prevent="cartBtnClick" ref="addCartBtn">加入购物车</button>
            <button class="but jb-red shop-buy-btn" @click.prevent="orderBtnClick">立即购买</button>
        </div>
        <div class="shop-pay-action-btns flex jc" v-html="btns.favorite + btns.service + btns.share"></div>
    </div>
</div>';

    $content_html = $title_price_html . $opts_html . $service_combine_html . $buy_num_html . $buttons_html;

    $html = '<div class="v-shop-detail vue-mount flex xx" v-cloak @vue:mounted="mounted" v-config=\'' . esc_attr(json_encode($v_data)) . '\'>';
    $html .= $content_html;
    $html .= '</div>';
    return $html;
}

// 更多tab栏目
function zib_shop_get_single_tabs($post = null)
{
    if (!is_object($post)) {
        $post = get_post($post);
    }

    //商品参数
    $product_id = $post->ID;

    $single_tabs_config = zib_shop_get_product_config($product_id, 'single_tabs');
    $config_type        = $single_tabs_config['type'] ?? '';

    if ($config_type === 'disable') {
        return;
    }

    if ($config_type === 'custom') {
        $_tabs = $single_tabs_config['tabs'] ?? array();
    } else {
        $cat_config  = zib_shop_get_product_cat_config($product_id, 'single_tabs', 'type');
        $config_type = $cat_config['type'] ?? '';

        if ($config_type === 'disable') {
            return;
        }
        if ($config_type === 'custom') {
            $_tabs = $cat_config['tabs'] ?? array();
        } else {
            $theme_config = _pz('shop_single_tabs', array());
            $_tabs        = $theme_config['tabs'] ?? array();
        }
    }

    if (!isset($_tabs[0])) {
        return;
    }

    $tabs = array();
    $i    = 1;
    foreach ($_tabs as $config) {
        if (!empty($config['title']) && !empty($config['content'])) {
            $content           = apply_filters('the_content', $config['content']);
            $tabs['tab-' . $i] = array(
                'title'         => $config['title'],
                'content_class' => 'single-tabs-more',
                'content'       => '<div class="zib-widget article product-article"><div class="wp-posts-content">' . $content . '</div></div>',
            );
            $i++;
        }
    }

    return $tabs;
}

// 面包屑
function zib_shop_get_breadcrumbs()
{
    global $post, $zib_shop;
    if ('shop_product' != $post->post_type || !_pz('shop_breadcrumbs_s', true) || wp_is_mobile()) {
        return;
    }

    $html = '';
    $icon = '<i class="fa fa-map-marker"></i> ';

    // 网站首页
    if (_pz('shop_breadcrumbs_home', true)) {
        $html .= '<li><a href="' . home_url() . '">' . $icon . '首页</a></li>';
        $icon = '';
    }

    // 商城首页
    if (_pz('shop_breadcrumbs_shop_home', true)) {
        $home_url = zib_shop_get_home_url();
        if ($home_url) {
            $home_name = _pz('shop_breadcrumbs_shop_home_name') ?: '商城';
            $html .= '<li><a href="' . esc_url($home_url) . '">' . $icon . $home_name . '</a></li>';
            $icon = '';
        }
    }

    // 商品分类
    $product_categories = get_the_terms($post, 'shop_cat');
    if ($product_categories) {
        $args = array(
            'separator' => '</li><li>',
            'link'      => true,
            'format'    => 'name',
        );

        $parents = '<li>' . $icon . get_term_parents_list($product_categories[0]->term_id, 'shop_cat', $args) . '</li>';
        $html .= str_replace('<li></li>', '', $parents);
        $icon = '';
    }

    // 商品名称
    $html .= '<li>' . $icon . zib_str_cut($post->post_title, 0, 20) . '</li>';
    return '<ul class="breadcrumb">' . $html . '</ul>';
}

//文章页面显示底部tabbar
function zib_shop_single_footer_tabbar($btn)
{

    global $post;
    if (!is_singular('shop_product')) {
        return $btn;
    }

    $service     = zib_shop_get_author_contact_link($post->post_author, 'tabbar-item'); //客服按钮
    $count       = zib_shop_get_cart_count();
    $url         = zib_shop_get_cart_url();
    $icon        = '<svg class="icon" aria-hidden="true"><use xlink:href="#icon-shopping-cart"></use></svg>';
    $cart_button = '<a class="tabbar-item tabbar-cart" href="' . $url . '">' . $icon . '<text>购物车</text><badge cart-count="' . $count . '">' . ($count ?: '') . '</badge></a>';

    $pay = '
    <div class="but-group shop-paybtn-group">
            <button class="but jb-yellow-2" data-onclick=".shop-add-cart-btn">加入购物车</button>
            <button class="but jb-red" data-onclick=".shop-buy-btn">立即购买</button>
        </div>';

    $shop_author_show = _pz('shop_author_show', true);
    if ($shop_author_show) {
        $author_url    = zib_shop_get_author_url($post->post_author);
        $author_avatar = zib_get_avatar_box($post->post_author, 'avatar-mini', false, false);
        $author_btn    = '<a class="tabbar-item" href="' . $author_url . '"><icon>' . $author_avatar . '</icon><text>店铺</text></a>';

        return $author_btn . $service . $cart_button . $pay;
    }

    $favorite = zib_shop_get_product_favorite_btn($post->ID, 'tabbar-item');
    $btn      = $service . $favorite . $cart_button . $pay;
    return $btn;

}
add_filter('footer_tabbar', 'zib_shop_single_footer_tabbar');

//获取内容区布局配置
function zib_shop_get_product_content_layout($product_id = 0)
{
    if (!$product_id) {
        $product_id = get_the_ID();
    }

    $config = zib_shop_get_product_in_turn_config($product_id, 'content_layout', 'full');
    return $config;
}

function zib_shop_single_body_sidebar_class($classes)
{
    $classes[] = 'site-layout-2';
    return $classes;
}

function zib_shop_single_frontend_set_input_array($input_array, $post_id)
{
    if (!is_singular('shop_product')) {
        return $input_array;
    }

    $page_input   = array();
    $page_input[] = array(
        'name' => __('标题', 'zibll'),
        'id'   => 'post_title',
        'std'  => get_the_title($post_id),
        'type' => 'text',
    );
    $page_input[] = array(
        'name' => __('阅读数', 'zibll'),
        'id'   => 'views',
        'std'  => get_post_meta($post_id, 'views', true),
        'type' => 'number',
    );

    $page_input[] = array(
        'id'       => 'content_layout',
        'type'     => 'radio',
        'inline'   => true,
        'name'     => '页面布局',
        'value'    => zib_shop_get_product_config($post_id, 'content_layout'),
        'question' => '商品详情页宽度填充方式',
        'options'  => array(
            ''     => '默认',
            'full' => '全屏',
            'box'  => '适应内容宽度',
            'side' => '适应内容宽度+侧边栏',
        ),
    );

    $page_input[] = array(
        'id'       => 'content_show_bg',
        'type'     => 'radio',
        'inline'   => true,
        'name'     => '详情背景盒子',
        'value'    => zib_shop_get_product_config($post_id, 'content_show_bg'),
        'question' => '商品详情页的内容部分是否显示背景盒子，常用图片作为商品介绍，推荐隐藏，如果选择了带侧边栏布局则建议开启',
        'options'  => array(
            ''    => '默认',
            'on'  => '显示',
            'off' => '隐藏',
        ),
    );

    return $page_input;
}

function zib_shop_single_frontend_set_save($object_data, $type)
{
    //判断文章类型
    if ($type !== 'post' && get_post_type($object_data) !== 'shop_product') {
        return;
    }
    $post_id          = $object_data->ID;
    $config           = zib_shop_get_product_config($post_id);
    $update_post_meta = array('content_layout', 'content_show_bg');
    foreach ($update_post_meta as $meta) {
        if (isset($_POST[$meta])) {
            $config[$meta] = $_POST[$meta];
        }
    }

    update_post_meta($post_id, 'product_config', $config);
}
add_action('zib_frontend_set_save', 'zib_shop_single_frontend_set_save', 10, 2);
