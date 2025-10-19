<?php 
//========================================================================//
//	AUTOR  : JUAN CARLOS PINTO LARICO
//	FECHA  : MAYO  2018
//	VERSION: 2.0.0.0
//	E-MAIL : jcpintol@hotmail.com
//  hoja de seccion personalizada para su busqueda
//========================================================================//
class MySQLcn {
	protected $_link;
	protected $_result;
	protected $_numRows;
	private $_host = "localhost";
	private $_username = "root";
	private $_password = "";
	private $_database = "myweb";
	
	// Establezca una conexión a la base de datos, cuando se crea una instancia de clase
	public function __construct() {
		$this->_link = new mysqli($this->_host, $this->_username, $this->_password, $this->_database);
		if(mysqli_connect_errno()) {
			echo "Connection Failed: " . mysqli_connect_errno();
			exit();
		}
	}
	
	// Envía la consulta a la conexión
	public function Query($sql) {
		$this->_result = $this->_link->query($sql);
		if ($this->_result === false) {
			// Registrar y propagar el error adecuadamente
			throw new \Exception('MySQL Query Error: ' . mysqli_error($this->_link));
		}
		// Para consultas SELECT, _result es un mysqli_result; para otras, puede ser bool
		$this->_numRows = (is_object($this->_result)) ? mysqli_num_rows($this->_result) : 0;
	}
	
	// Inserta en databse
	public function UpdateDb($sql) {
		$this->_result = $this->_link->query($sql);
		if ($this->_result === false) {
			throw new \Exception('MySQL Update Error: ' . mysqli_error($this->_link));
		}
		return $this->_result;
	}
	
	// Inserta y retorna ID
	public function InsertaDb($sql) {
		$res = $this->_link->query($sql);
		if ($res === false) {
			throw new \Exception('MySQL Insert Error: ' . mysqli_error($this->_link));
		}
		return mysqli_insert_id($this->_link);
	}
	
	// Devuelve el número de filas
	public function NumRows() {
		return $this->_numRows;
	}
	
	// Trae las filas y las devuelve
	public function Rows() {
		$rows = array();
		
		for($x = 0; $x < $this->NumRows(); $x++) {
			$rows[] = mysqli_fetch_assoc($this->_result);
		}
		return $rows;
	}
	
	// Trae las filas y las devuelve
	public function FetRows() {
		return mysqli_fetch_row($this->_result);
	}
	
	// Utilizado por otras clases para obtener la conexión
	public function GetLink() {
		return $this->_link;
	}
	
	// Asegurar datos de entrada
	public function SecureInput($value) {
		return mysqli_real_escape_string($this->_link, $value);
	}
	
	//Cerrar la conexion
	public function Close() {
        mysqli_close($this->_link);
    }
}
?>