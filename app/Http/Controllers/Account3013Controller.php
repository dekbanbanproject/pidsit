<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\support\Facades\Hash;
use Illuminate\support\Facades\Validator;
use App\Models\User;
use App\Models\Acc_debtor;
use App\Models\Pttype_eclaim;
use App\Models\Account_listpercen;
use App\Models\Leave_month;
use App\Models\Acc_debtor_stamp;
use App\Models\Acc_debtor_sendmoney;
use App\Models\Pttype;
use App\Models\Pttype_acc;
use App\Models\Acc_stm_ti;
use App\Models\Acc_stm_ti_total;
use App\Models\Acc_opitemrece;
use App\Models\Acc_1102050101_202;
use App\Models\Acc_1102050101_217;
use App\Models\Acc_1102050101_2166;
use App\Models\Acc_stm_ucs;
use App\Models\Acc_1102050101_301;
use App\Models\Acc_1102050101_304;
use App\Models\Acc_1102050101_308;
use App\Models\Acc_1102050101_4011;
use App\Models\Acc_1102050101_3099;
use App\Models\Acc_1102050101_401;
use App\Models\Acc_1102050101_402;
use App\Models\Acc_1102050102_801;
use App\Models\Acc_1102050102_802;
use App\Models\Acc_1102050102_803;
use App\Models\Acc_1102050102_804;
use App\Models\Acc_1102050101_4022;
use App\Models\Acc_1102050102_602;
use App\Models\Acc_1102050102_603;
use App\Models\Acc_stm_prb;
use App\Models\Acc_stm_ti_totalhead;
use App\Models\Acc_stm_ti_excel;
use App\Models\Acc_stm_ofc;
use App\Models\acc_stm_ofcexcel;
use App\Models\Acc_stm_lgo;
use App\Models\Acc_stm_lgoexcel;
use App\Models\Check_sit_auto;
use App\Models\Acc_1102050101_3013;

use PDF;
use setasign\Fpdi\Fpdi;
use App\Models\Budget_year;
use Illuminate\Support\Facades\File;
use DataTables;
use Intervention\Image\ImageManagerStatic as Image;
use App\Mail\DissendeMail;
use Mail;
use Illuminate\Support\Facades\Storage;
use Auth;
use Http;
use SoapClient;
// use File;
// use SplFileObject;
use Arr;
// use Storage;
use GuzzleHttp\Client;

use App\Imports\ImportAcc_stm_ti;
use App\Imports\ImportAcc_stm_tiexcel_import;
use App\Imports\ImportAcc_stm_ofcexcel_import;
use App\Imports\ImportAcc_stm_lgoexcel_import;
use App\Models\Acc_1102050101_217_stam;
use App\Models\Acc_opitemrece_stm;

use SplFileObject;
use PHPExcel;
use PHPExcel_IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\IOFactory;

date_default_timezone_set("Asia/Bangkok");


class Account3013Controller extends Controller
 {
    // ***************** 3013********************************
     
    public function account_3011_dash(Request $request)
    {
        $startdate = $request->startdate;
        $enddate = $request->enddate;
        $acc_trimart_id = $request->acc_trimart_id;

        $dabudget_year = DB::table('budget_year')->where('active','=',true)->first();
        $leave_month_year = DB::table('leave_month')->orderBy('MONTH_ID', 'ASC')->get();
        $date = date('Y-m-d');
        $y = date('Y') + 543;
        $newweek = date('Y-m-d', strtotime($date . ' -1 week')); //ย้อนหลัง 1 สัปดาห์
        $newDate = date('Y-m-d', strtotime($date . ' -5 months')); //ย้อนหลัง 5 เดือน
        $newyear = date('Y-m-d', strtotime($date . ' -1 year')); //ย้อนหลัง 1 ปี 
       
        $data_ = DB::table('acc_trimart')->where('acc_trimart_id','=',$acc_trimart_id)->first();
        
       if ($acc_trimart_id == '') {
            $data_trimart = DB::table('acc_trimart')->where('active','Y')->limit(12)->orderBy('acc_trimart_id','desc')->get();
            $trimart = DB::table('acc_trimart')->orderBy('acc_trimart_id','desc')->get();
       } else {
            // $data_trimart = DB::table('acc_trimart')->whereBetween('dchdate', [$startdate, $enddate])->orderBy('acc_trimart_id','desc')->get();
            $data_trimart = DB::table('acc_trimart')->where('active','Y')->where('acc_trimart_id','=',$acc_trimart_id)->orderBy('acc_trimart_id','desc')->get();
            $trimart = DB::table('acc_trimart')->orderBy('acc_trimart_id','desc')->get();
       }       

        return view('account_3011.account_3011_dash',[
            'startdate'        => $startdate,
            'enddate'          => $enddate,
            'leave_month_year' => $leave_month_year,
            'data_trimart'     => $data_trimart,
            'newyear'          => $newyear,
            'date'             => $date,
            'trimart'          => $trimart,
        ]);
    }
       
    public function account_3013_pull(Request $request)
    {
        $datenow = date('Y-m-d');
        $months = date('m');
        $year = date('Y');
        // dd($year);
        $startdate = $request->startdate;
        $enddate = $request->enddate;
        if ($startdate == '') {
            // $acc_debtor = Acc_debtor::where('stamp','=','N')->whereBetween('dchdate', [$datenow, $datenow])->get();
            $acc_debtor = DB::select('
                SELECT a.*,c.subinscl from acc_debtor a 
                left join checksit_hos c on c.vn = a.vn
                WHERE a.account_code="1102050101.3013"
                AND a.stamp = "N"
                group by a.vn
                order by a.vstdate desc
            ');
            // and month(a.dchdate) = "'.$months.'" and year(a.dchdate) = "'.$year.'"
        } else {
            $acc_debtor = DB::select('
                SELECT a.*,c.subinscl from acc_debtor a
                left join checksit_hos c on c.vn = a.vn
                WHERE a.account_code="1102050101.3013"
                AND a.stamp = "N"
                group by a.vn
                order by a.vstdate desc
            ');
            // $acc_debtor = Acc_debtor::where('stamp','=','N')->whereBetween('dchdate', [$startdate, $enddate])->get();
        }

        return view('account_3013.account_3013_pull',[
            'startdate'     =>     $startdate,
            'enddate'       =>     $enddate,
            'acc_debtor'    =>     $acc_debtor,
        ]);
    }
    public function account_3013_pulldata(Request $request)
    {
        $datenow = date('Y-m-d');
        $datetimenow = date('Y-m-d H:i:s');
        
        $startdate = $request->datepicker;
        $enddate = $request->datepicker2; 
            $acc_debtor = DB::connection('mysql2')->select(' 
                    SELECT * FROM
                    (
                        SELECT i.an,v.hn,v.vn,v.cid,v.vstdate,i.dchdate,concat(p.pname,p.fname," ",p.lname) as ptname,v.pttype,d.cc,h.hospcode,ro.icd10 as referin_no,h.name as hospmain
                        ,"07" as acc_code,"1102050101.3013" as account_code,"ลูกหนี้ค่ารักษาประกันสังคม - OP CT" as account_name,v.pdx,v.dx0
                        ,v.income,v.uc_money ,v.discount_money,v.rcpt_money,v.paid_money  
                        ,ov.name as active_status 

                        ,CASE WHEN (SELECT SUM(sum_price) sum_price FROM opitemrece WHERE icode = "3009147" AND vn = v.vn) THEN "1200" 
                        ELSE "0.00" 
                        END as debit_without
                        ,CASE WHEN (SELECT SUM(sum_price) sum_price FROM opitemrece WHERE icode = "3009148" AND vn = v.vn) THEN "1200" 
                        ELSE "0.00" 
                        END as debit_with
                        ,CASE WHEN (SELECT SUM(sum_price) sum_price FROM opitemrece WHERE icode = "3010860" AND vn = v.vn) THEN "2500" 
                        ELSE "0.00" 
                        END as debit_upper
                        ,CASE WHEN (SELECT SUM(sum_price) sum_price FROM opitemrece WHERE icode = "3009187" AND vn = v.vn) THEN "2500" 
                        ELSE "0.00" 
                        END as debit_lower
                        ,CASE WHEN (SELECT SUM(sum_price) sum_price FROM opitemrece WHERE icode = "3009143" AND vn = v.vn) THEN "1000" 
                        ELSE "0.00" 
                        END as debit_multiphase
                        ,CASE WHEN (SELECT SUM(sum_price) sum_price FROM opitemrece WHERE icode = "3011265" AND vn = v.vn) THEN "1100" 
                        ELSE "0.00" 
                        END as debit_drug100
                        ,CASE WHEN (SELECT SUM(sum_price) sum_price FROM opitemrece WHERE icode = "3011266" AND vn = v.vn) THEN "1100" 
                        ELSE "0.00" 
                        END as debit_drug150
                        ,CASE WHEN (SELECT SUM(sum_price) sum_price FROM opitemrece WHERE icode IN("3009178","3009148") AND vn = v.vn) THEN "2500" 
                        ELSE "0.00" 
                        END as ct_cwith_bwith
                        
                        ,(SELECT SUM(rcptamt) sumprice FROM incoth WHERE income <> "08" AND vn = v.vn) AS price_noct
                        ,case
                        when v.uc_money < 1000 then v.uc_money
                        else "1000"
                        end as toklong
                        from vn_stat v
                        left outer join ipt i on i.vn = v.vn
                        left outer join patient p on p.hn = v.hn
                        left outer join pttype pt on pt.pttype =v.pttype 
                        left outer join icd101 oo on oo.code IN(v.pdx,v.dx0,v.dx1,v.dx2,v.dx3,v.dx4,v.dx5 )
                        left outer join opdscreen d on d.vn = v.vn
                        left outer join hospcode h on h.hospcode = v.hospmain 
                        left outer join referin ro on ro.vn = v.vn 
                        left outer join opitemrece om on om.vn = v.vn  
                        LEFT OUTER JOIN ovst ot on ot.vn = v.vn
                        LEFT OUTER JOIN ovstost ov on ov.ovstost = ot.ovstost
                        LEFT OUTER JOIN xray_report x on x.vn = v.vn 
			            LEFT OUTER JOIN xray_items xi on xi.xray_items_code = x.xray_items_code 
                        where v.vstdate BETWEEN "' . $startdate . '" AND "' . $enddate . '"
                        and i.an is null AND v.uc_money <> 0 AND p.nationality = "99"   
                        and v.pttype IN (SELECT pttype FROM pkbackoffice.acc_setpang_type WHERE pang ="1102050101.3013" AND opdipd ="OPD")
                        and (v.pdx not like "c%" and v.pdx not like "b24%" and v.pdx not like "n185%" )                        
                        and (oo.code  BETWEEN "E110" and "E149" or oo.code  BETWEEN "I10" and "I150" or oo.code  BETWEEN "J440" and "J449")
                        group by v.vn
                
                        UNION
                
                        SELECT i.an,v.hn,v.vn,v.cid,v.vstdate,i.dchdate,concat(p.pname,p.fname," ",p.lname) as ptname,v.pttype,d.cc,h.hospcode,ro.icd10 as referin_no,h.name as hospmain
                        ,"07" as acc_code,"1102050101.3013" as account_code,"ลูกหนี้ค่ารักษาประกันสังคม - OP CT" as account_name,v.pdx,v.dx0
                        ,v.income,v.uc_money ,v.discount_money,v.rcpt_money,v.paid_money  
                        ,ov.name as active_status 
                        
                        ,CASE WHEN (SELECT SUM(sum_price) sum_price FROM opitemrece WHERE icode = "3009147" AND vn = v.vn) THEN "1200" 
                        ELSE "0.00" 
                        END as debit_without
                        ,CASE WHEN (SELECT SUM(sum_price) sum_price FROM opitemrece WHERE icode = "3009148" AND vn = v.vn) THEN "1200" 
                        ELSE "0.00" 
                        END as debit_with
                        ,CASE WHEN (SELECT SUM(sum_price) sum_price FROM opitemrece WHERE icode = "3010860" AND vn = v.vn) THEN "2500" 
                        ELSE "0.00" 
                        END as debit_upper
                        ,CASE WHEN (SELECT SUM(sum_price) sum_price FROM opitemrece WHERE icode = "3009187" AND vn = v.vn) THEN "2500" 
                        ELSE "0.00" 
                        END as debit_lower
                        ,CASE WHEN (SELECT SUM(sum_price) sum_price FROM opitemrece WHERE icode = "3009143" AND vn = v.vn) THEN "1000" 
                        ELSE "0.00" 
                        END as debit_multiphase
                        ,CASE WHEN (SELECT SUM(sum_price) sum_price FROM opitemrece WHERE icode = "3011265" AND vn = v.vn) THEN "1100" 
                        ELSE "0.00" 
                        END as debit_drug100
                        ,CASE WHEN (SELECT SUM(sum_price) sum_price FROM opitemrece WHERE icode = "3011266" AND vn = v.vn) THEN "1100" 
                        ELSE "0.00" 
                        END as debit_drug150
                        ,CASE WHEN (SELECT SUM(sum_price) sum_price FROM opitemrece WHERE icode IN("3009178","3009148") AND vn = v.vn) THEN "2500" 
                        ELSE "0.00" 
                        END as ct_cwith_bwith
                        
                        ,(SELECT SUM(rcptamt) sumprice FROM incoth WHERE income <> "08" AND vn = v.vn) AS price_noct
                        ,case
                        when v.uc_money < 700 then v.uc_money
                        else "700"
                        end as toklong
                        from vn_stat v
                        left outer join ipt i on i.vn = v.vn
                        left outer join patient p on p.hn = v.hn
                        left outer join pttype pt on pt.pttype =v.pttype 
                        left outer join ovstdiag oo on oo.vn = v.vn
                        left outer join opdscreen d on d.vn = v.vn
                        left outer join hospcode h on h.hospcode = v.hospmain 
                        left outer join referin ro on ro.vn = v.vn 
                        left outer join opitemrece om on om.vn = v.vn 
                        LEFT OUTER JOIN ovst ot on ot.vn = v.vn
                        LEFT OUTER JOIN ovstost ov on ov.ovstost = ot.ovstost
                        LEFT OUTER JOIN xray_report x on x.vn = v.vn 
			            LEFT OUTER JOIN xray_items xi on xi.xray_items_code = x.xray_items_code 
                        where v.vstdate BETWEEN "' . $startdate . '" AND "' . $enddate . '"
                        and i.an is null AND v.uc_money <> 0  AND p.nationality = "99"  
                        and v.pttype IN (SELECT pttype FROM pkbackoffice.acc_setpang_type WHERE pang ="1102050101.3013" AND opdipd ="OPD")
                        and (v.pdx not like "c%" and v.pdx not like "b24%" and v.pdx not like "n185%" )                        
                        AND v.pdx NOT BETWEEN "E110" AND "E149" AND v.pdx NOT BETWEEN "J440" AND "J449" AND v.pdx NOT BETWEEN "I10" AND "I159"
                        AND v.dx0 NOT BETWEEN "E110" AND "E149" AND v.dx0 NOT BETWEEN "J440" AND "J449" AND v.dx0 NOT BETWEEN "I10" AND "I159"
                        AND v.dx1 NOT BETWEEN "E110" AND "E149" AND v.dx1 NOT BETWEEN "J440" AND "J449" AND v.dx1 NOT BETWEEN "I10" AND "I159"
                        AND v.dx2 NOT BETWEEN "E110" AND "E149" AND v.dx2 NOT BETWEEN "J440" AND "J449" AND v.dx2 NOT BETWEEN "I10" AND "I159"
                        AND v.dx3 NOT BETWEEN "E110" AND "E149" AND v.dx3 NOT BETWEEN "J440" AND "J449" AND v.dx3 NOT BETWEEN "I10" AND "I159"
                        AND v.dx4 NOT BETWEEN "E110" AND "E149" AND v.dx4 NOT BETWEEN "J440" AND "J449" AND v.dx4 NOT BETWEEN "I10" AND "I159"
                        AND v.dx5 NOT BETWEEN "E110" AND "E149" AND v.dx5 NOT BETWEEN "J440" AND "J449" AND v.dx5 NOT BETWEEN "I10" AND "I159"
                        group by v.vn
                    ) As Refer 
            ');         
            // ,(SELECT SUM(sum_price) sum_price FROM opitemrece WHERE icode NOT IN("3009147","3009148","3010860","3009187","3009143","3011265","3011266") AND vn = v.vn) as pricenoct           
            foreach ($acc_debtor as $key => $value) { 
                $check = Acc_debtor::where('vn', $value->vn)->where('account_code','1102050101.3013')->count();

                if ($check > 0 ) { 
                } else {
                    if ($value->debit_without > 0 || $value->debit_with > 0 || $value->debit_upper > 0 || $value->debit_lower > 0 || $value->debit_multiphase > 0 || $value->debit_drug100 > 0 || $value->debit_drug150 > 0 || $value->ct_cwith_bwith > 0) {
                        Acc_debtor::insert([
                            'hn'                 => $value->hn,
                            'an'                 => $value->an,
                            'vn'                 => $value->vn,
                            'cid'                => $value->cid,
                            'ptname'             => $value->ptname,
                            'pttype'             => $value->pttype,
                            'vstdate'            => $value->vstdate,
                            'dchdate'            => $value->dchdate,
                            'acc_code'           => $value->acc_code,
                            'account_code'       => $value->account_code,
                            'account_name'       => $value->account_name, 
                            'hospcode'           => $value->hospcode,
                            'income'             => $value->income,
                            'uc_money'           => $value->uc_money,
                            'discount_money'     => $value->discount_money,
                            'paid_money'         => $value->paid_money,
                            'rcpt_money'         => $value->rcpt_money,
                            'debit'              => $value->uc_money, 
                            'debit_total'        => $value->price_noct, 
                            'referin_no'         => $value->referin_no, 
                            'pdx'                => $value->pdx, 
                            'dx0'                => $value->dx0, 
                            'cc'                 => $value->cc, 
                            'ct_price'           => ($value->debit_without+$value->debit_with+$value->debit_upper+$value->debit_lower+$value->debit_multiphase+$value->debit_drug100+$value->debit_drug150+$value->ct_cwith_bwith), 
                            'ct_sumprice'        => '100',  
                            'sauntang'           => ($value->uc_money)-($value->debit_without+$value->debit_with+$value->debit_upper+$value->debit_lower+$value->debit_multiphase+$value->debit_drug100+$value->debit_drug150+$value->ct_cwith_bwith)-('100'), 
                            'acc_debtor_userid'  => Auth::user()->id,
                            'date_pull'          => $datetimenow,
                            'active_status'      => $value->active_status,
                            'referin_no'         => $value->referin_no,
                        ]);
                    }else{
                        Acc_debtor::insert([
                            'hn'                 => $value->hn,
                            'an'                 => $value->an,
                            'vn'                 => $value->vn,
                            'cid'                => $value->cid,
                            'ptname'             => $value->ptname,
                            'pttype'             => $value->pttype,
                            'vstdate'            => $value->vstdate,
                            'dchdate'            => $value->dchdate,
                            'acc_code'           => $value->acc_code,
                            'account_code'       => $value->account_code,
                            'account_name'       => $value->account_name, 
                            'hospcode'           => $value->hospcode,
                            'income'             => $value->income,
                            'uc_money'           => $value->uc_money,
                            'discount_money'     => $value->discount_money,
                            'paid_money'         => $value->paid_money,
                            'rcpt_money'         => $value->rcpt_money,
                            'debit'              => $value->uc_money, 
                            'debit_total'        => $value->toklong, 
                            'referin_no'         => $value->referin_no, 
                            'pdx'                => $value->pdx, 
                            'dx0'                => $value->dx0, 
                            'cc'                 => $value->cc, 
                            'ct_price'           => ($value->debit_without+$value->debit_with+$value->debit_upper+$value->debit_lower+$value->debit_multiphase+$value->debit_drug100+$value->debit_drug150+$value->ct_cwith_bwith), 
                            'ct_sumprice'        => '100',  
                            'sauntang'           => ($value->uc_money)-($value->debit_without+$value->debit_with+$value->debit_upper+$value->debit_lower+$value->debit_multiphase+$value->debit_drug100+$value->debit_drug150+$value->ct_cwith_bwith)-('100'), 
                            'acc_debtor_userid'  => Auth::user()->id,
                            'date_pull'          => $datetimenow,
                            'active_status'      => $value->active_status,
                            'referin_no'         => $value->referin_no,
                        ]);
                     
                    }
              
                }                
            }      
            return response()->json([

                'status'    => '200'
            ]);
    }
    
   
    public function account_3013_stam(Request $request)
    {
        $id = $request->ids;
        $iduser = Auth::user()->id;
        $data = Acc_debtor::whereIn('acc_debtor_id',explode(",",$id))->get();
            Acc_debtor::whereIn('acc_debtor_id',explode(",",$id))
                    ->update([
                        'stamp' => 'Y'
                    ]);
        foreach ($data as $key => $value) {
                $date = date('Y-m-d H:m:s');
             
                $check = Acc_1102050101_3013::where('vn', $value->vn)->count();
                if ($check > 0) {
                # code...
                } else {
                    if ($value->ct_sumprice > 0) {
                        $ct_sumprice = 'C';
                    } else {
                        $ct_sumprice = 'R';
                    }                    
                    Acc_1102050101_3013::insert([ 
                            'hn'                 => $value->hn,
                            'an'                 => $value->an,
                            'vn'                 => $value->vn,
                            'cid'                => $value->cid,
                            'ptname'             => $value->ptname,
                            'pttype'             => $value->pttype,
                            'vstdate'            => $value->vstdate,
                            'dchdate'            => $value->dchdate,
                            'acc_code'           => $value->acc_code,
                            'account_code'       => $value->account_code, 
                            'income'             => $value->income,
                            'uc_money'           => $value->uc_money,
                            'discount_money'     => $value->discount_money, 
                            'rcpt_money'         => $value->rcpt_money,
                            'debit'              => $value->debit, 
                            'debit_total'        => $value->debit_total, 
                            'hospcode'           => $value->hospcode, 
                            'cc'                 => $value->cc, 
                            'sauntang'           => $value->sauntang, 
                            'referin_no'         => $value->referin_no, 
                            'ct_price'           => $value->ct_price, 
                            'ct_sumprice'        => $value->ct_sumprice, 
                            'pdx'                => $value->pdx, 
                            'dx0'                => $value->dx0, 
                            'acc_debtor_userid'  => $iduser
                    ]);
                }

        }
        return response()->json([
            'status'    => '200'
        ]);
    }
    public function account_3013_destroy(Request $request)
    {
        $id = $request->ids;
        $iduser = Auth::user()->id;
        $data = Acc_debtor::whereIn('acc_debtor_id',explode(",",$id))->get();
            Acc_debtor::whereIn('acc_debtor_id',explode(",",$id))->delete();
                   
        return response()->json([
            'status'    => '200'
        ]);
    }

    public function account_3013_search(Request $request)
    {
        $datenow = date('Y-m-d');
        $startdate = $request->startdate;
        $enddate = $request->enddate;
        $date = date('Y-m-d'); 
        $new_day = date('Y-m-d', strtotime($date . ' -5 day')); //ย้อนหลัง 1 วัน
        $data['users'] = User::get();
        if ($startdate =='') {
           $datashow = DB::select(' 
               SELECT * from acc_1102050101_3013 
               WHERE vstdate BETWEEN "'.$new_day.'" AND  "'.$date.'"  
           ');
        } else {
           $datashow = DB::select(' 
               SELECT * from acc_1102050101_3013
               WHERE vstdate BETWEEN "'.$startdate.'" AND  "'.$enddate.'"  
           ');
        } 
        return view('account_3013.account_3013_search', $data, [
            'startdate'     => $startdate,
            'enddate'       => $enddate,
            'datashow'      => $datashow,
            'startdate'     => $startdate,
            'enddate'       => $enddate
        ]);
    }











    public function account_301_detail(Request $request,$startdate,$enddate)
    {
        $datenow = date('Y-m-d');
        $startdate = $request->startdate;
        $enddate = $request->enddate;
        // dd($id);
        $data['users'] = User::get();

        $data = DB::select('
        SELECT U1.vn,U1.hn,U1.cid,U1.ptname,U1.vstdate,U1.pttype,U1.debit_total
            from acc_1102050101_301 U1
        
            WHERE U1.vstdate BETWEEN "'.$startdate.'" and "'.$enddate.'"
            GROUP BY U1.vn
        ');
        // WHERE month(U1.vstdate) = "'.$months.'" and year(U1.vstdate) = "'.$year.'"
        return view('account_301.account_301_detail', $data, [ 
            'data'          =>     $data,
            'startdate'     =>     $startdate,
            'enddate'       =>     $enddate
        ]);
    }

    public function account_301_detail_date(Request $request)
    { 
        $data['users'] = User::get();
        $startdate = $request->startdate;
        $enddate = $request->enddate;

        $data = DB::select('
        SELECT U1.vn,U1.hn,U1.cid,U1.ptname,U1.vstdate,U1.pttype,U1.debit_total
            from acc_1102050101_301 U1
        
            WHERE U1.vstdate BETWEEN "'.$startdate.'" and "'.$enddate.'"
            GROUP BY U1.vn
        ');
        return view('account_301.account_301_detail_date', $data, [ 
            'data'           =>     $data,
            'startdate'      =>     $startdate,
            'enddate'        =>     $enddate
        ]);
    }
    
   
 

 }