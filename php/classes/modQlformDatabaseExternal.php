<?php
/**
 * @package        mod_qlform
 * @copyright    Copyright (C) 2022 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

if (!class_exists('modQlformDatabase')) return;

class modQlformDatabaseExternal extends modQlformDatabase
{

    /**
     * Method for construction darabase params
     *
     * @param string $database database name
     * @param string $table Name of table to save data in
     *
     * @return  bool true on success, false on failure
     *
     */
    function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * Method for construction darabase params
     *
     * @param string $database database name
     * @param string $table Name of table to save data in
     *
     * @return  bool true on success, false on failure
     *
     */
    function getDatabase()
    {
        return JDatabaseDriver::getInstance($this->params);
    }

    /**
     * Method for getting Joomla! database name
     *
     * @return  string database name
     *
     */
    function getDatabaseName()
    {
        return $this->params['database'];
    }

    /**
     * Method for getting Joomla! prefix name
     *
     * @return  string database name
     *
     */
    function getPrefix()
    {
        return $this->params['prefix'];
    }

    /**
     * Method for getting Joomla! database name
     *
     * @return  string database name
     *
     */
    function getTableName($table)
    {
        return $table;
    }
}