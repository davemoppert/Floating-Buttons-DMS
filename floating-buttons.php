<?php

/*

    Plugin Name: Floating Buttons

    Description: Add icons to your site with an excerpt hover effect and link.

    Author: Catapult Impact

    Author URI: http://www.catapultimpact.com

    Demo: http://catapultimpact.com/pagelines/floating-buttons/
	
	Pagelines: true

*/

class PageLinesSocialButtons{

	function __construct() {

		$this->base_url = sprintf( '%s/%s', WP_PLUGIN_URL,  basename(dirname( __FILE__ )));

		$this->icon = $this->base_url . '/icon.png';

		$this->base_dir = sprintf( '%s/%s', WP_PLUGIN_DIR,  basename(dirname( __FILE__ )));

		$this->base_file = sprintf( '%s/%s/%s', WP_PLUGIN_DIR,  basename(dirname( __FILE__ )), basename( __FILE__ ));

		// register plugin hooks...		

		$this->plugin_hooks();

	}
	
	function plugin_hooks(){

		// Always run		

		add_action( 'after_setup_theme', array( &$this, 'options' ));		

		add_action( 'wp_enqueue_scripts', array( &$this, 'socialbuttons_css' ) );	

		add_action( 'pagelines_page', array( &$this, 'socialbuttons' ));

	}
	
	function socialbuttons_css() {

		wp_register_style( 'socialbuttons-style', plugins_url( 'css/maincss.css', __FILE__ ) );

		wp_enqueue_style( 'socialbuttons-style' );
		
		wp_register_script( 'socialbuttons-script', plugins_url('/css/jquery.qtip-1.0.0-rc3.min.js', __FILE__),array( 'jquery' ) );

		wp_enqueue_script( 'socialbuttons-script' );

	}
   
   function options(){
	
		$options = array();

		$options[] = array(

			'key'		=> 'socialbutton_show_settings',

			'type'		=> 'multi',			

			'title'		=> __('SocialButtons Main Options', 'pagelines'),			

			'col'		=> 1,			

			'opts'	=> array(

				array(

					'key'	=> 'show_socialbutton',					

					'type' 	=> 'check',					

					'label' => 'Activate the SocialButtons globally?',					

					'help'	=> 'This option will activate the SocialButtons globally.'

				),

				array(

					'key'	=> 'socialbutton_position_type',

					'type' 	=> 'check',

					'label' => 'Make SocialButtons Position Fixed?'

				),

				array(

					'key'	=> 'socialbutton_orientation',

					'type' 	=> 'select',

					'label' => 'Select SocialButtons Orientation',

					'opts'	=> array(

						'right'	=> array('name'	=>'Right (Default)'),

						'left'			=> array('name'	=>'Left'),

					),
					'default'		=> 'right',

				),
				
				array(

					'key'	  => 'socialbutton_top_position',

					'type'    => 'text',

					'label'	  => 'SocialButtons top position.'

				),

			),

		);
		
		$options[] = array(
			'key'		=> 'socialbuttons_array',
	    	'type'		=> 'accordion', 
			'col'		=> 2,
			'title'		=> __('SocialButton Setup', 'pagelines'), 
			'post_type'	=> __('SocialButton', 'pagelines'), 
			'opts'	=> array(
				array(
					'key'		=> 'title',
					'label'		=> __( 'SocialButton Title', 'pagelines' ),
					'type'		=> 'text'
				),
				array(
					'key'		=> 'text',
					'label'	=> __( 'SocialButton Text', 'pagelines' ),
					'type'	=> 'textarea'
				),
				array(
					'key'		=> 'link',
					'label'		=> __( 'SocialButton Link', 'pagelines' ),
					'type'		=> 'text'
				),
				array(
					'key'		=> 'newwindow',
					'label'		=> __( 'Open in new window', 'pagelines' ),
					'type'		=> 'check'
				),
				array(
					'key'		=> 'icon',
					'label'		=> __( 'Icon (Icon Mode)', 'pagelines' ),
					'type'		=> 'select_icon'
				),
				array(
					'key'		=> 'image',
					'label'		=> __( 'SocialButton Image (Image Mode)', 'pagelines' ),
					'type'		=> 'image_upload'
				),
				array(
					'key'		=> 'color',
					'label'		=> __( 'Icon Color', 'pagelines' ),
					'type'		=> 'color'
				),
				

			)
	    );

		$option_args = array(

			'name'		=> 'SocialButtons',			

			'opts'		=> $options,			

			'icon'		=> 'icon-tag',			

			'pos'		=> 12		

		);

		

		pl_add_options_page( $option_args );
	
	}

	function socialbuttons() {
	
		// The SocialButtons
		
		$socialbuttons_array = pl_setting('socialbuttons_array');
		
		// Keep
		$show_socialbutton = (pl_setting('show_socialbutton')!='') ? pl_setting('show_socialbutton') : 1;
		$socialbutton_position_type = (pl_setting('socialbutton_position_type')!='') ? pl_setting('socialbutton_position_type') : 2;
		$social_buttons_orentation = (pl_setting('socialbutton_orientation')!='') ? pl_setting('socialbutton_orientation') : 'right';
		$social_buttons_top = (pl_setting('socialbutton_top_position')!='') ? pl_setting('socialbutton_top_position') : 10;
		
		if( is_array($socialbuttons_array) ){

			if($socialbutton_position_type == 1){

				$positionType = 'position: fixed;';

			}else{

				$positionType = '';

			}

			if($show_socialbutton == 1){

				if($social_buttons_orentation == 'right'){

					$tooltip = 'rightMiddle';

					$target = 'leftMiddle';

				}elseif($social_buttons_orentation == 'left'){

					$tooltip = 'leftMiddle';

					$target = 'rightMiddle';

				}

				$socialbuttons = count( $socialbuttons_array );
				
				$count = 1;
		?> 

				<script>

					jQuery(document).ready(function(){

						//jQuery("header").first().prepend(jQuery("#outer_social_div"));social_img_link

						jQuery('#social_img_cont').find('.social_img_div').each(function(){

							var tooltip_str = jQuery(this).attr('t');

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

								  padding: 6, 

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

		<?php 	foreach ($socialbuttons_array as $socialbutton){
		
					$text = pl_array_get( 'text', $socialbutton, 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'); 
					$title = pl_array_get( 'title', $socialbutton, 'socialbutton '. $count); 
					$link = pl_array_get( 'link', $socialbutton ); 
					$new_window = pl_array_get( 'newwindow', $socialbutton, 1 ); 
					$image = pl_array_get( 'image', $socialbutton ); 
					$icon = pl_array_get( 'icon', $socialbutton ); 
					$color = pl_hash( pl_array_get( 'color', $socialbutton ), false);

					if($new_window == 1){

						$open_in_new_window = 'target="_blank"';

					}else{

						$open_in_new_window = '';

					}
					
					if( !$image || $image == '' ){
						
						if(!$icon || $icon == ''){
							
							$icons = pl_icon_array();
							
							$icon = $icons[ array_rand($icons) ];
							
							$imgStr = '';
						
						}
						
					}else{
					
						$src = $image;
						
						$imgStr = '<img src='.$src.' />';
						
						$icon = '';
					
					}
					
					$chkemailstr = strtolower($title);
					

		?>

					<div class="social_img_div" t="<?php echo $text; ?>">

						<?php if($chkemailstr == "email"){

							$str = '[pl_modal type="iii icon icon-3x icon-'.$icon.'" title="Join our email list for extra savings" label="'.$imgStr.'"][gravityform id="1" name="Join our email list for extra savings" title="false"][/pl_modal]';

							echo do_shortcode($str);

						}else{ ?>

						<a style="color: <?php echo $color; ?>" class='iii icon icon-3x icon-<?php echo $icon; ?>' href="<?php echo $link; ?>" id="social_img_link" <?php echo $open_in_new_window; ?> >

							<?php echo $imgStr; ?>

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
		
		

	}


}
new PageLinesSocialButtons;
?>