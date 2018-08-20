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

namespace Rise\Request\Upload {

function move_uploaded_file($from, $to) {
	return rename($from, $to);
}

}

namespace Rise\Template\Blocks {

function realpath($path) {
	if (basename($path) === 'not.found.phtml') { // Hard code a "not found" filename
		return false;
	}

	$protocol = 'vfs://';
	$path = substr($path, strlen($protocol));
	$root = ($path[0] === '/') ? '/' : '';
	$segments = explode('/', trim($path, '/'));
	$ret = array();

	foreach($segments as $segment){
		if (($segment == '.') || strlen($segment) === 0) {
			continue;
		}
		if ($segment == '..') {
			array_pop($ret);
		} else {
			array_push($ret, $segment);
		}
	}

	return $protocol . $root . implode('/', $ret);
}

}
