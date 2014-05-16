<?php
// Only report errors and no NOTICES, DEPRECATED
// and STRICT warnings.
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);

// Set correct header
header("Content-Type: text/json");

require("config.php");
if($config["AllowExternalAccess"]) {
	header("Access-Control-Allow-Origin: *");
}

// Calculate cache to seconds
if($config["cacheminutes"] < 1) {
	$Cache = false;
}else{
	$cachetime = $config["cacheminutes"] * 60;
}

$flag = true;
$get = trim($_GET["q"]);
if(empty($get)) die("ERROR");
if(!file_exists("cache_".$get.".txt")) {
	file_put_contents("cache_".$get.".txt", "");
	$flag = false;
}

// Check if cache needs to be updated
if((filemtime("cache_".$get.".txt") + $cachetime) > time() && $flag) {
	// Output the content of the cache
	echo file_get_contents("cache_".$get.".txt");

}else{
	// Save memInfo output to variable
	$time = time();

	if($get == "NetSpeedUp") {
		$data["value"]      = getSpeed("tx");

	}else if($get == "NetSpeedDown") {
		$data["value"]      = getSpeed("rx");

	}else if($get == "NetExtIp") {
		$data["value"] = getExternalIP();

	} else if($get == "all") {

		$memInfo = getMemInfo();

		$i = 0;
		foreach($config["Software"] as $key => $value) {
			$name = array_keys($value);
			$name = $name[0];
			$process = $value[$name];
			$data[$key] = $name." ".CheckRunningProcess($process);
			$i++;
		}

		$data["HW_OS"]         = getOperatingSystem();
		$data["HW_KERNEL"]     = getKernel();
		$data["HW_CPU"]        = getCPU();

		$data["HW_RAM_USED"]   = getSize($memInfo["MemTotal"] - $memInfo["MemFree"]);
		$data["HW_RAM_AVAIL"]  = getSize($memInfo["MemTotal"]);
		$data["HW_RAM_PRNT"]   = round(((100 * ($memInfo["MemTotal"] - $memInfo["MemFree"])) / $memInfo["MemTotal"]), 1);

		$data["HW_SWAP_USED"]  = getSize($memInfo["SwapTotal"] - $memInfo["SwapFree"]);
		$data["HW_SWAP_AVAIL"] = getSize($memInfo["SwapTotal"]);
		$data["HW_SWAP_PRNT"]  = round(((100 * ($memInfo["SwapTotal"] - $memInfo["SwapFree"])) / $memInfo["SwapTotal"]), 1);

		$data["NET_IP"]        = getIP();

	}else{
		die("ERROR1");
	}

	$data["CACHE"]     = $cachetime; 
	$data["TIMESTAMP"] = $time;

	// Encode array to json
	$data = json_encode($data);

	// Save to cache
	file_put_contents("cache_".$get.".txt", $data);
	echo $data;
}

// Functions
//
//  Normally no need to change!!
//
function CheckRunningProcess($prc) {
	// Check if process is running
	$o = shell_exec("pidof ".$prc);
	if(empty($o)) {
		if(CheckInitdProcess($prc) == false) {
			return "R0";
		}else{
			return "R1";
		}
	 }else{
		return "R1";
	}
}

function CheckInitdProcess($prc) {
	// Check if process is running
	$o = shell_exec("/etc/init.d/".$prc." status");
	if(strpos($o, "running") !== false) {
		return false;
	 }else{
		return true;
	}
}

function getOperatingSystem() {
	// Read out current operating system
	// Requires 'lsb-release' package.
	$o = shell_exec("lsb_release -d");
	$o = explode(":", $o);
	return trim($o[1]);
}

function getCPU() {
	// Get current CPU usage
	// and parse it
	$o = shell_exec("cat /proc/loadavg");
	$o = explode(" ", $o);
	return $o[0]." ".$o[1]." ".$o[2];
}

function getKernel() {
	// Read out current kernel
	$o = shell_exec("uname -r");
	return $o;
}

function getMemInfo() {
	// Load and parse /proc/meminfo to get
	// the RAM and SWAP usage. All other values
	// will be saved but not used in this script.
	$o = shell_exec("cat /proc/meminfo");
	$o = explode("\n", $o);
	foreach($o as $i) {
		if(!empty($i)) {
			$i = explode(":", $i);
			$t = explode(" ", trim($i[1]));
			$memInfo[$i[0]] = ($t[0] * 1024);
		}
	}
	return $memInfo;
}

function getIP() {
	return $_SERVER["SERVER_ADDR"];
}

// Requires shell_exec
function getExternalIP() {
	$externalContent = file_get_contents('http://checkip.dyndns.com/');
	preg_match('/Current IP Address: ([\[\]:.[0-9a-fA-F]+)</', $externalContent, $m);
	return $m[1];
}

function getSpeed($r) {
	global $config;
	// Calculate the average download and
	// upload speed within 3 seconds.
	// Required line in sudo configuration:
	//   www-data        ALL=NOPASSWD: /sbin/ifconfig eth0
	if($r == "rx") {
		$cmd = "sudo ifconfig ".$config["interface"]." | grep 'RX bytes' | cut -d: -f2 | awk '{ print $1 }'";
	 }else if($r == "tx") {
		$cmd = "sudo ifconfig ".$config["interface"]." | grep 'TX bytes' | cut -d: -f3 | awk '{ print $1 }'";
	 }else{
		die("ERROR2");
	}
	// Executing and get total send/received
	// bytes the next three seconds
	$o1 = shell_exec($cmd);
	sleep(1);
	$o2 = shell_exec($cmd);
	sleep(1);
	$o3 = shell_exec($cmd);
	sleep(1);
	$o4 = shell_exec($cmd);
	// Calculate differences
	$o1 = $o2 - $o1;
	$o2 = $o3 - $o2;
	$o3 = $o4 - $o3;
	// Calc average
	$o = (($o1 + $o2 + $o3) / 3);
	return getSize($o)."/s";
}

function getSize($size) {
	// Calculate sizes for better display
	$round = 2;
	if ($size<=1024) $size = $size." Byte";
	else if ($size<=1024000) $size = round($size/1024,$round)." KB";
	else if ($size<=1048576000) $size = round($size/1048576,$round)." MB";
	else if ($size<=1073741824000) $size = round($size/1073741824,$round)." GB";
	$size = explode(" ", $size);
	$size = number_format($size[0], $round, '.', '')." ".$size[1];
	return $size;
}

?>