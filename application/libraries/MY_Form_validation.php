<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Form_validation extends CI_Form_validation {
	
	public function __construct($config = array())
	{
		parent::__construct($config);
	}
	
	/**
	 * Poll options required
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function poll_options_required($str)
	{
		if ( ! is_array($str))
		{
			return (trim($str) == '') ? FALSE : TRUE;
		}
		else
		{
			return ( ! empty($str));
		}
	}
}

/* End of file MY_Form_validation.php */
/* Location: ./system/application/libraries/MY_Form_validation.php */