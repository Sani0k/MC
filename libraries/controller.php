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
 * Главный контроллер
 */
abstract class Controller {
    /**
     * Переменная, в которую записываем ошибки при валидации форм
     */
    public $error = false;
    /**
     * Класс шаблонизатора
     */
    public $tpl;
    /**
     * Тема
     */
    protected $template_theme = 'default';
    /**
     * Количество элементов на страницу по умолчанию
     */
    protected $per_page = 7;
    


    /**
     * Constructor
     */
    public function __construct() {
  	$this->config = Registry::get('config');
		$this->db = Registry::get('db');

		// Определение старта для пагинации
		$this->start = !empty($_GET['start']) ? intval($_GET['start']) : 0;
		$this->start = is_numeric($_GET['page']) ? $_GET['page'] * $this->per_page - 1 : $this->start;
		if ($this->start < 0) a_error('Не верный формат данных');
        
		// Подключение шаблонизатора
		a_import('libraries/template');
		$this->tpl = new Template;
		
		// Добавляем объект шаблона в Registry
		Registry::set('tpl', $this->tpl);
        
		// Подключение кеширования
		if ( ! class_exists('File_Cache')) a_import('libraries/file_cache');
		$this->cache = new File_Cache(ROOT .'cache/file_cache');
		
		// Подключение класса bb-кодов
		a_import('libraries/bb_codes');
		
        
		// Добавление мета данных на страницу
		define('DESCRIPTION', $this->config['system']['description']);
		define('KEYWORDS', $this->config['system']['keywords']);

		// Получение данных о польльзователе
		if ( ! empty($_SESSION['check_user_id'])) $user_id = $_SESSION['check_user_id'];
		elseif ( ! empty($_SESSION['user_id'])) $user_id = $_SESSION['user_id'];
		else $user_id = -1;
		
        
        /**
         * Авторизация пользователя
         */ 
         
        //$username = FALSE;
        //$user_hash = FALSE;
		
        if ($user_id == -1 && isset($_COOKIE['username']) && isset($_COOKIE['user_hash'])) {
             // Авторизация по COOKIE
         $username  = base64_decode($_COOKIE['username']);   
         $user_hash = base64_decode($_COOKIE['user_hash']);
         $user_id = base64_decode($_COOKIE['user_id']);

        }
            
		if ($username && $user_hash) {
        if ($try_user_id = $this->db->get_one("SELECT user_id FROM #__users WHERE username = '". $username ."' AND user_hash = '". $user_hash ."'")) {
                $user_id = $try_user_id;
               $_SESSION['user_id'] = $user_id;
             }
             
         }
         
        
        
         
		// Получаем UserAgent
        if (isset($_SERVER["HTTP_X_OPERAMINI_PHONE_UA"]) && strlen(trim($_SERVER['HTTP_X_OPERAMINI_PHONE_UA'])) > 5) {
            $user_agent = 'Opera Mini: ' . htmlspecialchars(mb_substr(trim($_SERVER['HTTP_X_OPERAMINI_PHONE_UA']), 0, 150));
        } elseif (isset($_SERVER['HTTP_USER_AGENT'])) {
            $user_agent = htmlspecialchars(mb_substr(trim($_SERVER['HTTP_USER_AGENT']), 0, 150));
        } else {
            $user_agent = 'Not Recognised';
        }
        
        
		// Добавление гостей в бд
		if ($user_id == -1) { 
			// Проверяем наличие гостя в списке
			if ($guest = $this->db->get_row("SELECT id FROM #__guests WHERE ip = '". real_ip() ."' AND user_agent = '". $user_agent ."'")) {
				// Обновляем дату последнего посещения
				$this->db->query("UPDATE #__guests SET
					last_time = UNIX_TIMESTAMP()
					WHERE id = '". intval($guest['id']) ."'
				");
			    // Добавляем нового гостя в список
			} else {
				// Записываем гостя в базу
				$this->db->query("INSERT INTO #__guests SET
					ip = '". real_ip() ."',
					user_agent = '". $user_agent ."',
					last_time = UNIX_TIMESTAMP()
				");     
			}
		}
        
		$this->user = $this->db->get_row("SELECT * FROM #__users LEFT JOIN #__users_profiles USING(user_id) WHERE user_id = $user_id");
        
		define('USER_ID', $this->user['user_id']);
		$this->tpl->assign('user', $this->user);
        
		// Обновляем время последнего посещения
		if (USER_ID != -1) $this->db->query("UPDATE #__users SET last_visit = UNIX_TIMESTAMP(),  ip = '". real_ip() ."', user_agent = '". $user_agent ."' WHERE user_id = '". USER_ID ."'");
        
		// Подключения помощника пользователей
		a_import('modules/user/helpers/user');
			

        
		// Управление правами доступа
		$this->access = a_load_class('libraries/access');

		if ($this->user) $access_level = $this->access->get_level($this->user['status']);
		else $access_level = 1;

		define('ACCESS_LEVEL', $access_level);

		// Выполнение событий до вызова контроллера
		main::events_exec(&$this->db, 'pre_controller');

		if (ACCESS_LEVEL < $this->access_level) {
			if (USER_ID == -1) {
				header('Location: '. a_url('user/login', 'from='. urlencode($_SERVER["REQUEST_URI"]) , true));
				exit;
			}
			else {
				a_error('У вас нет доступа к данной странице!');
			}
		}
          
           // Закрытие сайта на тех работы
          if ( $this->config['system']['site_cloused'] == 2 && ACCESS_LEVEL < 10 ) { // доступ только админу
          header('Location: '. URL.'cloused.php');
		  exit;
          } 
        
          if($this->config['system']['site_cloused'] == 1 && ACCESS_LEVEL < 8 ){ //доступ администрации
          header('Location: '. URL.'cloused.php');
		  exit;	
          } 
          
		// Получение темы оформления, для админки
		if ($this->template_theme == 'admin') {
			$this->tpl->theme = $this->config['system']['admin_theme'];
		}
		// Использование Web темы
		elseif (WEB_VERSION == '1') {            
			$this->tpl->theme = $this->config['system']['web_theme'];
		}
		else {
			$this->tpl->theme = $this->config['system']['default_theme'];
		}
        
		define('THEME', $this->tpl->theme);

		// Проверка модерации пользователя
		if (defined('MODERATE')) a_error(MODERATE);
		// Проверка бана юзера
		if (defined('BAN_USER')) a_error(BAN_USER);
	}
}

