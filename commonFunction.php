<?php

function updateSeenStatus($sn, $connection) {
    $currentDateTime = date("Y-m-d H:i:s");
    $query = "UPDATE reader SET seen = '$currentDateTime' WHERE sn = '$sn'";
    $result_data = mysqli_query($connection, $query);
}

?>
