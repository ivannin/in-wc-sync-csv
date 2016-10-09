<?php
/**
 *  Класс реализует запись в лог
 *  Класс сингелтон, чтобы он всегда работал с одним экземплятом
 */
class INWCSYNC_Log
{
		
	/**
	 * Статическая функция пишет в лог
	 * @static	 
	 * @param string	$str	Строка текста	
	 */		
	static public function write( $str ) 
	{
		$logger = self::getInstance();
		$logger->writeStr( $str );
	}		
		
		
	/**
	 * Экземпляр класса
	 * @var INWCSYNC_Log
	 * @static
	 */ 
	private static $_instance = null;
	
	/**
	 * Возвращает экземпляр класса
	 * @static	 
	 */		
	static public function getInstance() 
	{
		if( is_null( self::$_instance ) )
		{
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Имя файла лога
	 * @var string
	 */ 
	protected $fileName;	
	
	
	/**
	 * Конструктор класса
	 * приватный конструктор ограничивает реализацию getInstance()	 
	 */	
	private function __construct() 
	{
		// Формируем имя файла
		$settings = new INWCSYNC_Settings();
		$this->fileName = 
			$settings->get('folder', $_SERVER['DOCUMENT_ROOT'] . '/wp-content/uploads/' . INWCSYNC_TEXT_DOMAIN . '/') . 
			INWCSYNC_TEXT_DOMAIN . '.log';
		// Пишем в файл дату и время
		file_put_contents( $this->fileName, date( 'd.m.Y H:i:s') . PHP_EOL . PHP_EOL );
	}

	/**
	 * Функция записи в файл строки
	 * приватная, чтобы ее вызывал только сам класс
	 * @param string	$str	Строка текста	 
	 */	
	private function writeStr( $str ) 
	{
		file_put_contents( $this->fileName, $str . PHP_EOL, FILE_APPEND );
	}
	
}