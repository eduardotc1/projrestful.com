<?php
/**
 * MySQL DB. All data is stored in pdo_mysql database
 * Create an empty MySQL database and set the dbname, username
 * and password below
 *
 * This class will create the table with sample data
 * automatically on first `get` or `get($id)` request
 */
use Luracast\Restler\RestException;

class Storage_PDO
{
	const DataNotFound = 'DataNotFound';
	
	private $db;
	private $dsn;		
	private $username;
	private $password;
	private $options;
	
    function __construct()
    {		
        try {
			include_once 'config.php';
			$this->dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME;	
			$this->username = DB_USER;
			$this->password = DB_PASS;
			
            $this->options = array(PDO::MYSQL_ATTR_INIT_COMMAND => DB_OPTIONS);
			$this->db = new PDO($this->dsn, $this->username, $this->password, $this->options);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
						
        } catch (PDOException $e) {
            throw new RestException(501, 'MySQL: ' . $e->getMessage());
        }
    }
	
	function key($id, $checkmd5, $installTableOnFailure = FALSE)
	{
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
			$stmt = $this->db->prepare("CALL sp_key(?,?)");
			$stmt->bindParam(1, $id, PDO::PARAM_STR, 32);
			$stmt->bindParam(2, $checkmd5, PDO::PARAM_STR, 32);
			$stmt->execute();
			$count = $stmt->rowCount();
			$hoteles = array();
			if ($count > 0) {
				return $this->id2int($stmt->fetch());
			}
			$stmt->closeCursor();
		} catch (PDOException $e) {
            if (!$installTableOnFailure && $e->getCode() == '42S02') {
                //SQLSTATE[42S02]: Base table or view not found: 1146 Table 'affiliates' doesn't exist
				return $this->key($id, TRUE);
            }
            throw new RestException(501, 'MySQL: ' . $e->getMessage());
        }
    }
		
	function get($id, $installTableOnFailure = FALSE)
	{
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
			$stmt = $this->db->prepare("CALL sp_hoteles(?)");
			$stmt->bindParam(1, $id, PDO::PARAM_STR, 20);
			$stmt->execute();
			$count = $stmt->rowCount();
			if ($count > 0) {
				$hoteles['hoteles'] = $stmt->fetchAll();
				return $this->id2int($hoteles);	
			}
			$stmt->closeCursor();
		} catch (PDOException $e) {
            if (!$installTableOnFailure && $e->getCode() == '42S02') {
                //SQLSTATE[42S02]: Base table or view not found: 
				//1146 Table 'ct-hotels' doesn't exist
				return $this->get($id, TRUE);
            }
            throw new RestException(501, 'MySQL: ' . $e->getMessage());
        }
    }
	
    private function id2int($r)
    {
		if (is_array($r)) {
            if (isset($r['id'])) {
                $r['id'] = intval($r['id']);
            }
        }
        return $r;
    }
}

