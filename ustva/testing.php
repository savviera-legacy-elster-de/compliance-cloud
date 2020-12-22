<?php
header('Content-Type: text/html; charset=utf-8');
session_start();



$current_year = date("Y");
$current_month = date("m");
$current_day = date("d");
$current_usecase = "UStVA";
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

$prefix_test = "TEST_USTVA_";
$prefix_prod = "PROTOCOL_USTVA_";

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
}
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
}

if(!empty($payload["steuer_nr"]))
{
        $external_steuer_nr = trim($payload["steuer_nr"]);
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
        $external_plz_ausland = trim($payload["plz_ausland"]);
}
if(!empty($payload["field_83"]))
{
        $external_field_83 = trim($payload["field_83"]);
}
if(!empty($payload["field_81"]))
{
        $external_field_81 = trim($payload["field_81"]);
}


else
{
	$external_field_81 = 0;
}

if(!empty($payload["field_66"]))
{
        $external_field_66 = trim($payload["field_66"]);
}
if(!empty($payload["field_62"]))
{
        $external_field_62 = trim($payload["field_62"]);
}


if(!empty($payload["field_61"]))
{
        $external_field_61 = trim($payload["field_61"]);
}

if(!empty($payload["field_41"]))
{
        $external_field_41 = trim($payload["field_41"]);
}
if(!empty($payload["field_89"]))
{
        $external_field_89 = trim($payload["field_89"]);
}
if(!empty($payload["field_29"]))
{
        $external_field_29 = trim($payload["field_29"]);
}


if(!empty($payload["is_correction"]))
{
        $external_is_correction = trim($payload["is_correction"]);
}
if(!empty($payload["correction_msg"]))
{
        $external_correction_msg = trim($payload["correction_msg"]);
}



$empfaenger_and_country_and_steuer_nr = find_finanzamt_nr_and_normalize_steuer_nr_from_steuer_nr_and_country($external_steuer_nr, $external_land);


$new_xml = $xml_template_content;



$nutzdatenticket = rand(100000000, 999999999);
$new_xml = str_replace("##herstellerid##", $herstellerid, $new_xml);
$new_xml = str_replace("##nutzdatenticket##", $nutzdatenticket, $new_xml);
$new_xml = str_replace("##empfaenger##", $empfaenger_and_country_and_steuer_nr["empfaenger"], $new_xml);
$new_xml = str_replace("##jahr_erstellung##", $current_year, $new_xml);
$new_xml = str_replace("##monat_erstellung##", $current_month, $new_xml);
$new_xml = str_replace("##tag_erstellung##", $current_day, $new_xml);
$new_xml = str_replace("##jahr##", $external_year, $new_xml);
$new_xml = str_replace("##zeitraum##", $external_month, $new_xml);

$bezeichnung_name_arr = format_name($external_name);
$new_xml = str_replace("##bezeichnung##", $bezeichnung_name_arr["bezeichnung"], $new_xml);
$new_xml = str_replace("##name##", $bezeichnung_name_arr["name"], $new_xml);

$new_xml = str_replace("##vorname##", "", $new_xml);

$external_address = format_address($external_address);
$new_xml = str_replace("##strasse##", $external_address, $new_xml);


$new_xml = str_replace("##hausnr##", "", $new_xml);
$new_xml = str_replace("##ort##", $external_ort, $new_xml);
$new_xml = str_replace("##plz##", $external_plz, $new_xml);
$new_xml = str_replace("##plz_ausland##", $external_plz_ausland, $new_xml);
$new_xml = str_replace("##land##", $empfaenger_and_country_and_steuer_nr["external_land"], $new_xml);
$new_xml = str_replace("##telefon##", "", $new_xml);
$new_xml = str_replace("##email##", "", $new_xml);
$new_xml = str_replace("##steuernummer##", $empfaenger_and_country_and_steuer_nr["external_steuer_nr"], $new_xml);
$new_xml = str_replace("##kz83##", $external_field_83, $new_xml);

$kzs = "";



$kzs .= "<Kz35>".$external_field_81."</Kz35>";
$xxx = round($external_field_81 * 0.16, 2);
$xxx = sprintf("%.2f", $xxx);
$kzs .= "<Kz36>".$xxx."</Kz36>";




$kzs .= "<Kz66>".$external_field_66."</Kz66>";
$kzs .= "<Kz62>".$external_field_62."</Kz62>";





if(!empty($external_field_61))
	$kzs .= "<Kz61>".$external_field_61."</Kz61>";

if(!empty($external_field_41))
        $kzs .= "<Kz41>".$external_field_41."</Kz41>";


if(!empty($external_field_89))
{
	
       $kzs .= "<Kz95>".$external_field_89."</Kz95>";
	$xxx = round($external_field_89 * 0.16, 2);
  $xxx = sprintf("%.2f", $xxx);

       $kzs .= "<Kz98>".$xxx."</Kz98>";
}



if(!empty($external_field_29) && $external_field_29 == "1")
        $kzs .= "<Kz29>1</Kz29>";






if(!empty($external_is_correction) && $external_is_correction == "y")
{
	$kzs .= "<Kz10>1</Kz10>";

	$kzs .= "<Kz23>1</Kz23>";
	$correction_msg = "-";
	if(!empty($external_correction_msg))
		$correction_msg = $external_correction_msg;
	$kzs .= "<Kz23_Begruendung>".$correction_msg."</Kz23_Begruendung>";
}

$new_xml = str_replace("##kzs##", $kzs, $new_xml);

$pattern = "/<[^>]*><\/[^>]*>/"; 
$new_xml = preg_replace($pattern, '', $new_xml); 

$path_to_xml_file = $path_to_xml_folder."/".$prefix_test.$nutzdatenticket.".xml";
$path_to_pdf_file = $path_to_pdf_folder."/".$prefix_prod.$nutzdatenticket.".pdf";

$num_of_bytes = file_put_contents($path_to_xml_file, $new_xml);

chdir($path_to_eric_script_folder);
$cmd = "sh startedemo-x64.sh -v ".$current_usecase."_".$external_year." -x ".$path_to_xml_file." -c ".$path_to_certificate_file." -p ".$password;
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





function find_finanzamt_nr_and_normalize_steuer_nr_from_steuer_nr_and_country($external_steuer_nr, $external_land)
{

        $empfaenger_and_country_and_steuer_nr = array();
        $external_steuer_nr = trim($external_steuer_nr);

        if(strcasecmp($external_land, "ch") == 0 || strcasecmp($external_land, "cn") == 0 || strcasecmp($external_land, "china") == 0)
        {
                $empfaenger_and_country_and_steuer_nr["empfaenger"] = "1116";
                $empfaenger_and_country_and_steuer_nr["external_land"] = "China";
        }
        elseif(strcasecmp($external_land, "uk") == 0 || strcasecmp($external_land, "England") == 0 ||   strcasecmp($external_land, "United Kingdom") == 0)
        {
                $empfaenger_and_country_and_steuer_nr["empfaenger"] = "2325";
                $empfaenger_and_country_and_steuer_nr["external_land"] = "United Kingdom";
        }
        elseif(strcasecmp($external_land, "us") == 0 || strcasecmp($external_land, "usa") == 0 ||   strcasecmp($external_land, "United States") == 0)
        {
                $empfaenger_and_country_and_steuer_nr["empfaenger"] = "5205";
                $empfaenger_and_country_and_steuer_nr["external_land"] = "USA";
        }
        elseif(strcasecmp($external_land, "sp") == 0 || strcasecmp($external_land, "spain") == 0 ||   strcasecmp($external_land, "spanien") == 0)
        {
                $relevant_part_from_steuer_nr = substr($external_steuer_nr, 1, 2);
                $empfaenger_and_country_and_steuer_nr["empfaenger"] = "26".$relevant_part_from_steuer_nr;
                $empfaenger_and_country_and_steuer_nr["external_land"] = "Spain";
        }
        elseif(strcasecmp($external_land, "fr") == 0 || strcasecmp($external_land, "france") == 0 ||   strcasecmp($external_land, "frankreich") == 0)
        {
                $relevant_part_from_steuer_nr = substr($external_steuer_nr, 0, 2);
                $empfaenger_and_country_and_steuer_nr["empfaenger"] = "28".$relevant_part_from_steuer_nr;
                $empfaenger_and_country_and_steuer_nr["external_land"] = "France";
        }
        elseif(strcasecmp($external_land, "it") == 0 || strcasecmp($external_land, "italy") == 0 ||  strcasecmp($external_land, "italien") == 0)
        {
                $relevant_part_from_steuer_nr = substr($external_steuer_nr, 1, 2);
                $empfaenger_and_country_and_steuer_nr["empfaenger"] = "91".$relevant_part_from_steuer_nr;
                $empfaenger_and_country_and_steuer_nr["external_land"] = "Italy";
        }
        else
        {
                die("country is not supported. Supported countries: cn, it, uk, us, fr, sp");
        }

        $steuer_nr_without_slash = trim(implode("", explode("/", $external_steuer_nr)));
        $last_part_from_steuer_nr = substr($steuer_nr_without_slash, -8);
        $empfaenger_and_country_and_steuer_nr["external_steuer_nr"] = $empfaenger_and_country_and_steuer_nr["empfaenger"]."0".$last_part_from_steuer_nr;

        return $empfaenger_and_country_and_steuer_nr;
}



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


function format_x($x)
        {
                $parts = explode(",", $x);
                if(count($parts) == 2)
                {
                        $s = strlen($parts[1]);
                        if($s == 1)
                                $parts[1] .= "0";
                        $i = implode(".", $parts);
                        return $i;
                }
                else
                {
                        $i = $x.".00";
                        return $i;
                }
        }
 
