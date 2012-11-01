<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Poll lib
 *
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author		WookieMonster
 * @link		http://github.com/wookiemonster
 */
class Poll_lib {
	
	private $CI;
	private $allow_multiple_votes;
	private $interval_between_votes;
	private $max_poll_options;
	private $min_poll_options;
	private $errors;
	private $error_start_delim;
	private $error_end_delim;
	
	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->database();
		$this->CI->load->model('poll_model');
		
		$this->CI->load->config('poll');
		$this->allow_multiple_votes = $this->CI->config->item('allow_multiple_votes', 'poll');
		$this->interval_between_votes = $this->CI->config->item('interval_between_votes', 'poll');
		$this->max_poll_options = $this->CI->config->item('max_poll_options', 'poll');
		$this->min_poll_options = $this->CI->config->item('min_poll_options', 'poll');
		
		$this->CI->lang->load('poll');
		$this->errors = array();
		
		$this->error_start_delim = '<p class="error">';
		$this->error_end_delim = '</p>';
	}
	
	/**
	 * __call() - overload undefined methods in this class to Poll_model
	 * 
	 * @access	public
	 * @param	string
	 * @param	array
	 * @return	mixed
	 */
	public function __call($method, $params)
	{
		if ( ! method_exists($this->CI->poll_model, $method))
		{
			throw new Exception("Undefined method Poll::{$method}() called");
		}

		return call_user_func_array(array($this->CI->poll_model, $method), $params);
	}
	
	/**
	 * Output a data structure which can be used to display all polls (supports paging with $limit, $offset)
	 * 
	 * @access	public
	 * @param	integer
	 * @param	integer
	 * @return	mixed
	 */
	public function all_polls($limit, $offset)
	{
		$polls = $this->get_polls($limit, $offset);
		$data = array();
		
		if ($polls === FALSE)
		{
			return FALSE;
		}
		
		foreach ($polls as $poll)
		{
			// get the votes for each option
			$options = array();
			$total_votes = 0;
			
			foreach ($this->get_poll_options($poll['poll_id']) as $option)
			{
				$option_votes = $this->get_options_votes($option['option_id']);
				$options[] = array('option_id' => $option['option_id'], 'title' => $option['title'], 'votes' => $option_votes);
				// add up total number of votes for this poll
				$total_votes += $option_votes;
			}
			
			// calculate percentages
			foreach ($options as $key => $value)
			{
				if ($options[$key]['votes'] == 0)
				{
					$options[$key]['percentage'] = 0;
				}
				else
				{
					$options[$key]['percentage'] = ($options[$key]['votes'] / $total_votes) * 100;
				}
			}
			
			// add array of options => votes to $data
			$data[] = array(
				'poll_id' => $poll['poll_id'],
				'title' => $poll['title'],
				'total_votes' => $total_votes,
				'options' => $options,
				'closed' => $poll['closed']
			);
		}
		
		return $data;
	}
	
	/**
	 * Output a data structure of a single poll with poll_id
	 * To be added: if poll_id not set then show latest poll
	 * 
	 * @access	public
	 * @param	integer
	 * @return	mixed
	 */
	public function single_poll($poll_id = FALSE)
	{
		// if no poll id fetch the latest poll
		if ($poll_id === FALSE)
		{
			$poll = $this->get_latest_poll();
		}
		else
		{
			$poll = $this->get_poll($poll_id);
		}
		
		if ($poll === FALSE)
		{
			return FALSE;
		}
		
		$options = array();
		$total_votes = 0;
		
		foreach ($this->get_poll_options($poll['poll_id']) as $option)
		{
			$option_votes = $this->get_options_votes($option['option_id']);
			$options[] = array('option_id' => $option['option_id'], 'title' => $option['title'], 'votes' => $option_votes);
			// add up total number of votes for this poll
			$total_votes += $option_votes;
		}
		
		// calculate percentages
		foreach ($options as $key => $value)
		{
			if ($options[$key]['votes'] == 0)
			{
				$options[$key]['percentage'] = 0;
			}
			else
			{
				$options[$key]['percentage'] = ($options[$key]['votes'] / $total_votes) * 100;
			}
		}
		
		$data = array(
			'poll_id' => $poll['poll_id'],
			'title' => $poll['title'],
			'total_votes' => $total_votes,
			'options' => $options,
			'closed' => $poll['closed']
		);
		
		return $data;
	}
	
	/**
	 * Add users vote for this poll
	 * 
	 * @access	public
	 * @param	integer
	 * @param	integer
	 * @return	mixed
	 */
	public function vote($poll_id, $option_id)
	{
		if ($this->is_closed($poll_id))
		{
			$this->set_error('error_poll_closed');
			return FALSE;
		}
		
		if ($this->allow_multiple_votes === TRUE)
		{
			if ( ! $this->has_previously_voted_within($this->interval_between_votes, $poll_id))
			{
				$this->add_vote($option_id);
				return TRUE;
			}
			else
			{
				$this->set_error('error_has_previously_voted_within_time');
				return FALSE;
			}
		}
		else
		{
			if ( ! $this->has_previously_voted($poll_id))
			{
				$this->add_vote($option_id);
				return TRUE;
			}
			else
			{
				$this->set_error('error_multiple_votes_not_allowed');
				return FALSE;
			}
		}
	}
	
	/**
	 * Set the start and end delimiters for error messages
	 * 
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	null
	 */
	public function set_error_delimiters($error_start_delim, $error_end_delim)
	{
		$this->error_start_delim = $error_start_delim;
		$this->error_end_delim = $error_end_delim;
	}
	
	/**
	 * Sets an error message
	 * 
	 * @access	public
	 * @param	string
	 * @return	null
	 */
	public function set_error($error)
	{
		$this->errors[] = $error;
	}
	
	/**
	 * Get error messages
	 * 
	 * @access	public
	 * @return	string
	 */
	public function get_errors()
	{
		$str = '';
		
		foreach ($this->errors as $error)
		{
			$str .= $this->error_start_delim.$this->CI->lang->line($error).$this->error_end_delim;
		}
		
		return $str;
	}
}

/* End of file Poll.php */
/* Location: ./application/libraries/Poll.php */