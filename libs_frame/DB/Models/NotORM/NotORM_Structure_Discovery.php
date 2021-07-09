<?php
/**
 * Structure reading meta-informations from the database
 */
namespace DB\Models\NotORM;

class NotORM_Structure_Discovery implements NotORM_Structure
{
    protected $connection;
    protected $cache;
    protected $structure = [];
    protected $foreign;

    /**
     * Create autodisovery structure
     * @param PDO
     * @param NotORM_Cache
     * @param string use "%s_id" to access $name . "_id" column in $row->$name
     * @param mixed $foreign
     */
    public function __construct(\PDO $connection, NotORM_Cache $cache = null, $foreign = '%s')
    {
        $this->connection = $connection;
        $this->cache = $cache;
        $this->foreign = $foreign;
        if ($cache) {
            $this->structure = $cache->load('structure');
        }
    }

    /** Save data to cache
     */
    public function __destruct()
    {
        if ($this->cache) {
            $this->cache->save('structure', $this->structure);
        }
    }

    public function getPrimary($table)
    {
        $return = &$this->structure['primary'][$table];
        if (!isset($return)) {
            $return = '';
            foreach ($this->connection->query("EXPLAIN $table") as $column) {
                if ($column[3] == 'PRI') { // 3 - "Key" is not compatible with PDO::CASE_LOWER
                    if ($return != '') {
                        $return = ''; // multi-column primary key is not supported
                        break;
                    }
                    $return = $column[0];
                }
            }
        }
        return $return;
    }

    public function getReferencingColumn($name, $table)
    {
        $name = strtolower($name);
        $return = &$this->structure['referencing'][$table];
        if (!isset($return[$name])) {
            foreach ($this->connection->query(
                '
                SELECT TABLE_NAME, COLUMN_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND REFERENCED_TABLE_SCHEMA = DATABASE()
                AND REFERENCED_TABLE_NAME = ' . $this->connection->quote($table) . '
                AND REFERENCED_COLUMN_NAME = ' . $this->connection->quote($this->getPrimary($table)) //! may not reference primary key
            ) as $row) {
                $return[strtolower($row[0])] = $row[1];
            }
        }
        return $return[$name];
    }

    public function getReferencingTable($name, $table)
    {
        return $name;
    }

    public function getReferencedColumn($name, $table)
    {
        return sprintf($this->foreign, $name);
    }

    public function getReferencedTable($name, $table)
    {
        $column = strtolower($this->getReferencedColumn($name, $table));
        $return = &$this->structure['referenced'][$table];
        if (!isset($return[$column])) {
            foreach ($this->connection->query('
                SELECT COLUMN_NAME, REFERENCED_TABLE_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND REFERENCED_TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = ' . $this->connection->quote($table) . '
            ') as $row) {
                $return[strtolower($row[0])] = $row[1];
            }
        }
        return $return[$column];
    }

    public function getSequence($table)
    {
    }
}
