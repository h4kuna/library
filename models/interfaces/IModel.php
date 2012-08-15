<?php

namespace Models;

/**
 *
 * @author Milan Matějček
 * pro spousteni transakce je dobre pouzivat statickou tridu dibi
 */
interface IDbModel {

    /**
     * @param ArrayHash $data
     * @param int $id
     * @param string $by
     * @return int count of change rows
     */
    public function update(array $data, $id, $by = NULL);

    /**
     * @param ArrayHash $data
     * @param bool $lastId
     */
    public function insert(array $data, $lastId = FALSE);

    /**
     * @param int|array $id
     * @param string $column
     * @param string $by
     * @return int count of deleted rows
     */
    public function delete($id, $column = NULL, $by = NULL);

    /**
     * vrati jeden radek zavisli na ID
     * @param int $id
     * @param string $column
     * @param string $by
     */
    public function find($id, $column = NULL, $by = NULL);

    /**
     * metoda urcena pro vypis
     */
    public function findAll($columns = '', $parameters = NULL, $condition = NULL);

    public function getTable();

//-----------------transakce
    public function begin();

    public function commit();

    public function rollback();
}

