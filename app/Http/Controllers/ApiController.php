<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Api_neweclaim;
use App\Models\Patient;
use App\Models\Check_sit_auto;
use App\Models\Visit_pttype;
use App\Models\Fdh_mini_dataset;
use App\Models\Visit_pttype_205;
use App\Models\Visit_pttype_217;
use App\Models\Check_sit_205_auto;



use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\support\Facades\Hash;
use Illuminate\support\Facades\Validator;
// use Illuminate\support\Facades\Http;
use Stevebauman\Location\Facades\Location;
use Http;
use SoapClient;
use File;
use SplFileObject;
use Arr;
use Storage;
use GuzzleHttp\Client; 
use PHPExcel;
use PHPExcel_IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\IOFactory;
use ZipArchive;
use Illuminate\Support\Facades\Redirect;
use PhpParser\Node\Stmt\If_; 

use Auth;
 
use Illuminate\Filesystem\Filesystem;

class ApiController extends Controller
{ 
    public function patient_readonly(Request $request)
    { 
        $year = substr(date("Y"),2) +43;
        $mounts = date('m');
        $day = date('d');
        $time = date("His");
        $ip = $request->ip();
        // $detallot = 'L'.substr(date("Ymd"),2).'-'.date("His");
        $hcode = '10978';
        $vn = $year.''.$mounts.''.$day.''.$time;
        $getpatient =  DB::connection('mysql3')->select('select cid,hometel from patient limit 2');
        $getvn_stat =  DB::connection('mysql3')->select('select hos_guid,vn,hn,vstdate,pttype,pttypeno from vn_stat limit 2');
        $get_ovst =  DB::connection('mysql3')->select('select hos_guid,vn,hn,vstdate,pttype,pttypeno from ovst limit 2');
        $get_opdscreen =  DB::connection('mysql3')->select('select hos_guid,vn,hn,vstdate from opdscreen limit 2');
        $get_ovst_seq =  DB::connection('mysql3')->select('select vn,seq_id,last_opdcard_depcode from ovst_seq limit 2');

        $getovst_key = Http::get('https://cloud4.hosxp.net/api/ovst_key?Action=get_ovst_key&hospcode="'.$hcode.'"&vn="'.$vn.'"&computer_name=abcde&app_name=AppName&fbclid=IwAR2SvX7NJIiW_cX2JYaTkfAduFqZAi1gVV7ftiffWPsi4M97pVbgmRBjgY8')->collect();
       
        // เจน  hos_guid  จาก Hosxp
        $data_key = DB::connection('mysql3')->select('SELECT uuid() as keygen'); 
        // $output = Arr::sort($data_key); 
        // $output2 = Arr::query($data_key);       
        // $output3 = Arr::sort($data_key['keygen']);
        $output4 = Arr::sort($data_key); 

        foreach ($output4 as $key => $value) { 
            $output_show = $value->keygen; 
        }
        // dd($output_show);
       
        return response([
            // $getpatient,
            $getvn_stat
            ,$get_ovst
            ,$get_opdscreen
            ,$vn,$get_ovst_seq,
            $getovst_key,$output_show
        ]);
    }
    public function ovst_key(Request $request)
    {
        $data['patient'] =  DB::connection('mysql')->select('select cid,hometel from patient limit 10');

        $year = substr(date("Y"),2) +43;
        $mounts = date('m');
        $day = date('d');
        $time = date("His"); 
        $hcode = '10978';
        $vn = $year.''.$mounts.''.$day.''.$time;
        $ip = $request->ip();

        $collection = Http::get('http://'.$ip.':8189/api/smartcard/read')->collect();
        // $collection = Http::get('http://localhost:8189/api/smartcard/read')->collect();
        $datapatient = DB::table('patient')->where('cid','=',$collection['pid'])->first();
            if ($datapatient->hometel != null) {
                $cid = $datapatient->hometel;
            } else {
                $cid = '';
            }   
            if ($datapatient->hn != null) {
                $hn = $datapatient->hn;
            } else {
                $hn = '';
            }  
            if ($datapatient->hcode != null) {
                $hcode = $datapatient->hcode;
            } else {
                $hcode = '';
            } 

          $getovst_key = Http::get('https://cloud4.hosxp.net/api/ovst_key?Action=get_ovst_key&hospcode="'.$hcode.'"&vn="'.$vn.'"&computer_name=abcde&app_name=AppName&fbclid=IwAR2SvX7NJIiW_cX2JYaTkfAduFqZAi1gVV7ftiffWPsi4M97pVbgmRBjgY8')->collect();    
        
          $outputcard = Arr::sort($getovst_key);
          // $outputcard = Arr::sort($getovst_key['ovst_key']);
        //    foreach ($outputcard as $values) { 
              // $showovst_key = $values['result']; 
        //   }
        
          return response([
            'getovst_key'  => $getovst_key['result']['ovst_key'],
              $outputcard
             ]);
    }
    
    
    public function getfire(Request $request,$firenum)
    {    
        // $firedata = Fire::where('fire_num','=', $firenum)->first();      
        // $fire_ = Fire::where('fire_num','=', $firenum)->get();
        $fire_ = Fire::get();
         return response([$fire_]);
          
    }
    public function authen_spsch(Request $request)
    {
            $date_now = date('Y-m-d'); 
            // $date_now = date('2024-05-10'); 
            $data_ = DB::connection('mysql')->select('SELECT vn,cid,hn,vstdate FROM check_sit_auto WHERE vstdate = "'.$date_now.'" AND (claimcode IS NULL OR claimcode ="") AND pttype NOT IN("M1","M2","M3","M4","M5","M6","O1","O2","O3","O4","O5","O6","L1","L2","L3","L4","L5","L6") GROUP BY vn'); 
            // $data_ = DB::connection('mysql')->select('SELECT vn,cid,hn,vstdate FROM fdh_mini_dataset WHERE vstdate = "'.$date_now.'" AND (claimcode IS NULL OR claimcode ="") AND pttype NOT IN("M1","M2","M3","M4","M5","M6","O1","O2","O3","O4","O5","O6","L1","L2","L3","L4","L5","L6") GROUP BY vn'); 
            $ch = curl_init(); 
            foreach ($data_ as $key => $value) {
                    $cid         = $value->cid;
                    $vn          = $value->vn;
                    $vstdate     = $value->vstdate; 
                    $headers = array();
                    $headers[] = "Accept: application/json";
                    $headers[] = "Authorization: Bearer 3045bba2-3cac-4a74-ad7d-ac6f7b187479";    
                    curl_setopt($ch, CURLOPT_URL, "https://authenucws.nhso.go.th/authencodestatus/api/check-authen-status?personalId=$cid&serviceDate=$vstdate&serviceCode=PG0060001");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
                    $response = curl_exec($ch); 
                    $contents = $response; 
                    $result = json_decode($contents, true);  
                    if ($result != null ) {  
                            isset( $result['statusAuthen'] ) ? $statusAuthen = $result['statusAuthen'] : $statusAuthen = ""; 
                            if ($statusAuthen =='false') { 
                                $his = $result['serviceHistories']; 
                                // dd($his); 
                                foreach ($his as $key => $value_s) {
                                    $cd	           = $value_s["claimCode"];
                                    $sv_code	   = $value_s["service"]["code"];
                                    $sv_name	   = $value_s["service"]["name"];
                                        
                                    Visit_pttype::where('vn','=', $vn)
                                        ->update([
                                            'claim_code'     => $cd, 
                                            'auth_code'      => $cd, 
                                    ]);
                                    
                                    Check_sit_auto::where('vn','=', $vn)
                                        ->update([
                                            'claimcode'     => $cd,
                                            'claimtype'     => $sv_code,
                                            'servicename'   => $sv_name, 
                                    ]);
                                    Fdh_mini_dataset::where('vn','=', $vn)
                                        ->update([
                                            'claimcode'     => $cd,
                                            'claimtype'     => $sv_code,
                                            'servicename'   => $sv_name, 
                                    ]);
                                    
                                }  
                            } 
                    } 
            }  
            
            
            $datati_ = DB::connection('mysql')->select('SELECT vn,cid,hn,vstdate FROM check_sit_auto WHERE vstdate = "'.$date_now.'" AND (claimcode IS NULL OR claimcode ="") AND pttype IN("M1","M2","M3","M4","M5","M6") GROUP BY vn'); 
            // $datati_ = DB::connection('mysql')->select('SELECT vn,cid,hn,vstdate FROM fdh_mini_dataset WHERE vstdate = "'.$date_now.'" AND (claimcode IS NULL OR claimcode ="") AND pttype IN("M1","M2","M3","M4","M5","M6") GROUP BY vn'); 
            
                
            $chti = curl_init(); 
                foreach ($datati_ as $key => $valueti) {
                        $cidti         = $valueti->cid;
                        $vnti          = $valueti->vn;
                        $vstdateti     = $valueti->vstdate; 
                        $headers = array();
                        $headers[] = "Accept: application/json";
                        $headers[] = "Authorization: Bearer 3045bba2-3cac-4a74-ad7d-ac6f7b187479";    
                        curl_setopt($chti, CURLOPT_URL, "https://authenucws.nhso.go.th/authencodestatus/api/check-authen-status?personalId=$cidti&serviceDate=$vstdateti&serviceCode=PG0130001");
                        curl_setopt($chti, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($chti, CURLOPT_CUSTOMREQUEST, "GET");
                        curl_setopt($chti, CURLOPT_HTTPHEADER, $headers);  
                        $responseti = curl_exec($chti);
                        // // curl_close($chti);
                        // dd($ccde);
                        $contents_ti = $responseti; 
                        $result_ti = json_decode($contents_ti, true); 
                        if ($result_ti != null ) {  
                                    isset( $result_ti['statusAuthen'] ) ? $statusAuthen_ti = $result_ti['statusAuthen'] : $statusAuthen_ti = "";
                                     
                                    if ($statusAuthen_ti =='false') { 
                                        $his_ti = $result_ti['serviceHistories']; 
                                        // dd($his); 
                                        foreach ($his_ti as $key => $value_ss) {
                                            $cd_ti	        = $value_ss["claimCode"];
                                            $sv_code_ti	    = $value_ss["service"]["code"];
                                            $sv_name_ti	    = $value_ss["service"]["name"];
                                            Visit_pttype::where('vn','=', $vnti)
                                                ->update([
                                                    'claim_code'     => $cd_ti, 
                                                    'auth_code'      => $cd_ti, 
                                            ]);                                    
                                            Check_sit_auto::where('vn','=', $vnti)
                                                ->update([
                                                    'claimcode'     => $cd_ti,
                                                    'claimtype'     => $sv_code_ti,
                                                    'servicename'   => $sv_name_ti, 
                                            ]);
                                        //     Fdh_mini_dataset::where('vn','=', $vnti)
                                        //     ->update([
                                        //         'claimcode'     => $cd_ti,
                                        //         'claimtype'     => $sv_code_ti,
                                        //         'servicename'   => $sv_name_ti, 
                                        // ]);
                                        }  
                                    }
                                // }
                        }
                        
                } 
            return response()->json('true');
                
    }
    public function authen_spsch_mini(Request $request)
    {
            $date_now = date('Y-m-d'); 
            // $date_now = date('2024-05-10'); 
            // $data_ = DB::connection('mysql')->select('SELECT vn,cid,hn,vstdate FROM check_sit_auto WHERE vstdate = "'.$date_now.'" AND (claimcode IS NULL OR claimcode ="") AND pttype NOT IN("M1","M2","M3","M4","M5","M6","O1","O2","O3","O4","O5","O6","L1","L2","L3","L4","L5","L6") GROUP BY vn'); 
            $data_ = DB::connection('mysql')->select('SELECT vn,cid,hn,vstdate FROM fdh_mini_dataset WHERE vstdate = "'.$date_now.'" AND (claimcode IS NULL OR claimcode ="") AND pttype NOT IN("M1","M2","M3","M4","M5","M6","O1","O2","O3","O4","O5","O6","L1","L2","L3","L4","L5","L6") GROUP BY vn'); 
            $ch = curl_init(); 
            foreach ($data_ as $key => $value) {
                    $cid         = $value->cid;
                    $vn          = $value->vn;
                    $vstdate     = $value->vstdate; 
                    $headers = array();
                    $headers[] = "Accept: application/json";
                    $headers[] = "Authorization: Bearer 3045bba2-3cac-4a74-ad7d-ac6f7b187479";    
                    curl_setopt($ch, CURLOPT_URL, "https://authenucws.nhso.go.th/authencodestatus/api/check-authen-status?personalId=$cid&serviceDate=$vstdate&serviceCode=PG0060001");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
                    $response = curl_exec($ch); 
                    $contents = $response; 
                    $result = json_decode($contents, true);  
                    if ($result != null ) {  
                            isset( $result['statusAuthen'] ) ? $statusAuthen = $result['statusAuthen'] : $statusAuthen = ""; 
                            if ($statusAuthen =='false') { 
                                $his = $result['serviceHistories']; 
                                // dd($his); 
                                foreach ($his as $key => $value_s) {
                                    $cd	           = $value_s["claimCode"];
                                    $sv_code	   = $value_s["service"]["code"];
                                    $sv_name	   = $value_s["service"]["name"];
                                        
                                    // Visit_pttype::where('vn','=', $vn)
                                    //     ->update([
                                    //         'claim_code'     => $cd, 
                                    // ]);
                                    
                                    // Check_sit_auto::where('vn','=', $vn)
                                    //     ->update([
                                    //         'claimcode'     => $cd,
                                    //         'claimtype'     => $sv_code,
                                    //         'servicename'   => $sv_name, 
                                    // ]);
                                    Fdh_mini_dataset::where('vn','=', $vn)
                                        ->update([
                                            'claimcode'     => $cd,
                                            'claimtype'     => $sv_code,
                                            'servicename'   => $sv_name, 
                                    ]);
                                    
                                }  
                            } 
                    } 
            }  
            
            
            // $datati_ = DB::connection('mysql')->select('SELECT vn,cid,hn,vstdate FROM check_sit_auto WHERE vstdate = "'.$date_now.'" AND (claimcode IS NULL OR claimcode ="") AND pttype IN("M1","M2","M3","M4","M5","M6") GROUP BY vn'); 
            $datati_ = DB::connection('mysql')->select('SELECT vn,cid,hn,vstdate FROM fdh_mini_dataset WHERE vstdate = "'.$date_now.'" AND (claimcode IS NULL OR claimcode ="") AND pttype IN("M1","M2","M3","M4","M5","M6") GROUP BY vn'); 
                            
            $chti = curl_init(); 
                foreach ($datati_ as $key => $valueti) {                      
                        $vnti          = $valueti->vn;
                        $cidti         = $valueti->cid;
                        $vstdateti     = $valueti->vstdate; 

                        // $cidti         = '5361090032186';
                        // $vstdateti     = '2024-05-12'; 

                        $headers = array();
                        // $headers[] = "Accept: application/json";
                        // $headers[] = "Authorization: Bearer 3045bba2-3cac-4a74-ad7d-ac6f7b187479";    
                        // curl_setopt($chti, CURLOPT_URL, "https://authenucws.nhso.go.th/authencodestatus/api/check-authen-status?personalId=$cidti&serviceDate=$vstdateti&serviceCode=PG0130001");
                        
                        $headers = array();
                        $headers[] = "Accept: application/json";
                        $headers[] = "Authorization: Bearer 3045bba2-3cac-4a74-ad7d-ac6f7b187479";    
                        curl_setopt($chti, CURLOPT_URL, "https://authenucws.nhso.go.th/authencodestatus/api/check-authen-status?personalId=$cidti&serviceDate=$vstdateti&serviceCode=PG0130001");
                        
                        curl_setopt($chti, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($chti, CURLOPT_CUSTOMREQUEST, "GET");
                        curl_setopt($chti, CURLOPT_HTTPHEADER, $headers);  
                        $responseti = curl_exec($chti);
                        // // curl_close($chti);
                        // dd($ccde);
                        $contents_ti = $responseti; 
                        $result_ti = json_decode($contents_ti, true); 
                        if ($result_ti != null ) {  
                                    isset( $result_ti['statusAuthen'] ) ? $statusAuthen_ti = $result_ti['statusAuthen'] : $statusAuthen_ti = "";
                                     
                                    if ($statusAuthen_ti =='false') { 
                                        $his_ti = $result_ti['serviceHistories']; 
                                        // dd($his); 
                                        foreach ($his_ti as $key => $value_ss) {
                                            $cd_ti	        = $value_ss["claimCode"];
                                            $sv_code_ti	    = $value_ss["service"]["code"];
                                            $sv_name_ti	    = $value_ss["service"]["name"];
                                            
                                            Fdh_mini_dataset::where('vn','=', $vnti)
                                            ->update([
                                                'claimcode'     => $cd_ti,
                                                'claimtype'     => $sv_code_ti,
                                                'servicename'   => $sv_name_ti, 
                                        ]);
                                        }  
                                    }
                                // }
                        }
                        
                } 
            return response()->json('true');
                
    }
    public function pull_hosapi(Request $request)
    {        
                $date_now = date('Y-m-d'); 
                // $date_now = date('2024-05-11');

                $data_sits = DB::connection('mysql10')->select(
                    'SELECT o.an,v.vn,p.hn,p.cid,o.vstdate,o.vsttime,o.pttype,p.pname,p.fname,concat(p.pname,p.fname," ",p.lname) as fullname,op.name as staffname,p.hometel,v.pdx,s.cc
                    ,pt.nhso_code,o.hospmain,o.hospsub,p.birthday
                    ,o.staff,op.name as sname
                    ,o.main_dep,v.income-v.discount_money-v.rcpt_money debit
                    FROM vn_stat v
                    LEFT JOIN visit_pttype vs on vs.vn = v.vn
                    LEFT JOIN ovst o on o.vn = v.vn
                    LEFT JOIN opdscreen s ON s.vn = v.vn
                    LEFT JOIN patient p on p.hn=v.hn
                    LEFT JOIN pttype pt on pt.pttype = v.pttype AND v.pttype 
                    LEFT JOIN opduser op on op.loginname = o.staff 

                    WHERE o.vstdate = "' . $date_now . '" 
                    AND ptt.hipdata_code ="UCS" AND v.income > 0  
                    AND pt.nationality = "99"
                    AND (o.an IS NULL OR o.an ="")
                    GROUP BY v.vn
                
                
                ');   
                // NOT IN("M1","M4","M5") 
                // LIMIT 100
                foreach ($data_sits as $key => $value) {
                    $check = Check_sit_auto::where('vn', $value->vn)->count();
                    if ($check > 0) {   
                        Check_sit_auto::where('vn', $value->vn)->update([  
                            'pttype'              => $value->pttype,
                            'debit'               => $value->debit, 
                        ]);              
                    } else {
                        Check_sit_auto::insert([
                            'vn'         => $value->vn,
                            'an'         => $value->an,
                            'hn'         => $value->hn,
                            'cid'        => $value->cid,
                            'vstdate'    => $value->vstdate,
                            'hometel'    => $value->hometel,
                            'vsttime'    => $value->vsttime,
                            'fullname'   => $value->fullname,
                            'pttype'     => $value->pttype,
                            'hospmain'   => $value->hospmain,
                            'hospsub'    => $value->hospsub,
                            'main_dep'   => $value->main_dep,
                            'staff'      => $value->staff,
                            'staff_name' => $value->staffname,
                            'debit'      => $value->debit,
                            'pdx'        => $value->pdx,
                            'cc'         => $value->cc
                        ]);
                        
                        // Fdh_mini_dataset::insert([
                        //     'service_date_time'   => $value->vstdate . ' ' . $value->vsttime,
                        //     'cid'                 => $value->cid,
                        //     'hcode'               => $value->hcode,
                        //     'total_amout'         => $value->total_amout,
                        //     'invoice_number'      => $value->invoice_number,
                        //     'vn'                  => $value->vn,
                        //     'pttype'              => $value->pttype,
                        //     'ptname'              => $value->ptname,
                        //     'hn'                  => $value->hn,
                        //     'vstdate'             => $value->vstdate,
                        //     'vsttime'             => $value->vsttime,
                        //     'datesave'            => $date_now,
                          
                        // ]);
                       
                    }
                }
                return response()->json('200');
                // $data_ = DB::connection('mysql')->select('SELECT vn,cid,hn,vstdate FROM check_sit_auto WHERE vstdate = "'.$date_now.'" AND (claimcode IS NULL OR claimcode ="") AND pttype NOT IN("M1","M2","M3","M4","M5","M6") GROUP BY vn'); 
                // return response()->json('200');
                // return response()->json(['status'=>'200']);
                //   return response()->json($data_, 200, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'],
                // JSON_UNESCAPED_UNICODE);
    }
//    ***************** Mini Dataset ********************
    public function pull_hosminiapi(Request $request)
    {        
                $date_now = date('Y-m-d'); 
                // $date_now = date('2024-05-11');
                $data_sits = DB::connection('mysql10')->select(
                    'SELECT v.vstdate,o.vsttime
                        ,Time_format(o.vsttime ,"%H:%i") vsttime2
                        ,v.cid,"10978" as hcode
                        ,rd.total_amount as total_amout
                        ,rd.finance_number as invoice_number
                        ,v.vn,concat(pt.pname,pt.fname," ",pt.lname) as ptname,v.hn,v.pttype
                        FROM vn_stat v 
                        LEFT OUTER JOIN ovst o ON v.vn = o.vn 
                        LEFT OUTER JOIN patient pt on pt.hn = v.hn
                        LEFT OUTER JOIN pttype ptt ON v.pttype=ptt.pttype   
                        LEFT OUTER JOIN rcpt_debt rd ON v.vn = rd.vn 
                   
                    WHERE o.vstdate = "' . $date_now . '" 
                    AND ptt.hipdata_code ="UCS" AND v.income > 0  
                    AND pt.nationality = "99"
                    AND (o.an IS NULL OR o.an ="")
                    GROUP BY v.vn
                ');   
                // LIMIT 100
                foreach ($data_sits as $key => $value) {
                    $check = Fdh_mini_dataset::where('vn', $value->vn)->count();
                    if ($check >0) {                    
                        Fdh_mini_dataset::where('vn', $value->vn)->update([  
                            'pttype'              => $value->pttype,
                            'total_amout'         => $value->total_amout,
                            'invoice_number'      => $value->invoice_number,
                        ]);              
                    } else {
                        Fdh_mini_dataset::insert([
                            'service_date_time'   => $value->vstdate . ' ' . $value->vsttime,
                            'cid'                 => $value->cid,
                            'hcode'               => $value->hcode,
                            'total_amout'         => $value->total_amout,
                            'invoice_number'      => $value->invoice_number,
                            'vn'                  => $value->vn,
                            'pttype'              => $value->pttype,
                            'ptname'              => $value->ptname,
                            'hn'                  => $value->hn,
                            'vstdate'             => $value->vstdate,
                            'vsttime'             => $value->vsttime,
                            'datesave'            => $date_now, 
                        ]);
                    
                    }
                }
                return response()->json('200');
            
    }
    public function fdh_mini_auth(Request $request)
    {  
        $username        = 'wijit.11407';
        $password        = 'CmbjBfqfLtsR3mQ9';
        $password_hash   = strtoupper(hash_hmac('sha256', $password, '$jwt@moph#'));

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://fdh.moph.go.th/token?Action=get_moph_access_token&user=' . $username . '&password_hash=' . $password_hash . '&hospital_code=10978',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'Cookie: __cfruid=bedad7ad2fc9095d4827bc7be4f52f209543768f-1714445470'
            ),
        ));
        $token = curl_exec($curl);
        // dd($token); 
        curl_close($curl);
 
        $check = Api_neweclaim::where('api_neweclaim_user', $username)->where('api_neweclaim_pass', $password)->count();
        if ($check > 0) {
            Api_neweclaim::where('api_neweclaim_user', $username)->update([
                'api_neweclaim_token'       => $token, 
                'password_hash'             => $password_hash,
                'hospital_code'             => '11407',
                'active_mini'               => 'Y',
            ]);
        } else {
            Api_neweclaim::insert([
                'api_neweclaim_user'        => $username,
                'api_neweclaim_pass'        => $password,
                'api_neweclaim_token'       => $token,
                'password_hash'             => $password_hash,
                'hospital_code'             => '11407',
                'active_mini'               => 'Y', 
            ]);
        }
        
        return response()->json('200');
    }
    public function fdh_mini_pullhosinv(Request $request)
    { 
            $date_now = date('Y-m-d');           
            $datashow_ = DB::connection('mysql2')->select(
                'SELECT v.vstdate,o.vsttime
                    ,Time_format(o.vsttime ,"%H:%i") vsttime2
                    ,v.cid,"10978" as hcode
                    ,v.income as total_amout
                    ,rd.finance_number as invoice_number
                    ,v.vn,concat(pt.pname,pt.fname," ",pt.lname) as ptname,v.hn,v.pttype
                    FROM vn_stat v 
                    LEFT OUTER JOIN ovst o ON v.vn = o.vn 
                    LEFT OUTER JOIN patient pt on pt.hn = v.hn
                    LEFT OUTER JOIN pttype ptt ON v.pttype=ptt.pttype 
                    LEFT OUTER JOIN rcpt_debt rd ON v.vn = rd.vn 
               
                WHERE o.vstdate = "' . $date_now . '" 
                AND ptt.hipdata_code ="UCS" AND v.income > 0  
                AND pt.nationality = "99"
                AND (o.an IS NULL OR o.an ="")
                GROUP BY v.vn
            '); 
            // AND v.pttype NOT IN("M1","M4","M5") 
            // AND v.income > 0 
            // AND rd.total_amount IS NOT NULL
            foreach ($datashow_ as $key => $value) {
                $check_opd = Fdh_mini_dataset::where('vn', $value->vn)->count();
                if ($check_opd > 0) {
                    Fdh_mini_dataset::where('vn', $value->vn)->update([  
                        'pttype'              => $value->pttype,
                        'total_amout'         => $value->total_amout,
                        'invoice_number'      => $value->invoice_number, 
                    ]);
                } else {
                    Fdh_mini_dataset::insert([
                        'service_date_time'   => $value->vstdate . ' ' . $value->vsttime,
                        'cid'                 => $value->cid,
                        'hcode'               => $value->hcode,
                        'total_amout'         => $value->total_amout,
                        'invoice_number'      => $value->invoice_number,
                        'vn'                  => $value->vn,
                        'pttype'              => $value->pttype,
                        'ptname'              => $value->ptname,
                        'hn'                  => $value->hn,
                        'vstdate'             => $value->vstdate,
                        'vsttime'             => $value->vsttime,
                        'datesave'            => $date_now,
                      
                    ]);
                }
            }
            return response()->json('200');      
    }
    public function fdh_minipullhosnoinv(Request $request)
    { 
            $date_now  = date('Y-m-d'); 
            $datashow_ = DB::connection('mysql2')->select(
                'SELECT v.vstdate,o.vsttime
                    ,Time_format(o.vsttime ,"%H:%i") vsttime2
                    ,v.cid,"10978" as hcode
                    ,IFNULL(rd.total_amount,v.income) as total_amout
                    ,IFNULL(rd.finance_number,v.vn) as invoice_number
                    ,v.vn,concat(pt.pname,pt.fname," ",pt.lname) as ptname,v.hn,v.pttype
                    FROM vn_stat v 
                    LEFT OUTER JOIN ovst o ON v.vn = o.vn 
                    LEFT OUTER JOIN patient pt on pt.hn = v.hn
                    LEFT OUTER JOIN pttype ptt ON v.pttype = ptt.pttype 
                    LEFT OUTER JOIN rcpt_debt rd ON v.vn = rd.vn 
                WHERE o.vstdate = "' . $date_now . '" 
                AND ptt.hipdata_code ="UCS" AND v.income > 0  
                AND pt.nationality = "99"
                AND (o.an IS NULL OR o.an ="")
                GROUP BY v.vn 
            ');
            // WHERE o.vstdate = "' . $date_now . '" AND (o.an IS NULL OR o.an = "") 
            // AND pt.nationality = "99"
            // AND ptt.hipdata_code ="UCS" AND v.income > 0 and rd.finance_number IS NULL  
            // GROUP BY o.vn 
            // AND v.pttype NOT IN("M1","M4","M5")  
            foreach ($datashow_ as $key => $value) {
                $check_opd = Fdh_mini_dataset::where('vn', $value->vn)->count();
                if ($check_opd > 0) {
                    Fdh_mini_dataset::where('vn', $value->vn)->update([   
                        'cid'                 => $value->cid, 
                        'pttype'              => $value->pttype, 
                        'total_amout'         => $value->total_amout,
                        'invoice_number'      => $value->invoice_number, 
                    ]);
                } else {
                    Fdh_mini_dataset::insert([
                        'service_date_time'   => $value->vstdate . ' ' . $value->vsttime,
                        'cid'                 => $value->cid,
                        'hcode'               => $value->hcode,
                        'total_amout'         => $value->total_amout,
                        'invoice_number'      => $value->invoice_number,
                        'vn'                  => $value->vn,
                        'pttype'              => $value->pttype,
                        'ptname'              => $value->ptname,
                        'hn'                  => $value->hn,
                        'vstdate'             => $value->vstdate,
                        'vsttime'             => $value->vsttime,
                        'datesave'            => $date_now,
                      
                    ]);
                }
            }
     
            return response()->json('200'); 
    }
    public function fdh_mini_pidsit(Request $request)
    { 
           $date_now = date('Y-m-d');
        //    $data_vn_1 = Fdh_mini_dataset::where('vstdate','=',$date_now)->where('invoice_number','<>','')->get();
           $data_vn_1 = DB::connection('mysql')->select('SELECT * FROM fdh_mini_dataset WHERE invoice_number IS NOT NULL AND vstdate = "'.$date_now.'"');
           $data_token_ = DB::connection('mysql')->select(' SELECT * FROM api_neweclaim WHERE active_mini = "Y"');
           foreach ($data_token_ as $key => $val_to) {
               $token_   = $val_to->api_neweclaim_token;
           }
           $token = $token_;   
           $startcount = 1;
           $data_claim = array();
           foreach ($data_vn_1 as $key => $val) {
               $service_date_time_      = $val->service_date_time;   
               $service_date_time    = substr($service_date_time_,0,16);
               $cid                  = $val->cid;
               $hcode                = $val->hcode;
               $total_amout          = $val->total_amout;
               $invoice_number       = $val->invoice_number;
               $vn                   = $val->vn;  
           
               $curl = curl_init();
                   $postData_send = [ 
                       "service_date_time"  => $service_date_time,
                       "cid"                => $cid,
                       "hcode"              => $hcode,
                       "total_amout"        => $total_amout,
                       "invoice_number"     => $invoice_number,
                       "vn"                 => $vn 
                   ];
                   curl_setopt($curl, CURLOPT_URL,"https://fdh.moph.go.th/api/v1/reservation");
                   curl_setopt($curl, CURLOPT_POST, 1);
                   curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                   curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postData_send, JSON_UNESCAPED_SLASHES));
                   curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                       'Content-Type: application/json',
                       'Authorization: Bearer '.$token,
                       'Cookie: __cfruid=bedad7ad2fc9095d4827bc7be4f52f209543768f-1714445470'
                   ));
       
                   $server_output     = curl_exec ($curl);
                   $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE); 
                   $content = $server_output;
                   $result = json_decode($content, true); 
                   @$status = $result['status']; 
                   @$message = $result['message'];
                   @$data = $result['data'];
                   @$uid = $data['transaction_uid']; 
                   if (@$message == 'success') {
                           Fdh_mini_dataset::where('vn', $vn)
                           ->update([
                               'transaction_uid' =>  @$uid,
                               'active'          => 'Y'
                           ]); 
                   } elseif ($status == '400') {
                           Fdh_mini_dataset::where('vn', $vn)
                               ->update([
                                   'transaction_uid' =>  @$uid,
                                   'active'          => 'Y'
                               ]);
                   } else {
                   }
           }       
           return response()->json('200');              

    }
    public function fdh_mini_pullbookid(Request $request)
    {
            $date = date('Y-m-d');
            $data_vn_1 = Fdh_mini_dataset::where('vstdate','=',$date)->where('transaction_uid','<>','')->get();
            $data_token_ = DB::connection('mysql')->select(' SELECT * FROM api_neweclaim WHERE active_mini = "Y"');
            foreach ($data_token_ as $key => $val_to) {
                $token_   = $val_to->api_neweclaim_token;
            }
            $token = $token_; 
            foreach ($data_vn_1 as $key => $val) { 
                $transaction_uid      = $val->transaction_uid;
                $hcode                = $val->hcode; 

                    $curl = curl_init(); 
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://fdh.moph.go.th/api/v1/reservation',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'GET',
                        CURLOPT_POSTFIELDS => '{
                            "transaction_uid": "'.$transaction_uid.'", 
                            "hcode"          : "'.$hcode.'" 
                        }',
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/json',
                            'Authorization: Bearer '.$token,
                            'Cookie: __cfruid=bedad7ad2fc9095d4827bc7be4f52f209543768f-1714445470'
                        ),
                    ));
                    $response = curl_exec($curl);
                    // dd($response); 
                    $result            = json_decode($response, true); 
                    @$status           = $result['status']; 
                    @$message          = $result['message'];
                    @$data             = $result['data'];
                    @$uidrep           = $data['transaction_uid'];
                    @$id_booking       = $data['id_booking'];
                    @$uuid_booking     = $data['uuid_booking']; 
                    if (@$message == 'success') {
                            Fdh_mini_dataset::where('transaction_uid', $uidrep)
                            ->update([
                                'id_booking'     => @$id_booking,
                                'uuid_booking'   => @$uuid_booking
                            ]);  
                    } elseif ($status == '400') {
                            Fdh_mini_dataset::where('transaction_uid', $uidrep)
                                ->update([
                                    'id_booking'     => @$id_booking,
                                    'uuid_booking'   => @$uuid_booking
                                ]);
                    } else {
                        # code...
                    }
            }
            return response()->json('200'); 
    }
    public function fdh_countvn(Request $request)
    { 
           $date_now = date('Y-m-d');
           
           $data_vn_1 = DB::connection('mysql')->select(
            'SELECT COUNT(DISTINCT vn) as count_vn FROM fdh_mini_dataset 
            WHERE vstdate = "'.$date_now.'" AND pttype NOT IN("M1","M4","M5","M6","O1","O2","O3","O4","O5","O6","L1","L2","L3","L4","L5","L6","13","23","x7","10") AND cid <>""
            ');
           foreach ($data_vn_1 as $key => $value) {
            $countvn = $value->count_vn;
           }                 
           return response()->json($countvn); 
    }
    public function fdh_sumincome(Request $request)
    { 
           $date_now = date('Y-m-d');
           
           $data_vn_1 = DB::connection('mysql')->select(
            'SELECT CONCAT(FORMAT(SUM(total_amout), 2)) as total FROM fdh_mini_dataset 
            WHERE vstdate = "'.$date_now.'" AND pttype NOT IN("M1","M4","M5","M6","O1","O2","O3","O4","O5","O6","L1","L2","L3","L4","L5","L6","13","23","x7","10") AND cid <>""
            ');
           foreach ($data_vn_1 as $key => $value) {
            $sumincome = $value->total;
            
           }                 
           return response()->json($sumincome); 
    }
    public function fdh_countpidsit(Request $request)
    { 
           $date_now = date('Y-m-d');
           
           $data_vn_1 = DB::connection('mysql')->select(
            'SELECT COUNT(DISTINCT vn) as count_vn FROM fdh_mini_dataset 
            WHERE vstdate = "'.$date_now.'" AND transaction_uid IS NOT NULL AND pttype NOT IN("M1","M4","M5","M6","O1","O2","O3","O4","O5","O6","L1","L2","L3","L4","L5","L6","13","23","x7","10") AND cid <>""');
           foreach ($data_vn_1 as $key => $value) {
            $countpidsit = $value->count_vn;
           }                 
           return response()->json($countpidsit); 
    }
    public function fdh_countbookid(Request $request)
    { 
           $date_now = date('Y-m-d');
           
           $data_vn_1 = DB::connection('mysql')->select(
            'SELECT COUNT(DISTINCT vn) as count_vn FROM fdh_mini_dataset 
            WHERE vstdate = "'.$date_now.'" AND id_booking IS NOT NULL AND pttype NOT IN("M1","M4","M5","M6","O1","O2","O3","O4","O5","O6","L1","L2","L3","L4","L5","L6","13","23","x7","10") AND cid <>""');
           foreach ($data_vn_1 as $key => $value) {
            $countpidsit = $value->count_vn;
           }                 
           return response()->json($countpidsit); 
    }
    public function fdh_countauthen(Request $request)
    { 
           $date_now = date('Y-m-d');
           
           $data_vn_1 = DB::connection('mysql')->select(
            'SELECT COUNT(DISTINCT vn) as count_authen FROM fdh_mini_dataset 
            WHERE vstdate = "'.$date_now.'" AND pttype NOT IN("M1","M4","M5","M6","O1","O2","O3","O4","O5","O6","L1","L2","L3","L4","L5","L6","13","23","x7","10") AND cid <>""
            AND claimcode <> ""');
           foreach ($data_vn_1 as $key => $value) {
            $countauthen = $value->count_authen;
           }                 
           return response()->json($countauthen); 
    }
    public function fdh_countauthennull(Request $request)
    { 
           $date_now = date('Y-m-d');
           
           $data_vn_1 = DB::connection('mysql')->select(
            'SELECT COUNT(DISTINCT vn) as count_authen FROM fdh_mini_dataset 
            WHERE vstdate = "'.$date_now.'" AND claimcode IS NULL AND pttype NOT IN("M1","M4","M5","M6","O1","O2","O3","O4","O5","O6","L1","L2","L3","L4","L5","L6","13","23","x7","10")');
           foreach ($data_vn_1 as $key => $value) {
            $countauthen = $value->count_authen;
           }                 
           return response()->json($countauthen); 
    }
    public function fdh_sumincome_authen(Request $request)
    { 
           $date_now = date('Y-m-d');
           
           $data_vn_1 = DB::connection('mysql')->select(
            'SELECT CONCAT(FORMAT(SUM(total_amout), 2)) as total FROM fdh_mini_dataset 
            WHERE vstdate = "'.$date_now.'" AND claimcode <> "" AND pttype NOT IN("M1","M4","M5","M6","O1","O2","O3","O4","O5","O6","L1","L2","L3","L4","L5","L6","13","23","x7","10")');
           foreach ($data_vn_1 as $key => $value) {
            $sumincome = $value->total;
            
           }                 
           return response()->json($sumincome); 
    }
    public function fdh_sumincome_noauthen(Request $request)
    { 
           $date_now = date('Y-m-d');
           
           $data_vn_1 = DB::connection('mysql')->select(
            'SELECT CONCAT(FORMAT(SUM(total_amout), 2)) as total FROM fdh_mini_dataset 
            WHERE vstdate = "'.$date_now.'" AND claimcode IS NULL AND pttype NOT IN("M1","M4","M5","M6","O1","O2","O3","O4","O5","O6","L1","L2","L3","L4","L5","L6","13","23","91","x7","10")');
           foreach ($data_vn_1 as $key => $value) {
            $sumincome = $value->total;
            
           }                 
           return response()->json($sumincome); 
    }
    public function countfiregreenall(Request $request)
    { 
           $date_now = date('Y-m-d');
           
           $data_vn_1 = DB::connection('mysql')->select('SELECT COUNT(fire_id) fire_id FROM fire WHERE fire_color = "green" AND fire_backup ="N"');
           foreach ($data_vn_1 as $key => $value) {
            $firegreen = $value->fire_id; 
           }                 
           return response()->json($firegreen); 
    }
    public function countfiregreen(Request $request)
    { 
           $date_now = date('Y-m-d');
           
           $data_vn_1 = DB::connection('mysql')->select('SELECT COUNT(fire_id) fire_id FROM fire WHERE fire_color = "green" AND active ="Y" AND fire_edit ="Narmal" AND fire_backup ="N"');
           foreach ($data_vn_1 as $key => $value) {
            $firegreen = $value->fire_id; 
           }                 
           return response()->json($firegreen); 
    }
    public function countfiregreenrepaire(Request $request)
    { 
           $date_now = date('Y-m-d');
           
           $data_vn_1 = DB::connection('mysql')->select('SELECT COUNT(fire_id) fire_id FROM fire WHERE fire_color = "green" AND active ="N" AND fire_edit ="Narmal" AND fire_backup ="N"');
           foreach ($data_vn_1 as $key => $value) {
            $firegreenre = $value->fire_id; 
           }                 
           return response()->json($firegreenre); 
    }
    public function countfireredall(Request $request)
    { 
           $date_now = date('Y-m-d');
           $data_vn_1 = DB::connection('mysql')->select('SELECT COUNT(fire_id) fire_id FROM fire WHERE fire_color = "red" AND fire_edit ="Narmal" AND fire_backup ="N"');
        //    $data_vn_1 = DB::connection('mysql')->select('SELECT COUNT(fire_id) fire_id FROM fire WHERE fire_color = "red"');
           foreach ($data_vn_1 as $key => $value) {
            $firered = $value->fire_id; 
           }                 
           return response()->json($firered); 
    }
    public function countfirered(Request $request)
    { 
           $date_now = date('Y-m-d');
           
           $data_vn_1 = DB::connection('mysql')->select('SELECT COUNT(fire_id) fire_id FROM fire WHERE fire_color = "red" AND active ="Y" AND fire_edit ="Narmal" AND fire_backup ="N"');
           foreach ($data_vn_1 as $key => $value) {
            $firered = $value->fire_id; 
           }                 
           return response()->json($firered); 
    }
    public function countfireredrepaire(Request $request)
    { 
           $date_now = date('Y-m-d');
           
           $data_vn_1 = DB::connection('mysql')->select('SELECT COUNT(fire_id) fire_id FROM fire WHERE fire_color = "red" AND active ="N" AND fire_edit ="Narmal" AND fire_backup ="N"');
           foreach ($data_vn_1 as $key => $value) {
            $fireredre = $value->fire_id; 
           }                 
           return response()->json($fireredre); 
    }
    public function getfirenum(Request $request,$firenum)
    { 
           $date_now = date('Y-m-d');           
           $data_vn_1 = DB::connection('mysql')->select('SELECT * FROM fire WHERE fire_num = "'.$firenum.'" ');
           foreach ($data_vn_1 as $key => $value) {
                $fireactive = $value->active; 
           }                 
           return response()->json($fireactive); 
    }
     // ************************** จองเคลม  OK**************
     public function auth_mini(Request $request)
     { 
             $username        = $request->username;
             $password        = $request->password;
             $password_hash   = strtoupper(hash_hmac('sha256', $password, '$jwt@moph#'));
             $basic_auth = base64_encode("$username:$password");
             $context = stream_context_create(array(
                 'http' => array(
                     'header' => "Authorization: Basic ".base64_encode("$username:$password")
                 )
             ));
                // $username = 'myusername';
                // $password = 'mypassword';
                // ...
                // curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
                // ...
             $curl = curl_init();
             curl_setopt_array($curl, array(
                 CURLOPT_URL => 'https://fdh.moph.go.th/token?Action=get_moph_access_token&user=' . $username . '&password_hash=' . $password_hash . '&hospital_code=10978',
                 CURLOPT_RETURNTRANSFER => true,
                 CURLOPT_ENCODING => '',
                 CURLOPT_MAXREDIRS => 10,
                 CURLOPT_TIMEOUT => 0,
                 CURLOPT_FOLLOWLOCATION => true,
                 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                 CURLOPT_CUSTOMREQUEST => 'POST',
                 CURLOPT_HTTPHEADER => array(
                     'Cookie: __cfruid=bedad7ad2fc9095d4827bc7be4f52f209543768f-1714445470'
                 ),
             ));
             $token = curl_exec($curl);
             // dd($token); 
             curl_close($curl); 
             $check = Api_neweclaim::where('api_neweclaim_user', $username)->where('api_neweclaim_pass', $password)->count();
             if ($check > 0) {
                 Api_neweclaim::where('api_neweclaim_user', $username)->update([
                     'api_neweclaim_token'       => $token,
                     'user_id'                   => "754",
                     'password_hash'             => $password_hash,
                     'hospital_code'             => '10978',
                     'basic_auth'                => $basic_auth,
                 ]);
             } else {
                 Api_neweclaim::insert([
                     'api_neweclaim_user'        => $username,
                     'api_neweclaim_pass'        => $password,
                     'api_neweclaim_token'       => $token,
                     'password_hash'             => $password_hash,
                     'hospital_code'             => '10978',
                     'basic_auth'                => $basic_auth,
                     'user_id'                   => "754",
                 ]);
             } 
             
             return response()->json('200'); 
  
     }
     public function mini_pull_hos(Request $request)
     { 
             $date = date('Y-m-d');     
             $datashow_ = DB::connection('mysql10')->select(
                 'SELECT v.vstdate,o.vsttime
                     ,Time_format(o.vsttime ,"%H:%i") vsttime2
                     ,v.cid,"10978" as hcode
                     ,IFNULL(rd.total_amount,v.income) as total_amout
                     ,IFNULL(rd.finance_number,v.vn) as invoice_number
                     ,v.vn,concat(pt.pname,pt.fname," ",pt.lname) as ptname,v.hn,v.pttype
                     ,IFNULL(vp.claim_code,vp.auth_code) as authen_code 
                     FROM ovst o
                     LEFT OUTER JOIN vn_stat v ON v.vn = o.vn 
                     LEFT OUTER JOIN patient pt on pt.hn = v.hn
                     LEFT OUTER JOIN pttype ptt ON v.pttype = ptt.pttype 
                     LEFT OUTER JOIN rcpt_debt rd ON v.vn = rd.vn 
                     LEFT OUTER JOIN visit_pttype vp on vp.vn = o.vn
                 WHERE o.vstdate = "' . $date . '"  
                 AND ptt.hipdata_code ="UCS" AND v.income > 0 AND pt.cid IS NOT NULL AND pt.nationality ="99" AND pt.birthday <> "'.$date.'"
                 AND (o.an IS NULL OR o.an ="")
                 GROUP BY v.vn LIMIT 50
             ');
        
             foreach ($datashow_ as $key => $value) {
                 $check_opd = Fdh_mini_dataset::where('vn', $value->vn)->count();
                 if ($check_opd > 0) {
                     Fdh_mini_dataset::where('vn', $value->vn)->update([ 
                         'cid'                 => $value->cid, 
                         'pttype'              => $value->pttype,
                         'total_amout'         => $value->total_amout,
                         'invoice_number'      => $value->invoice_number, 
                         'claimcode'           => $value->authen_code, 
                     ]);
                 } else {
                     Fdh_mini_dataset::insert([
                         'service_date_time'   => $value->vstdate . ' ' . $value->vsttime,
                         'cid'                 => $value->cid,
                         'hcode'               => $value->hcode,
                         'total_amout'         => $value->total_amout,
                         'invoice_number'      => $value->invoice_number,
                         'vn'                  => $value->vn,
                         'pttype'              => $value->pttype,
                         'ptname'              => $value->ptname,
                         'hn'                  => $value->hn,
                         'vstdate'             => $value->vstdate,
                         'vsttime'             => $value->vsttime,
                         'datesave'            => $date,
                         'claimcode'           => $value->authen_code,                       
                     ]);
                 }
             }
             return response()->json('200');
       
     }
     public function mini_dataset_apicliam(Request $request)
     { 
            $date = date('Y-m-d');
            $iduser = "1"; 
            $data_vn_1 = DB::connection('mysql')->select('SELECT * from fdh_mini_dataset WHERE active ="N" AND cid <> "" AND (transaction_uid ="" OR transaction_uid IS NULL) AND invoice_number <>"" LIMIT 10');            
            $data_token_ = DB::connection('mysql')->select(' SELECT * FROM api_neweclaim WHERE active_mini = "Y" AND user_id = "'.$iduser.'"');
            foreach ($data_token_ as $key => $val_to) {
                $token_   = $val_to->api_neweclaim_token;
            }
            $token = $token_;    
            $startcount = 1;
            $data_claim = array();
            foreach ($data_vn_1 as $key => $val) {
                $service_date_time_      = $val->service_date_time;    
                $service_date_time    = substr($service_date_time_,0,16);
                $cid                  = $val->cid;
                $hcode                = $val->hcode;
                $total_amout          = $val->total_amout;
                $invoice_number       = $val->invoice_number;
                $vn                   = $val->vn;
                
                $curl = curl_init();
                    $postData_send = [ 
                        "service_date_time"  => $service_date_time,
                        "cid"                => $cid,
                        "hcode"              => $hcode,
                        "total_amout"        => $total_amout,
                        "invoice_number"     => $invoice_number,
                        "vn"                 => $vn 
                    ];
                    curl_setopt($curl, CURLOPT_URL,"https://fdh.moph.go.th/api/v1/reservation");
                    curl_setopt($curl, CURLOPT_POST, 1);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postData_send, JSON_UNESCAPED_SLASHES));
                    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',
                        'Authorization: Bearer '.$token,
                        'Cookie: __cfruid=bedad7ad2fc9095d4827bc7be4f52f209543768f-1714445470'
                    ));
        
                    $server_output     = curl_exec ($curl);
                    $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                    // dd($statusCode);
                    $content = $server_output;
                    $result = json_decode($content, true);
                    #echo "<BR>";
                    @$status = $result['status'];
                    #echo "<BR>";
                    @$message = $result['message'];
                    @$data = $result['data'];
                    @$uid = $data['transaction_uid'];
                    #echo "<BR>";
                    // dd(@$result);
                    if (@$message == 'success') {
                            Fdh_mini_dataset::where('vn', $vn)
                            ->update([
                                'transaction_uid' =>  @$uid,
                                'active'          => 'Y'
                            ]); 
                    } elseif ($status == '400') {
                            Fdh_mini_dataset::where('vn', $vn)
                                ->update([
                                    'transaction_uid' =>  @$uid,
                                    'active'          => 'Y'
                                ]);
                    } else {
                        # code...
                    }
            }        
            return response()->json('200'); 
     }

     public function mini_dataset_pulljong(Request $request)
    {
            $date = date('Y-m-d');
            $iduser = "754"; 
            $data_vn_1 = DB::connection('mysql')->select('SELECT * FROM fdh_mini_dataset WHERE invoice_number IS NOT NULL AND cid <>"" AND hcode <> "" AND id_booking IS NULL LIMIT 50');
            $data_token_ = DB::connection('mysql')->select(' SELECT * FROM api_neweclaim WHERE active_mini = "Y" AND user_id = "'.$iduser.'"');
            foreach ($data_token_ as $key => $val_to) {
                $token_   = $val_to->api_neweclaim_token;
            }
            $token = $token_; 
            foreach ($data_vn_1 as $key => $val) { 
                $transaction_uid      = $val->transaction_uid;
                $hcode                = $val->hcode; 

                    $curl = curl_init(); 
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://fdh.moph.go.th/api/v1/reservation',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'GET',
                        CURLOPT_POSTFIELDS => '{
                            "transaction_uid": "'.$transaction_uid.'", 
                            "hcode"          : "'.$hcode.'" 
                        }',
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/json',
                            'Authorization: Bearer '.$token,
                            'Cookie: __cfruid=bedad7ad2fc9095d4827bc7be4f52f209543768f-1714445470'
                        ),
                    ));
                    $response = curl_exec($curl);
                    // dd($response); 
                    $result            = json_decode($response, true); 
                    @$status           = $result['status']; 
                    @$message          = $result['message'];
                    @$data             = $result['data'];
                    @$uidrep           = $data['transaction_uid'];
                    @$id_booking       = $data['id_booking'];
                    @$uuid_booking     = $data['uuid_booking']; 
                    if (@$message == 'success') {
                            Fdh_mini_dataset::where('transaction_uid', $uidrep)
                            ->update([
                                'id_booking'     => @$id_booking,
                                'uuid_booking'   => @$uuid_booking
                            ]);  
                    } elseif ($status == '400') {
                            Fdh_mini_dataset::where('transaction_uid', $uidrep)
                                ->update([
                                    'id_booking'     => @$id_booking,
                                    'uuid_booking'   => @$uuid_booking
                                ]);
                    } else {
                        # code...
                    }
            }
            return response()->json('200'); 
             
    }
    public function update_authento_hos(Request $request)
    {  
            $iduser      = "754";
            $id          = $request->ids;
            $date_now    = date('Y-m-d');
            // $date_now     = date('2024-05-30');
            // $data_vn_1 = Check_sit_auto::whereIn('check_sit_auto_id', explode(",", $id))->get();
            $data_vn_1 = DB::connection('mysql10')->select(
                'SELECT v.vn,p.hn,p.cid,o.vstdate,o.pttype,p.birthday,p.hometel,p.citizenship,p.nationality,v.pdx,o.hospmain,o.hospsub
                ,o.staff,op.name as sname,v.income-v.discount_money-v.rcpt_money debit
                FROM vn_stat v
                LEFT JOIN visit_pttype vs on vs.vn = v.vn
                LEFT JOIN ovst o on o.vn = v.vn 
                LEFT JOIN patient p on p.hn=v.hn
                LEFT JOIN pttype pt on pt.pttype=v.pttype
                LEFT JOIN opduser op on op.loginname = o.staff
                WHERE o.vstdate = "'.$date_now.'" 
                AND p.cid IS NOT NULL AND p.nationality ="99" AND p.birthday <> "'.$date_now.'" AND (vs.claim_code IS NULL OR vs.claim_code ="")
                GROUP BY o.vn
                LIMIT 70
            ');
            // WHERE o.vstdate = "'.$date_now.'" 
            // AND p.cid IS NOT NULL AND p.nationality ="99" AND p.birthday <> "2024-05-29" AND (vs.claim_code IS NULL OR vs.claim_code ="")
            // GROUP BY o.vn
            // LIMIT 100
            $data_token_ = DB::connection('mysql')->select(' SELECT * FROM api_neweclaim WHERE active_mini = "Y" AND user_id="'.$iduser.'"');
            foreach ($data_token_ as $key => $val_to) {
                $token_       = $val_to->api_neweclaim_token;
                $basic_auth   = $val_to->basic_auth;
            }
            $token = $token_;                  
            foreach ($data_vn_1 as $key => $value) {
                    $cid         = $value->cid;
                    $vn          = $value->vn;
                    $vstdate     = $value->vstdate; 

                    $ch = curl_init(); 
                    $headers = array();
                    $headers[] = "Accept: application/json";
                    $headers[] = "Authorization: Bearer 3045bba2-3cac-4a74-ad7d-ac6f7b187479";
                    // https://authenservice.nhso.go.th/authencode/api/authencode-report?hcode=10978&provinceCode=3600&zoneCode=09&claimDateFrom=2024-05-29&claimDateTo=2024-05-29&pid=3361000824057&page=0&size=10&sort=transId,desc   
                    // $url = "https://authenservice.nhso.go.th/authencode/api/authencode-report?hcode=10978&provinceCode=3600&zoneCode=09&claimDateFrom=$vstdate&claimDateTo=$vstdate&pid=$cid&page=0&size=10&sort=transId,desc"; 
                    curl_setopt($ch, CURLOPT_URL, "https://nhso.go.th/authencodestatus/api/check-authen-status?personalId=$cid&serviceDate=$vstdate&serviceCode=PG0060001"); 
                    // curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
                    $response = curl_exec($ch); 
                    $contents = $response; 
                    // dd($response);
                    $result = json_decode($contents, true);  
                    // dd($result);
                    if ($result != null ) {  
                            isset( $result['statusAuthen'] ) ? $statusAuthen = $result['statusAuthen'] : $statusAuthen = ""; 
                            if ($statusAuthen =='false') { 
                                $his = $result['serviceHistories']; 
                                // dd($his); 
                                foreach ($his as $key => $value_s) {
                                    $cd	           = $value_s["claimCode"];
                                    $sv_code	   = $value_s["service"]["code"];
                                    $sv_name	   = $value_s["service"]["name"];
                                        
                                    Visit_pttype::where('vn','=', $vn)
                                        ->update([
                                            'claim_code'     => $cd, 
                                            'auth_code'      => $cd, 
                                    ]);
                                    
                                    Check_sit_auto::where('vn','=', $vn)
                                        ->update([
                                            'claimcode'     => $cd,
                                            'claimtype'     => $sv_code,
                                            'servicename'   => $sv_name, 
                                    ]);
                                    Fdh_mini_dataset::where('vn','=', $vn)
                                        ->update([
                                            'claimcode'     => $cd,
                                            'claimtype'     => $sv_code,
                                            'servicename'   => $sv_name, 
                                    ]);

                                }  
                            } 
                    } 

            }  

            return response()->json('200'); 
                
    }

    public function authen_auth_apinew(Request $request)
    {      
                $date_now = date('Y-m-d');
                // $date_now = date('2024-04-03');
                // $date_now = date('2024-04-15');
                // $data_vn_1 = DB::connection('mysql')->select(
                //     'SELECT vn,cid,hn,vstdate,claimcode 
                //         FROM fdh_mini_dataset WHERE vstdate = "'.$date_now.'"
                //         AND (claimcode IS NULL OR claimcode ="") AND cid is not null  
                //         GROUP BY vn 
                       
                //     '); 
                $data_vn_1 = DB::connection('mysql10')->select(
                    'SELECT v.vn,p.hn,p.cid,o.vstdate,o.pttype,p.birthday,p.hometel,p.citizenship,p.nationality,v.pdx,o.hospmain,o.hospsub
                    ,o.staff,op.name as sname,v.income-v.discount_money-v.rcpt_money debit,vs.claim_code,vs.auth_code
                    FROM vn_stat v
                    LEFT JOIN visit_pttype vs on vs.vn = v.vn
                    LEFT JOIN ovst o on o.vn = v.vn 
                    LEFT JOIN patient p on p.hn=v.hn
                    LEFT JOIN pttype pt on pt.pttype=v.pttype
                    LEFT JOIN opduser op on op.loginname = o.staff
                    WHERE o.vstdate = "'.$date_now.'" 
                    AND p.cid IS NOT NULL AND p.nationality ="99" 
                    AND (vs.claim_code IS NULL OR vs.claim_code ="")
                    AND v.pttype NOT IN("M1","M2","M3","M4","M5","M6","O1","O2","O3","O4","O5","O6","L1","L2","L3","L4","L5","L6") 
                    GROUP BY o.vn LIMIT 70
                ');
                    // AND pttype NOT IN("M1","M2","M3","M4","M5","M6","O1","O2","O3","O4","O5","O6","L1","L2","L3","L4","L5","L6") 
                $ch = curl_init(); 
                foreach ($data_vn_1 as $key => $value) {
                        $cid         = $value->cid;
                        $vn          = $value->vn;
                        $vstdate     = $value->vstdate; 
                        $headers = array();
                        $headers[] = "Accept: application/json";
                        $headers[] = "Authorization: Bearer 3045bba2-3cac-4a74-ad7d-ac6f7b187479";    
                        // curl_setopt($ch, CURLOPT_URL, "https://authenucws.nhso.go.th/authencodestatus/api/check-authen-status?personalId=$cid&serviceDate=$vstdate&serviceCode=PG0060001");
                        curl_setopt($ch, CURLOPT_URL, "https://nhso.go.th/authencodestatus/api/check-authen-status?personalId=$cid&serviceDate=$vstdate&serviceCode=PG0060001");
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);                         
                        $response = curl_exec($ch); 
                        $contents = $response; 
                        $result = json_decode($contents, true); 
 
                                if ($result != null ) { 
                                    
                                            isset( $result['statusAuthen'] ) ? $statusAuthen = $result['statusAuthen'] : $statusAuthen = "";
                                           
                                            if ($statusAuthen =='false') {
                                              
                                                $his = $result['serviceHistories']; 
                                                // dd($his); 
                                                foreach ($his as $key => $value_s) {
                                                    $cd	           = $value_s["claimCode"];
                                                    $sv_code	   = $value_s["service"]["code"];
                                                    $sv_name	   = $value_s["service"]["name"];
                                                    // dd($sv_name);                                               
                                                    Check_sit_205_auto::where('vn', $vn)
                                                        ->update([
                                                            'claimcode'     => $cd,
                                                            'claimtype'     => $sv_code,
                                                            'servicename'   => $sv_name, 
                                                    ]);
                                                    Visit_pttype_205::where('vn', $vn)
                                                        ->update([
                                                            'claim_code'     => $cd, 
                                                            'auth_code'      => $cd, 
                                                    ]);
                                                    Visit_pttype_217::where('vn', $vn)
                                                        ->update([
                                                            'claim_code'     => $cd, 
                                                            'auth_code'      => $cd, 
                                                    ]);
                                                    Visit_pttype::where('vn','=', $vn)
                                                        ->update([
                                                            'claim_code'     => $cd, 
                                                            'auth_code'      => $cd, 
                                                    ]);
                                                 
                                                    Check_sit_auto::where('vn','=', $vn)
                                                        ->update([
                                                            'claimcode'     => $cd,
                                                            'claimtype'     => $sv_code,
                                                            'servicename'   => $sv_name, 
                                                    ]);
                                                    Fdh_mini_dataset::where('vn','=', $vn)
                                                        ->update([
                                                            'claimcode'     => $cd, 
                                                    ]);
                                                }  
                                            }
                                        // }
                                }
                        // }
                } 
                
                return response()->json('200'); 
    }

    public function authen_auth_apitinew(Request $request)
    {      
                $date_now = date('Y-m-d');
                // $date_now = date('2024-04-03');
                // $date_now = date('2024-04-15');
                // $data_vn_1 = DB::connection('mysql')->select(
                //     'SELECT vn,cid,hn,vstdate,claimcode 
                //         FROM fdh_mini_dataset WHERE vstdate = "'.$date_now.'"
                //         AND (claimcode IS NULL OR claimcode ="") AND cid is not null  
                //         GROUP BY vn 
                       
                //     '); 
                $data_vn_1 = DB::connection('mysql10')->select(
                    'SELECT v.vn,p.hn,p.cid,o.vstdate,o.pttype,p.birthday,p.hometel,p.citizenship,p.nationality,v.pdx,o.hospmain,o.hospsub
                    ,o.staff,op.name as sname,v.income-v.discount_money-v.rcpt_money debit,vs.claim_code,vs.auth_code
                    FROM vn_stat v
                    LEFT JOIN visit_pttype vs on vs.vn = v.vn
                    LEFT JOIN ovst o on o.vn = v.vn 
                    LEFT JOIN patient p on p.hn=v.hn
                    LEFT JOIN pttype pt on pt.pttype=v.pttype
                    LEFT JOIN opduser op on op.loginname = o.staff
                    WHERE o.vstdate = "'.$date_now.'" 
                    AND p.cid IS NOT NULL AND p.nationality ="99" 
                    AND (vs.claim_code IS NULL OR vs.claim_code ="")
                    AND v.pttype IN("M1","M2","M3","M4","M5","M6") 
                    GROUP BY o.vn
                ');
                    // AND pttype NOT IN("M1","M2","M3","M4","M5","M6","O1","O2","O3","O4","O5","O6","L1","L2","L3","L4","L5","L6") 
                $ch = curl_init(); 
                foreach ($data_vn_1 as $key => $value) {
                        $cid         = $value->cid;
                        $vn          = $value->vn;
                        $vstdate     = $value->vstdate; 
                        $headers = array();
                        $headers[] = "Accept: application/json";
                        $headers[] = "Authorization: Bearer 3045bba2-3cac-4a74-ad7d-ac6f7b187479";    
                        curl_setopt($ch, CURLOPT_URL, "https://nhso.go.th/authencodestatus/api/check-authen-status?personalId=$cid&serviceDate=$vstdate&serviceCode=PG0130001");
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);                         
                        $response = curl_exec($ch); 
                        $contents = $response; 
                        $result = json_decode($contents, true); 
 
                                if ($result != null ) { 
                                    
                                            isset( $result['statusAuthen'] ) ? $statusAuthen = $result['statusAuthen'] : $statusAuthen = "";
                                           
                                            if ($statusAuthen =='false') {
                                              
                                                $his = $result['serviceHistories']; 
                                                // dd($his); 
                                                foreach ($his as $key => $value_s) {
                                                    $cd	           = $value_s["claimCode"];
                                                    $sv_code	   = $value_s["service"]["code"];
                                                    $sv_name	   = $value_s["service"]["name"];
                                                    // dd($sv_name);                                               
                                                    Check_sit_205_auto::where('vn', $vn)
                                                        ->update([
                                                            'claimcode'     => $cd,
                                                            'claimtype'     => $sv_code,
                                                            'servicename'   => $sv_name, 
                                                    ]);
                                                    Visit_pttype_205::where('vn', $vn)
                                                        ->update([
                                                            'claim_code'     => $cd, 
                                                            'auth_code'      => $cd, 
                                                    ]);
                                                    Visit_pttype_217::where('vn', $vn)
                                                        ->update([
                                                            'claim_code'     => $cd, 
                                                            'auth_code'      => $cd, 
                                                    ]);
                                                    Visit_pttype::where('vn','=', $vn)
                                                        ->update([
                                                            'claim_code'     => $cd, 
                                                            'auth_code'      => $cd, 
                                                    ]);
                                                 
                                                    Check_sit_auto::where('vn','=', $vn)
                                                        ->update([
                                                            'claimcode'     => $cd,
                                                            'claimtype'     => $sv_code,
                                                            'servicename'   => $sv_name, 
                                                    ]);
                                                    Fdh_mini_dataset::where('vn','=', $vn)
                                                        ->update([
                                                            'claimcode'     => $cd, 
                                                    ]);
                                                }  
                                            }
                                        // }
                                }
                        // }
                } 
                
                return response()->json('200'); 
    }

    public function mini_dataset_line(Request $request)
    { 
           $date_now = date('Y-m-d');
        //    $date_now = date('2024-06-05');
           $iduser = "754"; 

           $count_visit_all_ = DB::connection('mysql')->select('SELECT COUNT(vn) as Cvn FROM fdh_mini_dataset WHERE vstdate = "'.$date_now.'" AND cid <>""');      
           foreach ($count_visit_all_ as $key => $value) {
                $count_visit_all = $value->Cvn;
           }  
           $sum_total_amount_ = DB::connection('mysql')->select('SELECT sum(total_amout) as sumtotal_amout FROM fdh_mini_dataset WHERE vstdate = "'.$date_now.'" AND cid <>""');      
           foreach ($sum_total_amount_ as $key => $value5) {
                $sum_total_amount = $value5->sumtotal_amout;
           }     
           $count_invoice_ = DB::connection('mysql')->select('SELECT COUNT(vn) as Cvn FROM fdh_mini_dataset WHERE vstdate = "'.$date_now.'" AND invoice_number IS NOT NULL AND cid <>""');      
           foreach ($count_invoice_ as $key => $value2) {
                $count_invoice = $value2->Cvn;
           }  
           $count_uuidnull_ = DB::connection('mysql')->select('SELECT COUNT(vn) as Cvn FROM fdh_mini_dataset WHERE vstdate = "'.$date_now.'" AND uuid_booking IS NULL AND cid <>""');      
           foreach ($count_uuidnull_ as $key => $value3) {
                $count_uuidnull = $value3->Cvn;
           }  
           $count_uuidnotnull_ = DB::connection('mysql')->select('SELECT COUNT(vn) as Cvn FROM fdh_mini_dataset WHERE vstdate = "'.$date_now.'" AND uuid_booking IS NOT NULL AND cid <>""');      
           foreach ($count_uuidnotnull_ as $key => $value4) {
                $count_uuidnotnull = $value4->Cvn;
           }  
           $jong_success_ = DB::connection('mysql')->select('SELECT COUNT(vn) as Cvn FROM fdh_mini_dataset WHERE vstdate = "'.$date_now.'" AND transaction_uid IS NOT NULL AND cid <>""');      
           foreach ($jong_success_ as $key => $value6) {
                $jong_success = $value6->Cvn;
           }  
           $jong_nosuccess_ = DB::connection('mysql')->select('SELECT COUNT(vn) as Cvn FROM fdh_mini_dataset WHERE vstdate = "'.$date_now.'" AND transaction_uid ="" AND cid <>""');      
           foreach ($jong_nosuccess_ as $key => $value7) {
                $jong_nosuccess = $value7->Cvn;
           }  
           $authen_success_ = DB::connection('mysql')->select('SELECT COUNT(vn) as Cvn,sum(total_amout) as sumtotal_amout FROM fdh_mini_dataset WHERE vstdate = "'.$date_now.'" AND claimcode IS NOT NULL');      
           foreach ($authen_success_ as $key => $value8) {
                $authen_success   = $value8->Cvn;
                $sum_total_authen = $value8->sumtotal_amout;
           } 
        //    $sum_total_authen_ = DB::connection('mysql')->select('SELECT sum(total_amout) as sumtotal_amout FROM fdh_mini_dataset WHERE vstdate = "'.$date_now.'" AND claimcode IS NOT NULL');      
        //    foreach ($sum_total_authen_ as $key => $value9) {
        //         $sum_total_authen = $value9->sumtotal_amout;
        //    }    
            
           //แจ้งเตือน 
            function DateThailine($strDate)
            {
                $strYear = date("Y", strtotime($strDate)) + 543;
                $strMonth = date("n", strtotime($strDate));
                $strDay = date("j", strtotime($strDate));

                $strMonthCut = array("", "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค.");
                $strMonthThai = $strMonthCut[$strMonth];
                return "$strDay $strMonthThai $strYear";
            }

            $header = "จองเคลม";
            // $linegroup = DB::table('line_token')->where('line_token_id', '=', 6)->first();
            // $line = $linegroup->line_token_code;

            $link = DB::table('orginfo')->where('orginfo_id', '=', 1)->first();
            $link_line = $link->orginfo_link;

            $datesend = date('Y-m-d');
            $sendate = DateThailine($datesend);
            $user = DB::table('users')->where('id', '=', $iduser)->first();

            function formatetime($strtime)
            {
                $H = substr($strtime, 0, 5);
                return $H;
            }

            $message = $header .               
                "\n" . "วันที่ส่ง: " . $sendate.
                "\n" . "Visit All: " . $count_visit_all ." คน". 
                "\n" . "ยอดจอง : " . number_format($sum_total_amount, 2) . " บาท".
                "\n" . "มีเลข Invoice : " . $count_invoice ." คน". 
                "\n" . "จองสำเร็จ : " . $jong_success ." คน".
                "\n" . "จองไม่สำเร็จ : " .$jong_nosuccess ." คน".
                "\n" . "ดึงข้อมูลจอง : " . $count_uuidnotnull." คน".
                "\n" . "Authenสำเร็จ : " .$authen_success. " คน".
                "\n" . "ยอด Authen: " .number_format($sum_total_authen, 2) ." บาท";

            // $linesend = $line;
            $linesend = "an9Ve40JLaJ3z6XHzzneTIoLQy6RpD5I30GJqEsSJmE";
            if ($linesend == null) {
                $test = '';
            } else {
                $test = $linesend;
            }

            if ($test !== '' && $test !== null) {
                $chOne = curl_init();
                curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
                curl_setopt($chOne, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($chOne, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($chOne, CURLOPT_POST, 1);
                curl_setopt($chOne, CURLOPT_POSTFIELDS, $message);
                curl_setopt($chOne, CURLOPT_POSTFIELDS, "message=$message");
                curl_setopt($chOne, CURLOPT_FOLLOWLOCATION, 1);
                $headers = array('Content-type: application/x-www-form-urlencoded', 'Authorization: Bearer ' . $test . '',);
                curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
                $result = curl_exec($chOne);
                if (curl_error($chOne)) {
                    echo 'error:' . curl_error($chOne);
                } else {
                    $result_ = json_decode($result, true);
                    //return response()->json(['status'=>200]);                        
                }
                curl_close($chOne);
            }
           return response()->json('200'); 
    }

    // public function getimage(Request $request,$id)
    // { 
    //     $productID=explode(".",$id);
    //     $rendered_buffer= Product::all()->find($productID[0])->image;

    //     $response = Response::make($rendered_buffer);
    //     $response->header('Content-Type', 'image/png');
    //     $response->header('Cache-Control','max-age=2592000');
    //     return $response;
    //     //    $date_now = date('Y-m-d');           
    //     //    $data_vn_1 = DB::connection('mysql')->select('SELECT * FROM fire WHERE fire_num = "'.$firenum.'" ');
    //     //    foreach ($data_vn_1 as $key => $value) {
    //     //         $fireactive = $value->active; 
    //     //    }                 
    //     //    return response()->json($fireactive); 
    // }

}





