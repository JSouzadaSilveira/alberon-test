<?php

namespace App\Jobs;

use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;
use League\Csv\Statement;

class ProcessCsvImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $headers;
    protected $summary = [
        'totalRows' => 0,
        'importedRows' => 0,
        'duplicateRows' => 0,
        'invalidRows' => 0
    ];

    /**
     * Create a new job instance.
     */
    public function __construct(string $filePath, array $headers)
    {
        $this->filePath = $filePath;
        $this->headers = $headers;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Verifica se o arquivo existe
            if (!Storage::exists($this->filePath)) {
                throw new \Exception("Arquivo CSV não encontrado: {$this->filePath}");
            }

            // Obtém o caminho absoluto do arquivo
            $csvPath = Storage::path($this->filePath);

            // Cria o leitor CSV
            $csv = Reader::createFromPath($csvPath, 'r');
            $csv->setHeaderOffset(0);
            $csv->setDelimiter(',');

            // Processa os registros
            $records = $csv->getRecords();

            // Conta o total de linhas
            $this->summary['totalRows'] = iterator_count($csv);

            // Processa cada registro
            foreach ($records as $record) {
                $data = array_combine($this->headers, array_values($record));

                // Valida os dados
                $validator = Validator::make($data, [
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|max:255',
                    'phone' => 'required|string|max:20',
                    'birthdate' => 'required|date',
                ]);

                if ($validator->fails()) {
                    $this->summary['invalidRows']++;
                    \Log::info('Registro inválido:', [
                        'data' => $data,
                        'errors' => $validator->errors()->toArray()
                    ]);
                    continue;
                }

                // Verifica duplicatas
                if (Contact::where('email', $data['email'])->exists()) {
                    $this->summary['duplicateRows']++;
                    \Log::info('Email duplicado:', ['email' => $data['email']]);
                    continue;
                }

                // Cria o contato
                try {
                    Contact::create($data);
                    $this->summary['importedRows']++;

                    // Atualiza o sumário no cache periodicamente
                    if ($this->summary['importedRows'] % 100 === 0) {
                        Cache::put('import_summary', $this->summary);
                    }
                } catch (\Exception $e) {
                    \Log::error('Erro ao criar contato:', [
                        'data' => $data,
                        'error' => $e->getMessage()
                    ]);
                    throw $e;
                }
            }

            // Atualização final do sumário
            Cache::put('import_summary', $this->summary);

            \Log::info('Importação concluída:', $this->summary);

        } catch (\Exception $e) {
            // Log do erro
            \Log::error('Erro ao processar CSV: ' . $e->getMessage());
            throw $e;
        } finally {
            // Limpa o arquivo temporário
            Storage::delete($this->filePath);
        }
    }
}
