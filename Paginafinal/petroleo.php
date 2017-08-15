<?php
	//session_start();
?>
<script language="javascript" src="jquery-1.11.3.min.js"></script>
<script language="javascript" >
 //-------------------- VARIABLES GLOBALES --------------
	var barra_temperatura=0;
	var estado1="true";
    var estado2="true";
	var barra_temp=0;
	var new_height=0;
	var new_top=72;
    var height_gas=0;
    var top_gas=0;
    var timer_crudo;
    var poten=500; 

    // ---------- FUNCION RECIBIR Y ENVIAR POR AJAX ------------------
		function Prender(valor,status,valor_0,valor_1,valor_2,valor_6,valor_7,valor_8){
				/*
				Valores que recibe y envia por ajax a conexion1.php en este caso "LED" es la direccion a la que se le enviaría al NodeMCU y "VALUE" es el valor que debe tomar esa direccion
				value_0, value_1, value_2, value_6, value_7, value_8 reflejan los valores que llegan desde el NodeMCU para hacer cambios visuales en la página WEB
				*/
		        dataObj = { 
		          led: valor,
		          value: status,
		          value_0: 0,
				  value_1: 0,
				  value_2: 0,
				  value_6: 0,
				  value_7: 0,
				  value_8: 0
		        };

		        $.ajax({
		            type: "POST", // Forma de envio
		            url: "conexion1.php", // Página que recibe los datos
		            data: dataObj,

		         success: function(data) {
					var pushedData=jQuery.parseJSON(data); /* Convierte el array (data) recibida desde conexion1.php para ser usada en esta página y hacer los cambios visuales segun sus entradas analógicas o digitales*/
										//ENTRADAS DIGITALES Y ANALOGICAS

					//--TORRE DE PETROLEO QUE USA INYECCION DE GAS--ENTRADA ANALÓGICA POR LA DIRECCION 6
                	if(valor_6==6){
            			var poten=(pushedData.value_6[0]*256)+(pushedData.value_6[1]);
					    new_height=poten/20.48;
					    new_top=72-new_height;/*poten*0.021484375;*/
					    var cam=document.getElementById("crudo");
					    var gas_l=document.getElementById("gas");
					    if(new_height<=50){
					        cam.setAttribute("style","position: absolute; width:50%; height:"+new_height+"%; top:"+new_top+"%; left:25%; background-color:#000000;");
					    }
					    if(new_height>=0 && new_height<=15){
					    	gas_l.setAttribute("style","position: relative; width:75%; height:100%; top:0%; left:20%; background-color:#0e5855");
					    }
					    else{
					    	if(new_height>15 && new_height<=30){
					    		gas_l.setAttribute("style","position: relative; width:75%; height:100%; top:0%; left:20%; background-color:#38e0da");
					    	}
					    	else{
					    		if(new_height>30 && new_height<=50){
					    			gas_l.setAttribute("style","position: relative; width:75%; height:100%; top:0%; left:20%; background-color:#a7f1ef");
					    		}
					    	}
					    }
                	}

					//--PLANTA DE LLENADO DE BARRILES--ENTRADA ANALÓGICA POR LA DIRECCION 7
	                if(valor_7==7){
		                var tanque=(pushedData.value_7[0]*256)+(pushedData.value_7[1]);
		                var cam=document.getElementById("barra");
		                var calculo=(tanque*100)/1024;
	                    var a=100-calculo;
	   					cam.setAttribute("style","height:"+calculo+"%; width: 100%; background: #000000; position: absolute; top:"+a+"%;");
                	}
                	
                	//--VERIFICACION DE TEMPERATURA DE LA INDUSTRIA --ENTRADA ANALOGICA POR LA DIRECCION 8
                	if(valor_8==8){
                		var tanque=(pushedData.value_8[0]*256)+(pushedData.value_8[1]);
						var calculo=100-((tanque*100)/1024);
						var grado= ((calculo*180)/100)*-1;
						var tem=document.getElementById("linea_pon");
						tem.setAttribute("transform","translate(255,250) rotate("+grado+")");
						if(tanque>1023*0.80){
							var stop1=document.getElementById("boton_stop");
							var tem_b1=document.getElementById("tb1");
							var tem_b2=document.getElementById("tb2");
							tem_b1.disabled=true;
							tem_b2.disabled=true;
							stop1.setAttribute("style","background:#DF0101");
						}
                	}
                	//--LUCES DE CONTROL--ENTRADA DIGITAL DEL PANEL DE CONTROL POR LA DIRECCION 1
                	if(valor_1==1){
                		var bb=document.getElementById("b1");
                		if(pushedData.value_1==true){
                			bb.setAttribute("style","background:#5cb85c;");
                		}else{
                			if(pushedData.value_1==false){
                				bb.setAttribute("style","background:#848484;");
                			}
                		}

                	}//--LUCES DE CONTROL--ENTRADA DIGITAL DEL PANEL DE CONTROL POR LA DIRECCION 2
                	if(valor_2==2){
                		var bbb=document.getElementById("b2");
                		if(pushedData.value_2==true){
                			bbb.setAttribute("style","background:#5cb85c;");
                		}else{
                			if(pushedData.value_2==false){
                				bbb.setAttribute("style","background:#848484;");
                			}
                		}
                	}
                	//--EXTRAE O NO EXTRAE PETROLEO -- ENTRADA DIGITAL DEL BALANCIN POR LA DIRECCION 0
			         if(valor_0==0){
			         	var cam=document.getElementById("crudo_torre2");
			         	if(pushedData.value_0==true){
                			height_cru2=47;

                		}else{
                			if(pushedData.value_0==false){
                				height_cru2=0;
                			}
                		}
						cam.setAttribute("style","position: absolute;width: 50%; height:"+height_cru2+"%;top:14%;left:25%;background-color:#000000");
	                }


		        },
		            catch: function(err){
		              console.log(err);
		            }
		        });

											//SALIDAS DIGITALES Y ANALOGICOS

				// BARRA DE REFRIGERACION -- SALIDA ANALOGICA DIRECCION 10
				if(valor==10){
					if(status>=0 && status<=100){
						var cam=document.getElementById("barra2");
						barra_temp=status;
						var a=100-barra_temp;
		            	cam.setAttribute("style","height:"+barra_temp+"%; width: 100%; background: #58ACFA; position: absolute; top:"+a+"%;");
					}
		        }
		        // BARRA DE TEMPERATURA DE PROCESO DE PETROLEO -- SALIDA ANALOGICA DIRECCION 11
		        if(valor==11 && (status>=0 && status<=100)){
					var cam=document.getElementById("barra3");
					barra_temperatura=status;
					var a=100-barra_temperatura;
		            cam.setAttribute("style","height:100%; width: "+barra_temperatura+"%; background: #FE9A2E; position: absolute; left:0%;");
		        }
		       // ESTADO DE BOTONES P/T -- SALIDA DIGITAL DIRECCION 3 Y 4
		        if(valor==3){
		            if(estado1=="false"){
		                estado1="true";

		            }else{estado1="false";}
		        }
	            if(valor==4){
	                if(estado2=="false"){
	                    estado2="true";
	                }
                	else{
                		estado2="false";
	            	}
	        	}
       }setInterval(Prender,2000,"-1","enviar","0","1","3","6","7","8");
//-------------------------------------------------------------------------------------------------------------------------
</script>
<html>
    <head >
        <title> MODBUS </title>
    <link rel="stylesheet" href="style.css">
    </head>
    <body bgcolor="#fff9e8">
    <!--SE CREA LA DIVISION PARA EL TANQUE QUE SERÁ UNA ENTRADA ANÁLOGICA-->
    	<div class="cajon">
		        <div class="cajon2"><img src="industria.png" width= "100%";/></div>
		        <div class="cajon3"><img src="tubo1.png" width= "100%";/></div>
		        <div class="cajon4"><img src="tubo.png" width= "100%";/></div>

		        <div id="contenedor" style="width: 12%; height: 25%; background: #D0D3D4; position: absolute; top:  70%;left: 72.5%;">
				  <div id="barra"  style="height:0%; width: 100%; background: #000000; position: relative; top: 100%;"></div>
				</div>

		</div>
		<div class="grama"></div>
		<div>

			<div class="perforacion">
	            <div class="tubo_crudo">
	            	<img id="tubo"; src="tubo3.png";/>
	            	<div id="crudo";></div>
	        	</div>
	            <div class="gas_lift">
	                <img id="tubo_gas"; src="tubo3.png"/>
	                <div class="ondas_gas">
	                    <div id="gas"></div>
	                </div>
	            </div>
	        </div>

	        <div class="torre_p">
	            <img id="pet_torre"; src="torre-de-petroleo.png";/>
	        </div>

		</div> <!--PERFORACION-->

		<div class="torre2">

			<!-- Generator: Adobe Illustrator 19.1.0, SVG Export Plug-In . SVG Version: 6.00 Build 0)  -->
			<svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
				 viewBox="0 0 217.22 217.22" style="enable-background:new 0 0 217.22 217.22;" xml:space="preserve">
			<g>
				<g id="group-112svg">
					<path id="path-3_15_" style="fill:#142A3B;" d="M173.002,193.415H112.03L127.721,55.33l28.48,14.162L173.002,193.415z
						 M120.141,185.416h44.573L149.539,73.814l-15.786-7.91L120.141,185.416z"/>
					<path id="path-4_15_" style="fill:#142A3B;" d="M166.575,191.081c-0.747,0-1.496-0.23-2.143-0.706l-47.091-34.664l33.885-42.483
						l-23.702-21.087c-1.493-1.33-1.627-3.617-0.298-5.111c1.33-1.497,3.624-1.625,5.113-0.298l28.83,25.647l-33.476,41.963
						l41.031,30.202c1.612,1.184,1.955,3.451,0.771,5.062C168.784,190.569,167.688,191.081,166.575,191.081z"/>
					<path id="path-5_15_" style="fill:#FF3A2F;" d="M180.872,86.824l-89.865-41.44l3.61-8.412l89.864,41.441L180.872,86.824z"/>
					<path id="path-6_15_" style="fill:#142A3B;" d="M77.79,196.692c-2.002,0-3.5-1.621-3.5-3.62V77.222c0-2,1.498-3.621,3.5-3.621
						c2,0,3.5,1.621,3.5,3.621v115.85C81.29,195.071,79.79,196.692,77.79,196.692z"/>
					<path id="path-7_15_" style="fill:#142A3B;" d="M80.333,82.659c-8.598-1.245-16.881-5.177-23.496-11.796
						c-16.293-16.303-16.293-42.739,0-59.044C63.495,5.156,71.845,1.217,80.504,0l18.667,41.33L80.333,82.659z"/>
					<path id="path-8_11_" style="opacity:0.35;enable-background:new    ;" d="M96.237,35.46L77.399,76.79
						c-8.598-1.245-16.882-5.177-23.496-11.797c-2.509-2.509-4.584-5.28-6.32-8.197c2.036,5.121,5.113,9.923,9.254,14.067
						c6.615,6.619,14.898,10.551,23.496,11.796L99.171,41.33L96.237,35.46z"/>
					<path id="path-9_10_" style="fill:#142A3B;" d="M215.29,196.415h-145v-10.999h145V196.415z"/>
				</g>
			</g>
			</svg>
			<div class="perforacion2">
				<img id="tubo_torre2"src="tubo3.png">
				<div id="crudo_torre2"></div>
			</div>
		</div><!--TORRE2-->

		<div class="control">
			<div class="botones"><!-- botonesss-->

				<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
				<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" width="100%" height="100%" viewBox="0 0 462.167 462.167" style="enable-background:new 0 0 462.167 462.167;" xml:space="preserve">
				<g>
				<path d="M0,381.249c0,44.688,36.231,80.918,80.914,80.918h300.335c44.687,0,80.918-36.23,80.918-80.918V80.914   C462.167,36.231,425.936,0,381.249,0H80.914C36.231,0,0,36.231,0,80.914V381.249z M23.55,80.914   c0-31.63,25.734-57.364,57.364-57.364h300.335c31.63,0,57.366,25.734,57.366,57.364v300.334c0,31.631-25.736,57.366-57.366,57.366   H80.914c-31.63,0-57.364-25.735-57.364-57.366V80.914z" fill="#fafce8"/>

				<path d="M93.986,318.444h-4.412v-11.16c0-3.25-2.637-5.887-5.889-5.887c-3.25,0-5.887,2.637-5.887,5.887v11.16h-4.412   c-9.514,0-17.256,7.743-17.256,17.265c0,9.51,7.742,17.244,17.256,17.244h4.412v68.054c0,3.25,2.637,5.887,5.887,5.887   c3.252,0,5.889-2.637,5.889-5.887v-68.054h4.412c9.514,0,17.252-7.734,17.252-17.244C111.238,326.187,103.5,318.444,93.986,318.444   z M93.986,341.174h-4.412H77.798h-4.412c-3.023,0-5.482-2.444-5.482-5.466c0-3.025,2.458-5.486,5.482-5.486h4.412h11.776h4.412   c3.02,0,5.476,2.461,5.476,5.486C99.462,338.73,96.998,341.174,93.986,341.174z" fill="#fafce8"/>
				<path d="M153.162,307.284v66.567h-4.412c-9.518,0-17.256,7.734-17.256,17.256c0,9.514,7.738,17.248,17.256,17.248h4.412v12.644   c0,3.25,2.637,5.887,5.889,5.887c3.246,0,5.887-2.637,5.887-5.887v-12.644h4.412c9.514,0,17.254-7.734,17.254-17.248   c0-9.521-7.74-17.256-17.254-17.256h-4.412v-66.567c0-3.25-2.641-5.887-5.887-5.887   C155.799,301.397,153.162,304.034,153.162,307.284z M174.828,391.107c0,3.029-2.458,5.475-5.478,5.475h-4.412h-11.776h-4.412   c-3.023,0-5.48-2.445-5.48-5.475c0-3.021,2.457-5.474,5.48-5.474h4.412h11.776h4.412   C172.37,385.633,174.828,388.085,174.828,391.107z" fill="#fafce8"/>
				<path d="M241.129,301.397H230.83h-10.299c-9.513,0-17.254,7.742-17.254,17.264c0,9.506,7.741,17.24,17.254,17.24h4.413v85.105   c0,3.25,2.641,5.887,5.887,5.887c3.25,0,5.891-2.637,5.891-5.887v-85.098h4.408c9.514,0,17.26-7.727,17.26-17.248   C258.389,309.147,250.643,301.397,241.129,301.397z M241.129,324.135h-4.408h-11.777h-4.413c-3.02,0-5.478-2.453-5.478-5.475   s2.458-5.474,5.478-5.474h4.413h11.777h4.408c3.029,0,5.482,2.452,5.482,5.474S244.158,324.135,241.129,324.135z" fill="#fafce8"/>
				<path d="M275.941,351.41c0,9.51,7.746,17.243,17.252,17.243h4.416v52.354c0,3.25,2.638,5.887,5.888,5.887s5.887-2.637,5.887-5.887   v-52.354h4.416c9.506,0,17.248-7.733,17.248-17.243c0-9.521-7.742-17.265-17.248-17.265h-4.416v-26.861   c0-3.25-2.637-5.887-5.887-5.887s-5.888,2.637-5.888,5.887v26.861h-4.416C283.688,334.145,275.941,341.888,275.941,351.41z    M293.193,345.923h4.416h11.774h4.416c3.021,0,5.474,2.46,5.474,5.486c0,3.021-2.452,5.466-5.474,5.466h-4.416h-11.774h-4.416   c-3.021,0-5.474-2.444-5.474-5.466C287.72,348.383,290.172,345.923,293.193,345.923z" fill="#fafce8"/>
				<path d="M389.159,373.859h-4.416v-66.56c0-3.25-2.637-5.887-5.887-5.887s-5.887,2.637-5.887,5.887v66.56h-4.408   c-9.514,0-17.256,7.742-17.256,17.248c0,9.521,7.742,17.248,17.256,17.248h4.408v12.651c0,3.25,2.637,5.887,5.887,5.887   s5.887-2.637,5.887-5.887v-12.651h4.416c9.514,0,17.252-7.727,17.252-17.248C406.411,381.601,398.673,373.859,389.159,373.859z    M389.159,396.582h-4.416H372.97h-4.408c-3.03,0-5.482-2.445-5.482-5.475c0-3.021,2.452-5.474,5.482-5.474h4.408h11.773h4.416   c3.021,0,5.475,2.452,5.475,5.474C394.634,394.136,392.181,396.582,389.159,396.582z" fill="#fafce8"/>
				</g>
				</svg>
				<div style="width: 30%; height: 30%; position: absolute; top: 17%; left: 16%;">
					<div id="b1" class="circulo"  style="background:#848484;"> <!--#5cb85c -->
					</div>
				</div>

				<div style="width: 30%; height: 30%; position: absolute; top: 17%; left: 55%;">
					<div  id="b2" class="circulo1"  style="background:#848484 ;">
					</div>
				</div>
			</div><!--FIN DE DE SVG botonesss-->
			<div class="potencimetro">

				<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
					 viewBox="0 0 508 508" style="enable-background:new 0 0 508 508;" xml:space="preserve" width="100%" height="100%">
				<circle style="fill:#FD8469;" cx="254" cy="254" r="254"/>
				<path style="fill:#324A5E;" d="M254,446.8c-106.4,0-192.8-86.4-192.8-192.8S147.6,61.2,254,61.2S446.8,147.6,446.8,254
					S360.4,446.8,254,446.8z"/>
				<path style="fill:#E6E9EE;" d="M406.8,262.8c0-2.8,0.4-6,0.4-8.8c0-84.4-68.4-153.2-153.2-153.2c-84.4,0-153.2,68.4-153.2,153.2
					c0,2.8,0,6,0.4,8.8H406.8z"/><!-- medio circulo-->
				<g ID="linea_pon" transform="translate(255,250) rotate(180)"><!-- mover barra con rotate-->
					<line id="segundos" x2="100" stroke="navy" stroke-width="10" stroke-linecap="round">
					</line>
				</g >
				<circle style="fill:#FFD05B;" cx="254" cy="262.8" r="27.6"/>
				<g>
					<path style="fill:#ACB3BA;" d="M254,136L254,136c-2.8,0-5.2-2.4-5.2-5.2V120c0-2.8,2.4-5.2,5.2-5.2l0,0c2.8,0,5.2,2.4,5.2,5.2v10.8
						C259.2,133.6,256.8,136,254,136z"/>
					<path style="fill:#ACB3BA;" d="M337.6,170.4L337.6,170.4c-2-2-2-5.2,0-7.2l7.6-7.6c2-2,5.2-2,7.2,0l0,0c2,2,2,5.2,0,7.2l-7.6,7.6
						C342.4,172.4,339.2,172.4,337.6,170.4z"/>
					<path style="fill:#ACB3BA;" d="M156,156L156,156c2-2,5.2-2,7.2,0l7.6,7.6c2,2,2,5.2,0,7.2l0,0c-2,2-5.2,2-7.2,0l-7.6-7.6
						C154,161.2,154,158,156,156z"/>
					<path style="fill:#ACB3BA;" d="M208.8,144.8L208.8,144.8c-2.4,1.2-5.6,0-6.4-2.8l-4-10c-1.2-2.4,0-5.6,2.8-6.4l0,0
						c2.4-1.2,5.6,0,6.4,2.8l4,10C212.8,140.8,211.6,144,208.8,144.8z"/>
					<path style="fill:#ACB3BA;" d="M363.2,208.8L363.2,208.8c-1.2-2.4,0-5.6,2.8-6.4l10-4c2.4-1.2,5.6,0,6.4,2.8l0,0
						c1.2,2.4,0,5.6-2.8,6.4l-10,4C367.2,212.8,364,211.6,363.2,208.8z"/>
					<path style="fill:#ACB3BA;" d="M299.2,144.8L299.2,144.8c-2.4-1.2-3.6-4-2.8-6.4l4-10c1.2-2.4,4-3.6,6.4-2.8l0,0
						c2.4,1.2,3.6,4,2.8,6.4l-4,10C304.8,144.8,301.6,146,299.2,144.8z"/>
					<path style="fill:#ACB3BA;" d="M125.6,200.8L125.6,200.8c1.2-2.4,4-3.6,6.4-2.8l10,4c2.4,1.2,3.6,4,2.8,6.4l0,0
						c-1.2,2.4-4,3.6-6.4,2.8l-10-4C126,206.4,124.8,203.6,125.6,200.8z"/>
				</g>
				<g>
					<circle style="fill:#2B3B4E;" cx="254" cy="82.4" r="10"/>
					<circle style="fill:#2B3B4E;" cx="254" cy="425.6" r="10"/>
					<circle style="fill:#2B3B4E;" cx="425.6" cy="254" r="10"/>
					<circle style="fill:#2B3B4E;" cx="82.4" cy="254" r="10"/>
				</g>
				</svg>
			</div><!--FIN DE DE SVG potenciometro-->
			<div class="cont_stop">
				<button id="boton_stop" type="button" class="stop" style="background:#F74141">STOP</button>
			</div><!--FIN boton stop-->
			<div id="contenedor2" style="width: 8%; height: 58%; background: #D0D3D4; position: absolute; top:  17%;left: 90%;">
				<div id="barra2"  style="height:0%; width: 100%; background: #58ACFA; position: relative; top: 100%;"></div>
			</div>
			<!--BOTONES BARRA DE REFRIGERACION -->
			<div class="boton_mas">
				<input type="button" id="tb1" type="button" class="mas" value="+" onclick="Prender(10,barra_temp+20,-1,-1,-1,-1,-1,-1);" disabled >
			</div>
			<div class="boton_menos">
				<input type="button" id="tb2" type="button" class="menos" value="-" onclick="Prender(10,barra_temp-20,-1,-1,-1,-1,-1,-1);" disabled>
			</div>
		</div>

			<!-- ****************************************** BOTONES P/T  ******************************************-->
		<div class="cajonBTN">
    		<button type="button" class="btnP" onClick="Prender(3,estado1,-1,-1,-1,-1,-1,-1);">P</button>
    		<button type="button" class="btnT" onClick="Prender(4,estado2,-1,-1,-1,-1,-1,-1);">T</button>
    	</div>

    	<!--****************************************BARRA DE CONTROL DE TEMPEATURA************************************-->
    	<div class="control2">
			<div class="boton_mas2">
				<button type="button" class="mas2" onclick="Prender(11,barra_temperatura+20,-1,-1,-1,-1,-1,-1);" enabled>+</button>
			</div>
			<div class="boton_menos2">
				<button type="button" class="menos2" onclick="Prender(11,barra_temperatura-20,-1,-1,-1,-1,-1,-1);">-</button>
			</div>
			<div id="contenedor3" style="width: 100%; height: 50%; background: #D0D3D4; position: absolute; top:  50%;left: 0%;">
				<div id="barra3"  style="height:100%; width: 0%; background: #FE9A2E; position:  relative; left: 0%;"></div>
			</div>
		</div> <!--******fin de barra de control******-->

    </body>
</html>