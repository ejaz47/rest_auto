<?php
    
    require_once 'config.php';

    if (isset($_GET['database']) && isset($_GET['table'])) {
        $db = $_GET['database'];
        $table = $_GET['table'];
    }
    else{
        echo "No Database and/or table name found!";
        exit();
    }

    $link = mysql_connect($config['database']['server'], $config['database']['username'], $config['database']['password']);
    if (!$link) {
        die('Could not connect: ' . mysql_error());
    }

    // make current db
    $db_selected = mysql_select_db($db, $link);
    if (!$db_selected) {
        die ('Can\'t use '.$db.' : ' . mysql_error());
    }

    $class_name = "sample_name";
    if (isset($_GET['name'])) {
        if ($_GET['name'] == '') {
            $class_name = 'sample_name';
        }else{
            $class_name = strtolower($_GET['name']);
        }
    }

?>

<?php
    // Usage without mysql_list_dbs()
    $res = mysql_query("SHOW COLUMNS FROM ".$table);
    $arr = [];

    while($row = mysql_fetch_assoc($res) ){
        $arr[] = $row;
    }

    $txt = "";
    // $txt .= "\n\n";

    $gen_insert_query = "";
    $gen_insert_param = ""; 
    $gen_insert_value = "";
    $gen_data_array   = "";

    $gen_update_query      = "";
    $gen_update_param      = "";
    $gen_update_value      = "";
    $gen_update_data_array = "";

    getInsertQuery($arr);
    getUpdateQuery($arr);

    function getInsertQuery($array){
        global $table;
        global $gen_insert_query;
        global $gen_insert_param;
        global $gen_insert_value;
        global $gen_data_array;

        $q = "INSERT INTO ";
        $q .= "`" . $table . "` (";

        $cols = '';
        $vals = '';
        $para = '';
        $data_val = '';
        $data_arr = '';

        foreach ($array as $field) {
            if($field['Extra'] != "auto_increment"){
                $cols .= "`" . $field['Field'] . "`, ";
                $vals .= "?, ";

                if (strpos($field['Type'], 'int') !== false) {
                    $para .= 'i';
                }else{
                    $para .= 's';
                }

                $data_val .= '$data[\''. $field['Field'] .'\'], ';
                $data_arr .= "'" . $field['Field'] . "', ";
            }
        }


        $data_val = substr(trim($data_val), 0, -1);
        $data_arr = substr(trim($data_arr), 0, -1);
        $cols     = substr(trim($cols), 0, -1);
        $vals     = substr(trim($vals), 0, -1);

        $q .= $cols . ") VALUES(";
        $q .= $vals . ")";

        $gen_insert_query = $q;
        $gen_insert_param = $para;
        $gen_insert_value = $data_val;
        $gen_data_array = $data_arr;
    }

    function getUpdateQuery($array){
        global $table;
        global $gen_update_query;
        global $gen_update_param;
        global $gen_update_value;
        global $gen_update_data_array;

        $q = "UPDATE ";
        $q .= "`" . $table . "` SET ";

        $cols = '';
        $para = '';
        $data_val = '';
        $data_arr = '';

        foreach ($array as $field) {
            if($field['Extra'] != "auto_increment"){
                $cols .= "`" . $field['Field'] . "` = ?, ";

                if (strpos($field['Type'], 'int') !== false) {
                    $para .= 'i';
                }else{
                    $para .= 's';
                }

                $data_val .= '$data[\''. $field['Field'] .'\'], ';
                $data_arr .= "'" . $field['Field'] . "', ";
            }
        }


        $para     .= 'i';
        $data_val .= '$data[\'fm_id\'], ';

        $data_val = substr(trim($data_val), 0, -1);
        $data_arr = substr(trim($data_arr), 0, -1);
        $cols     = substr(trim($cols), 0, -1);

        $q .= $cols ;
        $q .= " WHERE `fm_id` = ?";

        $gen_update_query = $q;
        $gen_update_param = $para;
        $gen_update_value = $data_val;
        $gen_update_data_array   = $data_arr;
    }



        // echo "<pre><code class=\"php\">";
        // echo $gen_update_query;
        // echo $txt;
        // var_dump($arr);

        // echo "</code></pre>";

        // exit();
//DB class
    $txt .= 
'    class Db_'.$class_name.'
    {
        private $conn;
        function __construct()
        {
            require_once dirname(__FILE__) . \'/../../include/DbConnect.php\';
            // opening db connection
            $db         = new DbConnect();
            $this->conn = $db->connect();
        }

        public function get($data)
        {
            $stmt = $this->conn->prepare("SELECT * FROM '. $table .' WHERE fm_caid = ? AND fm_id = ?");
            $stmt->bind_param("ii", $data[\'fm_caid\'], $data[\'fm_id\']);
            if ($stmt->execute()) 
            {
                $result = $stmt->get_result();
                $ret_data = [];
                while ($row = $result->fetch_assoc()) 
                {
                    $ret_data[] = $row;
                }
                $stmt->close();
                return $ret_data;
            } 
            else 
            {
                return  NULL;
            }
        }

        public function create($data)
        {
            $data_array = ['. $gen_data_array .'];
            foreach($data_array as $val){
                if(!isset($data[$val])){
                    $data[$val] = \'\';
                }
            }

            $query = "'. $gen_insert_query .'";
            $query_param = "'. $gen_insert_param .'";


            $stmt = $this->conn->prepare($query);
            $stmt->bind_param($query_param, ' . $gen_insert_value . ');
            $result = $stmt->execute();
            $stmt->close();
            // Check for successful insertion
            if ($result) {
                // Farmer successfully inserted
                return true;
            } else {
                // Failed to create Farmer
                return false;
            }
        }


        public function update($data)
        {
            $data_array = ['. $gen_update_data_array .'];
            foreach($data_array as $val){
                if(!isset($data[$val])){
                    $data[$val] = \'\';
                }
            }

            $query = "'. $gen_update_query .'";
            $query_param = "'. $gen_update_param .'";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param($query_param, ' . $gen_update_value . ');
            $result = $stmt->execute();
            $stmt->close();
            // Check for successful insertion
            if ($result) {
                // Farmer successfully inserted
                return true;
            } else {
                // Failed to create Farmer
                return false;
            } 
        }

    }';


$txt .= "\n\n";


//Get method
    $txt .= 
'    $app->get(\'/'.$class_name.'/:id\', \'authenticate\', function($id) use ($app) {
        
        $db = new Db_'.$class_name.'();
        global $user_id;
        if (isset($id)) {

            $form_data[\'fm_caid\'] = $user_id;
            $form_data[\'fm_id\'] = $id;

            $data   = $db->get($form_data);
            $response["success"] = true;
            $response["data"] = $data;
        }
        else{
            $response["success"] = false;
            $response["message"] = "Parameter Id is missing!";
        }

        echoResponse(200, $response);

    });';

$txt .= "\n\n";

//Post method
    $txt .= 
'    $app->post(\'/'.$class_name.'\', \'authenticate\', function() use ($app){
        verifyRequiredParams([]); //provide a list of required parametes
        
        $data = $app->request->post(); //fetching the post data into variable
        global $user_id;
        
        //set default values here
        $data[\'fm_caid\'] = $user_id;

        $db = new Db_'.$class_name.'();
        $return_data    = $db->create($data);

        if ($return_data != false) {
            $response["success"] = true;
            $response["message"] = "Data added successfully!";
        } else {
            $response["success"] = false;
            $response["message"] = "Failed to Add data. Please try again";
        }
        echoResponse(201, $response);
    });';

$txt .= "\n\n";

//PUT method
    $txt .= 
'    $app->put(\'/'.$class_name.'/:id\', \'authenticate\', function() use ($app){
        verifyRequiredParams([]); //provide a list of required parametes
        
        $data = $app->request->put(); //fetching the post data into variable
        global $user_id;
        
        //set default values here
        $data[\'fm_caid\'] = $user_id;
        $data[\'fm_id\'] = $id;

        $db = new Db_'.$class_name.'();

        $return_data    = $db->update($data, $user_id, $id);
        
        if ($return_data != false) {
            $response["success"] = true;
            $response["message"] = "Updated successfully!";
        } else {
            $response["success"] = false;
            $response["message"] = "Failed to update. Please try again";
        }
        echoResponse(200, $response);
    });';

$txt .= "\n\n";
    
    if (isset($_GET['download'])) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='. $class_name .'.php');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        echo "<?php\n\n";
        echo $txt;
        echo "?>";

        exit;
    }
    else{
        echo '<div class="row"><div class="col-md-12"><a href="get_php.php?download=true&database='.$db.'&table='.$table.'&name='.$class_name.'" class="btn btn-success btn-sm float-right" style="border-radius:0px;">Download</a></div></div>';
        echo "<pre><code class=\"php scroll scroll4\">";
        echo htmlspecialchars($txt);
        echo "</code></pre>";
    }
    
?>
