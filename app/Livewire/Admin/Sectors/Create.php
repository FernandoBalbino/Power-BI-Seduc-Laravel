<?php

namespace App\Livewire\Admin\Sectors;

use App\Models\Sector;
use App\Services\SectorRegistrationCodeService;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public string $description = '';

    public string $registrationCode = '';

    public function mount(): void
    {
        $this->generateCode();
    }

    public function generateCode(): void
    {
        $this->registrationCode = app(SectorRegistrationCodeService::class)->generate();
    }

    public function save(): void
    {
        $this->registrationCode = app(SectorRegistrationCodeService::class)->normalize($this->registrationCode);

        $validated = $this->validate($this->rules(), $this->messages());

        Sector::query()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?: null,
            'registration_code' => $validated['registrationCode'],
            'is_active' => true,
        ]);

        session()->flash('status', 'Setor criado com sucesso.');

        $this->redirectRoute('admin.sectors.index', navigate: true);
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'registrationCode' => [
                'required',
                'string',
                'max:60',
                Rule::unique('sectors', 'registration_code'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'name.required' => 'Informe o nome do setor.',
            'name.max' => 'O nome do setor deve ser mais curto.',
            'description.max' => 'A descrição deve ser mais curta.',
            'registrationCode.required' => 'Gere um código de cadastro.',
            'registrationCode.unique' => 'Este código já existe. Gere um novo código.',
            'registrationCode.max' => 'O código deve ser mais curto.',
        ];
    }

    public function render()
    {
        return view('livewire.admin.sectors.create')
            ->layout('layouts.app')
            ->title('Criar Setor | SEDUC BI');
    }
}
