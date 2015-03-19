<?php
	require_once("absencelib.php");
	
	$crud = new AbsenceCrud();
	$crud->allowAdd = false;
	$crud->run();
?>