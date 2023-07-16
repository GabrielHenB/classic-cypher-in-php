<?php

//================== DEFAULT ====================
define("CAMINHO_TEXTO", __DIR__ . "/text.txt");
define("CAMINHO_CHAVE", __DIR__ . "/key.txt");
define("ALFABETO", __DIR__ . "/alphabet.txt");
define("METHOD", __DIR__ . "/method.txt");
define("DEFAULT_KEY", "umaChave");
define("DEFAULT_ALPHABET", 'abcdefghijklmnopqrstuvwxyz');
define("METHODS",['vigenere','vernam','vigenere_transposed','dual']);

$key = DEFAULT_KEY;
$plaintext = "textoteste";
$alphabet = DEFAULT_ALPHABET;

//========FILE STUFF=====================
if(!file_exists(CAMINHO_TEXTO) || !is_file(CAMINHO_TEXTO)){
    echo "Arquivo nao encontrado. Executando debug padrao. ";
    $resultado = vigenere_cypher($key,$plaintext,$alphabet,true);
    
    echo "========= VIGENERE CYPHER ===========\n";
    echo $plaintext . "\n with key " . $key . "\n into " . $resultado;
    echo "\n and decryption to " . vigenere_cypher($key,$resultado,$alphabet,false);
    $resultado2 = vernam_cypher($key,$plaintext,$alphabet);
    
    echo "\n\n The Vernam cypher equivalent generates: ". $plaintext . " with key " . $key . " into "
        . $resultado2 ;
        
        echo "\n\n the decryption generates: ". vernam_cypher($key,$resultado2,$alphabet);
        
        $transposed = a_certain_specific_transposition($plaintext,$alphabet,true,1);
        
        echo "\n\n\n\n".$transposed . "  ||  is   " . a_certain_specific_transposition($transposed,$alphabet,false,1);
        
        $dual = vernam_cypher($key,vigenere_cypher($key,$plaintext,$alphabet,true),$alphabet);
        
        echo "\nA crazy dual-crypt " . $dual . "\n which is " . vigenere_cypher($key,vernam_cypher($key,$dual,$alphabet),$alphabet,false); 
}else{
    //arquivo existe entao ele sera o texto
    $plaintext = file_get_contents(CAMINHO_TEXTO);
    var_dump($plaintext);
    if(file_exists(CAMINHO_CHAVE) && is_file(CAMINHO_CHAVE)){
        $key = file_get_contents(CAMINHO_CHAVE);
    }else{
        $key = DEFAULT_KEY;
    }
    var_dump($key);
    if(file_exists(ALFABETO) && is_file(ALFABETO)){
        $a = file_get_contents(ALFABETO);
    }else{
        $a = DEFAULT_ALPHABET;
    }
    var_dump($a);
    if(file_exists(METHOD) && is_file(METHOD)){
        $read = file_get_contents(METHOD);
        var_dump($read);
        $readM = strtok($read,"|");
        $readO = strtok("|");
        var_dump($readM, $readO);
    }else{
        $readM = METHODS[2];
    }
    
    if($readM == METHODS[0]){
        echo "\n You selected " . METHODS[0] . " which is \n";
        if($readO == '1'){
            $result = vigenere_cypher($key,$plaintext,$a,true);
            echo $result;
            echo "\nDEBUG DECRYPT: ".vigenere_cypher($key,$result,$a,false);
        }else{
            $result = vigenere_cypher($key,$plaintext,$a,false);
            echo $result;
        }
    }
    if($readM == METHODS[1]){
        echo "\n You selected " . METHODS[1] . " which is \n";
        if($readO == '1'){
            $result = vernam_cypher($key,$plaintext,$a);
            echo $result;
            echo "\nDEBUG DECRYPT: ".vernam_cypher($key,$result,$a);
        }else{
            $result = vernam_cypher($key,$plaintext,$a);
            echo $result;
        }
    }
    if($readM == METHODS[2]){
        echo "\n You selected " . METHODS[2] . " which is \n";
        if($readO == '1'){
            $result = a_certain_specific_transposition(vigenere_cypher($key,$plaintext,$a,true),$a,true);
            echo "$result";
            echo "\nDEBUG DECRYPT: ".vigenere_cypher($key,a_certain_specific_transposition($result,$a,false),$a,false);
        }else{
            $result = vigenere_cypher($key,a_certain_specific_transposition($plaintext,$a,false),$a,false);
            echo $result;
        }
    }
    if($readM == METHODS[3]){
        echo "\n You selected " . METHODS[3] . " which is \n";
        if($readO == '1'){
            $result = vernam_cypher($key,vigenere_cypher($key,$plaintext,$a,true),$a);
            echo "$result";
            echo "\nDEBUG DECRYPT: ". vigenere_cypher($key,vernam_cypher($key,$result,$a),$a,false);
        }else{
            $result = vigenere_cypher($key,vernam_cypher($key,$plaintext,$a),$a,false);
            echo $result;
        }
    }
    
    
    
}


//==========FUNCTIONS====================

function get_string_mapping($input, $alphabet){
	//working
	$letterNumberMap = [];
	$letters = str_split($alphabet); //to char array

	foreach ($letters as $index => $letter) {
		$number = $index;
		$letterNumberMap[$letter] = $number;
	}
	//string_debugger($letterNumberMap, $alphabet);
	$input = str_split($input);
	$res = [];
	foreach($input as $index=>$value){
		if(isset($letterNumberMap[$value])){
			$res[] = $letterNumberMap[$value];
		}else{
			//$res[] = $value;
			die("\n A entrada contem caracteres que nao estao no alfabeto! \n");
		}
	}
	//var_dump(["alfabeto" => $alphabet,"codificacao" => $letterNumberMap,"input" => $input,"resposta" => $res]);
	return $res; //only numbers
}

function invert_get_string_mapping($input, $alphabet){
	//Not optimal probably idk
	$letterNumberMap = [];
	$letters = str_split($alphabet); //to char array

	$alphabet = str_split($alphabet);
	$res = [];
	
	foreach($input as $thing){
		for($i = 0; $i < count($alphabet); $i++){
			if($alphabet[$i] == $thing)
				$res[] = $alphabet[$i];
		}
	}
	
	return implode('',$res);
}

function vigenere_cypher($k,$p,$a,$op = 1){
	$k = strtolower($k);
	$p = strtolower($p);
	$mapped_k = get_string_mapping($k, $a);
	$mapped_p = get_string_mapping($p, $a);
	$temp = 0;
	
	//If key is smaller than plaintext, repeat it
	while(count($mapped_k) < count($mapped_p)){
		$mapped_k[] = $mapped_k[$temp];
		$temp++;
	}
	
	//var_dump($k,$p,$mapped_k,$mapped_p);
		
		//encrypt
		if($op){
			for($i = 0; $i < count($mapped_p); $i++){
				$c[] = $a[(($mapped_p[$i]+$mapped_k[$i])%strlen($a))];
			}
			//return $c;
		}
		else{
			//decrypt
			for($i = 0; $i < count($mapped_p); $i++){
				$c[] = $a[(($mapped_p[$i]-$mapped_k[$i])%strlen($a))];
			}
			//return $c;
		}
	//This will implode it back to string
	return invert_get_string_mapping($c, $a);
}

function vernam_cypher($k, $p, $a){
    $k = strtolower($k);
    $p = strtolower($p);
    $mapped_k = get_string_mapping($k, $a);
    $mapped_p = get_string_mapping($p, $a);
    $temp = 0;
    
    //If key is smaller than plaintext, repeat it
    while(count($mapped_k) < count($mapped_p)){
        $mapped_k[] = $mapped_k[$temp];
        $temp++;
    }
    
    //var_dump($k,$p,$mapped_k,$mapped_p);
    
    //encrypt-decrypt
    
        for($i = 0; $i < count($mapped_p); $i++){
            $c[] = $a[(($mapped_p[$i] ^ $mapped_k[$i])%strlen($a))];
        }
        
    
    
    //This will implode it back to string
    return invert_get_string_mapping($c, $a);
}

function a_certain_specific_transposition($text,$a,bool $option = true,int $increment = 1){
    //Sometimes transposin can be fun (can it?)
    
    $text = strtolower($text);
    
    $mapped_p = get_string_mapping($text, $a);
    
    $c = [];
    //encrypt
    if($option){
        for($i = 0; $i < count($mapped_p); $i++){
            $c[] = $a[(($mapped_p[$i]+$increment)%strlen($a))];
        }
        //return $c;
    }
    else{
        //decrypt
        for($i = 0; $i < count($mapped_p); $i++){
            $c[] = $a[(($mapped_p[$i]-$increment)%strlen($a))];
        }
        //return $c;
    }
    //This will implode it back to string
    return invert_get_string_mapping($c, $a);
}