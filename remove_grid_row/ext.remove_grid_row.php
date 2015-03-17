<?php if ( ! defined('APP_VER')) exit('No direct script access allowed');
/**
 * Zoo Visitor Extension
 * 
 * @author    Matthew Shearing <matt@armitageonline.co.uk>
 * @copyright Copyright (c) 2015 Matthew Shearing
 * @license   none
 */
class Remove_grid_row_ext {
	var $name           = 'Remove Grid Row';
	var $version        = '1.0';
	var $description    = 'Deletes the grid row from the database using zoo_visitor_update_end hook.';
	var $settings_exist = 'n';
	var $docs_url       = '';
	/**
	 * Class Constructor
	 */
	function __construct()
	{
		
	}
	// --------------------------------------------------------------------
	/**
	 * Activate Extension
	 */
	function activate_extension()
	{
		// add the row to exp_extensions
		ee()->db->insert('extensions', array(
			'class'    => get_class($this),
			'method'   => 'zoo_visitor_update_end',
			'hook'     => 'zoo_visitor_update_end',
			'settings' => '',
			'priority' => 10,
			'version'  => $this->version,
			'enabled'  => 'y'
		));
	}
	/**
	 * Update Extension
	 */
	function update_extension($current = '')
	{
		// Nothing to change...
		return FALSE;
	}
	/**
	 * Disable Extension
	 */
	function disable_extension()
	{
		// Remove all zoo_visitor_update_end rows from exp_extensions
		ee()->db->where('class', get_class($this))
		             ->delete('extensions');
	}
	function zoo_visitor_update_end($member_data, $member_id) {
		
		if ($member_data['URI'] == "account/edit-details") {
			//member data multidimensional array has all data about the logged in user
			//$table is the field_id, and $data could be a value or an array
			foreach ($member_data as $table => $data) {
				//select only the $data that is an array
				if (is_array($data)) {
					//loop through each point in the $data array as $entry
					foreach($data as $entry) {
						//select only the $entry that is an array
						if (is_array($entry)) {
							//turn our field_id for this multidimensional array into a database table name
							$table = str_replace("field_id_", "exp_channel_grid_field_", $table);
							//loop through each $entry as $val
							foreach ($entry as $key => $val) {
								//get the value and column from the $entry array
								foreach ($val as $col => $value) {
									//if column is delete and value is yes then do something
									if ($col == 'delete' && $value == 'yes') {
										//destroy the variable for this entry from the array
										unset($entry[$key]);
										//get the row id by only using integers, remove all letters
										$row_id = filter_var($key, FILTER_SANITIZE_NUMBER_INT);
										//remove this entry from the database using the row_id, and member entry_id
										ee()->db->delete($table, array('row_id' => $row_id, 'entry_id' => $member_data['entry_id']));
									}
									if ($col == 'row_order') {
										//get the row id by only using integers, remove all letters
										$row_id = filter_var($key, FILTER_SANITIZE_NUMBER_INT);
										//update the row order of this entry in the database using the row_id, and member entry_id
										ee()->db->update($table, array($col => $value), array('row_id' => $row_id, 'entry_id' => $member_data['entry_id']));
									}
								}
							}
						}
					}
				}
			}
		}
	}
	// --------------------------------------------------------------------
	 
} //end ext class