<?php  if ( ! defined('IN_SYSTEM')) exit('No direct script access allowed');

 /**
 * CodeIgniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package  	CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Pagination Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Pagination
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/pagination.html
 */
class CI_Pagination {

	var $base_url			= ''; // The page we are linking to
	var $total_rows  		= ''; // Total number of items (database results)
	var $per_page	 		= 10; // Max number of items you want shown per page
	var $num_links			=  2; // Number of "digit" links to show before/after the currently viewed page
	var $cur_page	 		=  0; // The current page being viewed
	var $first_link   		= '&lt;&lt;';
	var $next_link			= 'Туда&#187;&#187;<br />';//next
	var $prev_link			= '&#171;&#171;Сюда '; // prew
	var $last_link			= '&gt;&gt;';
	var $uri_segment		= 3;
	var $full_tag_open		= '';
	var $full_tag_close		= '';
	var $first_tag_open		= '';
	var $first_tag_close	= ' ... ';
	var $last_tag_open		= ' ... ';
	var $last_tag_close		= '<br />';
	var $cur_tag_open		= ' <strong>';
	var $cur_tag_close		= '</strong>';
	var $next_tag_open		= '';
	var $next_tag_close		= ' ';
	var $prev_tag_open		= ' ';
	var $prev_tag_close		= ' | ';
	var $num_tag_open		= ' ';
	var $num_tag_close		= ',';
	var $page_query_string	= FALSE;
	var $query_string_segment = 'start';

	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	array	initialization parameters
	 */
	function CI_Pagination($params = array())
	{
		if (count($params) > 0)
		{
			$this->initialize($params);
		}

		#log_message('debug', "Pagination Class Initialized");
	}

	// --------------------------------------------------------------------

	/**
	 * Initialize Preferences
	 *
	 * @access	public
	 * @param	array	initialization parameters
	 * @return	void
	 */
	function initialize($params = array())
	{
		if (count($params) > 0)
		{
			foreach ($params as $key => $val)
			{
				if (isset($this->$key))
				{
					$this->$key = $val;
				}
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Generate the pagination links
	 *
	 * @access	public
	 * @return	string
	 */
	function create_links()
	{
		$start = $_GET['start'];
		$start = is_numeric($_GET['page']) ? $_GET['page'] * $this->per_page - 1 : $start;
		// If our item count or per-page total is zero there is no need to continue.
		if ($this->total_rows == 0 OR $this->per_page == 0)
		{
		   return '';
		}

		// Calculate the total number of pages
		$num_pages = ceil($this->total_rows / $this->per_page);

		// Is there only one page? Hm... nothing more to do here then.
		if ($num_pages == 1)
		{
			return '';
		}

		$this->cur_page = $start;

		// Prep the current page - no funny business!
		$this->cur_page = (int) $this->cur_page;


		$this->num_links = (int)$this->num_links;

		if ($this->num_links < 1)
		{
			a_error('Your number of links must be a positive number.');
		}

		if ( ! is_numeric($this->cur_page))
		{
			$this->cur_page = 0;
		}

		// Is the page number beyond the result range?
		// If so we show the last page
		if ($this->cur_page > $this->total_rows)
		{
			$this->cur_page = ($num_pages - 1) * $this->per_page;
		}

		$uri_page_number = $this->cur_page;
		$this->cur_page = floor(($this->cur_page/$this->per_page) + 1);

		// Calculate the start and end numbers. These determine
		// which number to start and end the digit links with
		$start = (($this->cur_page - $this->num_links) > 0) ? $this->cur_page -  ($this->num_links  - 1) : 1;
		$end   = (($this->cur_page + $this->num_links) < $num_pages) ? $this->cur_page + $this->num_links : $num_pages;

		// And here we go...
		$output = '';

		// Render the "previous" link
		if(!empty($this->prev_link)) {
			if  ($this->cur_page != 1)
			{
				$i = $uri_page_number - $this->per_page;
				if ($i == 0) $i = '';
				$output .= $this->prev_tag_open.'<a href="'.$this->base_url.$i.'">'.$this->prev_link.'</a>'.$this->prev_tag_close;
			}
			else $output .= $this->prev_tag_open . $this->prev_link . $this->prev_tag_close;
			

		}
				// Render the "next" link
		if(!empty($this->next_link)) {
			if ($this->cur_page < $num_pages)
			{
				$output .= $this->next_tag_open.'<a href="'.$this->base_url.($this->cur_page * $this->per_page).'">'.$this->next_link.'</a>'.$this->next_tag_close;
			}
			else $output .= $this->next_tag_open . $this->next_link . $this->next_tag_close;
		}
        
		// Render the "First" link
		if(!empty($this->first_link)) {
			if  ($this->cur_page > ($this->num_links + 1))
			{
				$output .= $this->first_tag_open .'<a href="'.$this->base_url.'">1</a>'. $this->first_tag_close;
			}
			#else $output .= $this->first_tag_open . $this->first_link . $this->first_tag_close;;
		}


		

		// Write the digit links
		for ($loop = $start -1; $loop <= $end; $loop++)
		{
			$i = ($loop * $this->per_page) - $this->per_page;

			if ($i >= 0)
			{
				if ($this->cur_page == $loop)
				{
					$output .= $this->cur_tag_open.$loop.$this->cur_tag_close.($loop != $num_pages ? ',' : ''); // Current page
				}
				else
				{
					$n = ($i == 0) ? '' : $i;
					$output .= $this->num_tag_open.'<a href="'.$this->base_url.$n.'">'.$loop.'</a>'. ($loop != $num_pages ? $this->num_tag_close : '');
				}
			}
		}


		

		// Render the "Last" link
		if(!empty($this->last_link)) {
			if (($this->cur_page + $this->num_links) < $num_pages)
			{
				$i = (($num_pages * $this->per_page) - $this->per_page);
				$output .= $this->last_tag_open.'<a href="'.$this->base_url.$i.'">'.$num_pages.'</a>'.$this->last_tag_close;
			}
			#else $output .= $this->last_tag_open . $this->last_link . $this->last_tag_close;
		}




		// Kill double slashes.  Note: Sometimes we can end up with a double slash
		// in the penultimate link so we'll kill all double slashes.
		$output = preg_replace("#([^:])//+#", "\\1/", $output);

		// Add the wrapper HTML if exists
		$output = $this->full_tag_open.$output.$this->full_tag_close;

		return $output;
	}
}
// END Pagination Class

