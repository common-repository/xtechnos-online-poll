<?php 
 
class xTechnosPollWidget extends WP_Widget{

  function xTechnosPollWidget(){
	  
    $widget_ops = array('classname' => 'xTechnosPollWidget', 'description' => 'Display Polls' );
    $this->WP_Widget('xTechnosPollWidget', 'xTechnos Online Poll', $widget_ops);
  }
 
  function form($instance){
	  
    $instance = wp_parse_args( (array) $instance, array( 'title' => '','poll' => ''  ) );
    $title = $instance['title'];
	 $poll = $instance['poll'];
	
?>
  <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
  <p>
  	<label for="<?php echo $this->get_field_id('poll'); ?>">Select Poll:</label>
	<select name="<?php  echo $this->get_field_name('poll');?>">
     <?php 
			$args = array('post_type' => 'xtechnos_poll');
			$myposts = get_posts($args);		
	 	foreach( $myposts as $post ) :	setup_postdata($post);{
			$post_title =  get_the_title($post->ID);
			
			echo '<option value="'.$post->ID. '"';
			 if($poll==$post->ID){
				echo ' selected="selected"';
			}
			echo '>'. $post_title .'</option>';
		}
		endforeach;
	 ?> 
	</select>
  </p>
   
<?php

 
  }
 
  function update($new_instance, $old_instance){
	  	  
    $instance = $old_instance;
    $instance['title'] = $new_instance['title'];
	$instance['poll']=  $new_instance['poll'];	
    return $instance;
  }
  
  
 
  function widget($args, $instance){
    extract($args, EXTR_SKIP);
 
    echo $before_widget;
    $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
	$total_tests = empty($instance['total_tests']) ? ' ' : apply_filters('widget_title', $instance['total_tests']);
	$total_char = empty($instance['total_char']) ? ' ' : apply_filters('total_char', $instance['total_char']);
 	$poll = empty($instance['poll']) ? ' ' : apply_filters('widget_title', $instance['poll']);
    if (!empty($title))
    echo $before_title . $title . $after_title;;
 
    // WIDGET CODE GOES HERE
	global $xtn_online_poll;
    $xtn_online_poll -> xtechnos_online_poll_form($poll);	
					 
  }
 
}
add_action( 'widgets_init', create_function('', 'return register_widget("xTechnosPollWidget");') );?>