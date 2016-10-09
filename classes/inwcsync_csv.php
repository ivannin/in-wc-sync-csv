<?php
/**
 * Класс работы с CSV файлом
 */
class INWCSYNC_CSV
{
	/**
	 * Указатель на открытый файл CSV
	 * @var resource
	 */
	protected $file;
	
	/**
	 * Размер буфера чтения. Должен быть больше самой длинной строки (в символах), найденной в CSV-файле (включая завершающий символ конца строки)
	 * @var string
	 */
	public $length = 4096;	
	
	/**
	 * Параметр delimiter устанавливает разделитель поля (только один символ)
	 * @var string
	 */
	public $delimiter = ',';	
	
	/**
	 * Параметр enclosure устанавливает символ ограничителя поля (только один символ)
	 * @var string
	 */
	public $enclosure = '"';	
	
	/**
	 * Параметр escape устанавливает экранирующий символ (только один символ)
	 * @var string
	 */
	public $escape = '\\';
	
	/**
	 * Локаль для чтения CSV. Нужна поскольку файлы в однобайтовой кодировке могут читаться неправильно!
	 * @var string
	 */
	public $locale = 'ru_RU.cp1251';

	/**
	 * Кодировка файла CSV
	 * @var string
	 */
	public $encoding = 'CP1251';	
	
	/**
	 * Пропуск первой строки
	 * @var bool
	 */
	public $skipHeaders = false;	
	
		
	/**
	 * Конструктор
	 * Открывает файл
	 * @param	$fileName	Полное имя файла для чтения
	 */
	public function __construct( $fileName )
	{
		// Проверяем наличие файла
		if (! file_exists( $fileName ))
			throw new Exception( __( 'CSV file not found: ' . $fileName, INWCSYNC_TEXT_DOMAIN ) );
		
		// Открываем файл
		$this->file = fopen( $fileName, 'r' );
		if ( $this->file === false )
			throw new Exception( __( 'Error open CSV file: ' . $fileName, INWCSYNC_TEXT_DOMAIN ) );
	}
	
	/**
	 * Счетчик прочитанных строк
	 * @var string
	 */
	public $lineCount = 0;	
	
	/**
	 * Чтение CSV файла
	 * @param mixed	$callback	Функция, которая вызывается для каждой прочитанной строки
	 * 							Вызов метода объекта array( $obj, 'метод')
	 * 							Передается параметр - прочитанная строка
	 */
	public function read( $callback )
	{
		// Устанавливаем локаль
		$currentLocale = locale_get_default();
		setlocale(LC_ALL, $this->locale);

		// Читаем файл
		while( ( $data = fgetcsv( 
							$this->file, 
							$this->length, 
							$this->delimiter,
							$this->enclosure,
							$this->escape) ) !== FALSE )
		{
			// Считаем строки
			$this->lineCount++;
			// Если указано, пропускаем заголовки
			if ($this->skipHeaders && $this->lineCount == 1)
				continue;
			
				
			// Перекодировка данных
			$data = array_map( array( $this, 'convert' ), $data );
			
			// Вызов обработчика данных
			call_user_func( $callback, $data );
		}
		
		// Восстанавливаем локаль
		setlocale(LC_ALL, $currentLocale);
		
	}	
	
	/**
     * Конвертация кодировки строки
	 * @param string $value 	Исходная строка
	 */
	public function convert( $value ) 
	{
		if ( $this->encoding == 'UTF-8')
			return $value;
		else
			return iconv( $this->encoding, 'UTF-8', $value );
	}	
	
	
	
	/**
	 * Деструктор
	 * Закрывает файл и освобождает ресурсы
	 */
	public function __destruct()
	{
		if ( ! empty( $this->file ))
			fclose( $this->file );
	}	
}