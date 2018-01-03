<?php
    require_once 'config.php';
    
    if (isset($_GET['database'])) {
        $db = $_GET['database'];
    }
    else{
        echo "No Database name found!";
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

 ?>

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <select class="form-control" id="ddl_table">
                <option disabled selected>Select table</option>
                <?php
                    // Usage without mysql_list_dbs()
                    $res = mysql_query("SHOW TABLES;");

                    while ($row = mysql_fetch_assoc($res)) {
                        echo '<option>'. $row['Tables_in_'.$db] .'</option>';
                    }
                ?>
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <input type="text" id="txt_name" class="form-control" placeholder="Enter name">
        </div>
    </div>
</div>

