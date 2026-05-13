<?php

namespace Tests\Feature;

use App\Enums\DashboardImportStatus;
use App\Enums\UserRole;
use App\Livewire\Admin\Sectors\Create as SectorCreate;
use App\Livewire\Auth\Register;
use App\Livewire\Dashboards\Create as DashboardCreate;
use App\Livewire\Dashboards\ImportWizard;
use App\Models\Dashboard;
use App\Models\DashboardImport;
use App\Models\Sector;
use App\Models\User;
use App\Services\SpreadsheetReaderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /**
     * A basic test example.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/dashboards');
        $this->get('/dashboard')->assertRedirect('/dashboards');
        $this->get('/dashboards')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_see_dashboard(): void
    {
        $sector = Sector::factory()->create();
        $user = User::factory()->create(['sector_id' => $sector->id]);

        $this->actingAs($user)
            ->get('/dashboards')
            ->assertOk()
            ->assertSee('Dashboards do setor');
    }

    public function test_admin_middleware_blocks_setor_users(): void
    {
        $user = User::factory()->create(['role' => UserRole::Setor]);

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admin_can_create_sector_with_registration_code(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);

        Livewire::test(SectorCreate::class)
            ->set('name', 'SUENG')
            ->set('description', 'Setor de obras e engenharia.')
            ->call('save')
            ->assertRedirect(route('admin.sectors.index'));

        $sector = Sector::query()->where('name', 'SUENG')->first();

        $this->assertNotNull($sector);
        $this->assertNotEmpty($sector->registration_code);
        $this->assertTrue($sector->is_active);
    }

    public function test_setor_user_cannot_access_sector_admin_pages(): void
    {
        $user = User::factory()->create(['role' => UserRole::Setor]);

        $this->actingAs($user)
            ->get('/admin/setores')
            ->assertForbidden();
    }

    public function test_user_registers_with_active_sector_code(): void
    {
        $sector = Sector::factory()->create([
            'registration_code' => 'SEDUC-ABC12345',
            'is_active' => true,
        ]);

        Livewire::test(Register::class)
            ->set('name', 'Maria Silva')
            ->set('email', 'maria@example.com')
            ->set('registrationCode', 'seduc-abc12345')
            ->set('password', 'password123')
            ->set('passwordConfirmation', 'password123')
            ->call('register')
            ->assertRedirect(route('dashboards.index'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'maria@example.com',
            'role' => UserRole::Setor->value,
            'sector_id' => $sector->id,
        ]);
    }

    public function test_user_cannot_register_with_invalid_sector_code(): void
    {
        Livewire::test(Register::class)
            ->set('name', 'Carlos Souza')
            ->set('email', 'carlos@example.com')
            ->set('registrationCode', 'CODIGO-INVALIDO')
            ->set('password', 'password123')
            ->set('passwordConfirmation', 'password123')
            ->call('register')
            ->assertHasErrors(['registrationCode']);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', [
            'email' => 'carlos@example.com',
        ]);
    }

    public function test_sector_user_can_create_dashboard(): void
    {
        $sector = Sector::factory()->create();
        $user = User::factory()->create(['sector_id' => $sector->id]);

        $this->actingAs($user);

        Livewire::test(DashboardCreate::class)
            ->set('name', 'Panorama de Obras')
            ->set('description', 'Acompanhamento inicial de obras do setor.')
            ->call('save')
            ->assertRedirect();

        $this->assertDatabaseHas('dashboards', [
            'name' => 'Panorama de Obras',
            'sector_id' => $sector->id,
            'user_id' => $user->id,
            'status' => 'draft',
        ]);
    }

    public function test_sector_user_only_sees_dashboards_from_own_sector(): void
    {
        $sector = Sector::factory()->create();
        $otherSector = Sector::factory()->create();
        $user = User::factory()->create(['sector_id' => $sector->id]);
        $otherUser = User::factory()->create(['sector_id' => $otherSector->id]);

        Dashboard::factory()->create([
            'sector_id' => $sector->id,
            'user_id' => $user->id,
            'name' => 'Dashboard do Meu Setor',
        ]);

        Dashboard::factory()->create([
            'sector_id' => $otherSector->id,
            'user_id' => $otherUser->id,
            'name' => 'Dashboard de Outro Setor',
        ]);

        $this->actingAs($user)
            ->get('/dashboards')
            ->assertOk()
            ->assertSee('Dashboard do Meu Setor')
            ->assertDontSee('Dashboard de Outro Setor');
    }

    public function test_sector_user_cannot_open_dashboard_from_another_sector(): void
    {
        $sector = Sector::factory()->create();
        $otherSector = Sector::factory()->create();
        $user = User::factory()->create(['sector_id' => $sector->id]);
        $otherUser = User::factory()->create(['sector_id' => $otherSector->id]);
        $dashboard = Dashboard::factory()->create([
            'sector_id' => $otherSector->id,
            'user_id' => $otherUser->id,
        ]);

        $this->actingAs($user)
            ->get(route('dashboards.show', $dashboard))
            ->assertForbidden();
    }

    public function test_import_wizard_reads_csv_preview(): void
    {
        Storage::fake('local');

        $sector = Sector::factory()->create();
        $user = User::factory()->create(['sector_id' => $sector->id]);
        $dashboard = Dashboard::factory()->create([
            'sector_id' => $sector->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(ImportWizard::class, ['dashboard' => $dashboard])
            ->set('file', UploadedFile::fake()->createWithContent(
                'obras.csv',
                "Município,Valor previsto,Status\nMaceió,100000,Em andamento\nArapiraca,250000,Concluída\n"
            ))
            ->call('uploadFile')
            ->assertSet('step', 3)
            ->assertSee('Município')
            ->assertSee('Maceió');

        $columns = $component->get('columns');

        $this->assertSame('Município', $columns[0]['name']);
        $this->assertSame('municipio', $columns[0]['normalized_name']);
        $this->assertSame(['Maceió', 'Arapiraca'], array_slice($columns[0]['samples'], 0, 2));

        $this->assertDatabaseHas('dashboard_imports', [
            'dashboard_id' => $dashboard->id,
            'original_filename' => 'obras.csv',
            'status' => DashboardImportStatus::Mapped->value,
            'sheet_name' => $component->get('selectedSheet'),
        ]);
    }

    public function test_import_wizard_uses_header_start_cell_before_reading_file(): void
    {
        Storage::fake('local');

        $sector = Sector::factory()->create();
        $user = User::factory()->create(['sector_id' => $sector->id]);
        $dashboard = Dashboard::factory()->create([
            'sector_id' => $sector->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(ImportWizard::class, ['dashboard' => $dashboard])
            ->set('headerStartCell', 'A2')
            ->set('file', UploadedFile::fake()->createWithContent(
                'processos.csv',
                "\nEMPRESA,PROCESSO,VALOR,ASSUNTO\nEmpresa A,E:01800.000001/2026,R$ 203.534,80,Transporte\nEmpresa B,E:01800.000002/2026,R$ 735,56,Horas Extras\n"
            ))
            ->call('uploadFile')
            ->assertSet('headerStartCell', 'A2')
            ->assertSet('headerRow', 2)
            ->assertSee('EMPRESA')
            ->assertSee('Empresa A');

        $columns = $component->get('columns');

        $this->assertSame('EMPRESA', $columns[0]['name']);
        $this->assertSame('empresa', $columns[0]['normalized_name']);
        $this->assertSame('A', $columns[0]['letter']);
        $this->assertSame('Empresa A', $component->get('previewRows')[0]['values'][0]);
    }

    public function test_import_wizard_uses_data_end_cell_to_ignore_summary_rows(): void
    {
        Storage::fake('local');

        $sector = Sector::factory()->create();
        $user = User::factory()->create(['sector_id' => $sector->id]);
        $dashboard = Dashboard::factory()->create([
            'sector_id' => $sector->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(ImportWizard::class, ['dashboard' => $dashboard])
            ->set('dataEndCell', 'A3')
            ->set('file', UploadedFile::fake()->createWithContent(
                'totais.csv',
                "Cidade,Valor\nMaceió,100\nPenedo,200\nTotal,300\n"
            ))
            ->call('uploadFile')
            ->assertSet('dataEndCell', 'A3')
            ->assertSee('Maceió')
            ->assertDontSee('Total');

        $this->assertCount(2, $component->get('previewRows'));

        $this->assertDatabaseHas('dashboard_imports', [
            'dashboard_id' => $dashboard->id,
            'original_filename' => 'totais.csv',
            'data_end_cell' => 'A3',
            'status' => DashboardImportStatus::Mapped->value,
        ]);
    }

    public function test_import_wizard_requires_end_cell_after_header_cell(): void
    {
        Storage::fake('local');

        $sector = Sector::factory()->create();
        $user = User::factory()->create(['sector_id' => $sector->id]);
        $dashboard = Dashboard::factory()->create([
            'sector_id' => $sector->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        Livewire::test(ImportWizard::class, ['dashboard' => $dashboard])
            ->set('headerStartCell', 'A2')
            ->set('dataEndCell', 'A1')
            ->set('file', UploadedFile::fake()->createWithContent(
                'invalido.csv',
                "\nCidade,Valor\nMaceió,100\n"
            ))
            ->call('uploadFile')
            ->assertHasErrors(['dataEndCell']);
    }

    public function test_import_wizard_ignores_rows_and_columns_before_preview(): void
    {
        Storage::fake('local');

        $sector = Sector::factory()->create();
        $user = User::factory()->create(['sector_id' => $sector->id]);
        $dashboard = Dashboard::factory()->create([
            'sector_id' => $sector->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(ImportWizard::class, ['dashboard' => $dashboard])
            ->set('headerStartCell', 'A2')
            ->set('ignoredRowsInput', '3')
            ->set('excludedColumnsInput', 'D')
            ->set('file', UploadedFile::fake()->createWithContent(
                'creches.csv',
                "\n#,MUNICIPIO,ORDEM DE SERVIÇO,CONTRATO,STATUS\nDBN - DEBONI SISTEMAS CONSTRUTIVOS LTDA,,,,\n1,Santana do Ipanema,12/11/2025,Nº 06/2025,NÃO INICIADA\n2,Pão de Açúcar,12/11/2025,Nº 06/2025,EM ANDAMENTO\n"
            ))
            ->call('uploadFile')
            ->assertSet('ignoredRows', [3])
            ->assertSet('excludedColumns', ['D'])
            ->assertSee('Santana do Ipanema')
            ->assertDontSee('DBN - DEBONI')
            ->assertDontSee('CONTRATO')
            ->assertDontSee('Nº 06/2025');

        $columns = $component->get('columns');

        $this->assertSame(['A', 'B', 'C', 'E'], array_column($columns, 'letter'));
        $this->assertNotContains('CONTRATO', array_column($columns, 'name'));

        $import = DashboardImport::query()->where('original_filename', 'creches.csv')->firstOrFail();

        $this->assertSame([3], $import->ignored_rows);
        $this->assertSame(['D'], $import->excluded_columns);
    }

    public function test_spreadsheet_reader_reads_xlsx_sheets_columns_and_preview(): void
    {
        $path = storage_path('framework/testing/import-wizard-sample.xlsx');

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()
            ->setTitle('Resumo')
            ->fromArray([
                ['Indicador', 'Total'],
                ['Obras', 2],
            ]);

        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Obras')
            ->fromArray([
                ['Cidade', 'Investimento', 'Execução'],
                ['Maceió', 120000, '53%'],
                ['Penedo', 80000, '41%'],
            ]);

        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        try {
            $reader = app(SpreadsheetReaderService::class);

            $this->assertSame(['Resumo', 'Obras'], $reader->sheetNames($path));

            $preview = $reader->preview($path, 'Obras', 1, 10);

            $this->assertSame('Obras', $preview['sheet_name']);
            $this->assertSame('cidade', $preview['columns'][0]['normalized_name']);
            $this->assertSame('investimento', $preview['columns'][1]['normalized_name']);
            $this->assertSame('Maceió', $preview['rows'][0]['values'][0]);
            $this->assertSame('Penedo', $preview['rows'][1]['values'][0]);
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function test_spreadsheet_reader_can_start_header_on_another_column(): void
    {
        $path = storage_path('framework/testing/import-wizard-offset.xlsx');

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()
            ->setTitle('Processos')
            ->fromArray([
                ['', '', '', ''],
                ['', 'EMPRESA', 'PROCESSO', 'VALOR'],
                ['', 'Empresa A', 'E:01800.000001/2026', 'R$ 203.534,80'],
                ['', 'Total', '', 'R$ 203.534,80'],
            ]);

        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        try {
            $preview = app(SpreadsheetReaderService::class)
                ->preview($path, 'Processos', 2, 10, 1, 3, [], [2]);

            $this->assertSame('EMPRESA', $preview['columns'][0]['name']);
            $this->assertSame('B', $preview['columns'][0]['letter']);
            $this->assertSame('VALOR', $preview['columns'][1]['name']);
            $this->assertSame('D', $preview['columns'][1]['letter']);
            $this->assertSame('Empresa A', $preview['rows'][0]['values'][0]);
            $this->assertCount(1, $preview['rows']);
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}
