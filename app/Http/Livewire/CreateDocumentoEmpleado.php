<?php

namespace App\Http\Livewire;

use App\Models\ContratoTrabajo;
use App\Models\DocumentoEmpleado;
use App\Models\DocumentoEmpleadoUser;
use App\Models\TipoDocumentoEmpleado;
use App\Models\User;
use setasign\Fpdi\Fpdi;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateDocumentoEmpleado extends Component
{
    use WithFileUploads;
    public $idEmpleado, $empleado;
    public $addDocument = false;
    public $tipoSel, $fechaInicial, $fechaCaducidad, $documento;
    public $tiposDisponibles;

    public $acepto = false;

    protected $rules = [
        "tipoSel" => "required|numeric|min:1",
        "documento" => "required|mimes:pdf",
    ];


    public function mount()
    {
        $this->empleado = ContratoTrabajo::find($this->idEmpleado);
        $this->listaDisponibles();
    }

    public function listaDisponibles()
    {
        $this->tiposDisponibles = TipoDocumentoEmpleado::all();
    }

    public function updatedAddDocument()
    {
        $this->listaDisponibles();
        $this->reset(["tipoSel", "fechaInicial", "fechaCaducidad", "documento"]);
    }

    public function render()
    {
        return view('livewire.create-documento-empleado');
    }


    /*public function agregarDocumentoooooo()
    {
        $this->validate();
        $nombre = $this->idEmpleado . '-doc-' . rand();

        $documento_guardado = DocumentoEmpleado::create([
            'tipoDocumento' => $this->tipoSel,
            'fechaInicio' => $this->fechaInicial,
            'fechaExpiracion' => $this->fechaCaducidad,
            'ruta' => $this->documento->storeAs('public/docsEmpleados', $nombre . '.' . $this->documento->extension()),
            'extension' => $this->documento->extension(),
        ]);

        $docTaller = DocumentoEmpleadoUser::create([
            'idDocumentoEmpleado' => $documento_guardado->id,
            'idUser' => $this->idEmpleado,
            'estado' => 1,
        ]);
        $this->emitTo('editar-empleado', 'refrescaEmpleado');
        $this->emitTo('documentos-empleados', 'resetEmpleado');
        $this->reset(["tipoSel", "fechaInicial", "fechaCaducidad", "documento", "addDocument"]);
        $this->tipoSel = "";
        $this->emit("CustomAlert", ["titulo" => "BUEN TRABAJO!", "mensaje" => "Se ingreso correctamente un nuevo documento del empleado " . $this->empleado->idUser, "icono" => "success"]);
    }*/

    public function agregarDocumento()
    {
        if ($this->tipoSel == 4) {
            $this->validate(['acepto' => 'accepted'], ['acepto.accepted' => 'Debe autorizar la firma digital para proceder.']);
        } else {
            $this->validate();
        }

        try {
            $nombreBase = $this->idEmpleado . '-doc-' . rand();
            $rutaFinal = '';

            if ($this->tipoSel == 4) {
                $user = User::find($this->empleado->idUser);

                // --- LLAMADA INTERNA AL CONTROLADOR ---
                // Instanciamos el controlador manualmente
                $pdfController = app(\App\Http\Controllers\PdfController::class);

                // Verificamos si es externo o planilla
                $esExterno = $this->empleado->cont_externo == 1;

                // Llamamos a la función y capturamos el objeto DomPDF que retorna
                // IMPORTANTE: Como tu controller hace 'return $pdf->stream()', 
                // el getContent() obtendrá los bytes del PDF.
                if ($esExterno) {
                    $pdfResponse = $pdfController->generaPdfContrato($this->idEmpleado);
                } else {
                    $pdfResponse = $pdfController->generaPdfContratoPlanilla($this->idEmpleado);
                }

                // Extraemos los bytes puros del PDF
                $pdfContent = $pdfResponse->getContent();

                // Guardamos temporalmente para que FPDI pueda procesarlo
                $tempPathOriginal = storage_path('app/temp_contrato_' . time() . '.pdf');
                file_put_contents($tempPathOriginal, $pdfContent);

                // Preparar FPDI para estampar
                $pdf = new Fpdi();

                // Usamos un try/catch interno por si el PDF tiene una versión muy alta para FPDI
                try {
                    $pageCount = $pdf->setSourceFile($tempPathOriginal);
                } catch (\Exception $e) {
                    throw new \Exception("El formato del PDF generado no es compatible para el estampado de firma. Error: " . $e->getMessage());
                }

                for ($n = 1; $n <= $pageCount; $n++) {
                    $tplIdx = $pdf->importPage($n);
                    $specs = $pdf->getTemplateSize($tplIdx);

                    // Creamos la página respetando el tamaño original (A4, etc)
                    $pdf->addPage($specs['orientation'], [$specs['width'], $specs['height']]);
                    $pdf->useTemplate($tplIdx);

                    // Estampamos la firma solo en la última página
                    if ($n === $pageCount && $user->rutaFirma) {
                        $rutaFirma = storage_path('app/public/' . str_replace('public/', '', $user->rutaFirma));
                        
                        if (file_exists($rutaFirma)) {
                            // 1. CONFIGURACIÓN DE DIMENSIONES (En milímetros)
                            $wFirma = 50; // Ancho de la imagen de la firma.
                            $hFirma = 20; // Alto aproximado que ocupará la firma.

                            // 2. LÓGICA DE POSICIONAMIENTO SEGÚN TIPO DE CONTRATO
                            if ($esExterno) {
                                // --- CONTRATO EXTERNO (Locación) ---
                                $posX = 125; // Aumenta para mover a la derecha, disminuye para la izquierda.
                                $posY = 215; // Aumenta para bajar, disminuye para subir.
                            } else {
                                // --- CONTRATO PLANILLA ---
                                $posX = 125; // Posición horizontal para el lado derecho.
                                $posY = 100; // Posición vertical (cerca del final de la página 3).
                            }

                            // 3. EJECUCIÓN DEL ESTAMPADO
                            // Image(ruta, x, y, ancho, alto)
                            //$pdf->Image($rutaFirma, $posX, $posY, $wFirma);
                            $pdf->Image($rutaFirma, $posX, $posY, $wFirma, $hFirma);
                            
                            // 4. TEXTO DE VALIDACIÓN (Debajo de la firma)
                            $pdf->SetFont('Arial', '', 7); 
                            
                            // Colocamos el cursor para el texto: mismo X que la imagen, pero bajamos un poco el Y.
                            // El "+ 18" es el margen entre la firma y el texto.
                            $pdf->SetXY($posX, $posY + 22); 
                            
                            // Cell(ancho, alto, texto, borde, salto, alineación)
                            $pdf->Cell($wFirma, 3, utf8_decode('Firmado digitalmente por:'), 0, 1, 'C');
                            
                            // El siguiente Cell aparece automáticamente debajo del anterior
                            $pdf->SetX($posX);
                            $pdf->Cell($wFirma, 3, utf8_decode($user->name), 0, 1, 'C');
                        }
                    }
                }

                // Guardar el PDF ya firmado
                $nombreArchivo = 'FIRMADO_' . $nombreBase . '.pdf';
                $rutaFinal = 'docsEmpleados/' . $nombreArchivo;
                $pdf->Output(storage_path('app/public/' . $rutaFinal), 'F');

                // Limpiar el archivo temporal
                if (file_exists($tempPathOriginal)) {
                    unlink($tempPathOriginal);
                }

            } else {
                // Lógica normal de subida de archivos (FilePond)
                $path = $this->documento->storeAs('public/docsEmpleados', $nombreBase . '.' . $this->documento->extension());
                $rutaFinal = str_replace('public/', '', $path);
            }

            // Guardar en Base de Datos
            $documento_guardado = DocumentoEmpleado::create([
                'tipoDocumento' => $this->tipoSel,
                'fechaInicio' => $this->fechaInicial,
                'fechaExpiracion' => $this->fechaCaducidad,
                'ruta' => 'public/' . $rutaFinal, // Mantengo tu formato de ruta
                'extension' => 'pdf',
            ]);

            DocumentoEmpleadoUser::create([
                'idDocumentoEmpleado' => $documento_guardado->id,
                'idUser' => $this->idEmpleado,
                'estado' => 1,
            ]);

            $this->emitTo('editar-empleado', 'refrescaEmpleado');
            $this->emitTo('documentos-empleados', 'resetEmpleado');
            $this->reset(["tipoSel", "fechaInicial", "fechaCaducidad", "documento", "addDocument", "acepto"]);
            $this->emit("CustomAlert", ["titulo" => "¡EXITO!", "mensaje" => "Contrato firmado y guardado.", "icono" => "success"]);
        } catch (\Exception $e) {
            $this->emit("CustomAlert", ["titulo" => "ERROR", "mensaje" => $e->getMessage(), "icono" => "error"]);
        }
    }
}
