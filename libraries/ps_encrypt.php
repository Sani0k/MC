<?php

/**
 * MobileCMS
 *
 * Open source content management system for mobile sites
 *
 * @author MobileCMS Team <support@mobilecms.ru>
 * @copyright Copyright (c) 2011, MobileCMS Team
 * @link http://wmaze.ru Official site
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
 

/**
 * PHPSense Encryption Class
 *
 * PHP tutorials and scripts
 *
 * @package  	PHPSense
 * @author		Jatinder Singh Thind
 * @copyright	Copyright (c) 2012, Jatinder Singh Thind
 * @link		http://www.phpsense.com
 */

// ------------------------------------------------------------------------
class PS_Encrypt {

	private $key = '';
	private $cipher = MCRYPT_RIJNDAEL_256;
	private $cipher_mode = MCRYPT_MODE_CBC;

	public function __construct() {
		if(!function_exists('mcrypt_encrypt')) {
			throw new Exception('mcrypt library not installed.');
		}
	}

	/**
	 * Set the encrypton key with wich the data is encrypted. Set this once only.
	 *
	 * @access public
	 * @return void
	 */
	public function setKey($key) {
		$this->key = hash('sha256', $key, TRUE);;
	}

	/**
	 * Encrypt data.
	 *
	 * @data Data to encrypt
	 * @access public
	 * @return string
	 */
	public function encrypt($data) {
		$init_size = mcrypt_get_iv_size($this->cipher, $this->cipher_mode);
		$init_vect = mcrypt_create_iv($init_size, MCRYPT_RAND);
		return $this->randomString(strlen($this->key)).$init_vect.mcrypt_encrypt($this->cipher, $this->key, $data, $this->cipher_mode, $init_vect);
	}

	/**
	 * Descrypt data
	 *
	 * @data Data to decrypt
	 * @access public
	 * @return string
	 */
	public function decrypt($data) {
		$data = substr($data, strlen($this->key));
		$init_size = mcrypt_get_iv_size($this->cipher, $this->cipher_mode);
		$init_vect = substr($data, 0, $init_size);
		$data = substr($data, $init_size);
		return mcrypt_decrypt($this->cipher, $this->key, $data, $this->cipher_mode, $init_vect);
	}

	/**
	 * Generate random string
	 *
	 * @len Length of string to generate
	 * @access private
	 * @return string
	 */
	private function randomString($len) {
		$str = '';
		$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$pool_len = strlen($pool);
		for ($i = 0; $i < $len; $i++) {
			$str .= substr($pool, mt_rand(0, $pool_len - 1), 1);
		}
		return $str;
	}
}


	//Include the PS_Encrypt class
	//include('ps_encrypt.php');

	/*
	* Create a PS_Encrypt object
	*
	*/
	//$encrypt = new PS_Encrypt();

	/*
	* Set encryption key
	*/
	//$encrypt->setKey('q%6WXdXnv&amp;%g');

	/*
	* Encrypt data
	*/
	//$encrypted_data = $encrypt->encrypt('secret_password');
//
	/*
	* Descrypt data
	*
	*/
	//$decrypted_data = $encrypt->decrypt($encrypted_data);
	//echo $decrypted_data; //Will output "secret_password"
