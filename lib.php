<?php
// This script and data application were generated by AppGini 4.52
// Download AppGini for free from http://www.bigprof.com/appgini/download/


error_reporting(E_ALL ^ E_NOTICE);
if(function_exists('set_magic_quotes_runtime')) @set_magic_quotes_runtime(0);

$d=dirname(__FILE__);
if(!@is_file("$d/config.php")){
	echo StyleSheet() . "\n\n<div class=Error>";
	echo $Translation["error:"].' ';
	echo $Translation["if you haven't set up"];
	echo "</div>";
	exit;
}

include("$d/config.php");
include("$d/incCommon.php");
include("$d/HtmlFilter.php");
include("$d/datalist.php");
function sql($statment,$assoc=1){

	global $Translation;
	global $dbServer, $dbUsername, $dbPassword, $dbDatabase;

		/****** Connect to MySQL ******/
		if(!extension_loaded('mysql')){
			echo "<div class=Error>ERROR: PHP is not configured to connect to MySQL on this machine. Please see <a href=http://www.php.net/manual/en/ref.mysql.php>this page</a> for help on how to configure MySQL.</div>";
			exit;
		}

		if(!mysql_connect($dbServer, $dbUsername, $dbPassword)){
			echo StyleSheet() . "\n\n<div class=Error>";
			echo $Translation["error:"] . mysql_error();
			echo "</div>";
			exit;
		}

		/****** Connection Charset ********/
		@mysql_query("SET NAMES 'latin1'");

		/****** Select DB ********/
		if(!mysql_select_db($dbDatabase)){
			echo StyleSheet() . "\n\n<div class=Error>";
			echo $Translation["error:"] . mysql_error();
			echo $Translation["if you haven't set up"];
			echo "</div>";
			exit;
		}

	if(!$result = @mysql_query($statment)){
		if(!stristr($statment, "show columns")){
			// retrieve error codes
			$errorNum=mysql_errno();
			$errorMsg=mysql_error();

			echo StyleSheet() . "\n\n<div class=Error>";
			echo "<br /><b>" . $Translation["error:"] . "</b> ".htmlspecialchars($errorMsg)."\n\n<!--\n" . $Translation["query:"] . "\n $statment\n-->\n\n";

			if(stristr($statment, "select ")) echo ".<br />" . $Translation["if you haven't set up"];
			echo "</div>";
			echo "<a href=\"javascript:history.go(-1);\">" . $Translation["< back"] . "</a>";
			exit;
		}
	}

	return $result;
}

function NavMenus(){
	global $Translation;

	$t = time();
	$menu  = "<select name=nav_menu onChange='window.location=document.myform.nav_menu.options[document.myform.nav_menu.selectedIndex].value;'>";
	$menu .= "<option value='#' class=SelectedOption style='color:black;'>" . $Translation["select a table"] . "</option>";
	$menu .= "<option value='index.php' class=SelectedOption style='color:black;'>" . $Translation["homepage"] . "</option>";
	if(getLoggedAdmin()){
		$menu .= "<option value='admin/' class=SelectedOption style='color:red;'>" . $Translation['admin area'] . "</option>";
	}
	$arrTables=getTableList();
	if(is_array($arrTables)){
		foreach($arrTables as $tn=>$tc){
			$tChk=array_search($tn, array());
			if($tChk!==false && $tChk!==null){
				$searchFirst='&Filter_x=1';
			}else{
				$searchFirst='';
			}
			$menu .= "<option value='".$tn."_view.php?t=$t$searchFirst' class=SelectedOption>$tc[0]</option>";
		}
	}
	$menu .= "</select>";
	return $menu;
}

function StyleSheet(){
	return '<link rel="stylesheet" type="text/css" href="style.css">';
}

function getUploadDir($dir){
	global $Translation;

	if($dir==""){
		$dir=$Translation['ImageFolder'];
	}

	if(substr($dir, -1)!="/"){
		$dir.="/";
	}

	return $dir;
}

function PrepareUploadedFile($FieldName, $MaxSize, $FileTypes='jpg|jpeg|gif|png', $NoRename=false, $dir=""){
	global $Translation;
	$f = $_FILES[$FieldName];

	$dir=getUploadDir($dir);

	if($f['error'] != 4 && $f['name']!=''){
		if($f['size']>$MaxSize || $f['error']){
			echo StyleSheet()."<div class=Error>".str_replace("<MaxSize>", intval($MaxSize/1024), $Translation['file too large']).". <a href=".$_SERVER['HTTP_REFERER'].">".$Translation["< back"]."</a>.</div>";
			exit;
		}
		if(!preg_match('/\.('.$FileTypes.')$/i', $f['name'], $ft)){
			echo StyleSheet()."<div class=Error>".str_replace("<FileTypes>", str_replace('|', ', ', $FileTypes), $Translation['invalid file type']).". <a href=".$_SERVER['HTTP_REFERER'].">".$Translation["< back"]."</a>.</div>";
			exit;
		}

		if($NoRename){
			$n  = str_replace(' ', '_', $f['name']);
		}else{
			$n  = microtime();
			$n  = str_replace(' ', '_', $n);
			$n  = str_replace('0.', '', $n);
			$n .= $ft[0];
		}

		if(!file_exists($dir)){
			@mkdir($dir, 0777);
		}

		if(!@move_uploaded_file($f['tmp_name'], $dir . $n)){
			echo StyleSheet()."<div class=Error>Error: Couldn't save the uploaded file. Try chmoding the upload folder '".$dir."' to 777. <a href=".$_SERVER['HTTP_REFERER'].">".$Translation["< back"]."</a>.</div>";
			exit;
		}else{
			@chmod($dir.$n, 0666);
			return $n;
		}
	}
	return "";
}
?>