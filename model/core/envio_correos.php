<?php

namespace GSC_Systems\model;

require_once 'extras/fpdf/PDF.php';

class envio_correos extends \model
{
    public function __construct($data = false)
    {
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

    public function correo_docs_ventas($documento, $empresa, $isretencion, $isreenvio = false, $copias = '')
    {
        $result = array('error' => 'T', 'msj' => 'El documento no se encuentra autorizado, no se puede enviar el correo electrónico.');
        if ($documento->tipoid == 'F' && !$isreenvio) {
            $result = array('error' => 'F', 'msj' => "Correo enviado correctamente");
            return $result;
        }
        $ruta_xml = false;
        $ruta_pdf = false;
        if ($isretencion) {
            $carpeta = 'retencionescompra';
        } else if ($documento->coddocumento == '01') {
            $carpeta = 'facturas';
        } else if ($documento->coddocumento == '04') {
            $carpeta = 'notasdecredito';
        } else if ($documento->coddocumento == '05') {
            $carpeta = 'notasdedebito';
        } else if ($documento->coddocumento == '03') {
            $carpeta = 'liquidacionescompra';
        } else if ($documento->coddocumento == '06') {
            $carpeta = 'guiasremision';
        }

        //busco el xml para adjuntar el documento
        $rutaXmlAutorizado = JG_MYDOCS . 'datosEmpresas/' . $empresa->idempresa . "/documentosElectronicos/".$carpeta."/autorizados/";
        if ($isretencion) {
            $archivoAutorizado = $rutaXmlAutorizado . $documento->numero_retencion . ".xml";
        } else {
            $archivoAutorizado = $rutaXmlAutorizado . $documento->numero_documento . ".xml";
        }
        if (file_exists($archivoAutorizado)) {
            $ruta_xml = $archivoAutorizado;
        } else {
            $result = array('error' => 'T', 'msj' => 'No se encuentra el XML firmado del Documento.');
        }
        //Genero el archivo pdf
        $rutaRide = JG_MYDOCS . 'datosEmpresas/' . $empresa->idempresa . "/rides/".$carpeta."/";
        if (!file_exists($rutaRide)) {
            @mkdir($rutaRide, 0777, true);
        }
        if ($isretencion) {
            $archivoRide = $rutaRide . $documento->numero_retencion . ".pdf";
            $this->generar_pdf_electronico_ret($documento, $empresa, 'a4', $archivoRide);
        } else {
            $archivoRide = $rutaRide . $documento->numero_documento . ".pdf";
            $this->generar_pdf_electronico($documento, $empresa, 'a4', $archivoRide);
        }
        if (file_exists($archivoRide)) {
            $ruta_pdf = $archivoRide;
        }

        if ($ruta_xml && $ruta_pdf) {
            if ($empresa->can_send_mail()) {
                try {
                    $ccopia = !$isreenvio;
                    $mail = $empresa->new_mail('', '', $ccopia);
                    $mail->addAddress($documento->email, $documento->razonsocial);
                    if ($isretencion) {
                        $mail->Subject = 'Su Retencion Electronica N. ' . $documento->numero_retencion;
                    } else {
                        $mail->Subject = 'Su '.$documento->get_tipodoc().' Electronica N. ' . $documento->numero_documento;
                    }
                    $mail->AltBody = $empresa->razonsocial;
                    $mail->AddAttachment($ruta_xml);
                    $mail->AddAttachment($ruta_pdf);
                    $logo = $empresa->logo;
                    if (!$logo) {
                        $logo = JG_PATH . 'view/img/sinlogo.png';
                    }
                    $mail->AddEmbeddedImage($logo, 'logo');
                    if ($copias != '') {
                        $dcopias = explode(",", $copias);
                        foreach ($dcopias as $key => $c) {
                            $mail->addCC(trim($c));
                        }
                    }
                    $mail->Body = $this->plantilla_correo($documento, $empresa, $isretencion);
                    $mail->isHTML(true);
                    if (!$empresa->mail_connect($mail)) {
                        $result = array('error' => 'T', 'msj' => "No se ha podido conectar por email. ¿La contraseña es correcta?");
                    } else {
                        if ($mail->send()) {
                            $result = array('error' => 'F', 'msj' => "Correo enviado correctamente");
                            if (file_exists($ruta_pdf)) {
                                unlink($ruta_pdf);
                            }
                        }
                    }
                } catch (Exception $e) {
                    $result = array('error' => 'T', 'msj' => "El mensaje no se ha enviado. Mailer Error: {$mail->ErrorInfo}");
                }
            } else {
                $result = array('error' => 'T', 'msj' => 'La empresa no tiene la configuración necesaria para el envío de correos electrónicos.');
            }
        }
        return $result;
    }

    private function plantilla_correo($documento, $empresa, $isretencion = false)
    {
        $tdoc = $documento->get_tipodoc();
        $ndoc = $documento->numero_documento;
        $fdoc = $documento->fec_emision;
        $adoc = $documento->nro_autorizacion;
        $tota = $documento->total;
        if ($isretencion) {
            $tdoc = 'Retencion';
            $ndoc = $documento->numero_retencion;
            $fdoc = $documento->fec_registro;
            $adoc = $documento->nro_autorizacion_ret;
            $tota = $documento->totalret();
        }
        $plantilla = '<!DOCTYPE html>
        <html lang="en" xmlns="https://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width,initial-scale=1">
            <meta name="x-apple-disable-message-reformatting">
            <title></title>
            <!--[if mso]>
            <noscript>
                <xml>
                    <o:OfficeDocumentSettings>
                        <o:PixelsPerInch>96</o:PixelsPerInch>
                    </o:OfficeDocumentSettings>
                </xml>
            </noscript>
            <![endif]-->
            <style>
                table, td, div, h1, p {font-family: Arial, sans-serif;}
                table, td {border:2px solid #000000 !important;}
            </style>
        </head>
        <body style="margin:0;padding:0;">
            <table role="presentation" style="width: 100%; border-collapse: collapse; border: 0; border-spacing: 0; background: #ffffff;">
                <tbody>
                <tr>
                <th align="center" style="padding: 0; background: #AED6F1;"><img src="cid:logo" alt="' . $empresa->nombrecomercial . '" height="150" style="width: auto; display: block;" /></th>
                </tr>
                <tr>
                <td style="padding: 10px 10px 10px 30px;">
                <p><strong> Estimado(a). ' . $documento->razonsocial . ', </strong> <br><br>Su ' . $tdoc . ' Electr&oacute;nica N. <b> ' . $ndoc . ' </b> ha sido generada con los siguientes datos:</p>
                <p><b> Fecha de Emisi&oacute;n: </b>' . $fdoc . '</p>
                <p><b> N&uacute;mero de Autorizaci&oacute;n: </b>' . $adoc . '</p>';
        if ($documento->coddocumento != '06') {
            $plantilla . '<p><b> Valor Total: </b>' . show_numero($tota) . ' $</p>';
        }

        $plantilla .= '<p><br><br><b> Atentamente: </b></p>
                <p>' . $empresa->razonsocial . ' <br>' . $empresa->ruc . '</p>
                <p><b>Gracias por formar parte de nuestra empresa</b><br></p>
                </td>
                </tr>
                <tr>
                <td style="background: #D6DBDF;">
                <table role="presentation" style="width: 100%; border-collapse: collapse; border: 0; border-spacing: 0;">
                <tbody>
                <tr>
                <th align="center">
                <p>Powered by &reg; GSC_Systems, ' . date("Y") . '&nbsp; &nbsp;&nbsp;</p>
                </th>
                </tr>
                </tbody>
                </table>
                </td>
                </tr>
                </tbody>
            </table>
        </body>
        </html>';

        return $plantilla;
    }

    private function generar_pdf_electronico($documento, $empresa, $size = 'a4', $archivo = false)
    {
        if ($size == 'a5') {
            $pdf = new \PDF('A5');
        } else {
            $pdf = new \PDF();
        }
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->cabecera_docs_electronicos($documento, $empresa);
        if ($documento->coddocumento == '06') {
            $ypos = $pdf->GetY();
            $pdf->transporte_docs_electronicos($documento, $ypos);
            $ypos = $pdf->GetY();
            $pdf->clientetransporte_docs_electronicos($documento, $ypos);
        } else {
            $ypos = $pdf->GetY();
            $pdf->cliente_docs_electronicos($documento, $ypos);
        }
        $ypos = $pdf->GetY();
        $pdf->detfactura_docs_electronicos($documento, $ypos);
        $ypos = $pdf->GetY();
        if ($documento->coddocumento == '06') {
            if ($documento->tipo_guia == 2) {
                $pdf->subtotales_docs_electronicos($documento, $ypos);
            }
        } else {
            $pdf->subtotales_docs_electronicos($documento, $ypos);
            $pdf->infoad_docs_electronicos($documento, $ypos);
        }

        if ($archivo) {
            $pdf->save($archivo);
        } else {
            $pdf->show($documento->get_tipodoc() . "-" . $documento->numero_documento.'.pdf');
        }
    }

    private function generar_pdf_electronico_ret($documento, $empresa, $size = 'a4', $archivo = false)
    {
        if ($size == 'a5') {
            $pdf = new \PDF('A5');
        } else {
            $pdf = new \PDF();
        }
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->cabecera_docs_electronicos($documento, $empresa, true);
        $ypos = $pdf->GetY();
        $pdf->cliente_docs_electronicos($documento, $ypos, true);
        $ypos = $pdf->GetY();
        $pdf->detfactura_docs_electronicos($documento, $ypos, true);
        $ypos = $pdf->GetY();
        //$pdf->subtotales_docs_electronicos($documento, $ypos);
        $pdf->infoad_docs_electronicos($documento, $ypos, true);

        if ($archivo) {
            $pdf->save($archivo);
        } else {
            $pdf->show("03-" . $documento->numero_retencion.'.pdf');
        }
    }
}
