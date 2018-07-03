<?php
namespace Rise\Http\Request\Upload {

function move_uploaded_file($from, $to) {
	return rename($from, $to);
}

}
