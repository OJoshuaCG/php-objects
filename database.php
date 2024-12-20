<?php
declare(strict_types=1);
class Database
{
    private $connection;
    function __construct(
        private string $hostname,
        private string $database,
        private string $username,
        private string $password,
        private string $port,
        private string $engine
    ) {
        $this->connect();
    }
    //
    public function connect()
    {
        // $dsn = "sqlsrv:server=$;TrustServerCertificatethis->hostname,$this->port;Database=$this->database";
        $status = match($this->engine){
            "mysql"      => $this->mysqlConnect(),
            "sqlserver"  => $this->mssqlConnect(),
            "postgresql" => $this->postgresqlConnect(),
            default      => false
        };

        if($status){
            // echo("Connected!");
            return true;
        }

        echo("Error connection to SQL");
        return false;
    }

    protected function postgresqlConnect()
    {
        $this->validatePort();
        $dsn = "pgsql:host=$this->hostname;port=$this->port;dbname=$this->database;";
        try {
            $this->connection = new PDO(
                $dsn,
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                )
            );
            return true;
        } catch (PDOException $e) {
            echo ("Error connection to SQL:  " . $e->getMessage());
            return false;
        }
    }

    protected function mysqlConnect()
    {
        $this->validatePort();
        $dsn = "mysql:host=$this->hostname;port=$this->port;dbname=$this->database;charset=UTF8";
        try {
            $this->connection = new PDO(
                $dsn,
                $this->username,
                $this->password,
            );
            return true;
        } catch (PDOException $e) {
            echo ("Error connection to SQL:  " . $e->getMessage());
            return false;
        }
    }

    protected function mssqlConnect()
    {
        $this->validatePort();
        $dsn = "sqlsrv:Server=$this->hostname,$this->port;Database=$this->database;TrustServerCertificate=1";
        try {
            $this->connection = new PDO(
                $dsn,
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::SQLSRV_ATTR_ENCODING => PDO::SQLSRV_ENCODING_UTF8
                )
            );
            // $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // $this->connection->setAttribute(PDO::SQLSRV_ATTR_ENCODING, PDO::SQLSRV_ENCODING_UTF8);
            return true;
        } catch (PDOException $e) {
            echo ("Error connection to SQL:  " . $e->getMessage());
            return false;
        }
    }

    protected function validatePort()
    {
        if ($this->port) {
            return;
        }
        $this->port = match($this->engine) {
            "mysql"      => "3306",
            "sqlserver"  => "1433",
            "postgresql" => "5432",
            default      => ""
        };
    }

    public function executeQuery(string $query, bool $fetch_one = false, array $params = [])
    {
        try {
            $stmt = $this->connection->prepare($query);
            $result = $stmt->execute($params);
            if(!$result){
                return null;
            }
            if ($fetch_one)
                return $stmt->fetch(PDO::FETCH_ASSOC);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(Throwable $t){
            echo "Database. Ocurrio un error inesperado " . $t->getMessage();
            return null;
        }
    }
}


/* FETCH
    PDO::FETCH_ASSOC: Devuelve un array asociativo con los nombres de columna como claves.
    PDO::FETCH_NUM: Devuelve un array numérico indexado.
    PDO::FETCH_OBJ: Devuelve un objeto anónimo con los nombres de columna como propiedades.

*/

// $db = new database("localhost", "advetureworks", "root", "root", "3306", "mysql");
// $db = new database("localhost", "formulaone", "sa", "admin", "1433", "sqlserver");

/**
 * https://www.php.net/manual/es/ref.pdo-sqlsrv.php
 * https://learn.microsoft.com/es-es/sql/connect/php/pdo-setattribute?view=sql-server-ver16
 */