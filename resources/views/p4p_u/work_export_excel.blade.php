 
   
   <?php
   header("Content-Type: application/vnd.ms-excel");
   header('Content-Disposition: attachment; filename="p4p.xls"');//ชื่อไฟล์
   
   function DateThais($strDate)
   {
     $strYear = date("Y",strtotime($strDate))+543;
     $strMonth= date("n",strtotime($strDate));
     $strDay= date("j",strtotime($strDate));
   
     $strMonthCut = Array("","ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค.");
     $strMonthThai=$strMonthCut[$strMonth];
     return "$strDay $strMonthThai $strYear";
     }
   
     function getAges($birthday) {
       $then = strtotime($birthday);
       return(floor((time()-$then)/31556926));
   }
   
   ?>
<center>
    <br><br>
   <label for="" style="font-family: 'Kanit', sans-serif;font-size:15px;"><b>รายงานผลปฏิบัติงานนอกเวลาราชการและวันหยุดราชการ (ตามภาระงานที่เพิ่มขึ้น)</b></label><br>
   <label for="" style="font-family: 'Kanit', sans-serif;font-size:15px;"><b>กลุ่มงาน/งาน</b>  {{$dep_sub_sub->DEPARTMENT_SUB_SUB_NAME}}  <b>กลุ่มภารกิจ </b>{{$dep->DEPARTMENT_NAME}} </label><br>
   <label for="" style="font-family: 'Kanit', sans-serif;font-size:15px;"><b>ประจำเดือน...{{$monthth}}...พ.ศ…{{($check_year)+543}}...</b></label><br>
</center>
   <br><br>
   <table class="table" style="width: 100%">
       <thead>
           <tr>
              
               <th style="font-family: 'Kanit', sans-serif;border-color:#F0FFFF;text-align: center;border: 1px solid black;" width="10%">ลำดับ</th>
               <th style="font-family: 'Kanit', sans-serif;border-color:#F0FFFF;text-align: center;border: 1px solid black;" width="10%">หน่วยนับ</th> 
               <th style="font-family: 'Kanit', sans-serif;border-color:#F0FFFF;text-align: center;border: 1px solid black;">รายการ</th>
               <th style="font-family: 'Kanit', sans-serif;border-color:#F0FFFF;text-align: center;border: 1px solid black;" width="10%">แต้ม/ชิ้นงาน</th>
               <th style="font-family: 'Kanit', sans-serif;border-color:#F0FFFF;text-align: center;border: 1px solid black;">รวมชิ้นงาน</th>              
               <th style="font-family: 'Kanit', sans-serif;border-color:#F0FFFF;text-align: center;border: 1px solid black;" width="10%">รวมแต้ม</th> 
              
           </tr>
       </thead>
       <tbody>
        <?php $number = 0;?>
        @foreach ($p4p_workload as $item) 
        <?php $number++;?>
        <?php  
                $qty = $item->p4p_workload_1+$item->p4p_workload_2+$item->p4p_workload_3+$item->p4p_workload_4+$item->p4p_workload_5
                +$item->p4p_workload_6+$item->p4p_workload_7+$item->p4p_workload_8+$item->p4p_workload_9+$item->p4p_workload_10
                +$item->p4p_workload_11+$item->p4p_workload_12+$item->p4p_workload_13+$item->p4p_workload_14+$item->p4p_workload_15
                +$item->p4p_workload_16+$item->p4p_workload_17+$item->p4p_workload_18+$item->p4p_workload_19+$item->p4p_workload_20
                +$item->p4p_workload_21+$item->p4p_workload_22+$item->p4p_workload_23+$item->p4p_workload_24+$item->p4p_workload_25
                +$item->p4p_workload_26+$item->p4p_workload_27+$item->p4p_workload_28+$item->p4p_workload_29+$item->p4p_workload_30+$item->p4p_workload_31                                                    
            ?>
            <tr>
                <td style="font-family: 'Kanit', sans-serif;border-color:#F0FFFF;text-align: center;border: 1px solid black;" align="center" width="10%">{{ $number }} </td>
                <td style="font-family: 'Kanit', sans-serif;border-color:#F0FFFF;text-align: center;border: 1px solid black;" align="center" width="10%">{{ $item->p4p_workgroupset_unit_name }} </td> 
                <td style="font-family: 'Kanit', sans-serif;border-color:#F0FFFF;text-align: left;border: 1px solid black;">{{ $item->p4p_workset_name }}  </td>
                
                <td style="font-family: 'Kanit', sans-serif;border-color:#F0FFFF;text-align: center;border: 1px solid black;" align="center" width="10%">{{ $item->p4p_workset_score }} </td>
                <td style="font-family: 'Kanit', sans-serif;border-color:#F0FFFF;text-align: center;border: 1px solid black;" align="center" width="10%">{{ $qty}}</td>
                <td style="font-family: 'Kanit', sans-serif;border-color:#F0FFFF;text-align: center;border: 1px solid black;">&nbsp;&nbsp;{{ $item->p4p_workload_sum }}</td>
              
   
            </tr>
        @endforeach        
       </tbody> 
       <tr>
            <td colspan="3" class="text-end" style="background-color: #fca1a1"></td>
            <td class="text-center" style="background-color: #f3fca1;text-align: center">{{ number_format($data_p4p_, 5)}}</td>
            <td class="text-center" style="background-color: #b5eb82;text-align: center">{{$total_qty}}</td>
            <td class="text-center" style="background-color: #fcd8a1;text-align: center">{{ number_format($data_p4p_sum, 2)}}</td>
            {{-- <td class="text-center" style="background-color: #f3fca1;text-align: center"> </td> --}}
        </tr>  
       
        <tr> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td></tr>
        <tr> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td></tr>  
        <br>
        <tr> 
            <td colspan="2">
               <center>
                <label for="" style="font-family: 'Kanit', sans-serif;font-size:15px;"> ลงชื่อ……………………………………………….ผู้ควบคุม</label><br>
                <label for="" style="font-family: 'Kanit', sans-serif;font-size:15px;"> ( ........................................................... )</label><br> 
                <label for="" style="font-family: 'Kanit', sans-serif;font-size:15px;"> ( หัวหน้ากลุ่มงาน..................................................... ) </label><br>
               </center>
            </td>
            <td></td>
            <td colspan="2">
                <center>
                 <label for="" style="font-family: 'Kanit', sans-serif;font-size:15px;"> ลงชื่อ……………………………………………….ผู้จตรวจสอบ</label><br>
                 <label for="" style="font-family: 'Kanit', sans-serif;font-size:15px;"> ( ........................................................... )</label><br> 
                 <label for="" style="font-family: 'Kanit', sans-serif;font-size:15px;"> ( หัวหน้ากลุ่มภารกิจ..................................................... )</label><br>
                </center>
             </td>
        </tr>    
   </table>
   
 

   