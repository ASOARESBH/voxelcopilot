<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Middlewares\AuthMiddleware;

class PacientesController extends Controller {

    public function index(): void {
        AuthMiddleware::handle();

        $busca = trim($_GET['busca'] ?? '');
        $pagina = max(1, (int)($_GET['pagina'] ?? 1));
        $porPagina = 20;

        // Dados simulados de pacientes (integração PACS virá depois)
        $pacientes = $this->getPacientesSimulados();

        if ($busca) {
            $pacientes = array_filter($pacientes, fn($p) =>
                stripos($p['nome'], $busca) !== false ||
                stripos($p['cpf'], $busca) !== false ||
                stripos($p['accession'], $busca) !== false
            );
        }

        $total = count($pacientes);
        $pacientes = array_slice(array_values($pacientes), ($pagina - 1) * $porPagina, $porPagina);
        $totalPaginas = (int) ceil($total / $porPagina);

        $this->view('pacientes/index', [
            'title'        => 'Pacientes — VOXEL Copilot',
            'pageTitle'    => 'Pacientes',
            'pageSubtitle' => 'Histórico clínico e exames por paciente',
            'pacientes'    => $pacientes,
            'busca'        => $busca,
            'pagina'       => $pagina,
            'totalPaginas' => $totalPaginas,
            'total'        => $total,
        ]);
    }

    public function show(int $id): void {
        AuthMiddleware::handle();

        $pacientes = $this->getPacientesSimulados();
        $paciente  = null;
        foreach ($pacientes as $p) {
            if ($p['id'] === $id) { $paciente = $p; break; }
        }

        if (!$paciente) {
            header('Location: /pacientes');
            exit;
        }

        // Timeline do paciente
        $timeline = [
            ['ano'=>2018,'modalidade'=>'RX','descricao'=>'Tórax','status'=>'laudado','accession'=>'ACC-2018-001'],
            ['ano'=>2019,'modalidade'=>'TC','descricao'=>'Abdome','status'=>'laudado','accession'=>'ACC-2019-003'],
            ['ano'=>2020,'modalidade'=>'RM','descricao'=>'Encéfalo','status'=>'laudado','accession'=>'ACC-2020-007'],
            ['ano'=>2022,'modalidade'=>'PET','descricao'=>'Corpo inteiro','status'=>'laudado','accession'=>'ACC-2022-012'],
            ['ano'=>2025,'modalidade'=>'TC','descricao'=>'Atual','status'=>'aguardando','accession'=>'ACC-2025-001'],
        ];

        $this->view('pacientes/show', [
            'title'        => 'Paciente — ' . $paciente['nome'],
            'pageTitle'    => $paciente['nome'],
            'pageSubtitle' => 'Histórico clínico completo',
            'paciente'     => $paciente,
            'timeline'     => $timeline,
        ]);
    }

    private function getPacientesSimulados(): array {
        return [
            ['id'=>1,'nome'=>'João Carlos Pereira','cpf'=>'123.456.789-00','idade'=>58,'sexo'=>'M','nascimento'=>'1967-03-15','telefone'=>'(31) 99999-1234','email'=>'joao@email.com','cidade'=>'Belo Horizonte','estado'=>'MG','total_exames'=>5,'ultimo_exame'=>'2025-07-05','accession'=>'ACC-2025-001'],
            ['id'=>2,'nome'=>'Maria Aparecida Santos','cpf'=>'234.567.890-11','idade'=>62,'sexo'=>'F','nascimento'=>'1963-08-22','telefone'=>'(31) 98888-5678','email'=>'maria@email.com','cidade'=>'Contagem','estado'=>'MG','total_exames'=>3,'ultimo_exame'=>'2025-07-05','accession'=>'ACC001'],
            ['id'=>3,'nome'=>'Carlos Eduardo Lima','cpf'=>'345.678.901-22','idade'=>71,'sexo'=>'M','nascimento'=>'1954-12-10','telefone'=>'(31) 97777-9012','email'=>'carlos@email.com','cidade'=>'Belo Horizonte','estado'=>'MG','total_exames'=>8,'ultimo_exame'=>'2025-07-05','accession'=>'ACC002'],
            ['id'=>4,'nome'=>'Roberto Ferreira Costa','cpf'=>'456.789.012-33','idade'=>58,'sexo'=>'M','nascimento'=>'1967-05-30','telefone'=>'(31) 96666-3456','email'=>'roberto@email.com','cidade'=>'Betim','estado'=>'MG','total_exames'=>4,'ultimo_exame'=>'2025-07-05','accession'=>'ACC003'],
            ['id'=>5,'nome'=>'Ana Paula Rodrigues','cpf'=>'567.890.123-44','idade'=>45,'sexo'=>'F','nascimento'=>'1980-11-18','telefone'=>'(31) 95555-7890','email'=>'ana@email.com','cidade'=>'Belo Horizonte','estado'=>'MG','total_exames'=>2,'ultimo_exame'=>'2025-07-04','accession'=>'ACC004'],
            ['id'=>6,'nome'=>'José Antonio Pereira','cpf'=>'678.901.234-55','idade'=>67,'sexo'=>'M','nascimento'=>'1958-04-07','telefone'=>'(31) 94444-2345','email'=>'jose@email.com','cidade'=>'Sabará','estado'=>'MG','total_exames'=>6,'ultimo_exame'=>'2025-07-05','accession'=>'ACC005'],
            ['id'=>7,'nome'=>'Fernanda Lima Souza','cpf'=>'789.012.345-66','idade'=>38,'sexo'=>'F','nascimento'=>'1987-09-25','telefone'=>'(31) 93333-6789','email'=>'fernanda@email.com','cidade'=>'Nova Lima','estado'=>'MG','total_exames'=>1,'ultimo_exame'=>'2025-07-05','accession'=>'ACC006'],
            ['id'=>8,'nome'=>'Marcos Vinícius Alves','cpf'=>'890.123.456-77','idade'=>55,'sexo'=>'M','nascimento'=>'1970-07-14','telefone'=>'(31) 92222-0123','email'=>'marcos@email.com','cidade'=>'Belo Horizonte','estado'=>'MG','total_exames'=>7,'ultimo_exame'=>'2025-07-03','accession'=>'ACC007'],
            ['id'=>9,'nome'=>'Luciana Mendes Castro','cpf'=>'901.234.567-88','idade'=>49,'sexo'=>'F','nascimento'=>'1976-02-28','telefone'=>'(31) 91111-4567','email'=>'luciana@email.com','cidade'=>'Vespasiano','estado'=>'MG','total_exames'=>3,'ultimo_exame'=>'2025-07-05','accession'=>'ACC008'],
            ['id'=>10,'nome'=>'Ricardo Santos Oliveira','cpf'=>'012.345.678-99','idade'=>72,'sexo'=>'M','nascimento'=>'1953-06-03','telefone'=>'(31) 90000-8901','email'=>'ricardo@email.com','cidade'=>'Belo Horizonte','estado'=>'MG','total_exames'=>9,'ultimo_exame'=>'2025-07-05','accession'=>'ACC009'],
        ];
    }
}
