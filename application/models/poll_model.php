<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Poll lib model
 *
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author		WookieMonster
 * @link		http://github.com/wookiemonster
 */
class Poll_model extends CI_Model {
	
	protected $polls_table;
	protected $options_table;
	protected $votes_table;
	
	/**
	 * Constructor
	 * 
	 * @access	public
	 * @return	null
	 */
	public function __construct()
	{
		$this->polls_table = 'polls';
		$this->options_table = 'options';
		$this->votes_table = 'votes';
	}
	
	/**
	 * Returns total number of polls
	 * 
	 * @access	public
	 * @return	integer
	 */
	public function num_polls()
	{
		return $this->db->count_all($this->polls_table);
	}
	
	/**
	 * Gets a single poll with poll_id
	 * 
	 * @access	public
	 * @param	int
	 * @return	mixed (array, boolean)
	 */
	public function get_poll($poll_id)
	{
		$this->db->select('poll_id, title, closed');
		$query = $this->db->get_where($this->polls_table, array('poll_id' => $poll_id));
		
		if ($query->num_rows == 1)
		{
			return $query->row_array();
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Get all polls within limit and offset
	 * 
	 * @access	public
	 * @param	int
	 * @param	int
	 * @return	mixed (array, boolean)
	 */
	public function get_polls($limit, $offset)
	{
		$this->db->select('poll_id, title, closed')->order_by('created', 'desc');
		$query = $this->db->get($this->polls_table, $limit, $offset);
		
		if ($query->num_rows > 0)
		{
			return $query->result_array();
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Get the latest poll
	 * 
	 * @access	public
	 * @return	mixed (array, boolean)
	 */
	public function get_latest_poll()
	{
		$this->db->select('poll_id, title, closed')->order_by('created', 'desc');
		$query = $this->db->get($this->polls_table, 1); // limit to 1
		
		if ($query->num_rows == 1)
		{
			return $query->row_array();
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Get the poll options for poll with id
	 * 
	 * @access	public
	 * @param	int
	 * @return	mixed (array, boolean)
	 */
	public function get_poll_options($poll_id)
	{
		$this->db->select('option_id, poll_id, title')->where('poll_id', $poll_id);
		$query = $this->db->get($this->options_table);
		
		if ($query->num_rows > 0)
		{
			return $query->result_array();
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Get the number of votes for option with id
	 * 
	 * @param	int
	 * @return	int
	 */
	public function get_options_votes($option_id)
	{
		$this->db->from($this->votes_table)
			->select('vote_id')
			->where('option_id', $option_id);
			
		return $this->db->count_all_results();
	}
	
	/**
	 * Build complete data structure
	 * 
	 * @access	public
	 * @param	int
	 * @return	boolean
	 */
	public function add_vote($option_id)
	{
		$this->db->insert($this->votes_table, array(
			'option_id' => $option_id,
			'ip_address' => $this->input->ip_address(),
			'timestamp' => time()
		));
		
		return ($this->db->affected_rows() == 1) ? TRUE : FALSE;
	}
	
	/**
	 * Create new poll option
	 * 
	 * @access	public
	 * @param	int
	 * @param	array
	 * @param	array
	 * @return	int
	 */
	public function create_poll($title, $options = array())
	{
		// check minimum and maximum poll options
		if (count($options) >= $this->config->item('max_poll_options', 'poll') && count($options) <= $this->config->item('min_poll_options', 'poll'))
		{
			return FALSE;
		}
		
		$poll_data = array(
			'title' => $title,
			'created' => date('Y-m-d h:i:s', time())
		);
		
		// create the poll then add the options associated with that inserted id
		$this->db->trans_start();
		$this->db->insert($this->polls_table, $poll_data);
		$poll_id = $this->db->insert_id();
		
		foreach ($options as $option)
		{
			$this->db->insert($this->options_table, array(
				'poll_id' => $poll_id,
				'title' => $option)
			);
		}
		
		$this->db->trans_complete();
		
		if ($this->db->trans_status() === FALSE)
		{
			log_message('Transaction failed in: models/poll_model on method: create_poll()');
		}
		
		return $poll_id;
	}
	
	/**
	 * Has previous voted in poll
	 * 
	 * @access	public
	 * @param	integer
	 * @return	boolean
	 */
	public function has_previously_voted($poll_id)
	{
		$this->db->from($this->options_table)
			->join($this->votes_table, 'votes.option_id = options.option_id')
			->where('options.poll_id', $poll_id)
			->where('votes.ip_address', $this->input->ip_address());
			
		return ($this->db->count_all_results() > 0) ? TRUE : FALSE;
		
	}
	
	/**
	 * Has previous voted in poll within time in seconds
	 * 
	 * @access	public
	 * @param	integer
	 * @param	integer
	 * @return	boolean
	 */
	public function has_previously_voted_within($interval, $poll_id)
	{
		$this->db->from($this->options_table)
			->join($this->votes_table, 'votes.option_id = options.option_id')
			->where('options.poll_id', $poll_id)
			->where('votes.timestamp >', time() - $interval)
			->where('votes.ip_address', $this->input->ip_address());
		
		return ($this->db->count_all_results() > 0) ? TRUE : FALSE;
	}
	
	/**
	 * Adds an option to a poll
	 * 
	 * @access	public
	 * @param	integer
	 * @param	string
	 * @return	boolean
	 */
	public function add_option($poll_id, $option_title)
	{
		$this->db->insert($this->options_table, array(
			'poll_id' => $poll_id,
			'title' => $option_title,
		));
		
		return ($this->db->affected_rows() == 1) ? TRUE : FALSE;
	}
	
	/**
	 * Deletes an option (and associated votes)
	 * 
	 * @access	public
	 * @param	integer
	 * @return	boolean
	 */
	public function delete_option($option_id)
	{
		$this->db->delete($this->options_table, array('option_id' => $option_id));
		return ($this->db->affected_rows() == 1) ? TRUE : FALSE;
	}
	
	/**
	 * Deletes a poll (and associated options + votes)
	 * 
	 * @access	public
	 * @param	integer
	 * @return	boolean
	 */
	public function delete_poll($poll_id)
	{
		$this->db->delete($this->polls_table, array('poll_id' => $poll_id));
		return ($this->db->affected_rows() == 1) ? TRUE : FALSE;
	}
	
	/**
	 * Close a poll
	 * 
	 * @access	public
	 * @param	integer
	 * @return	null
	 */
	public function close_poll($poll_id)
	{
		$this->db->set('closed', 1)
			->where('poll_id', $poll_id)
			->update($this->polls_table);
	}
	
	/**
	 * Open a poll
	 * 
	 * @access	public
	 * @param	integer
	 * @return	null
	 */
	public function open_poll($poll_id)
	{
		$this->db->set('closed', 0)
			->where('poll_id', $poll_id)
			->update($this->polls_table);
	}
	
	/**
	 * Check if the poll is closed or not
	 * 
	 * @access	public
	 * @param	integer
	 * @return	boolean
	 */
	public function is_closed($poll_id)
	{
		$query = $this->db->get_where($this->polls_table, array('poll_id' => $poll_id));
		$row = $query->row_array();
		return (bool)$row['closed'];
	}
}