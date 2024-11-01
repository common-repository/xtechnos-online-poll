<?php

function widget_xtechnosop_init() {

	if ( !function_exists('register_sidebar_widget') )
		return;

	function widget_xtechnosop($args) {
		extract($args);

		$title = "Online Poll";
		echo $before_widget . $before_title . $title . $after_title;
		xtechnos_online_poll();
		echo $after_widget;
	}

	register_sidebar_widget(array('xTechnos Online Poll', 'widgets'), 'widget_xtechnosop');

}

add_action('widgets_init', 'widget_xtechnosop_init');

?>