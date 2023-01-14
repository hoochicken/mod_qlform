<?php
/**
 * @package        mod_qlform
 * @copyright    Copyright (C) 2023 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace QlformNamespace\Module\Qlform\Site\Helper;
use JDatabaseDriver;
use Joomla\Database\DatabaseDriver;

defined('_JEXEC') or die;

class modQlformDatabaseExternal extends modQlformDatabase
{
    private $params;

    /**
     * Method for construction darabase params
     *
     * @param $db
     * @param $params
     */
    function __construct($db, $params)
    {
        $this->params = $params;
        parent::__construct($db);
    }

    /**
     * Method for construction darabase params
     *
     * @return  DatabaseDriver true on success, false on failure
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
    function getDatabaseName(): string
    {
        return $this->params['database'];
    }

    /**
     * Method for getting Joomla! prefix name
     *
     * @return  string database name
     *
     */
    function getPrefix(): string
    {
        return $this->params['prefix'];
    }

    /**
     * Method for getting Joomla! database name
     *
     * @return  string database name
     *
     */
    function getTableName($table): string
    {
        return $table;
    }
}