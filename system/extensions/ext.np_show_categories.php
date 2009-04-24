<?php
/* ===========================================================================
ext.np_show_categories.php ---------------------------
Show categories on the edit page list.
            
INFO ---------------------------
Developed by: Nathan Pitman, nathanpitman.com
Created:   Apr 24 2009

Related Thread: 
=============================================================================== */
if ( ! defined('EXT')) { exit('Invalid file request'); }

class Np_show_categories
{
	var $settings		= array();
	var $name           = 'NP Show Categories';
	var $version        = '1.0';
	var $description    = 'Show category assignment on the edit page list.';
	var $settings_exist = 'y';
	var $docs_url       = 'http://nathanpitman.com';

// --------------------------------
//  PHP 4 Constructor
// --------------------------------
	function Np_show_categories($settings='')
	{
		$this->__construct($settings);
	}

// --------------------------------
//  PHP 5 Constructor
// --------------------------------
	function __construct($settings='')
	{
		$this->settings = $settings;
	}

	// --------------------------------
	//  Change Settings
	// --------------------------------  
 	function settings()
	{
		$settings = array();
		$settings['category_limit'] = '';

		return $settings;
	}
	
	// --------------------------------
	//  Activate Extension
	// --------------------------------
	function activate_extension()
	{
		global $DB, $PREFS;
		
		$default_settings = serialize(
			array(
				
			  'field_id' => ""
			)
		);
		

	
		$hooks = array(
		  'edit_entries_additional_tableheader' => 'edit_entries_additional_tableheader',
		  'edit_entries_additional_celldata'    => 'edit_entries_additional_celldata',
		);
		
		foreach ($hooks as $hook => $method)
		{
			$sql[] = $DB->insert_string( 'exp_extensions', 
				array('extension_id' 	=> '',
					'class'			=> get_class($this),
					'method'		=> $method,
					'hook'			=> $hook,
					'settings'		=> $default_settings,
					'priority'		=> 10,
					'version'		=> $this->version,
					'enabled'		=> "y"
				)
			);
		}

		// run all sql queries
		foreach ($sql as $query)
		{
			$DB->query($query);
		}
		return TRUE;
	}	
	
	// --------------------------------
	//  Disable Extension
	// -------------------------------- 
	function disable_extension()
	{
		global $DB;
		$DB->query("DELETE FROM exp_extensions WHERE class = '" . get_class($this) . "'");
	}
	
	// --------------------------------
	//  Update Extension
	// --------------------------------  
	function update_extension($current='')
	{
		global $DB;
		
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
		
		$DB->query("UPDATE exp_extensions
		            SET version = '".$DB->escape_str($this->version)."'
		            WHERE class = '".get_class($this)."'");
	}
	// END
// ============================================================================


	// --------------------------------
	//  Add Category Heading to Table
	// --------------------------------
	
	function edit_entries_additional_tableheader()
	{
		global $DSP, $LANG, $EXT;
		
		if (empty($this->settings['category_limit'])) {
		  return;
		}
		
		$LANG->fetch_language_file('np_show_categories');
		$extra = ($EXT->last_call !== FALSE) ? $EXT->last_call : '';
		return $extra.$DSP->table_qcell('tableHeadingAlt', $LANG->line('categories'));
	}
	// END


	// ---------------------------------
	//  Add Categories for Entries
	// ---------------------------------
	
	function edit_entries_additional_celldata($row)
	{	
		global $DSP, $LANG, $EXT, $DB;
		
		global $i;
		 
		 if (empty($i))
		 {
		 	$i = 0;
		 }
		
		if (empty($this->settings['category_limit'])) {
		  return;
		}
		
		$full_categories = "";
		$categories="";
		$more = false;
		
		$query = $DB->query("SELECT c.cat_name FROM (exp_categories AS c, exp_category_posts AS p) WHERE c.cat_id = p.cat_id AND p.entry_id='".$row['entry_id']."' ORDER BY c.cat_order");
		
		$category_count = 0;
		
		foreach($query->result as $category) {
			$full_categories = $categories.$category['cat_name'].", ";
			
			if ($category_count < $this->settings['category_limit']) {
				$categories = $categories.$category['cat_name'].", ";
				$category_count++;
			} else {
				$more = true;
			}
	  	}
	  	
	  	if ($more == true) {
	  		$categories = '<div class="smallNoWrap"><abbr title="'.rtrim($full_categories,", ").'" style="cursor: help;">'.rtrim($categories,", ").'...</abbr></div>';
		} else {
			$categories = '<div class="smallNoWrap">'.rtrim($categories,", ").'</div>';
		}
		$style = ($i % 2) ? 'tableCellOne' : 'tableCellTwo'; $i++;
		$extra = ($EXT->last_call !== FALSE) ? $EXT->last_call : '';
		return $extra.$DSP->table_qcell($style, $categories);
		
	}

/* END class */
}
/* End of file ext.np_show_categories.php */
/* Location: ./system/extensions/ext.np_show_categories.php */ 