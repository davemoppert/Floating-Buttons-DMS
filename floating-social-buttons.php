<?php
/*
    Plugin Name: Floating Buttons DMS
    Description: Add icons to your site with an excerpt hover effect and link.
    Author: Catapult Impact
    Author URI: http://www.catapultimpact.com
    Demo: http://catapultimpact.com/pagelines/floating-buttons/
    Version: 1.0
	Pagelines: true
*/

define('CURR_SOCIAL_PLUG_IN_URL', get_option('siteurl').'/wp-content/plugins/'.dirname(plugin_basename(__FILE__)));
define('PRO_FOLDER', dirname(plugin_basename(__FILE__)));
define('PRO_PLUGIN_FOLDER', 'wp-content/plugins/' . PRO_FOLDER);

class SocialButtonsClass{

	function __construct(){
		register_activation_hook(__FILE__,array(&$this,'socialbuttons_plugin_install'));
		register_deactivation_hook(__FILE__ , array(&$this,'socialbuttons_plugin_uninstall'));

		add_action('admin_menu', array(&$this,'socialbuttons_plugin_admin_menu'));
		
		add_action( 'pagelines_page', array( &$this, 'SocialButton_page' ));
	}
	
	function SocialButton_page() {
		wp_register_style( 'social-style', CURR_SOCIAL_PLUG_IN_URL.'/css/maincss.css' );
		wp_enqueue_style( 'social-style' );
		
		wp_register_script( 'social-script', CURR_SOCIAL_PLUG_IN_URL.'/css/jquery.qtip-1.0.0-rc3.min.js' );
		wp_enqueue_script( 'social-script' );
		
		$social_buttons_fixedPosition = (get_option('social_buttons_fixedPosition')!="")?get_option('social_buttons_fixedPosition'):2;
		if($social_buttons_fixedPosition == 1){
			$positionType = 'position: fixed;';
		}else{
			$positionType = '';
		}
		
		$social_buttons_enable = (get_option('social_buttons_enable')!="")?get_option('social_buttons_enable'):1;
		if($social_buttons_enable == 1){
			$social_buttons_top = (get_option('social_buttons_top')!="")?get_option('social_buttons_top'):10;
			$social_buttons_orentation = (get_option('social_buttons_orentation')!="")?get_option('social_buttons_orentation'):'right';
			if($social_buttons_orentation == 'right'){
				$tooltip = 'rightMiddle';
				$target = 'leftMiddle';
			}elseif($social_buttons_orentation == 'left'){
				$tooltip = 'leftMiddle';
				$target = 'rightMiddle';
			}
			
			global $wpdb;
			$table_name = $wpdb->prefix . "social_button"; 
			$socialbuttons_data = $wpdb->get_results("SELECT * FROM $table_name WHERE active=1 ORDER BY id");
	?> 
			<script>
				jQuery(document).ready(function(){
					jQuery("header").first().prepend(jQuery("#outer_social_div"));
					jQuery('#social_img_cont').find('.social_img_div').each(function(){
						var tooltip_str = jQuery(this).find("img").attr('t');
						jQuery(this).qtip({
						   content: tooltip_str,
						   position: {
							  corner: {
								 tooltip: '<?php echo $tooltip; ?>',
								 target: '<?php echo $target; ?>'
							  }
						   },
						   show: {
							  when: { event: 'mouseover' },
							  ready: false
						   },
						   hide: {
							  when: { event: 'mouseout' },
							  ready: false
						   },
						   style: {
							  border: {
								 width: 1,
								 radius: 1
							  },
							  padding: 10, 
							  textAlign: 'center',
							  tip: true,
							  name: 'light'
						   }
						});
					});
				});
			</script>
		<div id="outer_social_div" style="position: relative;">	
			<div id="social_img_cont" style="<?php echo $positionType; ?> top: <?php echo $social_buttons_top; ?>px; <?php echo $social_buttons_orentation;?>: 0;">
	<?php 	foreach ($socialbuttons_data as $data){ 
				if($data->open_in_new_window == 1){
					$open_in_new_window = 'target="_blank"';
				}else{
					$open_in_new_window = '';
				}
	?>
				<div class="social_img_div">
					<?php if($data->social_net_name == "email"){
						$str = '[pl_modal type="social_img_link" title="Join our email list for extra savings" label="<img src='.$data->social_img_link.' t=\''.$data->social_tooltip_html.'\' />"][gravityform id="1" name="Join our email list for extra savings" title="false"][/pl_modal]';
						echo do_shortcode($str);
					}else{ ?>
					<a href="<?php echo $data->social_url;?>" id="social_img_link" <?php echo $open_in_new_window; ?> >
						<img id="social_img" t="<?php echo $data->social_tooltip_html;?>" src="<?php echo $data->social_img_link;?>" />
					</a>
					<?php } ?>
				</div>
				
	<?php 	
			}
	?>
				<div style="clear: both;"></div>
			</div>
		</div>
		<div style="clear: both;"></div>
<?php
		}
	}


	function get_socialbuttons(){
		global $wpdb;
		$num=1;
		$table_name = $wpdb->prefix . "social_button"; 
		$socialbuttons_data = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id");
		foreach ($socialbuttons_data as $data){
			if($data->active == 1){
				$active='<a href="?page=socialbuttons&deactivate='.$data->id.'" class="button">Deactivate</a>';
				$disabled='';
			}else {
				$active='<a href="?page=socialbuttons&activate='.$data->id.'" class="button">Activate</a>';
				$disabled='disabled="disabled"';
			}
			echo '<tr style="height:40px;"><td style="width: 100px;text-align:center;padding: 10px;" >'.$data->id.'</td>
				  <td style="width: 100px;text-align:center;padding: 10px;" valign="middle"> '.$data->social_net_name.' </td>
				  <td style="width: 100px;text-align:center;padding: 10px;" valign="middle"><img src="'.$data->social_img_link.'"></td>
				  <td style="width: 100px;text-align:center;padding: 10px;" ><a href="?page=add-SocialButton&edit='.$data->id.'" class="button" '.$disabled.'>Edit</a>        
				  </td><td style="width: 100px;text-align:center;padding: 10px;"> '.$active.' </td>
				  <td style="width: 100px;text-align:center;padding: 10px;" > <a href="?page=socialbuttons&delete='.$data->social_net_name.'" class="button">Delete</a> </td></tr>';
				$num++;}
	?>
		<form method="post" action="?page=add-SocialButton" enctype="multipart/form-data">
			<tr style="height:60px;">
				<td style="width: 100px;text-align:center;padding: 20px;" colspan="2">
					<input type="submit" class="button-primary" value="Add new social button" />
				</td>
			</tr>
		</form>
	<?php
	}

	function socialbuttons_plugin_install() {
		$this->socialbuttons_install();
		global $wpdb;
		
		$social_content = "Content for tool tip text.";
		$social_content = mysql_real_escape_string($social_content);
		
		$table_name = $wpdb->prefix . "social_button"; 
		$sql = "INSERT IGNORE INTO " . $table_name . " values ('','facebook','".CURR_SOCIAL_PLUG_IN_URL."/images/1_facebook.png','http://facebook.com','".$social_content."','1','1')";
		$wpdb->query( $sql );
		
		$table_name = $wpdb->prefix . "social_button"; 
		$sql = "INSERT IGNORE INTO " . $table_name . " values ('','twitter','".CURR_SOCIAL_PLUG_IN_URL."/images/2_twitter.png','http://twitter.com','".$social_content."','1','1')";
		$wpdb->query( $sql );
		
		$table_name = $wpdb->prefix . "social_button"; 
		$sql = "INSERT IGNORE INTO " . $table_name . " values ('','google+','".CURR_SOCIAL_PLUG_IN_URL."/images/5_google-plus.png','http://plus.google.com','".$social_content."','1','1')";
		$wpdb->query( $sql );
	}

	//SocialButton Table
	function socialbuttons_install(){
		global $wpdb;
		$table_name = $wpdb->prefix . "social_button"; 
		
			$sql = "CREATE TABLE IF NOT EXISTS " . $table_name . " (
						id mediumint(9) NOT NULL AUTO_INCREMENT,
						social_net_name VARCHAR(255) NOT NULL,
						social_img_link VARCHAR(255) NOT NULL,
						social_url VARCHAR(255) NOT NULL,
						social_tooltip_html TEXT,
						open_in_new_window tinyint(1) NOT NULL DEFAULT  '1',
						active tinyint(1) NOT NULL DEFAULT  '1',
						PRIMARY KEY (`id`),
						UNIQUE (
								`social_net_name`
						)
					);";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
	}

	function socialbuttons_plugin_uninstall() {
		global $wpdb;
		$table_name = $wpdb->prefix . "social_button"; 
		$socialbuttons_data = $wpdb->get_results("SELECT social_net_name FROM $table_name ORDER BY id");
		foreach ($socialbuttons_data as $data) {
			delete_option($data->social_net_name);
		}
		$sql = "DROP TABLE " . $table_name;
		$wpdb->query( $sql );
	}

	function socialbuttons_plugin_admin_menu() {
		add_menu_page('Social Buttons', 'Social Buttons', 'publish_posts', 'socialbuttons', array(&$this,'socialbuttons_main'), CURR_SOCIAL_PLUG_IN_URL.'/icon.png');
		add_submenu_page('socialbuttons','Settings','Settings', 'publish_posts', 'socialbuttons', array(&$this,'socialbuttons_main'));
		add_submenu_page('socialbuttons','Add New','Add New', 'publish_posts', 'add-SocialButton', array(&$this,'socialbutton_edit_page'));
	}

	function socialbuttons_main(){
		if(isset($_REQUEST['uni_setting_do']) && $_REQUEST['uni_setting_do'] == 1){
			$social_buttons_enable_option = (isset($_REQUEST['social_buttons_enable']) && $_REQUEST['social_buttons_enable']!="")?$_REQUEST['social_buttons_enable']:2;
			$social_buttons_fixedPosition = (isset($_REQUEST['social_buttons_fixedPosition']) && $_REQUEST['social_buttons_fixedPosition']!="")?$_REQUEST['social_buttons_fixedPosition']:2;
			$social_buttons_orentation_option = $_REQUEST['social_buttons_orentation'];
			$social_buttons_top_option = $_REQUEST['social_buttons_top'];
			update_option( 'social_buttons_enable', $social_buttons_enable_option );
			update_option( 'social_buttons_orentation', $social_buttons_orentation_option );
			update_option( 'social_buttons_top', $social_buttons_top_option );
			update_option( 'social_buttons_fixedPosition', $social_buttons_fixedPosition );
		}
	?>
		<div class="wrap" style="width:820px;"><div id="icon-options-general" class="icon32"><br /></div>
		<h2>Floating Social Buttons</h2>
		<div class="metabox-holder" style="width: 820px; float:left;">
			<small>Version 1.0</small>
		</div>
	<?php

		if(isset($_GET['delete'])){
			$social_net_name=$_GET['delete'];
			delete_option($social_net_name);
			global $wpdb;
			$table_name = $wpdb->prefix . "social_button"; 
			$sql = "DELETE FROM " . $table_name . " WHERE social_net_name='".$social_net_name."';";
			$wpdb->query( $sql );
	?>
	<div class="updated" id="message">
		<p> <strong> SocialButton Deleted </strong> </p>
	</div>
	<?php
		}
		
		if(isset($_GET['deactivate'])){
			$id=$_GET['deactivate'];
			global $wpdb;
			$table_name = $wpdb->prefix . "social_button"; 
			$sql = "UPDATE " . $table_name . " SET active='0' WHERE id='".$id."';";
			$wpdb->query( $sql );
	?>
	<div class="updated" id="message">
		<p> <strong>Social Button Deactivated </strong> </p> 
	</div>
	<?php
		}
		
		if(isset($_GET['activate'])){
			$id=$_GET['activate'];
			global $wpdb;
			$table_name = $wpdb->prefix . "social_button"; 
			$sql = "UPDATE " . $table_name . " SET active='1' WHERE id='".$id."';";
			$wpdb->query( $sql );
	?>
	<div class="updated" id="message">
		<p> <strong> SocialButton Activated </strong> </p>
	</div>
	<?php
		}
		$social_buttons_enable = (get_option('social_buttons_enable')!="")?get_option('social_buttons_enable'):1;
		$social_buttons_enable_checked = ($social_buttons_enable == 1)?'checked="checked"':'';
		$social_buttons_fixedPosition = (get_option('social_buttons_fixedPosition')!="")?get_option('social_buttons_fixedPosition'):2;
		$social_buttons_position_checked = ($social_buttons_fixedPosition == 1)?'checked="checked"':'';
		$social_buttons_orentation = (get_option('social_buttons_orentation')!="")?get_option('social_buttons_orentation'):'right';
		$social_buttons_orentation_rchecked = ($social_buttons_orentation == 'right')?'selected="selected"':'';
		$social_buttons_orentation_lchecked = ($social_buttons_orentation == 'left')?'selected="selected"':'';
		$social_buttons_top = (get_option('social_buttons_top')!="")?get_option('social_buttons_top'):10;
		
	?>	
		<form method="post" action="?page=socialbuttons" enctype="multipart/form-data">
			<div style="clear: both;"></div>
			<div class="postbox" id="content_box" style="margin-top: 20px;">
				<h3 style="padding: 7px 10px;" class="hndle"><span>Global Setting</span></h3>
				<div class="postbox" id="content_box" style="margin-top: 20px; margin-left: 30px; width: 90%;">
					<h3 style="padding: 7px 10px;" class="hndle"><span>Activate</span></h3>
					<div class="inside">
						<div id="titlediv">
							<div id="titlewrap">
								<input id="social_buttons_enable" <?php echo $social_buttons_enable_checked; ?> type="checkbox" name="social_buttons_enable" value="1" >
								<label style="cursor: pointer; margin-left: 10px;" for="social_buttons_enable">Show Floating Social Buttons?</label><font style="font-size:10px;">&nbsp;&nbsp;&nbsp;&nbsp;* Default is Yes</font>
							</div>
						</div>
					</div>
				</div>
				<div class="postbox" id="content_box" style="margin-top: 20px; margin-left: 30px; width: 90%;">
					<h3 style="padding: 7px 10px;" class="hndle"><span>Position type</span></h3>
					<div class="inside">
						<div id="titlediv">
							<div id="titlewrap">
								<input id="social_buttons_fixedPosition" <?php echo $social_buttons_position_checked; ?> type="checkbox" name="social_buttons_fixedPosition" value="1" >
								<label style="cursor: pointer; margin-left: 10px;" for="social_buttons_fixedPosition">Make Floating Social Buttons Position Fixed?</label><font style="font-size:10px;">&nbsp;&nbsp;&nbsp;&nbsp;* Default is No</font>
							</div>
						</div>
					</div>
				</div>
				<div class="postbox" id="social_buttons_orentation" style="margin-top: 20px; margin-left: 30px; width: 90%;">
					<h3 style="padding: 7px 10px;" class="hndle"><span>Floating Social Buttons Orientation</span></h3>
					<div class="inside">
						<div id="titlediv">
							<div id="titlewrap">
								<select name="social_buttons_orentation">								
									<option value="right" <?php echo $social_buttons_orentation_rchecked; ?> >Right</option>
									<option value="left" <?php echo $social_buttons_orentation_lchecked; ?> >Left</option>
								</select><font style="font-size:10px;">&nbsp;&nbsp;&nbsp;&nbsp;* Default is Right</font>
							</div>
						</div>
					</div>
				</div>
				<div class="postbox" id="page-links-to" style="margin-top: 20px; margin-left: 30px; width: 90%;">
					<h3 style="padding: 7px 10px;" class="hndle"><span>Floating Social Buttons Top Position</span></h3>
					<div class="inside">
						<p>Current Top Position</p>
						<div>
							<div style="display: inline;"><input type="radio" checked="checked" value="wp" name="social_buttons_top_check" id="txfx-links-to-choose-wp"></div>
							<div style="display: inline; padding-left: 10px;"> 
								<?php echo $social_buttons_top; ?> px <font style="font-size:10px;">&nbsp;&nbsp;&nbsp;&nbsp;* Default is 10px</font>
							</div>
						</div>
						<p style="margin-top: 32px;">
							<div style="display: inline;"><input type="radio" value="new_img" name="social_buttons_top_check" id="txfx-links-to-choose-alternate"></div>
							<div style="display: inline; padding-left: 10px;"><label for="txfx-links-to-choose-alternate"> Click to set top position: </label></div>
						</p>
						<div class="hide-if-js" id="txfx-links-to-alternate-section" style="margin-left: 30px; display: none;">
							<p>
								<input type="text" id="social_buttons_top" name="social_buttons_top" size="20" value="<?php echo $social_buttons_top; ?>" /> px
								<font style="font-size:10px;">&nbsp;&nbsp;&nbsp;&nbsp;* Example: '10' will be set to 10px</font>
							</p>
						</div>
						<script>
							(function($){
								$('input[type=radio]', '#page-links-to').change(function(){
									if ( 'wp' == $(this).val() ) {
										$('#txfx-links-to-alternate-section').fadeOut();
									} else {
										$('#txfx-links-to-alternate-section').fadeIn(function(){
											i = $('#txfx-links-to');
											i.focus().val(i.val());
										});
									}
								});
							})(jQuery);
						</script>
					</div>
				</div>
				<input type="hidden" name="uni_setting_do" value="1" />
				<input type="submit" class="button-primary" style="margin-bottom: 20px; margin-left: 30px;" value="Save Settings" />
			</div>
		</form>
	
		<table class="widefat" cellspacing="0">
			<thead>
				<tr>
					<th scope="col" id="name" class="manage-column column-name" colspan="6">Floating Social Buttons</th>
				</tr>
					<tr style="background: #efefef;">
					<td style="width: 100px;text-align:center;"> ID </td>
					<td style="width: 100px;text-align:center;"> Social Button </td>
					<td style="width: 100px;text-align:center;"> Image </td>
					<td style="width: 100px;text-align:center;"> Edit </td>
					<td style="width: 100px;text-align:center;"> Active </td>
					<td style="width: 100px;text-align:center;"> Delete </td>
				</tr>
			</thead>
			<tbody>
				<?php
				  $this->get_socialbuttons();
				?>
			</tbody>
		</table>
		
		
		
		
	</form>
	<?php 
	}

	function socialbutton_edit_page(){ 
		global $message;
		$option = (isset($_GET['edit']))?$_GET['edit']:'';
	?>

	<div class="wrap"><div id="icon-options-general" class="icon32"><br /></div>
		
	<?php 
		global $wpdb;
		$table_name = $wpdb->prefix . "social_button";
		
		if(isset($_GET["edit"])){
			$button_name = "Update";
			$option=$_GET['edit'];
			$socialbuttons_data = $wpdb->get_row("SELECT * FROM $table_name WHERE id='$option'");
			$form_submit_url = "?page=add-SocialButton&id=$socialbuttons_data->id";
		}else if(isset($_REQUEST['id'])){
			$button_name = "Update";
			$option = $_REQUEST['id'];
			$snn_surl = $_REQUEST['snn_surl'];
			$snn_tooltip = $_REQUEST['snn_tooltip'];
			$snn_title = $_REQUEST['snn_title'];
			$open_in_new_window = (isset($_REQUEST['open_in_new_window']))?$_REQUEST['open_in_new_window']:0;
			$active = $_REQUEST['txfx_status'];
			if($_REQUEST['txfx_social_img']=="wp"){
				$wpdb->query("UPDATE $table_name SET social_net_name='$snn_title', social_tooltip_html='$snn_tooltip', social_url='$snn_surl', open_in_new_window='$open_in_new_window', active='$active' where id=$option " );
			}else if($_REQUEST['txfx_social_img']=="select_img"){
				$social_img_link = CURR_SOCIAL_PLUG_IN_URL."/" . $_REQUEST["social_img"];
				$wpdb->query("UPDATE $table_name SET social_net_name='$snn_title', social_img_link='$social_img_link' ,social_tooltip_html='$snn_tooltip', social_url='$snn_surl', open_in_new_window='$open_in_new_window', active='$active' where id=$option " );
			}else if($_REQUEST['txfx_social_img']=="upload_img"){
				if ($_FILES["social_img"]["error"] > 0){
					$crb_message="Return Code: " . $_FILES["social_img"]["error"] . "<br />";
				}else if(file_exists(CURR_SOCIAL_PLUG_IN_URL."/images/" . $_FILES["social_img"]["name"])){
					$crb_message=$_FILES["social_img"]["name"] . " already exists. ";
				}else{
					$social_img_link=CURR_SOCIAL_PLUG_IN_URL."/images/" . $_FILES["social_img"]["name"];
					move_uploaded_file($_FILES["social_img"]["tmp_name"],"../".PRO_PLUGIN_FOLDER."/images/".$_FILES["social_img"]["name"]);
					$crb_message="Social Image icon uploaded sucessfully.";
				}
				$wpdb->query("UPDATE $table_name SET social_net_name='$snn_title', social_img_link='$social_img_link' ,social_tooltip_html='$snn_tooltip', social_url='$snn_surl', open_in_new_window='$open_in_new_window', active='$active' where id=$option " );
			}
			$socialbuttons_data = $wpdb->get_row("SELECT * FROM $table_name WHERE id='$option'");
			$form_submit_url = "?page=add-SocialButton&id=$socialbuttons_data->id";
			//$message = "Social button updated.";
		}else{
			$button_name = "Add Social Button";
			$form_submit_url = "?page=add-SocialButton&add=1";
			if(isset($_REQUEST['add']) && $_REQUEST['add'] == 1){
				$snn_surl = $_REQUEST['snn_surl'];
				$snn_tooltip = $_REQUEST['snn_tooltip'];
				$snn_title = $_REQUEST['snn_title'];
				$active = $_REQUEST['txfx_status'];
				$open_in_new_window = (isset($_REQUEST['open_in_new_window']))?$_REQUEST['open_in_new_window']:0;
				if($_REQUEST['txfx_social_img']=="select_img"){
					$social_img_link = CURR_SOCIAL_PLUG_IN_URL."/" . $_REQUEST["social_img"];
				}else if($_REQUEST['txfx_social_img']=="upload_img"){
					if ($_FILES["social_img"]["error"] > 0){
						$crb_message="Return Code: " . $_FILES["social_img"]["error"] . "<br />";
					}else if(file_exists(CURR_SOCIAL_PLUG_IN_URL."/images/" . $_FILES["social_img"]["name"])){
						$crb_message=$_FILES["social_img"]["name"] . " already exists. ";
					}else{
						$social_img_link=CURR_SOCIAL_PLUG_IN_URL."/images/" . $_FILES["social_img"]["name"];
						move_uploaded_file($_FILES["social_img"]["tmp_name"],"../".PRO_PLUGIN_FOLDER."/images/".$_FILES["social_img"]["name"]);
						$crb_message="Social Image icon uploaded sucessfully.";
					}
				}
				$wpdb->query("INSERT INTO $table_name SET social_net_name='$snn_title', social_img_link='$social_img_link', social_url='$snn_surl', social_tooltip_html='$snn_tooltip', open_in_new_window='$open_in_new_window', active='$active' " );
				$lastid = $wpdb->insert_id;
				$socialbuttons_data = $wpdb->get_row("SELECT * FROM $table_name WHERE id='$lastid'");
				$form_submit_url = "?page=add-SocialButton&id=$socialbuttons_data->id";
				//$message = "Social button added.";
				$button_name = "Update";
			}
		}
		
	?>

	<h2>Floating Social Buttons</h2>

	<?php 

		$achecked = '';
		$ichecked = '';
		if(isset($socialbuttons_data) && $socialbuttons_data->active != ""){
			if($socialbuttons_data->active == 1){
				$status = "Active";
				$achecked = 'checked="checked"';
			}elseif($socialbuttons_data->active == 0){
				$status = "Inactive";
				$ichecked = 'checked="checked"';
			}
		}else{
			$status = "Active";
			$achecked = 'checked="checked"';
		}
		
		$new_window_checked = '';
		if(isset($socialbuttons_data) && $socialbuttons_data->open_in_new_window != ""){
			($socialbuttons_data->open_in_new_window == 1)?$new_window_checked = 'checked="checked"':'';
		}else{
			$new_window_checked = 'checked="checked"';
		}
		
		wp_register_style( 'social-style', CURR_SOCIAL_PLUG_IN_URL.'/css/maincss.css' );
		wp_enqueue_style( 'social-style' );	  
		
		?>
		
		<div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
					<h2>Edit Floating Social Icons</h2>
					<div id="general" class="inside" style="padding: 10px;">
						<form method="post" action="<?php echo $form_submit_url; ?>" enctype="multipart/form-data">
							<div id="post-body-content" style="width: 79%;">
								<div class="postbox" id="social_net_name">
									<h3 style="padding: 7px 10px;" class="hndle"><span>Social Network Name</span></h3>
									<div class="inside">
										<div id="titlediv">
											<div id="titlewrap">
												<label for="title" id="title-prompt-text" style="visibility:hidden" class="hide-if-no-js">Enter Social Networking Name here</label>
												<input type="text" autocomplete="off" id="title" value="<?php echo (isset($socialbuttons_data))?$socialbuttons_data->social_net_name:''; ?>" tabindex="1" size="30" name="snn_title">
											</div>
										</div>
									</div>
								</div>
								<div class="postbox" id="page-links-to" style="position: inherit;">
									<h3 style="padding: 7px 10px;" class="hndle"><span>Social Image Icon</span></h3>
									<div class="inside" style="position: inherit;">
										<p>Current Social Image Icon</p>
										<div>
											<div style="display: inline;"><input type="radio" checked="checked" value="wp" name="txfx_social_img" id="txfx-links-to-choose-wp"></div>
											<div style="display: inline; padding-left: 10px;"><img style="position: absolute; margin-top: -8px;" src="<?php echo (isset($socialbuttons_data))?$socialbuttons_data->social_img_link:''; ?>"></div>
										</div>
										<p style="margin-top: 32px;">
											<label>
												<input type="radio" value="select_img" name="txfx_social_img" id="image_popup"> Click to select image from available icons
											</label>
										</p>
										<p style="margin-top: 32px;">
											<label>
												<input type="radio" value="upload_img" name="txfx_social_img" id="txfx-links-to-choose-alternate"> Click to upload new Image Icon
											</label>
										</p>
										<div class="hide-if-js" id="upload_option" style="margin-left: 30px; display: none;">
											<p>
												<input type="file" id="social_img" name="social_img" size="20" />
												<font style="font-size:10px;">&nbsp;&nbsp;&nbsp;&nbsp;* Select your social image icon</font>
											</p>
										</div>
										<script>
											(function($){
												$("input[type=radio]", "#page-links-to").change(function(){
													if ( "wp" == $(this).val() || "select_img" == $(this).val()) {
														$("#upload_option").fadeOut();
													} else {
														$("#upload_option").fadeIn(function(){
															i = $("#txfx-links-to");
															i.focus().val(i.val());
														});
													}
												});
											})(jQuery);
										</script>
										<div id="images_div">
											<?php 
												$files = glob("../wp-content/plugins/".dirname(plugin_basename(__FILE__))."/images/*.*");
												for ($i=0; $i<count($files); $i++){ 
													$num = $files[$i];
													?>
													<a href="javascript:void(0);" class="image_unselected">
														<img src="<?php echo $num ?>" >
													</a>
													<?php
												}
											?>
										</div>
										<div id="show_selected_image"></div>
										<input type="hidden" name="social_img" id="new_image_sel">
										<script>
											jQuery(document).ready(function(){
												jQuery("#image_popup").click(function(){
													jQuery("#mask").fadeIn(1000,function(){
														jQuery("#images_div").fadeIn(500);
													});
												});
												
												jQuery("#mask").click(function(){
													jQuery("#images_div").fadeOut(1000,function(){
														jQuery("#mask").fadeOut(500);
													});
												});
												
												jQuery(".image_unselected").each(function(){
													jQuery(this).click(function(){
														jQuery(this).removeClass("image_unselected");
														jQuery("#images_div").find(".image_selected").removeClass("image_selected").addClass("image_unselected");
														jQuery(this).addClass("image_selected");
														jQuery("#show_selected_image").html("<img src="+jQuery(this).find("img").attr("src")+" /><label> You Selected</label>");
														var str = jQuery(this).find("img").attr("src");
														var n = str.indexOf("images/");
														var image_name = str.substring(n);
														jQuery("#new_image_sel").val(image_name);
													});
													
													jQuery(this).click(function(){
														jQuery(this).removeClass("image_unselected");
														jQuery("#images_div").find(".image_selected").removeClass("image_selected").addClass("image_unselected");
														jQuery(this).addClass("image_selected");
														jQuery("#show_selected_image").html("<img src="+jQuery(this).find("img").attr("src")+" /><label> You Selected</label>");
														var str = jQuery(this).find("img").attr("src");
														var n = str.indexOf("images/");
														var image_name = str.substring(n);
														jQuery("#new_image_sel").val(image_name);
														jQuery("#images_div").fadeOut(1000,function(){
															jQuery("#mask").fadeOut(500);
														});
													});
												});
											});
										</script>
									</div>
								</div>
								<div class="postbox" id="revisionsdiv">
									<h3 style="padding: 7px 10px;" class="hndle"><span>Current Status is <b><?php echo $status ?></b></span></h3>
									<div class="inside">
										<p>Current Status</p>
										<div>
											<div style="display: inline;"><input type="radio" <?php echo $achecked ?> value="1" name="txfx_status" id="active_satus"></div>
											<div style="display: inline; padding-left: 10px;"> <label for="active_satus">Active</label> </div>
										</div>
										<p style="margin-top: 32px;">
											<input type="radio" value="0" name="txfx_status" <?php echo $ichecked ?> id="inactive_status">
											<label for="inactive_status" style="padding-left: 10px;" >Deactivate
											</label>
										</p>
										
									</div>
								</div>
								<div class="postbox" id="revisionsdiv">
									<h3 style="padding: 7px 10px;" class="hndle"><span>Open in window status</span></h3>
									<div class="inside">
										<input type="checkbox" value="1" <?php echo $new_window_checked; ?> name="open_in_new_window" id="in_new_window" />
										<label style="margin-left: 10px;" for="in_new_window">Open link in new window?
											<font style="font-size:10px;">&nbsp;&nbsp;&nbsp;&nbsp;* Default is Yes</font>
										</label>
									</div>
								</div>
								<div class="postbox" id="social_net_name">
									<h3 style="padding: 7px 10px;" class="hndle"><span>Social Url link goes here</span></h3>
									<div class="inside">
										<div id="titlediv">
											<div id="titlewrap">
												<label for="title" id="title-prompt-text" style="visibility:hidden" class="hide-if-no-js">Enter Social Networking Url</label>
												<input type="text" autocomplete="off" id="title" value="<?php echo (isset($socialbuttons_data))?$socialbuttons_data->social_url:''; ?>" tabindex="1" size="30" name="snn_surl">
											</div>
										</div>
									</div>
								</div>
								<div class="postbox" id="social_net_name">
									<h3 style="padding: 7px 10px;" class="hndle">
										<span>Content for Tooltip display</span>
									</h3>
									<div class="inside">
										<div id="titlediv">
											<div id="titlewrap">
												<label for="title" id="title-prompt-text" style="visibility:hidden" class="hide-if-no-js">Enter content for tooltip</label>
												<input type="text" autocomplete="off" id="title" value="<?php echo (isset($socialbuttons_data))?$socialbuttons_data->social_tooltip_html:''; ?>" tabindex="1" size="30" name="snn_tooltip">
											</div>
										</div>
									</div>
								</div>
								<div id="publishing-action" style="float: left; margin-top: 10px;">
									<img alt="" id="ajax-loading" class="ajax-loading" src="http://localhost/new_blog/wp-admin/images/wpspin_light.gif" style="visibility: hidden;">
									<input type="hidden" value="Update" id="original_publish" name="original_publish">
									<input type="submit" value="<?php echo $button_name; ?>" accesskey="p" tabindex="5" id="publish" class="button-primary" name="save">
								</div>
							</div>
						</form>
					</div>
			 </div>
			 <div id="mask"></div>
		<?php
	}

}

new SocialButtonsClass;
?>