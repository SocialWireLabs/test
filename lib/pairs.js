function pintaFondo(id,nRespuestas){
	var cadenas=document.getElementById("respuestas").value.split(",");
	var i=0,j=0;
	var bloque;
	var iDiv;
	var ta;
	var ultimoValor;
	var valorActual;
	var valorSalto=-1;
	var valorSaltoAnterior=-1;
	var text;
	var left_text=new Array(nRespuestas/2);
	var right_text=new Array(nRespuestas/2);
	var longitud=document.getElementById("respuestas").value.length;
	var respuestasOrdenadas;
	var primerValorPar=false;
	var responses_left=new Array(nRespuestas/2);
	var num_responses_left=0;
	var responses_right=new Array(nRespuestas/2);
	var num_responses_right=0;
	var responses_tem="";
	if(document.getElementById("respuestasOrdenadas").value==""){
		i=1;
		while(i<=nRespuestas){
			if(i==1){
				document.getElementById("respuestasOrdenadas").value=i;
				document.getElementById("respuestasOrdenadas").value+=","+(i+1);
			}
			else{
				document.getElementById("respuestasOrdenadas").value+=","+i;
				document.getElementById("respuestasOrdenadas").value+=","+(i+1);	
			}
			i+=2;
		}
	}
	respuestasOrdenadas=document.getElementById("respuestasOrdenadas").value.split(",");
	if(cadenas[cadenas.length-1]==id){
		document.getElementById("textarea"+id).style.backgroundColor="white";
		if(id%2==0)
			desbloqueaDiv(cadenas,"par",nRespuestas);
		else
			desbloqueaDiv(cadenas,"impar",nRespuestas);
		document.getElementById("respuestas").value=document.getElementById("respuestas").value.substring(0,longitud-2)
	}
	else{
		if(cadenas[0]==""){
			i=1;
			while(i<=nRespuestas){
				bloque=document.getElementById("textarea"+i);	
				if(bloque.style.backgroundColor!="white")
					bloque.style.backgroundColor="white";
				i++;
			}
			document.getElementById("respuestas").value+=id;
		}
		else{
			i=0;
			if(cadenas[0]%2==0){
				primerValorPar=true;
			}

			i=0;
			while(i<nRespuestas/2){
				if(primerValorPar){
					if(cadenas[0]==respuestasOrdenadas[i*2+1])
						posicionPrimerValorVectorRespuestas=i;
				}
				else{
					if(id==respuestasOrdenadas[i*2+1])
						posicionPrimerValorVectorRespuestas=i;	
				}
				i++;
			}

			i=0;
			while(i<nRespuestas/2){
				if(primerValorPar){
					if(id==respuestasOrdenadas[i*2])
						posicionIdVectorRespuestas=i;
				}
				else{
					if(cadenas[0]==respuestasOrdenadas[i*2])
						posicionIdVectorRespuestas=i;	
				}
				i++;
			}

			i=0;
			while(i<nRespuestas){
				if(respuestasOrdenadas[i]%2==0){
					if(primerValorPar){
						if(respuestasOrdenadas[i]){	
							responses_right[num_responses_right]=respuestasOrdenadas[i];
							num_responses_right++;
						}
					}
					else{
						if(respuestasOrdenadas[i]){
							responses_right[num_responses_right]=respuestasOrdenadas[i];
							num_responses_right++;
						}
					}
				}
				else{
					if(primerValorPar){
						if(respuestasOrdenadas[i]){
							responses_left[num_responses_left]=respuestasOrdenadas[i];
							num_responses_left++;
						}
					}
					else{
						if(respuestasOrdenadas[i]){
							responses_left[num_responses_left]=respuestasOrdenadas[i];
							num_responses_left++;
						}
					}
				}
				i++;
			}

			for(i=0;i<nRespuestas/2;i++){
				if(primerValorPar){
					for(j=0;j<num_responses_right;j++){
						if(j==posicionIdVectorRespuestas){
							responses_tem=responses_right[j];
							responses_right[j]=responses_right[posicionPrimerValorVectorRespuestas];
							responses_right[posicionPrimerValorVectorRespuestas]=responses_tem;
						}
					}
				}
				else{
					for(j=0;j<num_responses_right;j++){
						if(j==posicionPrimerValorVectorRespuestas){
							responses_tem=responses_right[j];
							responses_right[j]=responses_right[posicionIdVectorRespuestas];
							responses_right[posicionIdVectorRespuestas]=responses_tem;
						}
					}	
				}
			}
			
			i=0;
			while(i<responses_left.length){
				left_text[i]=document.getElementById("textarea"+responses_left[i]).innerHTML;
				right_text[i]=document.getElementById("textarea"+responses_right[i]).innerHTML;
				i++;
			}

			i=0;
			while(i<responses_left.length){
				if(i==0){
					document.getElementById("respuestasOrdenadas").value=responses_left[i];
					document.getElementById("respuestasOrdenadas").value+=","+responses_right[i];
				}
				else{
					document.getElementById("respuestasOrdenadas").value+=","+responses_left[i];
					document.getElementById("respuestasOrdenadas").value+=","+responses_right[i];	
				}
				i++;
			}
			i=1;
			while(i<=nRespuestas){
				bloque=document.getElementById("div"+i);	
				bloque.parentNode.removeChild(bloque);
				i++;
			}
			i=0;
			while(i<responses_left.length){
				iDiv = document.createElement('div');
				iDiv.id = 'div'+responses_left[i];
				ta = document.createElement("textarea");
				ta.id="textarea"+responses_left[i];
				ta.disabled="true";
				text= document.createTextNode(left_text[i]);
				ta.appendChild(text);
				iDiv.appendChild(ta);
				document.getElementById('contenedor1').appendChild(iDiv);
				document.getElementById("div"+responses_left[i]).setAttribute("onclick","javascript:pintaFondo("+responses_left[i]+","+nRespuestas+");");
				i++;
			}
			i=0;
			while(i<responses_right.length){
				iDiv = document.createElement('div');
				iDiv.id = 'div'+responses_right[i];
				ta = document.createElement("textarea");
				ta.id="textarea"+responses_right[i];
				ta.disabled="true";
				text= document.createTextNode(right_text[i]);
				ta.appendChild(text);
				iDiv.appendChild(ta);
				document.getElementById('contenedor2').appendChild(iDiv);
				document.getElementById("div"+responses_right[i]).setAttribute("onclick","javascript:pintaFondo("+responses_right[i]+","+nRespuestas+");");
				i++;		
			}
			document.getElementById("respuestas").value+=","+id;
		}

		cadenas=document.getElementById("respuestas").value.split(",");
		i=0;
		while(i<cadenas.length){
			document.getElementById("textarea"+cadenas[i]).style.backgroundColor="#60B8F7";
			i++;
		}
		if(cadenas[cadenas.length-1]==id&&id%2==0&&cadenas.length%2!=0)
				bloqueaDiv(cadenas,"par",nRespuestas);
		else if(cadenas[cadenas.length-1]==id&&id%2!=0&&cadenas.length%2!=0)
				bloqueaDiv(cadenas,"impar",nRespuestas);
		if(cadenas.length%2==0&&cadenas.length!=1)
			if(id%2==0)
				desbloqueaDiv(cadenas,"impar",nRespuestas);
			else
				desbloqueaDiv(cadenas,"par",nRespuestas);	
		if(cadenas.length==2){
			document.getElementById("respuestas").value="";
		}
	}	
}

function bloqueaDiv(cadenas,paridad,nRespuestas){
	var i=1;
	while(i<=nRespuestas){
		if(cadenas[cadenas.length-1]!=i){
			if(paridad=="par")
				if(i%2==0)
					document.getElementById("div"+i).onclick=null;
			if(paridad=="impar")
				if(i%2!=0)
					document.getElementById("div"+i).onclick=null;
		}
		i++;
	}
}

function desbloqueaDiv(cadenas,paridad,nRespuestas){
	var i=1;
	while(i<=nRespuestas){
		if(cadenas[cadenas.length-1]!=i){
			if(paridad=="par")
				if(i%2==0)
					document.getElementById("div"+i).setAttribute("onclick","javascript:pintaFondo("+i+","+nRespuestas+");");				
			if(paridad=="impar")
				if(i%2!=0)
					document.getElementById("div"+i).setAttribute("onclick","javascript:pintaFondo("+i+","+nRespuestas+");");
		}
		i++;
	}
}


