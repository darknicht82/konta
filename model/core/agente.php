<?php

namespace GSC_Systems\model;

class agente extends \model
{

    /**
     * Clave primaria. Varchar (10).
     * @var string
     */
    public $idagente;

    /**
     * Identificador fiscal (CIF/NIF).
     * @var string
     */
    public $dnicif;
    public $nombre;
    public $apellidos;
    public $email;
    public $telefono;
    public $codpostal;
    public $provincia;
    public $ciudad;
    public $direccion;

    /**
     * Nº de la seguridad social.
     * @var string
     */
    public $seg_social;

    /**
     * cargo en la empresa.
     * @var string
     */
    public $cargo;

    /**
     * Cuenta bancaria
     * @var string
     */
    public $banco;

    /**
     * Fecha de nacimiento.
     * @var string
     */
    public $f_nacimiento;

    /**
     * Fecha de alta en la empresa.
     * @var string
     */
    public $f_alta;

    /**
     * Fecha de baja en la empresa.
     * @var string
     */
    public $f_baja;

    /**
     * Porcentaje de comisión del agente. Se utiliza en presupuestos, pedidos, albaranes y facturas.
     * @var float
     */
    public $porcomision;

    public function __construct($data = FALSE)
    {
        parent::__construct('agentes');
        if ($data) {
            $this->idagente = $data['idagente'];
            $this->nombre = $data['nombre'];
            $this->apellidos = $data['apellidos'];
            $this->dnicif = $data['dnicif'];
            $this->email = $data['email'];
            $this->telefono = $data['telefono'];
            $this->codpostal = $data['codpostal'];
            $this->provincia = $data['provincia'];
            $this->ciudad = $data['ciudad'];
            $this->direccion = $data['direccion'];
            $this->porcomision = floatval($data['porcomision']);
            $this->seg_social = $data['seg_social'];
            $this->banco = $data['banco'];
            $this->cargo = $data['cargo'];

            $this->f_alta = NULL;
            if ($data['f_alta'] != '') {
                $this->f_alta = Date('d-m-Y', strtotime($data['f_alta']));
            }

            $this->f_baja = NULL;
            if ($data['f_baja'] != '') {
                $this->f_baja = Date('d-m-Y', strtotime($data['f_baja']));
            }

            $this->f_nacimiento = NULL;
            if ($data['f_nacimiento'] != '') {
                $this->f_nacimiento = Date('d-m-Y', strtotime($data['f_nacimiento']));
            }
        } else {
            $this->idagente = NULL;
            $this->nombre = '';
            $this->apellidos = '';
            $this->dnicif = '';
            $this->email = NULL;
            $this->telefono = NULL;
            $this->codpostal = NULL;
            $this->provincia = NULL;
            $this->ciudad = NULL;
            $this->direccion = NULL;
            $this->porcomision = 0.00;
            $this->seg_social = NULL;
            $this->banco = NULL;
            $this->cargo = NULL;
            $this->f_alta = Date('d-m-Y');
            $this->f_baja = NULL;
            $this->f_nacimiento = Date('d-m-Y');
        }
    }

    protected function install()
    {
        $this->clean_cache();
        return "INSERT INTO " . $this->table_name . " (idagente,nombre,apellidos,dnicif)
         VALUES ('1','Paco','Pepe','00000014Z');";
    }

    /**
     * Devuelve nombre + apellidos del agente.
     * @return string
     */
    public function get_fullname()
    {
        return $this->nombre . " " . $this->apellidos;
    }

    /**
     * Genera un nuevo código de agente
     * @return string
     */
    public function get_new_codigo()
    {
        $sql = "SELECT MAX(" . $this->db->sql_to_int('idagente') . ") as cod FROM " . $this->table_name . ";";
        $data = $this->db->select($sql);
        if ($data) {
            return (string) (1 + (int) $data[0]['cod']);
        }

        return '1';
    }

    /**
     * Devuelve la url donde se pueden ver/modificar estos datos
     * @return string
     */
    public function url()
    {
        if (is_null($this->idagente)) {
            return "index.php?page=admin_agentes";
        }

        return "index.php?page=admin_agente&cod=" . $this->idagente;
    }

    /**
     * Devuelve el empleado/agente con idagente = $cod
     * @param string $cod
     * @return \agente|boolean
     */
    public function get($cod)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idagente = " . $this->var2str($cod) . ";");
        if ($data) {
            return new \agente($data[0]);
        }

        return FALSE;
    }

    /**
     * Devuelve TRUE si el agente/empleado existe, false en caso contrario
     * @return boolean
     */
    public function exists()
    {
        if (is_null($this->idagente)) {
            return FALSE;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idagente = " . $this->var2str($this->idagente) . ";");
    }

    /**
     * Comprueba los datos del empleado/agente, devuelve TRUE si son correctos
     * @return boolean
     */
    public function test()
    {
        $this->apellidos = $this->no_html($this->apellidos);
        $this->banco = $this->no_html($this->banco);
        $this->cargo = $this->no_html($this->cargo);
        $this->ciudad = $this->no_html($this->ciudad);
        $this->codpostal = $this->no_html($this->codpostal);
        $this->direccion = $this->no_html($this->direccion);
        $this->dnicif = $this->no_html($this->dnicif);
        $this->email = $this->no_html($this->email);
        $this->nombre = $this->no_html($this->nombre);
        $this->provincia = $this->no_html($this->provincia);
        $this->seg_social = $this->no_html($this->seg_social);
        $this->telefono = $this->no_html($this->telefono);

        if (strlen($this->nombre) < 1 || strlen($this->nombre) > 50) {
            $this->new_error_msg("El nombre del empleado debe tener entre 1 y 50 caracteres.");
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Guarda los datos en la base de datos
     * @return boolean
     */
    public function save()
    {
        if ($this->test()) {
            $this->clean_cache();

            if ($this->exists()) {
                $sql = "UPDATE " . $this->table_name . " SET nombre = " . $this->var2str($this->nombre) .
                    ", apellidos = " . $this->var2str($this->apellidos) .
                    ", dnicif = " . $this->var2str($this->dnicif) .
                    ", telefono = " . $this->var2str($this->telefono) .
                    ", email = " . $this->var2str($this->email) .
                    ", cargo = " . $this->var2str($this->cargo) .
                    ", provincia = " . $this->var2str($this->provincia) .
                    ", ciudad = " . $this->var2str($this->ciudad) .
                    ", direccion = " . $this->var2str($this->direccion) .
                    ", codpostal = " . $this->var2str($this->codpostal) .
                    ", f_nacimiento = " . $this->var2str($this->f_nacimiento) .
                    ", f_alta = " . $this->var2str($this->f_alta) .
                    ", f_baja = " . $this->var2str($this->f_baja) .
                    ", seg_social = " . $this->var2str($this->seg_social) .
                    ", banco = " . $this->var2str($this->banco) .
                    ", porcomision = " . $this->var2str($this->porcomision) .
                    "  WHERE idagente = " . $this->var2str($this->idagente) . ";";
            } else {
                if (is_null($this->idagente)) {
                    $this->idagente = $this->get_new_codigo();
                }

                $sql = "INSERT INTO " . $this->table_name . " (idagente,nombre,apellidos,dnicif,telefono,
               email,cargo,provincia,ciudad,direccion,codpostal,f_nacimiento,f_alta,f_baja,seg_social,
               banco,porcomision) VALUES (" . $this->var2str($this->idagente) .
                    "," . $this->var2str($this->nombre) .
                    "," . $this->var2str($this->apellidos) .
                    "," . $this->var2str($this->dnicif) .
                    "," . $this->var2str($this->telefono) .
                    "," . $this->var2str($this->email) .
                    "," . $this->var2str($this->cargo) .
                    "," . $this->var2str($this->provincia) .
                    "," . $this->var2str($this->ciudad) .
                    "," . $this->var2str($this->direccion) .
                    "," . $this->var2str($this->codpostal) .
                    "," . $this->var2str($this->f_nacimiento) .
                    "," . $this->var2str($this->f_alta) .
                    "," . $this->var2str($this->f_baja) .
                    "," . $this->var2str($this->seg_social) .
                    "," . $this->var2str($this->banco) .
                    "," . $this->var2str($this->porcomision) . ");";
            }

            return $this->db->exec($sql);
        }

        return FALSE;
    }

    /**
     * Elimina este empleado/agente
     * @return boolean
     */
    public function delete()
    {
        $this->clean_cache();
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE idagente = " . $this->var2str($this->idagente) . ";");
    }

    /**
     * Limpiamos la caché
     */
    private function clean_cache()
    {
        $this->cache->delete('m_agente_all');
    }

    /**
     * Devuelve un array con todos los agentes/empleados.
     * @return \agente
     */
    public function all($incluir_debaja = FALSE)
    {
        if ($incluir_debaja) {
            $listagentes = [];
            $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY nombre ASC, apellidos ASC;");
            if ($data) {
                foreach ($data as $a) {
                    $listagentes[] = new \agente($a);
                }
            }
        } else {
            /// leemos esta lista de la caché
            $listagentes = $this->cache->get_array('m_agente_all');

            if (empty($listagentes)) {
                /// si no está en caché, leemos de la base de datos
                $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE f_baja IS NULL ORDER BY nombre ASC, apellidos ASC;");
                if ($data) {
                    foreach ($data as $a) {
                        $listagentes[] = new \agente($a);
                    }
                }

                /// guardamos la lista en caché
                $this->cache->set('m_agente_all', $listagentes);
            }
        }

        return $listagentes;
    }
}
