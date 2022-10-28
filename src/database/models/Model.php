<?php

namespace App\Database\Models;


use App\Database\Connection;

class Model extends Connection
{
    protected static  $connection;
    protected $attributes;
    protected $dirty = false;
    protected static $table;

    public  function __construct($attributes = array())
    {
        $_ = $attributes;
        if (isset($this->defaults))
            $_ = array_merge($attributes, $this->defaults);

        $this->attributes = $_;
        return $this;
    }

    public static function setConnetion()
    {


        self::$connection = self::connect();
    }
    public  function __get($key)
    {
        if (isset($this->attributes[$key]))
            return $this->attributes[$key];
        else
            return null;
    }

    public function __set($key, $value = null)
    {
        $this->attributes[$key] = $value;
    }

    public function update_attributes(array $attributes = array())
    {
        $this->attributes = array_merge($this->attributes, $attributes);
    }

    // Validation

    /**
     * Override this for custom validation stuff
     */
    public function valid()
    {
        return true;
    }

    // Raw Query

    public static function connection()
    {
        self::$connection = self::connect();

        return self::$connection;
    }
    static public   function query($sql)
    {

        $result = self::connection()->query($sql);
        $caller = get_called_class();
        if ($result) {
            if ($result->num_rows > 1) {
                $objects = array();
                while ($attributes = $result->fetch_assoc())
                    $objects[] = new $caller($attributes);
                static::connection()->close();
                return $objects;
            } else {
                $ret = new $caller($result->fetch_assoc());
                static::connection()->close();
                return $ret;
            }
        } else {
            return null;
        }
    }

    static private    function column_map()
    {
        $columns = self::columns();
        $map = array();
        foreach ($columns as $column) {
            $type = 's';
            switch (substr($column->Type, 0, 1)) {
                case 'i': // int
                    $type = 'i';
                    break;
                case 'v': // varchar
                case 't': // text
                case 'd': // datetime
                default:
                    $type = 's';
            }
            $map[$column->Field] = $type;
        }

        return $map;
    }

    static private  function columns()
    {
        $result = self::connection()->query(sprintf('SHOW COLUMNS IN %s', static::$table));
        $columns = array();
        while ($column = $result->fetch_object())
            $columns[] = $column;

        return $columns;
    }

    static public    function find($id)
    {

        return self::query(sprintf("SELECT * FROM %s WHERE id= %d ", static::$table, (int)$id));
    }
    static public function where($assoc)
    {
        $where = '';
        $first = 1;
        foreach ($assoc as $key => $value) {
            // if ($first) {
            $where .= sprintf("%s ='%s' AND ", $key, $value);
            // }
        }
        $where .= ' 1 ';
        return self::query(sprintf('SELECT * FROM %s WHERE %s', static::$table, $where));
    }
    static public    function all()
    {
        return self::query(sprintf('SELECT * FROM %s', static::$table));
    }

    static public    function first()
    {
        return self::query(sprintf('SELECT * FROM %s ORDER BY id ASC LIMIT 1', static::$table));
    }

    static public    function last()
    {
        return self::query(sprintf('SELECT * FROM %s ORDER BY id DESC LIMIT 1', static::$table));
    }

    // Lifecycle

    public    function new_record()
    {
        return !isset($this->attributes['id']);
    }

    public    function save()
    {
        $db = self::connection();
        $column_map = self::column_map();
        $attributes = $this->attributes;

        if (!$this->new_record()) {
            $id = $attributes['id'];
            unset($attributes['id']);
        }

        // generate SETs for attributes (minus ID)
        $type_map = '';
        $map = array();
        foreach ($column_map as $column => $type) {
            if ($column == 'id')
                continue;

            $type_map .= $type;
            $casted = null;

            if (isset($attributes[$column])) {
                $casted = $attributes[$column];
            } else {
                switch ($type) {
                    case 'i':
                        $casted = 0;
                        break;
                    case 'b':
                    case 's':
                    default:
                        $casted = '';
                }
            }

            $map[$column] = $casted;
        }

        $sql_set = array();
        foreach ($map as $field => $value)
            $sql_set[] = sprintf('%s=?', $field);
        $sql_set = implode(', ', $sql_set);

        // setup the base SQL
        if ($this->new_record()) {
            $sql = 'INSERT INTO %s SET %s';
        } else {
            $sql = 'UPDATE %s SET %s WHERE id=?';
            $type_map .= 'i';
            $map['id'] = $id;
        }

        $sql = sprintf($sql, static::$table, $sql_set);
        $stmt = $db->prepare($sql);

        // convert map into a value map for binding
        // PHP > 5.3 wants references, so, we'll oblige.
        $params = array();
        $map = array_values($map);
        foreach ($map as $column => $value)
            $params[$column] = &$map[$column];
        array_unshift($map, $type_map);

        call_user_func_array(array(&$stmt, 'bind_param'), $map);

        $stmt->execute();
        $insert_id = $stmt->insert_id;
        $stmt->close();

        if ($this->new_record())
            $this->attributes['id'] = $insert_id;

        return $insert_id;
    }

    public    function delete()
    {
        $db = self::connection();
        $stmt = $db->prepare(sprintf('DELETE FROM %s WHERE id=?', static::$table));
        $stmt->bind_param('i', $this->id);
        $ret = $stmt->execute();
        $stmt->close();

        return $ret;
    }
}
