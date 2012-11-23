<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Poll extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->library('poll_lib');
		$this->load->library('form_validation');
		$this->load->library('session');
		
		$this->load->helper('url');
		$this->load->helper('form');
		$this->load->helper('html');
		
		$this->form_validation->set_error_delimiters('<dd class="error">', '</dd>');
		// $this->output->enable_profiler(TRUE);
	}
	
	// List latest polls
	// ----------------------------------------------------------------------
	public function index()
	{
		$data['title'] = 'Polls';
		$data['base_styles'] = 'res/css/base.css';
		
		$config['base_url'] = site_url('poll/page');
		$config['total_rows'] = $this->poll_lib->num_polls();
		$config['per_page'] = 10;
		$this->load->library('pagination');
		$this->pagination->initialize($config);
		
		$data['results'] = $this->poll_lib->all_polls($config['per_page'], $this->uri->segment(3));
		$data['paging_links'] = $this->pagination->create_links();
		
		$this->load->view('polls/index', $data);
	}
	
	// Create new poll
	// ----------------------------------------------------------------------
	public function create()
	{
		$data['title'] = 'Create a new poll';
		$data['base_styles'] = 'res/css/base.css';
		$data['min_options'] = $this->config->item('min_poll_options', 'poll');
		$data['max_options'] = $this->config->item('max_poll_options', 'poll');
		
		$this->form_validation->set_rules('title', 'title', 'required');
		$this->form_validation->set_rules('options[]', 'options', 'poll_options_required');
		$this->form_validation->set_error_delimiters('<li>', '</li>');
		
		if ($this->input->is_ajax_request())
		{
			if ($this->form_validation->run() == FALSE)
			{
				echo json_encode(array('fail' => TRUE, 'error_messages' => validation_errors()));
			}
			else
			{
				$this->poll_lib->create_poll($this->input->post('title'), $this->input->post('options'));
				echo json_encode(array('fail' => FALSE));
			}
		}
		else
		{
			$this->load->view('polls/create', $data);
		}
	}
	
	// Add vote on option to poll
	// ----------------------------------------------------------------------
	public function vote($poll_id, $option_id)
	{
		if ( ! $this->poll_lib->vote($poll_id, $option_id))
		{
			$data['base_styles'] = 'res/css/base.css';
			$data['title'] = 'Sorry an error occured';
			$data['error_message'] = $this->poll_lib->get_errors();
			$this->load->view('polls/error', $data);
		}
		else
		{
			redirect('', 'refresh');
		}
	}
	
	// View poll
	// ----------------------------------------------------------------------
	public function view($poll_id)
	{
		$data['title'] = 'Polls';
		$data['row'] = $this->poll_lib->single_poll($poll_id);
		$data['base_styles'] = 'res/css/base.css';

		$this->load->view('polls/view', $data);
	}
	
	// Delete poll
	// ----------------------------------------------------------------------
	public function delete($poll_id)
	{
		if ($this->poll_lib->delete_poll($poll_id))
		{
			redirect('', 'refresh');
		}
	}
	
	// Open closed poll
	// ----------------------------------------------------------------------
	public function open($poll_id)
	{
		$this->poll_lib->open_poll($poll_id);
		redirect('', 'refresh');
	}
	
	// Close opened poll
	// ----------------------------------------------------------------------
	public function close($poll_id)
	{
		$this->poll_lib->close_poll($poll_id);
		redirect('', 'refresh');
	}
	
	// View datastructure
	// ----------------------------------------------------------------------
	public function data()
	{
		echo '<h2>Single poll (note: needs a valid id): </h2>';
		echo '<pre>';
		print_r($this->poll_lib->single_poll(4));
		echo '</pre>';
		
		echo '<h2>Latest poll: </h2>';
		echo '<pre>';
		print_r($this->poll_lib->single_poll()); // note no value passed in: so returns latest poll
		echo '</pre>';
		
		echo '<h2>Multiple polls: </h2>';
		echo '<pre>';
		print_r($this->poll_lib->all_polls(10, 0));
		echo '</pre>';
	}
}

/* End of file poll.php */
/* Location: ./application/controllers/poll.php */