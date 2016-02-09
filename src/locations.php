<?php

if(!preg_match('/.*\/index.php$/', $_SERVER['PHP_SELF'])) {
    header("Location:../index.php");
    exit;
}

if(!isset($_REQUEST['location_mode']) || $_REQUEST['location_mode'] == 'delete_cancelled') {
    if(isset($_REQUEST['location_mode'])) {
        set_msg("Delete Cancelled");
    }

    $q = "SELECT l.*, COUNT(p.prefix_id) AS prefixes FROM locations l
            LEFT JOIN prefixes p
                ON p.location_id = l.location_id
            GROUP BY l.location_id";

    $res = $pdo->query($q);
    $out_array = array();
    while($row = $res->fetch()) {
        $out_array[] = array_merge($row,
            array(
                "edit_url" => "$base_url&mode=locations&location=".$row['location'],
                "delete_url" => "$base_url&mode=locations&location_mode=delete&location_id=".$row['location_id']."&location=".$row['location'],
            )
        );
    }

    $smarty->assign('out_array', $out_array);
    $smarty->display('header.tpl');
    $smarty->display('list_locations.tpl');
    $smarty->display('footer.tpl');
} else if ($_REQUEST['location_mode'] == 'add_location') {
    $smarty->display('header.tpl');
    require('src/add_location_form.php');
    $smarty->display('footer.tpl');
} else if ($_REQUEST['location_mode'] == 'add_location_now') {
    if (!isset($_REQUEST['location'])) {
        set_msg_err('You have to supply a location');
        $smarty->display('header.tpl');
        require('src/add_location_form.php');
        $smarty->display('footer.tpl');
        exit;
    }
    if (preg_match("/[a-zA-Z]{2}/", $_REQUEST['location']) == 0) {
        set_msg_err('Location must be exactly 2 ASCII letters');
        $smarty->display('header.tpl');
        require('src/add_location_form.php');
        $smarty->display('footer.tpl');
        exit;
    }

    $params = array(':location' => $_REQUEST['location']);
    $q = 'SELECT location_id FROM locations WHERE location=:location';
    $stmt = $pdo->prepare($q);
    $stmt->execute($params) or die(print_r($stmt->errorInfo()));
    if($stmt->rowCount() > 0) {
        set_msg_err("Error: location " . htmlentities($_REQUEST['location'], ENT_QUOTES) . " already exists");
        $smarty->display('header.tpl');
        require('src/add_location_form.php');
        $smarty->display('footer.tpl');
        exit;
    }

    $q = "INSERT INTO locations (location) values (:location)";
    $stmt = $pdo->prepare($q);
    $stmt->execute($params) or die(print_r($stmt->errorInfo()));
    set_msg("Location added successfully!");
    header("Location: $base_url&mode=locations&location=".$_REQUEST['location']);
    exit;
}
