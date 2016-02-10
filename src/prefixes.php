<?php

if(!preg_match('/.*\/index.php$/', $_SERVER['PHP_SELF'])) {
    header("Location:../index.php");
    exit;
}

function valid_ip_segment($seg) {
    global $use_ipv6;
    if ($use_ipv6 && preg_match("/^([0-9a-fA-F]{4}:){0,7}[0-9a-fA-F]{4}$/", $seg)) return true;
    return preg_match("/^([0-9]{1,3}\.){0,3}[0-9]{1,3}$/", $seg);
}

if (!isset($_REQUEST['prefix_mode'])) {
    header("Location: $base_url&mode=locations");
    exit;
} else if ($_REQUEST['prefix_mode'] == 'delete') {
    $q = "SELECT * FROM prefixes WHERE prefix_id=:prefix LIMIT 1";
    $stmt = $pdo->prepare($q);
    $stmt->execute(array(':prefix' => $_REQUEST['prefix_id'])) or die(print_r($stmt->errorInfo()));
    $row = $stmt->fetch();

    $smarty->assign('target', $row['prefix'] . " for " . get_location_name($row['location_id']));
    $smarty->assign('cancel_url', "$base_url&mode=prefixes&prefix_mode=list&location_id=".$row['location_id']);
    $smarty->assign('delete_url', "$base_url&mode=prefixes&prefix_mode=delete_now&prefix_id={$row['prefix_id']}");
    $smarty->display('header.tpl');
    $smarty->display('delete_confirmation.tpl');
    $smarty->display('footer.tpl');
    exit;
} else if ($_REQUEST['prefix_mode'] == 'delete_now') {
    if (!isset($_REQUEST['prefix_id'])) {
        set_msg_err("Error: no prefix_id");
        header("Location: $base_url&mode=locations");
        exit;
    }

    $params = array(':prefix' => $_REQUEST['prefix_id']);
    $q = "SELECT location_id FROM prefixes WHERE prefix_id=:prefix";
    $stmt = $pdo->prepare($q);
    $stmt->execute($params);
    if ($stmt->rowCount() == 0) {
        set_msg_err("No such prefix!");
        header("Location: $base_url&mode=locations");
    }

    $location_id = $stmt->fetch()['location_id'];

    $q = "DELETE FROM prefixes WHERE prefix_id=:prefix";
    $stmt = $pdo->prepare($q);
    $stmt->execute($params) or die(print_r($stmt->errorInfo()));
    set_msg("Prefix deleted successfully");
    header("Location: $base_url&mode=prefixes&prefix_mode=list&location_id=$location_id");
    exit;
// All Rules below this point require location_id
} else if (!isset($_REQUEST['location_id'])) {
    set_msg_err("No location");
    header("Location: $base_url&mode=locations");
    exit;
} else {
    $location = get_location_name($_REQUEST['location_id']);
    if (!$location) {
        set_msg_err("Invalid location! {$_REQUEST['location_id']}");
        header("Location: $base_url&mode=locations");
        exit;
    }

    $smarty->assign('location_id', $_REQUEST['location_id']);
    $smarty->assign('location', $location);

    if ($_REQUEST['prefix_mode'] == 'list') {
        $params = array(':location' => $_REQUEST['location_id']);
        $q = "SELECT p.* FROM prefixes p
              WHERE p.location_id = :location";

        $stmt = $pdo->prepare($q);
        $stmt->execute($params) or die(print_r($stmt->errorInfo()));

        $out_array = array();
        while($prefix = $stmt->fetch()) {
            $out_array[] = array_merge($prefix,
                array(
                    "edit_url" => "$base_url&mode=prefixes&prefix_mode=edit&prefix_id={$prefix['prefix_id']}&location_id={$prefix['location_id']}",
                    "delete_url" => "$base_url&mode=prefixes&prefix_mode=delete&prefix_id=".$prefix['prefix_id'],
                )
            );
        }

        $smarty->assign("out_array", $out_array);
        $smarty->assign("add_prefix_url", "$base_url&mode=prefixes&prefix_mode=add&location_id={$_REQUEST['location_id']}");
        $smarty->display("header.tpl");
        $smarty->display("list_prefixes.tpl");
        $smarty->display("footer.tpl");
    } else if ($_REQUEST['prefix_mode'] == 'add') {
        if (isset($_REQUEST['prefix'])) {
            $smarty->assign('prefix', $_REQUEST['prefix']);
        } else {
            $smarty->assign('prefix', '');
        }
        $smarty->assign('prefix_mode', 'add_now');
        $smarty->assign('action', 'add');
        $smarty->display('header.tpl');
        $smarty->display('form_prefixes.tpl');
        $smarty->display('footer.tpl');
    } else if ($_REQUEST['prefix_mode'] == 'add_now') {
        if (!isset($_REQUEST['prefix'])) {
            set_msg_err('You have to supply a prefix');
            $smarty->assign('prefix', '');
            $smarty->assign('prefix_mode', 'add_now');
            $smarty->assign('action', 'add');
            $smarty->display('header.tpl');
            $smarty->display('form_prefixes.tpl');
            $smarty->display('footer.tpl');
            exit;
        }
        if (valid_ip_segment($_REQUEST['prefix']) == 0) {
            set_msg_err('Prefix must be IPv4- or IPv6- like addresses');
            $smarty->assign('prefix', $_REQUEST['prefix']);
            $smarty->assign('prefix_mode', 'add_now');
            $smarty->assign('action', 'add');
            $smarty->display('header.tpl');
            $smarty->display('form_prefixes.tpl');
            $smarty->display('footer.tpl');
            exit;
        }

        $params = array(
            ':prefix' => $_REQUEST['prefix'],
            ':location' => $_REQUEST['location_id']
        );
        $q = 'SELECT prefix_id FROM prefixes WHERE prefix=:prefix AND location_id=:location';
        $stmt = $pdo->prepare($q);
        $stmt->execute($params) or die(print_r($stmt->errorInfo()));
        if($stmt->rowCount() > 0) {
            set_msg_err("Error: prefix " . htmlentities($_REQUEST['prefix'], ENT_QUOTES) . " already exists for $location");
            header("Location: $base_url&mode=prefixes&prefix_mode=add&location_id={$_REQUEST['location_id']}&prefix={$_REQUEST['prefix']}");
            exit;
        }

        $q = "INSERT INTO prefixes (location_id, prefix) values (:location, :prefix)";
        $stmt = $pdo->prepare($q);
        $stmt->execute($params) or die(print_r($stmt->errorInfo()));
        set_msg("prefix added successfully!");
        header("Location: $base_url&mode=prefixes&prefix_mode=list&location_id={$_REQUEST['location_id']}");
        exit;
    } else {
        if (!isset($_REQUEST['prefix_id'])) {
            set_msg_err('No prefix id specified');
            header("Location: $base_url&mode=prefixes&prefix_mode=list&location_id={$_REQUEST['location_id']}");
            exit;
        }

        $q = 'SELECT * FROM prefixes WHERE prefix_id=:prefix_id AND location_id=:location_id';
        $params = array(
            ':prefix_id' => $_REQUEST['prefix_id'],
            ':location_id' => $_REQUEST['location_id']
        );
        $stmt = $pdo->prepare($q);
        $stmt->execute($params) or die(print_r($stmt->errorInfo()));
        if ($stmt->rowCount() == 0) {
            set_msg_err("No such prefix id for $location");
            header("Location: $base_url&mode=prefixes&prefix_mode=list&location_id={$_REQUEST['location_id']}");
            exit;
        }
        $row = $stmt->fetch();
        $smarty->assign('prefix_id', $row['prefix_id']);
        $smarty->assign('prefix_mode', 'edit_now');
        $smarty->assign('action', 'edit');
        if ($_REQUEST['prefix_mode'] == 'edit') {
            $smarty->assign('prefix', $row['prefix']);
            $smarty->display('header.tpl');
            $smarty->display('form_prefixes.tpl');
            $smarty->display('footer.tpl');
        } else if ($_REQUEST['prefix_mode'] == 'edit_now') {
            if (!isset($_REQUEST['prefix'])) {
                set_msg_err('You have to supply a prefix');
                $smarty->assign('prefix', '');
                $smarty->display('header.tpl');
                $smarty->display('form_prefixes.tpl');
                $smarty->display('footer.tpl');
                exit;
            }

            if (valid_ip_segment($_REQUEST['prefix']) == 0) {
                set_msg_err('Prefix must be IPv4- or IPv6- like addresses');
                $smarty->assign('prefix', $_REQUEST['prefix']);
                $smarty->display('header.tpl');
                $smarty->display('form_prefixes.tpl');
                $smarty->display('footer.tpl');
                exit;
            }

            $params = array(
                ':location' => $_REQUEST['location_id'],
                ':prefix' => $_REQUEST['prefix'],
                ':prefix_id' => $_REQUEST['prefix_id']
            );
            $q = 'SELECT prefix_id FROM prefixes WHERE prefix=:prefix AND location_id=:location AND prefix_id!=:prefix_id';
            $stmt = $pdo->prepare($q);
            $stmt->execute($params) or die(print_r($stmt->errorInfo()));
            if($stmt->rowCount() > 0) {
                set_msg_err("Error: prefix " . htmlentities($_REQUEST['prefix'], ENT_QUOTES) . " already exists for $location");
                header("Location: $base_url&mode=prefixes&prefix_mode=edit&location_id={$_REQUEST['location_id']}&prefix_id={$_REQUEST['prefix_id']}");
                exit;
            }

            $params = array(
                ':prefix' => $_REQUEST['prefix'],
                ':prefix_id' => $_REQUEST['prefix_id']
            );
            $q = "UPDATE prefixes SET prefix=:prefix WHERE prefix_id=:prefix_id";
            $stmt = $pdo->prepare($q);
            $stmt->execute($params) or die(print_r($stmt->errorInfo()));
            set_msg("prefix updated successfully!");
            header("Location: $base_url&mode=prefixes&prefix_mode=list&location_id={$_REQUEST['location_id']}");
            exit;
        }
    }
}
