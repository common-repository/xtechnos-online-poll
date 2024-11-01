<?php
/*
Plugin Name: xTechnos Online Poll
Plugin URI: http://www.xtechnos.com
Description: xTechnos Online Poll is a wordpress online poll, you can use poll widget or place poll in any page/post using short code, very easy to use, you can add new poll from admin panel and check results. You can use [xTechnos-Online-Poll] short code to place poll in any page/post. You can use xTechnos Online Poll widget in sidebar
Version: 2.1.0
Author: zagham.naseem
Author URI: http://xtechnos.com
License: GPL2
*/

/*  Copyright 2012 Syed Zagham Naseem (email : zagham.naseem@xtechnos.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

include_once('xtechnos-poll-widget.php');

class xTechnos_Online_Poll {

	var $meta_fields = array('x_choice1','x_choice2','x_choice3','x_choice4','x_choice1_res','x_choice2_res','x_choice3_res','x_choice4_res' );
	var $xtnop_db_version = "2.1.0";
	
	//Constructor
	function xTechnos_Online_Poll() {
		
		register_post_type( 'xtechnos_poll',
			$args= array(
				'labels' => array(
				'name' => __( 'xTechnos Poll' ),
				'add_new_item' => __("Add New Poll"),
				'edit_item'=> __("Edit Poll"),
				'singular_name' => __( 'xTechnos Poll' )
				),
		
			'public' => true,
			'capability_type' 	=> 'post',
			'hierarchical' 		=> false,
			'rewrite' 			=> true,
			'has_archive' => true,
			'show_ui'=>true,
			'show_in_menu'=>true,
			'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments','custom-fields','cats'  ),
			'can_export'		=> true
			)
		);
		add_action("manage_posts_custom_column", array(&$this,"my_custom_columns"));
		add_filter("manage_edit-xtechnos_poll_columns", array(&$this,"my_xtechnos_poll_columns"));
		add_action("admin_init", array(&$this,"admin_init"));
		add_action("wp_insert_post", array(&$this, "wp_insert_post"), 10, 2);
		add_action('admin_menu', array(&$this, 'register_my_custom_submenu_page'));
		add_shortcode('xTechnos-Online-Poll',array(&$this, 'xtechnos_online_poll_form'));
	}
	
	//Register Menu/Sub Menu
	function register_my_custom_submenu_page() {	
		add_submenu_page( 'edit.php?post_type=xtechnos_poll', __('View Results'), __('View Results'), 'manage_options',"xtn_onlinepoll_result", array(&$this,"xtn_onlinepoll_result")); 
	}
	
	//View Columns
	function my_xtechnos_poll_columns($columns){
		$columns = array(
			"cb" => "<input type=\"checkbox\" />",
			"title" => "Poll Title",
			"question" => "Question",
			"x_choice1" => "Option 1",
			"x_choice2" => "Option 2",
			"x_choice3" => "Option 3",
			"x_choice4"  => "Option 4",
			"result"  => 	"Result",
			'date' 	=> 	__('Published')
		);
		return $columns;
	}
	
	//Custom Columns
	function my_custom_columns($column){
			global $post;
			switch ($column)
			{			
				case "question":
					the_excerpt();
				break;
			
				case "x_choice1":
					$custom = get_post_custom();
					echo $custom["x_choice1"][0];
				break;
				case "x_choice2":
					$custom = get_post_custom();
					echo $custom["x_choice2"][0];
				break;
				case "x_choice3":
					$custom = get_post_custom();
					echo $custom["x_choice3"][0];
				break;
				case "x_choice4":
					$custom = get_post_custom();
					echo $custom["x_choice4"][0];
				break;
				
				case "result":
				$purl= get_bloginfo("wpurl");
				echo "<a href='".$purl."/wp-admin/edit.php?post_type=xtechnos_poll&page=xtn_onlinepoll_result&id=".$post->ID."'>View Result";
				break;
			}		
	}
	
	// When a post is inserted or updated
	function wp_insert_post($post_id, $post = null){
		
		if ($post->post_type == "xtechnos_poll"){
			// Loop through the POST data
			foreach ($this->meta_fields as $key){
				
				$value = @$_POST[$key];
				if (empty($value)){
					delete_post_meta($post_id, $key);
					continue;
				}
	
				// If value is a string it should be unique
				if (!is_array($value)){
					// Update meta
					if (!update_post_meta($post_id, $key, $value)){
						// Or add the meta data
						add_post_meta($post_id, $key, $value);
					}
				}else{
					// If passed along is an array, we should remove all previous data
					delete_post_meta($post_id, $key);
					
					// Loop through the array adding new values to the post meta as different entries with the same name
					foreach ($value as $entry)
						add_post_meta($post_id, $key, $entry);
				}
			}
		}
	}
	
	//Results
	function xtn_onlinepoll_result () {
		if(isset($_GET['id']) && $_GET['id'] !="" ) {
			$poll_id = $_GET['id'];
			$custom = get_post_custom($poll_id);		
			$res_choice1 = $custom["x_choice1_res"][0];
			$res_choice2 = $custom["x_choice2_res"][0];
			$res_choice3 = $custom["x_choice3_res"][0];
			$res_choice4 = $custom["x_choice4_res"][0];
			
			$res_choice1 = $res_choice1 - 1;
			$res_choice2 = $res_choice2 - 1;
			$res_choice3 = $res_choice3 - 1;
			$res_choice4 = $res_choice4 - 1;
			$total = $res_choice1 + $res_choice2 + $res_choice3 + $res_choice4;
			if($res_choice1 > 0){
				$res_choice1_per = floor(($res_choice1/$total) * 100);
			}else{
				$res_choice1_per = 0;
			}
			
			if($res_choice2 > 0){
			$res_choice2_per = floor(($res_choice2/$total) * 100);
			}else{
				$res_choice2_per = 0;
			}
			
			if($res_choice3 > 0){
			$res_choice3_per = floor(($res_choice3/$total) * 100);
			}else{
				$res_choice3_per = 0;
			}
			
			if($res_choice4 > 0){
			$res_choice4_per = floor(($res_choice4/$total) * 100);
			}else{
				$res_choice4_per = 0;
			}
		
				
			$xtechnos_poll_choice1 = $custom["x_choice1"][0];
			$xtechnos_poll_choice2 = $custom["x_choice2"][0];
			$xtechnos_poll_choice3 = $custom["x_choice3"][0];
			$xtechnos_poll_choice4 = $custom["x_choice4"][0];
			$my_post = get_post($poll_id); 
			$mytitle = $my_post->post_title; 
			$content = $my_post->post_content;
			$path= get_bloginfo('url');?>
            
        <style>
			#res_table {  }
			#res_table tr td{ border:1px solid #DFDFDF; border-color: #DFDFDF;}
			#res_table th { background-color: #DFDFDF; font-weight:normal; font-family: sans-serif;font-size: 12px; line-height: 1.4em; }
			#res_table { font-weight:normal; }
		</style>
		<h1 align="center">Poll Result</h1><br/>
		<strong>Shortcode:</strong><br/>
        <span style="margin-left:70px">[xTechnos-Online-Poll id =<?php echo $poll_id; ?>]</span> <br/><br/>
		<div id="main" align="center">      
        <?php 
		
		echo "<table  width='100%' align='center' cellspacing='0' id='res_table' border='0' >
		<tr bgcolor='black'>";
		echo "<th width='15%' class='manage-column'>" .'Poll Name'. "</th>" ;
		echo "<th width='15%'>" .'Question'. "</th>" ;
		
		if ($xtechnos_poll_choice1 !=""){
			echo "<th width='15%'>" .$xtechnos_poll_choice1. "</th>";
		}
		if ($xtechnos_poll_choice2 !=""){
			echo "<th width='15%'>" .$xtechnos_poll_choice2. "</th>";
		}
		if ($xtechnos_poll_choice3 !=""){
			echo "<th width='15%'>" .$xtechnos_poll_choice3. "</th>";
		}
		if ($xtechnos_poll_choice4 !=""){
			echo "<th width='15%'>" .$xtechnos_poll_choice4. "</th>";
		}	
		
		echo "</tr>";
		

			echo "<tr>";
			echo "<td align='center'>" . $mytitle. "</td>";
			echo "<td align='center'>" . $content. "</td>";		
			
			if ($xtechnos_poll_choice1 !=""){
				echo "<td align='center'>" .$res_choice1. "</td>";
			}
			if ($xtechnos_poll_choice2 !=""){
				echo "<td align='center'>" .$res_choice2. "</td>";
			}
			if ($xtechnos_poll_choice3 !=""){
				echo "<td align='center'>" .$res_choice3. "</td>";
			}
			if ($xtechnos_poll_choice4 !=""){
				echo "<td align='center'>" .$res_choice4. "</td>";
			}
				
	echo "</table>";
	?>
        </div> <br/><br/><br/>    
            
            
            
            
			<script type="text/javascript" src="<?php echo $path ?>/wp-content/plugins/xTechnos-Online-Poll/js/jquery-min.js"></script>
			<script src="<?php echo $path ?>/wp-content/plugins/xTechnos-Online-Poll/js/dhtmlxchart.js" type="text/javascript"></script>
            <link rel="STYLESHEET" type="text/css" href="<?php echo $path ?>/wp-content/plugins/xTechnos-Online-Poll/js/dhtmlxchart.css">
            
			<script>
            var data = [
                <?php if(isset($xtechnos_poll_choice1) && $xtechnos_poll_choice1 !=""){ ?>
                
                    {choise:'<?php echo $xtechnos_poll_choice1;?>', answer: '<?php echo $res_choice1_per; ?>'}
                    
                    <?php } if(isset($xtechnos_poll_choice2) && $xtechnos_poll_choice2 !=""){?>
                    
                    ,{choise:'<?php echo $xtechnos_poll_choice2;?>', answer: '<?php echo $res_choice2_per; ?>'}
                    
                    <?php } if(isset($xtechnos_poll_choice3) && $xtechnos_poll_choice3 !=""){?>
                    
                    ,{choise:'<?php echo $xtechnos_poll_choice3;?>', answer: '<?php echo $res_choice3_per; ?>'}
                    
                    <?php } if(isset($xtechnos_poll_choice4) && $xtechnos_poll_choice4 !=""){?>
                    
                    ,{choise:'<?php echo $xtechnos_poll_choice4;?>', answer: '<?php echo $res_choice4_per; ?>'}
                <?php } ?>
            ];
            window.onload = function(){
            var pieChart =  new dhtmlXChart({
                view:"pie",
                container:"chart",
                value:"#answer#",
                pieInnerText:"#answer#",
                gradient:true,
                tooltip:{
                    template:"#answer#"
                },
                legend:"#choise#"
            });
            pieChart.parse(data,"json");
        
            }
            </script>
                
            <div id="chart" style="min-width: 400px; height: 400px; margin: 0 auto"></div>
			<?php }else { $path= get_bloginfo('url');  
					echo "<div align='center' style='text-align:center; font-weight:bold; font-size:14px'>Please Go Back & Follow Instructions to View Result</div><br/><br/>";?>
					<img src='<?php echo $path ?>/wp-content/plugins/xTechnos-Online-Poll/screenshot-3.png' width=840px height=500px style="margin-left:20px;" />	
		<?php
		}
	}
	
	//Form
	function xtechnos_online_poll_form($atts){
			if(is_array($atts)){
				$poll = $atts['id'];	
			}else{
				$poll = $atts;
			}

			$custom = get_post_custom($poll);		
			$choice1 = $custom["x_choice1"][0];
			$choice2 = $custom["x_choice2"][0];
			$choice3 = $custom["x_choice3"][0];
			$choice4 = $custom["x_choice4"][0];	
			
					
			$my_post = get_post($poll); 
			$content = $my_post->post_content;
			echo $content; ?>   

            <form method="post" action="">
                <?php if($choice1 != "") { ?>			
                 <input type="radio" name="choice" value="1" /> 
                 <?php echo $choice1; 
                  } ?>
                    
                <?php if($choice2 != "") { ?>			
                 <br /> <input type="radio" name="choice" value="2" />
                  <?php echo $choice2; 
                 } ?>
                        
                <?php if($choice3 != "") { ?>			
                <br /> <input type="radio" name="choice" value="3" />
                  <?php echo $choice3;
                  } ?>
                        
                <?php if($choice4 != "") { ?>			
                 <br /><input type="radio" name="choice" value="4" />
                 <?php echo $choice4; 
                  } ?>    
               <br /> <input type="submit" value="Submit"  />
                    
            </form>  
			<?php
			$custom = get_post_custom($poll);		
			$res_choice1 = $custom["x_choice1_res"][0];
			$res_choice2 = $custom["x_choice2_res"][0];
			$res_choice3 = $custom["x_choice3_res"][0];
			$res_choice4 = $custom["x_choice4_res"][0];	
			
			if($_POST['choice']){		   
					   
				if($_POST['choice']== 1){		   
					$res_choice1 = $res_choice1 + 1;
					update_post_meta($poll, 'x_choice1_res', $res_choice1);
				}
			
				
			   if($_POST['choice']== 2){	 
						$res_choice2 = $res_choice2 + 1;
						update_post_meta($poll, 'x_choice2_res', $res_choice2);		
				   }
				   
				if($_POST['choice']== 3){
						$res_choice3 = $res_choice3 + 1;
						update_post_meta($poll, 'x_choice3_res', $res_choice3);		
				}
				 
				 
			if($_POST['choice']== 4){	  
						$res_choice4 = $res_choice4 + 1;
						update_post_meta($poll, 'x_choice4_res', $res_choice4);		
				 }
					   
			 }//if(POST) ends here
	}	
	
	//Admin Init
	function admin_init() {
		// Custom meta boxes for the edit podcast screen
		add_meta_box("xtechnos-meta", "Number of Options",array(&$this, "meta_options"), "xtechnos_poll");
	}
	
	// Admin post meta contents
	function meta_options(){
	
			global $post;
			$custom = get_post_custom($post->ID);
			$xtechnos_poll_choice1 = $custom["x_choice1"][0];
			$xtechnos_poll_choice2 = $custom["x_choice2"][0];
			$xtechnos_poll_choice3 = $custom["x_choice3"][0];
			$xtechnos_poll_choice4 = $custom["x_choice4"][0];
	
			
			$xtechnos_poll_choice1_res = $custom["x_choice1_res"][0];
			if($xtechnos_poll_choice1_res==""){
				$xtechnos_poll_choice1_res = 1;			
			}
			$xtechnos_poll_choice2_res = $custom["x_choice2_res"][0];
			if($xtechnos_poll_choice2_res==""){
				$xtechnos_poll_choice2_res = 1;			
			}
			
			$xtechnos_poll_choice3_res = $custom["x_choice3_res"][0];
			if($xtechnos_poll_choice3_res==""){
				$xtechnos_poll_choice3_res = 1;			
			}
			
			$xtechnos_poll_choice4_res = $custom["x_choice4_res"][0];
			if($xtechnos_poll_choice4_res==""){
				$xtechnos_poll_choice4_res = 1;			
			}?>
            <label>Choice 1:</label> &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp; &nbsp;
            <input type="text" name="x_choice1" value="<?php echo $xtechnos_poll_choice1; ?>" />
            <input type="hidden" name="x_choice1_res" value="<?php echo $xtechnos_poll_choice1_res; ?>" /><br /><br />
            
            <label>Choice 2:</label> &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp; &nbsp;
            <input type="text" name="x_choice2" value="<?php echo $xtechnos_poll_choice2; ?>" />
            <input type="hidden" name="x_choice2_res" value="<?php echo $xtechnos_poll_choice1_res; ?>" /><br /><br />
            
            <label>Choice 3:</label> &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp; &nbsp;
            <input type="text" name="x_choice3" value="<?php echo $xtechnos_poll_choice3; ?>" />
            <input type="hidden" name="x_choice3_res" value="<?php echo $xtechnos_poll_choice3_res; ?>" /><br /><br />
            
            
            <label>Choice 4:</label> &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp; &nbsp;
            <input type="text" name="x_choice4" value="<?php echo $xtechnos_poll_choice4; ?>" />
            <input type="hidden" name="x_choice4_res" value="<?php echo $xtechnos_poll_choice4_res; ?>" /><br /><br />
	<?php
	  }
  
}
// Initiate the plugin
add_action("init", "xTechnosPollInit");
function xTechnosPollInit() { global $xtn_online_poll; $xtn_online_poll = new xTechnos_Online_Poll(); }
?>