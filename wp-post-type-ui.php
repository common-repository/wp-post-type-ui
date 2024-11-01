<?php
/*
Plugin Name: WP Post Type UI
Plugin URI: http://urlund.com/plugins/wp-post-type-ui
Description: Inspired by "Custom Post Type UI" this plugin that gives you what have always wanted for creating dynamic post types and custom taxonomies in WordPress. The UI is made in true WP style, with a smooth integration to give nice and easy access to create and edit post types and taxonomies. Just install and you are ready to rock... =)
Author: Henrik Urlund
Version: 0.5.2
Author URI: http://urlund.com/
*/

// Define current version constant
define('CURRENT_VERSION', '0.5.2');

class post_type_ui
{
	var $post_types;
	var $taxonomies;
	
	function post_type_ui()
	{
		// first we collect the data, if there are any
		$db_post_types 	= get_option('post_types');
		$db_taxonomies	= get_option('taxonomies');
		
		// then lets check if its a sting, if thats the case, make it an array
		$this->post_types = (is_string($db_post_types)) ? unserialize($db_post_types) 	: $db_post_type 	;
		$this->taxonomies = (is_string($db_taxonomies)) ? unserialize($db_taxonomies) 	: $db_taxonomies 	;
		
		// if we didnt get any, create empty arrays to work with
		if(!is_array($this->post_types))
			$this->post_types = array();
			
		if(!is_array($this->taxonomies))
			$this->taxonomies = array();
		
		// now initialize the stuff
		add_action('admin_menu',  array(&$this, 'plugin_menu'));
		add_action('init', array(&$this, 'init_post_types'), 0);
		
		// flushes rewrite rules on activation/deactivation
		register_deactivation_hook(__FILE__, array($this, 'deact_post_type_ui'));
		register_activation_hook(__FILE__, array($this, 'act_post_type_ui'));
	}
	
	/**
	 * :: Added in 0.4.2
	 * flush rewrite rules, on plugin deactivation to remove rules for custom post types
	 */
	
	function deact_post_type_ui()
	{
		flush_rewrite_rules();
	}
	
	/**
	 * :: Added in 0.4.2
	 * flush rewrite rules, on plugin activation to add rules for custom post types
	 */
	
	function act_post_type_ui()
	{
		flush_rewrite_rules();
	}
	
	function handle_action_call()
	{
		if(isset($_POST['action']))
		{
			switch($_POST['action'])
			{
				case 'add_post_type':
					$post_type = $this->create_register_post_type_array($_POST);
					if(is_array($post_type))
					{
						$this->post_types[] = $post_type;
						update_option('post_types', serialize($this->post_types));
					}
					wp_redirect($_POST['_wp_http_referer']);
					break;
				
				case 'edit_post_type':
					$post_type = $this->create_register_post_type_array($_POST);
					if(is_array($post_type))
					{
						$this->post_types[$_POST['post_type_index']] = $post_type;
						update_option('post_types', serialize($this->post_types));
					}
					wp_redirect($_POST['_wp_http_referer']);
					break;
				
				case 'add_taxonomy':
					$taxonomy = $this->create_register_taxonomy_array($_POST);
					if(is_array($taxonomy))
					{
						$this->taxonomies[] = $taxonomy;
						update_option('taxonomies', serialize($this->taxonomies));
					}
					wp_redirect($_POST['_wp_http_referer']);
					break;
				
				case 'edit_taxonomy':
					$taxonomy = $this->create_register_taxonomy_array($_POST);
					if(is_array($taxonomy))
					{
						$this->taxonomies[$_POST['taxonomy_index']] = $taxonomy;
						update_option('taxonomies', serialize($this->taxonomies));
					}
					wp_redirect($_POST['_wp_http_referer']);
					break;
			}
		}
		
		if(isset($_GET['action']))
		{
			switch($_GET['action'])
			{
				case 'delete_post_type':
					unset($this->post_types[$_GET['i']]);
					update_option('post_types', serialize($this->post_types));
					wp_redirect('?page='. $_GET['page']);
					break;
				
				case 'delete_taxonomy':
					unset($this->taxonomies[$_GET['i']]);
					update_option('taxonomies', serialize($this->taxonomies));
					wp_redirect('?page='. $_GET['page']);
					break;
			}
		}
	}
	
	function plugin_menu()
	{
		add_options_page('Post Types', 'Post Types', 8, 'post_types', array($this, 'manage_post_types'));
		add_options_page('Taxonomies', 'Taxonomies', 8, 'taxonomies', array($this, 'manage_taxonomies'));
	}
	
	function init_post_types()
	{
		$this->handle_action_call();
		
		foreach ($this->post_types as $post_type)
		{
			 // menu_position must be typecasted to int or be null
			if((int) $post_type['menu_position'] > 0)
				$post_type['menu_position'] = (int) $post_type['menu_position'];
			else
				$post_type['menu_position'] = null;
			
			// make sure that taxonomies is an array
			$post_type['taxonomies'] = (is_array($post_type['taxonomies'])) ? $post_type['taxonomies'] : array() ;
			
			register_post_type($post_type['labels']['name'], $post_type);
		}
		
		foreach ($this->taxonomies as $taxonomy)
			register_taxonomy($taxonomy['args']['labels']['name'], $taxonomy['object_type'], $taxonomy['args']);
		
		if(isset($_POST['action']) || isset($_GET['action']))
			flush_rewrite_rules();
	}
	
	function manage_post_types()
	{
		// Default values
		
		$btn_text		= 'Add New Post Type';
		$form_action		= 'add_post_type';
		$post_type_index	= '';
		$item_menu_position	= 0;
		$item_menu_icon		= '';
		
		$item_name 		= '';
		$item_label 		= '';
		$item_singular_name 	= '';
		$item_description	= '';
		$item_slug		= '';
		
		$item_public			= ' checked="checked"';
		$item_publicly_queryable	= ' checked="checked"';
		$item_exclude_from_search	= ' checked="checked"';
		$item_show_ui			= ' checked="checked"';
		$item_hierarchical		= '';
		$item_rewrite			= ' checked="checked"';
		$item_with_front		= ' checked="checked"';
		$item_query_var			= ' checked="checked"';
		$item_can_export		= ' checked="checked"';
		$item_show_in_nav_menus		= '';
		$capability_type_value		= '';
		
		$support_items			= array('title' => 'Title', 'editor' => 'Editor', 'author' => 'Author', 'thumbnail' => 'Thumbnail', 'excerpt' => 'Excerpt', 'trackbacks' => 'Trackbacks', 'custom-fields' => 'Custom Fields', 'comments' => 'Comments', 'revisions' => 'Revisions', 'page-attributes' => 'Page Attributes');
		$menu_position_items		= array(0 => 'Below Comments', 5 => 'Below Posts', 10 => 'Below Media', 20 => 'Below Pages');
		$taxonomies_options_items	= array('category' => 'Category', 'post_tag' => 'Post Tags');
		$item_taxonomies		= array();
		
		$support_selection		= array('title', 'editor');
		
		if(isset($_GET['action']))
		{
			switch($_GET['action'])
			{
				case 'edit_post_type':
					$btn_text		= 'Update Post Type';
					$form_action		= 'edit_post_type';
					$post_type_index	= $_GET['i'];
					
					$item = $this->post_types[$post_type_index];
					$item_menu_position	= $item['menu_position'];
					$item_name 		= $item['labels']['name'];
					$item_singular_name 	= $item['labels']['singular_name'];
					$support_selection	= $item['supports'];
					$item_description	= $item['description'];
					$item_menu_icon		= $item['menu_icon'];
					$item_capability_type	= $item['capability_type'];
					$item_taxonomies	= $item['taxonomies'];
					
					if(!is_array($item_taxonomies))
						$item_taxonomies[] = $item_taxonomies;
					
					if(!$item['public'])
						$item_public			= '';
					
					if(!$item['publicly_queryable'])
						$item_publicly_queryable	= '';
					
					if(!$item['exclude_from_search'])
						$item_exclude_from_search	= '';
						
					if(!$item['show_ui'])
						$item_show_ui			= '';
						
					if($item['hierarchical'])
						$item_hierarchical		= ' checked="checked"';
						
					if(!$item['rewrite'])
						$item_rewrite			= '';
						
					if(is_array($item['rewrite']))
						$item_slug			= $item['rewrite']['slug'];
					
					if(is_array($item['rewrite']))
						$item_with_front		= ($item['rewrite']['with_front']) ? ' checked="checked"' : '' ;
						
					if(!$item['query_var'])
						$item_query_var			= '';
					
					if(!$item['can_export'])
						$item_can_export		= '';
						
					if($item['show_in_nav_menus'])
						$item_show_in_nav_menus		= ' checked="checked"';
					
					break;
			}
		}
		
		$support_select_options 	= '';
		foreach($support_items as $support_item_key => $support_item_value)
			$support_select_options .= (in_array($support_item_key, $support_selection)) ? '<option value="'. $support_item_key .'" selected="selected">'. $support_item_value .'</option>' : '<option value="'. $support_item_key .'">'. $support_item_value .'</option>' ;
		
		$menu_position_select_options 	= '';
		foreach($menu_position_items as $position_item_index => $position_item_text)
			$menu_position_select_options .= ($item_menu_position == $position_item_index) ? '<option value="'. $position_item_index .'" selected="selected">'. $position_item_text .'</option>' : '<option value="'. $position_item_index .'">'. $position_item_text .'</option>' ;
		
		$taxonomies_options 		= '';
		foreach ($taxonomies_options_items as $taxonomies_options_item_value => $taxonomies_options_item_text)
			$taxonomies_options .= (in_array($taxonomies_options_item_value, $item_taxonomies)) ? '<option value="'. $taxonomies_options_item_value .'" selected="selected">'. $taxonomies_options_item_text .'</option>' : '<option value="'. $taxonomies_options_item_value .'">'. $taxonomies_options_item_text .'</option>' ;
		
		?>
		<div class="wrap nosubsub">
			<div class="icon32" id="icon-options-general"><br /></div>
			
			<h2>Post Types</h2>
			<div id="ajax-response"></div>
			
			<form method="get" action="" class="search-form">
				<input type="hidden" value="<?php echo $_GET['page']; ?>" name="page">
				<p class="search-box">
					<label for="tag-search-input" class="screen-reader-text">Search Categories:</label>
					<input type="text" value="<?php echo $_GET['s']; ?>" name="s" id="tag-search-input">
					<input type="submit" class="button" value="Search Post Types">
				</p>
			</form>
			
			<br class="clear">
			
			<div id="col-container">
				<div id="col-right">
					<div class="col-wrap">
						<form method="get" action="" id="posts-filter">
							<input type="hidden" value="category" name="taxonomy">
							<input type="hidden" value="post" name="post_type">
							<div class="tablenav">
								<?php if(isset($_GET['action'])) : ?><input type="button" value="Add New Post Type" onclick="location.href='?page=<?php echo $_GET['page']; ?>'" class="button"><?php endif; ?>
								<?php if(isset($_GET['s'])) : ?><input type="button" value="Cancel Post Type Search" onclick="location.href='?page=<?php echo $_GET['page']; ?>'" class="button"><?php endif; ?>
								<div class="alignleft actions"></div>
								<br class="clear">
							</div>
							<div class="clear"></div>
							
							<table cellspacing="0" class="widefat tag fixed">
								<thead>
									<tr>
										<th style="" class="manage-column column-cb check-column" id="cb" scope="col"></th>
										<th style="" class="manage-column column-name" id="name" scope="col">Name</th>
										<th style="" class="manage-column column-description" id="description" scope="col">Description</th>
										<th style="" class="manage-column column-slug" id="slug" scope="col">Show UI</th>
										<th style="" class="manage-column column-posts num" id="posts" scope="col">Posts</th>
									</tr>
								</thead>
								
								<tfoot>
									<tr>
										<th style="" class="manage-column column-cb check-column" scope="col"></th>
										<th style="" class="manage-column column-name" scope="col">Name</th>
										<th style="" class="manage-column column-description" scope="col">Description</th>
										<th style="" class="manage-column column-slug" scope="col">Show UI</th>
										<th style="" class="manage-column column-posts num" scope="col">Posts</th>
									</tr>
								</tfoot>
								
								<tbody class="list:tag" id="the-list">
									<?php
									if(count($this->post_types) > 0):
										foreach ($this->post_types as $index => $post_type) :
											if(isset($_GET['s']) && strlen($_GET['s']) > 0)
											{
												$search_string = strtolower($_GET['s']);
												
												// the # is a "hack" to prevent 0 in response
												if(!strpos(strtolower('#'. $post_type['labels']['name']), $search_string) && !strpos(strtolower('#'. $post_type['description']), $search_string))
													continue;
											}
											
											$num_posts = (array) wp_count_posts($post_type['labels']['name'], 'readable');
											$total_posts = array_sum((array) $num_posts);
											?>
											<tr class="alternate" id="tag-1">
												<th class="check-column" scope="row">&nbsp;</th>
												<td class="name column-name"><strong><a title="Edit <?php echo $post_type['labels']['name']; ?>" href="?page=<?php echo $_GET['page'] ?>&amp;action=edit_post_type&amp;i=<?php echo $index; ?>" class="row-title"><?php echo $post_type['labels']['name']; ?></a></strong><br><div class="row-actions"><span class="edit"><a href="?page=<?php echo $_GET['page'] ?>&amp;action=edit_post_type&amp;i=<?php echo $index; ?>">Edit</a> | </span><span class="trash"><a class="submitdelete" href="?page=<?php echo $_GET['page'] ?>&amp;action=delete_post_type&amp;i=<?php echo $index; ?>">Delete</a></span></div></td>
												<td class="description column-description"><?php echo $post_type['description']; ?></td>
												<td class="slug column-slug"><?php echo ($post_type['show_ui']) ? 'Yes' : 'No' ; ?></td>
												<td class="posts column-posts num"><a href="edit.php?post_type=<?php echo $post_type['labels']['name']; ?>"><?php echo number_format_i18n($total_posts - $num_posts['auto-draft']); ?></a></td>
											</tr>
											<?php
										endforeach;
									endif;
									?>
								</tbody>
							</table>
							
							<br class="clear">
						</form>
						
						<div class="form-wrap">
							<p><strong>Note:</strong><br>Deleting a post type does not delete the posts in that type. You can easily recreate your post types and the content will still exist.</p>
						</div>
					</div>
				</div><!-- /col-right -->
				
				<div id="col-left">
					<div class="col-wrap">
						<div class="form-wrap">
							<h3><?php echo $btn_text; ?></h3>
							<form class="validate" action="" method="post" id="addtag">
								<input type="hidden" value="<?php echo $form_action; ?>" name="action">
								<input type="hidden" value="<?php echo $post_type_index; ?>" name="post_type_index">
								<input type="hidden" value="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $_SERVER['QUERY_STRING']; ?>" name="_wp_http_referer">
								<div class="form-field">
									<label for="name">Post Type Name</label>
									<input type="text" aria-required="true" size="40" id="name" name="name" value="<?php echo $item_name; ?>">
									<p>General name for the post type, usually plural. (e.g. <code>Movies</code>)</p>
								</div>
								<div class="form-field">
									<label for="singular_name">Singular Label</label>
									<input type="text" aria-required="true" size="40" id="singular_name" name="singular_name" value="<?php echo $item_singular_name; ?>">
									<p>Name for one object of this post type. (e.g. <code>Movie</code>)</p>
								</div>
								<div class="form-field">
									<label for="description">Description</label>
									<textarea rows="5" name="description" id="description"><?php echo $item_description; ?></textarea>
									<p>A short descriptive summary of what the post type is. (e.g. <code>My favorite movies!</code>)</p>
								</div>
								<p class="submit">
									<input type="submit" value="<?php echo $btn_text; ?>" id="submit" name="submit" class="button">
								</p>
								
								<h3>Advanced Post Type Options</h3>
								<table class="form-table">
									<tbody>
										<tr valign="top">
											<th scope="row"><label for="public">Public</label>
											<p>Whether posts of this type should be shown in the admin UI.</p>
											</th>
											<td><input type="checkbox" class="small-text" value="1" id="public" name="public"<?php echo $item_public; ?>>&nbsp;</td>
										</tr>
										<tr valign="top">
											<th scope="row"><label for="publicly_queryable">Publicly Queryable</label>
											<p>Whether post_type queries can be performed from the front page.</p>
											</th>
											<td><input type="checkbox" class="small-text" value="1" id="publicly_queryable" name="publicly_queryable"<?php echo $item_publicly_queryable; ?>>&nbsp;</td>
										</tr>
										<tr valign="top">
											<th scope="row"><label for="exclude_from_search">Exclude From Search</label>
											<p>Whether to exclude posts with this post type from search results.</p>
											</th>
											<td><input type="checkbox" class="small-text" value="1" id="exclude_from_search" name="exclude_from_search"<?php echo $item_exclude_from_search; ?>>&nbsp;</td>
										</tr>
										<tr valign="top">
											<th scope="row"><label for="show_ui">Show UI</label>
											<p>Whether to generate a default UI for managing this post type.</p>
											</th>
											<td><input type="checkbox" class="small-text" value="1" id="show_ui" name="show_ui"<?php echo $item_show_ui; ?>>&nbsp;</td>
										</tr>
										<tr valign="top">
											<th scope="row"><label for="hierarchical">Hierarchical</label>
											<p>Whether the post type is hierarchical. Allows Parent to be specified.</p>
											</th>
											<td><input type="checkbox" class="small-text" value="1" id="hierarchical" name="hierarchical"<?php echo $item_hierarchical; ?>>&nbsp;</td>
										</tr>
										<tr valign="top">
											<th scope="row"><label for="rewrite">Rewrite</label>
											<p>Rewrite permalinks with this format.</p>
											</th>
											<td><input type="checkbox" class="small-text" value="1" id="rewrite" name="rewrite"<?php echo $item_rewrite; ?>>&nbsp;</td>
										</tr>
										<tr valign="top">
											<th scope="row"><label for="with_front">Rewrite Front</label>
											<p>Allowing permalinks to be prepended with front base. <code>(Defaults to true)</code></p>
											</th>
											<td><input type="checkbox" class="small-text" value="1" id="with_front" name="with_front"<?php echo $item_with_front; ?>>&nbsp;</td>
										</tr>
										<tr valign="top">
											<th scope="row"><label for="query_var">Query Var</label>
											<p>Name of the query var to use for this post type.</p>
											</th>
											<td><input type="checkbox" class="small-text" value="1" id="query_var" name="query_var"<?php echo $item_query_var; ?>>&nbsp;</td>
										</tr>
										<tr valign="top">
											<th scope="row"><label for="can_export">Can Export</label>
											<p>Can this post type be exported.</p>
											</th>
											<td><input type="checkbox" class="small-text" value="1" id="can_export" name="can_export"<?php echo $item_can_export; ?>>&nbsp;</td>
										</tr>
										<tr valign="top">
											<th scope="row"><label for="show_in_nav_menus">Show in Nav Menus</label>
											<p>Whether post type is available for selection in navigation menus.</p>
											</th>
											<td><input type="checkbox" class="small-text" value="1" id="show_in_nav_menus" name="show_in_nav_menus"<?php echo $item_show_in_nav_menus; ?>>&nbsp;</td>
										</tr>
									</tbody>
								</table>
								<div class="form-field">
									<label for="menu_position">Menu Position</label>
									<select class="postform" style="width: 250px;" id="menu_position" name="menu_position">
										<?php echo $menu_position_select_options; ?>
									</select>
									<p>The position in the menu order the post type should appear.</p>
								</div>
								<div class="form-field">
									<label for="supports[]">Supports</label>
									<select class="postform" style="height: 120px; width: 250px;" id="supports[]" name="supports[]" multiple="multiple" size="5">
										<?php echo $support_select_options; ?>
									</select>
									<p>The standard items you want to add to the post type.</p>
								</div>
								<div class="form-field">
									<label for="taxonomies[]">Standard taxonomies</label>
									<select class="postform" style="height: 42px; width: 250px;" id="taxonomies[]" name="taxonomies[]" multiple="multiple" size="2">
										<?php echo $taxonomies_options; ?>
									</select>
									<p>The standard taxonomies you want to add to the post type.</p>
								</div>
								<div class="form-field">
									<label for="slug">Slug</label>
									<input type="text" aria-required="true" size="40" id="slug" name="slug" value="<?php echo $item_slug; ?>">
									<p>Prepend posts with this slug.<br /><code>(Defaults to post type's name)</code></p>
								</div>
								<div class="form-field">
									<label for="capability_type">Capability Type</label>
									<input type="text" aria-required="true" size="40" id="slug" name="capability_type" value="<?php echo $item_capability_type; ?>">
									<p>The post type to use for checking read, edit, and delete capabilities.<br /><code>(Defaults to "post")</code></p>
								</div>
								<div class="form-field">
									<label for="menu_icon">Menu Icon</label>
									<input type="text" aria-required="true" size="40" id="menu_icon" name="menu_icon" value="<?php echo $item_menu_icon; ?>">
									<p>The url to the icon to be used for this menu.<br /><code>(Defaults to the posts icon)</code></p>
								</div>
								<p class="submit"><input type="submit" value="<?php echo $btn_text; ?>" id="submit" name="submit" class="button"></p>
							</form>
						</div>
					</div>
				</div><!-- /col-left -->
			</div><!-- /col-container -->
		</div>
	<?php
	}
	
	function create_register_post_type_array($post_values)
	{
		if(strlen($post_values['name']) == 0 || strlen($post_values['singular_name']) == 0)
			return false;
		
		$labels = array(
			'name' 			=> _x($post_values['name'], 'post type general name'),
			'singular_name' 	=> _x($post_values['singular_name'], 'post type singular name'),
			'add_new' 		=> _x('Add New', $post_values['singular_name']),
			'add_new_item' 		=> __('Add New '. $post_values['singular_name']),
			'edit_item' 		=> __('Edit '. $post_values['singular_name']),
			'new_item' 		=> __('New '. $post_values['singular_name']),
			'view_item' 		=> __('View '. $post_values['singular_name']),
			'search_items' 		=> __('Search '. $post_values['name']),
			'not_found' 		=> __('No '. $post_values['name'] .' found'),
			'not_found_in_trash' 	=> __('No '. $post_values['name'] .' found in Trash'), 
			'parent_item_colon' 	=> ''
		);
		
		$capability_type = 'post';
		if(strlen($post_values['capability_type']) > 0)
			$capability_type = $post_values['capability_type'];
		
		$menu_icon = ($post_values['menu_icon']) ? $post_values['menu_icon'] : null ;
		
		if($this->get_boolean($post_values['rewrite']))
		{
			$rewrite = true;
			if(strlen($post_values['slug']) > 0)
				$rewrite = array('slug' => $post_values['slug'], 'with_front' => $this->get_boolean($post_values['with_front']));
		}
		else
			$rewrite = false;
			
		/**
		 * Make sure $taxonomies result is an array, even if empty - to prevent:
		 * PHP Warning:  Invalid argument supplied for foreach() in /Users/hu/Sites/wp_test/wp-includes/post.php on line 886
		 */
		$taxonomies_array = (is_array($post_values['taxonomies'])) ? $post_values['taxonomies'] : array() ;
		
		$args = array(
			'labels' 		=> $labels,
			'public' 		=> $this->get_boolean($post_values['public']),
			'description'		=> $post_values['description'],
			'publicly_queryable' 	=> $this->get_boolean($post_values['publicly_queryable']),
			'exclude_from_search'	=> $this->get_boolean($post_values['exclude_from_search']),
			'show_ui' 		=> $this->get_boolean($post_values['show_ui']),
			'query_var' 		=> $this->get_boolean($post_values['query_var']),
			'rewrite' 		=> $rewrite,
			'capability_type' 	=> $capability_type,
			'hierarchical' 		=> $this->get_boolean($post_values['hierarchical']),
			'menu_position' 	=> $post_values['menu_position'],
			'menu_icon'		=> $menu_icon,
			'supports' 		=> $post_values['supports'],
			'taxonomies'		=> $taxonomies_array,
			'show_in_nav_menus'	=> $this->get_boolean($post_values['show_in_nav_menus'])
		);
		
		return $args;
	}
	
	function get_boolean($bool)
	{
		if((int) $bool > 0)
			return true;
		else
			return false;
	}
	
	function manage_taxonomies()
	{
		$btn_text 	= 'Add New Taxonomy';
		$form_action 	= 'add_taxonomy';
		
		$item_name		= '';
		$item_label		= '';
		$item_single_label 	= '';
		
		$taxonomy_index 	= '';
		$select_item		= '';
		
		$item_public			= ' checked="checked"';
		$item_hierarchical		= '';
		$item_show_ui			= ' checked="checked"';
		$item_rewrite			= ' checked="checked"';
		$item_query_var			= ' checked="checked"';
		$item_show_tagcloud		= ' checked="checked"';
		
		if(isset($_GET['action']))
		{
			switch($_GET['action'])
			{
				case 'edit_taxonomy':
					$taxonomy_index 	= $_GET['i'];
					$item = $this->taxonomies[$taxonomy_index];
					
					$btn_text 		= 'Update Taxonomy';
					$form_action 		= 'edit_taxonomy';
					$select_items		= $item['object_type'];
					$item_name		= $item['args']['labels']['name'];
					$item_label		= $item['args']['label'];
					$item_single_label 	= $item['args']['labels']['singular_name'];
					
					if(!$item['args']['public'])
						$item_public		= '';
					
					if($item['args']['hierarchical'])
						$item_hierarchical	= ' checked="checked"';
					
					if(!$item['args']['show_ui'])
						$item_show_ui		= '';
					
					if(!$item['args']['rewrite'])
						$item_rewrite		= '';
					
					if(!$item['args']['query_var'])
						$item_query_var		= '';
						
					if(!$item['args']['show_tagcloud'])
						$item_show_tagcloud	= '';
					
					break;
			}
		}
		
		$options = '';
		if(count($this->post_types) > 0)
		{
			// make it compatible with previos versions where item was a string
			$select_items = (is_array($select_items)) ? $select_items : array($select_items) ;
			
			foreach ($this->post_types as $index => $post_type)
				$options .= (in_array($post_type['labels']['name'], $select_items)) ? '<option value="'. $post_type['labels']['name'] .'" selected="selected">'. $post_type['labels']['name'] .'</option>' : '<option value="'. $post_type['labels']['name'] .'">'. $post_type['labels']['name'] .'</option>' ;
		}
		
	?>
		<div class="wrap nosubsub">
			<div class="icon32" id="icon-options-general"><br /></div>
			
			<h2>Taxonomies</h2>
			<div id="ajax-response"></div>
			
			<form method="get" action="" class="search-form">
				<input type="hidden" value="<?php echo $_GET['page']; ?>" name="page">
				<p class="search-box">
					<label for="tag-search-input" class="screen-reader-text">Search Taxonomies:</label>
					<input type="text" value="<?php echo $_GET['s']; ?>" name="s" id="tag-search-input">
					<input type="submit" class="button" value="Search Taxonomies">
				</p>
			</form>
			<br class="clear">
			
			<div id="col-container">
				<div id="col-right">
					<div class="col-wrap">
							
							<div class="tablenav">
								<?php if(isset($_GET['action'])) : ?><input type="button" value="Add New Taxonomy" onclick="location.href='?page=<?php echo $_GET['page']; ?>'" class="button"><?php endif; ?>
								<?php if(isset($_GET['s'])) : ?><input type="button" value="Cancel Taxonomy Search" onclick="location.href='?page=<?php echo $_GET['page']; ?>'" class="button"><?php endif; ?>
								<div class="alignleft actions"></div>
								<br class="clear">
							</div>
							<div class="clear"></div>
							
							<table cellspacing="0" class="widefat tag fixed">
								<thead>
									<tr>
										<th style="" class="manage-column column-cb check-column" id="cb" scope="col"></th>
										<th style="" class="manage-column column-name" id="name" scope="col">Name</th>
										<th style="" class="manage-column column-description" id="description" scope="col">Used for Post Type</th>
										<th style="" class="manage-column column-slug" id="slug" scope="col">Hierarchical</th>
										<th style="" class="manage-column column-posts num" id="posts" scope="col">Active</th>
									</tr>
								</thead>
								
								<tfoot>
									<tr>
										<th style="" class="manage-column column-cb check-column" scope="col"></th>
										<th style="" class="manage-column column-name" scope="col">Name</th>
										<th style="" class="manage-column column-description" scope="col">Used for Post Type</th>
										<th style="" class="manage-column column-slug" scope="col">Hierarchical</th>
										<th style="" class="manage-column column-posts num" scope="col">Active</th>
									</tr>
								</tfoot>
								
								<tbody class="list:tag" id="the-list">
									<?php
									if(count($this->taxonomies) > 0):
										foreach ($this->taxonomies as $index => $taxonomy) :
											if(isset($_GET['s']) && strlen($_GET['s']) > 0)
											{
												$search_string = strtolower($_GET['s']);
												
												// the # is a "hack" to prevent 0 in response
												if(!strpos(strtolower('#'. $taxonomy['args']['labels']['name']), $search_string) && !strpos(strtolower('#'. $taxonomy['object_type']), $search_string))
													continue;
											}
											?>
											<tr class="alternate" id="tag-1">
												<th class="check-column" scope="row">&nbsp;</th>
												<td class="name column-name"><strong><a href="#" class="row-title"><?php echo $taxonomy['args']['labels']['name']; ?></a></strong><br><div class="row-actions"><span class="edit"><a href="?page=<?php echo $_GET['page'] ?>&amp;action=edit_taxonomy&amp;i=<?php echo $index; ?>">Edit</a> | </span><span class="trash"><a class="submitdelete" href="?page=<?php echo $_GET['page'] ?>&amp;action=delete_taxonomy&amp;i=<?php echo $index; ?>">Delete</a></span></div></td>
												<td class="description column-description"><?php echo (is_array($taxonomy['object_type'])) ? implode(', ', $taxonomy['object_type']) : $taxonomy['object_type'] ; ?></td>
												<td class="slug column-slug"><?php echo ($taxonomy['args']['hierarchical'] == 1) ? 'Yes' : 'No' ; ?></td>
												<td class="posts column-posts num"><a href="edit-tags.php?taxonomy=<?php echo $taxonomy['args']['labels']['name']; ?>&post_type=<?php echo $taxonomy['object_type'] ?>"><?php echo count(get_terms($taxonomy['args']['labels']['name'])); ?></a></td>
											</tr>
											<?php
										endforeach;
									endif;
									?>
								</tbody>
							</table>
							
							<br class="clear">
						</form>
						
						<div class="form-wrap">
							<p><strong>Note:</strong><br>Deleting a post type does not delete the posts in that type. You can easily recreate your post types and the content will still exist.</p>
						</div>
					</div>
				</div><!-- /col-right -->
				
				<div id="col-left">
					<div class="col-wrap">
						<div class="form-wrap">
							<h3><?php echo $btn_text; ?></h3>
							<form class="validate" action="" method="post" id="addtag">
								<input type="hidden" value="<?php echo $form_action; ?>" name="action">
								<input type="hidden" value="<?php echo $taxonomy_index; ?>" name="taxonomy_index">
								<input type="hidden" value="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $_SERVER['QUERY_STRING']; ?>" name="_wp_http_referer">
								<div class="form-field">
									<label for="name">Taxonomy Name</label>
									<input type="text" aria-required="true" size="40" id="name" name="name" value="<?php echo $item_name; ?>">
									<p>The name of the taxonomy. (e.g. <code>Actors</code>)</p>
								</div>
								<div class="form-field">
									<label for="singular_name">Single Label</label>
									<input type="text" aria-required="true" size="40" id="singular_name" name="singular_name" value="<?php echo $item_single_label; ?>">
									<p>Name for one object of this taxonomy. (e.g. <code>Actor</code>)</p>
								</div>
								<div class="form-field">
									<label for="post_type_name[]">Use for Post Type</label>
									<select class="postform" style="height: 120px; width: 250px;" id="post_type_name[]" name="post_type_name[]" multiple="multiple" size="5">
										<?php echo $options; ?>
									</select>
									<p>Name of the object type for the taxonomy object. (e.g. <code>Movies</code>)</p>
								</div>
								<p class="submit">
									<input type="submit" value="<?php echo $btn_text; ?>" id="submit" name="submit" class="button">
								</p>
								<h3>Advanced Taxonomy Options</h3>
								<table class="form-table">
									<tbody>
										<tr valign="top">
											<th scope="row"><label for="public">Public</label>
											<p>Should this taxonomy be exposed in the admin UI.</p>
											</th>
											<td><input type="checkbox" class="small-text" value="1" id="public" name="public"<?php echo $item_public; ?>>&nbsp;</td>
										</tr>
										<tr valign="top">
											<th scope="row"><label for="hierarchical">Hierarchical</label>
											<p>Is this taxonomy hierarchical (have descendants) like categories or not hierarchical like tags.</p>
											</th>
											<td><input type="checkbox" class="small-text" value="1" id="hierarchical" name="hierarchical"<?php echo $item_hierarchical; ?>>&nbsp;</td>
										</tr>
										<tr valign="top">
											<th scope="row"><label for="show_ui">Show UI</label>
											<p>Whether to generate a default UI for managing this taxonomy.</p>
											</th>
											<td><input type="checkbox" class="small-text" value="1" id="show_ui" name="show_ui"<?php echo $item_show_ui; ?>>&nbsp;</td>
										</tr>
										<tr valign="top">
											<th scope="row"><label for="show_tagcloud">Show Tagcloud</label>
											<p>Whether to show a tag cloud in the admin UI for this taxonomy.</p>
											</th>
											<td><input type="checkbox" class="small-text" value="1" id="show_tagcloud" name="show_tagcloud"<?php echo $item_show_tagcloud; ?>>&nbsp;</td>
										</tr>
										<tr valign="top">
											<th scope="row"><label for="query_var">Query Var</label>
											<p>False to prevent queries, or string to customize query var. Default will use taxonomy name as query var.</p>
											</th>
											<td><input type="checkbox" class="small-text" value="1" id="query_var" name="query_var"<?php echo $item_query_var; ?>>&nbsp;</td>
										</tr>
										<tr valign="top">
											<th scope="row"><label for="rewrite">Rewrite</label>
											<p>Set to false to prevent rewrite, or array to customize customize query var. Default will use taxonomy name as query var </p>
											</th>
											<td><input type="checkbox" class="small-text" value="1" id="rewrite" name="rewrite"<?php echo $item_rewrite; ?>>&nbsp;</td>
										</tr>
									</tbody>
								</table>
								<p class="submit">
									<input type="submit" value="<?php echo $btn_text; ?>" id="submit" name="submit" class="button">
								</p>
							</form>
						</div>
					</div>
				</div><!-- /col-left -->
			</div><!-- /col-container -->
		</div>
	<?php
	}
	
	function create_register_taxonomy_array($post_values)
	{
		$labels = array(
			'name' 				=> _x( $post_values['name'], 'taxonomy general name' ),
			'singular_name' 		=> _x( $post_values['singular_name'], 'taxonomy singular name' ),
			'search_items' 			=> __( 'Search '. $post_values['name'] ),
			'popular_items' 		=> __( 'Popular '. $post_values['name'] ),
			'all_items' 			=> __( 'All '. $post_values['name'] ),
			'parent_item' 			=> null,
			'parent_item_colon' 		=> null,
			'edit_item' 			=> __( 'Edit '. $post_values['name'] ), 
			'update_item' 			=> __( 'Update '. $post_values['name'] ),
			'add_new_item' 			=> __( 'Add New '. $post_values['singular_name'] ),
			'new_item_name' 		=> __( 'New '. $post_values['singular_name'] ),
			'separate_items_with_commas' 	=> __( 'Separate '. $post_values['name'] .' with commas' ),
			'add_or_remove_items' 		=> __( 'Add or remove '. $post_values['name']),
			'choose_from_most_used' 	=> __( 'Choose from the most used '. $post_values['name'])
		);
		
		$args = array(
			'hierarchical' 	=> $this->get_boolean($post_values['hierarchical']),
			'label'		=> $post_values['label'],
			'labels' 	=> $labels,
			'public' 	=> $this->get_boolean($post_values['public']),
			'show_ui' 	=> $this->get_boolean($post_values['show_ui']),
			'query_var' 	=> $this->get_boolean($post_values['query_var']),
			'show_tagcloud'	=> $this->get_boolean($post_values['show_tagcloud']),
			'rewrite' 	=> $this->get_boolean($post_values['rewrite']),
		);
		
		return array('object_type' => $post_values['post_type_name'], 'args' => $args);
	}
}

new post_type_ui();
?>