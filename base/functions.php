<?php
/**
 * Redondeo bancario
 * @staticvar real $dFuzz
 * @param float $dVal
 * @param integer $iDec
 * @return float
 */
function bround($dVal, $iDec = 2)
{
    // banker's style rounding or round-half-even
    // (round down when even number is left of 5, otherwise round up)
    // $dVal is value to round
    // $iDec specifies number of decimal places to retain
    static $dFuzz = 0.00001; // to deal with floating-point precision loss

    $iSign = ($dVal != 0.0) ? intval($dVal / abs($dVal)) : 1;
    $dVal  = abs($dVal);

    // get decimal digit in question and amount to right of it as a fraction
    $dWorking      = $dVal * pow(10.0, $iDec + 1) - floor($dVal * pow(10.0, $iDec)) * 10.0;
    $iEvenOddDigit = floor($dVal * pow(10.0, $iDec)) - floor($dVal * pow(10.0, $iDec - 1)) * 10.0;

    if (abs($dWorking - 5.0) < $dFuzz) {
        $iRoundup = ($iEvenOddDigit & 1) ? 1 : 0;
    } else {
        $iRoundup = ($dWorking > 5.0) ? 1 : 0;
    }

    return $iSign * ((floor($dVal * pow(10.0, $iDec)) + $iRoundup) / pow(10.0, $iDec));
}

function complemento_exists($name_complemento = '')
{
    $exists           = false;
    $name_complemento = (string) $name_complemento;
    if (!empty($name_complemento) || !is_null($name_complemento)) {
        if (is_array($GLOBALS['plugins'])) {
            if (count($GLOBALS['plugins']) > 0) {
                foreach ($GLOBALS['plugins'] as $key => $value) {
                    if (in_array($name_complemento, $GLOBALS['plugins'])) {
                        $exists = true;
                        break;
                    }
                }
            }
        }
    }

    return $exists;
}

function show_fecha($fecha, $format = 'd-m-Y')
{
    return date($format, strtotime($fecha));
}

function show_numero($num = 0, $decimales = JG_NF0, $js = false)
{
    if ($js) {
        return number_format($num, $decimales, '.', ' ');
    }

    return number_format($num, $decimales, ".", " ");
}

function show_precio($num = 0, $decimales = JG_NF0, $js = false)
{
    if ($js) {
        return number_format($num, $decimales, '.', ' ') . " $";
    }

    return number_format($num, $decimales, ".", " ") . " $";
}

function show_numero2($num = 0, $decimales = JG_NF0, $js = false)
{
    if ($js) {
        return number_format($num, $decimales, '.', '');
    }

    return number_format($num, $decimales, ".", "");
}
/**
 * Muestra un mensaje de error en caso de error fatal, aunque php tenga
 * desactivados los errores.
 */
function fatal_handler()
{
    $error = error_get_last();
    if (isset($error) && in_array($error["type"], [1, 64])) {
        echo "<h1>Error sin controlar</h1>"
            . "<ul>"
            . "<li><b>Tipo:</b> " . $error["type"] . "</li>"
            . "<li><b>Archivo:</b> " . $error["file"] . "</li>"
            . "<li><b>Línea:</b> " . $error["line"] . "</li>"
            . "<li><b>Mensaje:</b> " . $error["message"] . "</li>"
            . "</ul>";
    }
}

/**
 * Devuelve la ruta del controlador solicitado.
 * @param string $name
 * @return string
 */
function find_controller($name)
{
    foreach ($GLOBALS['plugins'] as $plugin) {
        if (file_exists(JG_FOLDER . '/plugins/' . $plugin . '/controller/' . $name . '.php')) {
            return 'plugins/' . $plugin . '/controller/' . $name . '.php';
        }
    }

    if (file_exists(JG_FOLDER . '/controller/' . $name . '.php')) {
        return 'controller/' . $name . '.php';
    }

    return 'base/controller.php';
}

/**
 * Función alternativa para cuando el followlocation falla.
 * @param resource $ch
 * @param integer $redirects
 * @param boolean $curlopt_header
 * @return string
 */
function curl_redirect_exec($ch, &$redirects, $curlopt_header = false)
{
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $data      = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($http_code == 301 || $http_code == 302) {
        list($header) = explode("\r\n\r\n", $data, 2);
        $matches      = [];
        preg_match("/(Location:|URI:)[^(\n)]*/", $header, $matches);
        $url        = trim(str_replace($matches[1], "", $matches[0]));
        $url_parsed = parse_url($url);
        if (isset($url_parsed)) {
            curl_setopt($ch, CURLOPT_URL, $url);
            $redirects++;
            return curl_redirect_exec($ch, $redirects, $curlopt_header);
        }
    }

    if ($curlopt_header) {
        curl_close($ch);
        return $data;
    }

    list(, $body) = explode("\r\n\r\n", $data, 2);
    curl_close($ch);
    return $body;
}

/**
 * Descarga el archivo de la url especificada
 * @param string $url
 * @param string $filename
 * @param integer $timeout
 * @return boolean
 */
function file_download($url, $filename, $timeout = 30)
{
    $ok = false;

    try {
        $data = jg_file_get_contents($url, $timeout);
        if ($data && $data != 'ERROR' && file_put_contents($filename, $data) !== false) {
            $ok = true;
        }
    } catch (Exception $e) {
        /// nada
    }

    return $ok;
}

/**
 * Descarga el contenido con curl o jg_file_get_contents.
 * @param string $url
 * @param integer $timeout
 * @return string
 */
function jg_file_get_contents($url, $timeout = 10)
{
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (ini_get('open_basedir') === null) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        }

        /**
         * En algunas configuraciones de php es necesario desactivar estos flags,
         * en otras es necesario activarlos. habrá que buscar una solución mejor.
         */
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        if (defined('JG_PROXY_TYPE')) {
            curl_setopt($ch, CURLOPT_PROXYTYPE, JG_PROXY_TYPE);
            curl_setopt($ch, CURLOPT_PROXY, JG_PROXY_HOST);
            curl_setopt($ch, CURLOPT_PROXYPORT, JG_PROXY_PORT);
        }
        $data = curl_exec($ch);
        $info = curl_getinfo($ch);

        if ($info['http_code'] == 200) {
            curl_close($ch);
            return $data;
        } else if ($info['http_code'] == 301 || $info['http_code'] == 302) {
            $redirs = 0;
            return curl_redirect_exec($ch, $redirs);
        }

        /// guardamos en el log
        if (class_exists('core_log') && $info['http_code'] != 404) {
            $error = curl_error($ch);
            if ($error == '') {
                $error = 'ERROR ' . $info['http_code'];
            }

            $core_log = new core_log();
            $core_log->new_error($error);
            $core_log->save($url . ' - ' . $error);
        }

        curl_close($ch);
        return 'ERROR';
    }

    return jg_file_get_contents($url);
}

/**
 * Devuelve el equivalente a $_POST[$name], pero pudiendo definicar un valor
 * por defecto si no encuentra nada.
 * @param string $name
 * @param mixed $default
 * @return mixed
 */
function filter_input_post($name, $default = false)
{
    return isset($_POST[$name]) ? $_POST[$name] : $default;
}

/**
 * Devuelve el equivalente a $_REQUEST[$name], pero pudiendo definicar un valor
 * por defecto si no encuentra nada.
 * @param string $name
 * @param mixed $default
 * @return mixed
 */
function filter_input_req($name, $default = false)
{
    return isset($_REQUEST[$name]) ? $_REQUEST[$name] : $default;
}

/**
 * Deshace las conversiones realizadas por model::no_html()
 * @param string $txt
 * @return string
 */
function fix_html($txt)
{
    $original = array('&lt;', '&gt;', '&quot;', '&#39;');
    $final    = array('<', '>', "'", "'");
    return trim(str_replace($original, $final, $txt));
}

function limpiarTexto($txt)
{
    $original = array('.', '&', 'ñ', 'Ñ');
    $final    = array('', '', 'n', "N");
    return trim(str_replace($original, $final, $txt));
}

/**
 * Devuelve la IP del usuario.
 * @return string
 */
function get_ip()
{
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    if (isset($_SERVER['REMOTE_ADDR'])) {
        return $_SERVER['REMOTE_ADDR'];
    }

    return '';
}

/**
 * Devuelve el tamaño máximo de archivo que soporta el servidor para las subidas por formulario.
 * @return int
 */
function get_max_file_upload()
{
    $max = intval(ini_get('post_max_size'));
    if (intval(ini_get('upload_max_filesize')) < $max) {
        $max = intval(ini_get('upload_max_filesize'));
    }

    return $max;
}

/**
 * Devuelve el nombre de la clase del objeto, pero sin el namespace.
 * @param mixed $object
 * @return string
 */
function get_class_name($object = null)
{
    $name = get_class($object);
    $pos  = strrpos($name, '\\');
    if ($pos !== false) {
        $name = substr($name, $pos + 1);
    }

    return $name;
}

/**
 * Carga todos los modelos disponibles en los pugins activados y el núcleo.
 */
function require_all_models()
{
    if (!isset($GLOBALS['models'])) {
        $GLOBALS['models'] = [];
    }

    foreach ($GLOBALS['plugins'] as $plugin) {
        if (!file_exists('plugins/' . $plugin . '/model')) {
            continue;
        }

        foreach (scandir('plugins/' . $plugin . '/model') as $file_name) {
            if ($file_name != '.' && $file_name != '..' && substr($file_name, -4) == '.php' && !in_array($file_name, $GLOBALS['models'])) {
                require_once 'plugins/' . $plugin . '/model/' . $file_name;
                $GLOBALS['models'][] = $file_name;
            }
        }
    }

    /// ahora cargamos los del núcleo
    foreach (scandir('model') as $file_name) {
        if ($file_name != '.' && $file_name != '..' && substr($file_name, -4) == '.php' && !in_array($file_name, $GLOBALS['models'])) {
            require_once 'model/' . $file_name;
            $GLOBALS['models'][] = $file_name;
        }
    }
}

/**
 * Función obsoleta para cargar un modelo concreto.
 * @deprecated since version 2017.025
 * @param string $name
 */
function require_model($name)
{
    if (JG_DB_HISTORY) {
        $core_log = new core_log();
        $core_log->new_error("require_model('" . $name . "') es innecesario.");
    }
}

function consultardocsri($nroaut)
{
    $result = array('encontrado' => false, 'msj' => '', 'xml' => '');

    if (strlen($nroaut) == 49 && is_numeric($nroaut)) {
        if (substr($nroaut, 23, 1) == 2) {
            $wsdlUrl = 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl';
            try {
                $cliente      = new SoapClient($wsdlUrl, array('trace' => true, 'cache_wsdl' => WSDL_CACHE_MEMORY));
                $resultadoSri = $cliente->autorizacionComprobante(array('claveAccesoComprobante' => $nroaut));
                if (isset($resultadoSri->RespuestaAutorizacionComprobante->autorizaciones->autorizacion)) {
                    $respuestaWsdl = $resultadoSri->RespuestaAutorizacionComprobante->autorizaciones->autorizacion;
                    if (trim($respuestaWsdl->estado) == 'AUTORIZADO') {
                        $result = array('encontrado' => true, 'msj' => '', 'xml' => simplexml_load_string($respuestaWsdl->comprobante), 'fec_aut' => trim($respuestaWsdl->fechaAutorizacion));
                    } else {
                        $result['msj'] = 'El Documento consultado no se encuentra Autorizado.';
                    }
                } else {
                    $result['msj'] = 'Documento no encontrado en el SRI.';
                }
            } catch (Exception $e) {
                $result['msj'] = 'Problemas de conexión con el SRI, intente nuevamente en unos minutos.';
            }
        } else {
            $result['msj'] = 'El número de autorización consultado se encuentra en ambiente de Pruebas.';
        }
    } else {
        $result['msj'] = 'El número de autorización debe tener 49 números.';
    }

    return $result;
}

function consultarRucSri($ruc)
{
    $result = array('error' => 'F', 'msj' => '', 'nombre' => '', 'razonsocial' => '', 'ruc' => '', 'obligado' => '', 'direccion' => '', 'agenteR' => '');
    $url    = 'http://gscsystemsec-001-site1.htempurl.com/api/facturacion/GetRucSRI?Ruc=' . $ruc;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, false);
    $data = curl_exec($curl);
    curl_close($curl);

    $datos = json_decode($data);
    if (!isset($datos->errors)) {
        if ($datos->error != '') {
            $result['error'] = 'T';
            if(strpos($datos->error, "An existing connection was forcibly closed by the remote host") != false) {
                $result['msj']   = 'SRI Fuera de Servicio, puede realizar el ingreso de manera manual (Complete los datos).';
            } else if (strpos($datos->error, "The operation has timed out") != false) {
                $result['msj']   = 'SRI Fuera de Servicio, puede realizar el ingreso de manera manual (Complete los datos).';
            } else {
                $result['msj']   = $datos->error;
            }
        }
        if ($result['error'] == 'F') {
            if ($datos->contribuyenteFantasma == 'SI') {
                $result['error'] = 'T';
                $result['msj']   = 'El RUC ingresado se encuentra dentro de la lista de RUCS Fantasmas.';
            }
        }

        if ($result['error'] == 'F') {
            if ($datos->estadoContribuyenteRuc == 'SUSPENDIDO') {
                $result['error'] = 'T';
                $result['msj']   = 'El RUC ingresado se encuentra Suspendido.';
            }
        }

        if ($result['error'] == 'F') {
            if ($datos->contribuyenteEspecial == 'SI') {
                $result['regimen'] = 'CE';
            } else if ($datos->regimen == 'RIMPE') {
                if ($datos->categoria == 'EMPRENDEDOR') {
                    $result['regimen'] = 'RE';
                } else {
                    $result['regimen'] = 'RP';
                }
            } else {
                $result['regimen'] = 'GE';
            }
            $result['nombre']      = $datos->razonSocial;
            $result['razonsocial'] = $datos->razonSocial;
            $result['ruc']         = $datos->numeroRuc;
            $result['obligado']    = ucfirst(strtolower($datos->obligadoLlevarContabilidad));
            $result['agenteR']     = ucfirst(strtolower($datos->agenteRetencion));
            foreach ($datos->establecimientos as $key => $dir) {
                if ($dir->tipoEstablecimiento == 'MAT') {
                    $direccion           = explode(" / ", $dir->direccionCompleta);
                    $largo               = count($direccion);
                    $result['direccion'] = trim($direccion[$largo - 2]) . " - " . trim($direccion[$largo - 1]);
                    if ($dir->nombreFantasiaComercial) {
                        $result['nombre'] = $dir->nombreFantasiaComercial;
                    }
                }
            }
        }
    } else {
        $result['error'] = 'T';
        $result['msj']   = 'El RUC ingresado es incorrecto.';
    }

    return $result;
}

function consultarCedula($cedula)
{
    $result = array('error' => 'T', 'msj' => 'Cedula No encontrada', 'nombre' => '', 'razonsocial' => '', 'cedula' => '', 'direccion' => '');
    $url    = 'http://gscsystemsec-001-site1.htempurl.com/api/facturacion/GetCedulaSri?ClienteCiruc=' . $cedula;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, false);
    $data = curl_exec($curl);
    curl_close($curl);

    $datos = json_decode($data);
    if (!isset($datos->errors)) {
        if ($datos->nombreCompleto != '') {
            $result['error']       = 'F';
            $result['msj']         = 'Cedula encontrada';
            $result['nombre']      = $datos->nombreCompleto;
            $result['razonsocial'] = $datos->nombreCompleto;
            $result['cedula']      = $datos->identificacion;
            $result['direccion']   = '';
            $result['obligado']    = 'No';
            $result['agenteR']     = 'No';
        }
    } else {
        $result['error'] = 'T';
        $result['msj']   = 'La Cedula ingresada es incorrecta.';
    }

    return $result;
}

function cargar_firma_elect($archivop12, $ruc, $clave)
{
    $result = array('error' => 'T', 'msj' => 'Error al configurar la Firma Electronica, contacte al administrador del sistema.');
    $url    = 'http://gscsystemsec-001-site1.htempurl.com/api/facturacion/SubirP12';
    $curl   = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    $curl_file = new CURLFile($archivop12, 'file/p12', $ruc);
    $postData  = array(
        'EmpresaRuc'             => $ruc,
        'Dfeubicacionarchivop12' => $curl_file,
        'Dfecontrasena'          => $clave,
    );
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, false);
    $data = curl_exec($curl);
    curl_close($curl);
    $datos = json_decode($data);
    if (isset($datos->errors)) {
    } else {
        if (isset($datos->mensajeRespuesta)) {
            $result = array('error' => 'F', 'msj' => 'Firma Configurada Correctamente, ya puede emitir sus Comprobantes Electrónicos.');
        }
    }

    return $result;
}

function tiposIdentificacion()
{
    $tipos = array('C' => 'Cédula', 'R' => 'RUC', 'P' => 'Pasaporte', 'F' => 'Consumidor Final');
    return $tipos;
}

function regimen()
{
    $regimen = array('GE' => 'Regimen General', 'RP' => 'CONTRIBUYENTE NEGOCIO POPULAR - RÉGIMEN RIMPE', 'RE' => 'CONTRIBUYENTE RÉGIMEN RIMPE', 'CE' => 'Contribuyente Especial', 'RA' => 'Artesano');
    return $regimen;
}

function buscar_proveedores($idempresa = '', $query = '', $tipoid = '', $regimen = '', $offset = 0)
{
    $proveedor = new \proveedores();
    return $proveedor->search_proveedores($idempresa, $query, $tipoid, $regimen, $offset);
}

function buscar_clientes($idempresa = '', $query = '', $tipoid = '', $regimen = '', $offset = 0)
{
    $cliente = new \clientes();
    return $cliente->search_clientes($idempresa, $query, $tipoid, $regimen, $offset);
}

function buscar_articulos($idempresa = '', $query = '', $grupo = '', $marca = '', $tipo = '', $impuesto = '', $establecimiento = '', $sevende = '', $secompra = '', $bloqueado = '', $offset = 0)
{
    $articulo = new \articulos(false, $establecimiento);
    return $articulo->search_articulos($idempresa, $query, $grupo, $marca, $tipo, $impuesto, $sevende, $secompra, $bloqueado, $offset);
}

function buscar_formaspago($query = '', $idempresa = '', $escredito = '', $esventa = '', $escompra = '')
{
    $formapago = new \formaspago();
    return $formapago->search_formas_pago($query, $idempresa, $escredito, $esventa, $escompra);
}

function buscar_tiposretencion($query = '', $idempresa = '')
{
    $tiporetencion = new \tiposretenciones();
    return $tiporetencion->search_retencion($query, $idempresa);
}

function traer_marcas($idempresa)
{
    $marca = new \marcas();
    return $marca->all_by_idempresa($idempresa);
}

function traer_grupos($idempresa)
{
    $grupo = new \grupos();
    return $grupo->all_by_idempresa($idempresa);
}

function listar_impuestos()
{
    $impuesto = new \impuestos();
    return $impuesto->all();
}

function buscar_factura_prov($idempresa, $query, $idproveedor)
{
    $facturasprov = new \facturasprov();
    return $facturasprov->buscar_factura_proveedor($idempresa, $query, $idproveedor);
}

function buscar_factura_cli($idempresa, $query, $idcliente)
{
    $facturascli = new \facturascli();
    return $facturascli->buscar_factura_cliente($idempresa, $query, $idcliente);
}

function generarClaveAcceso($fecha, $tipoComp, $rucEmp, $ambiente, $numDoc, $cadN = '54234279', $emision = '1')
{
    $clave       = false;
    $claveAcceso = $fecha . $tipoComp . $rucEmp . $ambiente . $numDoc . $cadN . $emision;

    $digits = str_replace(array('.', ','), array('' . ''), strrev($claveAcceso));
    if (!ctype_digit($digits)) {
        return false;
    }

    $sum    = 0;
    $factor = 2;

    for ($i = 0; $i < strlen($digits); $i++) {
        $sum += substr($digits, $i, 1) * $factor;
        if ($factor == 7) {
            $factor = 2;
        } else {
            $factor++;
        }
    }
    $digitoV = 11 - ($sum % 11);
    $dv      = $digitoV;
    if ($digitoV == 11) {
        $dv = 0;
    } else if ($digitoV == 10) {
        $dv = 1;
    }

    $clave = $claveAcceso . $dv;

    return $clave;
}

function styleCabeceraReporte()
{
    $styleArray = array(
        'font'      => array(
            'name'  => 'Times New Roman',
            'size'  => 16,
            'bold'  => true,
            'color' => array(
                'rgb' => '000000',
            ),
        ),
        'fill'      => array(
            'type'       => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
            'rotation'   => 90,
            'startcolor' => array(
                'rgb' => '008080',
            ),
            'endcolor'   => array(
                'argb' => 'FFFFFFFF',
            ),
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            'wrap'       => true,
        ),
    );

    return $styleArray;
}

function styleNombreColumnas()
{

    $styleArray = array(
        'font'      => array(
            'name'  => 'Times New Roman',
            'size'  => 14,
            'bold'  => true,
            'color' => array(
                'rgb' => '000000',
            ),
        ),
        'fill'      => array(
            'type'       => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
            'rotation'   => 90,
            'startcolor' => array(
                'rgb' => '800080',
            ),
            'endcolor'   => array(
                'argb' => 'FFFFFFFF',
            ),
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            'wrap'       => true,
        ),
    );

    return $styleArray;
}

function styleNegrita()
{
    $styleArray = array(
        'font'      => array(
            'name' => 'Times New Roman',
            'bold' => true,
            'size' => 12,
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
        ),
    );

    return $styleArray;
}

function firmarXmlDocumento($xml, $claveAcceso, $rucEmpresa)
{
    $result   = array('error' => 'T', 'msj' => 'Error al firmar Documento, verifique los datos y vuelva a intentarlo.');
    $url      = 'http://gscsystemsec-001-site1.htempurl.com/api/facturacion/FirmaXml';
    $curl     = curl_init($url);
    $postData = array(
        'rucEmpresa'  => $rucEmpresa,
        'xmlGenerado' => $xml,
        'claveAcceso' => $claveAcceso,
    );
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $data = curl_exec($curl);
    curl_close($curl);
    $datos = json_decode($data);
    if (isset($datos->xmlFirmado)) {
        $result = array('error' => 'F', 'msj' => '', 'xmlFirmado' => $datos->xmlFirmado);
    }
    return $result;
}

function autorizacionDocumento($claveAcceso, $rucEmpresa, $ambiente = 1)
{
    $result = array('error' => 'T', 'msj' => 'Error al Autorizar Documento, verifique los datos y vuelva a intentarlo.');
    if ($ambiente == 1) {
        //Ambiente de Pruebas
        $url    = 'http://gscsystemsec-001-site1.htempurl.com/api/facturacion/RecepcionPrueba?ClaveAcceso=' . $claveAcceso;
        $urlAut = 'http://gscsystemsec-001-site1.htempurl.com/api/facturacion/AutorizacionPrueba?ClaveAcceso=' . $claveAcceso . '&RucEmpresa=' . $rucEmpresa;
    } else if ($ambiente == 2) {
        //Ambiente de Produccion
        $url    = 'http://gscsystemsec-001-site1.htempurl.com/api/facturacion/Recepcion?ClaveAcceso=' . $claveAcceso;
        $urlAut = 'http://gscsystemsec-001-site1.htempurl.com/api/facturacion/Autorizacion?ClaveAcceso=' . $claveAcceso . '&RucEmpresa=' . $rucEmpresa;
    }
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, false);
    $data = curl_exec($curl);
    curl_close($curl);

    $datos = json_decode($data);
    if (isset($datos->respuestaRecepcion)) {
        if ($datos->respuestaRecepcion == 'RECIBIDA') {
            $curl2 = curl_init();
            curl_setopt($curl2, CURLOPT_URL, $urlAut);
            curl_setopt($curl2, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl2, CURLOPT_HEADER, false);
            $data2 = curl_exec($curl2);
            curl_close($curl2);
            $datos2 = json_decode($data2);
            if (isset($datos2->respuestaAutorizacion)) {
                if ($datos2->respuestaAutorizacion == 'AUTORIZADO') {
                    $result = array('error' => 'F', 'msj' => '', 'xmlAutorizado' => $datos2->xmlAutorizado);
                } else {
                    $result = array('error' => 'T', 'msj' => 'Error al Autorizar Documento, Error: ' . $datos2->respuestaAutorizacion);
                }
            }
        } else if ($datos->respuestaRecepcion == 'DEVUELTA / Mensaje: CLAVE ACCESO REGISTRADA / InformacionAdicional: ') {
            $curl2 = curl_init();
            curl_setopt($curl2, CURLOPT_URL, $urlAut);
            curl_setopt($curl2, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl2, CURLOPT_HEADER, false);
            $data2 = curl_exec($curl2);
            curl_close($curl2);
            $datos2 = json_decode($data2);
            if (isset($datos2->respuestaAutorizacion)) {
                if ($datos2->respuestaAutorizacion == 'AUTORIZADO') {
                    $result = array('error' => 'F', 'msj' => '', 'xmlAutorizado' => $datos2->xmlAutorizado);
                } else {
                    $result = array('error' => 'T', 'msj' => 'Error al Autorizar Documento, Error: ' . $datos2->respuestaAutorizacion);
                }
            }
        } else {
            $result = array('error' => 'T', 'msj' => 'Error al Autorizar Documento, Error: ' . $datos->respuestaRecepcion);
        }
    }
    return $result;
}

function limpiarCaracteres($xml)
{
    $buscar    = array("&", 'Ñ', 'ñ', 'á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú');
    $remplazar = array(" ", 'N', 'n', 'a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U');
    //remplazo caracteres especiales
    $xml = str_replace($buscar, $remplazar, $xml);
    //limpio saltos de linea
    $especiales = '[\n|\r|\n\r]';

    return preg_replace($especiales, '', $xml);
}

function barcode($filepath = "", $text = "0", $size = "20", $orientation = "horizontal", $code_type = "code128", $print = false, $SizeFactor = 1)
{
    $code_string = "";
    // Translate the $text into barcode the correct $code_type
    if (in_array(strtolower($code_type), array("code128", "code128b"))) {
        $chksum = 104;
        // Must not change order of array elements as the checksum depends on the array's key to validate final code
        $code_array  = array(" " => "212222", "!" => "222122", "\"" => "222221", "#" => "121223", "$" => "121322", "%" => "131222", "&" => "122213", "'" => "122312", "(" => "132212", ")" => "221213", "*" => "221312", "+" => "231212", "," => "112232", "-" => "122132", "." => "122231", "/" => "113222", "0" => "123122", "1" => "123221", "2" => "223211", "3" => "221132", "4" => "221231", "5" => "213212", "6" => "223112", "7" => "312131", "8" => "311222", "9" => "321122", ":" => "321221", ";" => "312212", "<" => "322112", "=" => "322211", ">" => "212123", "?" => "212321", "@" => "232121", "A" => "111323", "B" => "131123", "C" => "131321", "D" => "112313", "E" => "132113", "F" => "132311", "G" => "211313", "H" => "231113", "I" => "231311", "J" => "112133", "K" => "112331", "L" => "132131", "M" => "113123", "N" => "113321", "O" => "133121", "P" => "313121", "Q" => "211331", "R" => "231131", "S" => "213113", "T" => "213311", "U" => "213131", "V" => "311123", "W" => "311321", "X" => "331121", "Y" => "312113", "Z" => "312311", "[" => "332111", "\\" => "314111", "]" => "221411", "^" => "431111", "_" => "111224", "\`" => "111422", "a" => "121124", "b" => "121421", "c" => "141122", "d" => "141221", "e" => "112214", "f" => "112412", "g" => "122114", "h" => "122411", "i" => "142112", "j" => "142211", "k" => "241211", "l" => "221114", "m" => "413111", "n" => "241112", "o" => "134111", "p" => "111242", "q" => "121142", "r" => "121241", "s" => "114212", "t" => "124112", "u" => "124211", "v" => "411212", "w" => "421112", "x" => "421211", "y" => "212141", "z" => "214121", "{" => "412121", "|" => "111143", "}" => "111341", "~" => "131141", "DEL" => "114113", "FNC 3" => "114311", "FNC 2" => "411113", "SHIFT" => "411311", "CODE C" => "113141", "FNC 4" => "114131", "CODE A" => "311141", "FNC 1" => "411131", "Start A" => "211412", "Start B" => "211214", "Start C" => "211232", "Stop" => "2331112");
        $code_keys   = array_keys($code_array);
        $code_values = array_flip($code_keys);
        for ($X = 1; $X <= strlen($text); $X++) {
            $activeKey = substr($text, ($X - 1), 1);
            $code_string .= $code_array[$activeKey];
            $chksum = ($chksum + ($code_values[$activeKey] * $X));
        }
        $code_string .= $code_array[$code_keys[($chksum - (intval($chksum / 103) * 103))]];

        $code_string = "211214" . $code_string . "2331112";
    } elseif (strtolower($code_type) == "code128a") {
        $chksum = 103;
        $text   = strtoupper($text); // Code 128A doesn't support lower case
        // Must not change order of array elements as the checksum depends on the array's key to validate final code
        $code_array  = array(" " => "212222", "!" => "222122", "\"" => "222221", "#" => "121223", "$" => "121322", "%" => "131222", "&" => "122213", "'" => "122312", "(" => "132212", ")" => "221213", "*" => "221312", "+" => "231212", "," => "112232", "-" => "122132", "." => "122231", "/" => "113222", "0" => "123122", "1" => "123221", "2" => "223211", "3" => "221132", "4" => "221231", "5" => "213212", "6" => "223112", "7" => "312131", "8" => "311222", "9" => "321122", ":" => "321221", ";" => "312212", "<" => "322112", "=" => "322211", ">" => "212123", "?" => "212321", "@" => "232121", "A" => "111323", "B" => "131123", "C" => "131321", "D" => "112313", "E" => "132113", "F" => "132311", "G" => "211313", "H" => "231113", "I" => "231311", "J" => "112133", "K" => "112331", "L" => "132131", "M" => "113123", "N" => "113321", "O" => "133121", "P" => "313121", "Q" => "211331", "R" => "231131", "S" => "213113", "T" => "213311", "U" => "213131", "V" => "311123", "W" => "311321", "X" => "331121", "Y" => "312113", "Z" => "312311", "[" => "332111", "\\" => "314111", "]" => "221411", "^" => "431111", "_" => "111224", "NUL" => "111422", "SOH" => "121124", "STX" => "121421", "ETX" => "141122", "EOT" => "141221", "ENQ" => "112214", "ACK" => "112412", "BEL" => "122114", "BS" => "122411", "HT" => "142112", "LF" => "142211", "VT" => "241211", "FF" => "221114", "CR" => "413111", "SO" => "241112", "SI" => "134111", "DLE" => "111242", "DC1" => "121142", "DC2" => "121241", "DC3" => "114212", "DC4" => "124112", "NAK" => "124211", "SYN" => "411212", "ETB" => "421112", "CAN" => "421211", "EM" => "212141", "SUB" => "214121", "ESC" => "412121", "FS" => "111143", "GS" => "111341", "RS" => "131141", "US" => "114113", "FNC 3" => "114311", "FNC 2" => "411113", "SHIFT" => "411311", "CODE C" => "113141", "CODE B" => "114131", "FNC 4" => "311141", "FNC 1" => "411131", "Start A" => "211412", "Start B" => "211214", "Start C" => "211232", "Stop" => "2331112");
        $code_keys   = array_keys($code_array);
        $code_values = array_flip($code_keys);
        for ($X = 1; $X <= strlen($text); $X++) {
            $activeKey = substr($text, ($X - 1), 1);
            $code_string .= $code_array[$activeKey];
            $chksum = ($chksum + ($code_values[$activeKey] * $X));
        }
        $code_string .= $code_array[$code_keys[($chksum - (intval($chksum / 103) * 103))]];

        $code_string = "211412" . $code_string . "2331112";
    } elseif (strtolower($code_type) == "code39") {
        $code_array = array("0" => "111221211", "1" => "211211112", "2" => "112211112", "3" => "212211111", "4" => "111221112", "5" => "211221111", "6" => "112221111", "7" => "111211212", "8" => "211211211", "9" => "112211211", "A" => "211112112", "B" => "112112112", "C" => "212112111", "D" => "111122112", "E" => "211122111", "F" => "112122111", "G" => "111112212", "H" => "211112211", "I" => "112112211", "J" => "111122211", "K" => "211111122", "L" => "112111122", "M" => "212111121", "N" => "111121122", "O" => "211121121", "P" => "112121121", "Q" => "111111222", "R" => "211111221", "S" => "112111221", "T" => "111121221", "U" => "221111112", "V" => "122111112", "W" => "222111111", "X" => "121121112", "Y" => "221121111", "Z" => "122121111", "-" => "121111212", "." => "221111211", " " => "122111211", "$" => "121212111", "/" => "121211121", "+" => "121112121", "%" => "111212121", "*" => "121121211");

        // Convert to uppercase
        $upper_text = strtoupper($text);

        for ($X = 1; $X <= strlen($upper_text); $X++) {
            $code_string .= $code_array[substr($upper_text, ($X - 1), 1)] . "1";
        }

        $code_string = "1211212111" . $code_string . "121121211";
    } elseif (strtolower($code_type) == "code25") {
        $code_array1 = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "0");
        $code_array2 = array("3-1-1-1-3", "1-3-1-1-3", "3-3-1-1-1", "1-1-3-1-3", "3-1-3-1-1", "1-3-3-1-1", "1-1-1-3-3", "3-1-1-3-1", "1-3-1-3-1", "1-1-3-3-1");

        for ($X = 1; $X <= strlen($text); $X++) {
            for ($Y = 0; $Y < count($code_array1); $Y++) {
                if (substr($text, ($X - 1), 1) == $code_array1[$Y]) {
                    $temp[$X] = $code_array2[$Y];
                }

            }
        }

        for ($X = 1; $X <= strlen($text); $X += 2) {
            if (isset($temp[$X]) && isset($temp[($X + 1)])) {
                $temp1 = explode("-", $temp[$X]);
                $temp2 = explode("-", $temp[($X + 1)]);
                for ($Y = 0; $Y < count($temp1); $Y++) {
                    $code_string .= $temp1[$Y] . $temp2[$Y];
                }

            }
        }

        $code_string = "1111" . $code_string . "311";
    } elseif (strtolower($code_type) == "codabar") {
        $code_array1 = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "0", "-", "$", ":", "/", ".", "+", "A", "B", "C", "D");
        $code_array2 = array("1111221", "1112112", "2211111", "1121121", "2111121", "1211112", "1211211", "1221111", "2112111", "1111122", "1112211", "1122111", "2111212", "2121112", "2121211", "1121212", "1122121", "1212112", "1112122", "1112221");

        // Convert to uppercase
        $upper_text = strtoupper($text);

        for ($X = 1; $X <= strlen($upper_text); $X++) {
            for ($Y = 0; $Y < count($code_array1); $Y++) {
                if (substr($upper_text, ($X - 1), 1) == $code_array1[$Y]) {
                    $code_string .= $code_array2[$Y] . "1";
                }

            }
        }
        $code_string = "11221211" . $code_string . "1122121";
    }

    // Pad the edges of the barcode
    $code_length = 20;
    if ($print) {
        $text_height = 30;
    } else {
        $text_height = 0;
    }

    for ($i = 1; $i <= strlen($code_string); $i++) {
        $code_length = $code_length + (integer) (substr($code_string, ($i - 1), 1));
    }

    if (strtolower($orientation) == "horizontal") {
        $img_width  = $code_length * $SizeFactor;
        $img_height = $size;
    } else {
        $img_width  = $size;
        $img_height = $code_length * $SizeFactor;
    }

    $image = imagecreate($img_width, $img_height + $text_height);
    $black = imagecolorallocate($image, 0, 0, 0);
    $white = imagecolorallocate($image, 255, 255, 255);

    imagefill($image, 0, 0, $white);
    if ($print) {
        imagestring($image, 5, 31, $img_height, $text, $black);
    }

    $location = 10;
    for ($position = 1; $position <= strlen($code_string); $position++) {
        $cur_size = $location + (substr($code_string, ($position - 1), 1));
        if (strtolower($orientation) == "horizontal") {
            imagefilledrectangle($image, $location * $SizeFactor, 0, $cur_size * $SizeFactor, $img_height, ($position % 2 == 0 ? $white : $black));
        } else {
            imagefilledrectangle($image, 0, $location * $SizeFactor, $img_width, $cur_size * $SizeFactor, ($position % 2 == 0 ? $white : $black));
        }

        $location = $cur_size;
    }

    // Draw barcode to the screen or save in a file
    if ($filepath == "") {
        header('Content-type: image/png');
        imagepng($image);
        imagedestroy($image);
    } else {
        imagepng($image, $filepath);
        imagedestroy($image);
    }
}

function compressImage($source, $destination, $quality = 20) { 
    // Obtenemos la información de la imagen
    $imgInfo = getimagesize($source); 
    $mime = $imgInfo['mime']; 
     
    // Creamos una imagen
    switch($mime){ 
        case 'image/jpeg': 
            $image = imagecreatefromjpeg($source); 
            break; 
        case 'image/png': 
            $image = imagecreatefrompng($source); 
            break; 
        case 'image/gif': 
            $image = imagecreatefromgif($source); 
            break; 
        default: 
            $image = imagecreatefromjpeg($source); 
    } 
     
    // Guardamos la imagen
    imagejpeg($image, $destination, $quality); 
     
    // Devolvemos la imagen comprimida
    return $destination; 
}