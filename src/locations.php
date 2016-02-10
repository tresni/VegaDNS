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
                "edit_url" => "$base_url&mode=prefixes&prefix_mode=list&location_id=".$row['location_id'],
                "delete_url" => "$base_url&mode=locations&location_mode=delete&location_id=".$row['location_id']."&location=".$row['location'],
            )
        );
    }

    $smarty->assign('out_array', $out_array);
    $smarty->assign('add_location_url', "$base_url&mode=locations&location_mode=add");
    $smarty->display('header.tpl');
    $smarty->display('list_locations.tpl');
    $smarty->display('footer.tpl');
} else if ($_REQUEST['location_mode'] == 'add') {
    $smarty->display('header.tpl');
    if (isset($_REQUEST['location'])) {
        $smarty->assign('location', $_REQUEST['location']);
    } else {
        $smarty->assign('location', '');
    }
    $smarty->assign('location_mode', 'add_now');
    $smarty->assign('action', 'add');
    $smarty->display('form_location.tpl');
    $smarty->display('footer.tpl');
} else if ($_REQUEST['location_mode'] == 'add_now') {
    if (!isset($_REQUEST['location'])) {
        set_msg_err('You have to supply a location');
        header("Location: $base_url&mode=locations&location_mode=add");
        exit;
    }
    if (preg_match("/[a-zA-Z]{2}/", $_REQUEST['location']) == 0) {
        set_msg_err('Location must be exactly 2 ASCII letters');
        header("Location: $base_url&mode=locations&location_mode=add&location=".$_REQUEST['location']);
        exit;
    }

    $params = array(':location' => $_REQUEST['location']);
    $q = 'SELECT location_id FROM locations WHERE location=:location';
    $stmt = $pdo->prepare($q);
    $stmt->execute($params) or die(print_r($stmt->errorInfo()));
    if($stmt->rowCount() > 0) {
        set_msg_err("Error: location " . htmlentities($_REQUEST['location'], ENT_QUOTES) . " already exists");
        header("Location: $base_url&mode=locations&location_mode=add");
        exit;
    }

    $q = "INSERT INTO locations (location) values (:location)";
    $stmt = $pdo->prepare($q);
    $stmt->execute($params) or die(print_r($stmt->errorInfo()));
    set_msg("Location added successfully!");
    header("Location: $base_url&mode=prefixes&prefix_mode=list&location_id=".$pdo->lastInsertId());
    exit;
} else if ($_REQUEST['location_mode'] == 'delete') {
    $q = "SELECT * FROM locations WHERE location_id=:location LIMIT 1";
    $stmt = $pdo->prepare($q);
    $stmt->execute(array(':location' => $_REQUEST['location_id'])) or die(print_r($stmt->errorInfo()));
    $row = $stmt->fetch();

    $smarty->assign('target', $row['location']);
    $smarty->assign('cancel_url', "$base_url&mode=locations");
    $smarty->assign('delete_url', "$base_url&mode=locations&location_mode=delete_now&location_id={$row['location_id']}");
    $smarty->display('header.tpl');
    $smarty->display('delete_confirmation.tpl');
    $smarty->display('footer.tpl');
    exit;
} else if ($_REQUEST['location_mode'] == 'delete_now') {
    if (!isset($_REQUEST['location_id'])) {
        set_msg_err("Error: no location_id");
        header("Location: $base_url&mode=locations");
        exit;
    }

    $q = "DELETE FROM locations WHERE location_id=:location";
    $stmt = $pdo->prepare($q);
    $stmt->execute(array(':location' => $_REQUEST['location_id'])) or die(print_r($stmt->errorInfo()));
    set_msg("Location deleted successfully");
    header("Location: $base_url&mode=locations");
    exit;
}
