<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class DatabaseController extends Controller
{

    function formatBytes($size, $precision = 2)
    {
        $size = $size * 1000;
        $base = log($size, 1000);
        $suffixes = array('', 'KB', 'MB', 'GB', 'TB');   

        return round(pow(1000, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
    }

    function formatBytesArchivelog($size, $precision = 2)
    {
        $size = $size * 1000000;
        $base = log($size, 1000);
        $suffixes = array('', 'KB', 'MB', 'GB', 'TB');   

        return round(pow(1000, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
    }

    function tablespace(){

        $tablespaces = [
            'PSAPSR3',
            'PSAPSR3700',
            'PSAPSR3QUEST01',
            'PSAPSR3REORGDATA',
            'PSAPSR3USR',
            'PSAPTEMP',
            'PSAPUNDO001',
            'PSAPUNDO002',
            'SYSAUX',
            'SYSTEM',
            'TBSSHAREPLEX'
        ];

        // get the file contents, assuming the file to be readable (and exist)
        $contents = file_get_contents(public_path("ALERT_TELEGRAM/tablespace.txt"));
        $result = [];
        $final["tablespace"] = [];
        $final["total"] = 0;
        $final["total_used"] = 0;
        $final["total_free"] = 0;
        $final["ok"] = 0;
        $final["warning"] = 0;
        $final["critical"] = 0;
        $final["ok_list"] = [];
        foreach($tablespaces as $t){
            $searchfor = $t;
            // escape special characters in the query
            $pattern = preg_quote($searchfor, '/');
            // finalise the regular expression, matching the whole line
            $pattern = "/^.*$pattern.*\n.*\$/m";
            // search, and store all matching occurences in $matches
            if(preg_match($pattern, $contents, $matches)){
                // echo "Found matches:\n";
                $text = str_replace(" ", ",",$matches[0]);
                $text = $n = preg_replace('/,+/', ',', $text);
                $text = rtrim($text, ','); 
                $text =  explode(",", $text);
                $text = array_filter($text);
                $result[$t] = $text;
            }
        }

        // dd($result);

        if ($result) {
            foreach($result as $key => $r){
                $final["tablespace"][$key]['name'] = $r[3];
                $final["tablespace"][$key]['status'] = $r[5];
                $final["tablespace"][$key]['total'] = (int) $r[14];
                $final["tablespace"][$key]['used'] = (int)$r[14] - (int) $r[16];
                $final["tablespace"][$key]['free'] = (int) $r[16];
                $final["tablespace"][$key]['total_in_byte'] = $this->formatBytes((int) $r[14]);
                $final["tablespace"][$key]['used_in_byte'] = $this->formatBytes((int)$r[14] - (int) $r[16]);
                $final["tablespace"][$key]['free_in_byte'] = $this->formatBytes((int) $r[16]);
                $final["tablespace"][$key]['used_in_percentage'] = (double) $r[15];

                $final["total"] += (int) $r[14];
                $final["total_free"] += (int) $r[16];

                $final["tablespace"][$key]['used_in_percentage'] < 90 ? $final["ok"] += 1 : null;
                $final["tablespace"][$key]['used_in_percentage'] >= 90 && $final["tablespace"][$key]['used_in_percentage'] <= 95 ? $final["warning"] += 1 : null;
                $final["tablespace"][$key]['used_in_percentage'] > 95 ? $final["critical"] += 1 : null;
            }
        }

        $final["total_used"] = $final["total"] - $final["total_free"];
        $final["total_in_percentage"] = (int)(($final["total_used"] / $final["total"])* 100);

        $final["total_used_in_byte"] = $this->formatBytes($final["total_used"]);
        $final["total_free_in_byte"] = $this->formatBytes($final["total_free"]);
        $final["total_in_byte"] = $this->formatBytes($final["total"]);


        foreach($final['tablespace'] as $key => $val) {
            $val['used_in_percentage']< 90 ? $final['ok_list'][] = $final['tablespace'][$key] : null ;
            $val['used_in_percentage']>= 90 && $val['used_in_percentage'] <= 95 ? $final['warning_list'][] = $final['tablespace'][$key] : null ;
            $val['used_in_percentage']> 95 ? $final['critical_list'][] = $final['tablespace'][$key] : null ;
        };

        return response($final);
    }

    function archivelog(){

        /** ARVHIVE */

        $arr = [
            'Database log mode',
            'Automatic archival',
            'Archive destination',
            'Oldest online log sequence',
            'Next log sequence to archive',
            'Current log sequence',
            'SQL>',
            'spool off;',
            ' ',
        ];

        $arv = file(public_path("ALERT_TELEGRAM/archive_status.txt"));
        array_shift($arv);
        array_shift($arv);
        $arv = str_replace("\n", ",",$arv);
        $arv = str_replace($arr, ",",$arv);
        $arv = preg_replace('/,+/', ',', $arv);
        $arv = str_replace(",", "",$arv);
        $arv = array_filter($arv);

        $arv_output['db_log_mode'] = $arv[0];
        $arv_output['auto_archival'] = $arv[1];
        $arv_output['archive_dest'] = $arv[2];
        $arv_output['old'] = $arv[3];
        $arv_output['next'] = $arv[4];
        $arv_output['current'] = $arv[5];


        /** GRID ENV */

        $output = [];
        $final = [];

        $active_archive = "P10_ARCH/";

        $contents = file(public_path("ALERT_TELEGRAM/gridenv.txt"));
        array_shift($contents);
        foreach($contents as $key => $val) {
            $contents[$key] = str_replace(" ", ",",$contents[$key]);
            $contents[$key] = str_replace("\n", "",$contents[$key]);
            $contents[$key] = preg_replace('/,+/', ',', $contents[$key]);
            $contents[$key] =  explode(",", $contents[$key]);
        }

        foreach($contents as $key => $val) {
            $output[$val[12]]['name'] = $val[12];
            $output[$val[12]]['status'] = $val[0];
            $output[$val[12]]['type'] = $val[1];
            $output[$val[12]]['total_mb'] = (int)$val[6];
            $output[$val[12]]['used_mb'] = (int)$val[6] - (int)$val[7];
            $output[$val[12]]['free_mb'] = (int)$val[7];
            $output[$val[12]]['total_mb_inbyte'] = $this->formatBytesArchivelog($output[$val[12]]['total_mb']);
            $output[$val[12]]['used_mb_inbyte'] = $this->formatBytesArchivelog($output[$val[12]]['used_mb']);
            $output[$val[12]]['free_mb_inbyte'] = $this->formatBytesArchivelog($output[$val[12]]['free_mb']);
            $output[$val[12]]['usable_free_mb'] = (int)$val[9];
            $output[$val[12]]['used_in_percentage'] = (double)number_format(((int)$output[$val[12]]['used_mb'] / (int)$val[6] * 100),2);

            if ($val[12] == $active_archive) {
                $final['archivelog'] = $output[$val[12]];
            }
        }

        foreach ($output as $key => $val) {
            // dd($key);
            if ($val['used_in_percentage'] < 90 ) {
                $final['gridenv']['ok'][] = $val;
            } else if ($val['used_in_percentage']>= 90 && $val['used_in_percentage'] <= 95) {
                $final['gridenv']['warning'][] = $val;
            } else if ($val['used_in_percentage'] > 95 ) {
                $final['gridenv']['critical'][] = $val;
            }
        }

        $final['archivemode'] = $arv_output;

        return $final;
    }

    function appList(){

        $final = [];
        $file_list = [
            "ALERT_TELEGRAM/newtremsapp_status/app1.txt",
            "ALERT_TELEGRAM/newtremsapp_status/app2.txt",
            "ALERT_TELEGRAM/newtremsapp_status/app3.txt",
            "ALERT_TELEGRAM/newtremsapp_status/app4.txt",
            "ALERT_TELEGRAM/newtremsapp_status/app5.txt",
            "ALERT_TELEGRAM/newtremsapp_status/app6.txt",
            "ALERT_TELEGRAM/newtremsapp_status/app7.txt",
            "ALERT_TELEGRAM/newtremsapp_status/app8.txt",
            "ALERT_TELEGRAM/newtremsapp_status/app9.txt",
            "ALERT_TELEGRAM/newtremsapp_status/app10.txt",
            "ALERT_TELEGRAM/newtremsapp_status/app11.txt",
            "ALERT_TELEGRAM/newtremsapp_status/app12.txt",
        ];

        foreach($file_list as $key => $val){
            $numkey = (int)$key + 1;
            $arrkey = "app".$numkey;
            $app1 = file(public_path($val));
            $string = 'Dispatcher Queue Statistics';
            $result = [];
            foreach($app1 as $key => $val) {
                if (stristr($val, $string)){
                    $result[$key] = stristr($val, $string); 
                    break; 
                } 
            }

            $result ? $final[$arrkey]['available'] = "active" : $final[$arrkey]['available'] = "not active";
            $result ? $final[$arrkey]['avail_bool'] = true : $final[$arrkey]['avail_bool'] = false;
            $result ? $final[$arrkey]['color'] = "#3498db" : $final[$arrkey]['color'] = "#db3434";
            $result ? $final[$arrkey]['icon'] = "cloud-done" : $final[$arrkey]['icon'] = "cloud-off";
        }
        return $final;
    }

}
