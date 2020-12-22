<?php
header('Content-Type: text/html; charset=utf-8');
session_start();


# DEFINE CONST
$current_year = date("Y");
$current_month = date("m");
$current_day = date("d");
$current_usecase = "ZMDO";
$xml_template_file_path = "template.xml";
$xml_template_content = file_get_contents($xml_template_file_path);
$herstellerid_test = "74931";
$errors = array();

$path_to_php_script_folder = "/var/www/html//backend/ustva";
$path_to_xml_folder = "/var/www/html//backend/shared/xml";
$path_to_pdf_folder = "/var/www/html//backend/shared/pdf";
$path_to_eric_script_folder = '/var/www/html//ERiC-30.2.8.0/Linux-x86_64/usecases/ustva';

        
$path_to_base_folder = "/var/www/html";
$base_url = "";

$prefix_test = "TEST_ZM_";
$prefix_prod = "PROTOCOL_ZM_";

$default_eric_output_filename = "ericprint.pdf";


$path_to_certificate_file = "/home/ubuntu/.pfx";
$password = "";
$herstellerid = "";



if(empty($_POST["payload"]))
{
	$errors[] = "FATAL: No payload provided!";
	die("no payload provided");
}

$payload = trim($_POST["payload"]);
$payload = json_decode($payload, true);


if(!empty($payload["month"]))
{	
	$external_month = trim($payload["month"]);
        $strlen = strlen($external_month);
	if($strlen != 2)
        {
                die("month should have 2 digits 01,02,..,12!");
	}

	$external_zeitraum = $external_month + 20;
	        $anzeige = "true";
}


if(!empty($payload["quarter"]))
{
	$external_quarter = trim($payload["quarter"]);

	$allowed = array("1", "2", "3", "4");
	$strlen = strlen($external_quarter);
        if(!in_array($external_quarter, $allowed))
        {
                die($external_quarter."..quarter should have 1 digit 1,2,3,4");
        }
	$external_zeitraum = $external_quarter;
	$anzeige = "false";

}

if(!empty($payload["quarter"]) && !empty($payload["month"]))
	die("Either month or quarter should be passed, not both!");


if(!empty($payload["year"]))
{
	$external_year = trim($payload["year"]);
	$strlen = strlen($external_year);
	if($strlen != 4)
	{
		$errors[] = "FATAL: year should have 4 digits e.g. 2020!";
		die("year should have 4 digits e.g. 2020!");
	}
}


if(!empty($payload["land"]))
{
        $external_land = trim($payload["land"]);
	if($external_land == "china" || $external_land == "China")
		$external_land = "CN";
	elseif($external_land == "Deutschland" || $external_land == "Germany" )
		$external_land = "DE";
	 elseif($external_land == "France" || $external_land == "Frankreich" )
		 $external_land = "FR"; 
	 elseif($external_land == "Italien" || $external_land == "Italy" )
		$external_land = "IT";
	 elseif($external_land == "UK" || $external_land == "England" || $external_land == "United Kingdom" )
		 $external_land = "BR";
	 elseif($external_land == "USA" || $external_land == "United States" )
		 $external_land = "US";
	 
        
}

if(!empty($payload["ustidnr"]))
{

        $external_ustidnr = trim($payload["ustidnr"]);
	if(strpos($external_ustidnr, "DE") !== 0)
		die("UStID should start with DE!");
}
if(!empty($payload["name"]))
{
        $external_name = trim($payload["name"]);
}
if(!empty($payload["address"]))
{
        $external_address = trim($payload["address"]);
}
if(!empty($payload["ort"]))
{
        $external_ort = trim($payload["ort"]);
}
if(!empty($payload["plz"]))
{
        $external_plz = trim($payload["plz"]);
}

if(!empty($payload["plz_ausland"]))
{
        $external_plz = trim($payload["plz_ausland"]);
}









if(!empty($payload["entries"]))
{
        $external_entries = trim($payload["entries"]);
}







$new_xml = $xml_template_content;


$nutzdatenticket = rand(100000000, 999999999);
$new_xml = str_replace("##herstellerid##", $herstellerid, $new_xml);
$new_xml = str_replace("##nutzdatenticket##", $nutzdatenticket, $new_xml);

$new_xml = str_replace("##ustidnr##", $external_ustidnr, $new_xml);

$new_xml = str_replace("##empfaenger##", "BF", $new_xml);



$new_xml = str_replace("##jahr##", $external_year, $new_xml);
$new_xml = str_replace("##zeitraum##", $external_zeitraum, $new_xml);

$bezeichnung_name_arr = format_name($external_name);
$new_xml = str_replace("##bezeichnung##", $bezeichnung_name_arr["bezeichnung"], $new_xml);
$new_xml = str_replace("##name##", $bezeichnung_name_arr["name"], $new_xml);



$external_address = format_address($external_address);
$new_xml = str_replace("##strasse##", $external_address, $new_xml);


$new_xml = str_replace("##hausnr##", "", $new_xml);
$new_xml = str_replace("##ort##", $external_ort, $new_xml);
$new_xml = str_replace("##plz##", $external_plz, $new_xml);

$new_xml = str_replace("##land##", $external_land, $new_xml);
$new_xml = str_replace("##telefon##", "", $new_xml);
$new_xml = str_replace("##email##", "", $new_xml);
$entries_xml = "";
$parts_1 = explode(";", $external_entries);
$agg_entries = array();
foreach($parts_1 as $line)
{
        $parts_2 = explode(":", $line);

	if(count($parts_2) != 3)
		continue;
	$type = $parts_2[0];
	$id = $parts_2[1];
	$amount = trim(ceil($parts_2[2]));

	if(!empty($agg_entries[$id]) && !empty($agg_entries[$id]["amount"]))
	{
		$agg_entries[$id]["amount"] += $amount;
	}
	else
	{
		$agg_entries[$id] = array("type" => $type, "id" => $id, "amount" => $amount);
	}


	
	
}
foreach($agg_entries as $k => $v)
{
	        $entries_xml .= "<zeile umsatzart=\"".$v["type"]."\"><knre>".$v["id"]."</knre><betrag>".$v["amount"]."</betrag> </zeile>";
}





$new_xml = str_replace("##anzeige##", $anzeige, $new_xml);

$new_xml = str_replace("##entries##", $entries_xml, $new_xml);

$pattern = "/<[^>]*><\/[^>]*>/"; 
$new_xml = preg_replace($pattern, '', $new_xml); 

$path_to_xml_file = $path_to_xml_folder."/".$prefix_test.$nutzdatenticket.".xml";
$path_to_pdf_file = $path_to_pdf_folder."/".$prefix_prod.$nutzdatenticket.".pdf";

$num_of_bytes = file_put_contents($path_to_xml_file, $new_xml);

chdir($path_to_eric_script_folder);
$cmd = "sh startedemo-x64.sh -v ".$current_usecase." -x ".$path_to_xml_file." -c ".$path_to_certificate_file." -p ".$password;
$cmd_result = shell_exec($cmd);


$payload["cmd_result"] = addslashes("cmd_result");
$payload["cmd"] = addslashes("cmd");
$payload = json_encode($payload);

$response = array();
if(file_exists($default_eric_output_filename))
{
	$response["success"] = "yes"; 
	$response["pdf"] = str_replace($path_to_base_folder, $base_url, $path_to_pdf_file);
	
	shell_exec("mv ".$path_to_eric_script_folder."/".$default_eric_output_filename." ".$path_to_pdf_file);


}
else
{
	$response["success"] = "no";
        $response["cmd_error"] = $cmd_result;
        $response["errors"] = implode(" -  ", $errors);
}

$response_str = json_encode($response);

echo $response_str;





function format_name($name)
{
        $bezeichnung_name_arr = array();
        $parts = explode(" ", $name);
        if(count($parts) == 1)
        {
                $bezeichnung_name_arr["bezeichnung"] = $name;
                $bezeichnung_name_arr["name"] = "-";

        }
        else
        {
                $tmp = "";
                for($i = 1; $i < count($parts); $i++)
                {
                        $tmp .= $parts[$i]." ";
                }
                $tmp = trim($tmp);
                $bezeichnung_name_arr["bezeichnung"] = $parts[0];
                $bezeichnung_name_arr["name"] = $tmp;
        }
        return $bezeichnung_name_arr;
}

function format_address($address)
{
	$strlen = strlen($address);
	if($strlen <= 33)
	{
        	return $address;
	}
	else
	{
        	$part = substr($address, 0, 30);
        	$part .= "...";
        	return $part;
	}
}
