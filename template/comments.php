<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-04-11 21:36:20
 * @LastEditTime : 2026-04-11 15:28:11
 */
defined('ABSPATH') or die('无法直接加载此文件.');

global $post;
$post_id = $post->ID;
if (!comments_open($post_id) || _pz('close_comments')) {
	return;
}

$count_t = $post->comment_count;
$user_id = get_current_user_id();

$closeTimer = (strtotime(current_time('Y-m-d G:i:s')) - strtotime(get_the_time('Y-m-d G:i:s'))) / 86400;
?>
<div class="theme-box" id="comments">
	<div class="box-body notop">
		<div class="title-theme"><?php echo _pz('comment_title') ?>
			<?php echo $count_t ? '<small>共' . $count_t . '条</small>' : '<small>抢沙发</small>'; ?></div>
	</div>

	<div class="no_webshot main-bg theme-box box-body radius8 main-shadow">
		<?php if (get_option('comment_registration') && !$user_id) { ?>
			<?php if (!zib_is_close_sign()) { ?>
				<div class="comment-signarea text-center box-body radius8">
					<h3 class="text-muted em12 theme-box muted-3-color">请登录后发表评论</h3>
					<p>
						<a href="javascript:;" class="signin-loader but c-blue padding-lg"><i class="fa fa-fw fa-sign-in mr10" aria-hidden="true"></i>登录</a>
						<?php echo !zib_is_close_signup() ? '<a href="javascript:;" class="signup-loader ml10 but c-yellow padding-lg">' . zib_get_svg('signup',null,'icon mr10') . '注册</a>' : ''; ?>
					</p>
					<?php zib_social_login(); ?>
				</div>
			<?php } ?>
		<?php } elseif (get_option('close_comments_for_old_posts') && $closeTimer > get_option('close_comments_days_old')) { ?>
			<div class="comment-signarea text-center box-body">
				<div class="text-muted em12 separator">文章评论已关闭</div>
			</div>
		<?php } elseif ($user_id && zib_user_is_ban($user_id)) {
			//封号
			echo '<div class="muted-box" style="padding:0 10px;">' . zib_get_user_ban_nocan_info($user_id, '暂时无法评论', 10, 200) . '</div>';
		?>
		<?php } else { ?>
			<?php echo zib_get_respond_mobile('#respond', esc_attr(_pz('comment_text'))); ?>
			<div id="respond" class="mobile-fixed">
				<div class="fixed-body"></div>
				<form id="commentform">
					<div class="flex ac">
						<div class="comt-title text-center flex0 mr10">
							<?php
							if ($user_id) {
								$avatar_img = zib_get_data_avatar($user_id);
								$vip_icon   = zibpay_get_vip_icon(zib_get_user_vip_level($user_id), 'em12 mr3');
								$vip_icon   = $vip_icon ? $vip_icon : '';

								echo '<div class="comt-avatar mb10">' . $avatar_img . '</div>';
								echo '<p class="text-ellipsis muted-2-color">' . $vip_icon . $user_identity . '</p>';
							} else {
								echo '<div class="comt-avatar mb10">' . zib_get_data_avatar($user_id) . '</div>';
								echo '<p class="" data-toggle-class="open" data-target="#comment-user-info" data-toggle="tooltip" title="填写用户信息">' . ($comment_author ? $comment_author : '昵称') . '</p>';
							}
							?>
						</div>
						<div class="comt-box grow1">
							<div class="action-text mb10 em09 muted-2-color"></div>
							<textarea placeholder="<?php echo esc_attr(_pz('comment_text')) ?>" autoheight="true" maxheight="188" class="form-control grin" name="comment" id="comment" cols="100%" rows="4" tabindex="1" onkeydown="if(event.ctrlKey&amp;&amp;event.keyCode==13){document.getElementById('submit').click();return false};"></textarea>
							<?php
							//人机验证
							if (_pz('verification_comment_s')) {
								$verification_input = zib_get_machine_verification_input('submit_comment');
								if ($verification_input) {
									echo '<div style="width: 230px;">' . $verification_input . '</div>';
								}
							}
							?>
							<div class="comt-ctrl relative">
								<div class="comt-tips">
									<?php
									comment_id_fields();
									echo zib_nonce_field('submit_comment');
									do_action('comment_form', $post->ID);
									?>
								</div>
								<div class="comt-tips-right pull-right">
									<a class="but c-red" id="cancel-comment-reply-link" href="javascript:;">取消</a>
									<button class="but c-blue pw-1em input-expand-submit comment-send" name="submit" id="submit" tabindex="5"><?php echo _pz('comment_submit_text') ?: '提交评论'; ?></button>
								</div>
								<div class="comt-tips-left">
									<?php
									if (!$user_id) {
										$require_name_email = get_option('require_name_email');
										$o_t                = $require_name_email ? '(必填)' : '';
										echo '<span class="dropup relative" id="comment-user-info"' . ($require_name_email ? ' require_name_email="true"' : '') . '>';
										echo '<a class="but mr6" data-toggle-class="open" data-target="#comment-user-info" href="javascript:;"><i class="fa fa-fw fa-user"></i><span class="hide-sm">昵称</span></a>';
										echo '<div class="dropdown-menu box-body" style="width:250px;">';
										echo '<div class="mb20">';
										echo '<p>请填写用户信息：</p>';
										echo '<ul>';
										echo '<li class="line-form mb10">';
										echo '<input type="text" name="author" class="line-form-input" tabindex="1" value="' . esc_attr($comment_author) . '" placeholder="">';
										echo '<div class="scale-placeholder">昵称' . $o_t . '</div>';
										echo '<div class="abs-right muted-color"><i class="fa fa-fw fa-user"></i></div>';
										echo '<i class="line-form-line"></i>';
										echo '</li>';
										echo '<li class="line-form">';
										echo '<input type="text" name="email" class="line-form-input" tabindex="2" value="' . esc_attr($comment_author_email) . '" placeholder="">';
										echo '<div class="scale-placeholder">邮箱' . $o_t . '</div>';
										echo '<div class="abs-right muted-color"><i class="fa fa-fw fa-envelope-o"></i></div>';
										echo '<i class="line-form-line"></i>';
										echo '</li>';
										echo '</ul>';
										echo '</div>';
										zib_social_login();
										echo '</div>';
										echo '</span>';
									}
									if (_pz('comment_smilie')) {
										echo zib_get_input_expand_but('smilie');
									}
									if (_pz('comment_code')) {
										echo zib_get_input_expand_but('code');
									}
									if (_pz('comment_img')) {
										echo zib_get_input_expand_but('image', _pz('comment_upload_img', false), 'comment');
									}
									if (_pz('comment_quick_s')) {
										echo zib_get_input_expand_but('quick',_pz('comment_quick_often'), 'comment');
									}
									?>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
		<?php } ?>
		<?php ?>
		<div id="postcomments">
			<ol class="commentlist list-unstyled">
				<?php
				if(!zib_current_user_can('comment_view',$post)){
					if( $user_id){
						echo zib_get_nocan_info(get_current_user_id(), 'comment_view', '无法查看评论');
					}else{
						echo zib_get_null('请登录后查看评论内容', 30, 'null-user.svg', 'comment-null');
					}
				}elseif (have_comments()) {
					$comment_orderby_html = '';					
					if (_pz('comment_corderby', true) && _pz('comment_like_s', true)) {
						$corderby = !empty($_GET['corderby']) ? $_GET['corderby'] : '';
	
						global $wp_rewrite;
						$url = (add_query_arg('cpage', false,zib_get_current_url())); //筛选 过滤
						$url = preg_replace("/\/$wp_rewrite->comments_pagination_base-([0-9]{1,})/", "", $url);
	
						$item = '<li class="mr6' . (!$corderby || $corderby == 'comment_date_gmt' ? ' active' : '') . '"><a rel="nofollow" class="comment-orderby" href="' . esc_url(add_query_arg('corderby', 'comment_date_gmt', $url)) . '">最新</a></li>';
						$item .= '<li class="' . ($corderby == 'comment_like' ? 'active' : '') . '"><a rel="nofollow" class="comment-orderby" href="' . esc_url(add_query_arg('corderby', 'comment_like', $url)) . '">最热</a></li>';
	
						//仅仅显示作者
						global $post;
						$author_id = $post->post_author;
	
						$only_author      = !empty($_GET['only_author']);
						$only_author_text = $only_author ? '查看全部' : '只看作者';
						$only_author      = '<a rel="nofollow" class="but comment-orderby btn-only-author p2-10" href="' . esc_url(add_query_arg('only_author', ($only_author ? false : $author_id), $url)) . '">' . $only_author_text . '</a>';
	
						$comment_orderby_html = '<div class="comment-filter tab-nav-theme flex ac jsb" win-ajax-replace="comment-order-btn">'; //筛选
						$comment_orderby_html .= '<ul class="list-inline comment-order-box" style="padding:0;">';
						$comment_orderby_html .= $item;
						$comment_orderby_html .= '</ul>';
						$comment_orderby_html .= $only_author;
						$comment_orderby_html .= '</div>';
					}

					echo $comment_orderby_html;
					wp_list_comments(
						array(
							'type'     => 'comment',
							'callback' => 'zib_comments_list',
						)
					);
					$loader = '<div style="display:none;" class="post_ajax_loader"><ul class="list-inline flex"><div class="avatar-img placeholder radius"></div><li class="flex1"><div class="placeholder s1 mb6" style="width: 30%;"></div><div class="placeholder k2 mb10"></div><i class="placeholder s1 mb6"></i><i class="placeholder s1 mb6 ml10"></i></li></ul><ul class="list-inline flex"><div class="avatar-img placeholder radius"></div><li class="flex1"><div class="placeholder s1 mb6" style="width: 30%;"></div><div class="placeholder k2 mb10"></div><i class="placeholder s1 mb6"></i><i class="placeholder s1 mb6 ml10"></i></li></ul><ul class="list-inline flex"><div class="avatar-img placeholder radius"></div><li class="flex1"><div class="placeholder s1 mb6" style="width: 30%;"></div><div class="placeholder k2 mb10"></div><i class="placeholder s1 mb6"></i><i class="placeholder s1 mb6 ml10"></i></li></ul><ul class="list-inline flex"><div class="avatar-img placeholder radius"></div><li class="flex1"><div class="placeholder s1 mb6" style="width: 30%;"></div><div class="placeholder k2 mb10"></div><i class="placeholder s1 mb6"></i><i class="placeholder s1 mb6 ml10"></i></li></ul></div>';
					echo $loader;
					zib_paginate_comments_links();
				} else {
						echo zib_get_null('暂无评论内容', 40, 'null.svg', 'comment comment-null');
						echo '<div class="pagenav hide"><div class="next-page ajax-next"><a href="#"></a></div></div>';
				}


				?>
			</ol>
		</div>
		<?php ?>
	</div>
</div>