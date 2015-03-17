<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * Grid Row ID Plugin
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Plugin
 * @author		Matt Shearing
 * @link		http://www.armitageonline.co.uk
 */

$plugin_info = array(
	'pi_name'		=> 'Grid Row ID',
	'pi_version'	=> '1.0',
	'pi_author'		=> 'Andrew Armitage, Matt Shearing',
	'pi_author_url'	=> 'http://www.armitageonline.co.uk',
	'pi_description'=> 'Provides row id for grid fields',
	'pi_usage'		=>  grid_row_id::usage()
);

class Grid_row_id {
	public $return_data;
    
	/**
	 * Constructor
	 */
	public function __construct()
	{
		//fetch our variables
		$table = ee()->TMPL->fetch_param('table');
		//entry_id="{entry_id}" dynamic members entry id
		$entry = ee()->TMPL->fetch_param('entry_id');
		//columns="col_id_10|col_id_11|col_id_12|col_id_13" pipe separated hard coded list of fields we want form the database table
		$columns = ee()->TMPL->fetch_param('columns');
		
		//turn string into array
		$columns = explode('|', $columns);
		
		//query the grid database table we want and pass into $result
		$result = ee()->db->from($table)->where(array('entry_id' => $entry))->order_by("row_order", "asc")->get();
		
		//change our value for $table into "field_id_xx" for use in input names
		$table = str_replace("exp_channel_grid_", "", $table);
		$table = str_replace("field_", "field_id_", $table);
		
		//if we have 1 or more rows returned
		if ($result->num_rows() > 0) {
			//loop through our sql result set
			foreach ($result->result() as $num => $row) {
				//row id used in each column so initiate foreach loop
				foreach ($columns as $column) {
					//set column name used in input fields
					$data[$column.'_name'] = $table.'[rows][row_id_'.$row->row_id.']['.$column.']';
					//set column value to be used in input fields
					$data[$column.'_value'] = $row->$column;
				}
				$data['row_order_name'] = $table.'[rows][row_id_'.$row->row_id.'][row_order]';
				$data['row_order_value'] = $row->row_order;
				//add delete input to be used as a checkbox
				$data['delete'] = $table.'[rows][row_id_'.$row->row_id.'][delete]';
				//return the grid row number for use in our jquery and divs/spans
				$data['grid_row'] = $row->row_id;
				$data['row_count'] = ++$num;
				$data['total_rows'] = $result->num_rows();
				
				//add our data array that we just build above to the vars array
				$vars[] = $data;
				//destroy our data array ready to be rebuilt in our next loop so there is no overlap of values
				unset($data);
			}
		//if no rows are returned
		} else {
			//go through each of our columns
			foreach ($columns as $column) {
				//set our input to add to database as a new row
				$data[$column.'_name'] = $table."[rows][new_row_0][".$column."]";
				//set our value to be empty
				$data[$column.'_value'] = "";
			}
			$data['row_count'] = 0;
			$data['row_order_name'] = $table.'[rows][new_row_0][row_order]';
			$data['row_order_value'] = 0;
			$data['total_rows'] = 0;
			//add our data array that we just build above to the vars array
			$vars[] = $data;
		}
	//return tag pair
	$this->return_data = ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $vars);
	}//end __construct()
	
	// ----------------------------------------------------------------
	
	/**
	 * Plugin Usage
	 */
	public static function usage()
	{
		ob_start();
?>

Core Purpose:
This plugin has been designed to pull member data from a grid field and display it within the plugin tags. The value are then displayed in separated fields which you can style and position yourself. It also creates the correct input names in an easy to use tag so that you can edit the details and submit them into the database. There are 3 parameters to the tag; the grid table you wish to target, the entry id, and a pipe separated list of the column names you wish to access. Each column selected will create 2 tags for use in form name attributes, eg. {col_id_10_name} and {col_id_10_value}. These can be used in your input fields as shown in the examples below. This was created to work with Zoo Visitor, but it should also work with Channel Form, but this is untested and may require the odd bit of tweaking.

Additional Features:
Adding new rows can be handled by javascript using [new_row_0] in the input name in front of the column number. And deleting rows has been included with a delete checkbox which works in combination with the extension we have also developed called Remove Grid Row. Just use the {delete} tag in the inputs name for this to work once the extension has been installed.

Basic Usage Example:
{exp:grid_row_id table="exp_channel_grid_field_72" entry_id="{entry_id}" columns="col_id_17|col_id_18"}
<input type="text" name="{col_id_17_name}" value="{col_id_17_value}">
<input type="text" name="{col_id_18_name}" value="{col_id_18_value}">
<input type="checkbox" name="{delete}" value="yes">
{/exp:grid_row_id}

Full Usage Example:
{exp:zoo_visitor:update_form return='/account/edit-details'}
<p>Email Address: <input id="member_email_address" name="member_email_address" type="text" value="{member_email_address}" /></p>

<p>
{exp:grid_row_id table="exp_channel_grid_field_71" entry_id="{entry_id}" columns="col_id_10,col_id_11,col_id_12,col_id_13"}
<span id="Address-{grid_row}">
Address 1: <input type="text" name="{col_id_10_name}" value="{col_id_10_value}"><br>
Address 2: <input type="text" name="{col_id_11_name}" value="{col_id_11_value}"><br>
Town: <input type="text" name="{col_id_12_name}" value="{col_id_12_value}"><br>
Default: <input type="checkbox" name="{col_id_13_name}" {if col_id_13_value == "default"}checked{/if} value="default"><br>
Delete: <input type="checkbox" name="{delete}" value="yes" onchange="delete_row('Address-{grid_row}')"><br>
</span>
{/exp:grid_row_id}
</p>

<p>
{exp:grid_row_id table="exp_channel_grid_field_72" entry_id="{entry_id}" columns="col_id_17"}
<span id="Telephone-{grid_row}">
Telephone Number: <input type="text" name="{col_id_17_name}" value="{col_id_17_value}"><br>
Delete: <input type="checkbox" name="{delete}" value="yes" onchange="delete_row('Telephone-{grid_row}')"><br>
</span>
{/exp:grid_row_id}
</p>

<p><input class="form-submit" type="submit" value="Update profile" /></p>
{/exp:zoo_visitor:update_form}

<?php
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}
}


/* End of file pi.grid_row_id.php */
/* Location: /system/expressionengine/third_party/grid_row_id/pi.grid_row_id.php */