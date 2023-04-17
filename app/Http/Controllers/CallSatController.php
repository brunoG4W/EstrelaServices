<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class CallSatController extends Controller
{
    public function __construct()
    {
        //
    }

    public function integra()
    {    
        $url = 'http://acesso.sistemarodar.com.br:8080/api?u=antoni.estrela&s=72133078&m=getcomm&format=json';
        $response = Http::get($url);  
        $posicoes = $response->json(); 

        dd($posicoes);
        
        foreach($posicoes as $posicao)
        {
            $placa = $this->validarPlaca($posicao);
            if( !$placa )
                continue;

                
            $dados = [
                'CdIDVeiculo' => $posicao['id'],
                'DtAtualizacao' => date('Y-m-d H:i'), 
                'QtLatitude' => number_format($posicao['posicao']['lat'],6, '.', ''), 
                'QtLongitude' => number_format($posicao['posicao']['lon'],6, '.', ''), 
                'DsComplemento' => '',
                'NrPlaca' => $placa,
                'InIgnicao' => null,
                'NrVelocidade' => null,
                'NrRpm' => null,
                'InAlarmeAcionado' => null,
                'InAlertaAcionado' => null,
                'InBauDestravado' => null,
                'InPortaCaronaAberta' => null,
                'InPortaMotAberta' => null,
                'InVeicBloqueado' => null,
                'InVelocMaxExcedida' => null,
                'DsPontoRef' => null,
                'NrDistPontoRef' => null,
                'DsOrigemImportacao' => 'CALLSAT',
            ]; 
           
            try {
                DB::table('SISPOS')->insert($dados);
            } catch (\Throwable $th) {
                dump ($th->getMessage());
            }

        }

    }


    public function validarPlaca($posicao) 
    {
        if( strlen($posicao['placa']) == 8  || strlen($posicao['placa']) == 7 )
            $placa = $posicao['placa'];
        else
            $placa = $posicao['modelo'];

        // se tiver vazio pula fora
        if (strlen($placa==0))
            return false;

        // troca espaço por hifen
        $placa_sem_espaco = str_replace(' ', '', $placa);

        // passa no regex
        $regex = '/[A-Z]{3}[0-9][0-9A-Z][0-9]{2}/';

        if( preg_match($regex, $placa_sem_espaco) === 0 )
            return false;           

        $placa = str_replace(' ', '-', $placa);
        return $placa;
    }


}

















    // if(is_numeric( substr($dadosAtivos[$i]['placa'], 4, 1))) {
    //     //adiciona o hifen        
    //     $split  =        str_split($dadosAtivos[$i]['placa'], 3);
    //     $placa_formatada = $split[0].'-'.$split[1].$split[2];
    //     $placa_f = $placa_formatada;
    //     // file_put_contents('./log.txt', $placa_formatada, FILE_APPEND);
    // }else{
    //     $placa_f = $dadosAtivos[$i]['placa'];
    // }




