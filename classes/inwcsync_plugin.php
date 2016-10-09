<?php
/**
 * Основной класс плагина
 */
class INWCSYNC_Plugin
{
	/**
	 * Параметры плагина
	 * @var INWCSYNC_Settings
	 */  	 
	public $settings;
	
	/**
	 * Полное имя CSV файла
	 * @var string
	 */  	 
	protected $csvFileName;	
		
	/**
	 * Конструктор
	 * реализует инициализацию плагина
	 */
	public function __construct()
	{
		// Параметры плагина. Название параметров в БД WordPress - имя текстового домена, то есть имя плагина
		$this->settings = new INWCSYNC_Plugin_Settings( INWCSYNC_TEXT_DOMAIN, $this );
		
		// Читаем имя файла из параметров
		$this->csvFileName = 
			$this->settings->get( 'folder', 	$_SERVER['DOCUMENT_ROOT'] . '/wp-content/uploads/in-wc-sync-csv/' ) . 
			$this->settings->get( 'csv_file', 		'test.csv' );
	}
	
	/**
	 * Работа плагина
	 */
	public function run()
	{
		INWCSYNC_Log::write( __( 'INWCSYNC_Plugin::Run', INWCSYNC_TEXT_DOMAIN ) );
		
		// Читаем файл
		$this->readCSV();
	}	
	
	/**
	 * Чтение CSV файла
	 */
	public function readCSV()
	{
		INWCSYNC_Log::write( __( 'INWCSYNC_Plugin::readCSV: ', INWCSYNC_TEXT_DOMAIN ) . $this->csvFileName );
		
		// Пытаемся прочитать файл
		try 
		{
			// Открываем файл
			$csv = new INWCSYNC_CSV( $this->csvFileName );
			// Настраиваем параметры CSV под формат Excel
			$csv->locale = $this->settings->get('locale', 'ru_RU.cp1251');
			$csv->encoding = $this->settings->get('encoding', 'CP1251');
			$csv->delimiter = $this->settings->get('delimiter', ';');
			$csv->skipHeaders = $this->settings->get('skip1stline', false);
			// Читаем файл
			$csv->read( array( $this, 'doRow' ) );		
		} 
		catch (Exception $e) 
		{
			INWCSYNC_Log::write( __( 'ERROR: ', INWCSYNC_TEXT_DOMAIN ) . $e->getMessage() );
		}		
	}
	
	/**
	 * Обработка одной прочитанной строки данных
	 * @param mixed	$data	Индексный массив с данными
	 */
	public function doRow( $data=null )
	{
		INWCSYNC_Log::write( __( 'INWCSYNC_Plugin::doRow: ', INWCSYNC_TEXT_DOMAIN ) . var_export( $data, true ) );
	}	
}