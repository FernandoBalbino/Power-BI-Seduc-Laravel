<?php

namespace App\Livewire\Admin\Sectors;

use App\Models\Sector;
use App\Services\SectorRegistrationCodeService;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Edit extends Component
{
    public Sector $sector;

    public string $name = '';

    public string $description = '';

    public string $registrationCode = '';

    public bool $isActive = true;

    public function mount(Sector $sector): void
    {
        $this->sector = $sector;
        $this->name = $sector->name;
        $this->description = $sector->description ?? '';
        $this->registrationCode = $sector->registration_code ?? '';
        $this->isActive = $sector->is_active;
    }

    public function regenerateCode(): void
    {
        $this->registrationCode = app(SectorRegistrationCodeService::class)->generate($this->sector->id);

        $this->sector->update([
            'registration_code' => $this->registrationCode,
        ]);

        session()->flash('status', 'Código de cadastro regenerado com sucesso.');
    }

    public function save(): void
    {
        $this->registrationCode = app(SectorRegistrationCodeService::class)->normalize($this->registrationCode);

        $validated = $this->validate($this->rules(), $this->messages());

        $this->sector->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?: null,
            'registration_code' => $validated['registrationCode'],
            'is_active' => $validated['isActive'],
        ]);

        session()->flash('status', 'Setor atualizado com sucesso.');

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
                Rule::unique('sectors', 'registration_code')->ignore($this->sector->id),
            ],
            'isActive' => ['boolean'],
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
            'registrationCode.required' => 'O setor precisa ter um código de cadastro.',
            'registrationCode.unique' => 'Este código já existe. Gere um novo código.',
            'registrationCode.max' => 'O código deve ser mais curto.',
        ];
    }

    public function render()
    {
        return view('livewire.admin.sectors.edit')
            ->layout('layouts.app')
            ->title('Editar Setor | SEDUC BI');
    }
}
