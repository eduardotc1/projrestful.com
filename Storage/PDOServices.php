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

class Storage_PDOServices
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
			if ($count > 0) {				
				return $this->id2int($stmt->fetchAll());
			} else {
				return Storage_PDOServices::DataNotFound;
			}
				
		} catch (PDOException $e) {
            if (!$installTableOnFailure && $e->getCode() == '42S02') {
                //SQLSTATE[42S02]: Base table or view not found: 
				//1146 Table 'affiliates' doesn't exist
				return $this->key($id, TRUE);
            }
            throw new RestException(501, 'MySQL: ' . $e->getMessage());
        }
    }
	
	function get($id, $fromID, $toID, $pax, $code, $fromDestID, $toDestID,
		 $installTableOnFailure = FALSE)
	{
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
			$toDestID = $this->getLongerDestination($fromDestID, $toDestID);
			$stmt = $this->db->prepare("CALL sp_coupon(?,?,?,?,?,?)");
			$stmt->bindParam(1, $id, PDO::PARAM_STR, 32);
			$stmt->bindParam(2, $fromID, PDO::PARAM_STR, 5);			
			$stmt->bindParam(3, $toID, PDO::PARAM_STR, 5);
			$stmt->bindParam(4, $pax, PDO::PARAM_STR, 2);
			$stmt->bindParam(5, $code, PDO::PARAM_STR, 45);
			$stmt->bindParam(6, $toDestID, PDO::PARAM_STR, 2);
			$stmt->execute();
			$count = $stmt->rowCount();
			
			$results = array();
			$hoteles = array();
			$services = array();
			$ecoInclude = array('Private Van with A/C', 'Meet & Greet at the airport', 
				'Travel insurance','Guaranteed Baby Car Seat',
				'Flight monitoring', '24 hrs transportation service',
				'No fees for flight changes', 'Professional bilingual driver' 
			);
			$vipInclude = array('Luxury Suburban', 'Amenities included', 
				'Meet & Greet at the airport', 'Air Conditioning',
				'Travel insurance', 'Flight monitoring',
				'24 hrs transportation service', 'Professional bilingual driver',
				'No fees for flight changes and delays'
			);
			$limoInclude = array('Sienna Limo', 'or Tundra Limmo',
				'or Hummer Limo', 'Seating for up to 6 people',
				'VIP Amenities included', '1 Bottle of Champagne',
				'Flight monitoring', '24 hrs transportation service',
				'Professional bilingual driver', 'No fees for flight changes and delay'
			);
			
			if ($count > 0) {
				$i=0;
		    	do {
					$rowset = $stmt->fetchAll();
		        	if ($rowset) {
						$results[$i] = $rowset;
						if ($i == 0) {
							$hoteles['hoteles'] = $results[$i];
						} else {
							foreach ($results[$i] as $value) {
								$type = strtolower($value['type']);
								switch ($type) {
									case 'eco':
										$value['include'] = $ecoInclude;
										break;
									case 'vip':
										$value['include'] = $vipInclude;
										break;
									case 'limo':
										$value['include'] = $limoInclude;
										break;									
									default:
										$value;
										break;
								}
								$services[] = $value;
							}
							$hoteles['services'] = $services;
							return $this->id2int($hoteles);	
						}
						unset($results[$i]);
					}
					$i++;
		    	} while ($stmt->nextRowset());
			} else {
				return Storage_PDOServices::DataNotFound;
			}			
			$stmt->closeCursor();
			
		} catch (PDOException $e) {
            if (!$installTableOnFailure && $e->getCode() == '42S02') {
                //SQLSTATE[42S02]: Base table or view not found: 1146 
				//Table 'ct-hotels' doesn't exist
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
	
	private function getLongerDestination($fromDestID, $toDestID) {
	    $destinationsArray = array(0 => '4', //Cancun
	        1 => '19', //Puerto Morelos
	        2 => '13', //Paraiso Beach
	        3 => '18', //Puerto Juarez
	        4 => '9', //Isla Mujeres
	        5 => '10', //Maroma Beach
	        6 => '15', //Playa Mujeres
	        7 => '14', //Playa del Carmen
	        8 => '8', //Cozumel (Ferry en Playa del Carmen)
	        9 => '16', //Playacar
	        10 => '24', //Xcaret
	        11 => '3', //Calica
	        12 => '12', //Paamul
	        13 => '17', //Puerto Aventuras
	        14 => '25', //Xpu-Ha
	        15 => '1', //Akumal
	        16 => '20', //Tankah
	        17 => '26', //Kantenah
	        18 => '21', //Tulum
	        19 => '22', //Tulum Hotel Zone
	        20 => '2', //Bocapaila
	        21 => '7', //Chiquila
	        22 => '23', //Valladolid
	        23 => '6', //Chichen Itza
	        24 => '11', //Merida
	        25 => '5'); //Chetumal
	    $fromID = array_search($fromDestID, $destinationsArray);
	    $toID = array_search($toDestID, $destinationsArray);
	    if ($fromID >= $toID) {
	        return $fromDestID;
	    }
	    return $toDestID;
	}

}