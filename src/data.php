<?php


/*
 *
 * VegaDNS - DNS Administration Tool for use with djbdns
 *
 * CREDITS:
 * Written by Bill Shupp
 * <hostmaster@shupp.org>
 *
 * LICENSE:
 * This software is distributed under the GNU General Public License
 * Copyright 2003-2015, Bill Shupp
 * see COPYING for details
 *
 */

if(!preg_match('/.*\/index.php$/', $_SERVER['PHP_SELF'])) {
    header("Location:../index.php");
    exit;
}

$out = "";

// build locations!
$q = "SELECT l.location, p.prefix FROM locations l
        LEFT JOIN prefixes p
            ON p.location_id = l.location_id
        ORDER BY l.location DESC";

$stmt = $pdo->query($q) or die(print_r($pdo->errorInfo()));
while ($row = $stmt->fetch()) {
    if (preg_match("/^([0-9a-fA-F]{4}:){0,7}[0-9a-fA-F]{4}$/", $row['prefix']) && $use_ipv6) {
        $out .= "%s{$row['location']}:".str_replace(":", "", $row['prefix']) . "\n";
    }
    else if (preg_match("/^([0-9]{1,3}\.){0,3}[0-9]{1,3}$/", $row['prefix'])) {
        $out .= "%{$row['location']}:{$row['prefix']}\n";
    }
    else {
        $out .= "#Invalid prefix: %{$row['location']}:{$row['prefix']}\n";
    }
}


// build data
$q = "select a.domain, b.host, b.type, b.val, b.distance, b.weight, b.port, b.ttl, c.location
    from domains a
    left join records b
        on a.domain_id = b.domain_id
    left join locations c
        on b.location_id = c.location_id
    where a.status='active' order by a.domain, b.type, b.host, b.val";
$stmt = $pdo->query($q) or die(print_r($pdo->errorInfo()));

$lastdomain = "";
while($row = $stmt->fetch()) {
    // Set comment if it's the beginning of a new domain
    if($lastdomain != $row['domain']) $out .= "\n#".$row['domain']."\n";

    // Get records
    $out .= build_data_line($row,$row['domain']);
    $lastdomain = $row['domain'];
}

if(isset($vegadns_generation_txt_record)) {
    $timestamp = get_latest_log_timestamp();
    $sum = md5($out);
    $row = array(
	'type' => 'T',
	'host' => $vegadns_generation_txt_record,
	'val' => $timestamp . '-' . $sum,
        'ttl' => 3600
    );
    $generation_record = build_data_line($row, "notused_for_txt");

    $out .= "\n# VegaDNS Generation TXT Record\n$generation_record";
}

print($out);
exit;

?>
