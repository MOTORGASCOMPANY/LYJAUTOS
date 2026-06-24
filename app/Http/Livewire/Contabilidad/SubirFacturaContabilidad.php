<?php

namespace App\Http\Livewire\Contabilidad;

use App\Models\FacturaContabilidad;
use Livewire\WithFileUploads;
use Livewire\Component;
use Illuminate\Support\Facades\Http; // Importante para la conexión API
use Illuminate\Support\Facades\Log;

class SubirFacturaContabilidad extends Component
{
    use WithFileUploads;

    public $open = false;
    public $tipo = 'compra'; // compra o venta
    public $numero, $proveedor, $ruc, $igv, $fecha_emision, $monto_total, $descripcion;
    public $archivo;

    // Nueva propiedad para el listado masivo
    public $listaVentas = [];

    protected $rules = [
        'archivo'       => 'required|mimes:pdf,jpg,jpeg,png|max:10240',
        'tipo'          => 'required',
        'ruc'           => 'required|max:11',
        //'igv'           => 'required|numeric',
        'monto_total'   => 'required|numeric',
    ];

    // Hook de Livewire: Se ejecuta automáticamente al cargar un archivo
    /*public function updatedArchivo2222()
    {
        // Validamos extensiones (agregamos JPEG que es la de tu log)
        $extension = strtolower($this->archivo->getClientOriginalExtension());
        if (in_array($extension, ['jpg', 'jpeg', 'png'])) {

            try {
                // Obtenemos la ruta absoluta del archivo temporal
                $rutaTemporal = $this->archivo->getRealPath();
                Log::info("Enviando a IA: " . $rutaTemporal);

                // LLAMADA CON TIMEOUT EXTENDIDO (120 segundos)
                $response = Http::timeout(120)->post('http://127.0.0.1:8001/extract-invoice', [
                    'path' => $rutaTemporal
                ]);

                if ($response->successful() && $response['status'] == 'success') {
                    $datosIA = $response['data'];
                    $this->ruc = $datosIA['ruc'] ?? '';
                    $this->igv = $datosIA['igv'] ?? '';
                    $this->monto_total = $datosIA['total'] ?? '';
                    $this->proveedor = $datosIA['proveedor'] ?? '';
                    $this->fecha_emision = $datosIA['fecha'] ?? '';
                    $this->emit("minAlert", ["titulo" => "IA FINALIZADA", "mensaje" => "Datos cargados correctamente", "icono" => "success"]);
                } else {
                    Log::error("IA respondió con error: " . $response->body());
                }
            } catch (\Exception $e) {
                Log::error("Error en conexión IA: " . $e->getMessage());
                $this->emit("minAlert", ["titulo" => "AVISO", "mensaje" => "La IA tardó demasiado, pero puedes llenar los datos manualmente", "icono" => "warning"]);
            }
        }
    }*/
    public function updatedArchivo()
    {
        try {
            $rutaTemporal = $this->archivo->getRealPath();
            Log::info("Enviando a IA (" . $this->tipo . "): " . $rutaTemporal);

            $response = Http::timeout(120)->post('http://127.0.0.1:8001/extract-invoice', [
                'path' => $rutaTemporal
            ]);

            if ($response->successful() && $response['status'] == 'success') {
                $datosIA = $response['data'];

                if (isset($response['es_listado']) && $response['es_listado'] == true) {
                    $rawComprobantes = [];

                    // Detectamos la llave que envió la IA
                    if (isset($datosIA['comprobantes'])) {
                        $rawComprobantes = $datosIA['comprobantes'];
                    } elseif (isset($datosIA['facturas'])) {
                        $rawComprobantes = $datosIA['facturas'];
                    }

                    // NORMALIZACIÓN: Mapeamos los nombres de la IA a los nombres de tu vista
                    $this->listaVentas = array_map(function ($item) {
                        // Primero obtenemos y limpiamos el total
                        $totalLimpio = $this->limpiarMonto($item['ImporteTotal'] ?? ($item['total'] ?? 0));
                        $igvCalculado = $totalLimpio * 0.18;

                        return [
                            'numero'    => $item['NroCPE'] ?? ($item['numero'] ?? 'S/N'),
                            'ruc'       => $item['Receptor']['RUC'] ?? ($item['ruc'] ?? 'N/A'),
                            'proveedor' => $item['Receptor']['Nombre'] ?? ($item['proveedor'] ?? '---'),
                            'total'     => $totalLimpio,
                            'igv'       => round($igvCalculado, 2),
                            'fecha'     => $item['FechaEmision'] ?? ($item['fecha'] ?? now()->format('Y-m-d')),
                        ];
                    }, $rawComprobantes);

                    $this->emit("minAlert", ["titulo" => "LISTADO CARGADO", "mensaje" => count($this->listaVentas) . " registros encontrados", "icono" => "success"]);
                } else {
                    // CARGA INDIVIDUAL
                    $this->listaVentas = [];
                    $this->ruc = $datosIA['ruc'] ?? '';
                    $this->proveedor = $datosIA['proveedor'] ?? '';
                    $this->fecha_emision = $datosIA['fecha'] ?? '';
                    $this->numero = $datosIA['numero'] ?? '';
                    $this->igv = $this->limpiarMonto($datosIA['igv'] ?? 0);
                    $this->monto_total = $this->limpiarMonto($datosIA['total'] ?? 0);

                    $this->emit("minAlert", ["titulo" => "IA FINALIZADA", "mensaje" => "Datos extraídos", "icono" => "success"]);
                }
            } else {
                throw new \Exception($response['message'] ?? 'Error desconocido en la IA');
            }
        } catch (\Exception $e) {
            Log::error("Error en conexión IA: " . $e->getMessage());
            $this->emit("minAlert", ["titulo" => "ERROR IA", "mensaje" => $e->getMessage(), "icono" => "error"]);
        }
    }

    private function limpiarMonto($valor)
    {
        if (is_numeric($valor)) return $valor;
        // Quita S/, comas y espacios si la IA los manda
        return (float) str_replace(['S/', 'S', ',', ' '], ['', '', '', ''], $valor);
    }

    /*public function guardar()
    {
        $this->validate();

        $ruta = $this->archivo->store('facturasContabilidad', 'public');
        $extension = $this->archivo->getClientOriginalExtension();

        FacturaContabilidad::create([
            'tipo'          => $this->tipo,
            'numero'        => $this->numero,
            'proveedor'     => $this->proveedor,
            'ruc'           => $this->ruc,
            'igv'           => $this->igv,
            'fecha_emision' => $this->fecha_emision,
            'monto_total'   => $this->monto_total,
            'descripcion'   => $this->descripcion,
            'nombre'        => $this->archivo->getClientOriginalName(),
            'ruta'          => $ruta,
            'extension'     => $extension,
            'usuario_id'    => auth()->id(),
        ]);

        $this->reset(['open', 'tipo', 'numero', 'proveedor', 'ruc', 'igv', 'fecha_emision', 'monto_total', 'descripcion', 'archivo']);
        $this->emit('facturaGuardada');
        $this->emit("minAlert", ["titulo" => "¡EXCELENTE TRABAJO!", "mensaje" => "La Factura se registro correctamente!", "icono" => "success"]);
    }*/

    public function guardar()
    {
        $this->validate();
        $ruta = $this->archivo->store('facturasContabilidad', 'public');

        FacturaContabilidad::create([
            'tipo'          => $this->tipo,
            'numero'        => $this->numero,
            'proveedor'     => $this->proveedor,
            'ruc'           => $this->ruc,
            'igv'           => $this->igv,
            'fecha_emision' => $this->fecha_emision,
            'monto_total'   => $this->monto_total,
            'descripcion'   => $this->descripcion,
            'nombre'        => $this->archivo->getClientOriginalName(),
            'ruta'          => $ruta,
            'extension'     => $this->archivo->getClientOriginalExtension(),
            'usuario_id'    => auth()->id(),
        ]);

        $this->finalizarProceso();
    }

    public function guardarMasivo()
    {
        if (empty($this->listaVentas)) return;

        $ruta = $this->archivo->store('facturasContabilidad', 'public');

        foreach ($this->listaVentas as $item) {
            FacturaContabilidad::create([
                'tipo'          => 'venta',
                'numero'        => $item['numero'] ?? null,
                'proveedor'     => $item['proveedor'] ?? 'N/A',
                'ruc'           => $item['ruc'] ?? 'N/A',
                'igv'           => $item['igv'],
                'fecha_emision' => $item['fecha'] ?? now()->format('Y-m-d'),
                'monto_total'   => $this->limpiarMonto($item['total'] ?? 0),
                'nombre'        => $this->archivo->getClientOriginalName(),
                'ruta'          => $ruta,
                'extension'     => 'pdf',
                'usuario_id'    => auth()->id(),
            ]);
        }

        $this->finalizarProceso();
    }

    public function finalizarProceso()
    {
        $this->reset(['open', 'tipo', 'numero', 'proveedor', 'ruc', 'igv', 'fecha_emision', 'monto_total', 'descripcion', 'archivo', 'listaVentas']);
        $this->emit('facturaGuardada');
        $this->emit("minAlert", ["titulo" => "¡PROCESO COMPLETADO!", "mensaje" => "Registros guardados correctamente", "icono" => "success"]);
    }

    public function render()
    {
        return view('livewire.contabilidad.subir-factura-contabilidad');
    }
}
