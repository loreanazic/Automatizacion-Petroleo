<?php
require_once dirname(__FILE__) . '/ModbusMaster.php';
// Create Modbus object
//session_start();
$ip = "192.168.250.3";

  //conectar($ip);

$modbus = new ModbusMaster($ip, "TCP");
$data_true = array(TRUE);
$data_false = array(FALSE);

//if(isset($_POST["led"]) && isset($_POST["value"])){
    $led = $_POST["led"];
    $value =$_POST["value"];
    $array = array(
      "led" => $led,
      "value" => $value,
      "value_0"=>0,
      "value_1"=>0,
      "value_2"=>0,
      "value_6"=>0,
      "value_7"=>0,
      "value_8"=>0
    );
    try {

      if($array["led"]==3 || $array["led"]==4){
        if($array["value"] == "false"){
          $modbus->writeSingleCoil(0, $array["led"], $data_false);
            $array2 = array(
            "led" => $led,
            "value" => "true",
            );
            echo json_encode($array2);
            exit();
        }
        else{
          if($array["value"] == "true"){
            $modbus->writeSingleCoil(0, $array["led"], $data_true);
            $array2 = array(
            "led" => $led,
            "value" => "false",
            );
            echo json_encode($array2);
            exit();
          }
        }
      }// FIN IF DIGITAL
      else{
        if($array["led"]==10 || $array["led"]==11){

          $modbus->writeSingleRegister(0,$led,array($value),'INT');

          $array = array(
            "led" => $led,
            "value" => $value,
            "value_0"=>0,
            "value_1"=>0,
            "value_2"=>0,
            "value_6"=>0,
            "value_7"=>0,
            "value_8"=>0
          );

          echo json_encode($array);
          exit();
        }else{
          //-----------------lectura digitales y analogicas

            $coils = $modbus->readCoils(0, 0, 3);
            $array["value_0"] = $coils[0]/*==1?"true":"false"*/;
            $array["value_1"] = $coils[1]/*==1?"true":"false"*/;
            $array["value_2"] = $coils[2]/*==1?"true":"false"*/;

            /*$recData = $modbus->readCoils(0, 1, 1);
            $array["value_1"] = $recData;

            $recData = $modbus->readCoils(0, 2, 1);
            $array["value_2"] = $recData;*/

            $recData=$modbus->readMultipleRegisters(0,6,3);
            $array["value_6"] = array($recData[0],$recData[1]);
            $array["value_7"] = array($recData[2],$recData[3]);
            $array["value_8"] = array($recData[4],$recData[5]);
            /*
            $valor=$modbus->readMultipleRegisters(0,7,1);
            $array["value_7"] = $recData;

            $valor=$modbus->readMultipleRegisters(0,8,1);
            $array["value_8"] = $recData;*/

            //retorna el valor que llega desde el nodemcu
            //echo "Estado ". $valor[0];
            echo json_encode($array);
            exit();
        }
      }
      //echo json_encode($array);
    }// fin try
    catch (Exception $e) {
        echo $e;

        conectar($ip);
        exit;
        //header('Location: index.php');
    }
    //$array = array("hola" => 2);

//}

function conectar($dirip){
  $_SESSION["modbus"] = new ModbusMaster($dirip, "TCP");
}
?>
