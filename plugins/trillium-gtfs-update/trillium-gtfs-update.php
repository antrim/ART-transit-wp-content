<?php
/**
 * Plugin Name: Trillium Transit GTFS Site updater
 * Plugin URI: 
 * Description: Updates the sites routes, schedule information, and sets up live data updating using Clever Devices info
 * Version: 1.0
 * Author: Paul Clay
 */
 
 

 
 function trillium_gtfs_update_install () {
   global $wpdb;

$charset_collate = $wpdb->get_charset_collate();

$sql = "

DROP TABLE IF EXISTS
    stop_names;

CREATE TABLE stop_names (
stop_id INT(32),
stop_name MEDIUMTEXT,
latitude INT(32),
longitude INT(32),
route_ids MEDIUMTEXT,
RID int(11) NOT NULL auto_increment,
primary KEY (RID)) $charset_collate;";

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
dbDelta( $sql );
}
 

// create custom plugin settings menu
add_action('admin_menu', 'trillium_gtfs_update_create_menu');

function trillium_gtfs_update_create_menu() {

	//create new top-level menu
	add_menu_page('GTFS Site Update', 'GTFS Update', 'administrator', __FILE__, 'trillium_gtfs_update_settings_page', plugins_url('trillium-gtfs-update/images/icon.png',  __FILE__));
	
}




function create_new_nav_menu($menu_name) {
		
	// Check if the menu exists
	$menu_exists = wp_get_nav_menu_object( $menu_name );

	// If it doesn't exist, let's create it.
	$menu_id = -1;
	if( !$menu_exists){
		$menu_id = wp_create_nav_menu($menu_name);
		
	} else {
	
		wp_delete_nav_menu($menu_name);
		$menu_id = wp_create_nav_menu($menu_name);
	}
	return $menu_id;
		
}



function trillium_gtfs_update_settings_page() {
?>
<div class="wrap">
<h2>GTFS Site Update</h2>
<?php
	echo '<h2>GTFS update - uses the trillium GTFS api to sync site to live data</h2>';
	
	echo 'This pulls data from the gtfs api to update the site, only do this, while it should update smoothly, you should always make a backup before doing this. ';
	
	echo "<h3>If you want to update the site, you need to add &update=true to the end of the url.  <br/><strong>DO NOT DO THIS IF YOU ARE UNSURE YOU?RE DOING THE RIGHT THING!!</strong></h3>";
	
	if($_GET["update"] == "true") {
		
		/*echo '<br /><br />updating';
		
		echo getcwd();
		
		echo 'br/>';
		
		include get_template_directory().'/library/php/simple_html_dom.php';
		
		
		$clever_deviced_json = file_get_contents('http://96.10.227.28/art/packet/json/shelter');
		$json = json_decode($clever_deviced_json );
		
		
		foreach($json->ShelterArray as &$shelter) {
			echo '<br /><br /><br />';
			$web_label_html = str_get_html($shelter->Shelter->WebLabel);
			echo $web_label_html->find('.labelShelterArrivalRowOdd')[0];
			//echo $shelter->Shelter->WebLabel;
			//echo trim(preg_replace('/<[^>]*>/', '****',$shelter->Shelter->WebLabel));
		}
		
			//print_r($json);*/
			
			
		
		
		// load
		
		$existing_route_lines = get_posts(array(
			'numberposts' => -1,
			'post_type' => 'route_line',
		));
		
		foreach($existing_route_lines as &$route_line) {
			echo "route lines reset.<br />";
			wp_delete_post( $route_line->ID, true );
			
		}
		
		$existing_route = get_posts(array(
			'numberposts' => -1,
			'post_type' => 'route',
		));
		
		foreach($existing_route as &$route) {
			echo "route reset.<br />";
			wp_delete_post( $route->ID, true );
			
		}
		
	
		
		$handle = fopen(get_site_url()."/wp-content/transit-data/gtfs/anaheim-ca-us/routes.txt", "r");
	
		$route_lines = array();
	
	
		if ($handle) {
			echo 'sdf';
			$lineCount = 0;
			 while (($line = fgets($handle)) !== false) {
			
				if($lineCount > 0) {
			
					//echo $line;
					echo "<br/>.".$line;
			
					$splitLine        =   explode(",", $line);
					$agency_id        = $splitLine[0];
					$route_id        = $splitLine[1];
					$route_short_name    = $splitLine[2];
					$route_long_name       = $splitLine[3];
					$route_desc    			= $splitLine[4];
					$route_type 		= $splitLine[5];
					$route_url		 	= $splitLine[6];
					$route_color 		= $splitLine[7];
					$route_text_color 	= $splitLine[8];
					
					// make or update route_line array
					if(!array_key_exists($route_long_name, $route_lines)) {
						$route_lines[$route_long_name] = array();
					} 
						
					$route_lines[$route_long_name][] = str_replace("\"","",$route_short_name);
		
					
					$my_post = array(
					  'post_title'    => str_replace("\"","",$route_short_name).' : '.$route_long_name,
					  'post_name' => slugify($route_short_name),
					  'post_status'   => 'publish',
					  'post_type'      => 'route',
					  'post_author'   => 1
						);
	
						// Insert the post into the database
						$post_to_update_id = wp_insert_post( $my_post );
						
						 
						update_field('field_5484fb3b6cdeb', $route_id        	, $post_to_update_id); 
						update_field('field_5484fb446cdec', intval(str_replace("\"","",$route_short_name)), $post_to_update_id); 
						update_field('field_5484fb4a6cded',str_replace("\"","", $route_long_name) 	, $post_to_update_id); 
						update_field('field_5484fb516cdee', $route_desc    	, $post_to_update_id); 
						update_field('field_5484fb576cdef', $route_text_color		 , $post_to_update_id); 
						update_field('field_5484fb2c6cdea', $route_color 			, $post_to_update_id); 
				}
				$lineCount ++;
			}
			
			while ($route_line = current($route_lines)) {
			 			
				
				$my_post = array(
					  'post_title'    => str_replace("\"","",key($route_lines)),
					  'post_name' => slugify(key($route_lines)),
					  'post_status'   => 'publish',
					  'post_type'      => 'route_line',
					  'post_author'   => 1
				);
				
				$post_to_update_id = wp_insert_post( $my_post );
				
				$numberString = "";
				$numCount = 0;
				foreach($route_line as $route_number) {
					
					$numberString.= $route_number;
					if($numCount < sizeof($route_line)-1) $numberString.=',';
					
					$numCount ++;
				}
				
				update_field('field_547f8a196be2d', $numberString, $post_to_update_id);
				
				
						
				
				next($route_lines);
			}
			
			};
			
			
		
		
		
		// delete menus
		
		
		
		$attractions_menu_id = create_new_nav_menu('attractions_planner_menu');
		echo 'attractions menu id: '.$attractions_menu_id;
		
		$hotel_menu_id =create_new_nav_menu('hotel_planner_menu');
		echo 'hotel menu id: '.$hotel_menu_id;
		
		$restaurant_menu_id =create_new_nav_menu('restaurant_planner_menu');
		echo 'restaurant menu id: '.$restaurant_menu_id;
		
		
		
		$handle = fopen(get_site_url()."/wp-content/transit-data/gtfs/anaheim-ca-us/landmarks.txt", "r"); 
	
		if ($handle) {
			
			$lineCount = 0;
			 while (($line = fgets($handle)) !== false) {
			
				if($lineCount > 0) {
				
					echo '.<br />';
				
					$splitLine = explode(",", $line);
					$landmark_id = $splitLine[0];
					$landmark_name = $splitLine[1];
					$category_name = $splitLine[2];
					$landmark_url = $splitLine[3];
					$icon_id = $splitLine[4];
					$lat = $splitLine[5];
					$lon = $splitLine[6];
					
					$menu_id = -1;
					
					switch ($category_name) {
						case 'Attractions':
							$menu_id = $attractions_menu_id;
							echo $category_name.': ';
							break;
						case 'Hotels':
							$menu_id = $hotel_menu_id;
							echo $category_name.': ';
							break;
						case 'Restaurants':
							$menu_id = $restaurant_menu_id;
							echo $category_name.': ';
							break;
					}
					echo $landmark_name;
					
				
					wp_update_nav_menu_item($menu_id, 0, array(
						'menu-item-title' =>  __($landmark_name),
						'menu-item-classes' => 'landmark-'.$category_name,
						'menu-item-url' => home_url( '#' ), 
						'menu-item-status' => 'publish'));

				}
				$lineCount ++;
			}
		}
	
	}
?>
</div>
<?php


 } 
 
 
 
 
 ?>