<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class MainController extends Controller
{
    public function index(){
        return view('home.index');
    }

    public function write_tabbed_file($filepath, $array, $save_keys=false){
        $content = '';

        reset($array);
        foreach ($array as $key => $val ) {
//        while(list($key, $val) = each($array)){

            // replace tabs in keys and values to [space]
            $key = str_replace("\t", " ", $key);
            $val = str_replace("\t", " ", $val);

            if ($save_keys){ $content .=  $key."\t"; }

            // create line:
            $content .= (is_array($val)) ? implode("\t", $val) : $val;
            $content .= "\n";
        }

        if (file_exists($filepath) && !is_writeable($filepath)){
            return false;
        }
        if ($fp = fopen($filepath, 'w+')){
            fwrite($fp, $content);
            fclose($fp);
        }
        else { return false; }
        return true;
    }

    function check_zero($val){
        if ($val < 1000) {
            return '0'.$val;
        } else {
            return $val;
        }
    }

    public function post(Request $request){

        $this->validate($request, [
            'num' => 'required|numeric',
            'mandt' => 'required|numeric',
            'hvorg' => 'required|numeric',
            'entries' => 'required'
        ]);


        if ($request->num %2 !== 0) {
            return redirect()->back()->with('alert-danger', $request->num.' is not an even number');
        }

        $a = ($request->all());
        $a = json_encode($a['entries']);
        $a = strip_tags($a, '<tr>');
        $a = str_replace('"<figure class=\"table\"><table><tbody></tbody></table></figure>"', '', $a);
        $a = str_replace('"', '', $a);
        $a = str_replace('One Time Charge', 'OTC', $a);
        $a = str_replace('Monthly Recurring Charges', 'Recurr', $a);
        $a = explode("<tr>", $a);

        foreach ($a as $b => $c){
            if ($c == ""){
                unset($a[$b]);
            }
        }

        $empty_field_te305 = [
            'STAKZ', 'XMANB', 'SPZAH', 'MANSP', 'VERKZ', 'IKEY', 'SPERZ', 'ZPSTI',
            'MAHNV', 'STRKZ'
        ];

        /**
         * Perhitungan Genap
         */
        $t305_e = [];
        $t305t_e = [];
        $tfktvo_e = [];
        $tfktvot_e = [];

        $num_genap = (int) $request->num;
        if ($num_genap % 2 == 0 ? $num_genap : $num_genap = $num_genap+1)
            foreach ($a as $b => $c) {
                //CEK GENAP
                if ($num_genap % 2 == 0 ? $num_genap : $num_genap = $num_genap+1)

                    //TE305 Even
                    $t305_e[$b]['MANDT'] = $request->mandt;
                $t305_e[$b]['APPLK'] = 'T';
                $t305_e[$b]['BUKRS'] = '1000';
                $t305_e[$b]['SPARTE'] = '';
                $t305_e[$b]['HVORG'] = $request->hvorg;
                $t305_e[$b]['TVORG'] = $this->check_zero($num_genap);
                $t305_e[$b]['SHKZG'] = 'S';
                foreach ($empty_field_te305 as $e) {
                    $t305_e[$b][$e] = '';
                }
                $t305_e[$b]['TXT10'] = $c;
                $t305_e[$b]['TXT30'] = $c;

                //TE305T Even
                $t305t_e[$b]['Cl.'] = $request->mandt;
                $t305t_e[$b]['Language'] = 'EN';
                $t305t_e[$b]['ApArea'] = 'T';
                $t305t_e[$b]['CoCd'] = '1000';
                $t305t_e[$b]['Dv'] = '';
                $t305t_e[$b]['MTrans'] = $request->hvorg;
                $t305t_e[$b]['STrans'] = $this->check_zero($num_genap);
                $t305t_e[$b]['Trans.Text'] = $c;
                $t305t_e[$b]['Text'] = $c;

                //TFKTVO Even
                $tfktvo_e[$b]['MANDT'] = $request->mandt;
                $tfktvo_e[$b]['APPLK'] = 'T';
                $tfktvo_e[$b]['HVORG'] = $request->hvorg;
                $tfktvo_e[$b]['TVORG'] = $this->check_zero($num_genap);
                $tfktvo_e[$b]['HVORG_REV'] = '';
                $tfktvo_e[$b]['TVORG_REV'] = '';
                $tfktvo_e[$b]['FAETP'] = '';
                $tfktvo_e[$b]['QSVTP'] = '';
                $tfktvo_e[$b]['RLADDR'] = '';
                $tfktvo_e[$b]['XPAYT'] = '';
                $tfktvo_e[$b]['XNEGA'] = '';
                $tfktvo_e[$b]['TXT10'] = $c;
                $tfktvo_e[$b]['TXT30'] = $c;

                //TFKTVOT Even
                $tfktvot_e[$b]['MANDT'] = $request->mandt;
                $tfktvot_e[$b]['SPRAS'] = 'EN';
                $tfktvot_e[$b]['APPLK'] = 'T';
                $tfktvot_e[$b]['HVORG'] = $request->hvorg;
                $tfktvot_e[$b]['TVORG'] = $this->check_zero($num_genap);
                $tfktvot_e[$b]['TXT30'] = $c;
                $tfktvot_e[$b]['ZZINVTXT'] = $c;

                //increment number
                $num_genap = $num_genap+1;
            }

        /**
         * Perhitungan Ganjil
         */
        $t305_o = [];
        $t305t_o = [];
        $tfktvo_o = [];
        $tfktvot_o = [];
        $num_ganjil = (int) $request->num + 1;
        if ($num_ganjil % 2 !== 0 ? $num_ganjil : $num_ganjil = $num_ganjil+1)
            foreach ($a as $b => $c) {
                //CEK GENAP
                if ($num_ganjil % 2 !== 0 ? $num_ganjil : $num_ganjil = $num_ganjil+1)

                    //T305
                    $t305_o[$b]['MANDT'] = $request->mandt;
                $t305_o[$b]['APPLK'] = 'T';
                $t305_o[$b]['BUKRS'] = '1000';
                $t305_o[$b]['SPARTE'] = '';
                $t305_o[$b]['HVORG'] = $request->hvorg;
                $t305_o[$b]['TVORG'] = $this->check_zero($num_ganjil);
                $t305_o[$b]['SHKZG'] = 'H';
                foreach ($empty_field_te305 as $e) {
                    $t305_o[$b][$e] = '';
                }
                $t305_o[$b]['TXT10'] = $c;
                $t305_o[$b]['TXT30'] = $c;

                //TE305T
                $t305t_o[$b]['Cl.'] = $request->mandt;
                $t305t_o[$b]['Language'] = 'EN';
                $t305t_o[$b]['ApArea'] = 'T';
                $t305t_o[$b]['CoCd'] = '1000';
                $t305t_o[$b]['Dv'] = '';
                $t305t_o[$b]['MTrans'] = $request->hvorg;
                $t305t_o[$b]['STrans'] = $this->check_zero($num_ganjil);
                $t305t_o[$b]['Trans.Text'] = $c;
                $t305t_o[$b]['Text'] = $c;

                //TFKTVO
                $tfktvo_o[$b]['MANDT'] = $request->mandt;
                $tfktvo_o[$b]['APPLK'] = 'T';
                $tfktvo_o[$b]['HVORG'] = $request->hvorg;
                $tfktvo_o[$b]['TVORG'] = $this->check_zero($num_ganjil);
                $tfktvo_o[$b]['HVORG_REV'] = '';
                $tfktvo_o[$b]['TVORG_REV'] = '';
                $tfktvo_o[$b]['FAETP'] = '';
                $tfktvo_o[$b]['QSVTP'] = '';
                $tfktvo_o[$b]['RLADDR'] = '';
                $tfktvo_o[$b]['XPAYT'] = '';
                $tfktvo_o[$b]['XNEGA'] = '';
                $tfktvo_o[$b]['TXT10'] = $c;
                $tfktvo_o[$b]['TXT30'] = $c;

                //TFKTVOT
                $tfktvot_o[$b]['MANDT'] = $request->mandt;
                $tfktvot_o[$b]['SPRAS'] = 'EN';
                $tfktvot_o[$b]['APPLK'] = 'T';
                $tfktvot_o[$b]['HVORG'] = $request->hvorg;
                $tfktvot_o[$b]['TVORG'] = $this->check_zero($num_ganjil);
                $tfktvot_o[$b]['TXT30'] = $c;
                $tfktvot_o[$b]['ZZINVTXT'] = $c;

                //increment
                $num_ganjil = $num_ganjil+1;
            }

        /**
         * Merge Array and Generate File for TE305
         */
        $t305 = array_merge($t305_e, $t305_o);
        // array unshift
        $field_t305 = [
            'MANDT', 'APPLK', 'BUKRS', 'SPARTE', 'HVORG', 'TVORG', 'SHKZG'
        ];
        $field_t305 = array_merge($field_t305, $empty_field_te305);
        $field_t305 = array_merge($field_t305, ['TXT10', 'TXT30']);
        array_unshift($t305, $field_t305);

        $file_output = 'TE305.txt';
        $this->write_tabbed_file($file_output, $t305);

        dd($t305);


        /**
         * Merge Array and Generate File for TE305T
         */
        $t305t = array_merge($t305t_e, $t305t_o);
        $field_t305t = [
            'Cl.', 'Language', 'ApArea', 'CoCd', 'Dv', 'MTrans', 'STrans',
            'Trans.Text', 'Text'
        ];
        array_unshift($t305t, $field_t305t);
        $file_output = 'TE305T.txt';
        $this->write_tabbed_file($file_output, $t305t);

        /**
         * Merge Array and Generate File for TFKTVO
         */
        $tfktvo = array_merge($tfktvo_e, $tfktvo_o);
        $field_tfktvo = [
            'MANDT', 'APPLK', 'HVORG', 'TVORG', 'HVORG_REV', 'TVORG_REV', 'FAETP',
            'QSVTP', 'RLADDR', 'XPAYT', 'XNEGA', 'TXT10', 'TXT30'
        ];
        array_unshift($tfktvo, $field_tfktvo);
        $file_output = 'TFKTVO.txt';
        $this->write_tabbed_file($file_output, $tfktvo);

        /**
         * Merge Array and Generate File for TFKTVOT
         */
        $tfktvot = array_merge($tfktvot_e, $tfktvot_o);
        $field_tfktvot = [
            'MANDT', 'SPRAS', 'APPLK', 'HVORG', 'TVORG', 'TXT30', 'ZZINVTXT'
        ];
        array_unshift($tfktvot, $field_tfktvot);
        $file_output = 'TFKTVOT.txt';
        $this->write_tabbed_file($file_output, $tfktvot);

        return redirect()->route('main.index')->with('alert-success', 'Generated Successfully :)');
    }

}
