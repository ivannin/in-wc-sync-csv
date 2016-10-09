<?php
/**
 * Класс расширяет класс параметров и добавляет интерфейс в админке
 */
class INWCSYNC_Plugin_Settings extends INWCSYNC_Settings
{
	/**
	 * Экземпляр класса плагина, нужен, чтобы вызывать методы плагина
	 * @var INWCSYNC_Plugin
	 */
	protected $plugin;
		
	/**
	 * Конструктор
	 * инициализирует параметры, загружает данные и формирует страницу параметров
	 * @param string 			optionName		Название опции в Wordpress, по умолчанию используется имя класса
	 * @param INWCSYNC_Plugin 	plugin			Экземпляр класса плагина
	 */
	public function __construct( $optionName = '', $plugin )
	{
		parent::__construct( $optionName );
		
		$this->plugin = $plugin;
		
		// Работа в админке
		if ( is_admin() )
		{
			// Страница параметров
			add_action( 'admin_menu', array( $this, 'addSettingsPage' ) );
			
			// Загрузка CSS плагина
			wp_enqueue_style( INWCSYNC_TEXT_DOMAIN, INWCSYNC_URL . 'admin.css');
			
			// Загрузка jQuery UI в админку
			global $wp_scripts;
			
			// load jQuery UI tabs
			wp_enqueue_script('jquery-ui-tabs');
			
			// get registered script object for jquery-ui
			$ui = $wp_scripts->query('jquery-ui-core');
			
			// load the Smoothness theme from CDN
			$protocol = is_ssl() ? 'https' : 'http';
			$url = "$protocol://code.jquery.com/ui/{$ui->ver}/themes/smoothness/jquery-ui.css";
			wp_enqueue_style('jquery-ui-smoothness', $url, array( 'jquery-ui-style' ), null);
		}
	}
	
	/**
	 * Добавляет страницу параметров
	 */
	public function addSettingsPage()
	{
		add_options_page(
			__( 'IN Woocommerce CSV Sync', 	INWCSYNC_TEXT_DOMAIN ), // page_title
			__( 'Woocommerce CSV Sync', 	INWCSYNC_TEXT_DOMAIN ), // menu_title
			'manage_options',										// capability
			INWCSYNC_TEXT_DOMAIN,									// menu_slug - совпадает с текстовым доменом
			array( $this, 'renderSettingsPage')						// function
		);		
	}	

	/**
	 * Сохранение параметра сиспользованием sanitize_text_field
	 * @param string	$param		Название параметра
	 * @param mixed 	$value		Значение параметра
	 */
	public function set( $param, $value )
	{
		$this->_params[ $param ] = sanitize_text_field( $value );
	}
	
	
	/**
	 * Формирует страницу параметров
	 */
	public function renderSettingsPage()
	{
		// Сохранение параметров при передаче формы
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' )
		{
			// Проверяем nonce
			check_admin_referer( get_class( $this ) );
			// Проверяем права
			if ( ! current_user_can( 'manage_options' ) )
				wp_die( __( 'You have no permissions to do this!', 	INWCSYNC_TEXT_DOMAIN ) );
			
			// Сохраняем данные из POST
			$this->set('folder', (isset( $_POST['inwcsync_folder'] )) ? $_POST['inwcsync_folder'] : '' );
			$this->set('csv_file', (isset( $_POST['inwcsync_csv_file'] )) ? $_POST['inwcsync_csv_file'] : '' );
			$this->set('locale', (isset( $_POST['inwcsync_locale'] )) ? $_POST['inwcsync_locale'] : '' );
			$this->set('encoding', (isset( $_POST['inwcsync_encoding'] )) ? $_POST['inwcsync_encoding'] : '' );
			$this->set('delimiter', (isset( $_POST['inwcsync_delimiter'] )) ? wp_unslash( $_POST['inwcsync_delimiter']) : '' );
			$this->set('skip1stline', (isset( $_POST['inwcsync_skip1line'] )) ? $_POST['inwcsync_skip1line'] : '0' );
			$this->save();
			
			// Если запуск, запускаем плагин
			if ( isset( $_POST['run_now'] ))
				$this->plugin->run();
			
			
		}
			
		$inwcsync_folder 	= $this->get('folder', $_SERVER['DOCUMENT_ROOT'] . '/wp-content/uploads/' . INWCSYNC_TEXT_DOMAIN . '/');
		$inwcsync_csv_file 	= $this->get('csv_file', 'efdata.csv');
		$inwcsync_locale 	= $this->get('locale', 'ru_RU.cp1251');
		$inwcsync_encoding 	= $this->get('encoding', 'CP1251');
		$inwcsync_delimiter	= $this->get('delimiter', ';');
		$inwcsync_skip1line	= $this->get('skip1stline', '0');
?>
	<h1><?php esc_html_e( 'IN Woocommerce CSV Sync', INWCSYNC_TEXT_DOMAIN ) ?></h1>
	<form id="in-wc-sync-csv" action="<?php echo $_SERVER['REQUEST_URI']?>" method="post">
		<?php wp_nonce_field( get_class( $this ) ) ?>
		<script>
			jQuery( function($) 
			{
				$( "#tabs" ).tabs();
			});
		</script>
		<div id="tabs">
			<ul>
				<li><a href="#common"><?php esc_html_e( 'Common', INWCSYNC_TEXT_DOMAIN ) ?></a></li>
				<li><a href="#schedule"><?php esc_html_e( 'Schedule', INWCSYNC_TEXT_DOMAIN ) ?></a></li>
				<?php if ( WP_DEBUG ): ?>
				<li><a href="#log"><?php esc_html_e( 'Log', INWCSYNC_TEXT_DOMAIN ) ?></a></li>
				<?php endif ?>				
			</ul>
			<fieldset id="common">
				<table>
					<tr>
						<td><label for="inwcsync_folder"><?php esc_html_e( 'Data Folder', INWCSYNC_TEXT_DOMAIN ) ?></label></td>
						<td><input id="inwcsync_folder" type="text" name="inwcsync_folder" value="<?php echo $inwcsync_folder ?>" placeholder="<?php esc_html_e( 'The folder should be writable', INWCSYNC_TEXT_DOMAIN ) ?>" /></td>
					</tr>
					<tr>
						<td><label for="inwcsync_csv_file"><?php esc_html_e( 'Data CSV File', INWCSYNC_TEXT_DOMAIN ) ?></label></td>
						<td><input id="inwcsync_csv_file" type="text" name="inwcsync_csv_file" value="<?php echo $inwcsync_csv_file ?>" placeholder="<?php esc_html_e( 'The CSV file with data', INWCSYNC_TEXT_DOMAIN ) ?>" /></td>
					</tr>					
					<tr>
						<td><label for="inwcsync_locale"><?php esc_html_e( 'Locale for reading file', INWCSYNC_TEXT_DOMAIN ) ?></label></td>
						<td><input id="inwcsync_locale" type="text" name="inwcsync_locale" value="<?php echo $inwcsync_locale ?>" placeholder="<?php esc_html_e( 'Locale for reading CSV', INWCSYNC_TEXT_DOMAIN ) ?>" /></td>
					</tr>
					<tr>
						<td><label for="inwcsync_encoding"><?php esc_html_e( 'Encoding of CSV file', INWCSYNC_TEXT_DOMAIN ) ?></label></td>
						<td><input id="inwcsync_encoding" type="text" name="inwcsync_encoding" value="<?php echo $inwcsync_encoding ?>" placeholder="<?php esc_html_e( 'Encoding of CSV file', INWCSYNC_TEXT_DOMAIN ) ?>" /></td>
					</tr>
					<tr>
						<td><label for="inwcsync_delimiter"><?php esc_html_e( 'Fields Delimeter in CSV', INWCSYNC_TEXT_DOMAIN ) ?></label></td>
						<td><input id="inwcsync_delimiter" type="text" name="inwcsync_delimiter" value="<?php echo $inwcsync_delimiter ?>" placeholder="<?php esc_html_e( 'Only One Char', INWCSYNC_TEXT_DOMAIN ) ?>" /></td>
					</tr>
					<tr>
						<td><label for="inwcsync_skip1line"><?php esc_html_e( 'Skip the 1st line', INWCSYNC_TEXT_DOMAIN ) ?></label></td>
						<td>
							<input id="inwcsync_skip1line" type="checkbox" name="inwcsync_skip1line" value="1" <?php checked($inwcsync_skip1line, '1', 'checked') ?> />
							<span><?php esc_html_e( 'If the first line contains headers, check this option', INWCSYNC_TEXT_DOMAIN ) ?></span>
						</td>
					</tr>					
				</table>
			</fieldset>
			<fieldset id="schedule">
				<h2><?php esc_html_e( 'Run immediately', INWCSYNC_TEXT_DOMAIN ) ?></h2>
				<?php submit_button( __('Run now!', INWCSYNC_TEXT_DOMAIN), 'primary', 'run_now' ) ?>
			</fieldset>
			<?php if ( WP_DEBUG ): ?>
			<fieldset id="log">
				<h2><?php esc_html_e( 'The Last Run Log', INWCSYNC_TEXT_DOMAIN ) ?></h2>
				<pre><?php 
					$logFile = $inwcsync_folder . $this->get( 'log', INWCSYNC_TEXT_DOMAIN . '.log' );
					if ( file_exists ( $logFile ) )
						esc_html_e( file_get_contents( $logFile ) ); 
				?></pre>
			</fieldset>			
			<?php endif ?>
		</div>
		<?php submit_button() ?>
	</form>
<?php	
	
	}

	
}