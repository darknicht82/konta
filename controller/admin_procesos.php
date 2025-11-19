<?php
/**
 * Controlador de Administrador -> Procesos.
 * @author Jonathan Guamba <jonathang_28@hotmail.es>
 */
class admin_procesos extends controller
{
    public $idempresa;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Procesos', 'Administrador', false, true, false, 'bi bi-tools');
    }

    protected function private_core()
    {
        $this->init_modelos();
        $this->init_filter();

        if (isset($_POST['regstock'])) {
            $this->regenerar_stock();
        } else if (isset($_POST['recalcular_stock'])) {
            $this->recalcular_stock();
        } else if (isset($_POST['procesos_costos'])) {
            $this->procesos_costos();
        }
    }

    private function init_modelos()
    {
        $this->stocks                 = new stocks();
        $this->trans_inventario       = new trans_inventario();
        $this->lineasfacturascli      = new lineasfacturascli();
        $this->lineasfacturasprov     = new lineasfacturasprov();
        $this->lineasmovimientos      = new lineasmovimientos();
        $this->lineasregularizaciones = new lineasregularizaciones();
    }

    private function init_filter()
    {
        $this->idempresa = $this->empresa->idempresa;
    }

    private function regenerar_stock()
    {
        //Paso 1 lineasfacturascli
        $lcli = $this->lineasfacturascli->get_by_idempresa($this->idempresa);
        foreach ($lcli as $key => $lc) {
            $lc->save(false);
        }

        //Paso 2 lineasfacturasprov
        $lpro = $this->lineasfacturasprov->get_by_idempresa($this->idempresa);
        foreach ($lpro as $key => $lp) {
            $lp->save(false);
        }

        //Paso 3 lineasmovimientos
        $lmov = $this->lineasmovimientos->get_by_idempresa($this->idempresa);
        foreach ($lmov as $key => $lm) {
            $lm->save();
        }

        //Paso 4 lineasmovimientos
        $lreg = $this->lineasregularizaciones->get_by_idempresa($this->idempresa);
        foreach ($lreg as $key => $lr) {
            $lr->save();
        }

        $this->new_message("Proceso ejecutado correctamente.");
    }

    private function recalcular_stock()
    {
        // 1. pongo todo el stock en 0
        $this->stocks->update_by_idempresa($this->idempresa);

        // 2. Busco la tabla acumulada
        $datos = $this->trans_inventario->get_recalculo_stock($this->idempresa);
        foreach ($datos as $key => $d) {
            $stock = $this->stocks->get_by_idestab_idart($d['idestablecimiento'], $d['idarticulo']);
            if (!$stock) {
                $stock                    = new \stocks();
                $stock->idempresa         = $this->idempresa;
                $stock->idestablecimiento = $d['idestablecimiento'];
                $stock->idarticulo        = $d['idarticulo'];
                $stock->fec_creacion      = date('Y-m-d');
                $stock->nick_creacion     = $this->user->nick;
            } else {
                $stock->fec_modificacion  = date('Y-m-d');
                $stock->nick_modificacion = $this->user->nick;
            }
            $stock->stock = $d['stock'];
            if (!$stock->save()) {
                $this->new_advice("Error al recalcular el stock del Artículo: ".$d['idarticulo']);
            }
        }

        $this->new_message("Proceso ejecutado correctamente.");
    }

    private function procesos_costos()
    {
        //Genero funcion de costo promedio ponderado
        $sql = "CREATE OR REPLACE FUNCTION costopromedio ( empresa INT, articulo INT, fecha_trans TIMESTAMP, opcion INT ) RETURNS NUMERIC AS $$ DECLARE
                    costo NUMERIC ( 16, 6 ) := 0;
                    costo_total NUMERIC ( 16, 6 ) := 0;
                    cantidad_total NUMERIC ( 16, 6 ) := 0;
                    retorno NUMERIC ( 16, 6 ) := 0;
                    kardex RECORD;
                BEGIN
                        FOR kardex IN (SELECT * FROM trans_inventario WHERE idarticulo = articulo AND idempresa = empresa AND aplica_stock = TRUE AND fec_trans < fecha_trans ORDER BY fec_trans ASC)
                            LOOP
                                IF kardex.movimiento > 0 AND kardex.origen NOT LIKE 'Nota de credito de Venta%' THEN
                                    costo_total = costo_total + (kardex.movimiento * kardex.costo);
                                ELSE
                                    costo_total = costo_total + (kardex.movimiento * costo);
                                END IF;
                                cantidad_total = cantidad_total + kardex.movimiento;
                                IF cantidad_total = 0 THEN
                                    costo_total = 0;
                                ELSE
                                    costo = costo_total / cantidad_total;
                                END IF;
                            END LOOP;
                            IF opcion = 1 THEN
                                retorno = cantidad_total;
                            ELSEIF opcion = 2 THEN
                                retorno = costo;
                            ELSE 
                                retorno = costo_total;
                            END IF;
                            
                            RETURN retorno;
                        END;    
                $$ LANGUAGE plpgsql;";
        if ($this->db->exec($sql)) {
            $this->new_message('Funcion de promedio ponderado generada correctamente');
        } else {
            $this->new_error_msg('Error en la generacion de Funcion de promedio ponderado');
        }
    }
}
