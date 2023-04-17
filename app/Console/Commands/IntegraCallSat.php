<?php
 
namespace App\Console\Commands;
 
use Illuminate\Console\Command;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
 
class IntegraCallSat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'integra:callsat';
 
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'integra -> callsat';
 
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('iniciar integração callsat');

        $url = 'http://acesso.sistemarodar.com.br:8080/api?u=antoni.estrela&s=72133078&m=getcomm&format=json';
        $response = Http::retry(3, 1000)->get($url);  
        $posicoes = $response->json(); 
        
        $this->info('api acessada com sucesso');

        foreach($posicoes as $posicao)
        {
            $this->info('placa: '.$posicao['placa']);

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
                $this->warn( $th->getMessage() );
            }

            $this->info('integrado placa '.$placa);

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