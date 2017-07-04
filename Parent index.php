<?php
require '../library/plivo.php';
require '../library/Mandrill.php';

require '../db/db.php';
require 'Slim/Slim.php';

date_default_timezone_set('Asia/Kolkata');

\Slim\Slim::registerAutoloader();


// Set header first
// header('Content-Type: text/html; charset=utf-8');
// header('Content-Type: application/json; charset=utf-8');

$app = new \Slim\Slim(array(
    'debug' => true
));
$app->post('/hello/:name', function ($name) {
    $posts = json_decode(file_get_contents("php://input"));
    $posts = stdToArray($posts);
//        exit();
    $username = $posts['phone'];
    echo "Hello, $name phone number is $username";
});

/*
* Added by chuch
* 10/17/2015
*/

$app->post('/login/:usertype', function($usertype){//1: admin, 
    $userinfo = array();
    if ($usertype == 1){ 
        $tb_name = "lm_dash_user";
        $username = $_POST['username'];
        $password = base64_encode($_POST['password']);
        $where = " (email='$username' OR phonenum='$username') AND userpass='$password' AND is_admin='1' AND is_delete='0'";
        $userinfo = getUsers($tb_name, $where);
        if ($userinfo){
            session_start();
            $_SESSION['SUPER_ID'] = $userinfo[0]['id'];
            $_SESSION['SUPER_ADMIN_NAME'] = $userinfo[0]['name'];
            $result['status'] = 1;
        }
        else {
            $result['status'] = 0;
        }
        echo json_encode($result);
    }
    else if ($usertype == 2) {
        $tb_name = "lm_dash_user";
        $username = $_POST['username'];
        $password = base64_encode($_POST['password']);
        $where = " (email='$username' OR phonenum='$username') AND userpass='$password' AND is_fulldashuser='1' AND is_delete='0'";
        $userinfo = getUsers($tb_name, $where);
        if ($userinfo){
            session_start();
            $_SESSION['FDASH_ID'] = $userinfo[0]['id'];
            $_SESSION['FDASH_NAME'] = $userinfo[0]['name'];
            $_SESSION['FDASH_PHONE'] = $userinfo[0]['phonenum'];
            $_SESSION['FDASH_EMAIL'] = $userinfo[0]['email'];
            $_SESSION['FDASH_HOSPITAL_ID'] = $userinfo[0]['hospital_id'];
            $_SESSION['FDASH_BRANCH_ID'] = $userinfo[0]['branch_id'];
            $_SESSION['FDASH_IS_MAIN'] = $userinfo[0]['is_mainuser'];
            $_SESSION['FDASH_IS_HOSADMIN'] = $userinfo[0]['is_hosadminuser'];
            $hospial_info = getHospitalInfo($userinfo[0]['hospital_id']);
            $_SESSION['FDASH_ISVISITED'] = $hospial_info[0]['is_visited'];
            $_SESSION['FDASH_HOSPITAL_NAME'] = $hospial_info[0]['name'];
            // $result['hospital_id'] = $userinfo[0]['hospital_id'];
            $result['isvisited'] = $hospial_info[0]['is_visited'];
            $result['status'] = 1;
        }
        else {
            $result['status'] = 0;
        }
        echo json_encode($result);
    }
    else if ($usertype == 3) {
        $tb_name = "lm_dash_user";
        $username = $_POST['username'];
        $password = base64_encode($_POST['password']);
        $where = " (email='$username' OR phonenum='$username') AND userpass='$password' AND is_limiteddashuser='1' AND is_delete='0'";
        $userinfo = getUsers($tb_name, $where);
        if ($userinfo){
            session_start();
            
        else {
            $result['status'] = 0;
        }
        echo json_encode($result);
    }
    else if ($usertype == 'phone') { //marketing mobile app user
        $tb_name = "lm_dash_user";
		$posts = json_decode(file_get_contents("php://input"));
		$posts = stdToArray($posts);
//		exit();
        $username = $posts['phone'];
        // // $username = $_POST['username'];
        $password = base64_encode($posts['password']);
        $where = " phonenum='$username' AND userpass='$password' AND is_marketappuser='1' AND is_delete='0'";
        $userinfo = getUsers($tb_name, $where);
        if ($userinfo) {
            $out = $userinfo[0]['id'];
            echo "{\"status\": \"OK\", \"result\": $out }";
        } else {
            echo "{\"status\": \"failed\", \"result\": \"No Dashboard User Found\" }";
        }
        
    }
    else if ($usertype == 'loginByToken') {
        echo 'bbbb';
    }
});

$app->post('/dashuser/:param', function($param){
    $tb_name = 'lm_dash_user';
    if ($param == "getallorderhospital") {
        $where = " (is_delete = 0 AND is_admin = 0)";
        $order = " hospital_id ASC, is_mainuser DESC, is_fulldashuser DESC, is_limiteddashuser DESC";
        $result = getUsers($tb_name, $where);
        if ($result) {
            $out = json_encode($result);
            echo "{\"status\": \"OK\", \"message\": $out, \"code\": \"$param\" }";
        } else {
            echo "{\"status\": \"failed\", \"message\": \"No Dashboard User Found\" }";
        }
    }
    else if ($param == "getmainuser") {
        $where = " (is_delete = 0 AND is_admin = 0 AND is_mainuser = 1)";
        $result = getUsers($tb_name, $where);
        if ($result) {
            $out = json_encode($result);
            echo "{\"status\": \"OK\", \"message\": $out, \"code\": \"$param\" }";
        } else {
            echo "{\"status\": \"failed\", \"message\": \"No Dashboard User Found\" }";
        }
    }
    else if ($param == "gethosadmin") {
        $where = " (is_delete = 0 AND is_admin = 0 AND is_mainuser = 0 AND is_hosadminuser = 1)";
        $result = getUsers($tb_name, $where);
        if ($result) {
            $out = json_encode($result);
            echo "{\"status\": \"OK\", \"message\": $out, \"code\": \"$param\" }";
        } else {
            echo "{\"status\": \"failed\", \"message\": \"No Dashboard User Found\" }";
        }
    }
    else if($param == 'getbyadduser'){
        session_start();
        $add_user_id = (array_key_exists('add_user_id', $_POST)? $_POST['add_user_id']:$_SESSION['FDASH_ID']);
        $where = " (is_delete = 0 AND is_admin = 0 AND is_mainuser = 0 AND add_user_id = ".$add_user_id.")";
        $result = getUsers($tb_name, $where);

        if ($result) {
            $out = json_encode($result);
            echo "{\"status\": \"OK\", \"message\": $out, \"code\": \"$param\" }";
        } else {
            echo "{\"status\": \"failed\", \"message\": \"No Dashboard User Found\" }";
        }
    }
    else if ($param == "getbyhospital") {
        session_start();
        $hospital_id = (array_key_exists('hospital_id', $_POST)? $_POST['hospital_id']:$_SESSION['FDASH_HOSPITAL_ID']);
        $where = " (is_delete = 0 AND is_admin = 0 AND is_mainuser = 0 AND hospital_id = ".$hospital_id.")";
        $result = getUsers($tb_name, $where);
        if ($result) {
            $out = json_encode($result);
            echo "{\"status\": \"OK\", \"message\": $out, \"code\": \"$param\" }";
        } else {
            echo "{\"status\": \"failed\", \"message\": \"No Dashboard User Found\" }";
        }
    }
    else if ($param == "getbybranch") {
        session_start();
        $branch_id = (array_key_exists('branch_id', $_POST)? $_POST['branch_id']:$_SESSION['FDASH_BRANCH_ID']);
        $where = " (is_delete = 0 AND is_admin = 0 AND branch_id = ".$branch_id.")";
        $result = getUsers($tb_name, $where);
        if ($result) {
            $out = json_encode($result);
            echo "{\"status\": \"OK\", \"message\": $out, \"code\": \"$param\" }";
        } else {
            echo "{\"status\": \"failed\", \"message\": \"No Dashboard User Found\" }";
        }
    }
     else if ($param == "getfromDashboard") {
        session_start();
        if ($_SESSION['FDASH_IS_MAIN'] == 1){
            $hospital_id = $_SESSION['FDASH_HOSPITAL_ID'];
            $where = " (is_delete = 0 AND is_admin = 0 AND is_mainuser = 0 AND hospital_id = ".$hospital_id.")";
            $result = getUsers($tb_name, $where);
        }
        else {
            $branch_id = $_SESSION['FDASH_BRANCH_ID'];
            $where = " (is_delete = 0 AND is_admin = 0 AND is_mainuser = 0 AND branch_id = ".$branch_id.")";
            $result = getUsers($tb_name, $where);
        }
        if ($result) {
            $out = json_encode($result);
            echo "{\"status\": \"OK\", \"message\": $out, \"code\": \"$param\" }";
        } else {
            echo "{\"status\": \"failed\", \"message\": \"No Dashboard User Found\" }";
        }
    }
    else if ($param=="new"){
        // echo json_encode($_POST);
        session_start();
        $_POST['is_mainuser'] = (array_key_exists('is_mainuser', $_POST)? $_POST['is_mainuser']:0);
        $_POST['is_fulldashuser'] = (array_key_exists('is_fulldashuser', $_POST)? $_POST['is_fulldashuser']:0);
        
        if ($_POST['is_mainuser'] == '1'){
            $_POST['is_fulldashuser'] = '1';
            $_POST['is_limiteddashuser'] = '1';
            $_POST['add_user_id'] = (array_key_exists('add_user_id', $_POST)? $_POST['add_user_id']:$_SESSION['SUPER_ID']);
        }
        else {
            $_POST['add_user_id'] = (array_key_exists('add_user_id', $_POST)? $_POST['add_user_id']:$_SESSION['FDASH_ID']);
            $_POST['hospital_id'] = (array_key_exists('hospital_id', $_POST)? $_POST['hospital_id']:$_SESSION['FDASH_HOSPITAL_ID']);
        }
        if ($_POST['is_fulldashuser'] == '1'){
            $_POST['is_limiteddashuser'] = '1';
        }
        $result = insertDashUser($_POST);
        echo $result;
    }
    else if ($param=="edit"){
        session_start();
        $_POST['is_mainuser'] = (array_key_exists('is_mainuser', $_POST)? $_POST['is_mainuser']:0);
//        $_POST['add_user_id'] = (array_key_exists('add_user_id', $_POST)? $_POST['add_user_id']:$_SESSION['FDASH_ID']);
        
        $_POST['is_fulldashuser'] = (array_key_exists('is_fulldashuser', $_POST)? $_POST['is_fulldashuser']:0);
        if ($_POST['is_mainuser'] == '1'){
            $_POST['is_fulldashuser'] = '1';
            $_POST['is_limiteddashuser'] = '1';
            updateHospitalName($_POST['hospital_id'], $_POST['hospital'], $_POST['chain_standalone']);
            $_POST['add_user_id'] = (array_key_exists('add_user_id', $_POST)? $_POST['add_user_id']:$_SESSION['SUPER_ID']);
        }
        else {
            $_POST['hospital_id'] = (array_key_exists('hospital_id', $_POST)? $_POST['hospital_id']:$_SESSION['FDASH_HOSPITAL_ID']);
            $_POST['add_user_id'] = (array_key_exists('add_user_id', $_POST)? $_POST['add_user_id']:$_SESSION['FDASH_ID']);
        }
        if ($_POST['is_fulldashuser'] == '1'){
            $_POST['is_limiteddashuser'] = '1';
        }
        $result = updateDashUser($_POST);
        echo $result;
    }
    else if ($param=="newhosadmin"){
        session_start();
        $_POST['is_mainuser'] = (array_key_exists('is_mainuser', $_POST)? $_POST['is_mainuser']:0);
        $_POST['add_user_id'] = (array_key_exists('add_user_id', $_POST)? $_POST['add_user_id']:$_SESSION['SUPER_ID']);
        $result = insertDashUser($_POST);
        echo $result;
    }
    else if ($param=="edithosadmin"){
        session_start();
        $_POST['is_mainuser'] = (array_key_exists('is_mainuser', $_POST)? $_POST['is_mainuser']:0);
        $_POST['add_user_id'] = (array_key_exists('add_user_id', $_POST)? $_POST['add_user_id']:$_SESSION['SUPER_ID']);
        
        $result = updateDashUser($_POST);
        echo $result;
    }
    elseif ($param=="changepassword") { //Change the full dashuser's password
        session_start();
        $id = $_SESSION['FDASH_ID'];
        $userinfo = getDashUserPassword($id);
        if ($userinfo[0]['userpass'] == base64_encode($_POST['cur_password'])){
            $new_password = $_POST['new_password'];
            $result = changeDashUserPassword($id, $new_password);
            echo $result;
        }
        else {
            echo "{\"status\": \"failed\", \"message\": \"Please provide your correct password\" }";
        }
    }
    elseif ($param=="lchangepassword") { //Change the limited dashuser's password
        session_start();
        $id = $_SESSION['LDASH_ID'];
        $userinfo = getDashUserPassword($id);
        if ($userinfo[0]['userpass'] == base64_encode($_POST['cur_password'])){
            $new_password = $_POST['new_password'];
            $result = changeDashUserPassword($id, $new_password);
        }            echo $result;

        else {
            echo "{\"status\": \"failed\", \"message\": \"Please provide your correct password\" }";
        }
    }
    else if ($param=="delete"){
		$user_type = (array_key_exists('user_type', $_POST)? $_POST['user_type']:null);
        $result = deleteDashUser($_POST['id'], $user_type);
        echo $result;
    }
    else if ($param == "delete")
    {
        $
    }
});

$app->post('/hospital/:param', function($param){
    if ($param == "update"){
        $_POST['is_primary'] = 1;
        $primary_branch_id = addNewBranch($_POST);
        $_POST['primary_branch_id'] = $primary_branch_id;
        $result = updateHospitalInfo($_POST);
        echo $result;
    }
    else if ($param=="insert"){
        $hospital_id = insertHospitalInfo($_POST);
        $_POST['hospital_id'] = $hospital_id;
        $primary_branch_id = addNewBranch($_POST);
        $return = changePrimaryBranch($hospital_id, $primary_branch_id, $_POST['branch_name']);
        echo $hospital_id;
    }
    else if ($param=="getAll"){
        $result = getHospitalInfo();
        if ($result) {
            $out = json_encode($result);
            echo "{\"status\": \"OK\", \"message\": $out, \"code\": \"$param\" }";
        } else {
            echo "{\"status\": \"failed\", \"message\": \"No Any Hospital Found\" }";
        }
    }
    else if ($param == "get"){
        session_start();
        $hospital_id = (array_key_exists('hospital_id', $_POST)? $_POST['hospital_id']:$_SESSION['FDASH_HOSPITAL_ID']);
        $result = getHospitalInfo($hospital_id);
        if ($result) {
            $out = json_encode($result);
            echo "{\"status\": \"OK\", \"message\": $out, \"code\": \"$param\" }";
        } else {
            echo "{\"status\": \"failed\", \"message\": \"No Any Hospital Found\" }";
        }
    }
});

$app->post('/camp/:param', function($param){
    session_start();
    switch ($param) {
        case 'new':
            $_POST['add_user_id'] = $_SESSION['FDASH_ID'];
            $_POST['hospital_id'] = $_SESSION['FDASH_HOSPITAL_ID'];
            $result = insertCamp($_POST);
            echo $result;
        break;
        case 'getactiveall':
            $result = getActiveCamps();
            if ($result) {
                $out = json_encode($result);
                echo "{\"status\": \"OK\", \"message\": $out, \"code\": \"$param\" }";
            } else {
                
        break;
        case 'getactivebyhospital':
            if ($_SESSION['FDASH_IS_MAIN'] == 1){
                $hospital_id = $_SESSION['FDASH_HOSPITAL_ID'];
                $result = getActiveCamps(null,$hospital_id);
            }
            else {
                $branch_id = $_SESSION['FDASH_BRANCH_ID'];
                $result = getActiveCampsByBranchID($branch_id);
            }
            if ($result) {
                $out = json_encode($result);
                echo "{\"status\": \"OK\", \"message\": $out, \"code\": \"$param\" }";
            } else {
                echo "{\"status\": \"failed\", \"message\": \"No Camps Found\" }";
            }
			exit();
        break;
        case 'edit':
            $result = updateCamp($_POST);
            echo $result;

            
        break;
		case 'delete':
            $result = deleteCamp($_POST['id']);
        echo $result;
        break;
        default:

        break;
    }
});

$app->post('/offer/:param', function($param){
    session_start();
    if ($param=="active"){ 
        $result = getOffers();
        if ($result) {
            $out = json_encode($result);
            echo "{\"status\": \"OK\", \"message\": $out, \"code\": \"$param\" }";
        } else {
            echo "{\"status\": \"failed\", \"message\": \"No Offer Found\" }";
        }
    }
    else if ($param=="activebyhospital"){
		if (!$_SESSION['FDASH_HOSPITAL_ID'] || $_SESSION['FDASH_HOSPITAL_ID']== null){
			echo "{\"status\": \"loggedout\", \"message\": \"Please login again!\" }";
			exit();
		}
        if ($_SESSION['FDASH_IS_MAIN'] == 1){
            $hospital_id = $_SESSION['FDASH_HOSPITAL_ID'];
            $result = getOffers(null,$hospital_id);
        }
        else {
            $branch_id = $_SESSION['FDASH_BRANCH_ID'];
            $result = getOffersByBranchID($branch_id);
        }
        if ($result) {
            $out = json_encode($result);
            echo "{\"status\": \"OK\", \"message\": $out, \"code\": \"$param\" }";
        } else {
            echo "{\"status\": \"failed\", \"message\": \"No Offer Found\" }";
        }
		exit();
    }
    else if($param=="new"){
        $_POST['add_user_id'] = $_SESSION['FDASH_ID'];
        $_POST['hospital_id'] = $_SESSION['FDASH_HOSPITAL_ID'];
        $result = insertOffer($_POST);
        echo $result;
        
    }
    else if($param=="edit"){
        $result = updateOffer($_POST);
        echo $result;
    }
    else if($param=="delete"){
        $result = deleteOffer($_POST['id']);
        echo $result;
    }
});

$app->post('/speciality/:param', function($param){
    if ($param == "get"){
        $result = getSpeciality();
        if ($result) {
            $out = json_encode($result);
            echo "{\"status\": \"OK\", \"message\": $out, \"code\": \"$param\" }";
        } else {
            echo "{\"status\": \"failed\", \"message\": \"No Speciality Found\" }";
        }
    }
});
$app->post('/procedure/:param', function($param){
    if ($param == "get"){
		$speciality_id = $_POST['speciality_id'];
        $result = getProcedurebySpecialityID($speciality_id);
        if ($result) {
            $out = json_encode($result);
            echo "{\"status\": \"OK\", \"message\": $out, \"code\": \"$speciality_id\" }";
        } else {
            echo "{\"status\": \"failed\", \"message\": \"No Procedure Found\" }";
        }
    }
});

$app->post('/notification/:param', function($param){
    session_start();
	if ($param == "new"){
	}
    else if ($param == "getall"){
        $result = getNotification();
        if ($result) {
            $out = json_encode($result);
            echo "{\"status\": \"OK\", \"message\": $out, \"code\": \"$param\" }";
        } else {
            echo "{\"status\": \"failed\", \"message\": \"No Notifications Found\" }";
        }    }

    else if ($param == "getallnotibyhospital"){
		if (!$_SESSION['FDASH_HOSPITAL_ID'] || $_SESSION['FDASH_HOSPITAL_ID']== null){
			echo "{\"status\": \"loggedout\", \"message\": \"Please login again!\" }";
			exit();
		}
        if ($_SESSION['FDASH_IS_MAIN'] == 1){
            $hospital_id = $_SESSION['FDASH_HOSPITAL_ID'];
            $result = getNotification($hospital_id);
        }
        else if ($_SESSION['FDASH_IS_HOSADMIN'] == 1){
            $branch_id = $_SESSION['FDASH_BRANCH_ID'];
            $result = getNotification(null, $branch_id);
        }
        else {
            $incharge_id = $_SESSION['FDASH_ID'];
            $result = getNotification(null, null, $incharge_id);
        }
        if ($result) {
            $out = json_encode($result);
            echo "{\"status\": \"OK\", \"message\": $out, \"code\": \"$param\" }";
        } else {
            echo "{\"status\": \"failed\", \"message\": \"No Notifications Found\" }";
        }
    }
    else if ($param == "getoffernotibyuser"){
		if ((!$_SESSION['LDASH_ID'] || $_SESSION['LDASH_ID']== null)&&(!$_SESSION['FDASH_ID'] || $_SESSION['FDASH_ID']== null)){
			echo "{\"status\": \"loggedout\", \"message\": \"Please login again!\" }";
			exit();
		}
        $incharge_id = (($_POST['usertype'] == 'ldashuser')? $_SESSION['LDASH_ID']:$_SESSION['FDASH_ID']);
        $result = getNotification(null, null, $incharge_id, 0);
        if ($result) {
            $out = json_encode($result);
            echo "{\"status\": \"OK\", \"message\": $out, \"code\": \"$param\" }";
        } else {
            echo "{\"status\": \"failed\", \"message\": \"No Notifications Found\" }";
        }
    }
    else if ($param == "getcampnotibyuser"){
		if ((!$_SESSION['LDASH_ID'] || $_SESSION['LDASH_ID']== null)&&(!$_SESSION['FDASH_ID'] || $_SESSION['FDASH_ID']== null)){
			echo "{\"status\": \"loggedout\", \"message\": \"Please login again!\" }";
			exit();
		}
        $incharge_id = (($_POST['usertype'] == 'ldashuser')? $_SESSION['LDASH_ID']:$_SESSION['FDASH_ID']);
        $result = getNotification(null, null, $incharge_id, 1);
        if ($result) {
            $out = json_encode($result);
            echo "{\"status\": \"OK\", \"message\": $out, \"code\": \"$param\" }";
        } else {
            echo "{\"status\": \"failed\", \"message\": \"No Notifications Found\" }";
        }
    }
	else if ($param == "call"){
		$posts = json_decode(file_get_contents("php://input"));
		$posts = stdToArray($posts);
		$id = $posts['id'];
		$contacted_date = date("Y-m-d H:i:s");
		$result = updateNoticificationContactedDate($id, $contacted_date);
		echo "{\"status\": \"OK\", \"message\": $result, \"code\": \"$param\" }";
	}
	else if ($param == "delete"){
		$posts = json_decode(file_get_contents("php://input"));
		$posts = stdToArray($posts);
		$id = $posts['id'];
		$result = deleteNotification($id);
		echo "{\"status\": \"OK\", \"message\": $result, \"code\": \"$param\" }";
	}
});

$app->post('/branch/:param', function($param){
    if ($param == "get"){
        $hospital_id = (array_key_exists('hospital_id', $_POST)? $_POST['hospital_id']:$_SESSION['FDASH_HOSPITAL_ID']);
        $result = getBranch($hospital_id);
        if ($result) {
            $out = json_encode($result);
            echo "{\"status\": \"OK\", \"message\": $out, \"code\": \"$param\" }";
        } else {
            echo "{\"status\": \"failed\", \"message\": \"No Branch Found\" }";
        }
    }
	else if ($param == "getfromDashboard"){
        session_start();
		if ($_SESSION['FDASH_IS_MAIN'] == 1){
			$hospital_id = $_SESSION['FDASH_HOSPITAL_ID'];
			$result = getBranch($hospital_id);
		}
		else {
			$branch_id = $_SESSION['FDASH_BRANCH_ID'];
			$result = getBranch(null, $branch_id);
		}
        if ($result) {
            $out = json_encode($result);
            echo "{\"status\": \"OK\", \"message\": $out, \"code\": \"$param\" }";
        } else {
            echo "{\"status\": \"failed\", \"message\": \"No Branch Found\" }";
        }
    }
    else if($param == "new"){
        session_start();
        $_POST['hospital_id'] = (array_key_exists('hospital_id', $_POST)? $_POST['hospital_id']:$_SESSION['FDASH_HOSPITAL_ID']);
        $_POST['is_primary'] = (array_key_exists('is_primary', $_POST)? $_POST['is_primary']:0);
        $result = addNewBranch($_POST);
        if ($result) {
            echo "{\"status\": \"OK\", \"message\": $result, \"code\": \"$param\" }";
        } else {
            echo "{\"status\": \"failed\", \"message\": \"Failed\" }";
        }
    }
    else if($param == "edit"){
        $_POST['is_primary'] = (array_key_exists('is_primary', $_POST)? $_POST['is_primary']:0);
        $result = editBranch($_POST);
        if ($result) {
            echo "{\"status\": \"OK\", \"message\": $result, \"code\": \"$param\" }";
        } else {
            echo "{\"status\": \"failed\", \"message\": \"Failed\" }";
        }
    }
    else if($param == "delete"){
        $result = deleteBranch($_POST['id']);
        if ($result) {
            echo "{\"status\": \"OK\", \"message\": $result, \"code\": \"$param\" }";
        } else {
            echo "{\"status\": \"failed\", \"message\": \"Failed\" }";
        }
    }
});
$app->post('/inclusion/:param', function($param){
    if ($param == "get"){
        session_start();
        $hospital_id = (array_key_exists('hospital_id', $_POST)? $_POST['hospital_id']:$_SESSION['FDASH_HOSPITAL_ID']);
        $result = getInclusion($hospital_id);
        if ($result) {
            $out = json_encode($result);
            echo "{\"status\": \"OK\", \"message\": $out, \"code\": \"$param\" }";
        } else {
            echo "{\"status\": \"failed\", \"message\": \"No Inclusion Found\" }";
        }
    }
    else if ($param == "new"){
        session_start();
        $_POST['hospital_id'] = (array_key_exists('hospital_id', $_POST)? $_POST['hospital_id']:$_SESSION['FDASH_HOSPITAL_ID']);
        $inclusion_id = insertInclusion($_POST);
		$result['id'] = $inclusion_id;
		$result['name'] = $_POST['inclusion_name'];
        if ($result) {
            $out = json_encode($result);
            echo "{\"status\": \"OK\", \"message\": $out, \"code\": \"$param\" }";
        } else {
            echo "{\"status\": \"failed\", \"message\": \"Failed\" }";
        }
    }
});


$app->post('/search/:param', function($param){
    switch ($param) {
        case 'offer':
			$searchkey = $_POST['search_key'];
            $city_id = (array_key_exists('city_id', $_POST)? $_POST['city_id']:"");
			$result = searchOffers($searchkey, $city_id);
            if ($result) {
                $out = json_encode($result);
                echo "{\"status\": \"OK\", \"data\": $out, \"param\": \"$param\" }";
            } else {
                echo "{\"status\": \"failed\", \"message\": \"No any Offers!\",\"param\": \"$param\" }";
            }
        break;
        case 'camp':
            $searchkey = $_POST['search_key'];
            $city_id = (array_key_exists('city_id', $_POST)? $_POST['city_id']:"");
            $result = searchCamps($searchkey, $city_id);
            if ($result) {
                $out = json_encode($result);
                echo "{\"status\": \"OK\", \"data\": $out, \"param\": \"$param\" }";
            } else {
                echo "{\"status\": \"failed\", \"message\": \"No any Camps!\",\"param\": \"$param\" }";
            }
        break;
        case 'doctor':
            $search_key = (array_key_exists('search_key', $_POST)? $_POST['search_key']:"");
            $city_id = (array_key_exists('city_id', $_POST)? $_POST['city_id']:"");
            $limit_from = (array_key_exists('limit_from', $_POST)? $_POST['limit_from']:0);
            
            $result = getDoctorInfo($search_key, $city_id, $limit_from);
            if ($result) {
                $out = json_encode($result);
                echo "{\"status\": \"OK\", \"data\": $out, \"param\": \"$param\" }";
            } else {
                echo "{\"status\": \"failed\", \"message\": \"No any Doctors!\",\"param\": \"$param\" }";
            }
        break;
        case 'hospital':
            $search_key = (array_key_exists('search_key', $_POST)? $_POST['search_key']:"");
            $city_id = (array_key_exists('city_id', $_POST)? $_POST['city_id']:"");
            $limit_from = (array_key_exists('limit_from', $_POST)? $_POST['limit_from']:0);
            $result = getSearchHospitalInfo($search_key, $city_id, $limit_from);
            if ($result) {
                $out = json_encode($result);
                echo "{\"status\": \"OK\", \"data\": $out, \"param\": \"$param\" }";
            } else {
                echo "{\"status\": \"failed\", \"message\": \"No any Hospitals!\",\"param\": \"$param\" }";
            }
        break;
        
        default:
            break;
    }
});
$app->post('/mainapp/:param', function($param){
    switch ($param) {
        case 'getOffers':
            $posts = json_decode(file_get_contents("php://input"));
            $posts = stdToArray($posts);
            $speciality_id = $posts['speciality_id'];
            $procedure_id = $posts['procedure_id'];
            $city_id = $posts['city_id'];
            $result = searchOffers(null, $city_id, $speciality_id, $procedure_id);
            if ($result) {
                $out = json_encode($result);
                echo "{\"status\": \"OK\", \"data\": $out}";
            } else {
                echo "{\"status\": \"failed\", \"data\": \"No any Offers!\"}";
            }
        break;
        case 'getCamps':
            $posts = json_decode(file_get_contents("php://input"));
            $posts = stdToArray($posts);
            $speciality_id = $posts['speciality_id'];
            $city_id = $posts['city_id'];
            $result = searchCamps(null, $city_id, $speciality_id);
            if ($result) {
                $out = json_encode($result);
                echo "{\"status\": \"OK\", \"data\": $out}";
            } else {
                echo "{\"status\": \"failed\", \"data\": \"No any Camps!\"}";
            }
        break;
        case 'getAllCamps':
            $result = searchCamps();
            if ($result) {
                $out = json_encode($result);
                echo "{\"status\": \"OK\", \"data\": $out}";
            } else {
                echo "{\"status\": \"failed\", \"data\": \"No any Camps!\"}";
            }
        break;
        case 'getDoctors':
            $posts = json_decode(file_get_contents("php://input"));
            $posts = stdToArray($posts);
            $searchkey = $posts['search_key'];
            $city_id = $posts['city_id'];
            $result = getSearchDoctorInfo($searchkey, $city_id);
            if ($result) {
                $out = json_encode($result);
                echo "{\"status\": \"OK\", \"data\": $out}";
            } else {
                echo "{\"status\": \"failed\", \"data\": \"No any Doctors!\"}";
            }
        break;
        case 'getHospitals':
            $posts = json_decode(file_get_contents("php://input"));
            $posts = stdToArray($posts);
            $searchkey = $posts['search_key'];
            $city_id = $posts['city_id'];
            $result = getSearchHospitalInfo($searchkey, $city_id);
            if ($result) {
                $out = json_encode($result);
                echo "{\"status\": \"OK\", \"data\": $out}";
            } else {
                echo "{\"status\": \"failed\", \"data\": \"No any Hospitals!\"}";
            }
        break;
        case 'getDoctorReviews':
            $posts = json_decode(file_get_contents("php://input"));
            $posts = stdToArray($posts);
            $doctor_id = $posts['doctor_id'];
            $result = getReview('lm_doctor_review', $doctor_id);
            if ($result) {
                $out = json_encode($result);
                echo "{\"status\": \"OK\", \"data\": $out}";
            } else {
                echo "{\"status\": \"failed\", \"data\": \"No any Reviews!\"}";
            }
        break;
        case 'getHospitalReviews':
            $posts = json_decode(file_get_contents("php://input"));
            $posts = stdToArray($posts);
            $hospital_id = $posts['hospital_id'];
            $result = getReview('lm_hospital_review', $hospital_id);
            if ($result) {
                $out = json_encode($result);
                echo "{\"status\": \"OK\", \"data\": $out}";
            } else {
                echo "{\"status\": \"failed\", \"data\": \"No any Reviews!\"}";
            }
        break;
        case 'signupOffer':

            $posts_json = json_decode(file_get_contents("php://input"));
            $posts = stdToArray($posts_json);
            if (checkCode($posts['verification_code'])){
                $posts['signup_method'] = "App Offer";
                $result = insertPatientInfo($posts);
                $noti_array = array();
                $noti_array['patient_id'] = $result;
                $noti_array['offer_camp_id'] = $posts['offer_camp_id'];
                $noti_array['received_date'] = date('Y-m-d H:i:s');
                $noti_array['create_date'] = date('Y-m-d H:i:s');
                $noti_array['type'] = 0;
                $offer_info = getOfferbyID($posts['offer_camp_id']);
                $noti_array['hospital_id'] = $offer_info['hospital_id'];
                $noti_array['branch_id'] = $offer_info['branch_ids'][0];
                $noti_array['incharge_id'] = $offer_info['incharge_id'];
                $noti_result = addNewNotification($noti_array);
                $offer_enquiry = increaseOfferEnquiryCount($posts['offer_camp_id']);

                $push_msg = "New Signup for ".$offer_info['offer_name'];
                // Send push notification to Marketing Mobile App
                $user_id = $noti_array['incharge_id'];
                $device_token_array = getToken($user_id);
                if ($device_token_array){
                    // send_PushNotification($device_token_array, $push_msg);
                }
                if ($result) {
                    echo "{\"status\": \"OK\", \"data\": \"Thank you for contacting us. We will contact you at a moment.\"}";
                } else {
                    echo "{\"status\": \"failed\", \"data\": \"Sorry, failed!\"}";
                }
            }
            else {
                echo "{\"status\": \"invalid\", \"data\": \"Invalid code. Please try to again.\"}";
            }
        break;
        case 'signupCamp':
            $posts_json = json_decode(file_get_contents("php://input"));
            $posts = stdToArray($posts_json);
            if (checkCode($posts['verification_code'])){
                $posts['signup_method'] = "App Camp";
                $result = insertPatientInfo($posts);
                $noti_array['patient_id'] = $result;
                $noti_array['offer_camp_id'] = $posts['offer_camp_id'];
                $noti_array['received_date'] = date('Y-m-d H:i:s');
                $noti_array['create_date'] = date('Y-m-d H:i:s');
                $noti_array['type'] = 1;
                $camp_info = getCampbyID($posts['offer_camp_id']);
                $noti_array['hospital_id'] = $camp_info['hospital_id'];
                $noti_array['branch_id'] = $camp_info['branch_id'];
                $noti_array['incharge_id'] = $camp_info['incharge_id'];
                $noti_result = addNewNotification($noti_array);
                $offer_enquiry = increaseCampEnquiryCount($posts['offer_camp_id']);

                $push_msg = "New Signup for ".$camp_info['camp_name'];
                $user_id = $noti_array['incharge_id'];
                $device_token_array = getToken($user_id);
                if ($device_token_array){
                    // send_PushNotification($device_token_array, $push_msg);
                }
                if ($result) {
                    echo "{\"status\": \"OK\", \"data\": \"Thank you for contacting us. We will contact you at a moment.\"}";
                } else {
                    echo "{\"status\": \"failed\", \"data\": \"Sorry, failed!\"}";
                }
            }
            else {
                echo "{\"status\": \"invalid\", \"data\": \"Invalid code. Please try to again.\"}";
            }
        break;
        case 'addDoctorReview':
            $posts_json = json_decode(file_get_contents("php://input"));
            $posts = stdToArray($posts_json);
            if (checkCode($posts['verification_code'])){
                $result = addReview('lm_doctor_review', $posts);
                updateDoctorScore($posts['receiver_id']);
                if ($result) {
                    echo "{\"status\": \"OK\", \"data\": \"Post a review successfully.\"}";
                } else {
                    echo "{\"status\": \"failed\", \"data\": \"Sorry, failed!\"}";
                }
            }
            else {
                echo "{\"status\": \"failed\", \"data\": \"Invalid code. Please try to again.\"}";
            }
        break;
        case 'addHospitalReview':
            $posts_json = json_decode(file_get_contents("php://input"));
            $posts = stdToArray($posts_json);
            if (checkCode($posts['verification_code'])){
                $result = addReview('lm_hospital_review', $posts);
                updateHospitalScore($posts['receiver_id']);
                if ($result) {
                    echo "{\"status\": \"OK\", \"data\": \"Post a review successfully.\"}";
                } else {
                    echo "{\"status\": \"failed\", \"data\": \"Sorry, failed!\"}";
                }
            }
            else {
                echo "{\"status\": \"failed\", \"data\": \"Invalid code. Please try to again.\"}";
            }
        break;
        case 'verifySMS':
            // $myCode = genCode();
            // $posts_json = json_decode(file_get_contents("php://input"));
            // $posts = stdToArray($posts_json);
            // $myNum = $posts['phone_number'];
            // $result = sendSMS($myNum, $myCode);
            $result = true;
            if ($result){
                echo "{\"status\": \"OK\", \"data\": \"Please check your phone for the verification code\"}";
            }
            else {
                echo "{\"status\": \"failed\", \"data\": \"Please check if your phone number is valid\"}";
            }
        break;
        case 'verifyEmail':
            // $myCode = genCode();
            // $posts_json = json_decode(file_get_contents("php://input"));
            // $posts = stdToArray($posts_json);
            // $myEmail = $posts['email_address'];
            // $myName = $posts['myName'];
            // $result = sendEmail($myEmail, $myName, $myCode);
            echo "{\"status\": \"OK\", \"data\": \"Please check your email for the verification code\"}";
        break;
    }
});
$app->post('/patient/:param', function($param){
    switch ($param) {
        case 'new':
            $result = insertPatientInfo($_POST);
            if (strpos($_POST['signup_method'],'Offer') !== false && strpos($_POST['signup_method'],'Camp') !== false) {
                echo $result;
                exit();
            }
			$noti_array = array();
			if (strpos($_POST['signup_method'],'Offer') !== false) {
				$noti_array['patient_id'] = $result;
				$noti_array['offer_camp_id'] = $_POST['offer_camp_id'];
				$noti_array['received_date'] = date('Y-m-d H:i:s');
				$noti_array['create_date'] = date('Y-m-d H:i:s');
				$noti_array['type'] = 0;
				$offer_info = getOfferbyID($_POST['offer_camp_id']);
                $noti_array['hospital_id'] = $offer_info['hospital_id'];
				$noti_array['branch_id'] = $offer_info['branch_ids'][0];
				$noti_array['incharge_id'] = $offer_info['incharge_id'];
				$noti_result = addNewNotification($noti_array);
                $offer_enquiry = increaseOfferEnquiryCount($_POST['offer_camp_id']);

				$push_msg = "New Signup for ".$offer_info['offer_name'];
			}
			else if (strpos($_POST['signup_method'],'Camp') !== false){
				$noti_array['patient_id'] = $result;
				$noti_array['offer_camp_id'] = $_POST['offer_camp_id'];
				$noti_array['received_date'] = date('Y-m-d H:i:s');
				$noti_array['create_date'] = date('Y-m-d H:i:s');
				$noti_array['type'] = 1;
				$camp_info = getCampbyID($_POST['offer_camp_id']);
                $noti_array['hospital_id'] = $camp_info['hospital_id'];
				$noti_array['branch_id'] = $camp_info['branch_id'];
				$noti_array['incharge_id'] = $camp_info['incharge_id'];
				$noti_result = addNewNotification($noti_array);
                $offer_enquiry = increaseCampEnquiryCount($_POST['offer_camp_id']);

				$push_msg = "New Signup for ".$camp_info['camp_name'];
			}

			if (strpos($_POST['signup_method'],'Offer') !== false || strpos($_POST['signup_method'],'Camp') !== false){
				// Send push notification to Marketing Mobile App
				$user_id = $noti_array['incharge_id'];
				$device_token_array = getToken($user_id);
				if ($device_token_array){
					foreach ($device_token_array as $key=>$value){
						$platform = $value['platform'];
						$token = $value['token'];
						switch ($platform){
							case "1": // Android
								$pushMessage = array(
									'vibrate'	=> 1,
									'sound'		=> 'default',
									'type' => 1,
									'msg' 	=> $push_msg,
								);
								$registration_ids = array();
								$registration_ids[0] = $token;
								send_Android_PushNotification($registration_ids, $pushMessage);
							break;
							case "2": // iOS
								send_iOS_PushNotification($token, $push_msg, 1, 'default');
							break;
						}
					}
				}
			}
            echo $result;
        break;
        case 'getall':
            // echo json_encode($_POST);
            $result = getPatient();
            if ($result) {
                $out = json_encode($result);
                echo "{\"status\": \"OK\", \"message\": $out, \"param\": \"$param\" }";
            } else {
                echo "{\"status\": \"failed\", \"message\": \"No any Patients!\",\"param\": \"$param\" }";
            }
        break;
        
        default:
            # code...
            break;
    }
});
$app->post('/review/:param', function($param){
    switch ($param) {
        case 'adddoctorreview':
			if (checkCode($_POST['verificaton_code'])){
				$result = addReview('lm_doctor_review', $_POST);
                updateDoctorScore($_POST['receiver_id']);
				echo "{\"status\": \"OK\", \"message\": $result, \"param\": \"$param\" }";
			}
			else {
				echo "{\"status\": \"failed\", \"message\": \"Invalid code. Please try to again.\", \"param\": \"$param\" }";
			}
        break;
        case 'addhospitalreview':
			if (checkCode($_POST['verificaton_code'])){
				$result = addReview('lm_hospital_review', $_POST);
                updateHospitalScore($_POST['receiver_id']);
				echo "{\"status\": \"OK\", \"message\": $result, \"param\": \"$param\" }";
			}
			else {
				echo "{\"status\": \"failed\", \"message\": \"Invalid code. Please try to again.\", \"param\": \"$param\" }";
			}
        break;
        case 'getdoctorreview':
            $doctor_id = (array_key_exists('doctor_id', $_POST)?$_POST['doctor_id']:null);
            $result = getReview('lm_doctor_review', $doctor_id);
            if ($result) {
                $out = json_encode($result);
                echo "{\"status\": \"OK\", \"message\": $out, \"param\": \"$param\" }";
            } else {
                echo "{\"status\": \"failed\", \"message\": \"No any Reviews!\",\"param\": \"$param\" }";
            }
        break;
        case 'gethospitalreview':
            $hospital_id = (array_key_exists('hospital_id', $_POST)?$_POST['hospital_id']:null);
            $result = getReview('lm_hospital_review', $hospital_id);
            if ($result) {
                $out = json_encode($result);
                echo "{\"status\": \"OK\", \"message\": $out, \"param\": \"$param\" }";
            } else {
                echo "{\"status\": \"failed\", \"message\": \"No any Reviews!\",\"param\": \"$param\" }";
            }
        break;
        
        default:
            break;
    }
});

$app->post('/verify/:param', function($param){
    switch ($param) {
        case 'sms':
            $myCode = genCode();
			$myNum = $_POST['phone_number'];
            $result = sendSMS($myNum, $myCode);
            if ($result){
                echo "{\"status\": \"OK\", \"message\": \"Please check your phone for the verification code\"}";
            }
            else {
                echo "{\"status\": \"failed\", \"message\": \"Please check if your phone number is valid\"}";
            }
        break;
        case 'email':
            $myCode = genCode();
			$myEmail = $_POST['email_address'];
			$myName = $_POST['myName'];
            $result = sendEmail($myEmail, $myName, $myCode);
            echo "{\"status\": \"OK\", \"message\": \"Please check your email for the verification code\"}";
            // echo "{\"status\": \"OK\", \"message\": \"Please check your email for the verification code\", \"code\": \"$result\" }";
        break;
        
        default:
            break;
    }

});

$app->post('/doctor/:param', function($param){
	switch ($param){
		case "new":
			$result = addNewDoctor($_POST);
			if ($result) {
				echo "{\"status\": \"OK\", \"message\": $result, \"code\": \"$param\" }";
			} else {
				echo "{\"status\": \"failed\", \"message\": \"Failed\" }";
			}
		break;
	}
});

$app->post('/city/:param', function($param){
	switch ($param){
		case 'getall':
			$result = getAllCity();
			if ($result) {
				$out = json_encode($result);
				echo "{\"status\": \"OK\", \"message\": $out, \"code\": \"$param\" }";
			} else {
				echo "{\"status\": \"failed\", \"message\": \"No any Cities\" }";
			}
		break;
	}
});

$app->post('/subregion/:param', function($param){
    switch ($param){
        case 'getbycity':
            $city_id = $_POST['city_id'];
            $result = getSubRegionbyCityID($city_id);
//            $result = getAllCity();
            if ($result) {
                $out = json_encode($result);
                echo "{\"status\": \"OK\", \"message\": $out, \"code\": \"$param\" }";
            } else {
                echo "{\"status\": \"failed\", \"message\": \"No any Cities\" }";
            }
        break;
    }
});

$app->post('/marketingapp/:param', function($param){
	$posts_json = json_decode(file_get_contents("php://input"));
	$posts = stdToArray($posts_json);
	switch ($param){
		case 'Login':
			$tb_name = "lm_dash_user";
			$username = $posts['phone'];
			$password = base64_encode($posts['password']);
			$where = " phonenum='$username' AND userpass='$password' AND is_marketappuser='1' AND is_delete='0'";
			$userinfo = getUsers($tb_name, $where);
			if ($userinfo) {
				$out = $userinfo[0]['id'];
				echo "{\"code\": 0, \"msg\": null, \"data\": {\"id\":$out,\"token\":$out}}";
			} else {
				echo "{\"code\": 3, \"msg\": \"No User Found\", \"data\": null}";
			}
		break;
		case 'LoginByToken':
			$tb_name = "lm_dash_user";
			$id = $posts['token'];
			$where = " id='$id' AND is_marketappuser='1' AND is_delete='0'";
			$userinfo = getUsers($tb_name, $where);
			if ($userinfo) {
				$out = $userinfo[0]['id'];
				echo "{\"code\": 0, \"msg\": null, \"data\": {\"id\":$out,\"token\":$out}}";
			} else {
				echo "{\"code\": 3, \"msg\": \"No User Found\", \"data\": null}";
			}
		break;
		case 'UpdateNotifToken':
			$user_id = $posts['id'];
			$platform = $posts['platform'];
			$token = $posts['token'];
			/*$user_id = $_POST['id'];
			$platform = $_POST['platform'];
			$token = $_POST['token'];*/
			if ($user_id != 0){
				$sel_result = getToken($user_id, $platform);
				if ($sel_result){
					$id = $sel_result[0]['id'];
					$device_id = updateToken($id, $token);
				}
				else {
					$device_id = addNewToken($user_id, $token, $platform);
				}
			}

			echo "{\"code\": 0, \"msg\": null, \"data\": null}";
		break;
		case 'GetNotification':
			$incharge_id = $posts['id'];
			$type = $posts['type'];
			if ($type){ // Offer Notification
				$result = getNotificationfromApp(null, $incharge_id, 0);
			}
			else { // Camp Notification
				$result = getNotificationfromApp(null, $incharge_id, 1);
			}
			if ($result) {
				$out = json_encode($result);
				echo "{\"code\": 0, \"msg\": null, \"data\": $out}";
			} else {
				echo "{\"code\": 0, \"msg\": null, \"data\": []}";
			}
		break;
		case 'DelNotification':
			$ids = $posts['ids'];
			//$ids_array = json_decode($ids);
			foreach ($ids as $id){
				$result = deleteNotification($id);
			}
			echo "{\"code\": 0, \"msg\": null, \"data\": null}";
		break;
		case 'Call':
			$id = $posts['id'];
			$contacted_date = date("Y-m-d H:i:s");
			$result = updateNoticificationContactedDate($id, $contacted_date);
			echo "{\"code\": 0, \"msg\": null, \"data\": null}";
		break;
	}
});

$app->post('/session/:param', function($param){
	session_start();
	switch ($param){
		case "getFdashID":
			if (isset($_SESSION)){
				$result = (array_key_exists('FDASH_ID', $_SESSION)? $_SESSION['FDASH_ID']:'0');
			}
			else {
				$result = '0';
			}
			echo "{\"status\": \"OK\", \"message\": \"$result\", \"code\": \"$param\" }";
		break;
	}
});

$app->get('/offer/:param', function ($param) {
	if ($param=='getall'){
		$result = getActiveOffers();
		echo json_encode($result);
		
	}
});
$app->get('/camp/:param', function ($param) {
	if ($param=='getall'){
		$result = getActiveCamps();
		echo json_encode($result);
		
	}
});
$app->get('/doctor/:param', function ($param) {
	if ($param=='getall'){
		$result = getDoctorInfo();
		echo json_encode($result);
		
	}
});
$app->get('/hospital/:param', function ($param) {
	if ($param=='getall'){
		$result = getHospitalInfo();
		echo json_encode($result);
		
	}
});
$app->get('/speciality/:param', function ($param) {
    if ($param=='getall'){
        $result = getSpeciality();
        echo json_encode($result);
    }
});
$app->get('/procedure/:param', function ($param) {
    if ($param=='getall'){
        $result = getProcedure();
        echo json_encode($result);
    }
});

$app->run();