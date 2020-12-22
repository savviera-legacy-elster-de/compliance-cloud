<?php
header('Content-Type: text/html; charset=utf-8');
session_start();





$current_year = date("Y");
$current_month = date("m");
$current_day = date("d");
$current_usecase = "Ust";
$xml_template_file_path = "template.xml";
$xml_template_content = file_get_contents($xml_template_file_path);
$herstellerid_test = "";
$errors = array();

$path_to_php_script_folder = "/var/www/html//backend/ust";
$path_to_xml_folder = "/var/www/html//backend/shared/xml";
$path_to_pdf_folder = "/var/www/html//backend/shared/pdf";

$path_to_eric_script_folder = '/var/www/html//ERiC-30.2.8.0/Linux-x86_64/usecases/ustva';

        
$path_to_base_folder = "/var/www/html";
$base_url = "";

$prefix_test = "TEST_UST_";
$prefix_prod = "PROTOCOL_UST_";

$default_eric_output_filename = "ericprint.pdf";


$path_to_certificate_file = ".pfx";
$password = "";
$herstellerid = "";

if(empty($_POST["payload"]))
{
	$errors[] = "FATAL: No payload provided!";
	die("no payload provided");
}

$payload = trim($_POST["payload"]);

$payload = json_decode($payload, true);


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

if(!empty($payload["field_38"]))
{
        $external_field_38 = comma_to_point(trim($payload["field_38"]));
}
else
{
        $external_field_38 = 0;
} 

if(!empty($payload["field_82"]))
{
        $external_field_82 = comma_to_point(trim($payload["field_82"]));
}
else
{
        $external_field_82 = 0;
} 
if(!empty($payload["field_86"]))
{
        $external_field_86 = comma_to_point(trim($payload["field_86"]));
}
else
{
        $external_field_86 = 0;
} 
if(!empty($payload["field_122"]))
{
        $external_field_122 = comma_to_point(trim($payload["field_122"]));
}
else
{
        $external_field_122 = 0;
} 
if(!empty($payload["field_123"]))
{
        $external_field_123 = comma_to_point(trim($payload["field_123"]));
}
else
{
        $external_field_123 = 0;
} 
if(!empty($payload["field_124"]))
{
        $external_field_124 = comma_to_point(trim($payload["field_124"]));
}
else
{
        $external_field_124 = 0;
}

if(!empty($payload["field_168"]))
{
        $external_field_168 = comma_to_point(trim($payload["field_168"]));
}
else
{
	$external_field_168 = 0;
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

$new_xml = str_replace("##name##", $external_name, $new_xml);



$new_xml = str_replace("##address##", $external_address, $new_xml);

$new_xml = str_replace("##land##", $empfaenger_and_country_and_steuer_nr["external_land"], $new_xml);
$new_xml = str_replace("##steuernummer##", $empfaenger_and_country_and_steuer_nr["external_steuer_nr"], $new_xml);


if($external_year == "2019")
	$kz22 = "<Feld index=\"01\" lfdNr=\"00001\" nr=\"3002203\" wert=\"1\" />";
else
	$kz22 = "";
$new_xml = str_replace("##kz22##", $kz22, $new_xml);




$new_xml = str_replace("##kz38##", $external_field_38, $new_xml);
$calculated_field_38_percent = round(0.19 * $external_field_38, 2);
$new_xml = str_replace("##kz38%##", indent_with_commas(point_to_comma($calculated_field_38_percent)), $new_xml);
$new_xml = str_replace("##kz60##", indent_with_commas(point_to_comma($calculated_field_38_percent)), $new_xml);
$new_xml = str_replace("##kz152##", indent_with_commas(point_to_comma($calculated_field_38_percent)), $new_xml);


$new_xml = str_replace("##kz82##", $external_field_82, $new_xml);
$calculated_field_82_percent = round(0.19 * $external_field_82, 2);
$new_xml = str_replace("##kz82%##", indent_with_commas(point_to_comma($calculated_field_82_percent)), $new_xml);
$new_xml = str_replace("##kz86##", indent_with_commas(point_to_comma($calculated_field_82_percent)), $new_xml);
$new_xml = str_replace("##kz153##", indent_with_commas(point_to_comma($calculated_field_82_percent)), $new_xml);



$new_xml = str_replace("##kz122##", indent_with_commas(point_to_comma($external_field_122)), $new_xml);
$new_xml = str_replace("##kz123##", indent_with_commas(point_to_comma($external_field_123)), $new_xml);
$new_xml = str_replace("##kz124##", indent_with_commas(point_to_comma($external_field_124)), $new_xml);
$calculated_field_131 = $external_field_122 + $external_field_123 + $external_field_124;
$new_xml = str_replace("##kz131##", indent_with_commas(point_to_comma($calculated_field_131)), $new_xml);
$new_xml = str_replace("##kz158##", indent_with_commas(point_to_comma($calculated_field_131)), $new_xml);


$calculated_field_152 = $calculated_field_38_percent;
$calculated_field_153 = $calculated_field_82_percent;
$calculated_field_157 = $calculated_field_152 + $calculated_field_153; 
$new_xml = str_replace("##kz157##", indent_with_commas(point_to_comma($calculated_field_157)), $new_xml);


$calculated_field_158 = $calculated_field_131;
$calculated_field_160 = $calculated_field_157 - $calculated_field_158;
$new_xml = str_replace("##kz160##", indent_with_commas(point_to_comma($calculated_field_160)), $new_xml);

$new_xml = str_replace("##kz165##", indent_with_commas(point_to_comma($calculated_field_160)), $new_xml);

$new_xml = str_replace("##kz167##", indent_with_commas(point_to_comma($calculated_field_160)), $new_xml);

$new_xml = str_replace("##kz168##", indent_with_commas(point_to_comma($external_field_168)), $new_xml);


$calculated_field_169 = round($calculated_field_160 - $external_field_168,2); 
$new_xml = str_replace("##kz169##", indent_with_commas(point_to_comma($calculated_field_169)), $new_xml);


$kzs = "";
if(!empty($external_is_correction) && $external_is_correction == "y")
{
	$kzs .= '<Feld index="01" lfdNr="00001" nr="3000601" wert="1" />';

}


$new_xml = str_replace("##kzs##", $kzs, $new_xml);

$pattern = "/<[^>]*><\/[^>]*>/"; 
$new_xml = preg_replace($pattern, '', $new_xml); 

$path_to_xml_file = $path_to_xml_folder."/".$prefix_test.$nutzdatenticket.".xml";
$path_to_pdf_file = $path_to_pdf_folder."/".$prefix_test.$nutzdatenticket.".pdf";

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



function point_to_comma($str)
{
	return str_replace(".", ",", $str);
}


function comma_to_point($str)
{
        return str_replace(",", ".", $str);
}

function indent_with_commas($str)
{
	if(!strpos($str, ","))

	{
		return $str.",00";
	}
	else
	{
		$parts = explode(",", $str);
		$len = strlen($parts[1]);
		if($len == 1)
			return $str."0";
		else
			return $str;
	}
}

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
