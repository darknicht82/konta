<?php
/**
 * Controlador de admin -> empresa.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class ver_empresa extends controller
{
    public $almacen;
    public $cuenta_banco;
    public $divisa;
    public $ejercicio;
    public $forma_pago;
    public $impresion;
    public $serie;
    public $pais;

    //filtros
    public $mostrar;

    //Variables de modelos
    public $establecimiento;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Empresa', 'Configuración', true, true, false, 'bi bi-building');
    }

    protected function private_core()
    {
        $this->establecimiento = new establecimiento();
        $this->mostrar         = 'logo';
        if (isset($_GET['mostrar'])) {
            $this->mostrar = $_GET['mostrar'];
        }

        if (isset($_POST['razonsocial'])) {
            $this->modificar_datos_empresa();
        } else if (isset($_POST['email'])) {
            $this->modificar_correo_empresa();
        } else if (isset($_POST['logo'])) {
            $this->cargar_logo_empresa();
        } else if (isset($_GET['delete_logo'])) {
            $this->borrar_logo_empresa();
        } else if (isset($_POST['idestablecimiento'])) {
            $this->tratar_establecimiento();
        } else if (isset($_GET['del_est'])) {
            $this->borrar_establecimiento();
        } else if (isset($_GET['delete_firma'])) {
            $this->borrar_firma_empresa();
        } else if (isset($_POST['firma'])) {
            $this->cargar_firma_empresa();
        }
    }

    private function modificar_datos_empresa()
    {
        /// guardamos solamente lo básico, ya que facturacion_base no está activado
        $this->empresa->razonsocial     = $_POST['razonsocial'];
        $this->empresa->nombrecomercial = $_POST['nombrecomercial'];
        if (isset($_POST['ruc']) && $this->user->admin) {
            $this->empresa->ruc = $_POST['ruc'];
        }
        $this->empresa->telefono      = $_POST['telefono'];
        $this->empresa->direccion     = $_POST['direccion'];
        $this->empresa->regimen       = $_POST['regimen'];
        $this->empresa->obligado      = isset($_POST['obligado']);
        $this->empresa->agretencion   = isset($_POST['agretencion']);
        $this->empresa->activafacelec = isset($_POST['activafacelec']);
        if (!$this->empresa->produccion) {
            $this->empresa->produccion = isset($_POST['produccion']);
        }
        $this->empresa->fec_modificacion  = date('Y-m-d');
        $this->empresa->nick_modificacion = $this->user->nick;

        if ($this->empresa->save()) {
            $this->new_message('Datos de Empresa guardados correctamente.');
        } else {
            $this->new_error_msg('Error al guardar los datos de la empresa.');
        }
    }

    private function modificar_correo_empresa()
    {
        /// configuración de email
        $this->empresa->email = $_POST['email'];
        //$this->empresa->mail_password = $_POST['mail_password'];
        $this->empresa->mail_bcc = $_POST['mail_bcc'];
        //$this->empresa->mail_firma        = $_POST['mail_firma'];
        //$this->empresa->mail_mailer       = $_POST['mail_mailer'];
        //$this->empresa->mail_host         = $_POST['mail_host'];
        //$this->empresa->mail_port         = intval($_POST['mail_port']);
        //$this->empresa->mail_enc          = strtolower($_POST['mail_enc']);
        //$this->empresa->mail_user         = $_POST['mail_user'];
        //$this->empresa->mail_low_security = isset($_POST['mail_low_security']);
        $this->empresa->fec_modificacion  = date('Y-m-d');
        $this->empresa->nick_modificacion = $this->user->nick;

        if ($this->empresa->save()) {
            $this->new_message('Datos de correo guardados correctamente.');
            $this->mail_test();
        } else {
            $this->new_error_msg('Error al guardar los datos del correo.');
        }
    }

    private function cargar_logo_empresa()
    {
        //carga de logo
        if (is_uploaded_file($_FILES['logoimagen']['tmp_name'])) {
            if (!file_exists(JG_MYDOCS . 'datosEmpresas/' . $this->empresa->idempresa . "/logo")) {
                @mkdir(JG_MYDOCS . 'datosEmpresas/' . $this->empresa->idempresa . "/logo", 0777, true);
            }

            $rutalogo = "";
            if (substr(strtolower($_FILES['logoimagen']['name']), -3) == 'png') {
                $rutalogo = JG_MYDOCS . "datosEmpresas/" . $this->empresa->idempresa . "/logo/logo.png";
            } else if (substr(strtolower($_FILES['logoimagen']['name']), -3) == 'jpg') {
                $rutalogo = JG_MYDOCS . "datosEmpresas/" . $this->empresa->idempresa . "/logo/logo.jpg";
            }
            // Image temp source
            $imageTemp = $_FILES["logoimagen"]["tmp_name"];
            // Comprimos el fichero
            $compressedImage = compressImage($imageTemp, $rutalogo);

            if ($compressedImage) {
                $this->empresa->logo = $rutalogo;
                if ($this->empresa->save()) {
                    $this->new_message('Logo guardo correctamente.');
                } else {
                    $this->new_error_msg('Error al guardar el logo de la empresa.');
                }
            } else {
                $this->new_error_msg("Error al comprimir la imagen");
            }
        }
    }

    private function cargar_firma_empresa()
    {
        //carga de logo
        if (is_uploaded_file($_FILES['firmaelectronica']['tmp_name'])) {
            if (!file_exists(JG_MYDOCS . 'datosEmpresas/' . $this->empresa->idempresa . "/firma")) {
                @mkdir(JG_MYDOCS . 'datosEmpresas/' . $this->empresa->idempresa . "/firma", 0777, true);
            }

            $rutafirma = JG_MYDOCS . "datosEmpresas/" . $this->empresa->idempresa . "/firma/firma_digital.p12";
            copy($_FILES['firmaelectronica']['tmp_name'], $rutafirma);
            if ($rutafirma != '') {
                $this->empresa->firmaelectronica = $rutafirma;
                $this->empresa->fec_caducidad    = $_POST['fec_caducidad'];
                $this->empresa->clave_digital    = $_POST['clave_digital'];
                if ($this->empresa->save()) {
                    //Proceso para enviar al servidor de Facturacion electronica
                    $respuesta = cargar_firma_elect($_FILES['firmaelectronica']['tmp_name'], $this->empresa->ruc, $this->empresa->clave_digital);
                    $this->new_message('Firma guardada correctamente.');
                    if ($respuesta['error'] == "T") {
                        $this->new_error_msg($respuesta['msj']);
                    } else {
                        $this->new_advice($respuesta['msj']);
                    }
                } else {
                    $this->new_error_msg('Error al guardar la firma de la empresa.');
                    if (file_exists($rutafirma)) {
                        unlink($rutafirma);
                    }
                }
            }
        }
    }

    private function borrar_logo_empresa()
    {
        if (file_exists($this->empresa->logo)) {
            unlink($this->empresa->logo);
        }
        $this->empresa->logo = null;
        if ($this->empresa->save()) {
            $this->new_message('Logo eliminado correctamente.');
        } else {
            $this->new_error_msg('Error al eliminar el logo de la empresa.');
        }
    }

    private function borrar_firma_empresa()
    {
        if (file_exists($this->empresa->firmaelectronica)) {
            unlink($this->empresa->firmaelectronica);
        }
        $this->empresa->firmaelectronica = null;
        $this->empresa->fec_caducidad    = null;
        $this->empresa->clave_digital    = null;

        if ($this->empresa->save()) {
            $this->new_message('Firma Digital eliminada correctamente.');
        } else {
            $this->new_error_msg('Error al eliminar la Firma Digital de la empresa.');
        }
    }

    private function tratar_establecimiento()
    {
        $est = false;
        if ($_POST['idestablecimiento'] != '') {
            $est = $this->establecimiento->get($_POST['idestablecimiento']);
        }
        if (!$est) {
            $est                = new establecimiento();
            $est->idempresa     = $this->empresa->idempresa;
            $est->fec_creacion  = date('Y-m-d');
            $est->nick_creacion = $this->user->nick;
        }
        $est->codigo     = $_POST['codigo'];
        $est->nombre     = $_POST['nombre'];
        $est->direccion  = $_POST['direccion'];
        $est->ptoemision = $_POST['ptoemision'];
        $est->numfac     = $_POST['numfac'];
        $est->numndd     = $_POST['numndd'];
        $est->numncc     = $_POST['numncc'];
        $est->numliq     = $_POST['numliq'];
        $est->numret     = $_POST['numret'];
        $est->numguia    = $_POST['numguia'];
        if (isset($_POST['numnvt'])) {
            $est->numnvt = $_POST['numnvt'];
        }

        if ($est->save()) {
            $this->new_message("Datos almacenados correctamente.");
        } else {
            $this->new_error_msg("Error en el almacenamiento de datos.");
        }
    }

    private function borrar_establecimiento()
    {
        $est = $this->establecimiento->get($_GET['del_est']);
        if ($est) {
            if ($est->delete()) {
                $this->new_message("Establecimiento eliminado correctamente.");
            } else {
                $this->new_error_msg("Error al eliminar el establecimiento, posiblemente ya tiene transacciones realizadas.");
            }
        } else {
            $this->new_advice("Establecimiento no encontrado.");

        }
    }

    private function mail_test()
    {
        if ($this->empresa->can_send_mail()) {
            /// Es imprescindible OpenSSL para enviar emails con los principales proveedores
            if (extension_loaded('openssl')) {
                try {
                    $mail = $this->empresa->new_mail('', '', false);
                    $mail->addAddress($this->empresa->email, $this->empresa->razonsocial); //Add a recipient
                    $mail->Subject = 'Prueba de Correo';
                    $mail->AltBody = $this->empresa->razonsocial;
                    $mail->Body    = 'Este es una prueba de envio de correo';
                    $mail->isHTML(true);
                    if (!$this->empresa->mail_connect($mail)) {
                        $this->new_error_msg('No se ha podido conectar por email. ¿La contraseña es correcta?');
                    } else {
                        if ($mail->send()) {
                            $this->new_advice('Correo enviado correctamente');
                        } else {
                            $this->new_error_msg("El mensaje no se ha enviado. Mailer Error: {$mail->ErrorInfo}");
                        }
                    }
                } catch (Exception $e) {
                    $this->new_advice("El mensaje no se ha enviado. Mailer Error: {$mail->ErrorInfo}");
                }
            } else {
                $this->new_error_msg('No se encuentra la extensión OpenSSL, imprescindible para enviar emails.');
            }
        } else {
            $this->new_advice("No tiene un correo electronico asociado.");
        }
    }

    public function encriptaciones()
    {
        return array(
            'ssl' => 'SSL',
            'tls' => 'TLS',
            ''    => 'Ninguna',
        );
    }

    public function mailers()
    {
        return array(
            'mail'     => 'Mail',
            'sendmail' => 'SendMail',
            'smtp'     => 'SMTP',
        );
    }
}
