<?php

/**
 * @package Robo.to Official Plugin
 * @version 1.0
 */
/*
Plugin Name: Robo.to Official Plugin
Plugin URI: http://wordpress.org/extend/plugins/roboto-official-plugin/
Description:  Easily add your latest Robo.to update to any post or page with the shortcode [robo.to].  You can also add Robo.to to your sidebar in the Widgets panel.
Author: Particle Programatica 
Version: 1.0
Author URI: http://particlebrand.com/
*/

/*-----------------------------------------------------------------*\

	Define a roboto object

\*-----------------------------------------------------------------*/

class roboto {
	public $uuid;
	public $username;
	public $size;
	public $border_size;
	public $loop;
	public $hide_blurb;
	public $offsetlogo;
	public $offsetTop;
	public $totalSize;

	public function __construct(){
		$this->fillFromDb();
	}

	public function fillFromDb(){
		$this->uuid 		= get_option('smirkr_uuid');
		$this->username 		= get_option('smirkr_username');
		$this->size 		= get_option('smirkr_size');
		$this->border_size 	= get_option('smirkr_border_size');
		$this->loop 		= get_option('smirkr_loop');
		$this->hide_blurb 	= get_option('smirkr_hide_blurb');
		$this->offsetlogo = $this->size+$this->border_size-66;
		$this->offsetlogoTop = $this->border_size+1;
		$this->totalSize = $this->size+($this->border_size*2);
	}

	public function url(){
		return "http://robo.to/swf/smirk.swf?hideBlerb={$this->hide_blurb}&loop={$this->loop}&target=_blank&cURI=rtmp%3A%2F%2Frobo.to%2Fsmirk_ul&uuid={$this->uuid}&href=http%3A%2F%2Frobo.to%2F{$this->username}&fx=1&sURL=http%3A%2F%2Frobo.to%2Famf%2Fgateway&params=0%2C1&sName=SmirksController";
	}

	public function embed(){
				$e .="<div style=\"position: relative; height:{$this->totalSize}px;\">";
				$e .= "<object width=\"{$this->size}\" height=\"{$this->size}\" style=\"height:{$this->size}px; width:{$this->size}px; border:{$this->border_size}px solid #111; padding:1px; display:block; position:absolute;\">";
        $e .= "	<param name=\"movie\" value=\"{$this->url()}\"></param>";
        $e .= "	<param name=\"allowFullScreen\" value=\"true\"></param>";
        $e .= "	<param name=\"allowscriptaccess\" value=\"always\"></param>";
        $e .= "	<embed src=\"{$this->url()}\" type=\"application/x-shockwave-flash\" allowscriptaccess=\"always\" allowfullscreen=\"true\" width=\"{$this->size}\" height=\"{$this->size}\"></embed>";
        $e .=" <param name=\"wmode\" value=\"transparent\" />";
        $e .= "</object>";
        $e .="<img src=\"http://particularplace.com/robo_widget/robotologo.png\"  width=\"67\" height=\"19\" alt=\"My Robo.to\" style=\" z-index:100; position:absolute; margin-top:{$this->offsetlogoTop}px; margin-left:{$this->offsetlogo}px;\" />";
        $e .="</div>";
        return $e;
    }
    
    public function update($post){
    	update_option('smirkr_uuid', $post['uuid']);
    	update_option('smirkr_username', $post['username']);
			update_option('smirkr_size', $post['size']);
			update_option('smirkr_border_size', $post['borderSize']);

		if(strtolower($post['loop']) == "on"){
			update_option('smirkr_loop', 1);
		}else{
			update_option('smirkr_loop', 0);
		}

		if(strtolower($post['hideBlurb']) == "on"){
			update_option('smirkr_hide_blurb', 1);
		}else{
			update_option('smirkr_hide_blurb', 0);
		}

		$this->fillFromDb();
	}

}

/*-----------------------------------------------------------------*\

	Widget Code

\*-----------------------------------------------------------------*/
add_action('widgets_init', 'roboto_load_widgets');

function roboto_load_widgets() {

    if ( !function_exists('register_sidebar_widget') )
            return;

    register_widget( 'Roboto_Widget' );
}

class Roboto_Widget extends WP_Widget {
    function Roboto_Widget()
    {
        /* Widget settings. */
        $widget_ops = array( 'classname' => 'roboto', 'description' => 'An example widget for robo.to' );

        /* Widget control settings. */
        $control_ops = array( 'height' => 300, 'id_base' => 'roboto-widget' );

        /* Create the widget. */
        $this->WP_Widget( 'roboto-widget', 'Roboto Widget', $widget_ops, $control_ops );

    }

    function widget( $args, $instance ) {
            extract( $args );
			$roboto = new roboto();

            /* Before widget (defined by themes). */
            echo $before_widget;

            /* Title of widget (before and after defined by themes). */
            if ( $title )
                    echo $before_title . $title . $after_title;
        
            echo $roboto->embed();
            
            /* After widget (defined by themes). */
            echo $after_widget;
    }
}


/*-----------------------------------------------------------------*\

	Short Code Code

\*-----------------------------------------------------------------*/

function smirk_shortcode( $atts, $content = null ) {
	$roboto = new roboto();

	$html .= '<div style="width:' . $roboto->size . 'px; height:' . $roboto->size . 'px; border:0;">';
	$html .= $roboto->embed();
	$html .= '</div>';
  return $html;
}

add_shortcode( 'robo.to', 'smirk_shortcode' );

add_action('admin_menu', 'my_plugin_menu');


/*-----------------------------------------------------------------*\

	Plugin/Options Code

\*-----------------------------------------------------------------*/

function my_plugin_menu() {
	add_options_page('Smirk Plugin Options', 'Robo.to', 'manage_options', 'my-unique-identifier', 'my_plugin_options');
}

function my_plugin_options() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}

	$roboto = new roboto();
	if (isset($_POST['update_message'])) {

		$roboto->update($_POST);

		echo '<div id="message" class="updated fade"><p><strong>Options Updated!  You can now add your Robo.to to any post or page with using the shortcode [robo.to]";</strong></p><p>If your theme has a sidebar you can add your Robo.to there in the Appearance->Widgets panel.</p></div>';
	}

	?>
	<div class=wrap>
	<h2>Robo.to</h2>
		<div style="width:auto; height:auto; background:#fff;">
			<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
				<div style="width:160px; height:160px; background-image:url(http://particularplace.com/robo_widget/roboto-smooth.png); display:relative; float:left;"></div>
	            <fieldset class="options" >
	              <table width="446" border="1">
			      <tr>
					    <td width="128">Your <a href='http://robo.to'>robo.to</a> UUID:</td>
					    <td width="203"><input name="uuid" type="text" id="uuid" value="<?php echo $roboto->uuid; ?>" size="54" /></td>
				      </tr>
					  <tr>
					  <tr>
					    <td width="128">Your Username</td>
					    <td width="203"><input name="username" type="text" id="username" value="<?php echo $roboto->username; ?>" size="54" /></td>
				      </tr>
					  <tr>
					    <td><label for="size2">Size:</label></td>
					    <td><input type="text" id="size" name="size" value="<?php echo $roboto->size; ?>" /></td>
				      </tr>
					  <tr>
					    <td><label for="borderSize2">Border Size:</label></td>
					    <td><input type="text" id="borderSize" name="borderSize" value="<?php echo $roboto->border_size; ?>" /></td>
				      </tr>
					  <tr>
					    <td><label for="loop2">Loop:</label></td>
					    <td><input type="checkbox" id="loop" name="loop" <?php if ($roboto->loop) echo 'checked' ; ?> /></td>
				      </tr>
					  <tr>
					    <td><label for="hideBlurb2">Hide Blurb:</label></td>
					    <td><input type="checkbox" id="hideBlurb" name="hideBlurb" <?php if ($roboto->hide_blurb) echo 'checked' ; ?> /></td>
				      </tr>
				  </table>
          <p class="submit">
              <input type="submit" name="update_message" class="button-primary" value="Save Changes" />
          </p>
				</fieldset><br>
				
				<img src="http://particularplace.com/robo_widget/robotoinstructions.jpg" width="652px" height="196px"  >
		  </form>
	  </div>

        
	</div><?php
}



?>