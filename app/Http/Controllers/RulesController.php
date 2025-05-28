<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Log;
use Carbon\Carbon;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Http;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Cache;
use App\Models\SmsAnalytics;
use Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Arr;

class RulesController extends BaseController
{
    public static function rulesSync($ruleId,$customers){
        $rule = DB::table('rules')->whereId($ruleId)->first();
        if($rule){
            $ruleCondition = json_decode($rule->rule, true);
            $directColumns = ['email','name','contact_no'];
            $customers->where(function ($query) use ($ruleCondition, $rule, $directColumns) {
                foreach ($ruleCondition as $key => $value) {
                    $conditionMethod = ($rule->rule_condition == "and") ? 'where' : 'orWhere';
                    switch ($value['options']) {
                        case 'include':
                            $searchValues = $value['values'];
                            if(str_contains($value['where'],"date")){
                                $searchValues = $value['date_include_exclude'];
                            }
                            if (in_array($value['where'], $directColumns)) {
                                $query->{$conditionMethod . 'In'}($value['where'], $searchValues);
                            } else {
                                if(str_contains($value['where'],"date")){
                                    $query->{$conditionMethod . 'In'}(
                                        DB::raw("STR_TO_DATE(json_unquote(json_extract(attributes, '$." . $value['where'] . "')), '%d-%m-%Y')"),
                                        $searchValues
                                    );
                                }else{
                                    $query->{$conditionMethod . 'In'}(DB::raw("json_unquote(json_extract(attributes, '$." . $value['where'] . "'))"), $searchValues);
                                }

                            }
                            break;
    
                        case 'exclude':
                            $searchValues = $value['values'];
                            if(str_contains($value['where'],"date")){
                                $searchValues = $value['date_include_exclude'];
                            }
                            if (in_array($value['where'], $directColumns)) {
                                $query->{$conditionMethod . 'NotIn'}($value['where'], $searchValues);
                            } else {
                                if(str_contains($value['where'],"date")){
                                    $query->{$conditionMethod . 'NotIn'}(
                                        DB::raw("STR_TO_DATE(json_unquote(json_extract(attributes, '$." . $value['where'] . "')), '%d-%m-%Y')"),
                                        $searchValues
                                    );
                                }else{
                                    $query->{$conditionMethod . 'NotIn'}(DB::raw("json_unquote(json_extract(attributes, '$." . $value['where'] . "'))"), $searchValues);
                                }
                            }
                            break;
    
                        case 'contains':
                            $searchValue = $value['value'];
                            if(str_contains($value['where'],"date")){
                                $searchValue = $value['date'];
                            }
                            if (in_array($value['where'], $directColumns)) {
                                $query->{$conditionMethod}($value['where'], 'LIKE', '%' . $searchValue . '%');
                            } else {
                                $query->{$conditionMethod}(DB::raw("json_unquote(json_extract(attributes, '$." . $value['where'] . "'))"), 'LIKE', '%' . $searchValue . '%');
                            }
                            break;
    
                        case 'not_contains':
                            $searchValue = $value['value'];
                            if(str_contains($value['where'],"date")){
                                $searchValue = $value['date'];
                            }
                            if (in_array($value['where'], $directColumns)) {
                                $query->{$conditionMethod}($value['where'], 'NOT LIKE', '%' . $searchValue . '%');
                            } else {
                                if(str_contains($value['where'],"date")){
                                    $searchValue = $value['date'];
                                }
                                $query->{$conditionMethod}(DB::raw("json_unquote(json_extract(attributes, '$." . $value['where'] . "'))"), 'NOT LIKE', '%' . $searchValue . '%');
                            }
                            break;
    
                        case 'greater_than':
                            $searchValue = $value['value'];
                            if(str_contains($value['where'],"date")){
                                $searchValue = $value['date'];
                            }
                            if (in_array($value['where'], $directColumns)) {
                                $query->{$conditionMethod}($value['where'], '=', $searchValue);
                            } else {
                                $query->{$conditionMethod}(
                                    DB::raw("STR_TO_DATE(json_unquote(json_extract(attributes, '$." . $value['where'] . "')), '%d-%m-%Y')"), 
                                    '>', 
                                    DB::raw("STR_TO_DATE('$searchValue', '%d-%m-%Y')"));
                            }
                            break;

                        case 'less_than':
                            $searchValue = $value['value'];
                            if(str_contains($value['where'],"date")){
                                $searchValue = $value['date'];
                            }
                            if (in_array($value['where'], $directColumns)) {
                                $query->{$conditionMethod}($value['where'], '=', $searchValue);
                            } else {
                                $query->{$conditionMethod}(
                                    DB::raw("STR_TO_DATE(json_unquote(json_extract(attributes, '$." . $value['where'] . "')), '%d-%m-%Y')"), 
                                    '<', 
                                    DB::raw("STR_TO_DATE('$searchValue', '%d-%m-%Y')"));
                                // $query->{$conditionMethod}(DB::raw("json_unquote(json_extract(attributes, '$." . $value['where'] . "'))"), '<', $searchValue);
                            }
                            break;

                        case 'range':
                            $searchValue = $value['value'];
                            if(str_contains($value['where'],"date")){
                                $searchValue = $value['date'];
                            }
                            if (in_array($value['where'], $directColumns)) {
                                $query->{$conditionMethod}($value['where'], '=', $searchValue);
                            } else {
                                $date_range = $value['date_range'];
                                // echo "<pre>";print_r($date_range);exit;
                                $query->{$conditionMethod}(
                                    DB::raw("STR_TO_DATE(json_unquote(json_extract(attributes, '$." . $value['where'] . "')), '%d-%m-%Y')"),
                                    '>=',
                                    DB::raw("STR_TO_DATE('" . $date_range[0] . "', '%d-%m-%Y')")
                                )->where(
                                    DB::raw("STR_TO_DATE(json_unquote(json_extract(attributes, '$." . $value['where'] . "')), '%d-%m-%Y')"),
                                    '<=',
                                    DB::raw("STR_TO_DATE('" . $date_range[1] . "', '%d-%m-%Y')")
                                );
                                // $query->{$conditionMethod}(DB::raw("json_unquote(json_extract(attributes, '$." . $value['where'] . "'))"), '<', $searchValue);
                            }
                            break;

                    }
                }
            });
        }
        return $customers;
    }
}
