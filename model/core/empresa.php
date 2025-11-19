<?php
namespace GSC_Systems\model;

use PHPMailer\PHPMailer\PHPMailer;

require 'extras/phpmailer/Exception.php';
require 'extras/phpmailer/PHPMailer.php';
require 'extras/phpmailer/SMTP.php';

class empresa extends \model
{
    public $idempresa;
    public $nick;
    public $razonsocial;
    public $nombrecomercial;
    public $ruc;
    public $telefono;
    public $email;
    public $direccion;
    public $logo;
    public $firmaelectronica;
    public $fec_caducidad;
    public $clave_digital;
    public $mail_password;
    public $mail_bcc;
    public $mail_mailer;
    public $mail_host;
    public $mail_port;
    public $mail_enc;
    public $mail_user;
    public $mail_low_security;
    //Cuando este activo el plugin de Facturador
    public $fec_inicio_plan;
    public $fec_caducidad_plan;
    public $numusers;
    public $numdocs;
    //Auditoria del sistema
    public $fec_creacion;
    public $nick_creacion;
    public $fec_modificacion;
    public $nick_modificacion;

    public function __construct($d = false)
    {
        parent::__construct('empresa');

        if ($d) {
            $this->idempresa         = $this->intval($d['idempresa']);
            $this->razonsocial       = $d['razonsocial'];
            $this->nombrecomercial   = $d['nombrecomercial'];
            $this->ruc               = $d['ruc'];
            $this->telefono          = $d['telefono'];
            $this->email             = $d['email'];
            $this->direccion         = $d['direccion'];
            $this->logo              = $d['logo'];
            $this->firmaelectronica  = $d['firmaelectronica'];
            $this->fec_caducidad     = $d['fec_caducidad'] ? Date('d-m-Y', strtotime($d['fec_caducidad'])) : null;
            $this->clave_digital     = $d['clave_digital'];
            $this->mail_password     = $d['mail_password'];
            $this->mail_bcc          = $d['mail_bcc'];
            $this->mail_mailer       = $d['mail_mailer'];
            $this->mail_host         = $d['mail_host'];
            $this->mail_port         = $d['mail_port'];
            $this->mail_enc          = $d['mail_enc'];
            $this->mail_user         = $d['mail_user'];
            $this->mail_low_security = $this->str2bool($d['mail_low_security']);
            $this->regimen           = $d['regimen'];
            $this->obligado          = $this->str2bool($d['obligado']);
            $this->agretencion       = $this->str2bool($d['agretencion']);
            $this->produccion        = $this->str2bool($d['produccion']);
            $this->activafacelec     = $this->str2bool($d['activafacelec']);
            //Cuando sea el facturador
            $this->fec_inicio_plan    = $d['fec_inicio_plan'] ? Date('Y-m-d', strtotime($d['fec_inicio_plan'])) : null;
            $this->fec_caducidad_plan = $d['fec_caducidad_plan'] ? Date('Y-m-d', strtotime($d['fec_caducidad_plan'])) : null;
            $this->numusers           = $this->intval($d['numusers']);
            $this->numdocs            = $this->intval($d['numdocs']);
            $this->plan_basico        = $this->str2bool($d['plan_basico']);
            //Auditoria del sistema
            $this->fec_creacion      = $d['fec_creacion'] ? Date('Y-m-d', strtotime($d['fec_creacion'])) : null;
            $this->nick_creacion     = $d['nick_creacion'];
            $this->fec_modificacion  = $d['fec_modificacion'] ? Date('Y-m-d', strtotime($d['fec_modificacion'])) : null;
            $this->nick_modificacion = $d['nick_modificacion'];
        } else {
            $this->idempresa         = null;
            $this->razonsocial       = null;
            $this->nombrecomercial   = null;
            $this->ruc               = null;
            $this->telefono          = null;
            $this->email             = null;
            $this->direccion         = null;
            $this->logo              = null;
            $this->firmaelectronica  = null;
            $this->fec_caducidad     = null;
            $this->clave_digital     = null;
            $this->mail_password     = null;
            $this->mail_bcc          = null;
            $this->mail_mailer       = 'smtp';
            $this->mail_host         = 'smtp.gmail.com';
            $this->mail_port         = '465';
            $this->mail_enc          = 'ssl';
            $this->mail_user         = null;
            $this->mail_low_security = false;
            $this->regimen           = 'GE';
            $this->obligado          = 0;
            $this->agretencion       = 0;
            $this->produccion        = 0;
            $this->activafacelec     = 0;
            //Cuando sea el Facturador
            $this->fec_inicio_plan    = null;
            $this->fec_caducidad_plan = null;
            $this->numusers           = 1;
            $this->numdocs            = 0;
            $this->plan_basico        = 0;
            //Auditoria del sistema
            $this->fec_creacion      = null;
            $this->nick_creacion     = null;
            $this->fec_modificacion  = null;
            $this->nick_modificacion = null;

        }
    }

    protected function install()
    {
        return "INSERT INTO " . $this->table_name . " (razonsocial, nombrecomercial, ruc, telefono, email, direccion, mail_mailer, mail_host, mail_port, mail_enc, mail_low_security, fec_creacion, nick_creacion) VALUES ('GSC SYSTEMS', 'GSC SYSTEMS', '9999999999999', '222222222', 'info@gscsystemsec.com', 'Quito', 'smtp', 'smtp.gmail.com', '465', 'ssl', " . $this->var2str(false) . ", '" . date('Y-m-d') . "', 'admin');";
    }

    /**
     * Devuelve la url donde ver/modificar los datos
     * @return string
     */
    public function url()
    {
        return 'index.php?page=ver_empresa';
    }

    /**
     * Devuelve TRUE si están definidos el email y la contraseña
     * @return boolean
     */
    public function can_send_mail()
    {
        if ($this->email == '') {
            return false;
        }
        return true;
    }

    /**
     * Devuelve un objeto PHPMailer con la configuración ya preparada.
     * @return \PHPMailer
     */
    public function new_mail($email = '', $nombre = '', $ccopia = true)
    {
        //traigo las configuraciones
        $config0 = new configuraciones();
        $config = $config0->getConfig();
        //genero el mail
        $mail = new PHPMailer();
        //$mail->SMTPDebug = 0; //Enable verbose debug output
        $mail->isSMTP(); //Send using SMTP
        $mail->Host       = $config->mail_host; //Set the SMTP server to send through
        $mail->SMTPAuth   = true; //Enable SMTP authentication
        $mail->Username   = $config->mail_user; //SMTP username
        $mail->Password   = $config->mail_password; //SMTP password
        $mail->SMTPSecure = $config->mail_enc; //Enable implicit TLS encryption
        $mail->Port       = $config->mail_port; //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        $mail->From     = $this->email;
        $mail->FromName = $this->nombrecomercial;
        if ($email != '') {
            $mail->From = $email;
        }

        if ($nombre != '') {
            $mail->FromName = $nombre;
        }

        if ($this->mail_bcc && $ccopia) {
            $mail->addCC($this->mail_bcc);
        }

        return $mail;
    }

    public function mail_connect(&$mail)
    {
        return $mail->smtpConnect($this->smtp_options());
    }

    /**
     * Devuelve un array con las opciones para $mail->smtpConnect) de PHPMailer
     * @return array
     */
    public function smtp_options()
    {
        $SMTPOptions = [];

        if ($this->mail_low_security) {
            $SMTPOptions = array(
                'ssl' => array(
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true,
                ),
            );
        }

        return $SMTPOptions;
    }

    /**
     * Función llamada al enviar correctamente un email
     * @param \PHPMailer $mail
     */
    public function save_mail($mail)
    {
        /// tu código aquí
        /// $mail es el email ya enviado (es un objeto PHPMailer)
    }

    /**
     * Devuelve TRUE si existe
     * @return boolean
     */
    public function exists()
    {
        if (is_null($this->idempresa)) {
            return false;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($this->idempresa) . ";");
    }

    public function get($idempresa)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idempresa = " . $this->var2str($idempresa) . ";");
        if ($data) {
            return new \empresa($data[0]);
        }

        return false;
    }

    public function getBasico($idempresa)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE plan_basico = ".$this->var2str(true)." AND idempresa = " . $this->var2str($idempresa) . ";");
        if ($data) {
            return true;
        }

        return false;
    }

    /**
     * Comprueba los datos de la empresa, devuelve TRUE si está todo correcto
     * @return boolean
     */
    public function test()
    {
        $status = false;

        $this->razonsocial     = trim($this->razonsocial);
        $this->nombrecomercial = trim($this->nombrecomercial);
        $this->ruc             = trim($this->ruc);
        $this->telefono        = trim($this->telefono);
        $this->email           = trim($this->email);
        $this->direccion       = trim($this->direccion);

        if (strlen($this->razonsocial) < 1 || strlen($this->razonsocial) > 250) {
            $this->new_error_msg("Razón Social de la empresa no válida.");
        } else if (strlen($this->nombrecomercial) < 1 || strlen($this->nombrecomercial) > 250) {
            $this->new_error_msg("Nombre Comercial de la empresa no válido.");
        } else if (strlen($this->ruc) != 13) {
            $this->new_error_msg("RUC de la empresa no válido.");
        } else {
            $status = true;
        }

        return $status;
    }

    /**
     * Guarda los datos en la base de datos
     * @return boolean
     */
    public function save()
    {
        if ($this->test()) {
            if ($this->exists()) {
                $sql = "UPDATE " . $this->table_name . " SET razonsocial = " . $this->var2str($this->razonsocial)
                . ", nombrecomercial = " . $this->var2str($this->nombrecomercial)
                . ", ruc = " . $this->var2str($this->ruc)
                . ", telefono = " . $this->var2str($this->telefono)
                . ", email = " . $this->var2str($this->email)
                . ", direccion = " . $this->var2str($this->direccion)
                . ", logo = " . $this->var2str($this->logo)
                . ", firmaelectronica = " . $this->var2str($this->firmaelectronica)
                . ", fec_caducidad = " . $this->var2str($this->fec_caducidad)
                . ", clave_digital = " . $this->var2str($this->clave_digital)
                . ", mail_password = " . $this->var2str($this->mail_password)
                . ", mail_bcc = " . $this->var2str($this->mail_bcc)
                . ", mail_mailer = " . $this->var2str($this->mail_mailer)
                . ", mail_host = " . $this->var2str($this->mail_host)
                . ", mail_port = " . $this->var2str($this->mail_port)
                . ", mail_enc = " . $this->var2str($this->mail_enc)
                . ", mail_user = " . $this->var2str($this->mail_user)
                . ", mail_low_security = " . $this->var2str($this->mail_low_security)
                . ", regimen = " . $this->var2str($this->regimen)
                . ", obligado = " . $this->var2str($this->obligado)
                . ", agretencion = " . $this->var2str($this->agretencion)
                . ", produccion = " . $this->var2str($this->produccion)
                . ", activafacelec = " . $this->var2str($this->activafacelec)
                . ", fec_inicio_plan = " . $this->var2str($this->fec_inicio_plan)
                . ", fec_caducidad_plan = " . $this->var2str($this->fec_caducidad_plan)
                . ", numusers = " . $this->var2str($this->numusers)
                . ", numdocs = " . $this->var2str($this->numdocs)
                . ", plan_basico = " . $this->var2str($this->plan_basico)
                . ", fec_creacion = " . $this->var2str($this->fec_creacion)
                . ", nick_creacion = " . $this->var2str($this->nick_creacion)
                . ", fec_modificacion = " . $this->var2str($this->fec_modificacion)
                . ", nick_modificacion = " . $this->var2str($this->nick_modificacion)
                . "  WHERE idempresa = " . $this->var2str($this->idempresa) . ";";

                return $this->db->exec($sql);
            }

            $sql = "INSERT INTO " . $this->table_name . " (razonsocial, nombrecomercial, ruc, telefono, email, direccion, logo, firmaelectronica, fec_caducidad, clave_digital, mail_password, mail_bcc, mail_mailer, mail_host, mail_port, mail_enc, mail_user, mail_low_security, regimen, obligado, agretencion, produccion, activafacelec, fec_inicio_plan, fec_caducidad_plan, numusers, numdocs, plan_basico, fec_creacion, nick_creacion, fec_modificacion, nick_modificacion) VALUES
                      (" . $this->var2str($this->razonsocial)
            . "," . $this->var2str($this->nombrecomercial)
            . "," . $this->var2str($this->ruc)
            . "," . $this->var2str($this->telefono)
            . "," . $this->var2str($this->email)
            . "," . $this->var2str($this->direccion)
            . "," . $this->var2str($this->logo)
            . "," . $this->var2str($this->firmaelectronica)
            . "," . $this->var2str($this->fec_caducidad)
            . "," . $this->var2str($this->clave_digital)
            . "," . $this->var2str($this->mail_password)
            . "," . $this->var2str($this->mail_bcc)
            . "," . $this->var2str($this->mail_mailer)
            . "," . $this->var2str($this->mail_host)
            . "," . $this->var2str($this->mail_port)
            . "," . $this->var2str($this->mail_enc)
            . "," . $this->var2str($this->mail_user)
            . "," . $this->var2str($this->mail_low_security)
            . "," . $this->var2str($this->regimen)
            . "," . $this->var2str($this->obligado)
            . "," . $this->var2str($this->agretencion)
            . "," . $this->var2str($this->produccion)
            . "," . $this->var2str($this->activafacelec)
            . "," . $this->var2str($this->fec_inicio_plan)
            . "," . $this->var2str($this->fec_caducidad_plan)
            . "," . $this->var2str($this->numusers)
            . "," . $this->var2str($this->numdocs)
            . "," . $this->var2str($this->plan_basico)
            . "," . $this->var2str($this->fec_creacion)
            . "," . $this->var2str($this->nick_creacion)
            . "," . $this->var2str($this->fec_modificacion)
            . "," . $this->var2str($this->nick_modificacion)
                . ");";

            if ($this->db->exec($sql)) {
                $this->idempresa = $this->db->lastval();
                $this->generar_datos_empresa();
                return true;
            }
        }

        return false;
    }

    public function delete()
    {
        /// no se puede borrar la empresa
        return false;
    }

    public function get_regimen()
    {
        $regimen = '';
        foreach (regimen() as $key => $value) {
            if ($this->regimen == $key && $key != 'CE') {
                $regimen = $value;
            }
        }
        return $regimen;
    }

    public function all()
    {
        $list = array();
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY fec_inicio_plan DESC;");
        if ($data) {
            foreach ($data as $u) {
                $list[] = new \empresa($u);
            }
        }
        return $list;
    }

    private function generar_datos_empresa()
    {
        //creo las formas de pago
        $sql_for = "INSERT INTO formaspago (idempresa, nombre, escredito, esventa, escompra, esnc, codigosri, num_doc, esreten, fec_creacion, nick_creacion) VALUES
            (" . $this->var2str($this->idempresa) . ", 'Crédito', " . $this->var2str(true) . ", " . $this->var2str(true) . ", " . $this->var2str(true) . ", " . $this->var2str(false) . ", '20', " . $this->var2str(false) . ", " . $this->var2str(false) . ",'" . date('Y-m-d') . "', " . $this->var2str($this->nick_creacion) . "),
            (" . $this->var2str($this->idempresa) . ", 'Transferencia', " . $this->var2str(false) . ", " . $this->var2str(true) . ", " . $this->var2str(true) . ", " . $this->var2str(false) . ", '20', " . $this->var2str(true) . ", " . $this->var2str(false) . ", '" . date('Y-m-d') . "', " . $this->var2str($this->nick_creacion) . "),
            (" . $this->var2str($this->idempresa) . ", 'Nota de Credito', " . $this->var2str(false) . ", " . $this->var2str(true) . ", " . $this->var2str(true) . ", " . $this->var2str(true) . ", 'NC', " . $this->var2str(false) . ", " . $this->var2str(false) . ", '" . date('Y-m-d') . "', " . $this->var2str($this->nick_creacion) . "),
            (" . $this->var2str($this->idempresa) . ", 'Retencion', " . $this->var2str(false) . ", " . $this->var2str(true) . ", " . $this->var2str(true) . ", " . $this->var2str(true) . ", 'RET', " . $this->var2str(false) . ", " . $this->var2str(true) . ", '" . date('Y-m-d') . "', " . $this->var2str($this->nick_creacion) . "),
            (" . $this->var2str($this->idempresa) . ", 'Efectivo', " . $this->var2str(false) . ", " . $this->var2str(true) . ", " . $this->var2str(true) . ", " . $this->var2str(false) . ", '20', " . $this->var2str(false) . ", " . $this->var2str(false) . ",'" . date('Y-m-d') . "', " . $this->var2str($this->nick_creacion) . ");";
        $this->db->exec($sql_for);

        //Creo los clientes de consumidor final
        $sql_cli = "INSERT INTO clientes (idempresa, identificacion, tipoid, razonsocial, nombrecomercial, telefono, regimen, fec_creacion, nick_creacion) VALUES
                (" . $this->var2str($this->idempresa) . ", '9999999999999', 'F', 'CONSUMIDOR FINAL', 'CONSUMIDOR FINAL', '2222222222', 'GE', '" . date('Y-m-d') . "', " . $this->var2str($this->nick_creacion) . ")";
        $this->db->exec($sql_cli);

    }

    public function count_users()
    {
        $contador = 0;
        $sql      = "SELECT count(nick) AS contador FROM users WHERE admin != " . $this->var2str(true) . " AND idempresa = " . $this->var2str($this->idempresa);
        $data     = $this->db->select($sql);
        if ($data) {
            $contador = $data[0]['contador'];
        }

        return $contador;
    }

    public function count_docs()
    {
        $contador = 0;

        $contador += $this->countFacturas();
        $contador += $this->countNotasCredito();
        $contador += $this->countNotasDebito();
        $contador += $this->countGuiasRemision();
        $contador += $this->countRetenciones();
        $contador += $this->countLiquidaciones();
        $contador += $this->countNotasVenta();

        return $contador;
    }

    public function plan_activo($mensajes = false)
    {
        if ($this->fec_caducidad_plan < date('Y-m-d')) {
            if ($mensajes) {
                $this->new_error_msg("Su plan se encuentra caducado");
            }
            return false;
        }

        if ($this->numdocs > 0) {
            if ($this->count_docs() >= $this->numdocs) {
                if ($mensajes) {
                    $this->new_error_msg("Ya no dispone de Documentos para utilizarlos.");
                }
                return false;
            }
        }
        return true;
    }

    public function search_empresa($query = '', $fechadesde = '', $fechahasta = '', $vencimiento = false, $offset = 0, $limit = JG_ITEM_LIMIT)
    {
        $list = array();
        if ($offset >= 0) {
            $sql = "SELECT * FROM " . $this->table_name . " WHERE 1 = 1";
        } else {
            $sql = "SELECT count(idempresa) AS cont FROM " . $this->table_name . " WHERE 1 = 1";
        }

        if ($query != '') {
            $query = strtolower($query);
            $sql .= " AND (lower(razonsocial) LIKE '%" . $query . "%' OR lower(nombrecomercial) LIKE '%" . $query . "%' OR lower(ruc) LIKE '%" . $query . "%')";
        }

        if ($vencimiento) {
            if ($fechadesde != '') {
                $sql .= " AND fec_caducidad_plan >= " . $this->var2str($fechadesde);
            }
            if ($fechahasta != '') {
                $sql .= " AND fec_caducidad_plan <= " . $this->var2str($fechahasta);
            }
        } else {
            if ($fechadesde != '') {
                $sql .= " AND fec_inicio_plan >= " . $this->var2str($fechadesde);
            }
            if ($fechahasta != '') {
                $sql .= " AND fec_inicio_plan <= " . $this->var2str($fechahasta);
            }
        }

        if ($offset >= 0) {
            $sql .= " ORDER BY fec_inicio_plan DESC";
            $data = $this->db->select_limit($sql, $limit, $offset);
        } else {
            $data = $this->db->select($sql);
        }

        if ($data) {
            if ($offset < 0) {
                return $data[0]['cont'];
            } else {
                foreach ($data as $p) {
                    $list[] = new \empresa($p);
                }
            }
        } else {
            if ($offset < 0) {
                return 0;
            }
        }

        return $list;

    }

    public function get_by_identificacion($identificacion)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE ruc = " . $this->var2str($identificacion) . ";");
        if ($data) {
            return new \empresa($data[0]);
        }

        return false;
    }

    public function getEstado()
    {
        $dia = date('Y-m-d');

        if ($this->numdocs < 0) {
            if (date('Y-m-d', strtotime($this->fec_caducidad_plan . "- 10 days")) <= $dia && $this->fec_caducidad_plan >= $dia) {
                return 'Por Caducar';
            } else if ($dia > $this->fec_caducidad_plan) {
                return 'Caducado';
            }
        } else {
            $cont = $this->count_docs();
            $docs = $this->numdocs - 5;
            if ($docs <= $cont && $this->numdocs >= $cont) {
                return 'Por Finalizar Documentos';
            } else if ($cont > $this->numdocs) {
                return 'Documentos Finalizados';
            } else if (date('Y-m-d', strtotime($this->fec_caducidad_plan . "- 10 days")) <= $dia && $this->fec_caducidad_plan >= $dia) {
                return 'Por Caducar';
            } else if ($dia > $this->fec_caducidad_plan) {
                return 'Caducado';
            }
        }
        return 'Vigente';
    }

    public function getEstado2()
    {
        $dia = date('Y-m-d');

        if ($this->numdocs < 0) {
            if (date('Y-m-d', strtotime($this->fec_caducidad_plan . "- 10 days")) <= $dia && $this->fec_caducidad_plan >= $dia) {
                return 'PC';
            } else if ($dia > $this->fec_caducidad_plan) {
                return 'C';
            }
        } else {
            $cont = $this->count_docs();
            $docs = $this->numdocs - 5;
            if ($docs <= $cont && $this->numdocs >= $cont) {
                return 'PC';
            } else if ($cont > $this->numdocs) {
                return 'C';
            } else if (date('Y-m-d', strtotime($this->fec_caducidad_plan . "- 10 days")) <= $dia && $this->fec_caducidad_plan >= $dia) {
                return 'PC';
            } else if ($dia > $this->fec_caducidad_plan) {
                return 'C';
            }
        }
        return 'V';
    }

    public function countFacturas()
    {
        $total = 0;

        $sql  = "SELECT count(idfacturacli) AS facturas FROM facturascli WHERE idempresa = " . $this->var2str($this->idempresa) . " AND fec_emision >= " . $this->var2str($this->fec_inicio_plan) . " AND fec_emision <= " . $this->var2str($this->fec_caducidad_plan) . " AND coddocumento = '01' AND saldoinicial != " . $this->var2str(true);
        $data = $this->db->select($sql);
        if ($data) {
            $total += floatval($data[0]['facturas']);
        }

        return $total;
    }

    public function countNotasCredito()
    {
        $total = 0;

        $sql  = "SELECT count(idfacturacli) AS facturas FROM facturascli WHERE idempresa = " . $this->var2str($this->idempresa) . " AND fec_emision >= " . $this->var2str($this->fec_inicio_plan) . " AND fec_emision <= " . $this->var2str($this->fec_caducidad_plan) . " AND coddocumento = '04' AND saldoinicial != " . $this->var2str(true);
        $data = $this->db->select($sql);
        $data = $this->db->select($sql);
        if ($data) {
            $total += floatval($data[0]['facturas']);
        }

        return $total;
    }

    public function countNotasDebito()
    {
        $total = 0;

        $sql  = "SELECT count(idfacturacli) AS facturas FROM facturascli WHERE idempresa = " . $this->var2str($this->idempresa) . " AND fec_emision >= " . $this->var2str($this->fec_inicio_plan) . " AND fec_emision <= " . $this->var2str($this->fec_caducidad_plan) . " AND coddocumento = '05' AND saldoinicial != " . $this->var2str(true);
        $data = $this->db->select($sql);
        $data = $this->db->select($sql);
        if ($data) {
            $total += floatval($data[0]['facturas']);
        }

        return $total;
    }

    public function countGuiasRemision()
    {
        $total = 0;

        $sql  = "SELECT count(idguiacli) AS guias FROM guiascli WHERE idempresa = " . $this->var2str($this->idempresa) . " AND fec_emision >= " . $this->var2str($this->fec_inicio_plan) . " AND fec_emision <= " . $this->var2str($this->fec_caducidad_plan);
        $data = $this->db->select($sql);
        if ($data) {
            $total += floatval($data[0]['guias']);
        }

        return $total;
    }

    public function countRetenciones()
    {
        $total = 0;

        $sql  = "SELECT count(idfacturaprov) AS facturas FROM facturasprov WHERE idempresa = " . $this->var2str($this->idempresa) . " AND numero_retencion IS NOT NULL AND fec_emision >= " . $this->var2str($this->fec_inicio_plan) . " AND fec_emision <= " . $this->var2str($this->fec_caducidad_plan) . " AND saldoinicial != " . $this->var2str(true);
        $data = $this->db->select($sql);
        if ($data) {
            $total += floatval($data[0]['facturas']);
        }

        return $total;
    }

    public function countLiquidaciones()
    {
        $total = 0;

        $sql  = "SELECT count(idfacturaprov) AS facturas FROM facturasprov WHERE idempresa = " . $this->var2str($this->idempresa) . " AND fec_emision >= " . $this->var2str($this->fec_inicio_plan) . "  AND coddocumento = '03' AND fec_emision <= " . $this->var2str($this->fec_caducidad_plan) . " AND saldoinicial != " . $this->var2str(true);
        $data = $this->db->select($sql);
        if ($data) {
            $total += floatval($data[0]['facturas']);
        }

        return $total;
    }

    public function countNotasVenta()
    {
        $total = 0;

        $sql  = "SELECT count(idfacturacli) AS facturas FROM facturascli WHERE idempresa = " . $this->var2str($this->idempresa) . " AND fec_emision >= " . $this->var2str($this->fec_inicio_plan) . " AND fec_emision <= " . $this->var2str($this->fec_caducidad_plan) . " AND coddocumento = '02' AND saldoinicial != " . $this->var2str(true);
        $data = $this->db->select($sql);
        if ($data) {
            $total += floatval($data[0]['facturas']);
        }

        return $total;
    }
}
