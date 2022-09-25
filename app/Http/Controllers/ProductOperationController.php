<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\User;

class ProductOperationController extends Controller
{
    public static function CalculatDays(Product $product){
        $days=0;
        $months=0;
        $expiration_date=$product->expiration_date;
        //حساب عدد الايام بين تاريخ اليوم وتاريخ انتهاء الصلاحية 
        if($product->expiration_date>date('Y-n-j')){
            $expiration_date=$product->expiration_date;
            //تقطيع تاريخ انتهاء الصلاحية 
            $year=substr($expiration_date,0,4);
            $month=substr($expiration_date,5,2);
            $day=substr($expiration_date,8,2);
           //تحويل تاريخ انتهاء الصلاحية الى اعداد للقيام بعمليات  حسابية عليه لحساب تاريخ الفترات
            $exp_year=intval($year);
            $exp_month=intval($month);
            $exp_day=intval($day);

            if($exp_day>=date('j')){
                $days+=$exp_day-date('j');
            }
            else{
                $exp_month-=1;
                $exp_day+=30;
                $days+=$exp_day-date('j');
            }
            if($exp_month>=date('n')){
                $months=$exp_month-date('n');
                $days+=$months*30;
            }
            else{
                $exp_year-=1;
                $exp_month+=12;
                $months=$exp_month-date('n');
                if($exp_month == 1 || $exp_month == 3 || $exp_month == 5 || $exp_month == 7 || $exp_month == 9 || $exp_month == 10 || $exp_month == 12)
                    $days+=$months*31;
                else if($exp_year%4==0){
                    $days+=$months*29;
                }
                else if($exp_year%4!=0){
                    $days+=$months*28;
                }
                else{
                    $days+=$months*30;
                }
            }
            if($exp_year>date('Y')){
                $days+=($exp_year-date('Y'))*365;
            }
            return $days;
        }
    }

    public static function CalculatDiscount(Product $product){
        $days=ProductOperationController::CalculatDays($product);
        $period_one=$product->period_one;
        $period_two=$product->period_two;
        $period_three=$product->period_three;

        if($days>=$period_one){
            return $product->discounts_one;
        }
        else if($days>= $period_two){
            return $product->discounts_two;
        }
        else{
            return $product->discounts_three;
        }

    }

}
