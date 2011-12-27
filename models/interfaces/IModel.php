<?php

namespace Models;

/**
 *
 * @author Milan Matějček
 * pro spousteni transakce je dobre pouzivat statickou tridu dibi
 */
interface IModel
{
	/**
	 * @param ArrayHash $data
	 * @param int $id
	 * @param string $by
	 * @return int count of change rows
	 */
	public function update(array $data, $id, $by=NULL);

	/**
	 * @param ArrayHash $data
	 * @param bool $lastId
	 * @return int|\Nette\Database\Table\Selection
	 */
	public function insert(array $data, $lastId=FALSE);

	/**
	 * @param int|array $id
	 * @param string $column
	 * @param string $by
	 * @return int count of deleted rows
	 */
	public function delete($id, $column=NULL, $by=NULL);

	/**
	 * vrati jeden radek zavisli na ID
	 * @param int $id
	 * @param string $column
	 * @param string $by
	 * @return Nette\Database\Table\Selection
	 */
	public function find($id, $column=NULL, $by=NULL);

	/**
	 * metoda urcena pro vypis
	 * @return Nette\Database\Table\Selection
	 */
	public function findAll($columns='*', $page=NULL, $itemsPerPage=50);
}

/**
 * IModel je moc obecne a zanikne az to prepisu
 */
interface IDbModel extends IModel
{
	public function getTable();
}
