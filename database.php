<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class Database {

    private $host = 'localhost';
    private $user = 'root';
    private $password = 'root';
    private $db = 'project_storage';

    /**
     * Creates a simple database-connection.
     *
     * @return PDO
     */
    private function create_connection() {
        $conn = new PDO("mysql:host=$this->host;dbname=$this->db", $this->user, $this->password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    }

    private function check_if_table_exist($connection, $table) {
        try {
            $connection->query("SELECT 1 FROM $table");
        } catch (PDOException $e) {
            return false;
        }
        return true;
    }



    private function create_sample_table() {
        // here: create table if not exist.
        try {
            $conn = $this->create_connection();
            if (!$this->check_if_table_exist($conn, 'samples')) {
                // sql to create table
                $sql = "CREATE TABLE samples (
                    sample_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    sample_name VARCHAR(40) NOT NULL,
                    material VARCHAR(40) NOT NULL,
                    setup_date TIMESTAMP,
                    container_number VARCHAR(10)
                    )";
                // use exec() because no results are returned
                $conn->exec($sql);
                echo "sample table created successfully";
            } else {
                // echo "user table already exist.";
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        $conn = null;
    }

    
    private function create_nid_table() {
        // here: create table if not exist.
        try {
            $conn = $this->create_connection();
            if (!$this->check_if_table_exist($conn, 'nid_files')) {
                // sql to create table
                $sql = "CREATE TABLE nid_files (
                    nid_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    nid_file MEDIUMBLOB NOT NULL,
                    sample_id INT(11) UNSIGNED NOT NULL,
                    nid_name VARCHAR(40) NOT NULL,
                    date_of_recording DATE NOT NULL,
                    nr_of_lines INT(5),
                    save_date TIMESTAMP )";
                // use exec() because no results are returned
                $conn->exec($sql);
                $sql = "
                ALTER TABLE `nid_files`  
                ADD CONSTRAINT `FK_nid_files_samples` 
                    FOREIGN KEY (`sample_id`) REFERENCES `samples`(`sample_id`) 
                        ON UPDATE CASCADE 
                        ON DELETE CASCADE;
                ";
                $conn->exec($sql);
                echo "nid table created successfully";
            } else {
                // echo "user table already exist.";
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        $conn = null;
    }




    
    public function prepare_registration() {
        $this->create_sample_table();
        $this->create_nid_table();
        return true;
    }
    
    public function store_file($nid_name, $nr_of_lines, $sample_id, $date_of_recording, $nid_file) {
        // here: insert a new nid_file into the database.
        try {
            $conn = $this->create_connection();
            $query = "SELECT * FROM `nid_files` WHERE nid_name = ?";
            $statement = $conn->prepare($query);
            $statement->execute([$nid_name]);

            $files = $statement->fetchAll(PDO::FETCH_CLASS);
            if (!empty($files)) {
                return false;
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }

        // now: save sample.
        try {
            $conn = $this->create_connection();

            $sql = 'INSERT INTO nid_files(sample_id, nid_name, date_of_recording, nid_file, nr_of_lines, save_date)
            VALUES(?, ?, ?, ?, ?, NOW())';
            $statement = $conn->prepare($sql);
            $statement->execute([$sample_id, $nid_name, $date_of_recording, $nid_file, $nr_of_lines]);
            return true;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }

        return false;
    }




    public function register_sample($sample_name, $material, $container_number) {
        // here: insert a new sample into the database.
        try {
            $conn = $this->create_connection();
            $query = "SELECT * FROM `samples` WHERE sample_name = ?";
            $statement = $conn->prepare($query);
            $statement->execute([$sample_name]);

            $sample = $statement->fetchAll(PDO::FETCH_CLASS);
            if (!empty($sample)) {
                return false;
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }

        // now: save sample.
        try {
            $conn = $this->create_connection();

            $sql = 'INSERT INTO samples(sample_name, material, container_number, setup_date)
            VALUES(?, ?, ?, NOW())';
            $statement = $conn->prepare($sql);
            $statement->execute([$sample_name, $material, $container_number]);
            return true;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }

        return false;
    }
    
    public function drop_all() {
        try {
            $conn = $this->create_connection();
            
            $sql = 'ALTER TABLE `nid_files`
                DROP FOREIGN KEY `FK_nid_files_samples`;';
            $conn->exec($sql);

            $sql = 'DROP TABLE nid_files';
            $conn->exec($sql);

            $sql = 'DROP TABLE samples';
            $conn->exec($sql);

        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        return false;
    }
}