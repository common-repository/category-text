<?php
/*
Plugin Name: Category Text
Plugin URI: http://michele.menciassi.name/en/wordpress-plugins/category-text/
Description: Category Text (or ctext) allows you easily to add a widget for a Category Text-Box.
Author: Michele Menciassi 
Author URI: http://www.isikom.it
Version: 1.2.0

Copyright 2008  Michele Menciassi  (email : michele at miblogo dot com)

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
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110, USA
*/
$ctext = array();
$ctext['tables']['table_name_lists'] = $wpdb->prefix . "ctext_lists";
$ctext['tables']['table_name_elements'] = $wpdb->prefix . "ctext_elements";
$ctext['tables']['table_name_categories'] = $wpdb->prefix . "ctext_categories";
$ctext['prefix'] = 'ctext';
// manage installation
register_activation_hook(__FILE__,'CtextInstall');
// adding admin pages
add_action('admin_menu', 'menage_menu');
// adding widget
add_action('init', 'widget_ctext_register', 1);
// traslation file
//load_plugin_textdomain('ctext', PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)));
load_plugin_textdomain('ctext', str_replace(ABSPATH, '', dirname(__FILE__)), dirname(plugin_basename(__FILE__)));


// INSTALL FUNCTIONS
// ============================================================================================================================
function CtextInstall() { 
  global $wpdb;
	
  $table_name_lists = $wpdb->prefix . "ctext_lists";
  $old_db_version = get_option("ctext_db_version");
  $old_plugin_version = get_option("ctext_version");
  // check and add 'lists' table 
  if ($wpdb->get_var("show tables like '".$table_name_lists."'") != $table_name_lists) { 
	// table do not exist
	$sql  = "CREATE TABLE `".$table_name_lists."` ( ";
	$sql .= "`id_list` int(11) NOT NULL AUTO_INCREMENT, ";
	$sql .= "`name` varchar(250) NOT NULL, ";
	$sql .= "PRIMARY KEY  (`id_list`) ); ";
	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
	dbDelta($sql);
	$welcome_list   = __('Sidebar List', 'ctext');
	$insert = "INSERT INTO " . $table_name_lists ." (id_list, name) " . "VALUES (1, '" . $wpdb->escape($welcome_list) . "')";
	$results = $wpdb->query( $insert );
  } else {
    //if table extist, i can check the DB version and apply modify to the tables    		

  }
  
  // check and add 'elements' table
  $table_name_elements = $wpdb->prefix . "ctext_elements";
  if($wpdb->get_var("show tables like '$table_name_elements'") != $table_name_elements) {
        // table do not exist
   	$sql= "CREATE TABLE `".$table_name_elements."` (
  		`id_element` int(11) NOT NULL auto_increment,
		`title` varchar(250) default NULL,
		`showtitle` TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT '0',
		`text` text default NULL,
		`id_list` int(11) NOT NULL,
		`child` tinyint(3) UNSIGNED NOT NULL default '0',
		`posts` tinyint(3) UNSIGNED NOT NULL default '0',
		`home` tinyint(3) UNSIGNED NOT NULL default '0',
		PRIMARY KEY  (`id_element`),
		KEY `id_list` (`id_list`) );";
	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
	dbDelta($sql);
  }else{
	// table exists
	// check version
	if ($old_db_version === '1.1.1'){
		// versions 1.1.1
		$sql= "ALTER TABLE `".$table_name_elements."` ADD COLUMN `home` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' ";
		$results = $wpdb->query( $sql );
	}else if ($old_db_version !== '1.2.0'){
		// versions before 1.1.1
		$sql= "ALTER TABLE `".$table_name_elements."` ADD COLUMN `home` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' ";
		$results = $wpdb->query( $sql );
		$sql= "ALTER TABLE `".$table_name_elements."` ADD COLUMN `posts` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' ";
		$results = $wpdb->query( $sql );
	}
  }
	
  // check and add 'elements' table
  $table_name_categories = $wpdb->prefix . "ctext_categories";
	if($wpdb->get_var("show tables like '$table_name_categories'") != $table_name_categories) {
   	$sql= "CREATE TABLE `".$table_name_categories."` (
  		`id_element` int(11) NOT NULL,
		`id_category` int(11) NOT NULL,				  
		PRIMARY KEY  (`id_element`, `id_category`) );";
	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
	dbDelta($sql);
  }

  //update version
  if ($old_db_version){
	update_option("ctext_db_version", "1.2.0");
  }else{
	add_option("ctext_db_version", "1.2.0");
  }
  if ($old_plugin_version){
	update_option("ctext_version", "1.2.0");
  }else{
	add_option("ctext_version", "1.2.0");
  }
}

// GENERAL FUNCTIONS
// ============================================================================================================================

function ctext_dropdown_lists($name = "ctext-list", $id = "ctext-list", $selected = '', $emptyoption = false){
	global $wpdb, $ctext;
	
	//extract lists
  	$table_name_lists = $ctext['tables']['table_name_lists'];
	$lists = $wpdb->get_results("SELECT * FROM `$table_name_lists` ORDER BY `name` ASC ");
  
	$dropdown  = "<select name=\"$name\" id=\"$id\">";
	if ($emptyoption === true){
		$dropdown .= '<option value="">'.__('Select a list', 'ctext').'</option>';;
	}
	foreach ($lists as $list_data){
		$dropdown .= '<option value="'.$list_data->id_list.'" ';
		if (!empty($selected) and $selected == $list_data->id_list){
			$dropdown .= '"selected"="selected" ';
		}
		$dropdown .= '>'.$list_data->name.'</option>';
	}	
	$dropdown .= '</select>';
	echo $dropdown;
}

function ctext_get_element($id_element){
	global $wpdb, $ctext;
	
	//extract lists
  $table_name_elements = $ctext['tables']['table_name_elements'];
	return $wpdb->get_results("SELECT * FROM `$table_name_elements` WHERE `id_element` = '$id_element' LIMIT 1 ");
}


function ctext_get_the_category_id($id_element){
	global $wpdb, $ctext;
	
	//extract lists
  $table_name_categories = $ctext['tables']['table_name_categories'];
	$categoryids = $wpdb->get_results("SELECT `id_category` FROM `$table_name_categories` WHERE `id_element` = '$id_element' ");
	$categories = array();
	if (is_array($categoryids) and !empty($categoryids)){
		foreach ($categoryids as $catid){
			array_push($categories, $catid->id_category);
		}
	}
	return $categories;
}

function ctext_get_the_category($id_element){
	global $wpdb, $ctext;
	
	//extract lists
  $table_name_categories = $ctext['tables']['table_name_categories'];
	$categoryids = $wpdb->get_results("SELECT `id_category` FROM `$table_name_categories` WHERE `id_element` = '$id_element' ");
	$categories = array();
	if (is_array($categoryids) and !empty($categoryids)){
		foreach ($categoryids as $catid){
		  $category = get_the_category_by_ID($catid->id_category);
			array_push($categories, $category);
		}
	}
	return $categories;
}

function ctext_get_category_parents( $id ) {
	$chain = array();
	$parent = &get_category( $id );
	if ( is_wp_error( $parent ) )
		return $parent;

	$catid = $parent->cat_ID;

	if ( !empty($parent->parent) && ( $parent->parent != $parent->term_id )  ) {
		$app = ctext_get_category_parents( $parent->parent );
		$chain = array_merge($chain, $app);
	}

	if (!empty($catid)){
	  array_push($chain, $catid);
	}
	return $chain;
}

// MENU ADMIN ITEMS
// ============================================================================================================================
/**********MANAGE LISTS PAGE **********/
function ctextFooter(){
	?>
	<div class="ctext-footer"><strong><?php echo __('Category Text', 'ctext'); ?></strong> - <?php echo __('powered by', 'ctext'); ?> <a href="http://michele.menciassi.name" target="_blank">Michele Menciassi</a>, <a href="http://www.isikom.it" target="_blank">Isikom</a></div>
	<?php
}

function ctextWelcomePage() {
	global $ctext;

	echo "<div class=\"wrap ctext\"><h2>".__('Ctext', 'ctext')."</h2>";

	echo '<p>' . __('Plugin Version', 'ctext') . ': ' . get_option('ctext_version'). '</p>';
	echo '<p>' . __('DB Version', 'ctext') . ': ' . get_option('ctext_db_version'). '</p>';
	
	echo '<p>' . __('If you find this plugin useful, you can think to offer me a beer, making a paypal donation', 'ctext').'</p>';
	?>
	
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_s-xclick">
	<input type="hidden" name="hosted_button_id" value="5830918">
	<input type="image" src="https://www.paypal.com/en_GB/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online.">
	<img alt="" border="0" src="https://www.paypal.com/it_IT/i/scr/pixel.gif" width="1" height="1">
	</form>
	
	<?php ctextFooter(); ?>
	</div>
	<?php
}

function ctextManageLists() {
	global $wpdb, $_POST, $ctext;
	 
	echo "<div class=\"wrap ctext\"><h2>".__('Lists Management', 'ctext')."</h2>";
	if ($_POST['doaction']){
    $table_name_lists = $ctext['tables']['table_name_lists'];
		$action = $_POST['action'];
		$id_list = (int)$_POST['ctext-list'];
		$new_name = (string)$_POST['ctext-new-name'];
		$new_name = strip_tags(trim($new_name));
		$message = '';
		if ($action == 'add'){
			if (empty($new_name)){
				$message = __('name required missed', 'ctext');
			}else{
        $insert = "INSERT INTO `".$table_name_lists ."` (`name`) VALUES ('".$wpdb->escape($new_name)."')";
        $results = $wpdb->query( $insert );
				$message = __('new list successfully added', 'ctext');
			}
		}else if ($action == 'delete'){
			if (empty($id_list)){
				$message = __('list id missed', 'ctext');
			}else{
        $delete = "DELETE FROM `".$table_name_lists ."` WHERE `id_list` = '".$wpdb->escape($id_list)."' ";
        $results = $wpdb->query( $delete );
				$message = __('list successfully deleted', 'ctext');
			}
		}else if ($action == 'rename'){
			if (empty($id_list)){
				$message = __('list id missed', 'ctext');
			}else if (empty($new_name)){
				$message = __('name required missed', 'ctext');
			}else{
        $update = "UPDATE `".$table_name_lists ."` SET `name` = '".$wpdb->escape($new_name)."' WHERE `id_list` = '".$wpdb->escape($id_list)."' ";
        $results = $wpdb->query( $update );
				$message = __('list successfully renamed', 'ctext');
			}
		}else{
			$message = __('invalid action', 'ctext');
		}
		echo "<div class=\"ctext-message\">$message</div>";
	}

	echo '<div class="clearfix">';
	 
	echo '<form action="#" method="post" class="threecols">';
	echo '<fieldset>';
	echo '<legend>'.__('New List', 'ctext').'</legend>';
	echo '<p>';
  echo '<label for="ctext-new-name">'.__('New name', 'ctext').'</label>';
	echo '<input type="text" name="ctext-new-name" id="ctext-new-name" value=""/>';
	echo '</p>';
	echo '<p>';
	echo '<input type="hidden" name="action" value="add"/>';
	echo '<input type="submit" name="doaction" value="'.__('add', 'ctext').'"/>';
	echo '</p>';
	echo '</fieldset>';
	echo '</form>';
	
	echo '<form action="#" method="post" class="threecols">';
	echo '<fieldset>';
	echo '<legend>'.__('Delete List', 'ctext').'</legend>';
	echo '<p>';
  echo '<label for="ctext-list">'.__('Lists', 'ctext').'</label>';
  ctext_dropdown_lists('ctext-list','ctext-list');
	echo '</p>';
	echo '<p>';
	echo '<input type="hidden" name="action" value="delete"/>';
	echo '<input type="submit" name="doaction" value="'.__('delete', 'ctext').'"/>';
	echo '</p>';
	echo '</fieldset>';
	echo '</form>';
	
	echo '<form action="#" method="post" class="threecols">';
	echo '<fieldset>';
	echo '<legend>'.__('Rename List', 'ctext').'</legend>';
	echo '<p>';
  echo '<label for="ctext-list">'.__('Lists', 'ctext').'</label>';
  ctext_dropdown_lists('ctext-list','ctext-list');
	echo '</p>';
	echo '<p>';
  echo '<label for="ctext-new-name">'.__('New name', 'ctext').'</label>';
	echo '<input type="text" name="ctext-new-name" id="ctext-new-name" value=""/>';
	echo '</p>';
	echo '<p>';
	echo '<input type="hidden" name="action" value="rename"/>';
	echo '<input type="submit" name="doaction" value="'.__('rename', 'ctext').'"/>';
	echo '</p>';
	echo '</fieldset>';
	echo '</form>';
	
	echo '</div>';
	
	ctextFooter();
	echo '</div>';
}

function ctextManageElements() {
  global $_REQUEST, $wpdb, $ctext;
	
	echo "<div class=\"wrap ctext\"><h2>".__('Elements Management', 'ctext')."</h2>";
	
	$ctext_list = $_REQUEST['ctext-list'];
	$ctext_category = $_REQUEST['ctext-category'];
	$ctext_submit = $_REQUEST['ctext-submit'];
	$ctext_text = $_REQUEST['ctext-text'];
	$action = $_REQUEST['action'];
	$element = $_REQUEST['element'];
	
	$table_name_elements = $ctext['tables']['table_name_elements'];
	$table_name_categories = $ctext['tables']['table_name_categories'];
	?>
	<form action="admin.php?page=manage-elements" method="post">
	<?php
  ctext_dropdown_lists('ctext-list', 'ctext-list', $ctext_list, true);
	?>
	<input type="submit" name="ctext-submit" value="<?php echo __('show elements', 'ctext'); ?>"/>
	</form>
	<?php

	if ($action === 'delete' and !empty($element) ){
		$delete = "DELETE FROM `".$table_name_elements ."` WHERE `id_element` = '".$wpdb->escape($element)."' ";
		$results = $wpdb->query( $delete );
		$delete = "DELETE FROM `".$table_name_categories ."` WHERE `id_element` = '".$wpdb->escape($element)."' ";
		$results = $wpdb->query( $delete );
		
		$message = __('element successfully delated', 'ctext');
		echo "<div class=\"ctext-message\">$message</div>";
	}
	
	if (!empty($ctext_submit)){
	  if ($action === 'fastedit' and !empty($ctext_text) and !empty($element) ){
      $update = "UPDATE `".$table_name_elements ."` SET `text` = '".$wpdb->escape($ctext_text)."' WHERE `id_element` = '".$wpdb->escape($element)."' ";
      $results = $wpdb->query( $update );
			$message = __('element successfully updated', 'ctext');
			echo "<div class=\"ctext-message\">$message</div>";
		}

		if (!empty($ctext_list)){
			//extract elements
			$SQL = "SELECT * FROM `$table_name_elements` WHERE `id_list` = '$ctext_list' ORDER BY `id_element` ASC ";
			$elements = $wpdb->get_results($SQL);			

			if (is_array($elements) and !empty($elements)){				
				?>
				<table class="widefat" cellspacing="0">
				<thead>
				<tr>
				<th style="width:35px;text-align:center;">ID</th>
				<th style="width:160px;"><?php echo __('Title', 'ctext'); ?></th>
				<th style="width:145px;"><?php echo __('Categories', 'ctext'); ?></th>
				<th><?php echo __('Text', 'ctext'); ?></th>
				<th><?php echo __('Preview', 'ctext'); ?></th>
				</tr>
				</thead>
				<?php
				$odd = true;
				foreach($elements as $element){
					if ($odd === true){
						echo '<tr class="alternate">';
						$odd = false;
					}else{
						echo '<tr>';
						$odd = true;
					}
				?>
				<td style="text-align:center;"><strong><?php echo $element->id_element; ?></strong></td>
				<td class="post-title column-title"><strong><a class="row-title" href="admin.php?page=add-element&action=edit&element=<?php echo $element->id_element; ?>&ref-list=<?php echo $ctext_list; ?>&ref-category=<?php echo $ctext_category; ?>" title="<?php echo __('Modify the element', 'ctext'); ?> &quot;<?php echo $element->title; ?>&quot;"><?php echo $element->title; ?></a></strong>
				<div><strong>- <?php if($element->showtitle == 1){ echo __('title showed', 'ctext'); }else{ echo __('title hidden', 'ctext');}?></strong></div>
				<div><strong>- <?php if ($element->posts){ echo __('showed in posts', 'ctext'); }else{ echo __('not showed in posts', 'ctext'); }?></strong></div>
				<div><strong>- <?php if ($element->home){ echo __('showed in home', 'ctext'); }else{ echo __('not showed in home', 'ctext'); }?></strong></div>
		<div class="row-actions">
		<span class='edit'><a href="admin.php?page=add-element&action=edit&element=<?php echo $element->id_element; ?>&ref-list=<?php echo $ctext_list; ?>&ref-category=<?php echo $ctext_category; ?>" title="<?php echo __('Edit this element', 'ctext'); ?>"><?php echo __('Edit', 'ctext'); ?></a> | </span>
		<span class='delete'><a class='submitdelete' title='<?php echo __('Delete this element', 'ctext'); ?>' href='admin.php?page=manage-elements&ctext-list=<?php echo $ctext_list; ?>&action=delete&element=<?php echo $element->id_element; ?>&ctext-submit=on' onclick="if ( confirm('<?php echo __('Do you really want delete this element?', 'ctext'); ?>') ) { return true;}return false;"><?php echo __('Delete', 'ctext'); ?></a></span>
		</div>
			</td>
				
			  <td>
				<?php
				foreach (ctext_get_the_category($element->id_element) as $categoria){
					echo "$categoria<br/>";
				}
				?>
				<p>
				<?php echo __('Children categories:', 'ctext'); ?> <strong><?php if ($element->child){ echo __('yes', 'ctext'); }else{ echo __('no', 'ctext'); }?></strong>
				</p>
				</td>
			 
				<td>
				<form action="admin.php?page=manage-elements" method="post">
				<textarea name="ctext-text" style="width:300px;height:140px;"><?php echo stripslashes(trim($element->text)); ?></textarea><br/>
				<input type="hidden" name="ctext-category" value="<?php echo $ctext_category; ?>"/>
				<input type="hidden" name="ctext-list" value="<?php echo $ctext_list; ?>"/>
				<input type="hidden" name="element" value="<?php echo $element->id_element; ?>"/>
				<input type="hidden" name="action" value="fastedit"/>
				<input type="submit" name="ctext-submit" value="<?php echo __('fast edit', 'ctext'); ?>"/>
				</form>
				</td>
				
				<td>
				<div class="iframe">
				<?php echo stripslashes(trim($element->text)); ?>
				</div>
				</td>
				
				</tr>
				<?php
				}
				?>
				</table>
				<?php
			}else{
				$message = __('No elements in this list', 'ctext');
				echo "<div class=\"ctext-message\">$message</div>";
			}
		}else{
			$message = __('You must select a list from menu', 'ctext');
		  echo "<div class=\"ctext-message\">$message</div>";
		}
	}
  ctextFooter(); ?>
	</div>
	<?php
}

function ctextAddElement() {
	global $wpdb, $_POST, $_REQUEST, $ctext;
	$id_element = $_REQUEST['element'];
	$action  = $_REQUEST['action'];
	$ref_category = $_REQUEST['ref-category'];
	$ref_list = $_REQUEST['ref-list'];
	if ( $action === 'edit' and !empty($id_element)){
		
	  echo "<div class=\"wrap ctext\"><h2>".__('Edit Element', 'ctext')."</h2>";
	  $categories  = ctext_get_the_category_id($id_element);
	  $element     = ctext_get_element($id_element);
	  $ctext_text  = stripslashes($element[0]->text);
	  $ctext_title = stripslashes($element[0]->title);
	  $ctext_showtitle = $element[0]->showtitle;
	  $ctext_list  = $element[0]->id_list;
	  $ctext_child = $element[0]->child;
	  $ctext_posts = $element[0]->posts;
	  $ctext_home = $element[0]->home;
	}else{
	  echo "<div class=\"wrap ctext\"><h2>".__('Add Element', 'ctext')."</h2>";
	  $categories  = array();
	  $ctext_text  = '';
	  $ctext_title = '';
	  $ctext_showtitle = '';
	  $ctext_list  = '';
	  $ctext_child = '';
	  $ctext_posts = '';
	  $ctext_home = '';
	}	
	if ($_POST['doaction']){
		$categories  = $_POST['post_category'];
		$ctext_text  = trim($_POST['ctext-text']);
		$ctext_title = $_POST['ctext-title'];
		$ctext_title = strip_tags(trim($ctext_title));
		$ctext_showtitle =  $_POST['ctext-showtitle'];
		$ctext_list  = (int)$_POST['ctext-list'];
		$ctext_child = $_POST['ctext-child'];
		$ctext_posts = $_POST['ctext-posts'];
		$ctext_home = $_POST['ctext-home'];
		if (!empty($ctext_title) and
			  !empty($ctext_text) and
			  !empty($ctext_list) and
				(is_array($categories) and !empty($categories)) ){

			$table_name_elements = $ctext['tables']['table_name_elements'];
			$table_name_categories = $ctext['tables']['table_name_categories'];
			if ( $action === 'add'){
				$insert  = "INSERT INTO " . $table_name_elements ." ( `title`, `showtitle`, `text`, `id_list`, `child`, `posts`, `home`) "; 
				$insert .= "VALUES ('".$wpdb->escape($ctext_title)."', '".$wpdb->escape($ctext_showtitle)."', '".$wpdb->escape($ctext_text)."', '".$wpdb->escape($ctext_list)."', '".$wpdb->escape($ctext_child)."', '".$wpdb->escape($ctext_posts)."', '".$wpdb->escape($ctext_home)."')";
				$results = $wpdb->query( $insert );
				$last = $wpdb->get_results("SELECT LAST_INSERT_ID( ) AS `id` ");
				$id_element = $last[0]->id;
				foreach ($categories as $id_category){
					$insert  = "INSERT INTO " . $table_name_categories ." ( `id_element`, `id_category`) "; 
					$insert .= "VALUES ('".$wpdb->escape($id_element)."', '".$wpdb->escape($id_category)."' )";
					$results = $wpdb->query( $insert );
				}
				$message = __('Successfully added', 'ctext').' - <a href="admin.php?page=add-element&action=edit&element='.$id_element.'">'.$ctext_title.'</a>';
			}

			if ( $action === 'edit' and !empty($id_element) ){
				$update  = "UPDATE " . $table_name_elements ." SET ";
				$update .= "`title` = '".$wpdb->escape($ctext_title)."', ";
				$update .= "`showtitle` = '".$wpdb->escape($ctext_showtitle)."', ";
				$update .= "`text` = '".$wpdb->escape($ctext_text)."', ";
				$update .= "`id_list`= '".$wpdb->escape($ctext_list)."', ";
				$update .= "`child` = '".$wpdb->escape($ctext_child)."', "; 
				$update .= "`posts` = '".$wpdb->escape($ctext_posts)."', "; 
				$update .= "`home` = '".$wpdb->escape($ctext_home)."' "; 
				$update .= "WHERE `id_element` = '$id_element' ";
				$results = $wpdb->query( $update );
				$SQL  = "DELETE FROM `$table_name_categories` WHERE `$table_name_categories`.`id_element` = '$id_element' ";
				$results = $wpdb->query( $SQL );
				foreach ($categories as $id_category){
					$insert  = "INSERT INTO " . $table_name_categories ." ( `id_element`, `id_category`) "; 
					$insert .= "VALUES ('".$wpdb->escape($id_element)."', '".$wpdb->escape($id_category)."' )";
					$results = $wpdb->query( $insert );
				}
				$message = __('Successfully updated', 'ctext');
				if ($ref_list or $ref_category){
				  $message .= ' - <a href="admin.php?page=manage-elements&ctext-submit=on&ctext-list='.$ref_list.'&ctext-category='.$ref_category.'">'.$ctext_title.'</a>';
				}
				$action  = 'add';
			}
			
			$categories  = array();
			$ctext_text  = '';
			$ctext_title = '';
			$ctext_showtitle = '';
			$ctext_list  = '';
			$ctext_child = '';
			$ctext_posts = '';
			$ctext_home = '';
		}else{
			$message  = __('Fields missed', 'ctext').':<br/>';
			if (empty($ctext_title)){
			  $message .= '-'.__('Title', 'ctext').'<br/>';
			}else{
				$ctext_title = stripslashes($ctext_title);
			}
			if (empty($ctext_text)){
			  $message .= '-'.__('Text', 'ctext').'<br/>';
			}else{
				$ctext_text = stripslashes($ctext_text);
			}
			if (empty($ctext_list))
			  $message .= '-'.__('List', 'ctext').'<br/>';
			if (empty($categories))
			  $message .= '-'.__('Categories', 'ctext').'<br/>';
		}
		echo "<div class=\"ctext-message\">$message</div>";
	}	
	?>

	<form action="#" method="post">
	<div class="clearfix">
	
	<div style="float:left;width:68%;">
	<p>
	<label for="ctext-title"><?php echo __('Title', 'ctext') ?>:</label>
	<input type="text" name="ctext-title" id="ctext-title" value="<?php echo $ctext_title; ?>" style="width:600px;"/>
	</p>
	<p>
	<label for="ctext-showtitle" style="display:inline;"><?php echo __('Show title', 'ctext') ?>:</label>
	<input type="checkbox" name="ctext-showtitle" id="ctext-showtitle" value="1" <?php if($ctext_showtitle){ echo '"checked"="checked"'; }?>/>
	</p>
	<p>
	<label for="ctext-text"><?php echo __('Text', 'ctext') ?>:</label>
	<textarea name="ctext-text" id="ctext-text" style="width:600px;height:100px;"><?php echo $ctext_text; ?></textarea>
	</p>

	<p>
	<?php
	if ($action === 'edit' and !empty($id_element)){
		echo '<input type="hidden" name="action" value="edit"/>';
		echo '<input type="hidden" name="element" value="'.$id_element.'"/>';
		echo '<input type="hidden" name="ref-category" value="'.$ref_category.'"/>';
		echo '<input type="hidden" name="ref-list" value="'.$ref_list.'"/>';
	}else{
		echo '<input type="hidden" name="action" value="add"/>';
	}	
	?>
	<input type="submit" name="doaction" value="<?php echo __('Save', 'ctext') ?>"/> 
	</p>

	</div>
	
	<div style="float:left;width:30%;">
	
	<p>
	<label for="ctext-list"><?php echo __('List', 'ctext') ?>:</label>
	<?php
	ctext_dropdown_lists('ctext-list', 'ctext-list', $ctext_list);
	?>
	</p>

  <div id="categorydiv">
	<label for="post_category"><?php echo __('Categories', 'ctext') ?>:</label>
	<ul id="categorychecklist" class="list:category categorychecklist form-no-clear">
	  <?php wp_category_checklist(0, false, $categories, false) ?>
	</ul>
	</div>
	
	<p>
	<label for="ctext-child"><?php echo __('Extend to child category', 'ctext') ?>:</label>
	<input type="checkbox" name="ctext-child" id="ctext-child" value="1" <?php if($ctext_child){ echo '"checked"="checked"'; }?>/> <?php echo __('children allowed', 'ctext') ?>
  </p>

	<p>
	<label for="ctext-posts"><?php echo __('Extend to posts of category', 'ctext') ?>:</label>
	<input type="checkbox" name="ctext-posts" id="ctext-posts" value="1" <?php if($ctext_posts){ echo '"checked"="checked"'; }?>/> <?php echo __('posts allowed', 'ctext') ?>
  </p>

	<p>
	<label for="ctext-home"><?php echo __('Show in home', 'ctext') ?>:</label>
	<input type="checkbox" name="ctext-home" id="ctext-home" value="1" <?php if($ctext_home){ echo '"checked"="checked"'; }?>/> <?php echo __('Extend to home page', 'ctext') ?>
  	</p>

	</div>
	</div>
	</form>
	
  <?php

  ctextFooter(); 
	echo "</div>";
}

function admin_header() {
	?>
	<style type="text/css">
  /* <![CDATA[ */
	.ctext .twocols {float:left;display:block;width:48%;margin-right:4px;}
	.ctext .threecols {float:left;display:block;width:32%;margin-right:4px;}
	.ctext fieldset{ border:1px solid #464646;padding:0px 6px; }
	.ctext label {display:block;font-weight:bold;}
	.ctext select {width:200px;}
	.ctext form {margin-bottom:10px;}
  .ctext .clearfix:after { content: ".";display: block;height: 0;clear: both;visibility: hidden;}
  .ctext .clearfix {display: inline-block;}
	.ctext ul, .ctext ol {list-style-type:none}
  /* Nasconde da IE-mac \*/
  * html .ctext  .clearfix {height: 1%;}
  .ctext .clearfix {display: block;}
  /* Fine dell'hack per IE-mac */
  div.ctext-message	{ margin:10px;padding:6px;font-weight:bold; border:1px dotted #464646;background-color:#FFFEEB; }
	.iframe {width:300px;height:180px;overflow: auto;border: 1px solid #464646;padding: 2px;}
	.ctext-footer {background-image:url('/<?php echo str_replace(ABSPATH, '', dirname(__FILE__));?>/logo_isikom_small.jpg');background-repeat:no-repeat;background-position:right top;padding-right:40px;text-align:right;font-size:0.8em;border-top:1px dotted #464646;padding-top:4px;margin-top:60px;height:40px; font-style:italic;}
	/* ]]> */
	</style>
	<?php
}


function menage_menu() {
  add_action('admin_head', 'admin_header');

	add_menu_page(__('Ctext', 'ctext'), __('Ctext', 'ctext'), 8, __FILE__, 'ctextWelcomePage');
	add_submenu_page(__FILE__, __('Manage Lists', 'ctext'), __('Manage Lists', 'ctext'), 8, 'manage-lists', 'ctextManageLists');
	add_submenu_page(__FILE__, __('Manage Elements', 'ctext'), __('Manage Elements', 'ctext'), 8, 'manage-elements', 'ctextManageElements');
  	add_submenu_page(__FILE__, __('Add Element', 'ctext'), __('Add Element', 'ctext'), 8, 'add-element', 'ctextAddElement');
}


// WIDGET FUNCTIONS
// ============================================================================================================================
function widget_ctext($args) {
	global $wp_query, $wpdb, $ctext;
	extract($args);
	// se la pagina è una categoria, esegui i vari controlli
	// se non siamo in una categoria non visualizzo il widget
	if (is_category()){
		// recupero l'ID del widget per recuperarne le opzioni
		
		// number indica l'ID numerico assegnato al widget e ci serve per recuperarne le opzioni dall'array delle opzioni.
		$number = 0;
		$prefix = $ctext['prefix'];
		//$widget_id = $args['widget_id'];
		if (!empty($widget_id)){
			$number = intval(substr($widget_id, strlen($prefix)+1));
		}
		$options = get_option('widget_ctext');
		
		// se l'ID recuperato non è presente nell'array opzioni termino la funzione
		if ( !isset($options[$number]) )
			return;
		
		// recuperiamo il title (se impostato) per la sua visualizzazione
		$title = apply_filters('widget_title', $options[$number]['title']);
		$list = $options[$number]['list'];
		
		if (!empty($list)){				
			$cat_obj = $wp_query->get_queried_object();
			$categoria = $cat_obj->term_id;
			$parent_chain = ctext_get_category_parents($categoria);
			$table_name_elements = $ctext['tables']['table_name_elements'];
			$table_name_categories = $ctext['tables']['table_name_categories'];
			if (count($parent_chain) == 1){
				//siamo nella categoria madre
				$SQL = "SELECT `title`, `showtitle`, `text` FROM `$table_name_elements` LEFT JOIN `$table_name_categories` ON `$table_name_elements`.`id_element` = `$table_name_categories`.`id_element` WHERE `id_category` = '". $wpdb->escape($categoria) ."' AND `$table_name_elements`.`id_list` = '". $wpdb->escape($list) ."'";					
			}else{
				//siamo in una categoria figlia
				$parent_list = implode(",",$parent_chain);
				$SQL  = "SELECT `title`, `showtitle`, `text` FROM `$table_name_elements` LEFT JOIN `$table_name_categories` ON `$table_name_elements`.`id_element` = `$table_name_categories`.`id_element` ";
				$SQL .= "WHERE `$table_name_elements`.`id_list` = '". $wpdb->escape($list) ."' AND ";
				$SQL .= "( ";
				$SQL .= "`id_category` = '". $wpdb->escape($categoria) ."' ";
				$SQL .= "OR ";
				$SQL .= "( `id_category` IN ($parent_list) AND `$table_name_elements`.`child` = 1  ) ";
				$SQL .= ") ";
			}
			$elements = $wpdb->get_results($SQL);
			if (!empty($elements)){
				// titolo del widget
				if ( !empty( $title) ){
					echo $before_widget;
					echo $before_title . $title . $after_title;
					echo '<div class="textwidget">';
					echo '</div>';
					echo $after_widget;
				}
				foreach($elements as $element){
					echo $before_widget;
					if ( !empty( $element->title ) and $element->showtitle == 1) { echo $before_title . $element->title . $after_title; }
					echo '<div class="textwidget">';
					echo stripslashes($element->text);
					echo '</div>';
					echo $after_widget;
				}
			}
		}
	}else if(is_single()){
		// number indica l'ID numerico assegnato al widget e ci serve per recuperarne le opzioni dall'array delle opzioni.
		$number = 0;
		$prefix = $ctext['prefix'];
		//$widget_id = $args['widget_id'];
		if (!empty($widget_id)){
			$number = intval(substr($widget_id, strlen($prefix)+1));
		}
		$options = get_option('widget_ctext');
		
		// se l'ID recuperato non è presente nell'array opzioni termino la funzione
		if ( !isset($options[$number]) )
			return;
		
		// recuperiamo il title (se impostato) per la sua visualizzazione
		$title = apply_filters('widget_title', $options[$number]['title']);
		$list = $options[$number]['list'];
		if (!empty($list)){
			$padri = array();
			$figli = array();
			$table_name_elements = $ctext['tables']['table_name_elements'];
			$table_name_categories = $ctext['tables']['table_name_categories'];
			foreach(get_the_category() as $cat) {

				$categoria = $cat->term_id; 
				array_push($padri, $categoria);
				$parent_chain = ctext_get_category_parents($categoria);
				if (count($parent_chain) > 1){
					array_pop($parent_chain);
					$figli = array_merge($figli, $parent_chain);
				}				
			}
			if (!empty($padri) or !empty($figli)){
				$lista_padri = implode(",",$padri);
				$lista_figli = implode(",",$figli);
				$SQL  = "SELECT `title`, `text`, `showtitle` FROM `$table_name_elements` ";
				$SQL .= "LEFT JOIN `$table_name_categories` ";
				$SQL .= "ON `$table_name_elements`.`id_element` = `$table_name_categories`.`id_element` ";
				$SQL .= "WHERE `$table_name_elements`.`id_list` = '". $wpdb->escape($list) ."' AND ";
				$SQL .= " `$table_name_elements`.`posts` = 1 AND ( ";
				if ($lista_padri){
					$SQL .= "( `id_category` IN ($lista_padri) )";
					if($lista_figli){
						$SQL .= "OR ( `id_category` IN ($lista_figli) AND `$table_name_elements`.`child` = 1  ) ";
					}
				}else if($lista_figli){
					$SQL .= "( `id_category` IN ($lista_figli) AND `$table_name_elements`.`child` = 1  ) ";
				}
				$SQL .= ") ";
			}
			$elements = $wpdb->get_results($SQL);
			if (!empty($elements)){
				// titolo del widget
				if ( !empty( $title) ){
					echo $before_widget;
					echo $before_title . $title . $after_title;
					echo '<div class="textwidget">';
					echo '</div>';
					echo $after_widget;
				}
				foreach($elements as $element){
					echo $before_widget;
					if ( !empty( $element->title ) and $element->showtitle == 1) { echo $before_title . $element->title . $after_title; }
					echo '<div class="textwidget">';
					echo stripslashes($element->text);
					echo '</div>';
					echo $after_widget;
				}
			}
		}
	}else if(is_home()){
		// number indica l'ID numerico assegnato al widget e ci serve per recuperarne le opzioni dall'array delle opzioni.
		$number = 0;
		$prefix = $ctext['prefix'];
		//$widget_id = $args['widget_id'];
		if (!empty($widget_id)){
			$number = intval(substr($widget_id, strlen($prefix)+1));
		}
		$options = get_option('widget_ctext');
		
		// se l'ID recuperato non è presente nell'array opzioni termino la funzione
		if ( !isset($options[$number]) )
			return;
		
		// recuperiamo il title (se impostato) per la sua visualizzazione
		$title = apply_filters('widget_title', $options[$number]['title']);
		$list = $options[$number]['list'];
		if (!empty($list)){
			$table_name_elements = $ctext['tables']['table_name_elements'];
			$SQL  = "SELECT `title`, `text`, `showtitle` FROM `$table_name_elements` ";
			$SQL .= "WHERE `$table_name_elements`.`id_list` = '". $wpdb->escape($list) ."' AND ";
			$SQL .= " `$table_name_elements`.`home` = 1 ";
			$elements = $wpdb->get_results($SQL);
			if (!empty($elements)){
				// titolo del widget
				if ( !empty( $title) ){
					echo $before_widget;
					echo $before_title . $title . $after_title;
					echo '<div class="textwidget">';
					echo '</div>';
					echo $after_widget;
				}
				foreach($elements as $element){
					echo $before_widget;
					if ( !empty( $element->title ) and $element->showtitle == 1) { echo $before_title . $element->title . $after_title; }
					echo '<div class="textwidget">';
					echo stripslashes($element->text);
					echo '</div>';
					echo $after_widget;
				}
			}
		}
	}
}

function get_ctext_elements($list, $title = false){
	global $wp_query, $wpdb, $ctext;	
	if (is_category()){
		// recupero l'ID del widget per recuperarne le opzioni
		if (!empty($list)){
			$cat_obj = $wp_query->get_queried_object();
			$categoria = $cat_obj->term_id;
			$parent_chain = ctext_get_category_parents($categoria);
			$table_name_elements = $ctext['tables']['table_name_elements'];
			$table_name_categories = $ctext['tables']['table_name_categories'];
			if (count($parent_chain) == 1){
				//siamo nella categoria madre
				$SQL = "SELECT `title`, `text`, `showtitle` FROM `$table_name_elements` LEFT JOIN `$table_name_categories` ON `$table_name_elements`.`id_element` = `$table_name_categories`.`id_element` WHERE `id_category` = '". $wpdb->escape($categoria) ."' AND `$table_name_elements`.`id_list` = '". $wpdb->escape($list) ."'";					
			}else{
				//siamo in una categoria figlia
				$parent_list = implode(",",$parent_chain);
				$SQL  = "SELECT `title`, `text` FROM `$table_name_elements` LEFT JOIN `$table_name_categories` ON `$table_name_elements`.`id_element` = `$table_name_categories`.`id_element` ";
				$SQL .= "WHERE `$table_name_elements`.`id_list` = '". $wpdb->escape($list) ."' AND ";
				$SQL .= "( ";
				$SQL .= "`id_category` = '". $wpdb->escape($categoria) ."' ";
				$SQL .= "OR ";
				$SQL .= "( `id_category` IN ($parent_list) AND `$table_name_elements`.`child` = 1  ) ";
				$SQL .= ") ";
			}
			$elements = $wpdb->get_results($SQL);
			if (!empty($elements)){
				foreach($elements as $element){
					echo '<div class="ctext-element">';
					if ( !empty( $element->title ) and $title === true ) { echo '<h2>' . $element->title . '</h2>'; }
					echo '<div class="ctext-container">';
					echo stripslashes($element->text);
					echo '</div>';
					echo '</div>';
				}
			}
		}
	}else if(is_single()){
		if (!empty($list)){
			$padri = array();
			$figli = array();
			$table_name_elements = $ctext['tables']['table_name_elements'];
			$table_name_categories = $ctext['tables']['table_name_categories'];
			foreach(get_the_category() as $cat) {
				$categoria = $cat->term_id;
				array_push($padri, $categoria);
				$parent_chain = ctext_get_category_parents($categoria);
				if (count($parent_chain) > 1){
					array_pop($parent_chain);
					$figli = array_merge($figli, $parent_chain);
				}				
			}
			if (!empty($padri) or !empty($figli)){
				$lista_padri = implode(",",$padri);
				$lista_figli = implode(",",$figli);
				$SQL  = "SELECT `title`, `text`, `showtitle` FROM `$table_name_elements` ";
				$SQL .= "LEFT JOIN `$table_name_categories` ";
				$SQL .= "ON `$table_name_elements`.`id_element` = `$table_name_categories`.`id_element` ";
				$SQL .= "WHERE `$table_name_elements`.`id_list` = '". $wpdb->escape($list) ."' AND ";
				$SQL .= " `$table_name_elements`.`posts` = 1 AND ( ";
				if ($lista_padri){
					$SQL .= "( `id_category` IN ($lista_padri) )";
					if($lista_figli){
						$SQL .= "OR ( `id_category` IN ($lista_figli) AND `$table_name_elements`.`child` = 1  ) ";
					}
				}else if($lista_figli){
					$SQL .= "( `id_category` IN ($lista_figli) AND `$table_name_elements`.`child` = 1  ) ";
				}
				$SQL .= ") ";
			}
			$elements = $wpdb->get_results($SQL);
			if (!empty($elements)){
				foreach($elements as $element){
					echo '<div class="ctext-element">';
					if ( !empty( $element->title ) and $title === true ) { echo '<h2>' . $element->title . '</h2>'; }
					echo '<div class="ctext-container">';
					echo stripslashes($element->text);
					echo '</div>';
					echo '</div>';
				}
			}
		}
	}else if(is_home()){
		if (!empty($list)){
			$table_name_elements = $ctext['tables']['table_name_elements'];
			$SQL  = "SELECT `title`, `text`, `showtitle` FROM `$table_name_elements` ";
			$SQL .= "WHERE `$table_name_elements`.`id_list` = '". $wpdb->escape($list) ."' AND ";
			$SQL .= " `$table_name_elements`.`home` = 1 ";
			$elements = $wpdb->get_results($SQL);
			if (!empty($elements)){
				foreach($elements as $element){
					echo '<div class="ctext-element">';
					if ( !empty( $element->title ) and $title === true ) { echo '<h2>' . $element->title . '</h2>'; }
					echo '<div class="ctext-container">';
					echo stripslashes($element->text);
					echo '</div>';
					echo '</div>';
				}
			}
		}
	}
}

function widget_ctext_control($args) {
  global $ctext;
	$prefix = $ctext['prefix'];
 
	$options = get_option('widget_ctext');
	if(empty($options)) $options = array();
	if(isset($options[0])) unset($options[0]);

// update options array
	if(!empty($_POST[$prefix]) && is_array($_POST)){
		foreach($_POST[$prefix] as $widget_number => $values){
			if(empty($values) && isset($options[$widget_number])) // user clicked cancel
				continue;
 
			if(!isset($options[$widget_number]) && $args['number'] == -1){
				$args['number'] = $widget_number;
				$options['last_number'] = $widget_number;
			}
			$options[$widget_number] = $values;
		}
 
		// update number
		if($args['number'] == -1 && !empty($options['last_number'])){
			$args['number'] = $options['last_number'];
		}
 
		// clear unused options and update options in DB. return actual options array
		$options = widget_ctext_update($prefix, $options, $_POST[$prefix], $_POST['sidebar'], 'widget_ctext');
	}
 
	// $number - is dynamic number for multi widget, gived by WP
	// by default $number = -1 (if no widgets activated). In this case we should use %i% for inputs
	//   to allow WP generate number automatically
	$number = ($args['number'] == -1)? '%i%' : $args['number'];
 
	// now we can output control
	$opts = @$options[$number];
 
	$title = @$opts['title'];
 	$list = @$opts['list'];

	?>
    Title<br />
		<input type="text" name="<?php echo $prefix; ?>[<?php echo $number; ?>][title]" value="<?php echo $title; ?>" />
		<p>
			<?php
			$id   = "ctext-list-$number";
			$name = 'ctext['.$number.'][list]';
			ctext_dropdown_lists($name, $id, $list);
			?>
	  </p>
<?php
}

function widget_ctext_update ($id_prefix, $options, $post, $sidebar, $option_name = ''){
	global $wp_registered_widgets;
	static $updated = false;

	// get active sidebar
	$sidebars_widgets = wp_get_sidebars_widgets();
	if ( isset($sidebars_widgets[$sidebar]) )
		$this_sidebar =& $sidebars_widgets[$sidebar];
	else
		$this_sidebar = array();

	// search unused options
	foreach ( $this_sidebar as $_widget_id ) {
		if(preg_match('/'.$id_prefix.'-([0-9]+)/i', $_widget_id, $match)){
			$widget_number = $match[1];

			// $_POST['widget-id'] contain current widgets set for current sidebar
			// $this_sidebar is not updated yet, so we can determine which was deleted
			if(!in_array($match[0], $_POST['widget-id'])){
				unset($options[$widget_number]);
			}
		}
	}

	// update database
	if(!empty($option_name)){
		update_option($option_name, $options);
		$updated = true;
	}

	// return updated array
	return $options;
}

function widget_ctext_register() {
  global $ctext;
	
	$prefix = $ctext['prefix'];
	$name = __('Category Text', 'ctext');
	$widget_ops = array('classname' => 'widget_ctext', 'description' => __('Arbitrary text or HTML for categories', 'ctext'));
	$control_ops = array('width' => 400, 'height' => 350, 'id_base' => $prefix);
	
	$options = get_option('widget_ctext');
  if(isset($options[0])) unset($options[0]);
	
	if(!empty($options)){
		foreach(array_keys($options) as $widget_number){
			wp_register_sidebar_widget($prefix.'-'.$widget_number, $name, 'widget_ctext', $widget_ops, array( 'number' => $widget_number ));
			wp_register_widget_control($prefix.'-'.$widget_number, $name, 'widget_ctext_control', $control_ops, array( 'number' => $widget_number ));
		}
	} else{
		$options = array();
		$widget_number = 1;
		wp_register_sidebar_widget($prefix.'-'.$widget_number, $name, 'widget_ctext', $widget_ops, array( 'number' => $widget_number ));
		wp_register_widget_control($prefix.'-'.$widget_number, $name, 'widget_ctext_control', $control_ops, array( 'number' => $widget_number ));
	}

}


?>
