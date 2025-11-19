<?php

namespace GSC_Systems\model;

class xml_documentos extends \model
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

    public function xml_factura($documento, $empresa)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<factura id="comprobante" version="2.1.0">'; //Aperturo el tag Factura
            $xml .= '<infoTributaria>'; //Aperturo <infoTributaria>
                $xml .= '<ambiente>'.substr($documento->nro_autorizacion, 23, 1).'</ambiente>';
                $xml .= '<tipoEmision>1</tipoEmision>';
                $xml .= '<razonSocial>'.$empresa->razonsocial.'</razonSocial>';
                $xml .= '<nombreComercial>'.$empresa->nombrecomercial.'</nombreComercial>';
                $xml .= '<ruc>'.$empresa->ruc.'</ruc>';
                $xml .= '<claveAcceso>'.$documento->nro_autorizacion.'</claveAcceso>';
                $xml .= '<codDoc>'.$documento->coddocumento.'</codDoc>';
                //Separo el numero de Documento
                $doc = explode("-", $documento->numero_documento);
                $xml .= '<estab>'.$doc[0].'</estab>';
                $xml .= '<ptoEmi>'.$doc[1].'</ptoEmi>';
                $xml .= '<secuencial>'.$doc[2].'</secuencial>';
                $xml .= '<dirMatriz>'.substr($empresa->direccion, 0, 300).'</dirMatriz>';
                if ($documento->agretencion_empresa) {
                    $xml .= '<agenteRetencion>1</agenteRetencion>';
                }
                if ($documento->regimen_empresa == 'RE') {
                    $xml .= '<contribuyenteRimpe>CONTRIBUYENTE RÉGIMEN RIMPE</contribuyenteRimpe>';
                } else if ($documento->regimen_empresa == 'RP') {
                    $xml .= '<contribuyenteRimpe>CONTRIBUYENTE NEGOCIO POPULAR - RÉGIMEN RIMPE</contribuyenteRimpe>';
                }
            $xml .= '</infoTributaria>'; //Cierro <infoTributaria>
            $xml .= '<infoFactura>'; //Aperturo <infoFactura>
                $xml .= '<fechaEmision>'.date('d/m/Y', strtotime($documento->fec_emision)).'</fechaEmision>';
                $xml .= '<dirEstablecimiento>'.substr($documento->get_establecimiento()->direccion, 0, 300).'</dirEstablecimiento>';
                if ($documento->regimen_empresa == 'CE') {
                    $xml .= '<contribuyenteEspecial>0000000000001</contribuyenteEspecial>';
                }
                if ($documento->obligado_empresa) {
                    $xml .= '<obligadoContabilidad>SI</obligadoContabilidad>';
                } else {
                    $xml .= '<obligadoContabilidad>NO</obligadoContabilidad>';
                }
                switch ($documento->tipoid) {
                    case 'C':
                        $xml .= '<tipoIdentificacionComprador>05</tipoIdentificacionComprador>';
                        break;
                    case 'R':
                        $xml .= '<tipoIdentificacionComprador>04</tipoIdentificacionComprador>';
                        break;
                    case 'P':
                        $xml .= '<tipoIdentificacionComprador>06</tipoIdentificacionComprador>';
                        break;
                    case 'F':
                        $xml .= '<tipoIdentificacionComprador>07</tipoIdentificacionComprador>';
                        break;
                    default:
                        break;
                }
                $xml .= '<razonSocialComprador>'.$documento->razonsocial.'</razonSocialComprador>';
                $xml .= '<identificacionComprador>'.$documento->identificacion.'</identificacionComprador>';
                $xml .= '<direccionComprador>'.$documento->direccion.'</direccionComprador>';
                $xml .= '<totalSinImpuestos>'.round($documento->base_noi + $documento->base_0 + $documento->base_gra + $documento->base_exc, 2).'</totalSinImpuestos>';
                $xml .= '<totalDescuento>'.round($documento->totaldescuento, 2).'</totalDescuento>';
                //Bases imponibles
                $subtotal00 = 0;
                $subtotal12 = 0;
                $subtotal14 = 0;
                $subtotalno = 0;
                $subtotalex = 0;
                $subtotaldi = 0;
                //Ivas
                $iva00 = 0;
                $iva12 = 0;
                $iva14 = 0;
                $ivano = 0;
                $ivaex = 0;
                $ivadi = 0;
                //Para el total con Impuestos recorro las lineas del documento
                $detalles = '<detalles>'; //Aperturo <detalles>
                foreach ($documento->getlineas() as $key => $linea) {
                    $detalles .= '<detalle>'; //Aperturo <detalle>
                        $detalles .= '<codigoPrincipal>'.substr($linea->codprincipal, 0, 25).'</codigoPrincipal>';
                        if ($linea->get_articulo()->codauxiliar && JG_IMP_CODAUXILIAR == 1) {
                            //$detalles .= '<codigoAuxiliar>'.substr($linea->get_articulo()->codauxiliar, 0, 25).'</codigoAuxiliar>';
                        }
                        $detalles .= '<descripcion>'.substr($linea->descripcion, 0, 300).'</descripcion>';
                        $detalles .= '<cantidad>'.round($linea->cantidad, 6).'</cantidad>';
                        $detalles .= '<precioUnitario>'.round($linea->pvpunitario, 6).'</precioUnitario>';
                        $detalles .= '<descuento>'.round($linea->pvpsindto - $linea->pvptotal, 2).'</descuento>';
                        $detalles .= '<precioTotalSinImpuesto>'.round($linea->pvptotal, 2).'</precioTotalSinImpuesto>';
                        $detalles .= '<impuestos>'; //Aperturo <impuestos>
                            $detalles .= '<impuesto>'; //Aperturo <impuesto>
                                $detalles .= '<codigo>2</codigo>';
                                switch ($linea->get_impuesto()) {
                                    case 'IVANO':
                                        $detalles .= '<codigoPorcentaje>6</codigoPorcentaje>';
                                        $subtotalno += $linea->pvptotal;
                                        $ivano += $linea->valoriva;
                                        break;
                                    case 'IVA0':
                                        $detalles .= '<codigoPorcentaje>0</codigoPorcentaje>';
                                        $subtotal00 += $linea->pvptotal;
                                        $iva00 += $linea->valoriva;
                                        break;
                                    case 'IVAEX':
                                        $detalles .= '<codigoPorcentaje>7</codigoPorcentaje>';
                                        $subtotalex += $linea->pvptotal;
                                        $ivaex += $linea->valoriva;
                                        break;
                                    case 'IVA12':
                                        $detalles .= '<codigoPorcentaje>2</codigoPorcentaje>';
                                        $subtotal12 += $linea->pvptotal;
                                        $iva12 += $linea->valoriva;
                                        break;
                                    case 'IVA14':
                                        $detalles .= '<codigoPorcentaje>3</codigoPorcentaje>';
                                        $subtotal14 += $linea->pvptotal;
                                        $iva14 += $linea->valoriva;
                                        break;
                                    default:
                                        $detalles .= '<codigoPorcentaje>8</codigoPorcentaje>';
                                        $subtotaldi += $linea->pvptotal;
                                        $ivadi += $linea->valoriva;
                                        break;
                                }
                                $detalles .= '<tarifa>'.$linea->get_porcentaje_impuesto().'</tarifa>';
                                $detalles .= '<baseImponible>'.round($linea->pvptotal, 2).'</baseImponible>';
                                $detalles .= '<valor>'.round($linea->valoriva, 2).'</valor>';
                            $detalles .= '</impuesto>'; //Cierro </impuesto>
                        $detalles .= '</impuestos>'; //Cierro </impuestos>
                    $detalles .= '</detalle>'; //Cierro </detalle>
                }
                $detalles .= '</detalles>'; //Cierro </detalles>
                $xml .= '<totalConImpuestos>'; //Aperturo <totalConImpuestos>
                    //Base 0%
                    $xml .= '<totalImpuesto>'; //Aperturo <totalImpuesto>
                        $xml .= '<codigo>2</codigo>';
                        $xml .= '<codigoPorcentaje>0</codigoPorcentaje>';
                        $xml .= '<baseImponible>'.round($subtotal00, 2).'</baseImponible>';
                        $xml .= '<tarifa>0.00</tarifa>';
                        $xml .= '<valor>'.round($iva00, 2).'</valor>';
                    $xml .= '</totalImpuesto>'; //Cierro <totalImpuesto>
                    //Base 12
                    $xml .= '<totalImpuesto>'; //Aperturo <totalImpuesto>
                        $xml .= '<codigo>2</codigo>';
                        $xml .= '<codigoPorcentaje>2</codigoPorcentaje>';
                        $xml .= '<baseImponible>'.round($subtotal12, 2).'</baseImponible>';
                        $xml .= '<tarifa>12.00</tarifa>';
                        $xml .= '<valor>'.round($iva12, 2).'</valor>';
                    $xml .= '</totalImpuesto>'; //Cierro <totalImpuesto>
                    if ($subtotal14 > 0) {
                        //Base 14
                        $xml .= '<totalImpuesto>'; //Aperturo <totalImpuesto>
                            $xml .= '<codigo>2</codigo>';
                            $xml .= '<codigoPorcentaje>3</codigoPorcentaje>';
                            $xml .= '<baseImponible>'.round($subtotal14, 2).'</baseImponible>';
                            $xml .= '<tarifa>14.00</tarifa>';
                            $xml .= '<valor>'.round($iva14, 2).'</valor>';
                        $xml .= '</totalImpuesto>'; //Cierro <totalImpuesto>
                    }
                    if ($subtotalno > 0) {
                        //Base No Objeto 
                        $xml .= '<totalImpuesto>'; //Aperturo <totalImpuesto>
                            $xml .= '<codigo>2</codigo>';
                            $xml .= '<codigoPorcentaje>6</codigoPorcentaje>';
                            $xml .= '<baseImponible>'.round($subtotalno, 2).'</baseImponible>';
                            $xml .= '<tarifa>0.00</tarifa>';
                            $xml .= '<valor>'.round($ivano, 2).'</valor>';
                        $xml .= '</totalImpuesto>'; //Cierro <totalImpuesto>
                    }
                    if ($subtotalex > 0) {
                        //Base Excento
                        $xml .= '<totalImpuesto>'; //Aperturo <totalImpuesto>
                            $xml .= '<codigo>2</codigo>';
                            $xml .= '<codigoPorcentaje>7</codigoPorcentaje>';
                            $xml .= '<baseImponible>'.round($subtotalex, 2).'</baseImponible>';
                            $xml .= '<tarifa>0.00</tarifa>';
                            $xml .= '<valor>'.round($ivaex, 2).'</valor>';
                        $xml .= '</totalImpuesto>'; //Cierro <totalImpuesto>
                    }
                    if ($subtotaldi > 0) {
                        //Base Diferenciada
                        $xml .= '<totalImpuesto>'; //Aperturo <totalImpuesto>
                            $xml .= '<codigo>2</codigo>';
                            $xml .= '<codigoPorcentaje>8</codigoPorcentaje>';
                            $xml .= '<baseImponible>'.round($subtotaldi, 2).'</baseImponible>';
                            $xml .= '<tarifa>8.00</tarifa>';
                            $xml .= '<valor>'.round($ivadi, 2).'</valor>';
                        $xml .= '</totalImpuesto>'; //Cierro <totalImpuesto>
                    }
                $xml .= '</totalConImpuestos>'; //Cierro <totalConImpuestos>
                $xml .= '<propina>0.00</propina>';
                $xml .= '<importeTotal>'.round($documento->total, 2).'</importeTotal>';
                $xml .= '<moneda>DOLAR</moneda>';
                $xml .= '<pagos>'; //Aperturo <pagos>
                    if ($fpsri = $documento->get_fp_sri()) {
                        $xml .= '<pago>'; //Aperturo <pago>
                            $xml .= '<formaPago>'.$fpsri->codigo.'</formaPago>';
                            $xml .= '<total>'.round($documento->total, 2).'</total>';
                            $xml .= '<plazo>'.$documento->diascredito.'</plazo>';
                            $xml .= '<unidadTiempo>dias</unidadTiempo>';
                        $xml .= '</pago>'; //Cierro <pago>
                    } else {
                        $xml .= '<pago>'; //Aperturo <pago>
                            $xml .= '<formaPago>20</formaPago>';
                            $xml .= '<total>'.round($documento->total, 2).'</total>';
                            $xml .= '<plazo>'.$documento->diascredito.'</plazo>';
                            $xml .= '<unidadTiempo>dias</unidadTiempo>';
                        $xml .= '</pago>'; //Cierro <pago>
                    }
                $xml .= '</pagos>'; //Cierro <pagos>
            $xml .= '</infoFactura>'; //Cierro <infoFactura>
            //Contateno el detalle de la factura
            $xml .= $detalles;
            //Concateno la informacion adicional
            $xml .= '<infoAdicional>'; //Aperturo <infoAdicional>
                $xml .= '<campoAdicional nombre="Email">'.$documento->email.'</campoAdicional>';
                if (strlen($documento->observaciones) > 0) {
                    $xml .= '<campoAdicional nombre="Observaciones">'.$documento->observaciones.'</campoAdicional>';
                }
                if ($med = $documento->get_medidor()) {
                    $xml .= '<campoAdicional nombre="Num-Medidor">'.$med->numero.'</campoAdicional>';
                }
            $xml .= '</infoAdicional>'; //Cierro <infoAdicional>
        $xml .= '</factura>'; //Cierro el tag Factura

        $xml = limpiarCaracteres($xml);
        $xml = str_replace("REGIMEN RIMPE", "RÉGIMEN RIMPE", $xml);
        return $xml;
    }

    public function xml_nota_debito($documento, $empresa)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<notaDebito version="1.0.0" id="comprobante">'; //Aperturo el tag NotaDebito
            $xml .= '<infoTributaria>'; //Aperturo <infoTributaria>
                $xml .= '<ambiente>'.substr($documento->nro_autorizacion, 23, 1).'</ambiente>';
                $xml .= '<tipoEmision>1</tipoEmision>';
                $xml .= '<razonSocial>'.$empresa->razonsocial.'</razonSocial>';
                $xml .= '<nombreComercial>'.$empresa->nombrecomercial.'</nombreComercial>';
                $xml .= '<ruc>'.$empresa->ruc.'</ruc>';
                $xml .= '<claveAcceso>'.$documento->nro_autorizacion.'</claveAcceso>';
                $xml .= '<codDoc>'.$documento->coddocumento.'</codDoc>';
                //Separo el numero de Documento
                $doc = explode("-", $documento->numero_documento);
                $xml .= '<estab>'.$doc[0].'</estab>';
                $xml .= '<ptoEmi>'.$doc[1].'</ptoEmi>';
                $xml .= '<secuencial>'.$doc[2].'</secuencial>';
                $xml .= '<dirMatriz>'.substr($empresa->direccion, 0, 300).'</dirMatriz>';
                if ($documento->agretencion_empresa) {
                    $xml .= '<agenteRetencion>1</agenteRetencion>';
                }
                if ($documento->regimen_empresa == 'RE') {
                    $xml .= '<contribuyenteRimpe>CONTRIBUYENTE RÉGIMEN RIMPE</contribuyenteRimpe>';
                } else if ($documento->regimen_empresa == 'RP') {
                    $xml .= '<contribuyenteRimpe>CONTRIBUYENTE NEGOCIO POPULAR - RÉGIMEN RIMPE</contribuyenteRimpe>';
                }
            $xml .= '</infoTributaria>'; //Cierro <infoTributaria>
            $xml .= '<infoNotaDebito>'; //Aperturo <infoNotaDebito>
            $xml .= '<fechaEmision>'.date('d/m/Y', strtotime($documento->fec_emision)).'</fechaEmision>';
                $xml .= '<dirEstablecimiento>'.substr($documento->get_establecimiento()->direccion, 0, 300).'</dirEstablecimiento>';
                switch ($documento->tipoid) {
                    case 'C':
                        $xml .= '<tipoIdentificacionComprador>05</tipoIdentificacionComprador>';
                        break;
                    case 'R':
                        $xml .= '<tipoIdentificacionComprador>04</tipoIdentificacionComprador>';
                        break;
                    case 'P':
                        $xml .= '<tipoIdentificacionComprador>06</tipoIdentificacionComprador>';
                        break;
                    case 'F':
                        $xml .= '<tipoIdentificacionComprador>07</tipoIdentificacionComprador>';
                        break;
                    default:
                        break;
                }
                $xml .= '<razonSocialComprador>'.$documento->razonsocial.'</razonSocialComprador>';
                $xml .= '<identificacionComprador>'.$documento->identificacion.'</identificacionComprador>';
                if ($documento->regimen_empresa == 'CE') {
                    $xml .= '<contribuyenteEspecial>0000000000001</contribuyenteEspecial>';
                }
                if ($documento->obligado_empresa) {
                    $xml .= '<obligadoContabilidad>SI</obligadoContabilidad>';
                } else {
                    $xml .= '<obligadoContabilidad>NO</obligadoContabilidad>';
                }
                $xml .= '<codDocModificado>'.$documento->coddocumento_mod.'</codDocModificado>';
                $xml .= '<numDocModificado>'.$documento->numero_documento_mod.'</numDocModificado>';
                $xml .= '<fechaEmisionDocSustento>'.date('d/m/Y', strtotime($documento->fec_emision_mod)).'</fechaEmisionDocSustento>';
                $xml .= '<totalSinImpuestos>'.round($documento->base_noi + $documento->base_0 + $documento->base_gra + $documento->base_exc, 2).'</totalSinImpuestos>';
                //Bases imponibles
                $subtotal00 = 0;
                $subtotal12 = 0;
                $subtotal14 = 0;
                $subtotalno = 0;
                $subtotalex = 0;
                $subtotaldi = 0;
                //Ivas
                $iva00 = 0;
                $iva12 = 0;
                $iva14 = 0;
                $ivano = 0;
                $ivaex = 0;
                $ivadi = 0;
                //Para el total con Impuestos recorro las lineas del documento
                $motivos = '<motivos>'; //Aperturo <motivos>
                foreach ($documento->getlineas() as $key => $linea) {
                    $motivos .= '<motivo>'; //Aperturo <motivo>
                        switch ($linea->get_impuesto()) {
                            case 'IVANO':
                                $subtotalno += $linea->pvptotal;
                                $ivano += $linea->valoriva;
                                break;
                            case 'IVA0':                                    
                                $subtotal00 += $linea->pvptotal;
                                $iva00 += $linea->valoriva;
                                break;
                            case 'IVAEX':
                                $subtotalex += $linea->pvptotal;
                                $ivaex += $linea->valoriva;
                                break;
                            case 'IVA12':
                                $subtotal12 += $linea->pvptotal;
                                $iva12 += $linea->valoriva;
                                break;
                            case 'IVA14':
                                $subtotal14 += $linea->pvptotal;
                                $iva14 += $linea->valoriva;
                                break;
                            default:
                                $subtotaldi += $linea->pvptotal;
                                $ivadi += $linea->valoriva;
                                break;
                        }
                        $motivos .= '<razon>'.$linea->descripcion.'</razon>';
                        $motivos .= '<valor>'.round($linea->pvptotal, 2).'</valor>';
                    $motivos .= '</motivo>'; //Cierro </motivo>
                }
                $motivos .= '</motivos>'; //Cierro </motivos>
             $xml .= '<impuestos>'; //Aperturo <impuestos>
                    //Base 0%
                    $xml .= '<impuesto>'; //Aperturo <impuesto>
                        $xml .= '<codigo>2</codigo>';
                        $xml .= '<codigoPorcentaje>0</codigoPorcentaje>';
                        $xml .= '<tarifa>0.00</tarifa>';
                        $xml .= '<baseImponible>'.round($subtotal00, 2).'</baseImponible>';
                        $xml .= '<valor>'.round($iva00, 2).'</valor>';
                    $xml .= '</impuesto>'; //Cierro <impuesto>
                    //Base 12
                    $xml .= '<impuesto>'; //Aperturo <impuesto>
                        $xml .= '<codigo>2</codigo>';
                        $xml .= '<codigoPorcentaje>2</codigoPorcentaje>';
                        $xml .= '<tarifa>12.00</tarifa>';
                        $xml .= '<baseImponible>'.round($subtotal12, 2).'</baseImponible>';
                        $xml .= '<valor>'.round($iva12, 2).'</valor>';
                    $xml .= '</impuesto>'; //Cierro <impuesto>
                    if ($subtotal14 > 0) {
                        //Base 14
                        $xml .= '<impuesto>'; //Aperturo <impuesto>
                            $xml .= '<codigo>2</codigo>';
                            $xml .= '<codigoPorcentaje>3</codigoPorcentaje>';
                            $xml .= '<tarifa>14.00</tarifa>';
                            $xml .= '<baseImponible>'.round($subtotal14, 2).'</baseImponible>';
                            $xml .= '<valor>'.round($iva14, 2).'</valor>';
                        $xml .= '</impuesto>'; //Cierro <impuesto>
                    }
                    if ($subtotalno > 0) {
                        //Base No Objeto 
                        $xml .= '<impuesto>'; //Aperturo <impuesto>
                            $xml .= '<codigo>2</codigo>';
                            $xml .= '<codigoPorcentaje>6</codigoPorcentaje>';
                            $xml .= '<tarifa>0.00</tarifa>';
                            $xml .= '<baseImponible>'.round($subtotalno, 2).'</baseImponible>';
                            $xml .= '<valor>'.round($ivano, 2).'</valor>';
                        $xml .= '</impuesto>'; //Cierro <impuesto>
                    }
                    if ($subtotalex > 0) {
                        //Base Excento
                        $xml .= '<impuesto>'; //Aperturo <impuesto>
                            $xml .= '<codigo>2</codigo>';
                            $xml .= '<codigoPorcentaje>7</codigoPorcentaje>';
                            $xml .= '<tarifa>0.00</tarifa>';
                            $xml .= '<baseImponible>'.round($subtotalex, 2).'</baseImponible>';
                            $xml .= '<valor>'.round($ivaex, 2).'</valor>';
                        $xml .= '</impuesto>'; //Cierro <impuesto>
                    }
                    if ($subtotaldi > 0) {
                        //Base Diferenciada
                        $xml .= '<impuesto>'; //Aperturo <impuesto>
                            $xml .= '<codigo>2</codigo>';
                            $xml .= '<codigoPorcentaje>8</codigoPorcentaje>';
                            $xml .= '<tarifa>8.00</tarifa>';
                            $xml .= '<baseImponible>'.round($subtotaldi, 2).'</baseImponible>';
                            $xml .= '<valor>'.round($ivadi, 2).'</valor>';
                        $xml .= '</impuesto>'; //Cierro <impuesto>
                    }
                $xml .= '</impuestos>'; //Cierro <impuestos>
                $xml .= '<valorTotal>'.round($documento->total, 2).'</valorTotal>';
                $xml .= '<pagos>'; //Aperturo <pagos>
                    if ($fpsri = $documento->get_fp_sri()) {
                        $xml .= '<pago>'; //Aperturo <pago>
                            $xml .= '<formaPago>'.$fpsri->codigo.'</formaPago>';
                            $xml .= '<total>'.round($documento->total, 2).'</total>';
                            $xml .= '<plazo>'.$documento->diascredito.'</plazo>';
                            $xml .= '<unidadTiempo>dias</unidadTiempo>';
                        $xml .= '</pago>'; //Cierro <pago>
                    } else {
                        $xml .= '<pago>'; //Aperturo <pago>
                            $xml .= '<formaPago>20</formaPago>';
                            $xml .= '<total>'.round($documento->total, 2).'</total>';
                            $xml .= '<plazo>'.$documento->diascredito.'</plazo>';
                            $xml .= '<unidadTiempo>dias</unidadTiempo>';
                        $xml .= '</pago>'; //Cierro <pago>
                    }
                $xml .= '</pagos>'; //Cierro <pagos>
            $xml .= '</infoNotaDebito>'; //Cierro <infoNotaDebito>
            //Contateno el detalle de la factura
            $xml .= $motivos;
            //Concateno la informacion adicional
            $xml .= '<infoAdicional>'; //Aperturo <infoAdicional>
                $xml .= '<campoAdicional nombre="Email">'.$documento->email.'</campoAdicional>';
                if (strlen($documento->observaciones) > 0) {
                    $xml .= '<campoAdicional nombre="Observaciones">'.$documento->observaciones.'</campoAdicional>';
                }
                if ($med = $documento->get_medidor()) {
                    $xml .= '<campoAdicional nombre="Num-Medidor">'.$med->numero.'</campoAdicional>';
                }
            $xml .= '</infoAdicional>'; //Cierro <infoAdicional>
        $xml .= '</notaDebito>'; //Cierro el tag NotaDebito

        $xml = limpiarCaracteres($xml);
        $xml = str_replace("REGIMEN RIMPE", "RÉGIMEN RIMPE", $xml);
        return $xml;
    }

    public function xml_nota_credito($documento, $empresa)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>';
        $xml .= '<notaCredito id="comprobante" version="1.1.0">'; //Aperturo el tag notaCredito
            $xml .= '<infoTributaria>'; //Aperturo <infoTributaria>
                $xml .= '<ambiente>'.substr($documento->nro_autorizacion, 23, 1).'</ambiente>';
                $xml .= '<tipoEmision>1</tipoEmision>';
                $xml .= '<razonSocial>'.$empresa->razonsocial.'</razonSocial>';
                $xml .= '<nombreComercial>'.$empresa->nombrecomercial.'</nombreComercial>';
                $xml .= '<ruc>'.$empresa->ruc.'</ruc>';
                $xml .= '<claveAcceso>'.$documento->nro_autorizacion.'</claveAcceso>';
                $xml .= '<codDoc>'.$documento->coddocumento.'</codDoc>';
                //Separo el numero de Documento
                $doc = explode("-", $documento->numero_documento);
                $xml .= '<estab>'.$doc[0].'</estab>';
                $xml .= '<ptoEmi>'.$doc[1].'</ptoEmi>';
                $xml .= '<secuencial>'.$doc[2].'</secuencial>';
                $xml .= '<dirMatriz>'.substr($empresa->direccion, 0, 300).'</dirMatriz>';
                if ($documento->agretencion_empresa) {
                    $xml .= '<agenteRetencion>1</agenteRetencion>';
                }
                if ($documento->regimen_empresa == 'RE') {
                    $xml .= '<contribuyenteRimpe>CONTRIBUYENTE RÉGIMEN RIMPE</contribuyenteRimpe>';
                } else if ($documento->regimen_empresa == 'RP') {
                    $xml .= '<contribuyenteRimpe>CONTRIBUYENTE NEGOCIO POPULAR - RÉGIMEN RIMPE</contribuyenteRimpe>';
                }
            $xml .= '</infoTributaria>'; //Cierro <infoTributaria>
            $xml .= '<infoNotaCredito>'; //Aperturo <infoNotaCredito>
                $xml .= '<fechaEmision>'.date('d/m/Y', strtotime($documento->fec_emision)).'</fechaEmision>';
                $xml .= '<dirEstablecimiento>'.substr($documento->get_establecimiento()->direccion, 0, 300).'</dirEstablecimiento>';
                switch ($documento->tipoid) {
                    case 'C':
                        $xml .= '<tipoIdentificacionComprador>05</tipoIdentificacionComprador>';
                        break;
                    case 'R':
                        $xml .= '<tipoIdentificacionComprador>04</tipoIdentificacionComprador>';
                        break;
                    case 'P':
                        $xml .= '<tipoIdentificacionComprador>06</tipoIdentificacionComprador>';
                        break;
                    case 'F':
                        $xml .= '<tipoIdentificacionComprador>07</tipoIdentificacionComprador>';
                        break;
                    default:
                        break;
                }
                $xml .= '<razonSocialComprador>'.$documento->razonsocial.'</razonSocialComprador>';
                $xml .= '<identificacionComprador>'.$documento->identificacion.'</identificacionComprador>';
                if ($documento->regimen_empresa == 'CE') {
                    $xml .= '<contribuyenteEspecial>0000000000001</contribuyenteEspecial>';
                }
                if ($documento->obligado_empresa) {
                    $xml .= '<obligadoContabilidad>SI</obligadoContabilidad>';
                } else {
                    $xml .= '<obligadoContabilidad>NO</obligadoContabilidad>';
                }
                $xml .= '<codDocModificado>'.$documento->coddocumento_mod.'</codDocModificado>';
                $xml .= '<numDocModificado>'.$documento->numero_documento_mod.'</numDocModificado>';
                $xml .= '<fechaEmisionDocSustento>'.date('d/m/Y', strtotime($documento->fec_emision_mod)).'</fechaEmisionDocSustento>';
                $xml .= '<totalSinImpuestos>'.round($documento->base_noi + $documento->base_0 + $documento->base_gra + $documento->base_exc, 2).'</totalSinImpuestos>';
                $xml .= '<valorModificacion>'.round($documento->total, 2).'</valorModificacion>';
                $xml .= '<moneda>DOLAR</moneda>';
                //Bases imponibles
                $subtotal00 = 0;
                $subtotal12 = 0;
                $subtotal14 = 0;
                $subtotalno = 0;
                $subtotalex = 0;
                $subtotaldi = 0;
                //Ivas
                $iva00 = 0;
                $iva12 = 0;
                $iva14 = 0;
                $ivano = 0;
                $ivaex = 0;
                $ivadi = 0;
                //Para el total con Impuestos recorro las lineas del documento
                $detalles = '<detalles>'; //Aperturo <detalles>
                foreach ($documento->getlineas() as $key => $linea) {
                    $detalles .= '<detalle>'; //Aperturo <detalle>
                        $detalles .= '<codigoInterno>'.substr($linea->codprincipal, 0, 25).'</codigoInterno>';
                        $detalles .= '<descripcion>'.substr($linea->descripcion, 0, 300).'</descripcion>';
                        $detalles .= '<cantidad>'.round($linea->cantidad, 6).'</cantidad>';
                        $detalles .= '<precioUnitario>'.round($linea->pvpunitario, 6).'</precioUnitario>';
                        $detalles .= '<descuento>'.round($linea->pvpsindto - $linea->pvptotal, 2).'</descuento>';
                        $detalles .= '<precioTotalSinImpuesto>'.round($linea->pvptotal, 2).'</precioTotalSinImpuesto>';
                        $detalles .= '<impuestos>'; //Aperturo <impuestos>
                            $detalles .= '<impuesto>'; //Aperturo <impuesto>
                                $detalles .= '<codigo>2</codigo>';
                                switch ($linea->get_impuesto()) {
                                    case 'IVANO':
                                        $detalles .= '<codigoPorcentaje>6</codigoPorcentaje>';
                                        $subtotalno += $linea->pvptotal;
                                        $ivano += $linea->valoriva;
                                        break;
                                    case 'IVA0':
                                        $detalles .= '<codigoPorcentaje>0</codigoPorcentaje>';
                                        $subtotal00 += $linea->pvptotal;
                                        $iva00 += $linea->valoriva;
                                        break;
                                    case 'IVAEX':
                                        $detalles .= '<codigoPorcentaje>7</codigoPorcentaje>';
                                        $subtotalex += $linea->pvptotal;
                                        $ivaex += $linea->valoriva;
                                        break;
                                    case 'IVA12':
                                        $detalles .= '<codigoPorcentaje>2</codigoPorcentaje>';
                                        $subtotal12 += $linea->pvptotal;
                                        $iva12 += $linea->valoriva;
                                        break;
                                    case 'IVA14':
                                        $detalles .= '<codigoPorcentaje>3</codigoPorcentaje>';
                                        $subtotal14 += $linea->pvptotal;
                                        $iva14 += $linea->valoriva;
                                        break;
                                    default:
                                        $detalles .= '<codigoPorcentaje>8</codigoPorcentaje>';
                                        $subtotaldi += $linea->pvptotal;
                                        $ivadi += $linea->valoriva;
                                        break;
                                }
                                $detalles .= '<tarifa>'.$linea->get_porcentaje_impuesto().'</tarifa>';
                                $detalles .= '<baseImponible>'.round($linea->pvptotal, 2).'</baseImponible>';
                                $detalles .= '<valor>'.round($linea->valoriva, 2).'</valor>';
                            $detalles .= '</impuesto>'; //Cierro </impuesto>
                        $detalles .= '</impuestos>'; //Cierro </impuestos>
                    $detalles .= '</detalle>'; //Cierro </detalle>
                }
                $detalles .= '</detalles>'; //Cierro </detalles>
                $xml .= '<totalConImpuestos>'; //Aperturo <totalConImpuestos>
                    //Base 0%
                    $xml .= '<totalImpuesto>'; //Aperturo <totalImpuesto>
                        $xml .= '<codigo>2</codigo>';
                        $xml .= '<codigoPorcentaje>0</codigoPorcentaje>';
                        $xml .= '<baseImponible>'.round($subtotal00, 2).'</baseImponible>';
                        $xml .= '<valor>'.round($iva00, 2).'</valor>';
                    $xml .= '</totalImpuesto>'; //Cierro <totalImpuesto>
                    //Base 12
                    $xml .= '<totalImpuesto>'; //Aperturo <totalImpuesto>
                        $xml .= '<codigo>2</codigo>';
                        $xml .= '<codigoPorcentaje>2</codigoPorcentaje>';
                        $xml .= '<baseImponible>'.round($subtotal12, 2).'</baseImponible>';
                        $xml .= '<valor>'.round($iva12, 2).'</valor>';
                    $xml .= '</totalImpuesto>'; //Cierro <totalImpuesto>
                    if ($subtotal14 > 0) {
                        //Base 14
                        $xml .= '<totalImpuesto>'; //Aperturo <totalImpuesto>
                            $xml .= '<codigo>2</codigo>';
                            $xml .= '<codigoPorcentaje>3</codigoPorcentaje>';
                            $xml .= '<baseImponible>'.round($subtotal14, 2).'</baseImponible>';
                            $xml .= '<valor>'.round($iva14, 2).'</valor>';
                        $xml .= '</totalImpuesto>'; //Cierro <totalImpuesto>
                    }
                    if ($subtotalno > 0) {
                        //Base No Objeto 
                        $xml .= '<totalImpuesto>'; //Aperturo <totalImpuesto>
                            $xml .= '<codigo>2</codigo>';
                            $xml .= '<codigoPorcentaje>6</codigoPorcentaje>';
                            $xml .= '<baseImponible>'.round($subtotalno, 2).'</baseImponible>';
                            $xml .= '<valor>'.round($ivano, 2).'</valor>';
                        $xml .= '</totalImpuesto>'; //Cierro <totalImpuesto>
                    }
                    if ($subtotalex > 0) {
                        //Base Excento
                        $xml .= '<totalImpuesto>'; //Aperturo <totalImpuesto>
                            $xml .= '<codigo>2</codigo>';
                            $xml .= '<codigoPorcentaje>7</codigoPorcentaje>';
                            $xml .= '<baseImponible>'.round($subtotalex, 2).'</baseImponible>';
                            $xml .= '<valor>'.round($ivaex, 2).'</valor>';
                        $xml .= '</totalImpuesto>'; //Cierro <totalImpuesto>
                    }
                    if ($subtotaldi > 0) {
                        //Base Diferenciada
                        $xml .= '<totalImpuesto>'; //Aperturo <totalImpuesto>
                            $xml .= '<codigo>2</codigo>';
                            $xml .= '<codigoPorcentaje>8</codigoPorcentaje>';
                            $xml .= '<baseImponible>'.round($subtotaldi, 2).'</baseImponible>';
                            $xml .= '<valor>'.round($ivadi, 2).'</valor>';
                        $xml .= '</totalImpuesto>'; //Cierro <totalImpuesto>
                    }
                $xml .= '</totalConImpuestos>'; //Cierro <totalConImpuestos>
                $xml .= '<motivo>'.$documento->observaciones.'</motivo>';
            $xml .= '</infoNotaCredito>'; //Cierro <infoNotaCredito>
            //Contateno el detalle de la Nota de Credito
            $xml .= $detalles;
            //Concateno la informacion adicional
            $xml .= '<infoAdicional>'; //Aperturo <infoAdicional>
                $xml .= '<campoAdicional nombre="Email">'.$documento->email.'</campoAdicional>';
                if ($med = $documento->get_medidor()) {
                    $xml .= '<campoAdicional nombre="Num-Medidor">'.$med->numero.'</campoAdicional>';
                }
            $xml .= '</infoAdicional>'; //Cierro <infoAdicional>
        $xml .= '</notaCredito>'; //Cierro el tag notaCredito

        $xml = limpiarCaracteres($xml);
        $xml = str_replace("REGIMEN RIMPE", "RÉGIMEN RIMPE", $xml);
        return $xml;
    }

    public function xml_liquidacion_compra($documento, $empresa)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<liquidacionCompra id="comprobante" version="1.1.0">'; //Aperturo el tag liquidacionCompra
            $xml .= '<infoTributaria>'; //Aperturo <infoTributaria>
                $xml .= '<ambiente>'.substr($documento->nro_autorizacion, 23, 1).'</ambiente>';
                $xml .= '<tipoEmision>1</tipoEmision>';
                $xml .= '<razonSocial>'.$empresa->razonsocial.'</razonSocial>';
                $xml .= '<nombreComercial>'.$empresa->nombrecomercial.'</nombreComercial>';
                $xml .= '<ruc>'.$empresa->ruc.'</ruc>';
                $xml .= '<claveAcceso>'.$documento->nro_autorizacion.'</claveAcceso>';
                $xml .= '<codDoc>'.$documento->coddocumento.'</codDoc>';
                //Separo el numero de Documento
                $doc = explode("-", $documento->numero_documento);
                $xml .= '<estab>'.$doc[0].'</estab>';
                $xml .= '<ptoEmi>'.$doc[1].'</ptoEmi>';
                $xml .= '<secuencial>'.$doc[2].'</secuencial>';
                $xml .= '<dirMatriz>'.substr($empresa->direccion, 0, 300).'</dirMatriz>';
                if ($documento->agretencion_empresa) {
                    $xml .= '<agenteRetencion>1</agenteRetencion>';
                }
                if ($documento->regimen_empresa == 'RE') {
                    $xml .= '<contribuyenteRimpe>CONTRIBUYENTE RÉGIMEN RIMPE</contribuyenteRimpe>';
                } else if ($documento->regimen_empresa == 'RP') {
                    $xml .= '<contribuyenteRimpe>CONTRIBUYENTE NEGOCIO POPULAR - RÉGIMEN RIMPE</contribuyenteRimpe>';
                }
            $xml .= '</infoTributaria>'; //Cierro <infoTributaria>
            $xml .= '<infoLiquidacionCompra>'; //Aperturo <infoLiquidacionCompra>
                $xml .= '<fechaEmision>'.date('d/m/Y', strtotime($documento->fec_emision)).'</fechaEmision>';
                $xml .= '<dirEstablecimiento>'.substr($documento->get_establecimiento()->direccion, 0, 300).'</dirEstablecimiento>';
                if ($documento->regimen_empresa == 'CE') {
                    $xml .= '<contribuyenteEspecial>0000000000001</contribuyenteEspecial>';
                }
                if ($documento->obligado_empresa) {
                    $xml .= '<obligadoContabilidad>SI</obligadoContabilidad>';
                } else {
                    $xml .= '<obligadoContabilidad>NO</obligadoContabilidad>';
                }
                switch ($documento->tipoid) {
                    case 'C':
                        $xml .= '<tipoIdentificacionProveedor>05</tipoIdentificacionProveedor>';
                        break;
                    case 'R':
                        $xml .= '<tipoIdentificacionProveedor>04</tipoIdentificacionProveedor>';
                        break;
                    case 'P':
                        $xml .= '<tipoIdentificacionProveedor>06</tipoIdentificacionProveedor>';
                        break;
                    case 'F':
                        $xml .= '<tipoIdentificacionProveedor>07</tipoIdentificacionProveedor>';
                        break;
                    default:
                        break;
                }
                $xml .= '<razonSocialProveedor>'.$documento->razonsocial.'</razonSocialProveedor>';
                $xml .= '<identificacionProveedor>'.$documento->identificacion.'</identificacionProveedor>';
                $xml .= '<direccionProveedor>'.$documento->direccion.'</direccionProveedor>';
                $xml .= '<totalSinImpuestos>'.round($documento->base_noi + $documento->base_0 + $documento->base_gra + $documento->base_exc, 2).'</totalSinImpuestos>';
                $xml .= '<totalDescuento>'.$documento->totaldescuento.'</totalDescuento>';
                //Bases imponibles
                $subtotal00 = 0;
                $subtotal12 = 0;
                $subtotal14 = 0;
                $subtotalno = 0;
                $subtotalex = 0;
                $subtotaldi = 0;
                //Ivas
                $iva00 = 0;
                $iva12 = 0;
                $iva14 = 0;
                $ivano = 0;
                $ivaex = 0;
                $ivadi = 0;
                //Para el total con Impuestos recorro las lineas del documento
                $detalles = '<detalles>'; //Aperturo <detalles>
                    foreach ($documento->getlineas() as $key => $linea) {
                        $detalles .= '<detalle>'; //Aperturo <detalle>
                            $detalles .= '<codigoPrincipal>'.substr($linea->codprincipal, 0, 25).'</codigoPrincipal>';
                            $detalles .= '<descripcion>'.substr($linea->descripcion, 0, 300).'</descripcion>';
                            $detalles .= '<cantidad>'.round($linea->cantidad, 6).'</cantidad>';
                            $detalles .= '<precioUnitario>'.round($linea->pvpunitario, 6).'</precioUnitario>';
                            $detalles .= '<descuento>'.round($linea->pvpsindto - $linea->pvptotal, 6).'</descuento>';
                            $detalles .= '<precioTotalSinImpuesto>'.round($linea->pvptotal, 6).'</precioTotalSinImpuesto>';
                            $detalles .= '<impuestos>'; //Aperturo <impuestos>
                                $detalles .= '<impuesto>'; //Aperturo <impuesto>
                                    $detalles .= '<codigo>2</codigo>';
                                    switch ($linea->get_impuesto()) {
                                        case 'IVANO':
                                            $detalles .= '<codigoPorcentaje>6</codigoPorcentaje>';
                                            $subtotalno += $linea->pvptotal;
                                            $ivano += $linea->valoriva;
                                            break;
                                        case 'IVA0':
                                            $detalles .= '<codigoPorcentaje>0</codigoPorcentaje>';
                                            $subtotal00 += $linea->pvptotal;
                                            $iva00 += $linea->valoriva;
                                            break;
                                        case 'IVAEX':
                                            $detalles .= '<codigoPorcentaje>7</codigoPorcentaje>';
                                            $subtotalex += $linea->pvptotal;
                                            $ivaex += $linea->valoriva;
                                            break;
                                        case 'IVA12':
                                            $detalles .= '<codigoPorcentaje>2</codigoPorcentaje>';
                                            $subtotal12 += $linea->pvptotal;
                                            $iva12 += $linea->valoriva;
                                            break;
                                        case 'IVA14':
                                            $detalles .= '<codigoPorcentaje>3</codigoPorcentaje>';
                                            $subtotal14 += $linea->pvptotal;
                                            $iva14 += $linea->valoriva;
                                            break;
                                        default:
                                            $detalles .= '<codigoPorcentaje>8</codigoPorcentaje>';
                                            $subtotaldi += $linea->pvptotal;
                                            $ivadi += $linea->valoriva;
                                            break;
                                    }
                                    $detalles .= '<tarifa>'.$linea->get_porcentaje_impuesto().'</tarifa>';
                                    $detalles .= '<baseImponible>'.round($linea->pvptotal, 2).'</baseImponible>';
                                    $detalles .= '<valor>'.round($linea->valoriva, 2).'</valor>';
                                $detalles .= '</impuesto>'; //Cierro </impuesto>
                            $detalles .= '</impuestos>'; //Cierro </impuestos>
                        $detalles .= '</detalle>'; //Cierro </detalle>
                    }
                $detalles .= '</detalles>'; //Cierro </detalles>
                $xml .= '<totalConImpuestos>'; //Aperturo <totalConImpuestos>
                    //Base 0%
                    $xml .= '<totalImpuesto>'; //Aperturo <totalImpuesto>
                        $xml .= '<codigo>2</codigo>';
                        $xml .= '<codigoPorcentaje>0</codigoPorcentaje>';
                        $xml .= '<baseImponible>'.round($subtotal00, 2).'</baseImponible>';
                        $xml .= '<tarifa>0.00</tarifa>';
                        $xml .= '<valor>'.$iva00.'</valor>';
                    $xml .= '</totalImpuesto>'; //Cierro <totalImpuesto>
                    //Base 12
                    $xml .= '<totalImpuesto>'; //Aperturo <totalImpuesto>
                        $xml .= '<codigo>2</codigo>';
                        $xml .= '<codigoPorcentaje>2</codigoPorcentaje>';
                        $xml .= '<baseImponible>'.round($subtotal12, 2).'</baseImponible>';
                        $xml .= '<tarifa>12.00</tarifa>';
                        $xml .= '<valor>'.$iva12.'</valor>';
                    $xml .= '</totalImpuesto>'; //Cierro <totalImpuesto>
                    if ($subtotal14 > 0) {
                        //Base 14
                        $xml .= '<totalImpuesto>'; //Aperturo <totalImpuesto>
                            $xml .= '<codigo>2</codigo>';
                            $xml .= '<codigoPorcentaje>3</codigoPorcentaje>';
                            $xml .= '<baseImponible>'.round($subtotal14, 2).'</baseImponible>';
                            $xml .= '<tarifa>14.00</tarifa>';
                            $xml .= '<valor>'.$iva14.'</valor>';
                        $xml .= '</totalImpuesto>'; //Cierro <totalImpuesto>
                    }
                    if ($subtotalno > 0) {
                        //Base No Objeto 
                        $xml .= '<totalImpuesto>'; //Aperturo <totalImpuesto>
                            $xml .= '<codigo>2</codigo>';
                            $xml .= '<codigoPorcentaje>6</codigoPorcentaje>';
                            $xml .= '<baseImponible>'.round($subtotalno, 2).'</baseImponible>';
                            $xml .= '<tarifa>0.00</tarifa>';
                            $xml .= '<valor>'.$ivano.'</valor>';
                        $xml .= '</totalImpuesto>'; //Cierro <totalImpuesto>
                    }
                    if ($subtotalex > 0) {
                        //Base Excento
                        $xml .= '<totalImpuesto>'; //Aperturo <totalImpuesto>
                            $xml .= '<codigo>2</codigo>';
                            $xml .= '<codigoPorcentaje>7</codigoPorcentaje>';
                            $xml .= '<baseImponible>'.round($subtotalex, 2).'</baseImponible>';
                            $xml .= '<tarifa>0.00</tarifa>';
                            $xml .= '<valor>'.$ivaex.'</valor>';
                        $xml .= '</totalImpuesto>'; //Cierro <totalImpuesto>
                    }
                    if ($subtotaldi > 0) {
                        //Base Diferenciada
                        $xml .= '<totalImpuesto>'; //Aperturo <totalImpuesto>
                            $xml .= '<codigo>2</codigo>';
                            $xml .= '<codigoPorcentaje>8</codigoPorcentaje>';
                            $xml .= '<baseImponible>'.round($subtotaldi, 2).'</baseImponible>';
                            $xml .= '<tarifa>8.00</tarifa>';
                            $xml .= '<valor>'.$ivadi.'</valor>';
                        $xml .= '</totalImpuesto>'; //Cierro <totalImpuesto>
                    }
                $xml .= '</totalConImpuestos>'; //Cierro <totalConImpuestos>
                $xml .= '<importeTotal>'.round($documento->total, 2).'</importeTotal>';
                $xml .= '<moneda>DOLAR</moneda>';
                $xml .= '<pagos>'; //Aperturo <pagos>
                    $xml .= '<pago>'; //Aperturo <pago>
                        $xml .= '<formaPago>20</formaPago>';
                        $xml .= '<total>'.round($documento->total, 2).'</total>';
                        $xml .= '<plazo>'.$documento->diascredito.'</plazo>';
                        $xml .= '<unidadTiempo>dias</unidadTiempo>';
                    $xml .= '</pago>'; //Cierro <pago>
                $xml .= '</pagos>'; //Cierro <pagos>
            $xml .= '</infoLiquidacionCompra>'; //Cierro <infoLiquidacionCompra>
            //Contateno el detalle de la Liquidacion de Compra
            $xml .= $detalles;
            //Concateno la informacion adicional
            $xml .= '<infoAdicional>'; //Aperturo <infoAdicional>
                $xml .= '<campoAdicional nombre="Email">'.$documento->email.'</campoAdicional>';
                if (strlen($documento->observaciones) > 0) {
                    $xml .= '<campoAdicional nombre="Observaciones">'.$documento->observaciones.'</campoAdicional>';
                }
            $xml .= '</infoAdicional>'; //Cierro <infoAdicional>
        $xml .= '</liquidacionCompra>'; //Cierro el tag liquidacionCompra
        $xml = limpiarCaracteres($xml);
        $xml = str_replace("REGIMEN RIMPE", "RÉGIMEN RIMPE", $xml);
        return $xml;   
    }

    public function xml_retencion_compra($documento, $empresa)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>';
        $xml .= '<comprobanteRetencion id="comprobante" version="2.0.0">'; //Aperturo el tag comprobanteRetencion
            $xml .= '<infoTributaria>'; //Aperturo <infoTributaria>
                $xml .= '<ambiente>'.substr($documento->nro_autorizacion_ret, 23, 1).'</ambiente>';
                $xml .= '<tipoEmision>1</tipoEmision>';
                $xml .= '<razonSocial>'.$empresa->razonsocial.'</razonSocial>';
                $xml .= '<nombreComercial>'.$empresa->nombrecomercial.'</nombreComercial>';
                $xml .= '<ruc>'.$empresa->ruc.'</ruc>';
                $xml .= '<claveAcceso>'.$documento->nro_autorizacion_ret.'</claveAcceso>';
                $xml .= '<codDoc>'.$documento->coddocumento_ret.'</codDoc>';
                //Separo el numero de Documento
                $doc = explode("-", $documento->numero_retencion);
                $xml .= '<estab>'.$doc[0].'</estab>';
                $xml .= '<ptoEmi>'.$doc[1].'</ptoEmi>';
                $xml .= '<secuencial>'.$doc[2].'</secuencial>';
                $xml .= '<dirMatriz>'.substr($empresa->direccion, 0, 300).'</dirMatriz>';
                if ($documento->agretencion_empresa) {
                    $xml .= '<agenteRetencion>1</agenteRetencion>';
                }
                if ($documento->regimen_empresa == 'RE') {
                    $xml .= '<contribuyenteRimpe>CONTRIBUYENTE RÉGIMEN RIMPE</contribuyenteRimpe>';
                } else if ($documento->regimen_empresa == 'RP') {
                    $xml .= '<contribuyenteRimpe>CONTRIBUYENTE NEGOCIO POPULAR - RÉGIMEN RIMPE</contribuyenteRimpe>';
                }
            $xml .= '</infoTributaria>'; //Cierro <infoTributaria>
            $xml .= '<infoCompRetencion>'; //Aperturo <infoCompRetencion>
                $xml .= '<fechaEmision>'.date('d/m/Y', strtotime($documento->fec_registro)).'</fechaEmision>';
                $xml .= '<dirEstablecimiento>'.substr($documento->get_establecimiento()->direccion, 0, 300).'</dirEstablecimiento>';
                if ($documento->regimen_empresa == 'CE') {
                    $xml .= '<contribuyenteEspecial>0000000000001</contribuyenteEspecial>';
                }
                if ($documento->obligado_empresa) {
                    $xml .= '<obligadoContabilidad>SI</obligadoContabilidad>';
                } else {
                    $xml .= '<obligadoContabilidad>NO</obligadoContabilidad>';
                }
                $idexterior = false;
                switch ($documento->tipoid) {
                    case 'C':
                        $xml .= '<tipoIdentificacionSujetoRetenido>05</tipoIdentificacionSujetoRetenido>';
                        break;
                    case 'R':
                        $xml .= '<tipoIdentificacionSujetoRetenido>04</tipoIdentificacionSujetoRetenido>';
                        break;
                    case 'P':
                        $idexterior = true;
                        $xml .= '<tipoIdentificacionSujetoRetenido>06</tipoIdentificacionSujetoRetenido>';
                        break;
                    case 'F':
                        $xml .= '<tipoIdentificacionSujetoRetenido>07</tipoIdentificacionSujetoRetenido>';
                        break;
                    default:
                        break;
                }
                if ($idexterior) {
                    $xml .= '<tipoSujetoRetenido>01</tipoSujetoRetenido>';
                }
                $xml .= '<parteRel>NO</parteRel>';
                $xml .= '<razonSocialSujetoRetenido>'.$documento->razonsocial.'</razonSocialSujetoRetenido>';
                $xml .= '<identificacionSujetoRetenido>'.$documento->identificacion.'</identificacionSujetoRetenido>';
                $xml .= '<periodoFiscal>'.date('m/Y', strtotime($documento->fec_registro)).'</periodoFiscal>';
            $xml .= '</infoCompRetencion>'; //Cierro <infoCompRetencion>
            $xml .= '<docsSustento>'; //Aperturo <docsSustento>
                $xml .= '<docSustento>'; //Aperturo <docSustento>
                    $xml .= '<codSustento>'.$documento->get_codsustento().'</codSustento>';
                    $xml .= '<codDocSustento>'.$documento->coddocumento.'</codDocSustento>';
                    $xml .= '<numDocSustento>'.str_replace("-", "", $documento->numero_documento).'</numDocSustento>';
                    $xml .= '<fechaEmisionDocSustento>'.date('d/m/Y', strtotime($documento->fec_emision)).'</fechaEmisionDocSustento>';
                    $xml .= '<fechaRegistroContable>'.date('d/m/Y', strtotime($documento->fec_registro)).'</fechaRegistroContable>';
                    $xml .= '<numAutDocSustento>'.$documento->nro_autorizacion.'</numAutDocSustento>';
                    $xml .= '<pagoLocExt>01</pagoLocExt>';
                    $xml .= '<totalSinImpuestos>'.round($documento->base_noi + $documento->base_0 + $documento->base_gra + $documento->base_exc, 2).'</totalSinImpuestos>';
                    $xml .= '<importeTotal>'.round($documento->total, 2).'</importeTotal>';
                    $xml .= '<impuestosDocSustento>'; //Aperturo <impuestosDocSustento>
                        //Bases imponibles
                        $subtotal00 = 0;
                        $subtotal12 = 0;
                        $subtotal14 = 0;
                        $subtotalno = 0;
                        $subtotalex = 0;
                        $subtotaldi = 0;
                        //Ivas
                        $iva00 = 0;
                        $iva12 = 0;
                        $iva14 = 0;
                        $ivano = 0;
                        $ivaex = 0;
                        $ivadi = 0;
                        foreach ($documento->getlineas() as $key => $linea) {
                            switch ($linea->get_impuesto()) {
                                case 'IVANO':
                                    $subtotalno += $linea->pvptotal;
                                    $ivano += $linea->valoriva;
                                    break;
                                case 'IVA0':
                                    $subtotal00 += $linea->pvptotal;
                                    $iva00 += $linea->valoriva;
                                    break;
                                case 'IVAEX':
                                    $subtotalex += $linea->pvptotal;
                                    $ivaex += $linea->valoriva;
                                    break;
                                case 'IVA12':
                                    $subtotal12 += $linea->pvptotal;
                                    $iva12 += $linea->valoriva;
                                    break;
                                case 'IVA14':
                                    $subtotal14 += $linea->pvptotal;
                                    $iva14 += $linea->valoriva;
                                    break;
                                default:
                                    $subtotaldi += $linea->pvptotal;
                                    $ivadi += $linea->valoriva;
                                    break;
                            }
                        }

                        //Base 0%
                        $xml .= '<impuestoDocSustento>'; //Aperturo <impuestoDocSustento>
                            $xml .= '<codImpuestoDocSustento>2</codImpuestoDocSustento>';
                            $xml .= '<codigoPorcentaje>0</codigoPorcentaje>';
                            $xml .= '<baseImponible>'.round($subtotal00, 2).'</baseImponible>';
                            $xml .= '<tarifa>0.00</tarifa>';
                            $xml .= '<valorImpuesto>'.round($iva00, 2).'</valorImpuesto>';
                        $xml .= '</impuestoDocSustento>'; //Cierro <impuestoDocSustento>
                        //Base 12
                        $xml .= '<impuestoDocSustento>'; //Aperturo <impuestoDocSustento>
                            $xml .= '<codImpuestoDocSustento>2</codImpuestoDocSustento>';
                            $xml .= '<codigoPorcentaje>2</codigoPorcentaje>';
                            $xml .= '<baseImponible>'.round($subtotal12, 2).'</baseImponible>';
                            $xml .= '<tarifa>12.00</tarifa>';
                            $xml .= '<valorImpuesto>'.round($iva12, 2).'</valorImpuesto>';
                        $xml .= '</impuestoDocSustento>'; //Cierro <impuestoDocSustento>
                        if ($subtotal14 > 0) {
                            //Base 14
                            $xml .= '<impuestoDocSustento>'; //Aperturo <impuestoDocSustento>
                                $xml .= '<codImpuestoDocSustento>2</codImpuestoDocSustento>';
                                $xml .= '<codigoPorcentaje>3</codigoPorcentaje>';
                                $xml .= '<baseImponible>'.round($subtotal14, 2).'</baseImponible>';
                                $xml .= '<tarifa>14.00</tarifa>';
                                $xml .= '<valorImpuesto>'.round($iva14, 2).'</valorImpuesto>';
                            $xml .= '</impuestoDocSustento>'; //Cierro <impuestoDocSustento>
                        }
                        if ($subtotalno > 0) {
                            //Base No Objeto 
                            $xml .= '<impuestoDocSustento>'; //Aperturo <impuestoDocSustento>
                                $xml .= '<codImpuestoDocSustento>2</codImpuestoDocSustento>';
                                $xml .= '<codigoPorcentaje>6</codigoPorcentaje>';
                                $xml .= '<baseImponible>'.round($subtotalno, 2).'</baseImponible>';
                                $xml .= '<tarifa>0.00</tarifa>';
                                $xml .= '<valorImpuesto>'.round($ivano, 2).'</valorImpuesto>';
                            $xml .= '</impuestoDocSustento>'; //Cierro <impuestoDocSustento>
                        }
                        if ($subtotalex > 0) {
                            //Base Excento
                            $xml .= '<impuestoDocSustento>'; //Aperturo <impuestoDocSustento>
                                $xml .= '<codImpuestoDocSustento>2</codImpuestoDocSustento>';
                                $xml .= '<codigoPorcentaje>7</codigoPorcentaje>';
                                $xml .= '<baseImponible>'.round($subtotalex, 2).'</baseImponible>';
                                $xml .= '<tarifa>0.00</tarifa>';
                                $xml .= '<valorImpuesto>'.round($ivaex, 2).'</valorImpuesto>';
                            $xml .= '</impuestoDocSustento>'; //Cierro <impuestoDocSustento>
                        }
                        if ($subtotaldi > 0) {
                            //Base Diferenciada
                            $xml .= '<impuestoDocSustento>'; //Aperturo <impuestoDocSustento>
                                $xml .= '<codImpuestoDocSustento>2</codImpuestoDocSustento>';
                                $xml .= '<codigoPorcentaje>8</codigoPorcentaje>';
                                $xml .= '<baseImponible>'.round($subtotaldi, 2).'</baseImponible>';
                                $xml .= '<tarifa>8.00</tarifa>';
                                $xml .= '<valorImpuesto>'.round($ivadi, 2).'</valorImpuesto>';
                            $xml .= '</impuestoDocSustento>'; //Cierro <impuestoDocSustento>
                        }
                    $xml .= '</impuestosDocSustento>'; //Cierro </impuestosDocSustento>
                    //Para el total con Impuestos recorro las lineas del documento
                    $xml .= '<retenciones>'; //Aperturo <retenciones>
                        foreach ($documento->get_retencion() as $key => $ret) {
                            $xml .= '<retencion>'; //Aperturo <retencion>
                                if ($ret['especie'] == 'renta') {
                                    $xml .= '<codigo>1</codigo>';
                                } else {
                                    $xml .= '<codigo>2</codigo>';
                                }
                                $xml .= '<codigoRetencion>'.$ret['codigo'].'</codigoRetencion>';
                                $xml .= '<baseImponible>'.$ret['baseimp'].'</baseImponible>';
                                $xml .= '<porcentajeRetener>'.$ret['porcentaje'].'</porcentajeRetener>';
                                $xml .= '<valorRetenido>'.$ret['valor'].'</valorRetenido>';
                            $xml .= '</retencion>'; //Cierro </retencion> 
                        }
                    $xml .= '</retenciones>'; //Cierro </retenciones>
                    $xml .= '<pagos>'; //Aperturo <pagos>
                        $xml .= '<pago>'; //Aperturo <pago>
                            $xml .= '<formaPago>20</formaPago>';
                            $xml .= '<total>'.round($documento->total, 2).'</total>';
                        $xml .= '</pago>'; //Cierro <pago>
                    $xml .= '</pagos>'; //Cierro <pagos>
                $xml .= '</docSustento>'; //Cierro <docSustento>
            $xml .= '</docsSustento>'; //Cierro <docsSustento>
            //Concateno la informacion adicional
            $xml .= '<infoAdicional>'; //Aperturo <infoAdicional>
                $xml .= '<campoAdicional nombre="Email">'.$documento->email.'</campoAdicional>';
                if (strlen($documento->observaciones) > 0) {
                    $xml .= '<campoAdicional nombre="Observaciones">'.$documento->observaciones.'</campoAdicional>';
                }
            $xml .= '</infoAdicional>'; //Cierro <infoAdicional>
        $xml .= '</comprobanteRetencion>'; //Cierro el tag comprobanteRetencion
        $xml = limpiarCaracteres($xml);
        $xml = str_replace("REGIMEN RIMPE", "RÉGIMEN RIMPE", $xml);
        return $xml;
    }

    public function xml_guia_remision($documento, $empresa)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<guiaRemision id="comprobante" version="1.1.0">'; //Aperturo el tag guiaRemision
            $xml .= '<infoTributaria>'; //Aperturo <infoTributaria>
                $xml .= '<ambiente>'.substr($documento->nro_autorizacion, 23, 1).'</ambiente>';
                $xml .= '<tipoEmision>1</tipoEmision>';
                $xml .= '<razonSocial>'.$empresa->razonsocial.'</razonSocial>';
                $xml .= '<nombreComercial>'.$empresa->nombrecomercial.'</nombreComercial>';
                $xml .= '<ruc>'.$empresa->ruc.'</ruc>';
                $xml .= '<claveAcceso>'.$documento->nro_autorizacion.'</claveAcceso>';
                $xml .= '<codDoc>'.$documento->coddocumento.'</codDoc>';
                //Separo el numero de Documento
                $doc = explode("-", $documento->numero_documento);
                $xml .= '<estab>'.$doc[0].'</estab>';
                $xml .= '<ptoEmi>'.$doc[1].'</ptoEmi>';
                $xml .= '<secuencial>'.$doc[2].'</secuencial>';
                $xml .= '<dirMatriz>'.substr($empresa->direccion, 0, 300).'</dirMatriz>';
                if ($documento->agretencion_empresa) {
                    $xml .= '<agenteRetencion>1</agenteRetencion>';
                }
                if ($documento->regimen_empresa == 'RE') {
                    $xml .= '<contribuyenteRimpe>CONTRIBUYENTE RÉGIMEN RIMPE</contribuyenteRimpe>';
                } else if ($documento->regimen_empresa == 'RP') {
                    $xml .= '<contribuyenteRimpe>CONTRIBUYENTE NEGOCIO POPULAR - RÉGIMEN RIMPE</contribuyenteRimpe>';
                }
            $xml .= '</infoTributaria>'; //Cierro <infoTributaria>
            $xml .= '<infoGuiaRemision>'; //Aperturo <infoGuiaRemision>
                $xml .= '<dirEstablecimiento>'.substr($documento->get_establecimiento()->direccion, 0, 300).'</dirEstablecimiento>';
                $xml .= '<dirPartida>'.substr($documento->dirpartida, 0, 300).'</dirPartida>';
                $xml .= '<razonSocialTransportista>'.$documento->razonsocial_trans.'</razonSocialTransportista>';
                switch ($documento->tipoid_trans) {
                    case 'C':
                        $xml .= '<tipoIdentificacionTransportista>05</tipoIdentificacionTransportista>';
                        break;
                    case 'R':
                        $xml .= '<tipoIdentificacionTransportista>04</tipoIdentificacionTransportista>';
                        break;
                    case 'P':
                        $xml .= '<tipoIdentificacionTransportista>06</tipoIdentificacionTransportista>';
                        break;
                    case 'F':
                        $xml .= '<tipoIdentificacionTransportista>07</tipoIdentificacionTransportista>';
                        break;
                    default:
                        break;
                }
                $xml .= '<rucTransportista>'.$documento->identificacion_trans.'</rucTransportista>';
                if ($documento->obligado_empresa) {
                    $xml .= '<obligadoContabilidad>SI</obligadoContabilidad>';
                } else {
                    $xml .= '<obligadoContabilidad>NO</obligadoContabilidad>';
                }
                if ($documento->regimen_empresa == 'CE') {
                    $xml .= '<contribuyenteEspecial>0000000000001</contribuyenteEspecial>';
                }
                $xml .= '<fechaIniTransporte>'.date('d/m/Y', strtotime($documento->fec_emision)).'</fechaIniTransporte>';
                $xml .= '<fechaFinTransporte>'.date('d/m/Y', strtotime($documento->fec_finalizacion)).'</fechaFinTransporte>';
                $xml .= '<placa>'.$documento->placa.'</placa>';
            $xml .= '</infoGuiaRemision>'; //Cierro <infoGuiaRemision>
            $xml .= '<destinatarios>'; //Aperturo <destinatarios>
                $xml .= '<destinatario>'; //Aperturo <destinatario>
                    $xml .= '<identificacionDestinatario>'.$documento->identificacion.'</identificacionDestinatario>';
                    $xml .= '<razonSocialDestinatario>'.$documento->razonsocial.'</razonSocialDestinatario>';
                    $xml .= '<dirDestinatario>'.$documento->direccion.'</dirDestinatario>';
                    $xml .= '<motivoTraslado>'.$documento->motivo.'</motivoTraslado>';
                    if ($documento->codestablecimiento) {
                        $xml .= '<codEstabDestino>'.$documento->codestablecimiento.'</codEstabDestino>';
                    }
                    if ($documento->ruta) {
                        $xml .= '<ruta>'.$documento->ruta.'</ruta>';
                    }
                    if ($documento->idfactura_mod) {
                        $xml .= '<codDocSustento>'.$documento->coddocumento_mod.'</codDocSustento>';
                        $xml .= '<numDocSustento>'.$documento->numero_documento_mod.'</numDocSustento>';
                        $xml .= '<numAutDocSustento>'.$documento->nro_autorizacion_mod.'</numAutDocSustento>';
                        $xml .= '<fechaEmisionDocSustento>'.date('d/m/Y', strtotime($documento->fec_emision_mod)).'</fechaEmisionDocSustento>';
                    }
                    $xml .= '<detalles>'; //Aperturo <detalles>
                    foreach ($documento->getlineas() as $key => $linea) {
                        $xml .= '<detalle>'; //Aperturo <detalle>
                            $xml .= '<codigoInterno>'.substr($linea->codprincipal, 0, 25).'</codigoInterno>';
                            $xml .= '<descripcion>'.substr($linea->descripcion, 0, 300).'</descripcion>';
                            $xml .= '<cantidad>'.round($linea->cantidad, 6).'</cantidad>';
                        $xml .= '</detalle>'; //Cierro </detalle>
                    }
                    $xml .= '</detalles>'; //Cierro </detalles>
                $xml .= '</destinatario>'; //Cierro <destinatario>
            $xml .= '</destinatarios>'; //Cierro <destinatarios>
            //Concateno la informacion adicional
            $xml .= '<infoAdicional>'; //Aperturo <infoAdicional>
                $xml .= '<campoAdicional nombre="Email">'.$documento->email.'</campoAdicional>';
                if (strlen($documento->observaciones) > 0) {
                    $xml .= '<campoAdicional nombre="Observaciones">'.$documento->observaciones.'</campoAdicional>';
                }
            $xml .= '</infoAdicional>'; //Cierro <infoAdicional>
        $xml .= '</guiaRemision>'; //Cierro el tag guiaRemision

        $xml = limpiarCaracteres($xml);
        $xml = str_replace("REGIMEN RIMPE", "RÉGIMEN RIMPE", $xml);
        return $xml;   
    }
}
