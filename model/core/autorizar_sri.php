<?php

namespace GSC_Systems\model;

class autorizar_sri extends \model
{
    private $xml;
    private $enviar_correo;

    public function __construct($data = false)
    {
        $this->xml           = new \xml_documentos();
        $this->enviar_correo = new \envio_correos();
    }
    public function install()
    {
        return '';
    }

    public function exists()
    {
        return '';
    }

    public function save()
    {
        return '';
    }

    public function delete()
    {
        return '';
    }

    public function procesar_documento_sri($documento, $empresa, $isretencion = false)
    {
        $result1 = array('error' => 'T', 'msj' => 'Error al procesar factura en el SRI.');
        //genero el xml
        if ($isretencion) {
            $xml_doc = $this->xml->xml_retencion_compra($documento, $empresa);
            $carpeta = 'retencionescompra';
        } else if ($documento->coddocumento == '01') {
            $xml_doc = $this->xml->xml_factura($documento, $empresa);
            $carpeta = 'facturas';
        } else if ($documento->coddocumento == '04') {
            $xml_doc = $this->xml->xml_nota_credito($documento, $empresa);
            $carpeta = 'notasdecredito';
        } else if ($documento->coddocumento == '05') {
            $xml_doc = $this->xml->xml_nota_debito($documento, $empresa);
            $carpeta = 'notasdedebito';
        } else if ($documento->coddocumento == '03') {
            $xml_doc = $this->xml->xml_liquidacion_compra($documento, $empresa);
            $carpeta = 'liquidacionescompra';
        } else if ($documento->coddocumento == '06') {
            $xml_doc = $this->xml->xml_guia_remision($documento, $empresa);
            $carpeta = 'guiasremision';
        } else {
            $result1 = array('error' => 'T', 'msj' => 'Documento No Encontrado.');
            return $result1;
        }
        //Generado el Xml procedo a enviarlo
        if ($isretencion) {
            $result = firmarXmlDocumento($xml_doc, $documento->nro_autorizacion_ret, $empresa->ruc);
        } else {
            $result = firmarXmlDocumento($xml_doc, $documento->nro_autorizacion, $empresa->ruc);
        }
        if ($result['error'] == 'F') {
            //Valido que la carpeta exista para almacenar el archivo firmado
            $rutaXmlFirmado = JG_MYDOCS . 'datosEmpresas/' . $empresa->idempresa . "/documentosElectronicos/".$carpeta."/firmados/";
            if (!file_exists($rutaXmlFirmado)) {
                @mkdir($rutaXmlFirmado, 0777, true);
            }
            if ($isretencion) {
                $archivoFirmado = $rutaXmlFirmado . $documento->numero_retencion . ".xml";
            } else {
                $archivoFirmado = $rutaXmlFirmado . $documento->numero_documento . ".xml";
            }
            if (file_exists($archivoFirmado)) {
                unlink($archivoFirmado);
            }
            //Guardamos el Archivo firmado
            $file = fopen($archivoFirmado, "w+");
            //Escribo el archivo
            fwrite($file, $result['xmlFirmado']);
            //Cierro el Archivo
            fclose($file);
            //lo envio a autorizar
            //Busco el ambiente
            $ambiente = 1;
            if ($empresa->produccion) {
                $ambiente = 2;
            }
            //Genero la Autorizacion del Documento
            if ($isretencion) {
                $result2 = autorizacionDocumento($documento->nro_autorizacion_ret, $empresa->ruc, $ambiente);
            } else {
                $result2 = autorizacionDocumento($documento->nro_autorizacion, $empresa->ruc, $ambiente);
            }
            if ($result2['error'] == 'F') {
                $rutaXmlAutorizado = JG_MYDOCS . 'datosEmpresas/' . $empresa->idempresa . "/documentosElectronicos/".$carpeta."/autorizados/";
                if (!file_exists($rutaXmlAutorizado)) {
                    @mkdir($rutaXmlAutorizado, 0777, true);
                }
                if ($isretencion) {
                    $archivoAutorizado = $rutaXmlAutorizado . $documento->numero_retencion . ".xml";
                } else {
                    $archivoAutorizado = $rutaXmlAutorizado . $documento->numero_documento . ".xml";
                }
                if (file_exists($archivoAutorizado)) {
                    unlink($archivoAutorizado);
                }
                //Guardamos el Archivo firmado
                $file2 = fopen($archivoAutorizado, "w+");
                //Escribo el archivo
                fwrite($file2, $result2['xmlAutorizado']);
                //Cierro el Archivo
                fclose($file2);
                $result1 = array('error' => 'F', 'msj' => 'Comprobante Autorizado Correctamente.');
            } else {
                $result1 = array('error' => 'T', 'msj' => $result2['msj']);
            }
        } else {
            $result1 = array('error' => 'T', 'msj' => $result['msj']);
        }

        return $result1;
    }
}
