<?php
namespace Rise {

function session_start($options = []) {
	$_SESSION = [];
	$GLOBALS['SID'] = hash("sha512", mt_rand(0, mt_getrandmax()));
	return true;
}

function session_destroy() {
	unset($_SESSION);
	unset($GLOBALS['SID']);
	return true;
}

function session_regenerate_id() {
	$GLOBALS['SID'] = hash("sha512", mt_rand(0, mt_getrandmax()));
	return true;
}

}

namespace Rise\Http\Request\Upload {

function move_uploaded_file($from, $to) {
	return rename($from, $to);
}

}
