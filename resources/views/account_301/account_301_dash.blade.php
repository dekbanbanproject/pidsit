@extends('layouts.accountpk')
@section('title', 'PK-OFFICE || ACCOUNT')
 
@section('content')
    <script>
        function TypeAdmin() {
            window.location.href = '{{ route('index') }}';
        }
    </script>
    <?php
    if (Auth::check()) {
        $type = Auth::user()->type;
        $iduser = Auth::user()->id;
    } else {
        echo "<body onload=\"TypeAdmin()\"></body>";
        exit();
    }
    $url = Request::url();
    $pos = strrpos($url, '/') + 1;
    $ynow = date('Y')+543;
    $yb =  date('Y')+542;
    ?>
     
     <style>
        #button {
            display: block;
            margin: 20px auto;
            padding: 30px 30px;
            background-color: #eee;
            border: solid #ccc 1px;
            cursor: pointer;
        }

        #overlay {
            position: fixed;
            top: 0;
            z-index: 100;
            width: 100%;
            height: 100%;
            display: none;
            background: rgba(0, 0, 0, 0.6);
        }

        .cv-spinner {
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .spinner {
            width: 250px;
            height: 250px;
            border: 5px #ddd solid;
            border-top: 10px #12c6fd solid;
            border-radius: 50%;
            animation: sp-anime 0.8s infinite linear;
        }

        @keyframes sp-anime {
            100% {
                transform: rotate(360deg);
            }
        }

        .is-hide {
            display: none;
        }
    </style>

    <?php
        $ynow = date('Y')+543;
        $yb =  date('Y')+542;
    ?>

   <div class="tabs-animation">
    <div class="row text-center">
        <div id="overlay">
            <div class="cv-spinner">
                <span class="spinner"></span>
            </div>
        </div> 
    </div> 
    <div id="preloader">
        <div id="status">
            <div class="spinner"> 
            </div>
        </div>
    </div>
        <form action="{{ url('account_301_dash') }}" method="GET">
            @csrf
            <div class="row mt-2"> 
                <div class="col"></div>
                <div class="col-md-4">
                    <h4 class="card-title" style="color:rgb(10, 151, 85)">Detail 1102050101.301</h4>
                    <p class="card-title-desc">รายละเอียดข้อมูล ผัง 1102050101.301</p>
                </div>
              
                <div class="col-md-1 text-end mt-2">วันที่</div>
                <div class="col-md-3 text-center ">
                    <select name="acc_trimart_id" id="acc_trimart_id" class="form-control inputacc">
                        <option value="">--เลือก--</option>
                        @foreach ($trimart as $item) 
                            <option value="{{$item->acc_trimart_id}}">{{$item->acc_trimart_name}}( {{$item->acc_trimart_start_date}} ถึง {{$item->acc_trimart_end_date}})</option>
                        @endforeach
                    </select> 
                </div>
                <div class="col-md-1 text-start">
                    <button type="submit" class="ladda-button me-2 btn-pill btn btn-primary inputacc" data-style="expand-left">
                        <span class="ladda-label"> <i class="fa-solid fa-magnifying-glass text-white me-2"></i>ค้นหา</span>
                        <span class="ladda-spinner"></span>
                    </button>  
                </div>
                <div class="col"></div>
            </div>
        </form>  
      
        <div class="row">  
            <div class="col"></div> 
            {{-- @if ($startdate !='')  --}}
                <div class="col-xl-9 col-md-9">
                    <div class="card card_audit_4c" style="background-color: rgb(246, 235, 247)">   
                        <div class="table-responsive p-3">                                
                            <table id="example" class="table table-striped table-bordered dt-responsive nowrap myTable"
                                style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead>
                                    <tr>
                                        <th class="text-center">ลำดับ</th> 
                                        <th class="text-center">ไตรมาส</th> 
                                        <th class="text-center">ลูกหนี้ที่ต้องตั้ง</th> 
                                        <th class="text-center">income</th>
                                        <th class="text-center">ลูกหนี้-301</th>  
                                        <th class="text-center">ลูกหนี้-3011</th> 
                                        <th class="text-center">ลูกหนี้-3013</th> 
                                        <th class="text-center">Statement</th>
                                        <th class="text-center">ยกยอดไป</th> 
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        $number = 0; $total1 = 0; $total2 = 0; $total3 = 0; $total4 = 0; $total5 = 0;$total6 = 0;
                                    ?>
                                    @foreach ($datashow as $item)
                                        <?php
                                                $number++;
                                                // $y = $item->year;
                                                $y = date('Y') + 543;
                                                $ynew = $y + 543;
                                           
                                                // ลูกหนี้ทั้งหมด
                                                $datas = DB::select('
                                                    SELECT count(DISTINCT vn) as Can
                                                        ,SUM(debit_total) as sumdebit
                                                        from acc_debtor
                                                        WHERE account_code="1102050101.301"
                                                        AND stamp = "N"
                                                        AND vstdate between "'.$item->acc_trimart_start_date.'" and "'.$item->acc_trimart_end_date.'"
                                                ');
                                                foreach ($datas as $key => $value) {
                                                    $count_N = $value->Can;
                                                    $sum_N = $value->sumdebit;
                                                }
                                            
                                                // ตั้งลูกหนี้
                                                $datasum_ = DB::select('
                                                    SELECT sum(debit_total) as debit_total,sum(income) as sincome,count(DISTINCT vn) as Cvit
                                                    from acc_1102050101_301
                                                    where vstdate between "'.$item->acc_trimart_start_date.'" and "'.$item->acc_trimart_end_date.'"
                                                ');   
                                                foreach ($datasum_ as $key => $value2) {
                                                    $sum_Y      = $value2->debit_total;
                                                    $sum_income = $value2->sincome;
                                                    $count_Y    = $value2->Cvit;
                                                }
                                                $total_sumY   = $sum_Y ;
                                                $total_countY = $count_Y; 

                                                // ตั้งลูกหนี้ OPD 3011
                                                $datasum_3011 = DB::select('
                                                    SELECT sum(debit_total) as debit_total,count(DISTINCT vn) as Cvits
                                                    from acc_1102050101_3011
                                                    where vstdate between "'.$item->acc_trimart_start_date.'" and "'.$item->acc_trimart_end_date.'" 
                                                ');   
                                                foreach ($datasum_3011 as $key => $value5) {
                                                    $sum_ins_sss   = $value5->debit_total;
                                                    $count_vn_sss  = $value5->Cvits;
                                                }

                                                 // ตั้งลูกหนี้ OPD 3013
                                                 $datasum_3013 = DB::select('
                                                    SELECT sum(debit_total) as debit_total,count(DISTINCT vn) as Cvits
                                                    from acc_1102050101_3013
                                                    where vstdate between "'.$item->acc_trimart_start_date.'" and "'.$item->acc_trimart_end_date.'" 
                                                ');   
                                                foreach ($datasum_3013 as $key => $value6) {
                                                    $sum_ct_sss      = $value6->debit_total;
                                                    $count_vnct_sss  = $value6->Cvits;
                                                }
                                            
                                                // STM
                                                // $stm_ = DB::select(
                                                //     'SELECT sum(stm_money) as stm_money,count(DISTINCT vn) as Countvisit FROM acc_1102050101_216 
                                                //     WHERE month(vstdate) = "'.$item->months.'" AND year(vstdate) = "'.$item->year.'" AND (stm_money IS NOT NULL OR stm_money <> "")
                                                // ');                                           
                                                // foreach ($stm_ as $key => $value3) {
                                                //     $sum_stm_money  = $value3->stm_money; 
                                                //     $count_stm      = $value3->Countvisit; 
                                                // }

                                                //STM
                                                $stm_ = DB::select('
                                                    SELECT 
                                                        SUM(ar.acc_stm_repmoney_price301) as total                                                   
                                                        FROM acc_stm_repmoney ar 
                                                        LEFT JOIN acc_trimart a ON a.acc_trimart_id = ar.acc_trimart_id 
                                                        WHERE ar.acc_trimart_id = "'.$item->acc_trimart_id.'" 
                                                        
                                                ');                                           
                                                foreach ($stm_ as $key => $value3) {
                                                    $total301 = $value3->total; 
                                                }
                                                if ( $sum_Y > $total301) {
                                                    $yokpai = $sum_Y - $total301;
                                                } else {
                                                    $yokpai = $total301 - $sum_Y;
                                                }
 
                                        ?>
                               
                                            <tr>
                                                <td class="text-font" style="text-align: center;" width="4%">{{ $number }} </td>  
                                                <td class="p-2">{{$item->acc_trimart_name}} {{$y}}</td>                                         
                                                <td class="text-end" style="color:rgb(73, 147, 231)" width="10%"> {{ number_format($sum_N, 2) }}</td>  
                                                <td class="text-end" width="10%"><a href="{{url('account_301_income/'.$item->acc_trimart_start_date.'/'.$item->acc_trimart_end_date)}}" target="_blank" style="color:rgb(186, 75, 250)"> {{ number_format($sum_income, 2) }}</a></td> 
                                                <td class="text-end" width="10%"><a href="{{url('account_301_dashsub/'.$item->acc_trimart_start_date.'/'.$item->acc_trimart_end_date)}}" target="_blank" style="color:rgb(78, 75, 243)"> {{ number_format($sum_Y, 2) }}</a></td> 
                                                <td class="text-end" width="10%"><a href="{{url('account_301_ins/'.$item->acc_trimart_start_date.'/'.$item->acc_trimart_end_date)}}" target="_blank" style="color:rgb(250, 75, 142)"> {{ number_format($sum_ins_sss, 2) }}</a></td> 
                                                <td class="text-end" width="10%"><a href="{{url('account_301_ct/'.$item->acc_trimart_start_date.'/'.$item->acc_trimart_end_date)}}" target="_blank" style="color:rgb(138, 13, 40)"> {{ number_format($sum_ct_sss, 2) }}</a></td>                                                 
                                                <td class="text-end" style="color:rgb(4, 161, 135)" width="10%">{{ number_format($total301, 2) }} </td> 
                                                <td class="text-end" style="color:rgb(224, 128, 17)" width="10%">0.00</td> 
                                            </tr>
                                        <?php
                                                $total1 = $total1 + $sum_N;
                                                $total6 = $total6 + $sum_income; 
                                                $total2 = $total2 + $sum_Y;                                                
                                                $total3 = $total3 + $sum_ins_sss; 
                                                $total4 = $total4 + $sum_ct_sss; 
                                                $total5 = $total5 + $total301; 
                                        ?>
                                    @endforeach

                                </tbody>
                                <tr style="background-color: #f3fca1">
                                    <td colspan="2" class="text-end" style="background-color: #fca1a1"></td>
                                    <td class="text-end" style="background-color: #47A4FA"><label for="" style="color: #FFFFFF">{{ number_format($total1, 2) }}</label></td>
                                    <td class="text-end" style="background-color: #6842f1"><label for="" style="color: #FFFFFF">{{ number_format($total6, 2) }}</label></td> 
                                    <td class="text-end" style="background-color: #9f4efc" ><label for="" style="color: #FFFFFF">{{ number_format($total2, 2) }}</label></td>                                   
                                    <td class="text-end" style="background-color: #c5224b"><label for="" style="color: #FFFFFF">{{ number_format($total3, 2) }}</label></td>
                                    <td class="text-end" style="background-color: #86122f"><label for="" style="color: #FFFFFF">{{ number_format($total4, 2) }}</label></td>
                                    <td class="text-end" style="background-color: #0ea080"><label for="" style="color: #FFFFFF">{{ number_format($total5, 2) }}</label></td> 
                                    <td class="text-end" style="background-color: #f89625"><label for="" style="color: #FFFFFF">0.00</label></td> 
                                 
                                </tr>  
                            </table>
                        </div>
                    </div>
                </div>
            {{-- @else  --}}
            {{-- @endif --}}
            <div class="col"></div>
        </div>
    </div>

@endsection
@section('footer')
    <script>
        $(document).ready(function() {
            $('#example').DataTable();
            $('#example2').DataTable();
            $('#acc_trimart_id').select2({
                placeholder: "--เลือก--",
                allowClear: true
            });
            $('#datepicker').datepicker({
            format: 'yyyy-mm-dd'
            });
            $('#datepicker2').datepicker({
                format: 'yyyy-mm-dd'
            });

            $('#datepicker3').datepicker({
                format: 'yyyy-mm-dd'
            });
            $('#datepicker4').datepicker({
                format: 'yyyy-mm-dd'
            });

        });
    </script>

@endsection
