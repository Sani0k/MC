<?php
/**
 * MobileCMS
 *
 * Open source content management system for mobile sites
 *
 * @author MobileCMS Team <support@wmaze.ru>
 * @copyright Copyright (c) 2013, MobileCMS Team
 * @link http://wmaze.ru No Official site
 * @license http:#opensource.org/licenses/gpl-license.php GNU Public License
 */

/**
 * Класc бб кодов
 */
class bbcodes {

  var $bbcode_uid = '';

  /**
	* BBcode вывод на дысплей
	*/
	public static function a_outup($str){
	
	       $str = self::highlight_url($str);
	       $str = self::highlight_code($str);
           $str = bbcodes::bb_text(stripslashes($str));   
                                    
        return $str;	
        }

	/**
	* BBcode замена
	*/
	public static function bb_text($markup){
        # Список bb кодов
        $preg = array(
            '#\[b](.+?)\[/b]#is'          =>   '<span style="font-weight: bold">$1</span>',                                        # Жирный
            '#\[i](.+?)\[/i]#is'          =>   '<span style="font-style:italic">$1</span>',                                        # Курсив
            '#\[u](.+?)\[/u]#is'          =>   '<span style="text-decoration:underline">$1</span>',                                # Подчеркнутый
            '#\[s](.+?)\[/s]#is'          =>   '<span style="text-decoration:line-through">$1</span>',                             # Зачеркнутый
            '#\[small](.+?)\[/small]#is'  =>   '<span style="font-size:x-small">$1</span>',                                        # Маленький шрифт
            '#\[big](.+?)\[/big]#is'      =>   '<span style="font-size:large">$1</span>',                                          # Большой шрифт
            '#\[red](.+?)\[/red]#is'      =>   '<span style="color:red">$1</span>',                                                # Красный
            '#\[green](.+?)\[/green]#is'  =>   '<span style="color:green">$1</span>',                                              # Зеленый
            '#\[blue](.+?)\[/blue]#is'    =>   '<span style="color:blue">$1</span>',                                               # Синий
            '!\[color=(#[0-9a-f]{3}|#[0-9a-f]{6}|[a-z\-]+)](.+?)\[/color]!is' => '<span style="color:$1">$2</span>',               # Цвет шрифта
            '!\[bg=(#[0-9a-f]{3}|#[0-9a-f]{6}|[a-z\-]+)](.+?)\[/bg]!is'       => '<span style="background-color:$1">$2</span>',    # Цвет фона
            '#\[q](.+?)\[/q]#is'          =>  '<span class="q" style="display:block">$1</span>'                                    # Цитата
        );
    
        return preg_replace(array_keys($preg), array_values($preg), $markup);
    }
	     



     /**
     * Подсветка php кода
     *
     */
     public static function highlight_code($var){
     
        if (!function_exists('process_code')) {
            function process_code($php)
            {
                $php = html_entity_decode(trim($php), ENT_QUOTES, 'UTF-8');
                $php = substr($php, 0, 2) != "<?" ? "<?php\n" . $php . "\n" : $php;
                $php = highlight_string(stripslashes($php), true);
                $php = strtr($php, array('&#92;' => '&#92;', ':' => '&#58;', '[' => '&#91;', '&nbsp;' => ' '));
                return '<div class="php">' . $php . '</div>';
            }
        }
        return preg_replace(array('#\[php\](.+?)\[\/php\]#se'), array("''.process_code('$1').''"), str_replace("]\n", "]", $var));
    }
	

	
    /**
    * Функция Url 
    *
    */
    public static function highlight_url($var){
    
	     if (!function_exists('parser_link')) {       
           function parser_link($url){
		     		   			
        
		             if(!$url[3]){
		       
		                  return '<a href="'.$url[1].'">'. ((mb_strlen($url[2]) > 55) ? mb_substr($url[2], 0 , 39).' … '.mb_substr($url[2], -10) : $url[2]).'</a>';
		            
			          } else { 
			        
			              return '<a href="'.$url[3].'">'.((mb_strlen($url[4]) > 55) ? mb_substr($url[4], 0 , 39).' … '.mb_substr($url[4], -10) : $url[4]).'</a>';
			        
			           }
                   } 
                }
           # Замена бб кодa
           return preg_replace_callback("~\\[url=(https?://.+?)\\](.+?)\\[/url\\]|(https?://([a-z0-9\.\/\-\#\?\_\!\=&;]*))~", 'parser_link', $var);
	
	}
    
    
    /**
	* Замена символов на валидные в тексте
	*/
	public static function specialchars($text) {
	
		$str_from = array('<', '>', '[', ']', '.', ':', '&');
		$str_to = array('&lt;', '&gt;', '&#91;', '&#93;', '&#46;', '&#58;','&amp;');

		return str_replace($str_from, $str_to, $text);
	}
	

	/**
    * Убераем теги
    *
    */
    public static function  replance_tag($var){
        $rep = array(
            '[b]'     =>   '', 
            '[/b]'    =>   '',                                      
            '[q]'     =>   '',
            '[/q]'    =>  '',
            '[i]'     =>   '',
            '[/i]'    =>   '',                                        
            '[u]'     =>   '',
            '[/u]'    =>   '',                               
            '[s]'     =>   '',
            '[/s]'    =>   '',                            
            '[small]' =>   '',
            '[/small]'=>   '',                                      
            '[big]'   =>   '',
            '[/big]'  =>   '',
            '[red]'   =>   '',
            '[/red]'  =>   '',                                         
            '[green]' =>   '',
            '[/green]'=>   '',                                           
            '[blue]'  =>   '',
            '[/blue]' =>   '',  
            '[php]'   =>   '', 
            '[/php]'  =>   '',                                                                          
        );
        
        $var = preg_replace('#\[color=(.+?)\](.+?)\[/color]#si', '$2', $var);
        $var = preg_replace('!\[bg=(#[0-9a-f]{3}|#[0-9a-f]{6}|[a-z\-]+)](.+?)\[/bg]!is', '$2', $var);
        
        return strtr($var, $rep);
    }
    
    /**
    * Маскировка ссылок в тексте
    *
    */
    public static function  maska_link($url){
        $url = preg_replace('~\\[url=(https?:#.+?)\\](.+?)\\[/url\\]|(https?:#(www.)?[0-9a-z\.-]+\.[0-9a-z]{2,6}[0-9a-zA-Z/\?\.\~&amp;_=/%-:#]*)~', '###', $url);
        $repl = array(
            '.net'     => '**',
            '.org'     => '**',
            '.info'    => '**',
            '.mobi'    => '**',
            '.ru'      => '**',
            '.com'     => '**',
            '.biz'     => '**',
            '.cn'      => '**',
            '.in'      => '**',
            '.wen'     => '**',
            '.kmx'     => '**',
            '.h2m'     => '**'
        );
        return strtr($var, $repl);
    }
    
	
    

	

	
	
} 

# bb codes lib
