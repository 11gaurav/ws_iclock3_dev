<?php 
function p($p, $exit = 1)
{
    echo '<pre>';
    print_r($p);
    echo '</pre>';
    if ($exit == 1)
    {
        exit;
    }
}
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 86400'); // Cache for 1 day
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

// Make sure that it is a POST request.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'Result' => "false",
        'Message' => "Request method must be POST!",
        'Method' => $_SERVER['REQUEST_METHOD']
    ]);
    exit;
}

// Make sure that the content type of the POST request is JSON
$contentType = isset($_SERVER["CONTENT_TYPE"])?$_SERVER["CONTENT_TYPE"] : '';
if (strcasecmp($contentType, 'application/json') !== 0) {
    echo json_encode([
        'Result' => "false",
        'Message' => "Content type must be: application/json",
        'Method' => $contentType
    ]);
    exit;
}

// Receive the raw POST data
$content = file_get_contents("php://input");

$obj = json_decode($content);

//include_once 'Logger.php';
require_once("validate.php");
//Logger::info($content);
	p("dd");
// Connect to database
include("connection.php");
$db = new Database();
$connection = $db->getConnstring($obj->ServerName);

// Insert Attendance
$query = "INSERT INTO attendance 
          (employee_pin, employee_id, reader_id, clock, mode, status, work, job, downloaded, mask, temperature, clock_gps, clock_photo, site_activity_code) 
          VALUES (?, ?, ?, ?, 15, ?, ?, '', 'No', 0, 0.0, ?, ?, ?)";

$stmt = $connection->prepare($query);
$stmt->bind_param(
    "siisissss",
    $obj->employee_pin,
    $obj->employee_id,
    $obj->reader_id,
    $obj->clock,
    $obj->status,
    $obj->work,
    $obj->clock_gps,
    $obj->clock_photo,
    $obj->site_activity_code
);

if (!$stmt->execute()) {
    echo json_encode(['Result' => "Error", 'Message' => "Failed to insert attendance"]);
    exit;
}
// Call updateSeenStatus
require_once("commonFunction.php");
//updateSeenStatus($obj->sn, $connection);

// Call additional functions after updateSeenStatus
process_attlog($connection, $obj->reader_id, $obj->site_id, $obj->employee_pin);

// Response
echo json_encode(['Result' => "Success"]);

// FUNCTIONS

function process_attlog($connection, $reader_id, $site_id, $employee_pin)
{
    try {
        $employee_data = get_employee_by_pin($connection, $employee_pin, $site_id);

        if (empty($employee_data)) {
            throw new Exception("Employee not found.");
        }

        $is_visitor = check_employee_is_visitor($connection, p($employee_pin););
        if (!empty($is_visitor) && $is_visitor['visiter'] === 'Yes') {
            if ($is_visitor['visit_multi_times'] !== 'Yes') {
                $exit_readers = get_reader_by_access_groups_cdata($connection, $employee_data[0]['access_group'], 'EXIT', $site_id);
                $check_is_exit = check_attendance_for_exit($connection, ['employee_id' => $employee_data[0]['employee_id'], 'reader_id' => $reader_id]);

                if ($check_is_exit >= 2 && in_array($reader_id, $exit_readers)) {
                    $all_readers = get_reader_by_access_groups_cdata($connection, $employee_data[0]['access_group'], 'ALL', $site_id);
                    foreach ($all_readers as $reader) {
                        save_command($connection, ['reader_id' => $reader, 'command' => "DATA DEL_USER PIN=" . $employee_pin, 'status' => 'Active', 'sourceinfo' => "From app for visitor"]);
                        delete_employee_reader($connection, ['employee_id' => $is_visitor['employee_id'], 'reader_id' => $reader]);
                    }
                }
            }
        }

    } catch (Exception $e) {
        //Logger::error("process_attlog Error: " . $e->getMessage());
        p($e->getMessage());
    }
}

function get_employee_by_pin($connection, $user_pin, $site_id)
{
    $query = "SELECT * FROM employee WHERE pin = ? AND site_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("si", $user_pin, $site_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function check_employee_is_visitor($connection, $employee_pin)
{
    $query = "SELECT * FROM employee WHERE pin = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("s", $employee_pin);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();

    if ($data) {
        $query = "SELECT * FROM visitors_access_code WHERE access_code = ? AND visitors_mobile_no = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param("ss", $data['password'], $data['mobile_number']);
        $stmt->execute();
        $visitors = $stmt->get_result()->fetch_assoc();

        if ($visitors) {
            return [
                'visiter' => 'Yes',
                'visit_multi_times' => $data['visit_multi_times'],
                'created_by' => $visitors['created_by'],
                'employee_id' => $data['employee_id']
            ];
        }
    }
    return [];
}

function get_reader_by_access_groups_cdata($connection, $reader_access_groups_id, $reader_type, $site_id)
{
    $readers = [];

    $tables = [
        'IN' => ['in_reader_trans', 'in_reader'],
        'OUT' => ['out_reader_trans', 'out_reader'],
        'EXIT' => ['exit_reader_trans', 'exit_reader']
    ];

    foreach ($tables as $type => $values) {
        list($table, $column) = $values;

        $query = "SELECT GROUP_CONCAT($column) AS readers FROM $table 
                  JOIN reader_access_groups ON $table.reader_access_groups_id = reader_access_groups.reader_access_groups_id 
                  WHERE reader_access_groups.code_id = ? AND $table.site_id = ?";

        $stmt = $connection->prepare($query);
        $stmt->bind_param("si", $reader_access_groups_id, $site_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if (!empty($result['readers'])) {
            $readers[$type] = explode(',', $result['readers']);
        }
    }

    return ($reader_type === 'ALL') 
        ? array_values(array_unique(array_merge(
            isset($readers['IN']) ? $readers['IN'] : [],
            isset($readers['OUT']) ? $readers['OUT'] : [],
            isset($readers['EXIT']) ? $readers['EXIT'] : []
        ))) 
        : (isset($readers[$reader_type])? $readers[$reader_type] : []);
}



function check_attendance_for_exit($connection, $insertdata)
{
    $query = "SELECT COUNT(attendance_id) AS att_count FROM attendance WHERE employee_id = ? AND clock LIKE ?";
    $stmt = $connection->prepare($query);
    $date = date('Y-m-d') . '%';
    $stmt->bind_param("is", $insertdata['employee_id'], $date);
    $stmt->execute();
    
    $result = $stmt->get_result()->fetch_assoc();
    if ($result) {
        return $result['att_count'];
    } else {
        return 0;
    }
}

function save_command($connection, $dataValues)
{
    $query = "INSERT INTO reader_command (reader_id, command, status, sourceinfo) VALUES (?, ?, ?, ?)";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("isss", $dataValues['reader_id'], $dataValues['command'], $dataValues['status'], $dataValues['sourceinfo']);
    return $stmt->execute();
}

function delete_employee_reader($connection, $dataValues)
{
    $query = "DELETE FROM employee_reader_trans WHERE employee_id = ? AND reader_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("ii", $dataValues['employee_id'], $dataValues['reader_id']);
    return $stmt->execute();
}
