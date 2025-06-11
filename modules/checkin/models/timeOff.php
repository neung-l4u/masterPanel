<?php
global $db;
session_start();
include '../assets/db/db.php';
include "../assets/db/initDB.php";

$param['staffID'] = (!empty($_POST['staffID'])) ? $_POST['staffID'] : '';

$return = [
    'result' => '',
    'msg' => '',
    'team' => '',
    'managerNickName' => '',
    'managerName' => ''
];

$staffTeam = $db->query('SELECT 
                            t.id AS teamID,
                            t.name AS teamName,
                            t.fullName AS teamFullName,
                            m.sNickName AS managerNickName, 
                            m.sName AS managerName,
                            s.sNickName AS staffNickName,
                            s.sName AS staffName
                        FROM staffs s 
                        JOIN team t ON s.teamID = t.id 
                        LEFT JOIN staffs m ON t.manager = m.sID
                        WHERE s.sID = ?', 
                        [$param['staffID']]
                  )->fetchAll();

$return['result'] = 'success';
$return['msg'] = 'Staff team and manager found';
$return['team'] = $staffTeam[0]['teamFullName'];
$return['managerNickName'] = $staffTeam[0]['managerNickName'];
$return['managerName'] = $staffTeam[0]['managerName'];
$return['staffNickName'] = $staffTeam[0]['staffNickName'];
$return['staffName'] = $staffTeam[0]['staffName'];

echo json_encode($return);
