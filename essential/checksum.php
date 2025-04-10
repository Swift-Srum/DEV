<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);

class Checksum {
    public $salt = '#ARZh@t6GwQM6f7UMwjxqENp1l3c/o=yHxdYWKq.0+knXO7!vsJR3PjSC1qb5_m6Y-+cJ7@SuUvXPYzB+q89Kk1M-o';
    public $second = 'yUa2V9rYAO61qWav-H!RiAp._p/0..wNu#a4X#sgDZ(*YhVsRU?7NYumdU+zF_S*tlAsMRv/uP_kTCGr.1*!Awkg+E@D#d@.cw9p8Fi9S-s2f0';
    public $conthash = 'DbsR:i?3SHj.XEc';

    function __construct($dataArray) {
        $f = $this->fromArray($dataArray);
        $d = $this->fromKeys($dataArray);
        $end = $f.$d.$this->conthash;
        return hash('sha1', $end);
    }

    function fromArray($array){
        $d = "";
        foreach($array as $key => $value) {
            if($key != 'checksum'){
                if(is_numeric($value)){
                    $d .= $this->fromNumber($value);
                }
                else{
                    $d .= $this->fromString($value);
                }
            }
        }
        return $d;
    }

    function fromNumber($number){
        $amt = strlen($number);
        $number = round(intval($number), -1);
        $slt = "";
        if($amt == 1){
            $slt .= "p21dcvdw,/".strval($number);
            return $slt;
        }
        for($i =0; $i < $amt; $i++){
            $slt .= $this->salt[$i] . strval($number);
        }
        return $slt;
    }

    function fromString($str){
        $data = "";
        $chars = 0;
        $split = str_split($str, 5);
        foreach($split as $strs){
            $chars += 6;
            //echo $strs .= $this->GetChars($chars, 3)."   ";
            $data .= $strs.=$this->GetChars($chars, 2);
        }
        return $data;
    }

    function GetChars($start, $amount){
        $chars = "";
        for($i =$start; $i < $start + $amount; $i++){
            $chars .= $this->second[$i];
        }
        return $chars;
    }

    function fromKeys($arr){
        $d = "";
        $keyArray = array();
        foreach($arr as $key => $value) {
            if($key != 'checksum'){
                array_push($keyArray, $key);
            }
        }
        sort($keyArray);
        foreach($keyArray as $value) {
            $d .= $value;
        }
        return $d;
    }
}
?>