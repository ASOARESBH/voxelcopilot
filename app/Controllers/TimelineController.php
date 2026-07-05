<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Middlewares\AuthMiddleware;

class TimelineController extends Controller {

    public function index(): void {
        AuthMiddleware::handle();

        $busca = trim($_GET['busca'] ?? '');

        $pacientes = [
            ['id'=>1,'nome'=>'João Carlos Pereira','cpf'=>'123.456.789-00','idade'=>58,'sexo'=>'M','total_exames'=>5,'ultimo_exame'=>'2025-07-05'],
            ['id'=>3,'nome'=>'Carlos Eduardo Lima','cpf'=>'345.678.901-22','idade'=>71,'sexo'=>'M','total_exames'=>8,'ultimo_exame'=>'2025-07-05'],
            ['id'=>8,'nome'=>'Marcos Vinícius Alves','cpf'=>'890.123.456-77','idade'=>55,'sexo'=>'M','total_exames'=>7,'ultimo_exame'=>'2025-07-03'],
            ['id'=>10,'nome'=>'Ricardo Santos Oliveira','cpf'=>'012.345.678-99','idade'=>72,'sexo'=>'M','total_exames'=>9,'ultimo_exame'=>'2025-07-05'],
        ];

        if ($busca) {
            $pacientes = array_filter($pacientes, fn($p) => stripos($p['nome'], $busca) !== false || stripos($p['cpf'], $busca) !== false);
        }

        $this->view('timeline/index', [
            'title'        => 'Timeline Clínica — VOXEL Copilot',
            'pageTitle'    => 'Timeline Clínica',
            'pageSubtitle' => 'Histórico longitudinal de exames por paciente',
            'pacientes'    => array_values($pacientes),
            'busca'        => $busca,
        ]);
    }

    public function show(int $id): void {
        AuthMiddleware::handle();

        $timeline = [
            ['ano'=>2018,'mes'=>'Mar','modalidade'=>'RX','descricao'=>'Tórax PA e perfil','instituicao'=>'Hospital das Clínicas','status'=>'laudado','accession'=>'ACC-2018-001','laudo_resumo'=>'Sem alterações pleuropulmonares significativas.'],
            ['ano'=>2019,'mes'=>'Jul','modalidade'=>'TC','descricao'=>'Abdome e pelve com contraste','instituicao'=>'Hospital das Clínicas','status'=>'laudado','accession'=>'ACC-2019-003','laudo_resumo'=>'Fígado com esteatose grau I. Demais estruturas sem alterações.'],
            ['ano'=>2020,'mes'=>'Nov','modalidade'=>'RM','descricao'=>'Encéfalo sem contraste','instituicao'=>'Clínica Imagem','status'=>'laudado','accession'=>'ACC-2020-007','laudo_resumo'=>'Exame dentro dos limites da normalidade para a faixa etária.'],
            ['ano'=>2022,'mes'=>'Fev','modalidade'=>'PET','descricao'=>'PET-CT Corpo inteiro','instituicao'=>'Instituto Oncológico','status'=>'laudado','accession'=>'ACC-2022-012','laudo_resumo'=>'Sem evidência de atividade metabólica neoplásica.'],
            ['ano'=>2025,'mes'=>'Jul','modalidade'=>'TC','descricao'=>'TC Tórax com contraste','instituicao'=>'VOXEL PACS','status'=>'aguardando','accession'=>'ACC-2025-001','laudo_resumo'=>null],
        ];

        $paciente = ['id'=>$id,'nome'=>'João Carlos Pereira','cpf'=>'123.456.789-00','idade'=>58,'sexo'=>'M','nascimento'=>'1967-03-15'];

        $this->view('timeline/show', [
            'title'        => 'Timeline — João Carlos Pereira',
            'pageTitle'    => 'Timeline Clínica',
            'pageSubtitle' => 'João Carlos Pereira · M · 58 anos · CPF 123.456.789-00',
            'paciente'     => $paciente,
            'timeline'     => $timeline,
        ]);
    }
}
