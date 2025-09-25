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
use Illuminate\Support\Facades\Log;
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
            Log::info('Iniciando processamento do CSV:', [
                'filePath' => $this->filePath,
                'headers' => $this->headers
            ]);

            // Verifica se o arquivo existe
            if (!Storage::exists($this->filePath)) {
                throw new \Exception("Arquivo CSV não encontrado: {$this->filePath}");
            }

            // Obtém o caminho absoluto do arquivo
            $csvPath = Storage::path($this->filePath);

            // Lê o conteúdo do arquivo
            $content = file_get_contents($csvPath);

            // Remove BOM se existir
            $content = str_replace("\xEF\xBB\xBF", '', $content);

            // Cria o leitor CSV
            $csv = Reader::createFromString($content);
            $csv->setDelimiter(';');

            // Pula a primeira linha (cabeçalho)
            $records = iterator_to_array($csv->getRecords());
            array_shift($records); // Remove o cabeçalho

            // Conta o total de linhas
            $this->summary['totalRows'] = count($records);

            // Processa cada registro
            foreach ($records as $record) {
                // Converte o registro em array
                $values = is_array($record) ? array_values($record) : explode(';', $record[0]);

                // Garante que temos o número correto de valores
                if (count($values) !== count($this->headers)) {
                    Log::error('Número incorreto de campos:', [
                        'esperado' => count($this->headers),
                        'recebido' => count($values),
                        'valores' => $values
                    ]);
                    $this->summary['invalidRows']++;
                    continue;
                }

                // Combina os headers com os valores
                $data = array_combine($this->headers, $values);

                Log::info('Registro:', ['data' => $data]);

                // Valida os dados
                $validator = Validator::make($data, [
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|max:255',
                    'phone' => 'required|string|max:20',
                    'birthdate' => 'required|date',
                ]);

                if ($validator->fails()) {
                    $this->summary['invalidRows']++;
                    Log::info('Registro inválido:', [
                        'data' => $data,
                        'errors' => $validator->errors()->toArray()
                    ]);
                    continue;
                }

                // Verifica duplicatas
                if (Contact::where('email', $data['email'])->exists()) {
                    $this->summary['duplicateRows']++;
                    Log::info('Email duplicado:', ['email' => $data['email']]);
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
                    Log::error('Erro ao criar contato:', [
                        'data' => $data,
                        'error' => $e->getMessage()
                    ]);
                    throw $e;
                }
            }

            // Atualização final do sumário
            Cache::put('import_summary', $this->summary);

            Log::info('Importação concluída:', $this->summary);

        } catch (\Exception $e) {
            // Log do erro
            Log::error('Erro ao processar CSV: ' . $e->getMessage());
            throw $e;
        } finally {
            // Limpa o arquivo temporário
            Storage::delete($this->filePath);
        }
    }
}
